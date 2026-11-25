/** @module stringProcessing/checkNofollow */

// We use an external library, which can be found here: https://github.com/fb55/htmlparser2.
import { Parser } from 'htmlparser2'

/**
 * The `rel` values that stop a link from passing link value or anchor text signal to its target.
 * @type {string[]}
 */
export const NOFOLLOW_REL_VALUES = [ 'nofollow', 'sponsored', 'ugc' ]

/**
 * Checks whether a `rel` attribute value contains any of the nofollow values.
 *
 * @param {string} rel The `rel` attribute value.
 * @returns {boolean} Whether the link is nofollow.
 */
export function isNofollowRel (rel) {
	if (!rel) {
		return false
	}

	// Split on commas too: the spec says space-separated, but themes and link plugins emit both.
	return rel.toLowerCase().split(/[\s,]+/).some(value => NOFOLLOW_REL_VALUES.includes(value))
}

/**
 * Checks if a link has a `rel` attribute with a nofollow value. If it has, returns Nofollow, otherwise Dofollow.
 *
 * @param {string} anchorHTML The anchor HTML to check against.
 * @returns {string} Returns Dofollow or Nofollow.
 */
export default function (anchorHTML) {
	let linkFollow = 'Dofollow'

	const parser = new Parser({
		/**
		 * Detects if there is a nofollow value in the `rel` attribute of a link.
		 *
		 * @param {string} tagName The tag name.
		 * @param {Object} attributes The tag attributes with the names and values of each attribute found.
		 * @returns {void}
		 */
		onopentag : function (tagName, attributes) {
			if ('a' === tagName && isNofollowRel(attributes.rel)) {
				linkFollow = 'Nofollow'
			}
		}
	})

	parser.write(anchorHTML)
	parser.end()

	return linkFollow
}