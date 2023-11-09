<template>
	<div class="aioseo-search-statistics-dashboard">
		<dashboard
			v-if="shouldShowMain('search-statistics')"
		/>

		<lite
			v-if="shouldShowUpgrade('search-statistics') || shouldShowLite"
		/>
	</div>
</template>

<script>
import Lite from './lite/dashboard/Index'
import Dashboard from './AIOSEO_VERSION/dashboard/Index'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	mixins     : [ LicenseConditions ],
	components : {
		Dashboard,
		Lite
	}
}
</script>