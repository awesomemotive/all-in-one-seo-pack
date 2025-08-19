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

import AnalyzeCompetitorSite from './AnalyzeCompetitorSite'
import CoreMain from '@/vue/components/common/core/main/Index'
import SeoHomepageAudit from './SeoHomepageAudit'
import SeoSiteAudit from './AIOSEO_VERSION/SeoSiteAudit'
import HeadlineAnalyzer from './HeadlineAnalyzer'

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
		AnalyzeCompetitorSite,
		CoreMain,
		SeoHomepageAudit,
		SeoSiteAudit,
		HeadlineAnalyzer
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
	}
}
</script>