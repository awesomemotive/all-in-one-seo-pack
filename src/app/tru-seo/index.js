// Polyfill window for Web Worker context BEFORE any imports.
// This must be first to ensure module-level code can access window during import.
/* eslint-disable import-x/first */
self.window = self

// Import core worker class directly - avoid importing from @/vue/plugins/tru-seo
// to prevent bundling main-thread-only code like TruSeoWrapper, App, etc.
import AnalysisWebWorker from './worker/AnalysisWebWorker'
import registerPremiumAssessments from './worker/registerPremiumAssessments'
/* eslint-enable import-x/first */

/**
 * Maps assessor type keys to worker registration methods.
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
 * Lazily loads and registers all e-commerce assessors.
 * The e-commerce assessors are loaded via dynamic import (code-split),
 * keeping the initial worker bundle smaller while ensuring all assessors
 * are available when the user navigates to e-commerce post types.
 *
 * @since 5.0.0
 * @param {AnalysisWebWorker} worker The worker instance.
 * @returns {Promise<void>} Promise that resolves when the e-commerce assessors are registered.
 */
async function registerEcommerceAssessors (worker) {
	try {
		// Dynamically import e-commerce assessors module (code-split).
		const { assessorConfigs } = await import('./worker/ecommerceAssessors')

		// Register all e-commerce assessors for all post types.
		// This ensures assessors are available when user navigates between post types.
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

// Send immediate message when worker loads.
self.postMessage({ type: 'worker_script_loaded', message: 'Worker file executed!' })

// Lazy-load language pack and initialize worker on first message.
let workerInstance = null,
	isInitializing = false

self.onmessage = async ({ data }) => {
	// If worker is already initialized, forward the message.
	if (workerInstance) {
		workerInstance.handleMessage({ data })
		return
	}

	// If initialization is in progress, queue this message.
	if (isInitializing) {
		// Wait for initialization to complete, then handle message.
		const checkInit = setInterval(() => {
			if (workerInstance) {
				clearInterval(checkInit)
				workerInstance.handleMessage({ data })
			}
		}, 10)
		return
	}

	// Initialize worker on first message.
	isInitializing = true

	try {
		// Lazy load language pack based on locale from first message.
		// Using loadLanguageInstance for dynamic code-split loading.
		const locale = data.payload?.locale || data.locale || 'en_US'
		const { loadLanguageInstance } = await import('./languages/LanguageFactory')
		const Language = await loadLanguageInstance(locale)

		// Create worker instance with loaded language.
		workerInstance = new AnalysisWebWorker(self, Language.getResearcher())

		// Register premium assessments.
		try {
			registerPremiumAssessments(workerInstance)
		} catch (error) {
			console.error('Error registering premium assessments:', error)
		}

		// Register e-commerce assessors (lazy-loaded via dynamic import).
		await registerEcommerceAssessors(workerInstance)

		workerInstance.register()

		self.postMessage({ type: 'worker_initialized', message: 'Worker registered successfully!' })

		// Now handle the initial message that triggered initialization.
		workerInstance.handleMessage({ data })
	} catch (error) {
		self.postMessage({
			type    : 'worker_error',
			message : 'Failed to initialize worker: ' + error.message
		})
	} finally {
		isInitializing = false
	}
}