import { isEmpty } from 'lodash-es'
import countWords from './countWords'

/**
 * Returns all texts per subheading.
 *
 * @param 	{string} text The text to extract from.
 * @returns {Array} 	  An array of objects containing subheading data.
 */
export default function (text) {
	const subheadings = []

	/*
	 Matching this in a regex is pretty hard, since we need to find a way for matching the text after a heading, and before the end of the text.
	 The hard thing capturing this is with a capture, it captures the next subheading as well, so it skips the next part of the text,
	 since the subheading is already matched.
	 For now we use this method to be sure we capture the right blocks of text. We remove all | 's from text,
	 then replace all headings with a | and split on a |.
	 */
	text = text.replace(/\|/ig, '')
	text = text.replace(/<h([1-6])(?:[^>]+)?>(.+?)<\/h\1>/ig, (_match, _p1, p2) => {
		subheadings.push(p2)

		return '|'
	})

	const subheadingTexts = text.split('|')
	if (isEmpty(subheadingTexts[0])) {
		subheadingTexts.shift()
	}

	return subheadingTexts.map((value, i) => {
		return {
			subheading : 0 === i ? '' : subheadings[i - 1],
			text       : value,
			wordCount  : countWords(value)
		}
	})
}