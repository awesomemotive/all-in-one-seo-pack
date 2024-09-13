<template>
	<div class="aioseo-opening-hours">
		<core-card
			slug="localBusinessOpeningHours"
			:noSlide="true"
		>

			<template #header>
				<span>{{ strings.openingHours }}</span>
				<core-pro-badge />
			</template>

			<blur />

			<cta
				:cta-link="links.getPricingUrl('local-seo', 'local-seo-upsell', 'opening-hours')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('local-seo', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
				:feature-list="features"
				align-top
				:hide-bonus="!licenseStore.isUnlicensed"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans addon="aioseo-local-business" />

					{{ strings.locationInfo1 }}
				</template>
			</cta>
		</core-card>
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
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
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
		CoreCard,
		CoreProBadge,
		Cta
	},
	data () {
		return {
			features : [
				__('Show Opening Hours', td),
				__('Multiple Locations', td),
				__('Opening Hours block, widget and shortcode', td)
			],
			strings : {
				locationInfo1 : __('Local Business schema markup informs Google about your business details like name, address, phone number, hours, and price range, which can appear in a Knowledge Graph card or business carousel.', td),
				openingHours  : __('Opening Hours Settings', td),
				ctaButtonText : __('Unlock Local SEO', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Local SEO is a %1$s Feature', td),
					'PRO'
				)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-opening-hours {
	.aioseo-input,
	.aioseo-select,
	.aioseo-multiselect {
		max-width: 480px;
	}
	.mt-10 {
		display: inline-block;
		margin-top: 10px;
		font-size: 14px;
	}
	.mt-16 {
		margin-top: 16px;
	}
}
.aioseo-col-flex {
	display: flex;
	align-items: center;
	padding: 12px 0;
	border-bottom: 1px solid $border;

	&:first-of-type {
		padding-top: 0;
	}

	&:last-of-type {
		padding-bottom: 0;
		border: none;
	}

	.aioseo-col-day {
		flex: 1
	}

	.aioseo-col-hours,
	.aioseo-col-alwaysopen {
		flex: 2
	}

	.aioseo-select {
		display: inline-block;
		max-width: 125px;
	}

	.multiselect--disabled {

		.multiselect__tags,
		.multiselect__single {
			background: $background;
		}
	}
}
</style>