import { AnalysisWorkerWrapper } from '@/app/tru-seo/worker'
import MainThreadAnalysisRunner from '@/app/tru-seo/worker/MainThreadAnalysisRunner'
import {
	useLicenseStore,
	usePostEditorStore,
	useRootStore,
	useSpellCheckerDictionaryStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'
import { useTags } from '@/vue/composables/Tags'
import { getPostEditedContentForAnalysis } from './components/postContent'
import { getPostEditedSlug } from './components/postSlug'
import { getPostEditedTitle } from '@/vue/utils/postData/postTitle'
import { getCustomAnalysisType } from './utils/customAnalysisType'
import { getWooProductData } from './utils/wooProductData'
import TruSeoWorkerModule from '@/app/tru-seo/index.js?worker'
import { transformWorkerResults, updateStoreWithResults } from '@/vue/plugins/tru-seo/helpers/resultsHelper'
import KeywordCannibalizationService from './services/KeywordCannibalizationService'
import {
	clearAnalysisRefresher,
	clearAnalysisRunner,
	clearSharedWorker,
	getSharedWorker,
	setAnalysisRefresher,
	setAnalysisRunner,
	setSharedWorker
} from './spellingSuggestions'

const useWebWorker = 'true' === import.meta.env.VITE_TRUSEO_WEB_WORKER

let workerInitPromise = null,
	measureCanvas = null

/**
 * Wrapper class for TruSEO analysis.
 * Provides a simplified interface to the TruSEO App.
 *
 * @since 5.0.0
 */
export default class TruSeoWrapper {
	constructor (options = {}) {
		this.worker = null
		this.initialized = false
		this.analysisTimeout = null
		this._resolvePending = null
		this.debounceDelay = options?.debounceDelay ?? 500 // Default 500ms for typing UX
		this.useSharedWorker = options?.useSharedWorker ?? true // NEW: control worker sharing
		this.locale = options?.locale || null // NEW: explicit locale config
		this.customAnalysisType = options?.customAnalysisType || '' // NEW: explicit analysis type
		this.useCornerstone = options?.useCornerstone || false // NEW: explicit cornerstone flag
		this.useTaxonomy = options?.useTaxonomy || false // Selects the taxonomy assessors for terms
		this.contentNouns = options?.contentNouns || null // Taxonomy labels used in result copy
		this._lastPostId = null // Track post ID for cache clearing
		this._lastLocale = null // Track locale for worker re-initialization
		this.workerInitError = null // Track initialization errors
		this._hasAnalyzedNonEmptyContent = false // Gate the empty-content reset (see _executeAnalysis)
		this._cannibalizationService = new KeywordCannibalizationService()
	}

	/**
	 * Initializes the worker for TruSEO analysis.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Promise<void>} The promise of the initialization.
	 * @throws {Error} If worker initialization fails.
	 */
	async initializeWorker () {
		// If this instance already has the worker, we're done
		if (this.initialized && this.worker) {
			return
		}

		// If initialization previously failed, throw the error
		if (this.workerInitError) {
			throw this.workerInitError
		}

		// For independent workers (batch scanning), create dedicated worker
		if (!this.useSharedWorker) {
			return this._createDedicatedWorker()
		}

		// For shared workers (single post editor), use module-level shared worker
		// If a shared worker already exists, use it
		if (getSharedWorker()) {
			this.worker = getSharedWorker()
			this.initialized = true
			return
		}

		// If initialization is in progress, wait for it
		if (workerInitPromise) {
			await workerInitPromise
			this.worker = getSharedWorker()
			this.initialized = true
			return
		}

		// Initialize the worker (only once globally)
		workerInitPromise = (async () => {
			try {
				const postEditorStore = usePostEditorStore()
				const rootStore = useRootStore()
				const locale = postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US'

				// Detect the custom analysis type based on context
				const customAnalysisType = getCustomAnalysisType(
					postEditorStore.currentPost,
					rootStore
				)

				const configuration = {
					locale                : locale,
					contentAnalysisActive : true,
					keywordAnalysisActive : true,
					useCornerstone        : postEditorStore.currentPost?.cornerstone || false,
					customAnalysisType    : customAnalysisType,
					// Terms get the taxonomy assessors, which apply taxonomy text-length
					// thresholds instead of post ones. The batch scanner sets the same flag from
					// its analysis payload, so a list score matches what the term editor shows.
					useTaxonomy           : 'term' === postEditorStore.currentPost?.context,
					contentNouns          : postEditorStore.currentPost?.contentNouns || null,
					spellChecker          : {
						enabled           : window.aioseo?.spellChecker?.enabled ?? false,
						dictionaryBaseUrl : window.aioseo?.spellChecker?.dictionaryBaseUrl || '',
						safeWordsUrl      : window.aioseo?.spellChecker?.safeWordsUrl || '',
						safeWordsMetaUrl  : window.aioseo?.spellChecker?.safeWordsMetaUrl || '',
						settingsUrl       : window.aioseo?.spellChecker?.settingsUrl || ''
					},
					translations : {
						aioseo : {
							domain      : 'all-in-one-seo-pack',
							locale_data : {
								'all-in-one-seo-pack' : window.aioseoTranslations?.translations || {}
							}
						},
						aioseoPro : {
							domain      : 'aioseo-pro',
							locale_data : {
								'aioseo-pro' : window.aioseoTranslationsPro?.translationsPro || {}
							}
						}
					}
				}

				if (useWebWorker) {
					// Create worker directly - Vite handles bundling
					const webWorker = new TruSeoWorkerModule()
					setSharedWorker(new AnalysisWorkerWrapper(webWorker))
				} else {
					// Fallback: run analysis on the main thread (e.g. development mode)
					setSharedWorker(new MainThreadAnalysisRunner())
				}

				// Initialize the worker with configuration
				await getSharedWorker().initialize(configuration)

				return getSharedWorker()
			} catch (error) {
				clearSharedWorker()
				workerInitPromise = null
				throw error
			}
		})()

		try {
			await workerInitPromise
			this.worker = getSharedWorker()
			this.initialized = true

			// When the spell checker dictionary finishes loading, re-run analysis
			// so spelling results appear without requiring a content change.
			// We must apply the results to the store — otherwise the UI keeps
			// showing the placeholder "dictionary not available" text from the
			// first analysis (which ran before Hunspell finished loading).
			if (this.worker) {
				this.worker.onSpellCheckerReady = async () => {
					try {
						const postEditorStore = usePostEditorStore()
						const postId          = postEditorStore.currentPost?.id

						const results = await this.runAnalysis({ postId })

						if (results) {
							updateStoreWithResults(results)
						}

						// During a locale switch the first analysis runs before
						// the new-language dictionary is loaded, so `spellingChecker`
						// has no sentences and isn't in `availableHighlightAnalyzers`
						// when `useTruSeoLocale` enables highlights. Now that the
						// dictionary is ready, re-apply the auto-enable so the
						// spelling checker is included.
						const highlighter = useTruSeoHighlighterStore()
						if (highlighter.awaitingFreshHighlights) {
							highlighter.enableAllAvailableHighlights()
							highlighter.awaitingFreshHighlights = false
						}
					} catch (e) {
						// Ignore — a superseding analysis call will refresh.
					}
				}
			}

			// Register a refresher that re-runs analysis and applies results to
			// the store. Used by the safe-words flow to repaint highlights after
			// a dictionary change. Lives on the shared bridge so the
			// `TruSeoHighlighterStore` can request a refresh without statically
			// importing the worker chain (which would break addon IIFE builds).
			setAnalysisRefresher(async () => {
				const postEditorStore = usePostEditorStore()
				const postId          = postEditorStore.currentPost?.id

				const results = await this.runAnalysis({ postId })

				if (results) {
					updateStoreWithResults(results)
				}
			})

			// Register an immediate (non-debounced) analysis runner. The optimize
			// flow needs the post-change spelling results synchronously after
			// rewriting the body, which the debounced refresher can't guarantee —
			// the content-change analysis it triggers shares this instance's timer
			// and can supersede the refresh, resolving null without applying.
			setAnalysisRunner(async () => {
				const postEditorStore = usePostEditorStore()
				const postId          = postEditorStore.currentPost?.id

				const results = await this.runAnalysisImmediate({ postId })

				if (results) {
					updateStoreWithResults(results)
				}

				return results || null
			})
		} catch (error) {
			this.workerInitError = error
			console.error('❌ TruSEO Worker initialization failed:', error)
			throw new Error('Failed to initialize TruSEO analysis. Please refresh the page and try again.')
		} finally {
			workerInitPromise = null
		}
	}

	/**
	 * Creates a dedicated worker for batch scanning.
	 * Uses explicit config from constructor (provided by backend API).
	 * Does NOT access stores - config must be passed in.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Promise<void>} The promise of the initialization.
	 * @throws {Error} If worker initialization fails.
	 */
	async _createDedicatedWorker () {
		try {
			// For dedicated workers (batch scanning), use explicit config or simple defaults
			// Do NOT fall back to stores - config comes from API
			const locale = this.locale || window.aioseo?.user?.locale || 'en_US'
			const customAnalysisType = this.customAnalysisType || '' // Empty string default
			const useCornerstone = this.useCornerstone || false // False default
			const useTaxonomy = this.useTaxonomy || false // False default
			const contentNouns = this.contentNouns || null // Null default (posts)

			if (useWebWorker) {
				const webWorker = new TruSeoWorkerModule()
				this.worker = new AnalysisWorkerWrapper(webWorker)
			} else {
				// Fallback: run analysis on the main thread (e.g. development mode)
				this.worker = new MainThreadAnalysisRunner()
			}

			await this.worker.initialize({
				locale,
				contentAnalysisActive : true,
				keywordAnalysisActive : true,
				useCornerstone,
				customAnalysisType,
				useTaxonomy,
				contentNouns,
				// Same spell-checker config the shared (editor) worker gets, so the batch
				// worker loads the dictionary and scores spelling too. Without it the
				// assessment scores 0, calculateOverallScore awards full spelling marks,
				// and the list score sits ~5 points above the editor for posts with typos.
				spellChecker          : {
					enabled           : window.aioseo?.spellChecker?.enabled ?? false,
					dictionaryBaseUrl : window.aioseo?.spellChecker?.dictionaryBaseUrl || '',
					safeWordsUrl      : window.aioseo?.spellChecker?.safeWordsUrl || '',
					safeWordsMetaUrl  : window.aioseo?.spellChecker?.safeWordsMetaUrl || '',
					settingsUrl       : window.aioseo?.spellChecker?.settingsUrl || ''
				},
				translations : {
					aioseo : {
						domain      : 'all-in-one-seo-pack',
						locale_data : {
							'all-in-one-seo-pack' : window.aioseoTranslations?.translations || {}
						}
					},
					aioseoPro : {
						domain      : 'aioseo-pro',
						locale_data : {
							'aioseo-pro' : window.aioseoTranslationsPro?.translationsPro || {}
						}
					}
				}
			})

			this.initialized = true
		} catch (error) {
			this.workerInitError = error
			throw new Error('Failed to initialize TruSEO analysis worker.')
		}
	}

	/**
	 * Runs the TruSEO analysis with debouncing to avoid excessive calls.
	 *
	 * When a newer call arrives before the debounce window elapses, the
	 * pending promise resolves with `null` instead of rejecting. Callers
	 * already gate on `if (results)`, so a `null` resolution is a clean
	 * no-op and never surfaces as a console error.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} options Analysis options.
	 * @param {number} options.postId The post ID.
	 * @param {Object} options.postData The post data (optional).
	 * @param {string} options.content The post content (optional).
	 * @param {string} options.slug The post slug (optional).
	 * @returns {Promise<Object|null>} Resolves with analysis results, or `null` when superseded.
	 */
	runAnalysis ({ postId, postData, content, slug } = {}) {
		// Cancel any pending debounced analysis and resolve its promise with
		// `null` so callers treat the superseded run as a no-op.
		if (this.analysisTimeout) {
			clearTimeout(this.analysisTimeout)
			this.analysisTimeout = null
		}

		if (this._resolvePending) {
			this._resolvePending(null)
			this._resolvePending = null
		}

		return new Promise((resolve, reject) => {
			this._resolvePending = resolve
			this.analysisTimeout = setTimeout(async () => {
				this._resolvePending = null
				try {
					const results = await this._executeAnalysis({ postId, postData, content, slug })
					resolve(results)
				} catch (error) {
					reject(error)
				}
			}, this.debounceDelay)
		})
	}

	/**
	 * Runs the analysis immediately, bypassing the typing debounce, and resolves
	 * with the results. Does not touch `analysisTimeout`/`_resolvePending`, so it
	 * neither supersedes nor is superseded by the debounced content-change flow.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} options Analysis options.
	 * @param {number} options.postId The post ID.
	 * @returns {Promise<Object|undefined>} The analysis results (undefined when there is no content).
	 */
	runAnalysisImmediate ({ postId } = {}) {
		return this._executeAnalysis({ postId })
	}

	/**
	 * Executes the TruSEO analysis (internal method called after debounce).
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} options Analysis options.
	 * @param {number} options.postId The post ID.
	 * @param {Object} options.postData The post data (optional).
	 * @param {string} options.content The post content (optional).
	 * @param {string} options.slug The post slug (optional).
	 * @returns {Promise<Object>} Promise that resolves with analysis results.
	 * @private
	 */
	async _executeAnalysis ({ postId, postData, content, slug } = {}) {
		// Block analysis until the background dictionary install settles
		// (success or failure) so the worker's spell checker can load its
		// files. ensureDownloaded() resolves immediately when no download
		// is needed.
		await useSpellCheckerDictionaryStore().ensureDownloaded()

		const postEditorStore = usePostEditorStore()
		const locale = postEditorStore.currentPost?.truseo_locale || window.aioseo?.user?.locale || 'en_US'

		// Re-initialize the worker when the locale changes.
		// The worker's Researcher is language-specific (morphology, function words, etc.)
		// and is only created during worker initialization, so we must destroy and
		// re-create the worker to load the correct language module.
		if (this._lastLocale && locale !== this._lastLocale) {
			await this.destroy()
		}
		this._lastLocale = locale

		// Clear caches if post ID changed (navigating to different post).
		if (postId && this._lastPostId && postId !== this._lastPostId) {
			await this.clearCaches()
			// A different post is effectively a fresh load; don't let the previous
			// post's "seen content" state clobber this post's saved analysis.
			this._hasAnalyzedNonEmptyContent = false
		}
		this._lastPostId = postId || this._lastPostId

		// Use provided data or fetch from store/DOM
		const analysisContent = content || getPostEditedContentForAnalysis(false)
		const analysisSlug = slug?.toString() || getPostEditedSlug().toString()
		const currentPost = postData || postEditorStore.currentPost
		const truseoData = postEditorStore.truseoData

		// The editor can report empty content transiently while it boots; analyzing that
		// would wipe the saved analysis. Skip until we've seen real content once — after
		// that, an empty read is a genuine deletion, so let the worker refresh the results
		// (readability drops to "Not enough content") instead of leaving them stale.
		const isEmptyContent = !analysisContent || '' === analysisContent.trim()
		if (isEmptyContent && !this._hasAnalyzedNonEmptyContent) {
			return
		}
		if (!isEmptyContent) {
			this._hasAnalyzedNonEmptyContent = true
		}

		// Pre-fetch keyword cannibalization data (cached by keyphrase). Keyword cannibalization
		// is a Pro, licensed-only feature backed by a Pro-only REST route, so skip it entirely
		// when unlicensed (which includes the Lite build) — otherwise the client calls an
		// endpoint that isn't registered and logs a 404.
		const focusKeyphrase = truseoData?.focusKeyword || ''
		const customData = {}
		if (!useLicenseStore().isUnlicensed && focusKeyphrase && postId) {
			// The ID is a term ID in the term editor, so the route has to be told which table to
			// resolve it against — otherwise it looks up an unrelated post and the check never fires.
			const objectType = 'term' === postEditorStore.currentPost?.context ? 'term' : 'post'
			const cannibalizationResult = await this._cannibalizationService.fetch(focusKeyphrase, postId, objectType)
			customData.keywordCannibalization = cannibalizationResult
		} else {
			// No focus keyphrase, or the feature is unsupported on this build — clear any cache.
			this._cannibalizationService.clear()
		}

		// WooCommerce product facts for the productIdentifier / productSKU assessments. Null for
		// anything that isn't a product, which leaves both assessments inapplicable.
		const wooProduct = getWooProductData(currentPost)
		if (wooProduct) {
			Object.assign(customData, wooProduct)
		}

		return this.runWorkerAnalysis({
			postId,
			currentPost,
			truseoData,
			analysisContent,
			analysisSlug,
			locale,
			customData
		})
	}

	/**
	 * Runs analysis using Web Worker (asynchronous, non-blocking).
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} options Analysis options.
	 * @returns {Promise<Object>} Promise that resolves with analysis results.
	 */
	async runWorkerAnalysis ({ currentPost, truseoData, analysisContent, analysisSlug, locale, customData }) {
		try {
			// Initialize worker if needed
			await this.initializeWorker()

			// Ensure worker is ready
			if (!this.worker) {
				throw new Error('Worker not initialized')
			}

			// Analyze the rendered meta title/description (smart tags parsed),
			// not the post H1 or the raw template. This is what search engines
			// see and what the SERP preview shows, so keyphrase-in-title and
			// keyphrase-in-description assessments score the real values.
			// `separator: undefined` lets parseTags resolve #separator_sa from
			// the tags store, matching the SEO preview.
			// Collapse and trim whitespace like the server's Meta\Helpers::sanitize(),
			// so empty smart tags don't widen the title past what the batch scan measures.
			const { parseTags } = useTags({ separator: undefined })
			const normalizeMeta = (value) => (value || '').replace(/\s+/g, ' ').trim()
			const metaTitle = normalizeMeta(parseTags(currentPost.title || currentPost.tags?.title || '#post_title #separator_sa #site_title'))
			const metaDescription = normalizeMeta(parseTags(currentPost.description || currentPost.tags?.description || '#post_content'))

			const paper = {
				text               : analysisContent,
				keyword            : truseoData?.focusKeyword || '',
				synonyms           : truseoData?.focusKeywordSynonyms || '',
				additionalKeywords : truseoData?.additionalKeywords?.map(kw => ({
					word     : kw.word,
					synonyms : kw?.synonyms || ''
				})) || null,
				description : metaDescription,
				title       : metaTitle,
				// Raw visible post title (not the meta title) — the H1 assessment counts it as the page's H1.
				postTitle   : getPostEditedTitle() || '',
				titleWidth  : this.measureTextWidth(metaTitle),
				slug        : analysisSlug,
				locale      : locale,
				permalink   : currentPost.permalink || '',
				customData  : customData || {}
			}

			const { result } = await this.worker.analyze(paper)

			if (!result) {
				throw new Error('Worker returned no results')
			}

			const seoAnalysis = result.seo?.focusKeyword || { score: 0, results: [] }
			const contentAnalysis = result.readability || { score: 0, results: [] }
			const additionalKeywordsAnalysis = result.seo?.additionalKeywords || []

			// Transform and return results (do NOT update store)
			return transformWorkerResults(contentAnalysis, seoAnalysis, additionalKeywordsAnalysis)
		} catch (error) {
			console.error('❌ TruSEO analysis failed:', error)
			throw error
		}
	}

	/**
	 * Handles analysis errors and notifies the user.
	 *
	 * @since 5.0.0
	 *
	 * @returns {void}
	 */
	handleAnalysisError () {
		const postEditorStore = usePostEditorStore()

		// Reset loading states
		if (postEditorStore.currentPost) {
			postEditorStore.currentPost.loading.focus = false
			postEditorStore.currentPost.additional_keywords?.forEach((_keyphrase, index) => {
				postEditorStore.currentPost.loading.additional[index] = false
			})
		}

		// Show user-friendly error notification if rootStore is available
		const rootStore = useRootStore()
		if (rootStore?.addNotification) {
			rootStore.addNotification({
				type    : 'error',
				message : 'TruSEO analysis failed. Please refresh the page and try again. If the problem persists, contact support.'
			})
		}
	}

	/**
	 * Measures the width of text (helper for title width calculation).
	 * Uses a cached canvas element for performance.
	 *
	 * @since 5.0.0
	 *
	 * @param {string} text The text to measure.
	 * @returns {number} The width in pixels.
	 */
	measureTextWidth (text) {
		if ('undefined' === typeof document) {
			return 0
		}

		// Cache canvas element to avoid creating new one on each call.
		if (!measureCanvas) {
			measureCanvas = document.createElement('canvas')
		}

		const context = measureCanvas.getContext('2d')
		context.font = '20px Arial'
		return context.measureText(text).width
	}

	/**
	 * Clears all caches in the worker.
	 * Call this when navigating between posts or when memory needs to be freed.
	 *
	 * @since 5.0.0
	 * @returns {Promise<void>} The promise of cache clearing.
	 */
	async clearCaches () {
		this._cannibalizationService.clear()

		if (this.worker) {
			try {
				await this.worker.clearCaches()
			} catch (error) {
				console.warn('Failed to clear caches:', error)
			}
		}
	}

	/**
	 * Gets cache statistics from the worker for debugging.
	 *
	 * @since 5.0.0
	 * @returns {Promise<Object>} The promise containing cache stats.
	 */
	async getCacheStats () {
		if (this.worker) {
			try {
				return await this.worker.getCacheStats()
			} catch (error) {
				console.warn('Failed to get cache stats:', error)
				return {}
			}
		}
		return {}
	}

	/**
	 * Destroys the worker if it exists and clears any pending analysis.
	 * Also resets module-level shared state to allow reinitialization.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Promise<void>} Resolves when cleanup is complete.
	 */
	async destroy () {
		// Clear any pending debounced analysis and resolve its promise with
		// `null` so in-flight callers treat the cancelled run as a no-op.
		if (this.analysisTimeout) {
			clearTimeout(this.analysisTimeout)
			this.analysisTimeout = null
		}
		if (this._resolvePending) {
			this._resolvePending(null)
			this._resolvePending = null
		}

		// Clear caches before terminating.
		await this.clearCaches()
		this._cannibalizationService.clear()

		// Terminate worker
		if (this.worker) {
			// Web Worker wrapper has a _worker with terminate(), main-thread runner does not.
			if (this.worker._worker?.terminate) {
				this.worker._worker.terminate()
			}
			this.worker = null
			this.initialized = false
		}

		// Reset module-level shared state to allow reinitialization.
		clearSharedWorker()
		clearAnalysisRefresher()
		clearAnalysisRunner()
		workerInitPromise = null

		// Reset tracking state.
		this._lastPostId = null
		this.workerInitError = null
		this._hasAnalyzedNonEmptyContent = false
	}
}