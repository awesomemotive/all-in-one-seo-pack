import { count } from '@/vue/plugins/wordcount'
import { map } from 'lodash-es'
import escapeRegex from '../researches/helpers/escapeRegex'
import { cleanTagsOnly } from '../researches/helpers/cleanText'
import getKeyphraseType from '../researches/helpers/getKeyphraseType'
import { __, sprintf } from '@wordpress/i18n'
import { td } from '@/vue/plugins/constants'

const scores = {
	fail : 0,
	fair : 3,
	good : 6,
	best : 9
}

function keywordDensity (text, keyword, type) {
	if (!text) {
		return {}
	}

	const keywordType    = getKeyphraseType(type)
	const regex          = new RegExp(map([ keyword ], escapeRegex).join('|'), 'gi')
	const wordCount      = count(text, 'words')
	const keywordMatches = (cleanTagsOnly(text).match(regex) || []).length
	const density        = parseFloat(((keywordMatches / wordCount) * 100).toFixed(2))
	const title          = sprintf(
		// Translators: 1 - Focus Keyphrase or Keyphrase.
		__('%1$s density', td),
		keywordType
	)

	if (0.5 > density) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase, 2 - Keyphrase Density Number, 3 - Keyphrase Matches Number.
				__('%1$s Density is low at %2$s, the Keyphrase appears %3$s times. For better results, try to aim for more than %4$s.', td),
				keywordType,
				`${density}%`,
				keywordMatches,
				'0.5%'
			),
			score    : scores.fail,
			type     : 'low',
			maxScore : scores.best,
			error    : 1
		}
	}

	if (2.5 < density) {
		return {
			title       : title,
			description : sprintf(
				// Translators: 1 - Focus Keyphrase or Keyphrase, 2 - Keyphrase Density Number, 3 - Keyphrase Matches Number.
				__('%1$s Density is high at %2$s, the Keyphrase appears %3$s times. For better results, try to aim for lower than %4$s.', td),
				keywordType,
				`${density}%`,
				keywordMatches,
				'2.5%'
			),
			type     : 'high',
			score    : scores.fail,
			maxScore : scores.best,
			error    : 1
		}
	}

	return {
		title       : title,
		description : sprintf(
			// Translators: 1 - Focus Keyphrase or Keyphrase, 2 - Keyphrase Density Number, 3 - Keyphrase Matches Number.
			__('%1$s Density is %2$s, the Keyphrase appears %3$s times.', td),
			keywordType,
			`${density}%`,
			keywordMatches
		),
		type     : 'best',
		score    : scores.best,
		maxScore : scores.best,
		error    : 0
	}
}

export default keywordDensity