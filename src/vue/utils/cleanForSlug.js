import { deburr, trim } from 'lodash-es'

/**
 * Performs some basic cleanup of a string for use as a post slug.
 * https://github.com/WordPress/gutenberg/blob/master/packages/url/src/clean-for-slug.js
 *
 * This replicates some of what `sanitize_title()` does in WordPress core, but
 * is only designed to approximate what the slug will be.
 *
 * Converts Latin-1 Supplement and Latin Extended-A letters to basic Latin
 * letters. Removes combining diacritical marks. Converts whitespace, periods,
 * and forward slashes to hyphens. Removes any remaining non-word characters
 * except hyphens. Converts remaining string to lowercase. It does not account
 * for octets, HTML entities, or other encoded characters.
 *
 * @param {string} string Title or slug to be processed.
 *
 * @returns {string} Processed string.
 */
export function cleanForSlug (string) {
	if (!string) {
		return ''
	}
	return trim(
		deburr(string)
			.replace(/[\s./]+/g, '-') // Convert each group of whitespace, periods, and forward slashes to a hyphen.
			.replace(/[^\w-]+/g, '')  // Remove additional non-word characters.
			.replace(/-+/g, '-')      // Replace multiple hyphens with a single hyphen.
			.toLowerCase(),
		'-'
	)
}