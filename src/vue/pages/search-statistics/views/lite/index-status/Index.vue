<template>
	<div>
		<blur v-if="!searchStatisticsStore.shouldShowSampleReports"/>

		<cta
			v-if="!searchStatisticsStore.shouldShowSampleReports"
			cta-second-button-action
			@cta-second-button-click="searchStatisticsStore.showSampleReports"
			:cta-link="links.getPricingUrl('search-statistics', 'search-statistics-upsell', 'index-status', 'liteUpgrade')"
			:button-text="strings.ctaButtonText"
			:second-button-text="strings.ctaSecondButtonText"
			cta-second-button-new-badge
			cta-second-button-visible
			:learn-more-link="links.getUpsellUrl('search-statistics', 'index-status', 'liteUpgrade')"
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
				<required-plans :core-feature="[ 'search-statistics' ]"/>

				{{ strings.ctaDescription }}
			</template>
		</cta>
	</div>
</template>

<script setup>
import {
	useLicenseStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import links from '@/vue/utils/links'

import Blur from './Blur'
import Cta from '@/vue/components/common/cta/Index'
import RequiredPlans from '@/vue/components/lite/core/upsells/RequiredPlans'
import { useCta } from '@/vue/pages/search-statistics/composables/Cta'

const { strings } = useCta()

const licenseStore          = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()
</script>