import { usePostEditorStore } from '@/vue/stores'
import { debounce } from 'lodash-es'
import { getEditorData, getBricksVueGlobalProperties } from './helpers'
import { handleEditorSave, createEditorChangeHandler, iframeRef } from '@/vue/standalone/page-builders/helpers'

const processContent = async () => {
	try {
		const $vueState = getBricksVueGlobalProperties('$_state')
		const content = $vueState?.content || ''
		const postEditorStore = usePostEditorStore()

		await postEditorStore.processContent({ content: content, integration: 'bricks' })
	} catch (error) {
		console.error(error)
	}
}

/**
 * Observes changes in the Bricks Builder editor and triggers appropriate callbacks for handling editor changes and save actions.
 *
 * @param {Window} window - The window object.
 * @param {Window} window.jQuery - The jQuery instance.
 * @returns {void}
 */
export default ({ jQuery: $ }) => {
	const observeEditor = ($editor) => {
		const handleEditorChange = createEditorChangeHandler()

		const mutationCallback = debounce(async () => {
			await processContent()
			await handleEditorChange({ editorData: getEditorData() })
		}, 1000)

		const observer = new MutationObserver(mutationCallback)

		observer.observe($editor, {
			subtree    : true,
			childList  : true,
			attributes : false
		})

		// Call immediately once to process initial content.
		mutationCallback()
	}

	const interval = window.setInterval(() => {
		const $editor = iframeRef('bricks-builder-iframe')?.querySelector('#brx-content')
		if ($editor) {
			window.clearInterval(interval)

			observeEditor($editor)
		}
	}, 500)

	$(document).ajaxComplete((_event, _request, settings) => {
		if (settings?.data?.includes('action=bricks_save_post')) {
			debounce(handleEditorSave, 100)()
		}
	})
}