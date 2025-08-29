<template>
	<div class="aioseo-locations-lite">
		<core-card
			slug="localBusinessInfo"
			class="aioseo-locations-card"
			:noSlide="true"
		>

			<template #header>
				<span>{{ strings.businessInfo }}</span>
				<core-pro-badge />
			</template>

			<blur />

			<cta
				:cta-link="links.getPricingUrl('local-seo', 'local-seo-upsell', 'locations', 'liteUpgrade')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('local-seo', null, 'liteUpgrade')"
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
	__('Local Business Schema', td),
	__('Multiple Locations', td),
	__('Business Info and Location blocks, widgets and shortcodes', td),
	__('Detailed Address, Contact and Payment Info', td)
]

const strings = {
	locationInfo1 : __('Local Business schema markup informs Google about your business details like name, address, phone number, hours, and price range, which can appear in a Knowledge Graph card or business carousel.', td),
	businessInfo  : __('Business Info', td),
	ctaButtonText : __('Unlock Local SEO', td),
	ctaHeader     : sprintf(
		// Translators: 1 - "PRO".
		__('Local SEO is a %1$s Feature', td),
		'PRO'
	)
}
</script>

<style lang="scss">
.aioseo-locations-lite {
	.aioseo-locations-card {
		.content {
			min-height: 550px;
		}
	}

	.location-description {
		margin-top: 0;
		margin-bottom: 16px;
		&.mb-0 {
			margin-bottom: 0;
		}
	}
	.aioseo-input,
	.aioseo-select,
	.aioseo-multiselect {
		max-width: 480px;
	}
	.aioseo-tag {
		background: $gray;
		padding: 3px 8px;
		margin-right: 8px;
		border-radius: 3px;
	}

	.info-business-image {
		.image-upload {
			display: flex;

			.aioseo-input-container {
				width: 100%;
				max-width: 445px;
				margin-right: 8px;

				.aioseo-input {
					width: 100%;
				}
			}

			.insert-image {
				min-width: 214px;
				margin-right: 8px;

				svg.aioseo-circle-plus {
					width: 13px;
					height: 13px;
					margin-right: 10px;
				}
			}
		}

		img {
			margin-top: 20px;
			width: auto;
			max-width: 525px;
			max-height: 525px;
			height: auto;
		}
	}
}
</style>