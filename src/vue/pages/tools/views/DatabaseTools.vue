<template>
	<div>
		<database-tools
			v-if="!rootStore.aioseo.data.isNetworkAdmin || (!licenseStore.isUnlicensed && license.hasCoreFeature('tools', 'network-tools-database'))"
		/>

		<lite-database-tools
			v-if="rootStore.aioseo.data.isNetworkAdmin && (licenseStore.isUnlicensed || !license.hasCoreFeature('tools', 'network-tools-database'))"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import DatabaseTools from './partials/DatabaseTools'
import LiteDatabaseTools from './lite/DatabaseTools'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		DatabaseTools,
		LiteDatabaseTools
	},
	data () {
		return {
			license
		}
	}
}
</script>