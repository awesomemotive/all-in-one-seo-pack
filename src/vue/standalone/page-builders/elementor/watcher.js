import emitter from 'tiny-emitter/instance'
import { isEqual } from 'lodash-es'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { registerElementorUIHookAfter, registerElementorDataHookAfter } from './hooks'
import { getEditorData } from './helpers'
import { handleEditorSave } from '@/vue/standalone/page-builders/helpers'

let editorData = {},
	inited = false

/**
 * Run TruSEO analysis when content updates.
 *
 * @returns {void}.
 */
const handleEditorChange = () => {
	const currentDocument = window.elementor.documents.getCurrent()

	/*
	Quit early if the change was caused by switching out of the wp-post/page document.
	This can happen when users go to Site Settings, for example.
	*/
	if (![ 'wp-post', 'wp-page' ].includes(currentDocument.config.type)) {
		return
	}

	const oldData = { ...editorData }
	const data = getEditorData()

	if (!isEqual(oldData, data)) {
		editorData = data
		updatePostData()
	}
}

/**
 * Enables the Elementor save button.
 *
 * @returns {void}
 */
const enableSaveButton = () => {
	window.$e.internal('document/save/set-is-modified', { status: true })
}

/**
 * Init the Elementor watcher.
 *
 * @returns {void}.
 */
export default () => {
	if (inited) {
		return
	}
	inited = true

	// This hook will fire when the Elementor preview becomes available.
	registerElementorUIHookAfter('editor/documents/attach-preview', 'aioseo-content-scraper-attach-preview', handleEditorChange)

	// This hook will fire when the contents of the editor are modified.
	registerElementorUIHookAfter('document/save/set-is-modified', 'aioseo-content-scraper-on-modified', handleEditorChange)

	// This hook will fire when the Update button is triggered.
	registerElementorDataHookAfter('document/save/save', 'aioseo-save', () => {
		handleEditorSave('elementor')
	})

	// This hook will fire when the AIOSEO settings are updated.
	emitter.on('postSettingsUpdated', enableSaveButton)
}