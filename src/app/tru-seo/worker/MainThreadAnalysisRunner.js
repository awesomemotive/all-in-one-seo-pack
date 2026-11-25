import AnalysisWebWorker from './AnalysisWebWorker'
import registerPremiumAssessments from '../worker/registerPremiumAssessments'
import Transporter from './transporter'

/**
 * Assessor type to worker registration method mapping.
 *
 * @since 5.0.0
 */
const assessorMethodMap = {
	seo                       : 'setCustomSEOAssessorClass',
	cornerstoneSeo            : 'setCustomCornerstoneSEOAssessorClass',
	content                   : 'setCustomContentAssessorClass',
	cornerstoneContent        : 'setCustomCornerstoneContentAssessorClass',
	relatedKeyword            : 'setCustomRelatedKeywordAssessorClass',
	cornerstoneRelatedKeyword : 'setCustomCornerstoneRelatedKeywordAssessorClass'
}

/**
 * Lazily loads and registers all e-commerce assessors on the main thread.
 *
 * @since 5.0.0
 * @param {AnalysisWebWorker} worker The worker instance.
 * @returns {Promise<void>} Promise that resolves when the e-commerce assessors are registered.
 */
async function registerEcommerceAssessors (worker) {
	try {
		const { assessorConfigs } = await import('../worker/ecommerceAssessors')

		Object.entries(assessorConfigs).forEach(([ postType, config ]) => {
			Object.entries(config).forEach(([ assessorType, AssessorClass ]) => {
				if (AssessorClass && assessorMethodMap[assessorType]) {
					worker[assessorMethodMap[assessorType]](AssessorClass, postType)
				}
			})
		})
	} catch (error) {
		console.error('Error loading e-commerce assessors:', error)
	}
}

/**
 * Main-thread fallback for TruSEO analysis.
 * Provides the same API as AnalysisWorkerWrapper but runs AnalysisWebWorker
 * directly on the main thread instead of in a Web Worker.
 *
 * Used when VITE_TRUSEO_WEB_WORKER is not enabled (e.g. development mode).
 *
 * @since 5.0.0
 */
export default class MainThreadAnalysisRunner {
	constructor () {
		this._worker = null
		this._initialized = false
		this._pendingRequests = {}
		this._requestId = 0
	}

	/**
	 * Initializes the analysis engine on the main thread.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} configuration The configuration to initialize with.
	 * @returns {Promise<Object>} The initialization result.
	 */
	async initialize (configuration) {
		if (this._initialized) {
			return { success: true }
		}

		const locale = configuration?.locale || 'en_US'

		// Load language pack.
		const { loadLanguageInstance } = await import('../languages/LanguageFactory')
		const Language = await loadLanguageInstance(locale)

		// Create a fake scope that routes postMessage calls back to our handler.
		const scope = this._createScope()

		// Create worker instance with loaded language.
		this._worker = new AnalysisWebWorker(scope, Language.getResearcher())

		// Register premium assessments.
		try {
			registerPremiumAssessments(this._worker)
		} catch (error) {
			console.error('Error registering premium assessments:', error)
		}

		// Register e-commerce assessors.
		await registerEcommerceAssessors(this._worker)

		this._worker.register()

		// Send the initialize message directly.
		const result = await this._sendMessage('initialize', configuration)
		this._initialized = true

		return result
	}

	/**
	 * Analyzes the paper on the main thread.
	 *
	 * @since 5.0.0
	 *
	 * @param {Object} paper The paper to analyze.
	 * @returns {Promise<Object>} The analysis results.
	 */
	analyze (paper) {
		return this._sendMessage('analyze', { paper }).then(result => ({ result }))
	}

	/**
	 * Clears all caches.
	 *
	 * @since 5.0.0
	 * @returns {Promise<Object>} The result of clearing caches.
	 */
	clearCaches () {
		return this._sendMessage('clearCaches', {})
	}

	/**
	 * Gets cache statistics.
	 *
	 * @since 5.0.0
	 * @returns {Promise<Object>} The cache statistics.
	 */
	getCacheStats () {
		return this._sendMessage('getCacheStats', {})
	}

	/**
	 * Requests spelling suggestions for a word.
	 *
	 * @since 5.0.0
	 *
	 * @param {string} word The misspelled word.
	 * @returns {Promise<Object>} Resolves with { suggestions: string[] }.
	 */
	requestSuggestions (word) {
		return this._sendMessage('spellChecker:suggest', { word }).then(result => ({ result }))
	}

	/**
	 * Asks the worker whether a single word is spelled correctly.
	 *
	 * @since 5.0.0
	 *
	 * @param {string} word The word to check.
	 * @returns {Promise<Object>} Resolves with { result: { valid: boolean } }.
	 */
	requestSpellingCheck (word) {
		return this._sendMessage('spellChecker:check', { word }).then(result => ({ result }))
	}

	/**
	 * Adds a word to the per-site safe-words dictionary in the live Hunspell instance.
	 *
	 * @since 5.0.0
	 *
	 * @param {string}  word      The word to add.
	 * @param {boolean} matchCase Whether the word must match its exact casing.
	 * @returns {Promise<Object>} Resolves with { result: { success: boolean } }.
	 */
	requestAddSafeWord (word, matchCase = false) {
		return this._sendMessage('spellChecker:addSafeWord', { word, matchCase }).then(result => ({ result }))
	}

	/**
	 * Removes a word from the per-site safe-words dictionary in the live Hunspell instance.
	 *
	 * @since 5.0.0
	 *
	 * @param {string} word The word to remove.
	 * @returns {Promise<Object>} Resolves with { result: { success: boolean } }.
	 */
	requestRemoveSafeWord (word) {
		return this._sendMessage('spellChecker:removeSafeWord', { word }).then(result => ({ result }))
	}

	/**
	 * Toggles the match-case state of a safe word in the live Hunspell instance.
	 *
	 * @since 5.0.0
	 *
	 * @param {string}  word      The word to update.
	 * @param {boolean} matchCase The desired match-case state.
	 * @returns {Promise<Object>} Resolves with { result: { success: boolean } }.
	 */
	requestSetSafeWordMatchCase (word, matchCase) {
		return this._sendMessage('spellChecker:setSafeWordMatchCase', { word, matchCase }).then(result => ({ result }))
	}

	/**
	 * Creates a fake scope object that routes postMessage calls to pending request handlers.
	 *
	 * @since 5.0.0
	 *
	 * @returns {Object} A scope object with postMessage and onmessage.
	 */
	_createScope () {
		return {
			postMessage : ({ type, id, payload }) => {
				// Ignore status messages without a request ID.
				if ('worker_script_loaded' === type || 'worker_initialized' === type) {
					return
				}

				// Handle spell checker dictionary ready notification.
				if ('spellChecker:ready' === type) {
					if ('function' === typeof this.onSpellCheckerReady) {
						this.onSpellCheckerReady()
					}

					return
				}

				const request = this._pendingRequests[id]
				if (!request) {
					return
				}

				payload = Transporter.parse(payload)
				delete this._pendingRequests[id]

				if (type.endsWith(':done')) {
					request.resolve(payload)
				} else if (type.endsWith(':failed')) {
					request.reject(payload)
				}
			},
			onmessage : null
		}
	}

	/**
	 * Sends a message to the AnalysisWebWorker and returns a Promise.
	 * Uses request IDs to match responses from the async scheduler.
	 *
	 * @since 5.0.0
	 *
	 * @param {string} type    The message type.
	 * @param {Object} payload The message payload.
	 * @returns {Promise<Object>} The result.
	 */
	_sendMessage (type, payload) {
		const id = ++this._requestId

		return new Promise((resolve, reject) => {
			this._pendingRequests[id] = { resolve, reject }

			// Send the message directly to handleMessage.
			this._worker.handleMessage({
				data : {
					type,
					id,
					payload : Transporter.serialize(payload)
				}
			})
		})
	}
}