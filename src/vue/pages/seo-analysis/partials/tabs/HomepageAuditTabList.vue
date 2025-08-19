<template>
	<div
		v-if="analyzerStore.analyzing"
		class="loading-container"
	>
		<core-loader dark />
	</div>

	<div v-else>
		<core-main-tabs
			v-if="tabs.length"
			:tabs="tabs"
      :showSaveButton="false"
      :active="analyzerStore.homepageAudit.activeTab"
      internal
      @changed="processChangeTab"
      skinny-tabs
    >
      <template #var-tab="{ tab }">
				<span
					class="icon"
					:class="tab.analyze.classColor"
				>
					<component :is="tab.icon" />
				</span>

        <span class="label">{{ tab.label }} ({{ tab.analyze.count || 0 }})</span>
      </template>
    </core-main-tabs>

    <core-seo-site-analysis-results
			v-if="analyzerStore.homeResults.results && tabs.length"
      :section="analyzerStore.homepageAudit.activeTab"
      :all-results="analyzerStore.homeResults.results"
      show-instructions
    />

		<div v-if="!tabs.length">
			<span>{{ strings.noResults }}</span>
		</div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import {
	useAnalyzerStore
} from '@/vue/stores'

import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreSeoSiteAnalysisResults from '@/vue/components/common/core/SeoSiteAnalysisResults'
import { getAuditTabs } from '@/vue/pages/seo-analysis/utils'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const analyzerStore = useAnalyzerStore()
const route = useRoute()

const internalDebounce = ref(false)

const tabs = computed(() => {
	const allTabs = getAuditTabs({
		error   : analyzerStore.homeResults.score ? analyzerStore.criticalCount('homepage') : 0,
		warning : analyzerStore.homeResults.score ? analyzerStore.recommendedCount('homepage') : 0,
		good    : analyzerStore.homeResults.score ? analyzerStore.goodCount('homepage') : 0
	})

	const validTabs = allTabs.filter(tab => 0 < tab.analyze.count)
	const queryTab = route?.query?.tab
	if (queryTab && validTabs.find(tab => tab.slug === queryTab)) {
		analyzerStore.changeHomepageAuditTab(queryTab)
	} else if (validTabs.length) {
		analyzerStore.changeHomepageAuditTab(validTabs[0].slug)
	}

	return validTabs
})

const strings = {
	noResults : __('No results found.', td)
}

function processChangeTab (value) {
	if (internalDebounce.value) {
		return
	}

	internalDebounce.value = true
	analyzerStore.changeHomepageAuditTab(value)

	// Debouncing a little here to save extra API calls.
	setTimeout(() => {
		internalDebounce.value = false
	}, 50)
}
</script>

<style lang="scss" scoped>
.loading-container {
	display: flex;
	justify-content: center;
	align-items: center;
	width: 100%;
	min-height: 100px;
}

.var-tab {
	 > .label {
		padding-left: 8px;
	 }

		> .icon {
		width: 16px;
		height: 16px;

		&.green {
			color: $green;
		}

		&.orange {
			color: $orange;
		}

		&.red {
			color: $red;
		}
	}
}
</style>