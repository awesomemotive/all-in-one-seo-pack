<template>
	<div class="author-seo-cta">
		<eeat-cta v-if="shouldShowUpdate || shouldShowActivate"/>

		<eeat-cta-lite v-if="shouldShowLite"/>
	</div>
</template>

<script setup>
import EeatCta from './AIOSEO_VERSION/eeat/EeatCta'
import EeatCtaLite from './lite/eeat/EeatCta'
import { useAddonConditions } from '@/vue/composables/AddonConditions'

const {
	shouldShowActivate,
	shouldShowLite,
	shouldShowUpdate
} = useAddonConditions({
	addonSlug : 'aioseo-eeat'
})
</script>

<style>
.aioseo-app .author-seo-cta {
	position: relative;
}
</style>