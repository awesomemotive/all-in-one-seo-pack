import DOMPurify from 'dompurify'
import { usePostEditorStore } from '@/vue/stores'
import { debounce } from 'lodash-es'
import { getEditorData } from './helpers'
import { handleEditorSave, createEditorChangeHandler } from '@/vue/standalone/page-builders/helpers'
import { getText } from '@/vue/utils/html'
import { getImages } from '@/app/tru-seo/analyzer/analysis/contentHasAssets'

const processContent = async (editorElement = null) => {
	const postEditorStore = usePostEditorStore()
	const content = []

	try {
		if (editorElement?.querySelector('#breakdance-empty-container')) {
			postEditorStore.currentPost.processedContent = ''
			return
		}

		for (const element of editorElement?.children || []) {
			const html      = element.outerHTML.trim()
				.replaceAll(/<p.*>(<img.*>)<\/p>/g, '$1')
				.replaceAll(/<!--[\s\S]*?-->/g, '')
			const cleanHtml = DOMPurify.sanitize(html, {
				ALLOWED_ATTR       : [ 'href', 'src', 'alt' ],
				ALLOW_ARIA_ATTR    : false,
				ALLOW_DATA_ATTR    : false,
				ALLOW_UNKNOWN_TAGS : false,
				FORBID_TAGS        : [ 'script', 'style' ],
				FORBID_ATTR        : [ 'style', 'on*' ]
			})

			// Bail if there's no text or images.
			if ('' === getText(cleanHtml, true, 'innerText') && 0 === getImages(cleanHtml).length) {
				continue
			}

			content.push(cleanHtml)
		}
	} catch (error) {
		console.error(error)
	} finally {
		postEditorStore.currentPost.processedContent = content.join(' ')
	}
}

/**
 * Observes changes in the Oxygen Builder editor and triggers appropriate callbacks for handling editor changes and save actions.
 *
 * @returns {void}
 */
export default () => {
	const observeEditor = ($editor) => {
		const handleEditorChange = createEditorChangeHandler()

		const mutationCallback = debounce(async () => {
			await processContent($editor)
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
		const $editor = document.querySelector('#breakdance_canvas')
		if ($editor) {
			window.clearInterval(interval)

			observeEditor($editor)
		}
	}, 500)

	window.parent.document.addEventListener('breakdanceSaveButtonClicked', () => {
		debounce(handleEditorSave, 100)()
	})
}