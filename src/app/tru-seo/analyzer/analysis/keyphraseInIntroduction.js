import matchParagraphs from '../researches/stringProcessing/matchParagraphs.js'
import wordMatch from '../researches/stringProcessing/matchTextWithWord'
import { reject, isEmpty } from 'lodash-es'
import getKeyphraseType from '../researches/helpers/getKeyphraseType'
import { __, sprintf } from '@wordpress/i18n'
import { td } from '@/vue/plugins/constants'

const scores = {
	noContent : 0,
	noMatches : 3,
	matches   : 9
}

function keyphraseInIntroduction (content, keyphrase, type, locale) {
	if (!content) {
		return {}
	}

	const keyphraseType = getKeyphraseType(type)
	const title =  sprintf(
		// Translators: 1 - Focus Keyphrase or Keyphrase.
		__('%1$s in introduction', td),
		keyphraseType
	)

	if (!content) {
		return {
			title       : title,
			description : __('No content added yet.', td),
			score       : scores.noContent,
			maxScore    : scores.matches,
			error       : 1
		}
	}

	let paragraphs = matchParagraphs(content)
	paragraphs     = reject(paragraphs, isEmpty)

	const keywordMatched = wordMatch(paragraphs[0], keyphrase, locale)
	if (0 < keywordMatched.count) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase.
				__('Your %1$s appears in the first paragraph. Well done!', td),
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
			__('Your %1$s does not appear in the first paragraph. Make sure the topic is clear immediately.', td),
			keyphraseType
		),
		score    : scores.noMatches,
		maxScore : scores.matches,
		error    : 1
	}
}

export default keyphraseInIntroduction