<template>
	<div class="aioseo-search-statistics-content-rankings">
		<content-rankings
			v-if="shouldShowMain('search-statistics', 'content-rankings')"
		/>

		<lite
			v-if="shouldShowUpgrade('search-statistics', 'content-rankings') || shouldShowLite"
		/>
	</div>
</template>

<script>
import Lite from './lite/content-rankings/Index'
import ContentRankings from './AIOSEO_VERSION/content-rankings/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	mixins     : [ LicenseConditions ],
	components : {
		ContentRankings,
		Lite
	}
}
</script>