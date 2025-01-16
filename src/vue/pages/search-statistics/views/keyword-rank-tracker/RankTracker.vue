<template>
	<div>
		<keyword-rank-tracker-tabs
			:active-tab="activeTab"
			@update:activeTab="activeTab = $event"
		>
			<template #tab-content>
				<component
					:is="keywordRankTrackerStore[activeTab].count ? (activeTab === 'keywords' ? Keywords : Groups) : EmptyState"
					:context="activeTab"
				/>
			</template>
		</keyword-rank-tracker-tabs>
	</div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'

import {
	useKeywordRankTrackerStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import EmptyState from './partials/pro/EmptyState'
import Groups from './partials/pro/Groups'
import KeywordRankTrackerTabs from './partials/Tabs'
import Keywords from './partials/Keywords'

import { removeParam } from '@/vue/utils/params'

const keywordRankTrackerStore = useKeywordRankTrackerStore()
const searchStatisticsStore   = useSearchStatisticsStore()

const activeTab = ref('keywords')

onBeforeMount(() => {
	if (
		searchStatisticsStore.isConnected &&
		!searchStatisticsStore.shouldShowSampleReports &&
		!keywordRankTrackerStore.keywords.all.rows.length
	) {
		try {
			const params = new URLSearchParams(document.location.search) || {}
			if (params?.get('search')) {
				keywordRankTrackerStore.keywords.paginated.searchTerm = params.get('search')

				removeParam('search')
			}

			keywordRankTrackerStore.maybeUpdateKeywords()
			keywordRankTrackerStore.maybeUpdateGroups()
		} catch (error) {
			console.error(error)
		}
	}
})
</script>