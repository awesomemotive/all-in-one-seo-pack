import {
	usePostEditorStore,
	useSeoRevisionsStore
} from '@/vue/stores'

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
	;[ '#vc_button-update', '#vc_button-save-draft', '#vc_button-save-as-pending' ].forEach(selector => {
		document.querySelector(selector)?.addEventListener('click', handleEditorSave)
	})
}