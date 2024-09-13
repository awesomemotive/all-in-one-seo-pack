<template>
	<cta
		:cta-link="links.getPricingUrl('seo-revisions', 'seo-revisions', parentComponentContext)"
		:button-text="strings.ctaButtonText"
		:learn-more-link="links.getUpsellUrl('seo-revisions', parentComponentContext, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
		:feature-list="strings.ctaFeatures"
		:hide-bonus="!licenseStore.isUnlicensed"
	>
		<template #header-text>
			{{ strings.ctaHeader }}
		</template>

		<template #description>
			<required-plans :core-feature="['seo-revisions']" />

			{{ strings.ctaDescription }}
		</template>
	</cta>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore(),
			links
		}
	},
	components : {
		Cta,
		RequiredPlans
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				ctaHeader : sprintf(
					// Translators: 1 - "PRO".
					__('SEO Revisions is a %1$s Feature', td),
					'PRO'
				),
				ctaDescription : __('Our powerful revisions feature provides a valuable record of SEO updates, allowing you to monitor the effectiveness of your SEO efforts and make informed decisions.', td),
				ctaFeatures    : [
					__('Improved SEO strategy', td),
					__('Easy to manage revisions', td),
					__('Greater transparency and accountability', td),
					__('Historical record of optimization efforts', td)
				],
				ctaButtonText : __('Unlock SEO Revisions', td)
			}
		}
	}
}
</script>