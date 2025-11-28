<template>
	<div class="aioseo-ai-insights">
		<core-main
			:page-name="pageName"
			:show-tabs="showTabs"
			:show-save-button="showSaveButton"
		>
			<component :is="getRoute" />
		</core-main>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import CoreMain from '@/vue/components/common/core/main/Index'
import BrandTracker from './BrandTracker.vue'
import KeywordReports from './keyword-reports/Index.vue'
import KeywordReport from './keyword-reports/Report.vue'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const route = useRoute()

const pageName = __('AI Insights', td)
const showTabs = computed(() => {
	// Hide tabs when viewing a report (UUID param exists)
	if (route.params?.uuid) {
		return false
	}
	return false !== route.meta?.showTabs
})
const showSaveButton = computed(() => false !== route.meta?.showSaveButton)

const getRoute = computed(() => {
	// Otherwise, route based on route name
	switch (route.name) {
		case 'keyword-reports':
			return route.params?.uuid ? KeywordReport : KeywordReports
		case 'brand-tracker':
			return BrandTracker
		default:
			return KeywordReports
	}
})
</script>