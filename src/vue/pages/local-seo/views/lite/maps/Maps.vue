<template>
	<div class="aioseo-local-maps">
		<core-card
			slug="localBusinessMapsApiKey"
			:noSlide="true"
		>
			<template #header>
				<span>{{ strings.googleMapsApiKey }}</span>
				<core-pro-badge />
			</template>

			<blur />

			<cta
				:cta-link="links.getPricingUrl('local-seo', 'local-seo-upsell', 'maps', 'liteUpgrade')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('local-seo', null, 'liteUpgrade')"
				:feature-list="features"
				:hide-bonus="!licenseStore.isUnlicensed"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans addon="aioseo-local-business" />

					{{ strings.ctaDescription }}
				</template>
			</cta>
		</core-card>
	</div>
</template>

<script setup>
import links from '@/vue/utils/links'
import {
	useLicenseStore
} from '@/vue/stores'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const licenseStore = useLicenseStore()

const features = [
	__('Google Places Support', td),
	__('Google Reviews', td),
	__('Driving Directions', td),
	__('Multiple Locations', td)
]

const strings = {
	googleMapsApiKey : __('Google Maps API Key', td),
	ctaButtonText    : __('Unlock Local SEO', td),
	ctaHeader        : sprintf(
		// Translators: 1 - "PRO".
		__('Local SEO is a %1$s Feature', td),
		'PRO'
	),
	ctaDescription : __('Show your location to your visitors using an interactive Google Map. Create multiple maps for use with multiple locations.', td)
}
</script>

<style lang="scss">
.aioseo-local-maps {
	position: relative;
}
</style>