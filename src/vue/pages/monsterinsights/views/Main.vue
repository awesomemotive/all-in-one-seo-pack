<template>
	<core-main
		:page-name="strings.pageName"
		:show-tabs="false"
		:show-save-button="false"
	>
		<monsterinsights />
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
		Monsterinsights : defineAsyncComponent(() => import('./Monsterinsights.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Analytics', td)
			}
		}
	},
	mounted () {
		// Preload component in the background
		const preloadComponents = () => {
			import('./Monsterinsights.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>