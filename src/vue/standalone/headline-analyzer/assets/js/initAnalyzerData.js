import { http, restUrl } from './functions'
import { sanitizeString } from '@/vue/utils/strings'
import { HeadlineCurrentScore } from './HeadlineCurrentScore'
import {
	usePostEditorStore
} from '@/vue/stores'
const { select } = window.wp.data

let timeout, postTitle = select('core/editor').getEditedPostAttribute('title')
export const debounceContext = (fn, time) => {
	return ((...args) => {
		const functionCall = () => fn(...args)

		clearTimeout(timeout)
		timeout = setTimeout(functionCall, time)
	}).call()
}

// Fetch data from the API.
export const fetchData = async (newHeadline = null) => {
	let headline = postTitle
	if (newHeadline) {
		headline = newHeadline
	}

	// Trim headline from whitespaces.
	headline = sanitizeString(headline.trim())

	if (!headline) {
		return null
	}

	return http(window.aioseo.nonce).post(restUrl('analyze-headline'))
		.send({
			headline            : headline,
			shouldStoreHeadline : false
		})
		.then(response => {
			return {
				data     : response.body,
				headline : headline
			}
		})
		.catch(error => {
			console.error('Couldn\'t fetch score for headline:', error)
			return null
		})
}

// Fetch new results after the title was changed.
window.wp.data.subscribe(() => {
	// If the title is the same, abort.
	if (postTitle === select('core/editor').getEditedPostAttribute('title')) {
		return
	}
	postTitle = select('core/editor').getEditedPostAttribute('title')

	debounceContext(() => {
		const postEditorStore = usePostEditorStore()
		if (postEditorStore.currentPost?.headlineAnalyzer?.newData) {
			postEditorStore.toggleShowNewHeadlineAnalyzerPreview(false)
		}
		if (postEditorStore.currentPost?.headlineAnalyzer?.showNewData) {
			postEditorStore.toggleShowNewHeadlineAnalyzerData(false)
		}
		HeadlineCurrentScore(true)
	}, 2000)
})