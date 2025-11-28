/* global TVE */
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

	// Clear isDirty flag as soon as save is initiated.
	postEditorStore.isDirty = false

	postEditorStore.saveCurrentPost(postEditorStore.currentPost).then(() => {
		const seoRevisionsStore = useSeoRevisionsStore()
		seoRevisionsStore.fetch()
	})
}

export default () => {
	TVE.add_action('tcb-ready', handleEditorChange)

	;[
		'tcb.after-insert',
		'tcb.element.remove',
		'tcb.element.duplicate',
		'tcb.froala.focus',
		'tcb.froala.blur',
		'tcb.image.change',
		'after_undo_redo'
	].forEach(action => {
		TVE.add_action(action, () => {
			debounce(handleEditorChange, 200)
		})
	})

	TVE.add_action('tve.save_post.success', () => {
		debounce(handleEditorSave, 100)
	})
}