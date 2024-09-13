import { count } from '@/vue/plugins/wordcount'
import { inRange } from 'lodash-es'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const wordAssesment = {
	recommendedMinimum   : 300,
	slightlyBelowMinimum : 250,
	belowMinimum         : 200,
	veryFarBelowMinimum  : 100
}
const scores = {
	recommendedMinimum   : 9,
	slightlyBelowMinimum : 6,
	belowMinimum         : 3,
	farBelowMinimum      : -10,
	veryFarBelowMinimum  : -20
}

function lengthContent (content) {
	if (!content) {
		return {
			error       : 1,
			title       : __('No content yet', td),
			description : __('Please add some content first.', td),
			score       : 1,
			maxScore    : 5
		}
	}

	const wordCount = count(content, 'words')

	if (wordAssesment.recommendedMinimum < wordCount) {
		return {
			title       : __('Content length', td),
			description : __('The content length is ok. Good job!', td),
			score       : 9,
			maxScore    : 9,
			error       : 0
		}
	}

	if (inRange(wordCount, 0, wordAssesment.belowMinimum)) {
		let badScore = scores.farBelowMinimum

		if (inRange(wordCount, 0, wordAssesment.veryFarBelowMinimum)) {
			badScore = scores.veryFarBelowMinimum
		}

		return {
			title       : __('Content length', td),
			description : __('This is far below the recommended minimum of words.', td),
			score       : badScore,
			maxScore    : 9,
			error       : 1
		}
	}

	if (inRange(wordCount, wordAssesment.slightlyBelowMinimum, wordAssesment.recommendedMinimum)) {
		return {
			title       : __('Content length', td),
			description : __('The content is below the minimum of words. Add more content.', td),
			score       : 6,
			maxScore    : 9,
			error       : 1
		}
	}

	return {
		title       : __('Content length', td),
		description : __('The content is below the minimum of words. Add more content.', td),
		score       : 3,
		maxScore    : 9,
		error       : 1
	}
}

export default lengthContent