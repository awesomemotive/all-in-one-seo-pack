/* global TVE */
import { isEqual } from 'lodash-es'
import { debounce } from '@/vue/utils/debounce'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { getEditorData } from './helpers'
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