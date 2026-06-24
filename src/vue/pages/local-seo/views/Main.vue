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
import { preloadOnIdle } from '@/vue/utils/preload'

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
		preloadOnIdle([
			() => import('./Locations.vue'),
			() => import('./Maps.vue'),
			() => import('./OpeningHours.vue'),
			() => import('./Import.vue')
		])
	}
}
</script>