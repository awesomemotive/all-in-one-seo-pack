<template>
	<core-main
		:page-name="strings.pageName"
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
		CoreMain,
		Locations    : defineAsyncComponent(() => import('./Locations.vue')),
		Maps         : defineAsyncComponent(() => import('./Maps.vue')),
		OpeningHours : defineAsyncComponent(() => import('./OpeningHours.vue')),
		Import       : defineAsyncComponent(() => import('./Import.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Local SEO', td)
			}
		}
	},
	mounted () {
		// Preload all route components in the background
		const preloadComponents = () => {
			import('./Locations.vue')
			import('./Maps.vue')
			import('./OpeningHours.vue')
			import('./Import.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>