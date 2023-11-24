import { count } from '@/vue/plugins/wordcount'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const recommendedMaximumLength = 40
const maximumLength = 60
const scores = {
	noTitle       : 1,
	tooLong       : 6,
	tooShort      : 6,
	correctLength : 9
}

function titleLength (content) {
	if (!content) {
		return {
			error       : 1,
			title       : __('SEO Title length', td),
			description : __('Please add a title first.', td),
			score       : 1,
			maxScore    : 5
		}
	}

	const tLength = count(content, 'characters_including_spaces')
	if (0 === tLength) {
		return {
			title       : __('SEO Title length', td),
			description : __('No title has been specified. Make sure to write one!', td),
			score       : scores.noTitle,
			maxScore    : 9,
			error       : 1
		}
	}

	if (tLength <= recommendedMaximumLength) {
		return {
			title       : __('SEO Title length', td),
			description : __('The title is too short.', td),
			score       : scores.tooShort,
			maxScore    : 9,
			error       : 1
		}
	}

	if (tLength > maximumLength) {
		return {
			title       : __('SEO Title length', td),
			description : __('The title is over 60 characters.', td),
			score       : scores.tooLong,
			maxScore    : 9,
			error       : 1
		}
	}

	if (tLength >= recommendedMaximumLength && tLength <= maximumLength) {
		return {
			title       : __('SEO Title length', td),
			description : __('Well done!', td),
			score       : scores.correctLength,
			maxScore    : 9,
			error       : 0
		}
	}
}

export default titleLength