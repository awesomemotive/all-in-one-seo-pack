<template>
	<div class="aioseo-search-statistics-keywords-distribution-graph">
		<graph
			:series="series"
			:loading="searchStatisticsStore.loading.keywords"
			preset="keywordsDistribution"
		/>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Graph from './Graph'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		Graph
	},
	computed : {
		series () {
			if (!this.searchStatisticsStore.data?.keywords?.distribution) {
				return []
			}

			const data = this.searchStatisticsStore.data.keywords.distribution

			return [ {
				name : __('Keywords', td),
				data : [ {
					x         : __('Top 3 Position', td),
					y         : data.top3,
					fillColor : '#005AE0'
				},
				{
					x         : __('4-10 Position', td),
					y         : data.top10,
					fillColor : '#00AA63'
				},
				{
					x         : __('11-50 Position', td),
					y         : data.top50,
					fillColor : '#F18200'
				},
				{
					x         : __('50-100 Position', td),
					y         : data.top100,
					fillColor : '#DF2A4A'
				} ]
			} ]
		}
	}
}
</script>