<template>
	<div class="aioseo-search-statistics-content-rankings">
		<blur />

		<cta
			:cta-link="$links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'content-rankings')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="$links.getUpsellUrl('search-statistics', 'content-rankings', 'home')"
			:feature-list="[
				strings.feature1,
				strings.feature2,
				strings.feature3,
				strings.feature4
			]"
			align-top
		>
			<template #header-text>
				{{ strings.ctaHeader }}
			</template>
			<template #description>
				<required-plans :core-feature="[ 'search-statistics', 'content-rankings' ]" />

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script>
import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
export default {
	components : {
		Blur,
		Cta,
		RequiredPlans
	},
	data () {
		return {
			strings : {
				ctaButtonText : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('Upgrade to %1$s and Unlock Search Statistics', this.$td),
					'Pro'
				),
				ctaHeader : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('Search Statistics is only for licensed %1$s %2$s users.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
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
.aioseo-search-statistics-content-rankings {
	position: relative;
}
</style>