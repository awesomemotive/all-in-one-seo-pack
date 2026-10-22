import { buildDatabaseResults } from '@/vue/plugins/tru-seo/helpers/resultsHelper'
import { withoutEphemeralAnalysisData } from '@/vue/utils/postData/analysisPayload'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { __, sprintf } from '@/vue/plugins/translations'
import { td } from '@/vue/plugins/constants'
import { useSpellCheckerDictionaryStore } from '@/vue/stores'

/**
 * Manages batch TruSEO scanning for posts in the admin list table.
 * Handles concurrency, progress tracking, and graceful cleanup on navigation.
 *
 * @since 5.0.0
 */
export default class BatchScanManager {
	constructor ({ maxWorkers = 5, posts = [], startDelay = 2000, objectType = 'post' }) {
		this.maxWorkers = maxWorkers // Default 5 concurrent workers

		// 'post' or 'term' — decides which REST route results are saved to. Everything else about
		// the scan is identical, since the analysis payload is normalised server-side.
		this.objectType = objectType

		// A post without an analysis payload can never be scanned — the API omits it for posts the
		// user cannot edit — so drop it here rather than in scanPost, where it would still count
		// towards the progress total and leave it stuck below 100%.
		this.posts = posts.filter(p => !p.hasTruseoData && p.truseoData)
		this.queue = [ ...this.posts ]
		this.activeScans = 0
		this.completedScans = 0
		this.failedScans = 0
		this.totalToScan = this.posts.length

		// NEW: Worker pool management
		this.availableWorkers = [] // Pool of idle TruSeoWrapper instances
		this.busyWorkers = new Set() // Currently busy workers

		this.isDestroyed = false
		this.startDelay = startDelay
		this.progressNoticeElement = null
		this.measureCanvas = null

		this.cleanup = this.cleanup.bind(this)
		window.addEventListener('beforeunload', this.cleanup)
	}

	/**
	 * Start the batch scanning process after configured delay.
	 *
	 * @since 5.0.0
	 * @returns {Promise<void>} Nothing to return.
	 */
	async start () {
		// Delay start to allow user to navigate away if desired
		await this.delay(this.startDelay)

		if (this.isDestroyed) {
			return
		}

		if (0 === this.totalToScan) {
			return
		}

		// Wait for the background dictionary install to settle so the spell
		// checker has its files when the workers boot. Resolves immediately
		// when no download is required, and on failure too.
		await useSpellCheckerDictionaryStore().ensureDownloaded()

		if (this.isDestroyed) {
			return
		}

		// Show progress indicator if we have posts to scan
		if (0 < this.totalToScan) {
			this.showProgressIndicator()
		}

		// Start processing queue
		this.processQueue()
	}

	/**
	 * Process the queue with concurrency control.
	 *
	 * @since 5.0.0
	 * @returns {Promise<void>} Nothing to return.
	 */
	async processQueue () {
		const inflight = new Set()

		while (0 < this.queue.length || 0 < inflight.size) {
			if (this.isDestroyed) {
				return
			}

			// Start new scans up to concurrency limit
			while (0 < this.queue.length && inflight.size < this.maxWorkers) {
				const post    = this.queue.shift()
				const promise = this.scanPost(post).finally(() => inflight.delete(promise))
				inflight.add(promise)
				this.activeScans = inflight.size
			}

			// Wake up as soon as any in-flight scan finishes — no polling delay.
			if (0 < inflight.size) {
				await Promise.race(inflight)
				if (this.isDestroyed) {
					return
				}
				this.activeScans = inflight.size
			}
		}

		// All scans complete
		if (!this.isDestroyed) {
			this.showCompletionNotice()
		}
	}

	/**
	 * Creates a new dedicated TruSeoWrapper instance.
	 *
	 * @since 5.0.0
	 *
	 * @param   {Object} config Worker configuration (locale, customAnalysisType, etc.)
	 * @returns {Promise}       Initialized wrapper.
	 */
	async createWorker (config = {}) {
		const TruSeoWrapper = (await import('@/vue/plugins/tru-seo/TruSeoWrapper')).default

		const wrapper = new TruSeoWrapper({
			debounceDelay      : 0, // No debouncing for batch scanning
			useSharedWorker    : false, // Dedicated worker
			locale             : config.locale || window.aioseo?.user?.locale || 'en_US',
			customAnalysisType : config.customAnalysisType || '',
			useCornerstone     : config.useCornerstone || false,
			// Without this a term is scored by the post assessor, which runs checks a term can
			// never satisfy (single H1, images, links) against a 300-word threshold — so the
			// backfilled list score would sit far below what the term editor shows.
			useTaxonomy        : config.useTaxonomy || false,
			contentNouns       : config.contentNouns || null
		})

		await wrapper.initializeWorker()

		return wrapper
	}

	/**
	 * Gets an available worker from pool or creates new one.
	 *
	 * @since 5.0.0
	 *
	 * @param   {Object} config Worker configuration.
	 * @returns {Promise}       Available worker.
	 */
	async getOrCreateWorker (config = {}) {
		// Try to get an idle worker from pool
		if (0 < this.availableWorkers.length) {
			const wrapper = this.availableWorkers.pop()
			this.busyWorkers.add(wrapper)
			return wrapper
		}

		// Create new worker if under limit
		if (this.busyWorkers.size < this.maxWorkers) {
			const wrapper = await this.createWorker(config)
			this.busyWorkers.add(wrapper)
			return wrapper
		}

		// All workers busy - wait briefly and retry
		await new Promise(resolve => setTimeout(resolve, 100))
		return this.getOrCreateWorker(config) // Recursive retry
	}

	/**
	 * Releases worker back to available pool.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} wrapper Worker to release.
	 * @returns {Promise<void>} Nothing to return.
	 */
	async releaseWorker (wrapper) {
		if (!wrapper) {
			return
		}

		try {
			// Clear worker cache before returning to pool
			await wrapper.clearCaches()
		} finally {
			// Always return the worker to the pool, even if clearCaches fails,
			// to prevent leaking workers and causing infinite recursion in getOrCreateWorker.
			this.busyWorkers.delete(wrapper)
			this.availableWorkers.push(wrapper)
		}
	}

	/**
	 * Terminates a specific worker.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} wrapper Worker to terminate.
	 * @returns {Promise<void>} Nothing to return.
	 */
	async terminateWorker (wrapper) {
		if (!wrapper) {
			return
		}

		await wrapper.destroy()
		this.busyWorkers.delete(wrapper)

		const index = this.availableWorkers.indexOf(wrapper)
		if (-1 < index) {
			this.availableWorkers.splice(index, 1)
		}
	}

	/**
	 * Terminates all workers in the pool.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Promise<void>} Nothing to return.
	 */
	async terminateAllWorkers () {
		// Terminate busy workers
		for (const wrapper of this.busyWorkers) {
			await wrapper.destroy()
		}
		this.busyWorkers.clear()

		// Terminate available workers
		for (const wrapper of this.availableWorkers) {
			await wrapper.destroy()
		}
		this.availableWorkers = []
	}

	/**
	 * Scan a single post.
	 *
	 * @since 5.0.0
	 * @param   {Object} post Post object with id.
	 * @returns {Promise<void>} Nothing to return.
	 */
	async scanPost (post) {
		const postData = post?.truseoData
		if (this.isDestroyed || !postData) {
			return
		}

		let workerWrapper = null

		try {
			// Show loading state
			this.updatePostLoadingState(post.id, true)

			// Get or create dedicated worker with post-specific config
			workerWrapper = await this.getOrCreateWorker({
				locale             : postData.locale,
				customAnalysisType : postData.customAnalysisType,
				useCornerstone     : postData.cornerstone,
				useTaxonomy        : postData.useTaxonomy,
				contentNouns       : postData.contentNouns
			})

			// Analyze post with dedicated worker
			const results = await this.analyzePost(postData, workerWrapper)

			// Save results to database
			await this.saveResults(post.id, results)

			// Update UI with new score
			this.updatePostScore(post.id, results.seo_score)

			this.completedScans++
			this.updateProgressIndicator()
		} catch (error) {
			this.failedScans++
			this.updateProgressIndicator()
		} finally {
			// Always release worker back to pool
			if (workerWrapper) {
				await this.releaseWorker(workerWrapper)
			}

			// Hide loading state
			this.updatePostLoadingState(post.id, false)
		}
	}

	/**
	 * Analyze a post using TruSeoWrapper.
	 *
	 * @since 5.0.0
	 *
	 * @param   {Object} postData      Post data from API.
	 * @param   {Object} workerWrapper TruSeoWrapper instance to use.
	 * @returns {Promise<Object>}      Analysis results.
	 */
	async analyzePost (postData, workerWrapper) {
		// Measure title width
		const titleWidth = this.measureTextWidth(postData.aioseoTitle || postData.wpTitle || '')

		// Build Paper object
		const paper = {
			text               : postData.content || '',
			keyword            : postData.focusKeyword || '',
			synonyms           : postData.focusKeywordSynonyms || '',
			additionalKeywords : postData.additionalKeywords?.map(kw => ({
				word     : kw.word,
				synonyms : kw?.synonyms || ''
			})) || null,
			description : postData.description || '',
			title       : postData.aioseoTitle || postData.wpTitle || '',
			// Raw visible post title — the H1 assessment counts it as the page's H1.
			postTitle   : postData.postTitle || '',
			titleWidth,
			slug        : postData.slug || '',
			locale      : postData.locale,
			permalink   : postData.permalink || ''
		}

		// Call worker directly
		const { result } = await workerWrapper.worker.analyze(paper)
		if (!result) {
			throw new Error('Worker analysis returned no results')
		}

		// Use shared helper for transformation
		return buildDatabaseResults(
			result.readability,
			result.seo?.focusKeyword,
			result.seo?.additionalKeywords,
			postData
		)
	}

	/**
	 * Save analysis results to database.
	 *
	 * @since 5.0.0
	 * @param   {number} postId  Post ID.
	 * @param   {Object} results Analysis results.
	 * @returns {Promise<void>} Nothing to return.
	 */
	async saveResults (postId, results) {
		const route = 'term' === this.objectType ? 'truseo/terms' : 'truseo/posts'
		await http.post(links.restUrl(`${route}/${postId}`))
			.send(withoutEphemeralAnalysisData({
				truseo              : results.truseo,
				seo_score           : results.seo_score,
				focus_keyword       : results.focus_keyword,
				additional_keywords : results.additional_keywords
			}))
	}

	/**
	 * Update post loading state in UI.
	 *
	 * @since 5.0.0
	 * @param {number}  postId    Post ID.
	 * @param {boolean} isLoading Whether post is loading.
	 * @returns {void} Nothing to return.
	 */
	updatePostLoadingState (postId, isLoading) {
		if (window.aioseoBus) {
			window.aioseoBus.$emit('batchScanLoading' + postId, isLoading)
		}
	}

	/**
	 * Update post score in UI.
	 *
	 * @since 5.0.0
	 * @param {number} postId Post ID.
	 * @param {number} score  New score.
	 * @returns {void} Nothing to return.
	 */
	updatePostScore (postId, score) {
		if (window.aioseoBus) {
			window.aioseoBus.$emit('batchScanScoreUpdate' + postId, score)
		}
	}

	/**
	 * Returns the plural noun for what is being scanned, e.g. "categories".
	 *
	 * NOTE: Localized per list screen from the registered post type/taxonomy labels, so the notice
	 * doesn't say "posts" on a product or category list.
	 *
	 * @returns {string} The plural noun.
	 */
	objectNounPlural () {
		return window.aioseo?.objectNouns?.plural || __('posts', td)
	}

	/**
	 * Show progress indicator notice.
	 *
	 * @since 5.0.0
	 * @returns {void} Nothing to return.
	 */
	showProgressIndicator () {
		this.removeTruSeoResetNotice()

		// Create admin notice
		const notice = document.createElement('div')
		notice.className = 'notice notice-info aioseo-batch-scan-progress'
		notice.style.cssText = 'display: flex; align-items: center; margin-left: 0 !important; padding: 12px; position: relative;'

		const message = document.createElement('p')
		message.style.cssText = 'margin: 0; font-weight: 500;'
		message.textContent = sprintf(
			// Translators: 1 - The number of posts analyzed, 2 - The total number of posts to analyze.
			__('Running TruSEO analysis... (%1$d/%2$d)', td),
			0,
			this.totalToScan
		)

		notice.appendChild(message)

		// Insert at top of page
		const wrap = document.querySelector('.wrap')
		const subsubsub = wrap?.querySelector('.subsubsub')
		if (wrap) {
			if (subsubsub) {
				wrap.insertBefore(notice, subsubsub)
			} else if (wrap.parentNode) {
				wrap.parentNode.insertBefore(notice, wrap)
			}

			this.progressNoticeElement = notice
		}
	}

	/**
	 * Update progress indicator.
	 *
	 * @since 5.0.0
	 * @returns {void} Nothing to return.
	 */
	updateProgressIndicator () {
		this.removeTruSeoResetNotice()

		if (this.progressNoticeElement) {
			const message = this.progressNoticeElement.querySelector('p')
			if (message) {
				const totalProcessed = this.completedScans + this.failedScans

				if (0 < this.failedScans) {
					message.textContent = sprintf(
						// Translators: 1 - The number of posts analyzed, 2 - The total number of posts to analyze, 3 - The number of posts that failed.
						__('Running TruSEO analysis... (%1$d/%2$d, %3$d failed)', td),
						totalProcessed,
						this.totalToScan,
						this.failedScans
					)
				} else {
					message.textContent = sprintf(
						// Translators: 1 - The number of posts analyzed, 2 - The total number of posts to analyze.
						__('Running TruSEO analysis... (%1$d/%2$d)', td),
						totalProcessed,
						this.totalToScan
					)
				}
			}
		}
	}

	/**
	 * Show completion notice.
	 *
	 * @since 5.0.0
	 * @returns {void} Nothing to return.
	 */
	showCompletionNotice () {
		this.removeTruSeoResetNotice()

		// Remove progress notice
		if (this.progressNoticeElement) {
			this.progressNoticeElement.remove()
			this.progressNoticeElement = null
		}

		// Don't show notice if nothing was processed
		if (0 === this.completedScans && 0 === this.failedScans) {
			return
		}

		// Show completion notice
		const notice = document.createElement('div')
		const hasFailures = 0 < this.failedScans
		const hasSuccesses = 0 < this.completedScans
		notice.className = hasFailures ? 'notice notice-error is-dismissible' : 'notice notice-success is-dismissible'
		notice.style.cssText = 'padding: 12px; margin-left: 0 !important;'

		const message = document.createElement('p')
		message.style.cssText = 'margin: 0;'

		// Build message based on results
		if (hasFailures && hasSuccesses) {
			// Mixed results: some succeeded, some failed
			message.textContent = sprintf(
				// Translators: 1 - The number analyzed successfully, 2 - The number that failed, 3 - The plural object noun, e.g. "categories".
				__('TruSEO analysis completed with errors. %1$d %3$s analyzed successfully, but %2$d could not be analyzed. Please refresh the page and try again.', td),
				this.completedScans,
				this.failedScans,
				this.objectNounPlural()
			)
		} else if (hasFailures && !hasSuccesses) {
			// All failed
			message.textContent = sprintf(
				// Translators: 1 - The number that failed, 2 - The plural object noun, e.g. "categories".
				__('TruSEO analysis failed. Could not analyze %1$d %2$s. Please refresh the page and try again.', td),
				this.failedScans,
				this.objectNounPlural()
			)
		} else {
			// All succeeded
			message.textContent = sprintf(
				// Translators: 1 - The number analyzed successfully, 2 - The plural object noun, e.g. "categories".
				__('TruSEO analysis complete! Successfully analyzed %1$d %2$s.', td),
				this.completedScans,
				this.objectNounPlural()
			)
		}

		notice.appendChild(message)

		// Add dismiss button
		const dismissButton = document.createElement('button')
		dismissButton.type = 'button'
		dismissButton.className = 'notice-dismiss'
		dismissButton.innerHTML = `<span class="screen-reader-text">${__('Dismiss this notice.', td)}</span>`
		dismissButton.onclick = () => notice.remove()
		notice.appendChild(dismissButton)

		// Insert at top of page
		const wrap = document.querySelector('.wrap')
		const subsubsub = wrap?.querySelector('.subsubsub')
		if (wrap) {
			if (subsubsub) {
				wrap.insertBefore(notice, subsubsub)
			} else if (wrap.parentNode) {
				wrap.parentNode.insertBefore(notice, wrap)
			}
		}

		// Auto-dismiss after 5 seconds (or longer for errors)
		if (!hasFailures) {
			setTimeout(() => {
				if (notice.parentNode) {
					notice.remove()
				}
			}, 5000)
		}
	}

	/**
	 * Cleanup resources and event listeners.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Promise<void>} Nothing to return.
	 */
	async cleanup () {
		if (this.isDestroyed) {
			return
		}

		this.isDestroyed = true

		// Remove event listeners
		window.removeEventListener('beforeunload', this.cleanup)

		// Terminate all workers
		await this.terminateAllWorkers()

		// Remove progress notice
		if (this.progressNoticeElement) {
			this.progressNoticeElement.remove()
			this.progressNoticeElement = null
		}
	}

	/**
	 * Measures the width of text (helper for title width calculation).
	 * Uses a cached canvas element for performance.
	 *
	 * @since 5.0.0
	 * @param   {string} text The text to measure.
	 * @returns {number} The width in pixels.
	 */
	measureTextWidth (text) {
		if ('undefined' === typeof document) {
			return 0
		}

		// Create canvas element for measuring
		if (!this.measureCanvas) {
			this.measureCanvas = document.createElement('canvas')
		}

		const context = this.measureCanvas.getContext('2d')
		context.font = '20px Arial'
		return context.measureText(text).width
	}

	/**
	 * Delay helper.
	 *
	 * @since 5.0.0
	 * @param   {number} ms Milliseconds to delay.
	 * @returns {Promise<void>} Nothing to return.
	 */
	delay (ms) {
		return new Promise(resolve => setTimeout(resolve, ms))
	}

	/**
	 * Remove TruSEO reset notice.
	 *
	 * @since 5.0.0
	 * @returns {void} Nothing to return.
	 */
	removeTruSeoResetNotice () {
		const noticeReset = document.querySelector('.notice-truseo-reset')
		if (noticeReset) {
			noticeReset.remove()
		}
	}
}