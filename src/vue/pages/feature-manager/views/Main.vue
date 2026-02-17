<template>
	<core-main
		:page-name="strings.pageName"
		:show-tabs="false"
		:show-save-button="false"
	>
		<feature-manager />
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
		FeatureManager : defineAsyncComponent(() => import('./FeatureManager.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Feature Manager', td)
			}
		}
	},
	mounted () {
		// Preload component in the background
		const preloadComponents = () => {
			import('./FeatureManager.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>