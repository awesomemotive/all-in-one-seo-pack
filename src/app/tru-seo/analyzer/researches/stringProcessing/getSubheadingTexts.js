import countWords from './countWords'

/**
 * Returns all texts per subheading.
 *
 * @param 	{string} string The text to extract from.
 * @returns {Array} 	  	An array of objects containing subheading data.
 */
export default function (string) {
	string = string.trim().replace(/^<!--[^<]+/, '')

	const regex = /<h([1-6])(?:[^>]+)?>(.+?)<\/h\1>/gi
	const subheadings = []
	const subheadingTexts = []

	let match
	while (null !== (match = regex.exec(string))) {
		subheadings.push({
			subheading : match[2],
			startIndex : match.index,
			endIndex   : match.index + match[0].length
		})
	}

	for (const [ i, v ] of subheadings.entries()) {
		// Append an empty subheading if the first found subheading is below some content.
		if (0 === i && 0 < v.startIndex) {
			subheadingTexts.unshift({
				text       : string.substring(0, v.startIndex),
				wordCount  : countWords(string.substring(0, v.startIndex)),
				subheading : ''
			})
		}

		const subheadingText = subheadings[i + 1]
			? string.substring(v.endIndex, subheadings[i + 1].startIndex)
			: string.substring(v.endIndex)

		subheadingTexts.push({
			text       : subheadingText,
			wordCount  : countWords(subheadingText),
			subheading : v.subheading
		})
	}

	return subheadingTexts
}