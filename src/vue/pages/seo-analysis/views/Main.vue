<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="$route.name === 'seo-site-audit' && analyzerStore.activeTab === 'settings-audit'"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import {
	useAnalyzerStore,
	useRootStore,
	useLicenseStore
} from '@/vue/stores'

import { defineAsyncComponent } from 'vue'

import CoreMain from '@/vue/components/common/core/main/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const rootStore   = useRootStore()
		const licenseStore = useLicenseStore()
		const analyzerStore = useAnalyzerStore()

		return {
			rootStore,
			licenseStore,
			analyzerStore
		}
	},
	components : {
		AnalyzeCompetitorSite : defineAsyncComponent(() => import('./AnalyzeCompetitorSite.vue')),
		CoreMain,
		SeoHomepageAudit      : defineAsyncComponent(() => import('./SeoHomepageAudit.vue')),
		SeoSiteAudit          : defineAsyncComponent(() => import('./AIOSEO_VERSION/SeoSiteAudit.vue')),
		HeadlineAnalyzer      : defineAsyncComponent(() => import('./HeadlineAnalyzer.vue'))
	},
	data () {
		return {
			strings : {
				pageName : __('SEO Analysis', td)
			}
		}
	},
	mounted () {
		if (
			this.rootStore.isPro &&
			!this.licenseStore.isUnlicensed &&
			100 !== this.analyzerStore.objectsScan.percent
		) {
			this.analyzerStore.fetchObjectsScanPercent()
		}

		// Preload all route components in the background
		const preloadComponents = () => {
			import('./AnalyzeCompetitorSite.vue')
			import('./SeoHomepageAudit.vue')
			import('./AIOSEO_VERSION/SeoSiteAudit.vue')
			import('./HeadlineAnalyzer.vue')
		}

		if ('requestIdleCallback' in window) {
			requestIdleCallback(preloadComponents)
		} else {
			setTimeout(preloadComponents, 1)
		}
	}
}
</script>