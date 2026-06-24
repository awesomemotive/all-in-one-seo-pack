<template>
	<div class="wrap">
		<seo-revisions v-if="!isUnlicensed"/>

		<seo-revisions-lite v-else />
	</div>
</template>

<script setup>
import {
	useLicenseStore
} from '@/vue/stores'

import { defineAsyncComponent, onMounted } from 'vue'
import { storeToRefs } from 'pinia'

import { preloadOnIdle } from '@/vue/utils/preload'

const SeoRevisions = defineAsyncComponent(() => import('./AIOSEO_VERSION/Index.vue'))
const SeoRevisionsLite = defineAsyncComponent(() => import('./lite/Index.vue'))

const { isUnlicensed } = storeToRefs(useLicenseStore())

onMounted(() => {
	preloadOnIdle([
		() => import('./AIOSEO_VERSION/Index.vue'),
		() => import('./lite/Index.vue')
	])
})
</script>