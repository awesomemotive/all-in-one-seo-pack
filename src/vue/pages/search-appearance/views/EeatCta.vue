<template>
	<div>
		<eeat-cta v-if="shouldShowUpdate || shouldShowActivate"/>

		<eeat-cta-lite v-if="shouldShowLite"/>
	</div>
</template>

<script>
import EeatCta from './AIOSEO_VERSION/eeat/EeatCta.vue'
import EeatCtaLite from './lite/eeat/EeatCta.vue'
import { AddonConditions } from '@/vue/mixins/AddonConditions'

export default {
	// TODO: Rewrite this to composition API once we've converted the mixin to a composable.
	components : {
		EeatCta,
		EeatCtaLite
	},
	mixins : [ AddonConditions ],
	data () {
		return {
			addonSlug : 'aioseo-eeat'
		}
	}
}
</script>