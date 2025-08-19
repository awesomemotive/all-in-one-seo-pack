<template>
	<cta
		:cta-link="links.getPricingUrl('seo-analysis', 'site-audit', parentComponentContext)"
		:button-text="strings.ctaButtonText"
		:learn-more-link="links.getUpsellUrl('site-audit', parentComponentContext, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
		:feature-list="strings.ctaFeatures"
		:hide-bonus="!licenseStore.isUnlicensed"
	>
		<template #header-text>
			<span class="upsell-header-text">
				{{ strings.ctaHeader }}
			</span>
		</template>

		<template #description>
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
		Cta
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			strings : {
				ctaHeader : sprintf(
					// Translators: 1 - "PRO".
					__('Site Audit is a %1$s Feature', td),
					'PRO'
				),
				ctaDescription : __('Discover which posts and terms on your site are facing common SEO issues so you can quickly resolve them in order to get your content back on track.', td),
				ctaFeatures    : [
					__('Meta Tag Validation', td),
					__('Keyword Cannibilization', td),
					__('Focus Keyword Analysis', td),
					__('Content Analysis', td)
				],
				ctaButtonText : __('Unlock Site Audit', td)
			}
		}
	}
}
</script>

<style lang="scss" scoped>
	.upsell-header-text {
		font-size: 24px;
	}
</style>