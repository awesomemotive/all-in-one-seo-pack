<template>
	<div class="aioseo-search-statistics-seo-statistics">
		<seo-statistics
			v-if="shouldShowMain('search-statistics', 'seo-statistics')"
		/>

		<lite
			v-if="shouldShowUpgrade('search-statistics', 'seo-statistics') || shouldShowLite"
		/>
	</div>
</template>

<script>
import Lite from './lite/seo-statistics/Index'
import SeoStatistics from './AIOSEO_VERSION/seo-statistics/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	mixins     : [ LicenseConditions ],
	components : {
		SeoStatistics,
		Lite
	}
}
</script>