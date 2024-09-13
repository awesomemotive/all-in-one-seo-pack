<template>
	<div>
		<eeat-cta v-if="shouldShowUpdate || shouldShowActivate"/>

		<eeat-cta-lite v-if="shouldShowLite"/>
	</div>
</template>

<script setup>
import EeatCta from './AIOSEO_VERSION/EeatCta'
import EeatCtaLite from './lite/EeatCta'
import { useAddonConditions } from '@/vue/composables/AddonConditions'

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-eeat'
})
</script>