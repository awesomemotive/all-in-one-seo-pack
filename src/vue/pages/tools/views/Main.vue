<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="showSaveButton"
	>
		<component :is="getRoute" />
	</core-main>
</template>

<script setup>
import { computed, defineAsyncComponent, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import {
	useRootStore
} from '@/vue/stores'

import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'
import { useRobotsTxt } from '@/vue/composables/RobotsTxt'

const rootStore = useRootStore()
const route  = useRoute()
const getRoute = computed(() => {
	switch (route.name) {
		case 'database-tools':
			return defineAsyncComponent(() => import('./DatabaseTools.vue'))
		case 'debug':
			return defineAsyncComponent(() => import('./AIOSEO_VERSION/Debug.vue'))
		case 'htaccess-editor':
			return defineAsyncComponent(() => import('./HtaccessEditor.vue'))
		case 'import-export':
			return defineAsyncComponent(() => import('./ImportExport.vue'))
		case 'system-status':
			return defineAsyncComponent(() => import('./SystemStatus.vue'))
		case 'wp-code':
			return defineAsyncComponent(() => import('./WpCode.vue'))
		default:
			return defineAsyncComponent(() => import('./RobotsEditor.vue'))
	}
})

useRobotsTxt()

const td = import.meta.env.VITE_TEXTDOMAIN

const showSaveButton = computed(() => {
	return 'system-status' !== route.name &&
		'import-export' !== route.name &&
		'database-tools' !== route.name &&
		'debug' !== route.name &&
		'wp-code' !== route.name
})

const strings = {
	pageName : rootStore.aioseo.data.isNetworkAdmin
		? __('Network Tools', td)
		: __('Tools', td)
}

// Preload other route components in the background
const preloadRouteComponents = () => {
	const loadComponents = () => {
		const currentRoute = route.name

		if ('database-tools' !== currentRoute) {
			import('./DatabaseTools.vue')
		}
		if ('debug' !== currentRoute) {
			import('./AIOSEO_VERSION/Debug.vue')
		}
		if ('htaccess-editor' !== currentRoute) {
			import('./HtaccessEditor.vue')
		}
		if ('import-export' !== currentRoute) {
			import('./ImportExport.vue')
		}
		if ('system-status' !== currentRoute) {
			import('./SystemStatus.vue')
		}
		if ('wp-code' !== currentRoute) {
			import('./WpCode.vue')
		}
		if ('robots-editor' !== currentRoute) {
			import('./RobotsEditor.vue')
		}
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