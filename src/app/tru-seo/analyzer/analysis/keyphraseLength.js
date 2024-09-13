import { count } from '@/vue/plugins/wordcount'
import { inRange } from 'lodash-es'
import getKeyphraseType from '../researches/helpers/getKeyphraseType'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const parameters = {
	recommendedMinimum : 1,
	recommendedMaximum : 4,
	acceptableMaximum  : 8
}
const scores = {
	veryBad : -999,
	bad     : 3,
	okay    : 6,
	good    : 9
}

function keyphraseLength (keyphrase, type) {
	const keyphraseType = getKeyphraseType(type)
	const keyLength     = count(keyphrase, 'words')
	const title         = sprintf(
		// Translators: 1 - Focus Keyphrase or Keyphrase.
		__('%1$s length', td),
		keyphraseType
	)

	if (keyLength < parameters.recommendedMinimum) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase.
				__('No %1$s was set. Set a %1$s in order to calculate your SEO score.', td),
				keyphraseType
			),
			score    : scores.veryBad,
			maxScore : 9,
			error    : 1,
			length   : keyLength
		}
	}
	if (inRange(keyLength, parameters.recommendedMinimum, parameters.recommendedMaximum + 1)) {
		return {
			title       : title,
			description : __('Good job!', td),
			score       : scores.good,
			maxScore    : 9,
			error       : 0,
			length      : keyLength
		}
	}
	if (inRange(keyLength, parameters.recommendedMaximum + 1, parameters.acceptableMaximum + 1)) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase.
				__('%1$s is slightly long. Try to make it shorter.', td),
				keyphraseType
			),
			score    : scores.okay,
			maxScore : 9,
			error    : 1,
			length   : keyLength
		}
	}
	return {
		title       : title,
		description : sprintf(
			// Translators: 1 - Focus Keyphrase or Keyphrase.
			__('%1$s is too long. Try to make it shorter.', td),
			keyphraseType
		),
		score    : scores.bad,
		maxScore : 9,
		error    : 1,
		length   : keyLength
	}
}

export default keyphraseLength