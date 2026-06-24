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
import { preloadOnIdle } from '@/vue/utils/preload'

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
		preloadOnIdle([
			() => import('./AboutUs.vue'),
			() => import('./GettingStarted.vue'),
			() => import('./AIOSEO_VERSION/LiteVsPro.vue')
		])
	}
}
</script>