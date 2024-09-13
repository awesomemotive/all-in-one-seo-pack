<template>
	<div class="keyword-rank-tracker-graphs">
		<graph
			:series="areaSeries"
			:loading="loading"
			legend-style="simple"
		/>

		<graph
			:series="barSeries"
			:loading="loading"
			preset="keywordsDistribution"
		/>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import Graph from '../../partials/Graph'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const keywordRankTrackerStore = useKeywordRankTrackerStore()

const loading = computed(() => keywordRankTrackerStore.isFetchingStatistics)
const areaSeries = computed(() => {
	const distribution = keywordRankTrackerStore.keywords.statistics?.distribution
	const distributionIntervals = keywordRankTrackerStore.keywords.statistics?.distributionIntervals
	if (!distribution || !distributionIntervals) {
		return []
	}

	return [
		{
			name : __('Top 3 Position', td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top3 }))
		},
		{
			name : __('4-10 Position', td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top10 }))
		},
		{
			name : __('11-50 Position', td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top50 }))
		},
		{
			name : __('50-100 Position', td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top100 }))
		}
	]
})
const barSeries = computed(() => {
	const distribution = keywordRankTrackerStore.keywords.statistics?.distribution
	if (!distribution) {
		return []
	}

	return [
		{
			name : __('Keywords', td),
			data : [
				{
					x         : __('Top 3 Position', td),
					y         : distribution.top3,
					fillColor : '#005AE0'
				},
				{
					x         : __('4-10 Position', td),
					y         : distribution.top10,
					fillColor : '#00AA63'
				},
				{
					x         : __('11-50 Position', td),
					y         : distribution.top50,
					fillColor : '#F18200'
				},
				{
					x         : __('50-100 Position', td),
					y         : distribution.top100,
					fillColor : '#DF2A4A'
				}
			]
		}
	]
})
</script>

<style lang="scss" scoped>
.keyword-rank-tracker-graphs {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
	row-gap: 10px;
}
</style>