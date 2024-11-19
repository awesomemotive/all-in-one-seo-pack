import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useHeadlineResult = () => {
	const strings = {
		wordBalance    : __('Word balance', td),
		characterCount : __('Character Count', td),
		sentiment      : __('Sentiment', td),
		wordCount      : __('Word Count', td),
		headlineType   : __('Headline Type', td),
		goal           : __('Goal: ', td)
	}

	return {
		strings
	}
}