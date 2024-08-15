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

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Lite from './lite/content-rankings/Index'
import ContentRankings from './content-rankings/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	mixins     : [ LicenseConditions ],
	components : {
		ContentRankings,
		Lite
	}
}
</script>