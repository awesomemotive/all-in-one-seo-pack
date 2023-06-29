<template>
	<div class="aioseo-search-statistics-keywords-graph">
		<graph
			:series="series"
			:loading="searchStatisticsStore.loading.keywords"
			:legend-style="legendStyle"
		/>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import Graph from '../Graph'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		Graph
	},
	props : {
		legendStyle : String
	},
	computed : {
		series () {
			if (!this.searchStatisticsStore.data?.keywords?.distribution || !this.searchStatisticsStore.data?.keywords?.distributionIntervals) {
				return []
			}

			const data  = this.searchStatisticsStore.data.keywords.distribution
			const graph = this.searchStatisticsStore.data.keywords.distributionIntervals

			return [ {
				name   : this.$t.__('Top 3 Position', this.$td),
				data   : graph.map((tick) => ({ x: tick.date, y: tick.top3 })),
				legend : {
					total : data.top3 || '0'
				}
			}, {
				name   : this.$t.__('4-10 Position', this.$td),
				data   : graph.map((tick) => ({ x: tick.date, y: tick.top10 })),
				legend : {
					total : data.top10 || '0'
				}
			}, {
				name   : this.$t.__('11-50 Position', this.$td),
				data   : graph.map((tick) => ({ x: tick.date, y: tick.top50 })),
				legend : {
					total : data.top50 || '0'
				}
			}, {
				name   : this.$t.__('50-100 Position', this.$td),
				data   : graph.map((tick) => ({ x: tick.date, y: tick.top100 })),
				legend : {
					total : data.top100 || '0'
				}
			} ]
		}
	}
}
</script>