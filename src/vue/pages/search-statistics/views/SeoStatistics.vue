<template>
	<div class="aioseo-search-statistics-seo-statistics">
		<seo-statistics
			v-if="shouldShowMain('search-statistics', 'seo-statistics') || searchStatisticsStore.shouldShowSampleReports"
		/>

		<lite
			v-if="(shouldShowUpgrade('search-statistics', 'seo-statistics') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
		/>
	</div>
</template>

<script setup>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Lite from './lite/seo-statistics/Index'
import SeoStatistics from './seo-statistics/Index'

import { useLicense } from '@/vue/composables/License'

const searchStatisticsStore = useSearchStatisticsStore()

const {
	shouldShowLite,
	shouldShowMain,
	shouldShowUpgrade
} = useLicense()
</script>