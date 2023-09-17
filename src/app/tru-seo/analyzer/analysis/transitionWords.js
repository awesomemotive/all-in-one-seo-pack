import { count } from '@/vue/plugins/wordcount'
import { inRange } from 'lodash-es'
import findTransitionWords from '../researches/findTransitionWords'
import formatNumber from '../researches/helpers/formatNumber'
import { __, sprintf } from '@wordpress/i18n'
import { td } from '@/vue/plugins/constants'

function calculateTransitionWordPercentage (sentences) {
	if (0 === sentences.transitionWordSentences || 0 === sentences.totalSentences) {
		return 0
	}

	return formatNumber((sentences.transitionWordSentences / sentences.totalSentences) * 100)
}

function calculateScoreFromPercentage (percentage) {
	if (20 > percentage) {
		return 3
	}
	if (inRange(percentage, 20, 30)) {
		return 6
	}
	if (30 <= percentage) {
		return 9
	}
}

function transitionWords (content, locale) {
	if (!content) {
		return {}
	}

	// Check if the text is longer than 200 words.
	if (200 > count(content, 'characters_including_spaces')) {
		return {}
	}

	const highlightSentences = []
	const transitionWordSentences = findTransitionWords(content, locale)
	const percentage = calculateTransitionWordPercentage(transitionWordSentences)
	const score = calculateScoreFromPercentage(percentage)
	if (7 > score && 0 === percentage) {
		return {
			title       : __('Transition words', td),
			description : __('None of the sentences contain transition words. Use some.', td),
			score       : formatNumber(score),
			maxScore    : 9,
			error       : 1
		}
	}

	if (Array.isArray(transitionWordSentences.sentenceResults) && transitionWordSentences.sentenceResults.length) {
		highlightSentences.push(...transitionWordSentences.sentenceResults.map(s => s.sentence))
	}

	if (7 > score) {
		return {
			title       : __('Transition words', td),
			description : sprintf(
				// Translators: 1 - Percentage of the sentences.
				__('Only %1$s of the sentences contain transition words, which is not enough. Use more of them.', td),
				`${percentage}%`
			),
			score              : formatNumber(score),
			maxScore           : 9,
			error              : 1,
			highlightSentences : highlightSentences
		}
	}

	return {
		title              : __('Transition words', td),
		description        : __('Well done!', td),
		score              : 9,
		maxScore           : 9,
		error              : 0,
		highlightSentences : highlightSentences
	}
}

export default transitionWords