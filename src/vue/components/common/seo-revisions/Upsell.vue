<template>
	<cta
		:cta-link="$links.getPricingUrl('seo-revisions', 'seo-revisions', parentComponentContext)"
		:button-text="strings.ctaButtonText"
		:learn-more-link="$links.getUpsellUrl('seo-revisions', parentComponentContext, 'home')"
		:feature-list="strings.ctaFeatures"
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
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

export default {
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
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - "Pro".
					this.$t.__('SEO Revisions is only available for licensed %1$s %2$s users.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					'Pro'
				),
				ctaDescription : this.$t.__('Our powerful revisions feature provides a valuable record of SEO updates, allowing you to monitor the effectiveness of your SEO efforts and make informed decisions.', this.$td),
				ctaFeatures    : [
					this.$t.__('Improved SEO strategy', this.$td),
					this.$t.__('Easy to manage revisions', this.$td),
					this.$t.__('Greater transparency and accountability', this.$td),
					this.$t.__('Historical record of optimization efforts', this.$td)
				],
				ctaButtonText : this.$t.sprintf(
					// Translators: 1 - "Pro".
					this.$t.__('Upgrade to %1$s and Unlock SEO Revisions', this.$td),
					'Pro'
				)
			}
		}
	}
}
</script>