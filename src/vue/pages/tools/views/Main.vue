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

import SeoAlerts from './SeoAlerts'
import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'
import { preloadOnIdle } from '@/vue/utils/preload'
import { useRobotsTxt } from '@/vue/composables/RobotsTxt'

const rootStore = useRootStore()
const route  = useRoute()
const getRoute = computed(() => {
	switch (route.name) {
		case 'seo-alerts':
			return SeoAlerts
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

onMounted(() => {
	const currentRoute = route.name
	const thunks       = []
	if ('database-tools' !== currentRoute) thunks.push(() => import('./DatabaseTools.vue'))
	if ('debug' !== currentRoute) thunks.push(() => import('./AIOSEO_VERSION/Debug.vue'))
	if ('htaccess-editor' !== currentRoute) thunks.push(() => import('./HtaccessEditor.vue'))
	if ('import-export' !== currentRoute) thunks.push(() => import('./ImportExport.vue'))
	if ('system-status' !== currentRoute) thunks.push(() => import('./SystemStatus.vue'))
	if ('wp-code' !== currentRoute) thunks.push(() => import('./WpCode.vue'))
	if ('robots-editor' !== currentRoute) thunks.push(() => import('./RobotsEditor.vue'))
	preloadOnIdle(thunks)
})
</script>