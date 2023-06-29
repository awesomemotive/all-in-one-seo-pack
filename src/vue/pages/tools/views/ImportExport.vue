<template>
	<div>
		<import-export
			v-if="!rootStore.aioseo.data.isNetworkAdmin || (!licenseStore.isUnlicensed && license.hasCoreFeature('tools', 'network-tools-import-export'))"
		/>

		<lite-import-export
			v-if="rootStore.aioseo.data.isNetworkAdmin && (licenseStore.isUnlicensed || !license.hasCoreFeature('tools', 'network-tools-import-export'))"
		/>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import ImportExport from './partials/ImportExport'
import LiteImportExport from './lite/ImportExport'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		ImportExport,
		LiteImportExport
	},
	data () {
		return {
			license
		}
	}
}
</script>