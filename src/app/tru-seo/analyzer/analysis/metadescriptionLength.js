import { count } from '@/vue/plugins/wordcount'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const recommendedMaximumLength = 120
const maximumLength = 160
const scores = {
	noMetaDescription : 1,
	tooLong           : 6,
	tooShort          : 6,
	correctLength     : 9
}

function metadescriptionLength (content) {
	if (!content) {
		return {}
	}

	const descriptionLength = count(content, 'characters_including_spaces')

	if (0 === descriptionLength) {
		return {
			title       : __('Meta description length', td),
			description : __('No meta description has been specified. Search engines will display copy from the page instead. Make sure to write one!', td),
			score       : scores.noMetaDescription,
			maxScore    : 9,
			error       : 1
		}
	}

	if (descriptionLength <= recommendedMaximumLength) {
		return {
			title       : __('Meta description length', td),
			description : __('The meta description is too short.', td),
			score       : scores.tooShort,
			maxScore    : 9,
			error       : 1
		}
	}

	if (descriptionLength > maximumLength) {
		return {
			title       : __('Meta description length', td),
			description : __('The meta description is over 160 characters.', td),
			score       : scores.tooLong,
			maxScore    : 9,
			error       : 1
		}
	}

	if (descriptionLength >= recommendedMaximumLength && descriptionLength <= maximumLength) {
		return {
			title       : __('Meta description length', td),
			description : __('Well done!', td),
			score       : scores.correctLength,
			maxScore    : 9,
			error       : 0
		}
	}
}

export default metadescriptionLength