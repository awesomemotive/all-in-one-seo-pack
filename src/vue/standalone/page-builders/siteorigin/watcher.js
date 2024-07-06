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

export default (builderView) => {
	handleEditorChange()

	builderView.on('content_change', () => {
		debounce(handleEditorChange, 1000)
	})

	builderView.$(document).on('ajaxComplete', function (_event, _xhr, settings) {
		const params = new URLSearchParams(settings.data)
		if ('so_panels_builder_content_json' !== params.get('action')) {
			return
		}

		debounce(handleEditorChange, 1000)
	})
}