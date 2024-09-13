import { powerWords } from '../researches/config/powerWords'
import { indexOf } from 'lodash-es'
import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noPowerWords       : 5,
	titleHasPowerWords : 9
}

function titleHasPowerWords (title) {
	if (!title) {
		return {}
	}
	const titleLower = title.toLowerCase().split(' ')
	const powerWordsInText = powerWords.filter((word) => 0 <= indexOf(titleLower, word))
	const hasPowerWords = 0 < powerWordsInText.length

	if (hasPowerWords) {
		return {
			title       : __('SEO Title has Power Words', td),
			description : sprintf(
				// Translators: 1 - Number of power words.
				__('Your SEO title contains %1$s power word(s). Booyah!', td),
				powerWordsInText.length
			),
			score    : scores.titleHasPowerWords,
			maxScore : scores.titleHasPowerWords,
			error    : 0
		}
	}

	return {
		title       : __('SEO Title has Power Words', td),
		description : __('Your SEO title doesn\'t contain a power word. Add at least one. Power Words are tried-and-true words that copywriters use to attract more clicks.', td),
		score       : scores.noPowerWords,
		maxScore    : scores.titleHasPowerWords,
		error       : 1
	}
}

export default titleHasPowerWords