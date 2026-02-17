<template>
	<div class="aioseo-ai-insights">
		<core-main
			:page-name="pageName"
			:show-tabs="showTabs"
			:show-save-button="showSaveButton"
		>
			<component :is="getRoute" />
		</core-main>
	</div>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const route = useRoute()

const pageName = __('AI Suite', td)
const showTabs = computed(() => {
	// Hide tabs when viewing a report (UUID param exists)
	if (route.params?.uuid) {
		return false
	}
	return false !== route.meta?.showTabs
})
const showSaveButton = computed(() => false !== route.meta?.showSaveButton)

const getRoute = computed(() => {
	// Otherwise, route based on route name
	switch (route.name) {
		case 'ai-content':
			return defineAsyncComponent(() => import('./AiContent.vue'))
		case 'keyword-reports':
			return route.params?.uuid
				? defineAsyncComponent(() => import('./keyword-reports/Report.vue'))
				: defineAsyncComponent(() => import('./keyword-reports/Index.vue'))
		case 'brand-tracker':
			return defineAsyncComponent(() => import('./BrandTracker.vue'))
		default:
			return defineAsyncComponent(() => import('./keyword-reports/Index.vue'))
	}
})

// Preload other route components in the background
const preloadRouteComponents = () => {
	const loadComponents = () => {
		// Always preload all route views
		import('./AiContent.vue')
		import('./keyword-reports/Index.vue')
		import('./keyword-reports/Report.vue')
		import('./BrandTracker.vue')
	}

	if ('requestIdleCallback' in window) {
		requestIdleCallback(loadComponents)
	} else {
		setTimeout(loadComponents, 1)
	}
}

onMounted(() => {
	preloadRouteComponents()
})
</script>