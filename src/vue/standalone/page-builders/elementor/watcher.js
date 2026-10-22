import emitter from 'tiny-emitter/instance'
import { debounce, isEqual } from 'lodash-es'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { registerElementorUIHookAfter, registerElementorDataHookAfter } from './hooks'
import { getEditorData, isAioseoDocument } from './helpers'
import { handleEditorSave } from '@/vue/standalone/page-builders/helpers'

let editorData = {},
	inited = false

/**
 * Run TruSEO analysis when content updates.
 *
 * @param   {boolean} notifyContentChanging Whether to emit the content-changing event.
 * @returns {void}.
 */
const handleEditorChange = (notifyContentChanging = true) => {
	// The change can come from another document, e.g. when users go to Site Settings.
	if (!isAioseoDocument()) {
		return
	}

	const oldData = { ...editorData }
	const data = getEditorData()

	// The first read establishes the baseline; it is not a change.
	if (!Object.keys(editorData).length) {
		editorData = data

		return
	}

	if (isEqual(oldData, data)) {
		return
	}

	editorData = data
	updatePostData(900, true, notifyContentChanging)
}

// Reading the content walks every element, so a burst of edits is read once, after it settles.
const handleEditorChangeDebounced = debounce(() => handleEditorChange(false), 400)

/**
 * Hides stale highlight UI at the edit itself, then reads the content once the burst settles.
 *
 * NOTE: the hide can't ride the debounced read — 400 ms later it lands after the spelling popover
 * has already advanced to the next issue, and closes it.
 *
 * @returns {void}.
 */
const handleEditorModified = () => {
	if (isAioseoDocument()) {
		window.aioseoBus.$emit('aioseo-content-changing')
	}

	handleEditorChangeDebounced()
}

/**
 * Records the snapshot a newly attached document is diffed against.
 *
 * NOTE: never analyzes. On a document switch the store still holds the previous post's data until
 * main.js:analyzeCurrentDocument() has synced it, and that call runs the analysis itself.
 *
 * @returns {void}.
 */
const handleAttachPreview = () => {
	if (isAioseoDocument()) {
		editorData = getEditorData()
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
	registerElementorUIHookAfter('editor/documents/attach-preview', 'aioseo-content-scraper-attach-preview', handleAttachPreview)

	// This hook will fire when the contents of the editor are modified.
	registerElementorUIHookAfter('document/save/set-is-modified', 'aioseo-content-scraper-on-modified', handleEditorModified)

	// This hook will fire when the Update button is triggered.
	registerElementorDataHookAfter('document/save/save', 'aioseo-save', () => {
		handleEditorSave('elementor')
	})

	// This hook will fire when the AIOSEO settings are updated.
	emitter.on('postSettingsUpdated', enableSaveButton)
}