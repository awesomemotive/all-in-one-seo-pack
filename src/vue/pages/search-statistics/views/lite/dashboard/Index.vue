<template>
	<div class="aioseo-search-statistics-dashboard">
		<blur
			v-if="!searchStatisticsStore.shouldShowSampleReports"
		/>

		<cta
			:cta-link="links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'dashboard')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('search-statistics', 'dashboard', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
			v-if="!searchStatisticsStore.shouldShowSampleReports"
			cta-second-button-action
			@cta-second-button-click="searchStatisticsStore.showSampleReports"
			:second-button-text="strings.ctaSecondButtonText"
			cta-second-button-new-badge
			cta-second-button-visible
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
				<required-plans :core-feature="[ 'search-statistics' ]" />

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script setup>
import {
	useLicenseStore,
	useSearchStatisticsStore,
	useRootStore
} from '@/vue/stores'

import links from '@/vue/utils/links'

import { useCta } from '@/vue/pages/search-statistics/composables/Cta'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

const { strings }           = useCta()
const licenseStore          = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()
const rootStore             = useRootStore()
</script>

<style lang="scss">
.aioseo-search-statistics-dashboard {
	position: relative;

	.aioseo-seo-statistics-card {
		.header {
			.aioseo-tooltip .popper {
				max-width: 400px;
			}
		}
	}

	> .aioseo-row {
		> .aioseo-col {
			display: flex;
			flex-direction: column;

			.aioseo-card {
				flex: 1;
			}
		}
	}

	.aioseo-card.aioseo-keywords-positions-card {
		.content {
			padding-top: 0;
		}
	}

	.aioseo-search-statistics-keywords-graph {
		margin-bottom: 20px;
	}
}
</style>