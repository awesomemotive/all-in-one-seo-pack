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

<script setup>
import Maps from './AIOSEO_VERSION/maps/Maps'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/maps/Maps'
import { useAddonConditions } from '@/vue/composables/AddonConditions'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-local-business'
})

const strings = {
	googleMapsApiKey : __('Google Maps API Key', td)
}
</script>