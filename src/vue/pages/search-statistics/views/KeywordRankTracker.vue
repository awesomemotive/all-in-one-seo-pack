<template>
	<lite
		class="aioseo-search-statistics-keyword-rank-tracker"
		v-if="(shouldShowUpgrade('search-statistics', 'keyword-rank-tracker') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
	/>

	<keyword-rank-tracker
		v-else
		:class="{'aioseo-search-statistics-keyword-rank-tracker--disable-click': searchStatisticsStore.shouldShowSampleReports}"
		class="aioseo-search-statistics-keyword-rank-tracker"
	/>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import { LicenseConditions } from '@/vue/mixins/LicenseConditions'

import KeywordRankTracker from './keyword-rank-tracker/Index'
import Lite from './lite/keyword-rank-tracker/Index'

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	mixins     : [ LicenseConditions ],
	components : {
		KeywordRankTracker,
		Lite
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-keyword-rank-tracker {
	&--disable-click {
		a,
		button,
		input[type="button"],
		input[type="submit"] {
			pointer-events: none !important;
		}
	}
}

.aioseo-search-statistics-keyword-rank-tracker-modal {
	&__body {
		--aioseo-gutter: 12px;

		display: flex;
		flex-direction: column;
		gap: 12px;
		padding: 20px;
	}

	&__footer {
		display: flex;
		gap: 12px;
		justify-content: flex-end;
		padding: 12px 20px;
	}
}
</style>