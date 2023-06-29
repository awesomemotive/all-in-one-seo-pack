<template>
	<div v-if="optionsStore.internalOptions.internal">
		<core-seo-setup :isWpDashboard="true" />
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import CoreSeoSetup from '@/vue/components/common/core/SeoSetup'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		CoreSeoSetup
	}
}
</script>