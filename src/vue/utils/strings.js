import { decodeHTMLEntities } from '@/vue/utils/helpers'
import { isString } from 'lodash-es'

export const sanitizeString = (string) => {
	if (!isString(string)) {
		return ''
	}
	return stripTags(decodeHTMLEntities(string))
}

export const stripTags = (string) => {
	if (!isString(string)) {
		return ''
	}
	return string.replace(/(<([^>]+)>)/gi, '')
}

/**
 * Sanitizes a string by only removing HTML tags containing JS events.
 *
 * @since 4.4.6
 *
 * @param   {string} string The string to sanitize.
 * @returns {string}        Returns the sanitized string.
 */
export const softSanitizeHtml = (string) => {
	if ('string' !== typeof string) {
		return string
	}

	return string.replace(/(<|&lt;).*?\bon\w+=.*?(&gt;|>)/gmi, '')
}