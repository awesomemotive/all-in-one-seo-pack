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
		GeneralSitemap : defineAsyncComponent(() => import('./GeneralSitemap.vue')),
		HtmlSitemap    : defineAsyncComponent(() => import('./HtmlSitemap.vue')),
		NewsSitemap    : defineAsyncComponent(() => import('./NewsSitemap.vue')),
		RssSitemap     : defineAsyncComponent(() => import('./RssSitemap.vue')),
		VideoSitemap   : defineAsyncComponent(() => import('./VideoSitemap.vue')),
		LlmsSitemap    : defineAsyncComponent(() => import('./LlmsSitemap.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('Sitemaps', td)
			}
		}
	},
	mounted () {
		// Preload all route components in the background
		const preloadComponents = () => {
			import('./GeneralSitemap.vue')
			import('./HtmlSitemap.vue')
			import('./NewsSitemap.vue')
			import('./RssSitemap.vue')
			import('./VideoSitemap.vue')
			import('./LlmsSitemap.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>

<style lang="scss">
.aioseo-app {
	.index-notice,
	.static-regeneration-notice {
		margin-top: 10px;
	}
}
</style>