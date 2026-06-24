<template>
	<core-main
		:page-name="strings.pageName"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import {
	useRootStore
} from '@/vue/stores'

import { defineAsyncComponent } from 'vue'

import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'
import { preloadOnIdle } from '@/vue/utils/preload'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			rootStore : useRootStore()
		}
	},
	components : {
		AccessControl    : defineAsyncComponent(() => import('./AccessControl.vue')),
		Advanced         : defineAsyncComponent(() => import('./Advanced.vue')),
		Breadcrumbs      : defineAsyncComponent(() => import('./Breadcrumbs.vue')),
		CoreMain,
		GeneralSettings  : defineAsyncComponent(() => import('./GeneralSettings.vue')),
		RssContent       : defineAsyncComponent(() => import('./RssContent.vue')),
		SeoChecklist     : defineAsyncComponent(() => import('./SeoChecklist.vue')),
		WebmasterTools   : defineAsyncComponent(() => import('./WebmasterTools.vue')),
		WritingAssistant : defineAsyncComponent(() => import('./WritingAssistant.vue'))
	},
	data () {
		return {
			strings : {
				pageName : this.rootStore.aioseo.data.isNetworkAdmin
					? __('Network Settings', td)
					: __('General Settings', td)
			}
		}
	},
	mounted () {
		preloadOnIdle([
			() => import('./AccessControl.vue'),
			() => import('./Advanced.vue'),
			() => import('./Breadcrumbs.vue'),
			() => import('./GeneralSettings.vue'),
			() => import('./RssContent.vue'),
			() => import('./SeoChecklist.vue'),
			() => import('./WebmasterTools.vue'),
			() => import('./WritingAssistant.vue')
		])
	}
}
</script>