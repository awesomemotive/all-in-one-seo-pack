import getLinkStats from '../researches/getLinkStats'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches           : 3,
	goodNumberOfMatches : 9
}

function isExternalLinkNoFollow (content, domain) {
	if (!content) {
		return {}
	}

	const linkStatistics = getLinkStats(content, domain)

	if (1 > linkStatistics.externalTotal) {
		return {}
	}

	if (0 < linkStatistics.externalDofollow) {
		return {
			title       : __('External link dofollow', td),
			description : __('At least one external link with dofollow found in your content.', td),
			score       : scores.goodNumberOfMatches,
			maxScore    : scores.goodNumberOfMatches,
			error       : 0
		}
	}

	return {
		title       : __('External link dofollow', td),
		description : __('We found outbound links in your content and all of them are nofollow.', td),
		score       : scores.noMatches,
		maxScore    : scores.goodNumberOfMatches,
		error       : 1
	}
}

export default isExternalLinkNoFollow