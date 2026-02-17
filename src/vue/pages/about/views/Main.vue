<template>
	<core-main
		:page-name="strings.pageName"
		:showSaveButton="false"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import { defineAsyncComponent } from 'vue'

import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		AboutUs        : defineAsyncComponent(() => import('./AboutUs.vue')),
		CoreMain,
		GettingStarted : defineAsyncComponent(() => import('./GettingStarted.vue')),
		LiteVsPro      : defineAsyncComponent(() => import('./AIOSEO_VERSION/LiteVsPro.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('About Us', td)
			}
		}
	},
	mounted () {
		// Preload all route components in the background
		const preloadComponents = () => {
			import('./AboutUs.vue')
			import('./GettingStarted.vue')
			import('./AIOSEO_VERSION/LiteVsPro.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>