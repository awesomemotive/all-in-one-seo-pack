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

<script setup>
import { useAddonConditions } from '@/vue/composables/AddonConditions'
import Cta from './AIOSEO_VERSION/overview/Cta'
import Lite from './lite/overview/Overview'
import Overview from './AIOSEO_VERSION/overview/Overview'

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-link-assistant'
})
</script>