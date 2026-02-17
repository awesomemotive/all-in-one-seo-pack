<template>
	<div v-if="optionsStore.internalOptions.internal">
		<core-seo-checklist :isWpDashboard="true" />
	</div>
</template>

<script>
import {
	useOptionsStore
} from '@/vue/stores'

import CoreSeoChecklist from '@/vue/components/common/core/SeoChecklist'
export default {
	setup () {
		return {
			optionsStore : useOptionsStore()
		}
	},
	components : {
		CoreSeoChecklist
	}
}
</script>