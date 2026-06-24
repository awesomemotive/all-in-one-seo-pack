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
import { preloadOnIdle } from '@/vue/utils/preload'

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
		preloadOnIdle([
			() => import('./FeatureManager.vue')
		])
	}
}
</script>