import { http, restUrl } from './functions'
import { stripMarkup } from '@/vue/utils/strings'
import { getPostEditedTitle } from '@/vue/utils/postData/postTitle'

let timeout
export const debounceContext = (fn, time) => {
	return ((...args) => {
		const functionCall = () => fn(...args)

		clearTimeout(timeout)
		timeout = setTimeout(functionCall, time)
	}).call()
}

// Fetch data from the API.
export const fetchData = async (newHeadline = null) => {
	let headline = newHeadline || getPostEditedTitle()

	// Trim headline from whitespaces.
	headline = stripMarkup((headline || '').trim())

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
			return { error: error.response.body?.message }
		})
}