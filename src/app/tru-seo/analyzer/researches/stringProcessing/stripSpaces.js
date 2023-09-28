/** @module stringProcessing/stripSpaces */

/**
 * Strip double spaces from text
 *
 * @param {string} text The text to strip spaces from.
 * @returns {string} The text without double spaces
 */
export default function (text) {
	// Replace multiple spaces with single space.
	text = text.replace(/\s{2,}/g, ' ')

	// Replace spaces followed by periods with only the period.
	text = text.replace(/\s\./g, '.')

	return text.trim()
}