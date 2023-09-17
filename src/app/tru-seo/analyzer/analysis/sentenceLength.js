import checkTooLongSentences from '../researches/stringProcessing/checkForTooLongSentences'
import countSentencesFromText from '../researches/helpers/countSentencesFromText'
import formatNumber from '../researches/helpers/formatNumber'
import { __, sprintf } from '@wordpress/i18n'
import { td } from '@/vue/plugins/constants'

const parameters = {
	recommendedWordCount : 20,
	slightlyTooMany      : 25,
	farTooMany           : 30
}
const scores = {
	incorrect       : 3,
	slightlyCorrect : 6,
	correctLength   : 9
}

function getTooLongSentencesData (sentences) {
	let longSentences = [],
		percentage = 0

	if (0 !== sentences.length) {
		longSentences = getTooLongSentences(sentences)
		percentage = formatNumber((longSentences.length / sentences.length) * 100)
	}

	return { longSentences, percentage }
}

function getTooLongSentences (sentences) {
	return checkTooLongSentences(sentences, parameters.recommendedWordCount)
}

function sentenceLength (content) {
	if (!content) {
		return {}
	}

	const sentenceCount = countSentencesFromText(content)
	const longSentencesData = getTooLongSentencesData(sentenceCount)
	const highlightSentences = []

	if (longSentencesData.percentage <= parameters.slightlyTooMany) {
		return {
			title              : __('Sentences length', td),
			description        : __('Sentence length is looking great!', td),
			score              : scores.correctLength,
			maxScore           : scores.correctLength,
			error              : 0,
			highlightSentences : highlightSentences
		}
	}

	return {
		title       : __('Sentences length', td),
		description : sprintf(
			// Translators: 1 - Number of the sentences, 2 - Number of words, 3 - Recommended maximum of words.
			__('%1$s of the sentences contain more than %2$s words, which is more than the recommended maximum of %3$s. Try to shorten the sentences.', td),
			`${longSentencesData.percentage}%`,
			parameters.recommendedWordCount,
			`${parameters.slightlyTooMany}%`
		),
		score              : scores.slightlyCorrect,
		maxScore           : scores.correctLength,
		error              : 1,
		highlightSentences : longSentencesData.longSentences.map(ls => ls.sentence)
	}
}

export default sentenceLength