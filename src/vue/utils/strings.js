import DOMPurify from 'dompurify'
import { decode } from 'he'
import { isString } from 'lodash-es'

function decodeHtml (string) {
	while (string !== decode(string)) {
		string = decode(string)
	}
	return string
}

/**
 * Sanitizes a string by stripping out everything that contains dangerous HTML to prevent XSS attacks.
 * In contrary to the `softSanitizeHtml` function, we strip the HTML tags completely.
 *
 * @since   ?
 * @version 4.8.2 Refactored to use DOMPurify.
 *
 * @param   {string} string The string to sanitize.
 * @returns {string}        Returns the sanitized string.
 */
export const sanitizeString = (string) => {
	if (!isString(string)) {
		return ''
	}

	// Decode HTML entities to prevent XSS attacks.
	string = decodeHtml(string)

	return DOMPurify.sanitize(string, {
		ALLOWED_TAGS      : [],
		ALLOWED_ATTR      : [],
		ALLOWED_URI_REGEX : []
	})
}

/**
 * Sanitizes a string by stripping out everything that contains dangerous HTML to prevent XSS attacks.
 * In contrary to the `sanitizeString` function, we do not strip the HTML tags completely.
 *
 * @since   4.4.6
 * @version 4.8.2 Refactored to use DOMPurify.
 *
 * @param   {string} string The string to sanitize.
 * @returns {string}        Returns the sanitized string.
 */
export const softSanitizeHtml = (string) => {
	if (!isString(string)) {
		return ''
	}

	return DOMPurify.sanitize(decodeHtml(string))
}

/**
 * Sanitizes a URL by ensuring it is valid and only allows http, https protocols or relative URLs.
 *
 * @param   {string} url The URL to sanitize.
 * @returns {string}     Returns the sanitized URL.
 */
export const sanitizeUrl = (url) => {
	if (!url) {
		return '#'
	}

	// If relative URL, return it as is
	if (url.startsWith('#') || url.startsWith('/')) {
		return sanitizeString(url)
	}

	try {
		const urlInstance = new URL(url)
		if (urlInstance.protocol && ![ 'http:', 'https:', 'mailto:', 'tel:' ].includes(urlInstance.protocol)) {
			return '#'
		}

		return sanitizeString(urlInstance.toString())
	} catch (e) {
		return '#'
	}
}