import {
	usePostEditorStore,
	useSeoRevisionsStore
} from '@/vue/stores'

import emitter from 'tiny-emitter/instance'
import { isEqual } from 'lodash-es'
import { debounce } from '@/vue/utils/debounce'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { getEditorData } from './helpers'

let editorData = {}

/**
 * Run TruSEO analysis when content updates.
 *
 * @returns {void}.
 */
const handleEditorChange = () => {
	const oldData = { ...editorData }
	const data = getEditorData()

	if (!isEqual(oldData, data)) {
		editorData = data
		updatePostData()
	}
}

/**
 * Save SEO Settings when Avada editor is saved.
 *
 * @returns {void}.
 */
const handleEditorSave = () => {
	const postEditorStore = usePostEditorStore()
	postEditorStore.saveCurrentPost(postEditorStore.currentPost).then(() => {
		const seoRevisionsStore = useSeoRevisionsStore()
		seoRevisionsStore.fetch()
	})
}

/**
 * Process the content and then handle the editor change.
 *
 * @returns {void}.
 */
const processContent = async () => {
	const {
		FusionApp,
		fusionBuilderGetContent,
		FusionPageBuilderApp
	} = window

	// Convert builder to shortcodes.
	FusionPageBuilderApp?.builderToShortcodes()

	const rawContent      = FusionApp?.getPost('post_content') || fusionBuilderGetContent('content') || ''
	const postEditorStore = usePostEditorStore()

	postEditorStore.processContent({ content: rawContent }).finally(() => {
		handleEditorChange()
	})
}

/**
 * Enables the Avada save button.
 *
 * @returns {void}
 */
const enableSaveButton = () => {
	const { FusionApp } = window

	FusionApp?.set('hasChange', true)
}

/**
 * Initialize backend editor event listeners
 *
 * @returns {void}
 */
const initBackendListeners = () => {
	// Wait for FusionPageBuilderEvents to be available
	const checkForEvents = () => {
		if (window.FusionPageBuilderEvents && 'function' === typeof window.FusionPageBuilderEvents.on) {
			const backendEvents = [
				'fusion-element-added',
				'fusion-element-removed',
				'fusion-element-cloned',
				'fusion-element-sorted'
			]

			backendEvents.forEach(eventName => {
				window.FusionPageBuilderEvents.on(eventName, () => {
					debounce(processContent, 1000)
				})
			})

			window.FusionPageBuilderEvents.on('fusion-settings-modal-open', () => {
				debounce(processContent, 2000)
			})

			return true
		}
		return false
	}

	// Try immediately, then retry with intervals
	if (!checkForEvents()) {
		let attempts = 0
		const maxAttempts = 50 // 10 seconds max wait

		const interval = setInterval(() => {
			attempts++
			if (checkForEvents() || attempts >= maxAttempts) {
				clearInterval(interval)
			}
		}, 200) // Check every 200ms
	}
}

/**
 * Initialize frontend editor event listeners
 *
 * @returns {void}
 */
const initFrontendListeners = () => {
	const frontendChangeEvents = [
		'fusion-app-setup',
		'fusion-element-added',
		'fusion-element-edited',
		'fusion-element-removed',
		'fusion-element-cloned',
		'fusion-content-changed',
		'fusion-post_title-changed'
	].join(' ')

	window.FusionEvents.on(frontendChangeEvents, () => {
		debounce(processContent, 1000)
	})

	window.FusionEvents.on('fusion-app-saved', () => {
		debounce(handleEditorSave, 100)
	})

	window.FusionEvents.on('fusion-sidebar-toggled', (isOpen) => {
		emitter.emit('fusionSidebarToggled', isOpen)
	})
}

/**
 * Wait for builder to be ready and then initialize appropriate listeners
 *
 * @returns {void}
 */
const waitForBuilderReady = () => {
	const checkBuilderReady = () => {
		const { FusionApp, FusionPageBuilderApp, FusionEvents } = window

		// Check if any builder is active
		const hasActiveBuilder = (FusionApp?.builderActive) ||
							   (FusionPageBuilderApp?.builderActive) ||
							   (FusionPageBuilderApp && document.querySelector('#fusion_builder_main_container'))

		if (!hasActiveBuilder) {
			return false
		}

		// Determine which editor type and initialize accordingly
		if (FusionEvents && FusionApp?.builderActive) {
			initFrontendListeners()

			return true
		} else if (FusionPageBuilderApp) {
			initBackendListeners()

			return true
		}

		return false
	}

	if (!checkBuilderReady()) {
		window.jQuery(document).ready(() => {
			if (!checkBuilderReady()) {
				// Wait for window load as last resort
				window.jQuery(window).on('load', () => {
					setTimeout(checkBuilderReady, 500)
				})
			}
		})
	}
}

export default () => {
	// Initial content processing
	processContent()

	// Wait for builder to be ready before setting up event listeners
	waitForBuilderReady()

	// This hook will fire when the AIOSEO settings are updated.
	emitter.on('postSettingsUpdated', enableSaveButton)
}