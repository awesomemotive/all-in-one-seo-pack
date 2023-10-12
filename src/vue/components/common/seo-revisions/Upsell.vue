<template>
	<cta
		:cta-link="$links.getPricingUrl('seo-revisions', 'seo-revisions', parentComponentContext)"
		:button-text="strings.ctaButtonText"
		:learn-more-link="$links.getUpsellUrl('seo-revisions', parentComponentContext, $isPro ? 'pricing' : 'liteUpgrade')"
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
import {
	useLicenseStore
} from '@/vue/stores'

import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
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
				ctaHeader : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('SEO Revisions is a %1$s Feature', this.$td),
					'PRO'
				),
				ctaDescription : this.$t.__('Our powerful revisions feature provides a valuable record of SEO updates, allowing you to monitor the effectiveness of your SEO efforts and make informed decisions.', this.$td),
				ctaFeatures    : [
					this.$t.__('Improved SEO strategy', this.$td),
					this.$t.__('Easy to manage revisions', this.$td),
					this.$t.__('Greater transparency and accountability', this.$td),
					this.$t.__('Historical record of optimization efforts', this.$td)
				],
				ctaButtonText : this.$t.__('Unlock SEO Revisions', this.$td)
			}
		}
	}
}
</script>