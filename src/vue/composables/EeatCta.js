import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useEeatCta = () => {
	const addonSlug = 'aioseo-eeat'
	const features  = [
		__('Author Schema', td),
		__('Author Bio Block', td),
		__('Reviewed By Schema', td),
		__('E-E-A-T for Higher Rankings', td)
	]

	const strings   = {
		authorInfo   : __('Author Info (E-E-A-T)', td),
		globalAuthor : __('Author Experience Topics (E-E-A-T)', td),
		headerText   : sprintf(
			// Translators: 1 - "PRO".
			__('Author SEO (E-E-A-T) is a %1$s Feature', td),
			'PRO'
		),
		headerTextActivate    : __('Enable Author SEO (E-E-A-T) on Your Site', td),
		description           : __('Optimize your site for Google\'s E-E-A-T ranking factor by proving your writer\'s expertise through author schema markup and new UI elements.', td),
		ctaButtonText         : __('Unlock Author SEO (E-E-A-T)', td),
		ctaButtonTextActivate : __('Activate Author SEO (E-E-A-T)', td),
		ctaButtonTextUpdate   : __('Update Author SEO (E-E-A-T)', td),
		learnMoreText         : __('Learn more about Author SEO (E-E-A-T)', td)
	}

	return {
		addonSlug,
		features,
		strings
	}
}