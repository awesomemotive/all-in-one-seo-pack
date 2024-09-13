<template>
	<div class="aioseo-search-statistics-content-rankings">
		<blur
			v-if="!searchStatisticsStore.shouldShowSampleReports"
		/>

		<cta
			v-if="!searchStatisticsStore.shouldShowSampleReports"
			:cta-link="links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'content-rankings')"
			:button-text="strings.ctaButtonText"
			:learn-more-link="links.getUpsellUrl('search-statistics', 'content-rankings', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
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
import links from '@/vue/utils/links'
import {
	useLicenseStore,
	useRootStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import { useCta } from '@/vue/pages/search-statistics/composables/Cta'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'

const { strings }           = useCta()
const licenseStore          = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()
const rootStore             = useRootStore()
</script>

<style>
.aioseo-search-statistics-content-rankings {
	position: relative;
}
</style>