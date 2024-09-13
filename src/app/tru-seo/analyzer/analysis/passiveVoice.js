import getPassiveVoice from '../researches/getPassiveVoice'
import formatNumber from '../researches/helpers/formatNumber'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

function passiveVoice (content, locale) {
	if (!content) {
		return {}
	}

	let score = 9,
		percentage = 0
	const highlightSentences = []
	const recommendedValue = 10
	const passiveVoiceResult = getPassiveVoice(content, locale)

	if (false === passiveVoiceResult) {
		return {
			title              : '', // Removing title so that it doesn't show up in the analysis.
			description        : __('Passive voice is not supported in your current language.', td),
			score              : 0,
			maxScore           : 0,
			error              : 0,
			highlightSentences : highlightSentences
		}
	}

	if (0 !== passiveVoiceResult.total) {
		percentage = formatNumber((passiveVoiceResult.passives.length / passiveVoiceResult.total) * 100)
	}

	if (10 < percentage) {
		// Orange indicator.
		score = 6
	}

	if (15 < percentage) {
		// Red indicator.
		score = 3
	}

	if (Array.isArray(passiveVoiceResult.passives) && passiveVoiceResult.passives.length) {
		highlightSentences.push(...passiveVoiceResult.passives)
	}

	if (6 < score) {
		return {
			title              : __('Passive voice', td),
			description        : __('You\'re using enough active voice. That\'s great!', td),
			score              : score,
			maxScore           : 9,
			error              : 0,
			highlightSentences : highlightSentences
		}
	}

	return {
		title       : __('Passive voice', td),
		description : sprintf(
			// Translators: 1 - Percentage of the sentences, 2 - Expected maximum percentage of sentences.
			__('%1$s of the sentences contain passive voice, which is more than the recommended maximum of %2$s. Try to use their active counterparts.', td),
			`${percentage}%`,
			`${recommendedValue}%`
		),
		score              : score,
		maxScore           : 9,
		error              : 1,
		highlightSentences : highlightSentences
	}
}

export default passiveVoice