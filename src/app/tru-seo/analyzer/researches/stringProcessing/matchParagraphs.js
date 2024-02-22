import { flatMap, filter } from 'lodash-es'

import { getBlocks } from '../helpers/html'

/**
 * Matches the paragraphs in <p>-tags and returns the text in them.
 * @param {string} text The text to match paragraph in.
 * @returns {Array} An array containing all paragraphs texts.
 */
const getParagraphsInTags = function (text) {
	const paragraphs = []
	// Get paragraph content by matching everything between <p> tags.
	// We'll also want to consider shortcodes content while looking for keyphrases.
	const regex = /<p(?:[^>]+)?>(.+?)<\/p>|\[.+?](.+?)\[\/.+?]/ig

	let match
	while (null !== (match = regex.exec(text))) {
		// Only look for the shortcode content if the <p> tag content is empty.
		const found = match[1] || match[2] || ''
		if (found) {
			paragraphs.push(found)
		}
	}

	return paragraphs
}

/**
 * Returns an array with all paragraphs from the text.
 * @param {string} text The text to match paragraph in.
 * @returns {Array} The array containing all paragraphs from the text.
 */
export default function (text) {
	let paragraphs = getParagraphsInTags(text),
		// If no <p> tags found, split on double linebreaks.
		blocks = getBlocks(text)

	if (0 < paragraphs.length) {
		return paragraphs
	}

	blocks = filter(blocks, function (block) {
		// Match explicit paragraph tags, or if a block has no HTML tags.
		return 0 !== block.indexOf('<h')
	})

	paragraphs = flatMap(blocks, function (block) {
		return block.split('\n\n')
	})

	if (0 < paragraphs.length) {
		return paragraphs
	}

	// If no paragraphs are found, return an array containing the entire text.
	return [ text ]
}