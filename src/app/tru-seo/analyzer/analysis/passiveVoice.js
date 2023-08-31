import getPassiveVoice from '../researches/getPassiveVoice'
import formatNumber from '../researches/helpers/formatNumber'
import { __, sprintf } from '@wordpress/i18n'
import { td } from '@/vue/plugins/constants'

function passiveVoice (content, locale) {
	if (!content) {
		return {}
	}

	let score = 9,
		percentage = 0
	const recommendedValue = 10
	const passiveVoiceResult = getPassiveVoice(content, locale)

	if (0 !== passiveVoiceResult.total) {
		percentage = formatNumber((passiveVoiceResult.passives.length / passiveVoiceResult.total)  * 100)
	}

	if (10 < percentage) {
		// Orange indicator.
		score = 6
	}

	if (15 < percentage) {
		// Red indicator.
		score = 3
	}

	if (6 < score) {
		return {
			title       : __('Passive voice', td),
			description : __('You\'re using enough active voice. That\'s great!', td),
			score       : score,
			maxScore    : 9,
			error       : 0
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
		score    : score,
		maxScore : 9,
		error    : 1
	}
}

export default passiveVoice