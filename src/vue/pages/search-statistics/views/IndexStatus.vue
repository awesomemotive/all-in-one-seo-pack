<template>
	<lite
		class="aioseo-search-statistics-index-status"
		v-if="(shouldShowUpgrade('search-statistics', 'index-status') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
	/>

	<index-status
		v-else
		:class="{'aioseo-search-statistics-index-status--disable-click': searchStatisticsStore.shouldShowSampleReports}"
		class="aioseo-search-statistics-index-status"
	/>
</template>

<script setup>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import IndexStatus from './index-status/Index'
import Lite from './lite/index-status/Index'

import { useLicense } from '@/vue/composables/License'

const searchStatisticsStore = useSearchStatisticsStore()

const {
	shouldShowLite,
	shouldShowUpgrade
} = useLicense()
</script>

<style lang="scss">
.aioseo-search-statistics-index-status {
	position: relative;

	&--disable-click {
		a:not(.allow-click),
		button:not(.allow-click),
		select,
		input[type="button"],
		input[type="submit"] {
			pointer-events: none !important;
		}
	}
}
</style>