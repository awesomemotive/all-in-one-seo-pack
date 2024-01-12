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
 * Save SEO Settings when WP Bakery editor is saved.
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

export default () => {
	if (!(window.FusionApp || window.FusionPageBuilderApp)?.builderActive) {
		return
	}

	processContent()

	const fusionEvents = window.FusionEvents || window.FusionPageBuilderEvents || {}
	const changeEvents = [
		'fusion-app-setup', // Triggers on first load.
		'fusion-history-save-step',
		'fusion-element-added',
		'fusion-element-removed',
		'fusion-element-cloned',
		'fusion-content-changed',
		'fusion-post_title-changed'
	].join(' ')

	fusionEvents.on(changeEvents, () => {
		debounce(processContent, 1000)
	})

	fusionEvents.on('fusion-app-saved', () => {
		debounce(handleEditorSave, 100)
	})

	fusionEvents.on('fusion-sidebar-toggled', (isOpen) => {
		emitter.emit('fusionSidebarToggled', isOpen)
	})

	// This hook will fire when the AIOSEO settings are updated.
	emitter.on('postSettingsUpdated', enableSaveButton)
}