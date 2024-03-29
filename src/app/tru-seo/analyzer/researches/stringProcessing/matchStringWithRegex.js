/** @module stringProcessing/matchStringWithRegex */

/**
 * Checks a string with a regex, return all matches found with that regex.
 *
 * @param {string} text The text to match the
 * @param {string} regexString A string to use as regex.
 * @returns {Array} Array with matches, empty array if no matches found.
 */
export default function (text, regexString) {
	const regex = new RegExp(regexString, 'ig')
	let matches = text.match(regex)

	if (null === matches) {
		matches = []
	}

	return matches
}