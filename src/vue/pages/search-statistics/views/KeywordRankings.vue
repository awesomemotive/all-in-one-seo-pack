<template>
	<div class="aioseo-search-statistics-keyword-rankings">
		<keyword-rankings
			v-if="shouldShowMain('search-statistics', 'keyword-rankings')"
		/>

		<lite
			v-if="shouldShowUpgrade('search-statistics', 'keyword-rankings') || shouldShowLite"
		/>
	</div>
</template>

<script>
import Lite from './lite/keyword-rankings/Index'
import KeywordRankings from './AIOSEO_VERSION/keyword-rankings/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	mixins     : [ LicenseConditions ],
	components : {
		KeywordRankings,
		Lite
	}
}
</script>