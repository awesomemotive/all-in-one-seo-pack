<template>
	<div class="aioseo-search-statistics-keywords">
		<grid-row>
			<grid-column>
				<core-card
					slug="keywordPositions"
					:header-text="strings.keywordPositionsCard"
					:toggles="false"
					no-slide
				>
					<template #tooltip>
						<span v-html="strings.keywordPositionsTooltip" />
					</template>

					<seo-statistics-overview
						:statistics="[ 'keywords', 'impressions', 'position' ]"
						:show-graph="false"
						view="side-by-side"
					/>

					<grid-row>
						<grid-column md="6">
							<keywords-graph legend-style="simple" />
						</grid-column>

						<grid-column md="6">
							<keywords-distribution-graph />
						</grid-column>
					</grid-row>
				</core-card>

				<core-card
					slug="keywordPerformance"
					:header-text="strings.keywordPerformanceCard"
					:toggles="false"
					no-slide
				>
					<template #tooltip>
						<span v-html="strings.keywordPerformanceTooltip" />
					</template>

					<keywords-table
						:keywords="searchStatisticsStore.data?.keywords?.paginated || defaultKeywords"
						:loading="searchStatisticsStore.loading.keywords"
						:columns="[ 'keywordSortable', 'clicksSortable', 'ctrSortable', 'impressionsSortable', 'positionSortable', 'buttons' ]"
						:append-columns="{
							all        : 'diffPosition',
							topLosing  : 'diffDecay',
							topWinning : 'diffDecay'
						}"
						show-items-per-page
						show-table-footer
					/>
				</core-card>
			</grid-column>
		</grid-row>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import KeywordsDistributionGraph from '../partials/KeywordsDistributionGraph'
import KeywordsGraph from '../partials/KeywordsGraph'
import KeywordsTable from '../partials/KeywordsTable'
import SeoStatisticsOverview from '../partials/SeoStatisticsOverview'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreCard,
		GridColumn,
		GridRow,
		KeywordsDistributionGraph,
		KeywordsGraph,
		KeywordsTable,
		SeoStatisticsOverview
	},
	data () {
		return {
			strings : {
				keywordPositionsCard      : this.$t.__('Keyword Positions', this.$td),
				keywordPositionsTooltip   : this.$t.__('This graph is a visual representation of how well <strong>keywords are ranking in search results over time</strong> based on their position and average CTR. This can help you understand the performance of keywords and identify any trends or fluctuations.', this.$td),
				keywordPerformanceCard    : this.$t.__('Keyword Performance', this.$td),
				keywordPerformanceTooltip : this.$t.__('This table displays the performance of keywords that your site ranks for over time, including metrics such as impressions, click-through rate, and average position in search results. It allows for easy analysis of how keywords are performing and identification of any underperforming keywords that may need to be optimized or replaced.', this.$td)
			},
			defaultKeywords : {
				rows   : [],
				totals : {
					page  : 0,
					pages : 0,
					total : 0
				}
			}
		}
	},
	mounted () {
		if (this.searchStatisticsStore.isConnected) {
			this.searchStatisticsStore.loadInitialData()
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-keywords {
	.track-keyword {
		&--tracked {
			color: $green;
		}
	}
}
</style>