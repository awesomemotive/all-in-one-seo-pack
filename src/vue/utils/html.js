import {
	useRootStore
} from '@/vue/stores'

import {
	sanitizeString,
	softSanitizeHtml
} from '@/vue/utils/strings'

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
	div.innerHTML = softSanitizeHtml(html.trim())
	return div
}

/**
 * Get the text from an HTML piece.
 *
 * @since   4.2.1
 * @version 4.6.5 Added the `removeBreaks` and `prop` parameters.
 *
 * @param 	{string|Object} html 		 The HTML to retrieve text.
 * @param 	{boolean} 	  	removeBreaks Whether to remove line breaks.
 * @param 	{string} 		prop 		 The property to get the text from.
 * @returns {string}           		     The text.
 */
export const getText = (html, removeBreaks = true, prop = 'textContent') => {
	if ('string' === typeof html) {
		if (removeBreaks) {
			html = html.replace(/[\t|\n]/gm, '')
		}
		html = createElement(html)
	}

	// Return the text inside the HTML.
	return html[prop] || html.textContent || html.innerText || ''
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
 * Truncate a given string.
 *
 * @since 4.2.8
 *
 * @param {string} string The text.
 * @param {number} length Text max character length.
 * @param {string} ellipsis The ellipsis to use.
 * @returns {string} The shortened string.
 */
export const truncate = (string, length = 200, ellipsis = '&hellip;') => {
	if (!string) {
		return string
	}

	if (length < string.length) {
		string = string.substring(0, length).trim() + sanitizeString(ellipsis)
	}

	return string
}

/**
 * Get the schema graph from a string.
 *
 * @since 4.5.6
 *
 * @param   {string}      type          The schema graph to get.
 * @param   {string}      schemaContent The entire schema.
 * @returns {Object|false}              The schema graph object, or false if not found.
 */
export const parseSchemaByType = (type, schemaContent) => {
	try {
		const parseSchema = JSON.parse(schemaContent)
		if (!parseSchema) {
			return false
		}

		if (
			parseSchema['@type'] &&
			(
				type === parseSchema['@type'] ||
				(Array.isArray(parseSchema['@type']) && parseSchema['@type'].includes(type))
			)
		) {
			return parseSchema
		}

		if (Array.isArray(parseSchema['@graph'])) {
			const foundItem = parseSchema['@graph']
				.find(item => (Array.isArray(item['@type']) ? item['@type'].includes(type) : item['@type'] === type))

			return foundItem || false
		}

		return false
	} catch (_e) {
		return false
	}
}

/**
 * Extract the Google SERP snippet data from DOM.
 *
 * @since 4.2.8
 *
 * @returns {{url: string, title: string, description: string}} The data.
 */
export const getDomGoogleSerpData = () => {
	return {
		url         : window.location.href,
		title       : document.title || '',
		description : document.querySelector('meta[name="description"]')?.content || ''
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
		description : document.querySelector('meta[property="og:description"]')?.content || '',
		image       : document.querySelector('meta[property="og:image"]')?.content || '',
		title       : document.querySelector('meta[property="og:title"]')?.content || '',
		type        : document.querySelector('meta[property="og:type"]')?.content || ''
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
 * @link https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
 *
 * @since 4.2.8
 *
 * @returns {{image: string, description: string, title: string, card: string}} The data.
 */
export const getTwitterSnippetData = () => {
	return {
		card        : document.querySelector('meta[name="twitter:card"]')?.content || '',
		description : document.querySelector('meta[name="twitter:description"]')?.content || '',
		image       : document.querySelector('meta[name="twitter:image"]')?.content || '',
		title       : document.querySelector('meta[name="twitter:title"]')?.content || ''
	}
}