<template>
	<div class="aioseo-search-statistics-keyword-rankings">
		<keyword-rankings
			v-if="shouldShowMain('search-statistics', 'keyword-rankings') || searchStatisticsStore.shouldShowSampleReports"
		/>

		<lite
			v-if="(shouldShowUpgrade('search-statistics', 'keyword-rankings') || shouldShowLite) && !searchStatisticsStore.shouldShowSampleReports"
		/>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Lite from './lite/keyword-rankings/Index'
import KeywordRankings from './keyword-rankings/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	mixins     : [ LicenseConditions ],
	components : {
		KeywordRankings,
		Lite
	}
}
</script>