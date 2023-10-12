<template>
	<div class="aioseo-link-assistant-overview">
		<blur />

		<cta
			class="aioseo-link-assistant-cta"
			:cta-link="$links.getPricingUrl('link-assistant', 'link-assistant-upsell', 'overview')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="$links.getUpsellUrl('link-assistant', 'overview', $isPro ? 'pricing' : 'liteUpgrade')"
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
import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import Cta from '@/vue/components/common/cta/Index'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
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
				ctaButtonText : this.$t.__('Unlock Link Assistant', this.$td),
				ctaHeader     : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Link Assistant is a %1$s Feature', this.$td),
					'PRO'
				),
				linkAssistantDescription : this.$t.__('Get relevant suggestions for adding internal links to all your content as well as finding any orphaned posts that have no internal links.', this.$td),
				linkOpportunities        : this.$t.__('Actionable Link Suggestions', this.$td),
				orphanedPosts            : this.$t.__('See Orphaned Posts', this.$td),
				affiliateLinks           : this.$t.__('See Affiliate Links', this.$td),
				domainReports            : this.$t.__('Top Domain Reports', this.$td)
			}
		}
	}
}
</script>