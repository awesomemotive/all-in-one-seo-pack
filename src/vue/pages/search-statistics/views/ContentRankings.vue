<template>
	<div class="aioseo-search-statistics-content-rankings">
		<content-rankings
			v-if="shouldShowMain('search-statistics', 'content-rankings') || searchStatisticsStore.shouldShowSampleReports"
		/>

		<lite
			v-if="(shouldShowUpgrade('search-statistics', 'content-rankings') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
		/>
	</div>
</template>

<script setup>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Lite from './lite/content-rankings/Index'
import ContentRankings from './content-rankings/Index'

import { useLicense } from '@/vue/composables/License'

const searchStatisticsStore = useSearchStatisticsStore()

const {
	shouldShowLite,
	shouldShowMain,
	shouldShowUpgrade
} = useLicense()
</script>