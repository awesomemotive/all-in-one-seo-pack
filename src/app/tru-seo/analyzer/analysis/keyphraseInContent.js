import wordMatch from '../researches/stringProcessing/matchTextWithWord'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches : 3,
	matches   : 9
}

function keyphraseInContent (content, keyphrase, locale) {
	if (!content) {
		return {}
	}

	const keywordMatched = wordMatch(content, keyphrase, locale)

	if (0 < keywordMatched.count) {
		return {
			title       : __('Focus keyphrase in content', td),
			description : __('Focus keyphrase found in content.', td),
			score       : scores.matches,
			maxScore    : scores.matches,
			error       : 0
		}
	}

	return {
		title       : __('Focus keyphrase in content', td),
		description : __('Focus keyphrase not found in content.', td),
		score       : scores.noMatches,
		maxScore    : scores.matches,
		error       : 1
	}
}

export default keyphraseInContent