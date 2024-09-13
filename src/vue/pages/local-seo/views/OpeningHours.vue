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

<script setup>
import OpeningHours from './AIOSEO_VERSION/opening-hours/OpeningHours'
import Cta from './AIOSEO_VERSION/partials/Cta'
import Lite from './lite/opening-hours/OpeningHours'
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
	openingHours : __('Opening Hours Settings', td)
}
</script>