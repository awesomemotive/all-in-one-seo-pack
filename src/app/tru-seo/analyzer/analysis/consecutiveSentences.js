import { partition } from 'lodash-es'
import getSentenceBeginnings from '../researches/helpers/getSentenceBeginnings'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

function parseSentenceBeginnings (sentenceBeginnings) {
	const [ tooOften ] = partition(sentenceBeginnings, word => 2 < word.count)
	if (0 === tooOften.length) {
		return { total: 0 }
	}

	return {
		total           : tooOften.length,
		lowestCount     : Math.min(...tooOften.map(word => word.count)),
		sentenceResults : tooOften.map(s => s.sentences)
	}
}

export default function consecutiveSentences (content, locale) {
	if (!content) {
		return {}
	}

	const highlightSentences = []
	const sentenceBeginnings = parseSentenceBeginnings(getSentenceBeginnings(content, locale))
	if (0 < sentenceBeginnings.total) {
		if (Array.isArray(sentenceBeginnings.sentenceResults) && sentenceBeginnings.sentenceResults.length) {
			highlightSentences.push(...sentenceBeginnings.sentenceResults)
		}

		return {
			title       : __('Consecutive sentences', td),
			description : sprintf(
				// Translators: 1 - Number of sentences.
				__('The text contains at least %1$d consecutive sentences starting with the same word. Try to mix things up!', td),
				sentenceBeginnings.lowestCount
			),
			score              : 3,
			maxScore           : 9,
			error              : 1,
			highlightSentences : highlightSentences
		}
	}

	return {
		title              : __('Consecutive sentences', td),
		description        : __('There is enough variety in your sentences. That\'s great!', td),
		score              : 9,
		maxScore           : 9,
		error              : 0,
		highlightSentences : highlightSentences
	}
}