import getParagraphs from '../researches/helpers/getParagraphs'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	incorrect     : 0,
	correctLength : 5
}

function paragraphLength (content) {
	if (!content) {
		return {}
	}

	const highlightSentences = []
	const paragraphs = getParagraphs(content)
	const bigParagraphs = paragraphs.filter(p => 120 < p.wordCount)

	if (bigParagraphs.length) {
		highlightSentences.push(bigParagraphs.map(bp => bp.text))

		return {
			title              : __('Paragraphs length', td),
			description        : __('At least one paragraph is long. Consider using short paragraphs.', td),
			score              : scores.incorrect,
			maxScore           : scores.correctLength,
			error              : 1,
			highlightSentences : highlightSentences
		}
	}

	return {
		title              : __('Paragraphs length', td),
		description        : __('You are using short paragraphs.', td),
		score              : scores.correctLength,
		maxScore           : scores.correctLength,
		error              : 0,
		highlightSentences : highlightSentences
	}
}

export default paragraphLength