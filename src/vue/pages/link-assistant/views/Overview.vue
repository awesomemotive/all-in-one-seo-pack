<template>
	<div class="aioseo-link-assistant-overview">
		<overview
			v-if="shouldShowMain"
		/>

		<cta
			v-if="shouldShowUpdate || shouldShowActivate"
		/>

		<lite
			v-if="shouldShowLite"
		/>
	</div>
</template>

<script>
import { AddonConditions } from '@/vue/mixins/AddonConditions'
import Cta from './AIOSEO_VERSION/overview/Cta'
import Lite from './lite/overview/Overview'
import Overview from './AIOSEO_VERSION/overview/Overview'
export default {
	mixins     : [ AddonConditions ],
	components : {
		Cta,
		Lite,
		Overview
	},
	data () {
		return {
			addonSlug : 'aioseo-link-assistant'
		}
	}
}
</script>