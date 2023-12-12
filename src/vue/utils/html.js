import {
	useRootStore
} from '@/vue/stores'

import { decodeHTMLEntities } from '@/vue/utils/helpers'

/**
 * Create an HTML element from a string.
 *
 * @since 4.2.1
 *
 * @param {string} html    The HTML string.
 * @param {string} element The wrapper element.
 * @returns {Element}      The element.
 */
export const createElement = (html, element = 'div') => {
	const div = document.createElement(element)
	div.innerHTML = html.trim()
	return div
}

/**
 * Get the text from an HTML piece.
 *
 * @since 4.2.1
 *
 * @param {string|Object} html The HTML to retrieve text.
 * @returns {string}           The text.
 */
export const getText = (html) => {
	if ('string' === typeof html) {
		html = createElement(html.replace(/[\t|\n]/gm, ''))
	}

	// Return the text inside the HTML.
	return html.textContent || html.innerText || ''
}

/**
 * Retrieves the `outerText` from a string or node.
 *
 * @since 4.4.6
 *
 * @param   {string|HTMLElement} html The string or node to retrieve the `outerText` property from.
 * @returns {string}                  The text.
 */
export const getOuterText = (html) => {
	if ('string' === typeof html) {
		html = createElement(html)
	}

	return html?.outerText ?? ''
}

/**
 * Get the image list from an HTML piece.
 *
 * @since 4.2.1
 *
 * @param {string|Object} html The HTML to retrieve images.
 * @returns {NodeList}         The image list.
 */
export const getImages = (html) => {
	if ('string' === typeof html) {
		html = createElement(html)
	}

	return html.querySelectorAll('img')
}

/**
 * Truncate a given string.
 *
 * @since 4.2.8
 *
 * @param {string} string The text.
 * @param {number} length Text max character length.
 * @returns {string}      The shortened string.
 */
export const truncate = (string, length = 200) => {
	if (!string) {
		return string
	}

	if (length < string.length) {
		string = string.substring(0, length).trim() + decodeHTMLEntities('&hellip;')
	}

	return string
}

/**
 * Extract the Google SERP snippet data from DOM.
 *
 * @since 4.2.8
 *
 * @returns {{domain: string, description: string, title: string}} The data.
 */
export const getGoogleSnippetData = () => {
	return {
		description : document.head.querySelector('meta[name="description"]')?.content || '',
		domain      : window.location.origin,
		title       : document.title || ''
	}
}

/**
 * Extract the Facebook link preview data from DOM.
 *
 * @link https://ogp.me/
 * @link https://developers.facebook.com/tools/debug/
 *
 * @since 4.2.8
 *
 * @returns {{image: string, description: string, title: string, type: string}} The data.
 */
export const getFacebookSnippetData = () => {
	const facebookData = {
		description : document.head.querySelector('meta[property="og:description"]')?.content || '',
		image       : document.head.querySelector('meta[property="og:image"]')?.content || '',
		title       : document.head.querySelector('meta[property="og:title"]')?.content || '',
		type        : document.head.querySelector('meta[property="og:type"]')?.content || ''
	}

	if (!facebookData.title) {
		facebookData.title = document.title || ''

		if (!facebookData.title) {
			const rootStore = useRootStore()
			facebookData.title = rootStore.aioseo.urls.domain
		}
	}

	return facebookData
}

/**
 * Extract the Twitter card data from DOM.
 *
 * @link https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
 *
 * @since 4.2.8
 *
 * @returns {{image: string, description: string, title: string, card: string}} The data.
 */
export const getTwitterSnippetData = () => {
	return {
		card        : document.head.querySelector('meta[name="twitter:card"]')?.content || '',
		description : document.head.querySelector('meta[name="twitter:description"]')?.content || '',
		image       : document.head.querySelector('meta[name="twitter:image"]')?.content || '',
		title       : document.head.querySelector('meta[name="twitter:title"]')?.content || ''
	}
}