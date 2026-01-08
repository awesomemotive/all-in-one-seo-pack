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
		handleEditorChange()
	})
}

export default ({ vc }) => {
	processContent()

	// Listen for changes to the front-end editor.
	vc.events.on('shortcodeView:updated', () => {
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