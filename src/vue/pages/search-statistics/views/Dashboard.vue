<template>
	<div class="aioseo-search-statistics-dashboard">
		<dashboard
			v-if="shouldShowMain('search-statistics') || searchStatisticsStore.shouldShowSampleReports"
		/>

		<lite
			v-if="(shouldShowUpgrade('search-statistics') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
		/>
	</div>
</template>

<script setup>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Lite from './lite/dashboard/Index'
import Dashboard from './dashboard/Index'

import { useLicense } from '@/vue/composables/License'

const searchStatisticsStore = useSearchStatisticsStore()

const {
	shouldShowLite,
	shouldShowMain,
	shouldShowUpgrade
} = useLicense()
</script>