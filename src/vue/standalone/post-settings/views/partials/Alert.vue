<template>
	<div>
		<core-api-bar v-if="!rootStore.pong"/>

		<core-license-key-bar v-if="$isPro && licenseStore.isUnlicensed && rootStore.pong" />
	</div>
</template>

<script>
import {
	useLicenseStore,
	useRootStore
} from '@/vue/stores'

import CoreApiBar from '@/vue/components/common/core/ApiBar'
import CoreLicenseKeyBar from '@/vue/components/AIOSEO_VERSION/core/LicenseKeyBar'
export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		CoreApiBar,
		CoreLicenseKeyBar
	}
}
</script>