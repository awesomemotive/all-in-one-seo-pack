<template>
	<div class="aioseo-maps">
		<maps
			v-if="shouldShowMain"
		/>

		<cta
			v-if="shouldShowUpdate || shouldShowActivate"
			card-slug="localBusinessMapsApiKey"
			:header-text="strings.googleMapsApiKey"
		/>

		<lite
			v-if="shouldShowLite"
		/>
	</div>
</template>

<script>
import Maps from './AIOSEO_VERSION/maps/Maps'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/maps/Maps'
import { AddonConditions } from '@/vue/mixins/AddonConditions'
export default {
	mixins     : [ AddonConditions ],
	components : {
		Maps,
		Cta,
		Lite
	},
	data () {
		return {
			addonSlug : 'aioseo-local-business',
			strings   : {
				googleMapsApiKey : this.$t.__('Google Maps API Key', this.$td)
			}
		}
	}
}
</script>