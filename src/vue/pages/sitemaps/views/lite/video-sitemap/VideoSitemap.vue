<template>
	<div class="aioseo-video-sitemap-lite">
		<core-card
			slug="videoSitemap"
			:noSlide="true"
		>
			<template #header>
				<span>{{ strings.video }}</span>
				<core-pro-badge />
			</template>

			<blur />

			<cta
				:feature-list="[
					strings.customFieldSupport,
					strings.exclude
				]"
				:cta-link="links.getPricingUrl('video-sitemap', 'video-sitemap-upsell', null, 'liteUpgrade')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('video-sitemap', null, 'liteUpgrade')"
				:hide-bonus="!licenseStore.isUnlicensed"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans addon="aioseo-video-sitemap" />

					{{ strings.description }}
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

import { useVideoSitemap } from '@/vue/pages/sitemaps/composables/VideoSitemap'

import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'

const licenseStore = useLicenseStore()
const { strings } = useVideoSitemap()
</script>

<style lang="scss">
.aioseo-video-sitemap-lite {
	.aioseo-card .content {
		min-height: 600px;
	}

	.aioseo-sitemap-preview {
		margin-right: 10px;
		display: flex;
		align-items: center;
		margin-bottom: 10px;

		.aioseo-select {
			max-width: 180px;
			margin-right: 10px;
		}

		svg.aioseo-external {
			width: 14px;
			height: 14px;
			margin-right: 10px;
		}
	}
}
</style>