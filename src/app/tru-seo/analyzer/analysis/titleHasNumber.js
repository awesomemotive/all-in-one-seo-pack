import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const scores = {
	noTitleNumber  : 5,
	titleHasNumber : 9
}

function titleHasNumber (title) {
	if (!title) {
		return {}
	}

	const hasNumber = /\d+/.test(title)
	if (hasNumber) {
		return {
			title       : __('SEO Title has number', td),
			description : __('Your SEO title contains a number. Good job.', td),
			score       : scores.titleHasNumber,
			maxScore    : scores.titleHasNumber,
			error       : 0
		}
	}

	return {
		title       : __('SEO Title has number', td),
		description : __('Your SEO title doesn\'t contain a number. Add a number to your title to improve CTR.', td),
		score       : scores.noTitleNumber,
		maxScore    : scores.titleHasNumber,
		error       : 1
	}
}

export default titleHasNumber