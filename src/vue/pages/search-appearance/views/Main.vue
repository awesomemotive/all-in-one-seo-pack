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
import { useRobotsTxt } from '@/vue/composables/RobotsTxt'

useRobotsTxt()

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Advanced       : defineAsyncComponent(() => import('./Advanced.vue')),
		Archives       : defineAsyncComponent(() => import('./Archives.vue')),
		AuthorSeo      : defineAsyncComponent(() => import('./AuthorSeo.vue')),
		ContentTypes   : defineAsyncComponent(() => import('./ContentTypes.vue')),
		CoreMain,
		GlobalSettings : defineAsyncComponent(() => import('./GlobalSettings.vue')),
		Media          : defineAsyncComponent(() => import('./Media.vue')),
		Taxonomies     : defineAsyncComponent(() => import('./Taxonomies.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Search Appearance', td)
			}
		}
	},
	mounted () {
		// Preload all route components in the background
		const preloadComponents = () => {
			import('./Advanced.vue')
			import('./Archives.vue')
			import('./AuthorSeo.vue')
			import('./ContentTypes.vue')
			import('./GlobalSettings.vue')
			import('./Media.vue')
			import('./Taxonomies.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>