import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useCta = () => {
	const strings = {
		ctaButtonText : __('Unlock Search Statistics', td),
		ctaHeader     : sprintf(
			// Translators: 1 - "PRO".
			__('Search Statistics is a %1$s Feature', td),
			'PRO'
		),
		ctaDescription      : __('Connect your site to Google Search Console to receive insights on how content is being discovered. Identify areas for improvement and drive traffic to your website.', td),
		thisFeatureRequires : __('This feature requires one of the following plans:', td),
		ctaSecondButtonText : __('Explore Sample Reports', td),
		feature1            : __('Search traffic insights', td),
		feature2            : __('Track page rankings', td),
		feature3            : __('Track keyword rankings', td),
		feature4            : __('Speed tests for individual pages/posts', td)
	}

	return {
		strings
	}
}