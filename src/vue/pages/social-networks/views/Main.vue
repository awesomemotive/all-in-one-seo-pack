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
		Facebook       : defineAsyncComponent(() => import('./Facebook.vue')),
		Pinterest      : defineAsyncComponent(() => import('./Pinterest.vue')),
		SocialProfiles : defineAsyncComponent(() => import('./SocialProfiles.vue')),
		Twitter        : defineAsyncComponent(() => import('./Twitter.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Social Networks', td)
			}
		}
	},
	mounted () {
		preloadOnIdle([
			() => import('./Facebook.vue'),
			() => import('./Pinterest.vue'),
			() => import('./SocialProfiles.vue'),
			() => import('./Twitter.vue')
		])
	}
}
</script>