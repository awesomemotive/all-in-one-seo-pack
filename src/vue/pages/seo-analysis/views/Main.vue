<template>
	<core-main
		:page-name="strings.pageName"
		:show-save-button="false"
	>
		<component :is="$route.name" />
	</core-main>
</template>

<script>
import AnalyzeCompetitorSite from './AnalyzeCompetitorSite'
import CoreMain from '@/vue/components/common/core/main/Index'
import SeoAuditChecklist from './SeoAuditChecklist'
import HeadlineAnalyzer from './HeadlineAnalyzer'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		AnalyzeCompetitorSite,
		CoreMain,
		SeoAuditChecklist,
		HeadlineAnalyzer
	},
	data () {
		return {
			strings : {
				pageName : __('SEO Analysis', td)
			}
		}
	}
}
</script>