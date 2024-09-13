import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

export default function (keyphrase) {
	if ('focus' === keyphrase) {
		return __('Focus keyphrase', td)
	}
	return __('Keyphrase', td)
}