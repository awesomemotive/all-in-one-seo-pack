<template>
	<div>
		<keywords-graph
			v-if="(shouldShowMain('search-statistics', 'keyword-rankings') || licenseStore.isUnlicensed || searchStatisticsStore.shouldShowSampleReports)"
			legend-style="simple"
		/>

		<upgrade
			v-if="shouldShowUpgrade('search-statistics', 'keyword-rankings') && !searchStatisticsStore.shouldShowSampleReports"
		/>
	</div>
</template>

<script setup>
import {
	useLicenseStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import KeywordsGraph from './keywords-graph/KeywordsGraph'
import Upgrade from './keywords-graph/Upgrade'

import { useLicense } from '@/vue/composables/License'

const {
	shouldShowMain,
	shouldShowUpgrade
} = useLicense()

const licenseStore          = useLicenseStore()
const searchStatisticsStore = useSearchStatisticsStore()
</script>

<style lang="scss">
.aioseo-app .aioseo-search-statistics-redirects {
	font-size: 14px;

	.aioseo-cta {
		.aioseo-cta-background {
			.header-text {
				font-size: 15px;
			}

			.description {
				font-size: 14px;
			}
		}
	}
}
</style>