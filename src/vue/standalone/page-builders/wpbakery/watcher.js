import {
	usePostEditorStore
} from '@/vue/stores'

import { isEqual } from 'lodash-es'
import { debounce } from '@/vue/utils/debounce'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { getEditorData, getSaveButtons } from './helpers'
import { handleEditorSave } from '@/vue/standalone/page-builders/helpers'

let editorData = {}

/**
 * Run TruSEO analysis when content updates.
 *
 * @param   {boolean} notifyContentChanging Whether to emit the content-changing event.
 * @returns {void}.
 */
const handleEditorChange = (notifyContentChanging = true) => {
	const oldData = { ...editorData }
	const data = getEditorData()

	if (!isEqual(oldData, data)) {
		editorData = data
		updatePostData(900, true, notifyContentChanging)
	}
}

/**
 * Hides stale highlight UI at the edit itself.
 * NOTE: the hide can't ride the delayed content read — it lands after the spelling popover has
 * already advanced to the next issue, and closes it.
 *
 * @returns {void}.
 */
const handleEditorModified = () => {
	window.aioseoBus.$emit('aioseo-content-changing')
}

/**
 * Gets the raw content from the editor.
 *
 * @returns {string} The raw content.
 */
const getRawContent = () => {
	const {
		vc,
		vc_mode : mode
	} = window

	if ('admin_frontend_editor' === mode) {
		return vc.builder.getContent()
	}

	return document.querySelector('#content')?.value || ''
}

/**
 * Process the content and then handle the editor change.
 *
 * @returns {void}.
 */
const processContent = async () => {
	const postEditorStore = usePostEditorStore()
	postEditorStore.processContent({ content: getRawContent() }).finally(() => {
		handleEditorChange(false)
	})
}

export default ({ vc }) => {
	processContent()

	// The edit moment in both editors; the frontend's `shortcodeView:updated` only follows the re-render.
	vc.shortcodes.on('change:params', handleEditorModified)
	vc.events.on('shortcodes:add shortcodes:destroy', handleEditorModified)

	// Listen for changes to the front-end editor.
	vc.events.on('shortcodeView:updated', () => {
		debounce(processContent, 1000)
	})

	// Adding or deleting an element fires neither event above, which left media stale.
	vc.events.on('shortcodes:add shortcodes:destroy', () => {
		debounce(processContent, 1000)
	})

	// Listen for changes to the back-end editor.
	vc.shortcodes.on('sync', () => {
		debounce(processContent, 1000)
	})

	// Listen for the editor being saved.
	getSaveButtons().forEach($button => {
		$button.addEventListener('click', () => {
			setTimeout(handleEditorSave)
		})
	})
}