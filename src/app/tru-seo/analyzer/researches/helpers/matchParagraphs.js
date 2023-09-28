/**
 * External dependencies
 */
import { flow, map } from 'lodash-es'

/**
 * WordPress dependencies
 */
import { autop } from '@wordpress/autop'

/**
 * Internal dependencies
 */
import { cleanText } from './cleanText'
import stripShortcodes from './stripShortcodes'
import stripHTMLComments from './stripHTMLComments'

/**
 * Matches the paragraphs in <p>-tags and returns the text in them.
 *
 * @param {string} text The text to match paragraph in.
 * @param {boolean} stripTags Should strip html within paragraphs.
 *
 * @returns {Array} An array containing all paragraphs texts.
 */
const getParagraphsInTags = (text, stripTags) => {
	// Matches everything between the <p> and </p> tags.
	const regex = /<p(?:[^>]+)?>(.*?)<\/p>/ig
	const paragraphs = []

	let match
	while (null !== (match = regex.exec(text))) {
		paragraphs.push(match)
	}

	// Returns only the text from within the paragraph tags.
	return map(paragraphs, (paragraph) => stripTags ? cleanText(paragraph[1]) : paragraph[1])
}

/**
 * Returns an array with all paragraphs from the text.
 *
 * @param {string} text The text to match paragraph in.
 * @param {boolean} stripTags Should strip html within paragraphs.
 *
 * @returns {Array} The array containing all paragraphs from the text.
 */
export default (text, stripTags = false) => {
	text = flow(
		[
			stripShortcodes,
			stripHTMLComments,
			autop
		]
	)(text)
	stripTags = stripTags || false

	return getParagraphsInTags(text, stripTags)
}