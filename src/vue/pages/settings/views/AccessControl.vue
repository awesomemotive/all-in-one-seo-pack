<template>
	<div class="aioseo-access-control">
		<access-control
			v-if="!licenseStore.isUnlicensed"
		/>

		<access-control-lite
			v-if="licenseStore.isUnlicensed"
		/>

	</div>
</template>

<script>
import {
	useLicenseStore
} from '@/vue/stores'

import AccessControl from './AIOSEO_VERSION/AccessControl'
import AccessControlLite from './lite/AccessControl'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore()
		}
	},
	components : {
		AccessControl,
		AccessControlLite
	}
}
</script>