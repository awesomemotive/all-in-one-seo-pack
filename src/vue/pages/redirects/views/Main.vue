<template>
	<redirects-main />
</template>

<script setup>
import { defineAsyncComponent, onMounted } from 'vue'

const RedirectsMain = defineAsyncComponent(() => import('./AIOSEO_VERSION/Main.vue'))

// Preload the component in the background
onMounted(() => {
	const preloadComponents = () => {
		import('./AIOSEO_VERSION/Main.vue')
	}

	if ('requestIdleCallback' in window) {
		requestIdleCallback(preloadComponents)
	} else {
		setTimeout(preloadComponents, 1)
	}
})
</script>