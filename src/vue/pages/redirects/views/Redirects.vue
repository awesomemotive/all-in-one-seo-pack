<template>
	<div class="aioseo-redirects-main">
		<redirects
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
import Cta from './AIOSEO_VERSION/redirects/Cta'
import Lite from './lite/redirects/Redirects'
import Redirects from './AIOSEO_VERSION/redirects/Redirects'
import { useAddonConditions } from '@/vue/composables/AddonConditions'

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowMain,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-redirects'
})
</script>