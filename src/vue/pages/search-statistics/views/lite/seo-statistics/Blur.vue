<template>
	<core-blur>
		<div class="aioseo-search-statistics-dashboard">
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
							legend-style="simple"
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
							:columns="[ 'postTitle', 'indexStatus', 'seoScore', 'clicksSortable', 'impressionsSortable', 'positionSortable', 'diffPositionSortable' ]"
							show-items-per-page
							show-table-footer
						/>
					</core-card>
				</grid-column>
			</grid-row>
		</div>
	</core-blur>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'
import Graph from '../../partials/Graph'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import PostsTable from '../../partials/PostsTable'
import SeoStatisticsOverview from '../../partials/SeoStatisticsOverview'
export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreBlur,
		CoreCard,
		Graph,
		GridColumn,
		GridRow,
		PostsTable,
		SeoStatisticsOverview
	},
	data () {
		return {
			strings : {
				seoStatisticsCard    : this.$t.__('SEO Statistics', this.$td),
				seoStatisticsTooltip : this.$t.__('The following SEO Statistics graphs are useful metrics for understanding the visibility of your website or pages in search results and can help you identify trends or changes over time.<br /><br />Note: This data is capped at the top 100 keywords per day to speed up processing and to help you prioritize your SEO efforts, so while the data may seem inconsistent with Google Search Console, this is intentional.', this.$td),
				contentCard          : this.$t.__('Content', this.$td),
				postsTooltip         : this.$t.__('These lists can be useful for understanding the performance of specific pages or posts and identifying opportunities for improvement. For example, the top winning content may be good candidates for further optimization or promotion, while the top losing may need to be reevaluated and potentially updated.', this.$td)
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
					name   : this.$t.__('Search Impressions', this.$td),
					data   : this.searchStatisticsStore.data.seoStatistics.intervals.map((tick) => ({ x: tick.date, y: tick.impressions })),
					legend : {
						total : this.searchStatisticsStore.data.seoStatistics.statistics.impressions
					}
				},
				{
					name   : this.$t.__('Search Clicks', this.$td),
					data   : this.searchStatisticsStore.data.seoStatistics.intervals.map((tick) => ({ x: tick.date, y: tick.clicks })),
					legend : {
						total : this.searchStatisticsStore.data.seoStatistics.statistics.clicks
					}
				}
			]
		}
	}
}
</script>