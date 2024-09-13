import wordMatch from '../researches/stringProcessing/matchTextWithWord'
import getKeyphraseType from '../researches/helpers/getKeyphraseType'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches : 3,
	matches   : 9
}

function keyphraseInDescription (description, keyphrase, type, locale) {
	if (!description) {
		return {}
	}

	const keyphraseType = getKeyphraseType(type)
	const keywordMatched = wordMatch(description, keyphrase, locale)
	const title =  sprintf(
		// Translators: 1 - Focus Keyphrase or Keyphrase.
		__('%1$s in meta description', td),
		keyphraseType
	)

	if (0 < keywordMatched.count) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase.
				__('%1$s found in meta description.', td),
				keyphraseType
			),
			score    : scores.matches,
			maxScore : scores.matches,
			error    : 0
		}
	}

	return {
		title       : title,
		description : sprintf(
			// Translators: 1 - Focus Keyphrase or Keyphrase.
			__('%1$s not found in meta description.', td),
			keyphraseType
		),
		score    : scores.noMatches,
		maxScore : scores.matches,
		error    : 1
	}
}

export default keyphraseInDescription