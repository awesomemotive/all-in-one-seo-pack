<template>
	<redirects-main />
</template>

<script setup>
import { defineAsyncComponent, onMounted } from 'vue'

import { preloadOnIdle } from '@/vue/utils/preload'

const RedirectsMain = defineAsyncComponent(() => import('./AIOSEO_VERSION/Main.vue'))

onMounted(() => {
	preloadOnIdle([
		() => import('./AIOSEO_VERSION/Main.vue')
	])
})
</script>