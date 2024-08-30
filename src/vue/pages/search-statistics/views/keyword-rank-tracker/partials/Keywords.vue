<template>
	<div class="keyword-rank-tracker-main">
		<keywords-summary/>

		<div class="aioseo-settings-row">
			<keywords-graphs/>
		</div>

		<keywords-table :paginated-keywords="keywordRankTrackerStore.keywords.paginated"/>
	</div>
</template>

<script setup>
import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import KeywordsGraphs from './KeywordsGraphs'
import KeywordsSummary from './KeywordsSummary'
import KeywordsTable from './KeywordsTable'

const keywordRankTrackerStore = useKeywordRankTrackerStore()
</script>