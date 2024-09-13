<template>
	<div class="aioseo-link-assistant-overview">
		<blur />

		<cta
			class="aioseo-link-assistant-cta"
			:cta-link="links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'overview')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('link-assistant', 'overview', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="[
				strings.linkOpportunities,
				strings.domainReports,
				strings.orphanedPosts,
				strings.affiliateLinks
			]"
			align-top
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans addon="aioseo-link-assistant" />

				{{ strings.linkAssistantDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import Cta from '@/vue/components/common/cta/Index'

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
		Blur,
		RequiredPlans,
		Cta
	},
	data () {
		return {
			strings : {
				ctaButtonText : __('Unlock Link Assistant', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Link Assistant is a %1$s Feature', td),
					'PRO'
				),
				linkAssistantDescription : __('Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.', td),
				linkOpportunities        : __('Actionable Link Suggestions', td),
				orphanedPosts            : __('See Orphaned Posts', td),
				affiliateLinks           : __('See Affiliate Links', td),
				domainReports            : __('Top Domain Reports', td)
			}
		}
	}
}
</script>