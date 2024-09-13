import getLinkStats from '../researches/getLinkStats'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches           : 3,
	goodNumberOfMatches : 9
}

function isExternalLink (content, domain) {
	if (!content) {
		return {}
	}

	const linkStatistics = getLinkStats(content, domain)

	if (0 < linkStatistics.externalTotal) {
		return {
			title       : __('External links', td),
			description : __('Great! You are linking to external resources.', td),
			score       : scores.goodNumberOfMatches,
			maxScore    : scores.goodNumberOfMatches,
			error       : 0
		}
	}

	return {
		title       : __('External links', td),
		description : __('No outbound links were found. Link out to external resources.', td),
		score       : scores.noMatches,
		maxScore    : scores.goodNumberOfMatches,
		error       : 1
	}
}

export default isExternalLink