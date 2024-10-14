<template>
	<div class="keyword-rank-tracker-main">
		<div class="aioseo-settings-row aioseo-settings-row--summary">
			<keywords-summary/>
		</div>

		<div class="aioseo-settings-row aioseo-settings-row--graphs">
			<div class="settings-name">
				<div class="name">
					{{ strings.topPositions }}
				</div>
			</div>

			<keywords-graphs/>
		</div>

		<keywords-table :paginated-keywords="keywordRankTrackerStore.keywords.paginated"/>
	</div>
</template>

<script setup>
import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

import KeywordsGraphs from './KeywordsGraphs'
import KeywordsSummary from './KeywordsSummary'
import KeywordsTable from './KeywordsTable'

const td = import.meta.env.VITE_TEXTDOMAIN

const keywordRankTrackerStore = useKeywordRankTrackerStore()

const strings = {
	topPositions : __('Top Positions', td)
}
</script>

<style lang="scss" scoped>
.aioseo-settings-row {
	&--summary,
	&--graphs {
		--aioseo-gutter: 24px;
	}

	&--graphs {
		.name {
			--font-size: 16px
		}
	}
}
</style>