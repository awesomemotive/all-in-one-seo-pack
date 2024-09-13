import { filter, inRange } from 'lodash-es'
import getSubheadingTexts from '../researches/stringProcessing/getSubheadingTexts'
import isTextTooLong from '../researches/helpers/isValueTooLong'
import getWords from '../researches/stringProcessing/getWords'
import { __, _n, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const parameters = {
	recommendedMaximumWordCount : 300,
	slightlyTooMany             : 300,
	farTooMany                  : 350
}
const scores = {
	goodShortTextNoSubheadings : 9,
	goodSubheadings            : 9,
	okSubheadings              : 6,
	badSubheadings             : 3,
	badLongTextNoSubheadings   : 2
}

function getTooLongSubheadingTexts (subheadingTextsLength) {
	return filter(subheadingTextsLength, subheading => isTextTooLong(parameters.recommendedMaximumWordCount, subheading.wordCount))
}

function subheadingsDistribution (content) {
	if (!content) {
		return {}
	}

	const highlightSentences = []
	const subheadingTexts = getSubheadingTexts(content)
	const textLength = getWords(content).length
	if (300 < textLength) {
		if (subheadingTexts.length) {
			const longSubheadingTexts = getTooLongSubheadingTexts(subheadingTexts)
			const sortSubheadingTexts = [ ...subheadingTexts ].sort((a, b) => b.wordCount - a.wordCount)
			const longestSubheadingTextLength = sortSubheadingTexts[0].wordCount
			if (longestSubheadingTextLength <= parameters.slightlyTooMany) {
				return {
					title              : __('Subheading distribution', td),
					description        : __('Great job!', td),
					score              : scores.goodSubheadings,
					maxScore           : 9,
					error              : 0,
					highlightSentences : highlightSentences
				}
			}

			return {
				title       : __('Subheading distribution', td),
				description : sprintf(
					// Translators: 1 - Expand to the number of text sections not separated by subheadings, 2 - expands to the recommended number of words following a subheading.
					_n(
						'%1$d section of your text is longer than %2$d words and is not separated by any subheadings. Add subheadings to improve readability.',
						'%1$d sections of your text are longer than %2$d words and are not separated by any subheadings. Add subheadings to improve readability.',
						longSubheadingTexts.length,
						td
					),
					longSubheadingTexts.length,
					parameters.recommendedMaximumWordCount
				),
				score              : inRange(longestSubheadingTextLength, parameters.slightlyTooMany, parameters.farTooMany) ? scores.okSubheadings : scores.badSubheadings,
				maxScore           : 9,
				error              : 1,
				highlightSentences : longSubheadingTexts.map(lst => lst.subheading)
			}
		}

		return {
			title              : __('Subheading distribution', td),
			description        : __('You are not using any subheadings, although your text is rather long. Try and add some subheadings.', td),
			score              : scores.badLongTextNoSubheadings,
			maxScore           : 9,
			error              : 1,
			highlightSentences : highlightSentences
		}
	}

	if (subheadingTexts.length) {
		return {
			title              : __('Subheading distribution', td),
			description        : __('Great job!', td),
			score              : scores.goodSubheadings,
			maxScore           : 9,
			error              : 0,
			highlightSentences : highlightSentences
		}
	}

	return {
		title              : __('Subheading distribution', td),
		description        : __('You are not using any subheadings, but your text is short enough and probably doesn\'t need them.', td),
		score              : scores.goodShortTextNoSubheadings,
		maxScore           : 9,
		error              : 0,
		highlightSentences : highlightSentences
	}
}

export default subheadingsDistribution