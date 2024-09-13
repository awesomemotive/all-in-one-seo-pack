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
				:cta-link="links.getPricingUrl('news-sitemap', 'news-sitemap-upsell')"
				:button-text="strings.ctaButtonText"
				:learn-more-link="links.getUpsellUrl('news-sitemap', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
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
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import { useNewsSitemap } from '@/vue/pages/sitemaps/composables/NewsSitemap'

import Blur from './Blur'
import CoreCard from '@/vue/components/common/core/Card'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
export default {
	setup () {
		const { strings } = useNewsSitemap()

		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore(),
			links,
			strings
		}
	},
	components : {
		Blur,
		CoreCard,
		CoreProBadge,
		Cta,
		RequiredPlans
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