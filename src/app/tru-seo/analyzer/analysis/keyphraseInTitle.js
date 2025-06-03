import wordMatch from '../researches/stringProcessing/matchTextWithWord'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches : 3,
	matches   : 9
}

function keyphraseInTitle (title, keyphrase, locale) {
	if (!title) {
		return {}
	}

	const keywordMatched = wordMatch(title, keyphrase, locale)

	if (0 < keywordMatched.count) {
		return {
			title       : __('Focus Keyword in SEO title', td),
			description : __('Focus Keyword found in SEO title.', td),
			score       : scores.matches,
			maxScore    : scores.matches,
			error       : 0
		}
	}

	return {
		title       : __('Focus Keyword in SEO title', td),
		description : __('Focus Keyword not found in SEO title.', td),
		score       : scores.noMatches,
		maxScore    : scores.matches,
		error       : 1
	}
}

export default keyphraseInTitle