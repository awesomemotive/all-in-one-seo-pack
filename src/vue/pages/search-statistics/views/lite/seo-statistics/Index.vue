<template>
	<div class="aioseo-search-statistics-seo-statistics">
		<blur />

		<cta
			:cta-link="$links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'seo-statistics')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="$links.getUpsellUrl('search-statistics', 'seo-statistics', $isPro ? 'pricing' : 'liteUpgrade')"
			:feature-list="[
				strings.feature1,
				strings.feature2,
				strings.feature3,
				strings.feature4
			]"
			align-top
			:hide-bonus="!licenseStore.isUnlicensed"
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans :core-feature="[ 'search-statistics', 'seo-statistics' ]" />

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	components : {
		Blur,
		Cta,
		RequiredPlans
	},
	data () {
		return {
			strings : {
				ctaButtonText : this.$t.__('Unlock Search Statistics', this.$td),
				ctaHeader     : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Search Statistics is a %1$s Feature', this.$td),
					'PRO'
				),
				ctaDescription      : this.$t.__('Connect your site to Google Search Console to receive insights on how content is being discovered. Identify areas for improvement and drive traffic to your website.', this.$td),
				thisFeatureRequires : this.$t.__('This feature requires one of the following plans:', this.$td),
				feature1            : this.$t.__('Search traffic insights', this.$td),
				feature2            : this.$t.__('Track page rankings', this.$td),
				feature3            : this.$t.__('Track keyword rankings', this.$td),
				feature4            : this.$t.__('Speed tests for individual pages/posts', this.$td)
			}
		}
	}
}
</script>

<style>
.aioseo-search-statistics-seo-statistics {
	position: relative;
}
</style>