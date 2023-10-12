<template>
	<div class="aioseo-news-sitemap-lite">
		<core-card
			slug="newsSitemap"
			:noSlide="true"
		>
			<template #header>
				<span>{{ strings.news }}</span>
				<core-pro-badge />
			</template>

			<blur />

			<cta
				:feature-list="[
					strings.setPublicationName,
					strings.exclude
				]"
				:cta-link="$links.getPricingUrl('news-sitemap', 'news-sitemap-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="$links.getUpsellUrl('news-sitemap', null, $isPro ? 'pricing' : 'liteUpgrade')"
				:hide-bonus="!licenseStore.isUnlicensed"
			>
				<template #header-text>
					{{ strings.ctaHeader }}
				</template>
				<template #description>
					<required-plans addon="aioseo-news-sitemap" />

					{{ strings.description }}
				</template>
			</cta>
		</core-card>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import { useNewsSitemap } from '@/vue/pages/sitemaps/composables'
import Blur from './Blur'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
export default {
	setup () {
		const { strings } = useNewsSitemap()

		return {
			licenseStore : useLicenseStore(),
			strings
		}
	},
	components : {
		Blur,
		RequiredPlans,
		CoreCard,
		CoreProBadge,
		Cta
	}
}
</script>

<style lang="scss">
.aioseo-news-sitemap-lite {
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