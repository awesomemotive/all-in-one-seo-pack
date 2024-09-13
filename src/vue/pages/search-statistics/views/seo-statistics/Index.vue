<template>
	<div class="aioseo-search-statistics-site-statistics">
		<grid-row>
			<grid-column>
				<core-card
					class="aioseo-seo-statistics-card"
					slug="seoPerformance"
					:header-text="strings.seoStatisticsCard"
					:toggles="false"
					no-slide
				>
					<template #tooltip>
						<span v-html="strings.seoStatisticsTooltip" />
					</template>

					<seo-statistics-overview
						:statistics="[ 'impressions', 'clicks', 'ctr', 'position' ]"
						:show-graph="false"
						view="side-by-side"
					/>

					<graph
						multi-axis
						:series="series"
						:loading="searchStatisticsStore.loading.seoStatistics"
						legend-style="simple"
						:timelineMarkers="searchStatisticsStore.data?.seoStatistics?.timelineMarkers"
					/>
				</core-card>

				<core-card
					slug="posts"
					:header-text="strings.contentCard"
					:toggles="false"
					no-slide
				>
					<template #tooltip>
						{{ strings.postsTooltip }}
					</template>

					<posts-table
						:posts="searchStatisticsStore.data?.seoStatistics?.pages?.paginated || defaultPages"
						:columns="[ 'postTitle','indexStatus', 'seoScore', 'clicksSortable', 'impressionsSortable', 'positionSortable' ]"
						:append-columns="{
							all        : 'diffPosition',
							topLosing  : 'diffDecay',
							topWinning : 'diffDecay'
						}"
						:isLoading="searchStatisticsStore.loading.seoStatistics"
						:initialFilter="initialTableFilter"
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
import Graph from '../partials/Graph'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import PostsTable from '../partials/PostsTable'
import SeoStatisticsOverview from '../partials/SeoStatisticsOverview'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreCard,
		Graph,
		GridColumn,
		GridRow,
		PostsTable,
		SeoStatisticsOverview
	},
	data () {
		return {
			initialTableFilter : '',
			strings            : {
				seoStatisticsCard    : __('SEO Statistics', td),
				seoStatisticsTooltip : __('The following SEO Statistics graphs are useful metrics for understanding the visibility of your website or pages in search results and can help you identify trends or changes over time.<br /><br />Note: This data is capped at the top 100 keywords per day to speed up processing and to help you prioritize your SEO efforts, so while the data may seem inconsistent with Google Search Console, this is intentional.', td),
				contentCard          : __('Content Performance', td),
				postsTooltip         : __('These lists can be useful for understanding the performance of specific pages or posts and identifying opportunities for improvement. For example, the top winning content may be good candidates for further optimization or promotion, while the top losing may need to be reevaluated and potentially updated.', td)
			},
			defaultPages : {
				rows   : [],
				totals : {
					page  : 0,
					pages : 0,
					total : 0
				}
			}
		}
	},
	computed : {
		series () {
			if (!this.searchStatisticsStore.data?.seoStatistics?.statistics || !this.searchStatisticsStore.data?.seoStatistics?.intervals) {
				return []
			}

			return [
				{
					name   : __('Search Impressions', td),
					data   : this.searchStatisticsStore.data.seoStatistics.intervals.map((tick) => ({ x: tick.date, y: tick.impressions })),
					legend : {
						total : this.searchStatisticsStore.data.seoStatistics.statistics.impressions
					}
				},
				{
					name   : __('Search Clicks', td),
					data   : this.searchStatisticsStore.data.seoStatistics.intervals.map((tick) => ({ x: tick.date, y: tick.clicks })),
					legend : {
						total : this.searchStatisticsStore.data.seoStatistics.statistics.clicks
					}
				}
			]
		}
	},
	beforeMount () {
		if (Object.keys(this.$route?.query).includes('tab')) {
			switch (this.$route.query.tab) {
				case 'TopLosingPages':
					this.initialTableFilter = 'topLosing'
					break
				case 'TopWinningPages':
					this.initialTableFilter = 'topWinning'
					break
				default:
					this.initialTableFilter = 'all'
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
.aioseo-search-statistics-site-statistics {
	.aioseo-seo-statistics-card {
		.header {
			.aioseo-tooltip .popper {
				max-width: 400px;
			}
		}
	}

	.aioseo-col {
		.aioseo-card {
			margin: 0 0 20px;
		}
	}

	.aioseo-graph-legend-simple {
		.apexcharts-legend {
			gap: 40px;
		}
	}
}
</style>