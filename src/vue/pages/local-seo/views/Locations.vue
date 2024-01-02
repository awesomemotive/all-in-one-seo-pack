<template>
	<div class="aioseo-locations">
		<locations
			v-if="shouldShowMain"
		/>

		<cta
			v-if="shouldShowUpdate || shouldShowActivate"
			card-slug="localBusinessInfo"
			:header-text="strings.businessInfo"
		/>

		<lite
			v-if="shouldShowLite"
		/>
	</div>
</template>

<script>
import Locations from './AIOSEO_VERSION/locations/Locations'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/locations/Locations'
import { AddonConditions } from '@/vue/mixins/AddonConditions'
export default {
	mixins     : [ AddonConditions ],
	components : {
		Locations,
		Cta,
		Lite
	},
	data () {
		return {
			addonSlug : 'aioseo-local-business',
			strings   : {
				businessInfo : this.$t.__('Business Info', this.$td)
			}
		}
	}
}
</script>