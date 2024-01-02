<template>
	<div class="aioseo-opening-hours">
		<opening-hours
			v-if="shouldShowMain"
		/>

		<cta
			v-if="shouldShowUpdate || shouldShowActivate"
			card-slug="localBusinessOpeningHours"
			:header-text="strings.openingHours"
		/>

		<lite
			v-if="shouldShowLite"
		/>
	</div>
</template>

<script>
import OpeningHours from './AIOSEO_VERSION/opening-hours/OpeningHours'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/opening-hours/OpeningHours'
import { AddonConditions } from '@/vue/mixins/AddonConditions'
export default {
	mixins     : [ AddonConditions ],
	components : {
		OpeningHours,
		Cta,
		Lite
	},
	data () {
		return {
			addonSlug : 'aioseo-local-business',
			strings   : {
				openingHours : this.$t.__('Opening Hours Settings', this.$td)
			}
		}
	}
}
</script>