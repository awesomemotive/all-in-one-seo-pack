<template>
	<div>
		<keywords-graph
			v-if="shouldShowMain('search-statistics', 'keyword-rankings') || licenseStore.isUnlicensed"
			legend-style="simple"
		/>

		<upgrade
			v-if="shouldShowUpgrade('search-statistics', 'keyword-rankings')"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import KeywordsGraph from './keywords-graph/KeywordsGraph'
import Upgrade from './keywords-graph/Upgrade'
import { LicenseConditions } from '@/vue/mixins/LicenseConditions'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	mixins : [ LicenseConditions ],
	props  : {
		redirects : Object
	},
	components : {
		KeywordsGraph,
		Upgrade
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-search-statistics-redirects {
	font-size: 14px;

	.aioseo-cta {
		.aioseo-cta-background {
			.header-text {
				font-size: 15px;
			}

			.description {
				font-size: 14px;
			}
		}
	}
}
</style>