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

<script setup>
import Locations from './AIOSEO_VERSION/locations/Locations'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/locations/Locations'
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
	businessInfo : __('Business Info', td)
}
</script>