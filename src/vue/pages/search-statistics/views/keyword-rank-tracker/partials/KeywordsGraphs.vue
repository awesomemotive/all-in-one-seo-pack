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
import { computed, inject } from 'vue'

import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import Graph from '../../partials/Graph'

const $t = inject('$t')
const $td = inject('$td')

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
			name : $t.__('Top 3 Position', $td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top3 }))
		},
		{
			name : $t.__('4-10 Position', $td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top10 }))
		},
		{
			name : $t.__('11-50 Position', $td),
			data : distributionIntervals.map((tick) => ({ x: tick.date, y: tick.top50 }))
		},
		{
			name : $t.__('50-100 Position', $td),
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
			name : $t.__('Keywords', $td),
			data : [
				{
					x         : $t.__('Top 3 Position', $td),
					y         : distribution.top3,
					fillColor : '#005AE0'
				},
				{
					x         : $t.__('4-10 Position', $td),
					y         : distribution.top10,
					fillColor : '#00AA63'
				},
				{
					x         : $t.__('11-50 Position', $td),
					y         : distribution.top50,
					fillColor : '#F18200'
				},
				{
					x         : $t.__('50-100 Position', $td),
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