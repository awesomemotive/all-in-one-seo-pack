import wordMatch from '../researches/stringProcessing/matchTextWithWord'
import { stripFullTags as stripTags } from '../researches/stringProcessing/stripHTMLTags.js'
import { getSubheadingContentsTopLevel } from '../researches/stringProcessing/getSubheadings'
import { inRangeStartEndInclusive } from '../researches/helpers/inRange'
import { sprintf, __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const parameters = {
	lowerBoundary : 0.3,
	upperBoundary : 0.75
}
const scores = {
	noMatches           : 3,
	tooFewMatches       : 3,
	goodNumberOfMatches : 9,
	tooManyMatches      : 3
}
const result = {
	count                  : 0,
	matches                : 0,
	percentReflectingTopic : 0
}
let subheadings,
	minNumberOfSubheadings,
	maxNumberOfSubheadings

function hasTooFewMatches () {
	return 0 < result.matches && result.matches < minNumberOfSubheadings
}

function hasTooManyMatches () {
	return 1 < result.count && result.matches > maxNumberOfSubheadings
}

function isOneOfOne () {
	return 1 === result.count && 1 === result.matches
}

function hasGoodNumberOfMatches () {
	return inRangeStartEndInclusive(
		result.matches,
		minNumberOfSubheadings,
		maxNumberOfSubheadings
	)
}

function numberOfSubheadingsReflectingTopic (keyphrase, subheads, locale) {
	return subheads.filter(subheading => {
		const keywordMatched = wordMatch(stripTags(subheading), keyphrase, locale)
		return 0 < keywordMatched.count
	}).length
}

function keyphraseInSubHeadings (content, keyphrase, locale) {
	if (!content) {
		return {}
	}

	subheadings = getSubheadingContentsTopLevel(content)
	if (!subheadings.length) {
		return {}
	}

	minNumberOfSubheadings = Math.ceil(subheadings.length * parameters.lowerBoundary)
	maxNumberOfSubheadings = Math.floor(subheadings.length * parameters.upperBoundary)

	if (0 !== subheadings.length) {
		result.count = subheadings.length
		result.matches = numberOfSubheadingsReflectingTopic(keyphrase, subheadings, locale)
		result.percentReflectingTopic = result.matches / result.count * 100
	}

	if (hasTooFewMatches()) {
		return {
			title       : __('Focus keyphrase in Subheadings', td),
			description : sprintf(
				// Translators: 1 - The percentage of headings.
				__('Less than %1$s of your H2 and H3 subheadings reflect the topic of your copy. That\'s too few.', td),
				(parameters.lowerBoundary * 100) + '%'
			),
			score    : scores.tooFewMatches,
			maxScore : scores.goodNumberOfMatches,
			error    : 1
		}
	}
	if (hasTooManyMatches()) {
		return {
			title       : __('Focus keyphrase in Subheadings', td),
			description : sprintf(
				// Translators: 1 - The percentage of headings.
				__('More than %1$s of your H2 and H3 subheadings reflect the topic of your copy. That\'s too much. Don\'t over-optimize!', td),
				(parameters.upperBoundary * 100) + '%'
			),
			score    : scores.tooManyMatches,
			maxScore : scores.goodNumberOfMatches,
			error    : 1
		}
	}
	if (isOneOfOne()) {
		return {
			title       : __('Focus keyphrase in Subheadings', td),
			description : __('Your H2 or H3 subheading reflects the topic of your copy. Good job!', td),
			score       : scores.goodNumberOfMatches,
			maxScore    : scores.goodNumberOfMatches,
			error       : 0
		}
	}
	if (hasGoodNumberOfMatches()) {
		return {
			title       : __('Focus keyphrase in Subheadings', td),
			description : __('Your H2 and H3 subheadings reflects the topic of your copy. Good job!', td),
			score       : scores.goodNumberOfMatches,
			maxScore    : scores.goodNumberOfMatches,
			error       : 0
		}
	}

	return {
		title       : __('Focus keyphrase in Subheadings', td),
		description : __('Use your focus keyphrase more in your H2 and H3 subheadings.', td),
		score       : scores.noMatches,
		maxScore    : scores.goodNumberOfMatches,
		error       : 1
	}
}

export default keyphraseInSubHeadings