import { uniq } from 'lodash-es'
import getKeyphraseType from '../researches/helpers/getKeyphraseType'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noMatches           : 3,
	goodNumberOfMatches : 9
}

function keyphraseInImageAlt (content, keyphrase, type) {
	if (!content) {
		return {}
	}
	const imgRegex = new RegExp('<img*', 'gi')
	const hasImage = content.match(imgRegex)
	if (null === hasImage) {
		return {}
	}

	const keyphraseType = getKeyphraseType(type)
	const keyword = uniq(keyphrase.split(' ')).join(' ')
	const keywordPattern = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/ /g, '.*')
	const regex = new RegExp('<img[^>]*alt=[\'"][^\'"]*' + keywordPattern + '[^\'"]*[\'"]', 'gi')

	if (null !== content.toLowerCase().match(regex)) {
		return {
			title : sprintf(
				// Translators: 1 - Focus Keyword or Keyword.
				__('%1$s in image alt attributes', td),
				keyphraseType
			),
			description : sprintf(
				// Translators: 1 - Focus Keyword or Keyword.
				__('%1$s found in image alt attribute(s).', td),
				keyphraseType
			),
			score    : scores.goodNumberOfMatches,
			maxScore : scores.goodNumberOfMatches,
			error    : 0
		}
	}

	return {
		title : sprintf(
			// Translators: 1 - Focus Keyword or Keyword.
			__('%1$s in image alt attributes', td),
			keyphraseType
		),
		description : sprintf(
			// Translators: 1 - Focus Keyword or Keyword.
			__('%1$s not found in image alt attribute(s). Add an image with your %1$s as alt text.', td),
			keyphraseType
		),
		score    : scores.noMatches,
		maxScore : scores.goodNumberOfMatches,
		error    : 1
	}
}

export default keyphraseInImageAlt