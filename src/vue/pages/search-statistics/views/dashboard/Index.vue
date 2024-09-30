<template>
	<div class="aioseo-search-statistics-dashboard">
		<grid-row>
			<grid-column md="6">
				<core-card
					class="aioseo-seo-statistics-card"
					slug="seoStatistics"
					:header-text="strings.statisticsCard"
					:toggles="false"
					no-slide
				>
					<template #header-icon>
						<svg-statistics />
					</template>

					<template #tooltip>
						<span>
							{{ strings.statisticsTooltip }}
						</span>
					</template>

					<seo-statistics-overview
						:statistics="[ 'impressions', 'clicks', 'ctr', 'position' ]"
					/>

					<card-footer buttonTarget="seo-statistics" />
				</core-card>

				<core-card
					slug="keywords"
					:header-text="strings.keywordsCard"
					:toggles="false"
					no-slide
				>
					<template #header-icon>
						<svg-query />
					</template>

					<template #tooltip>
						<span v-html="strings.keywordsTooltip" />
					</template>

					<template #tabs>
						<core-main-tabs
							:tabs="keywordsTabs"
							:showSaveButton="false"
							:active="activeKeywordsTab"
							@changed="value => activeKeywordsTab = value"
							internal
						/>
					</template>

					<transition name="route-fade" mode="out-in">
						<component :is="activeKeywordsTab"/>
					</transition>

					<card-footer
						buttonTarget="keyword-rank-tracker"
						:query="{tab:'AllKeywords'}"
					/>
				</core-card>
			</grid-column>

			<grid-column md="6">
				<core-card
					slug="keywordPositions"
					:header-text="strings.keywordPositionsCard"
					:toggles="false"
					no-slide
					class="aioseo-keyword-positions-card"
				>
					<template #header-icon>
						<svg-list />
					</template>

					<template #tooltip>
						<span v-html="strings.keywordPositionsTooltip" />
					</template>

					<keywords-graph legend-style="simple" />

					<card-footer
						buttonTarget="keyword-rank-tracker"
						:query="{tab:'AllKeywords'}"
					/>
				</core-card>

				<core-card
					slug="overallOptimization"
					:header-text="strings.overallOptimizationCard"
					:toggles="false"
					no-slide
				>
					<template #header-icon>
						<svg-speed />
					</template>

					<template #tooltip>
						{{ strings.overallOptimizationTooltip }}
					</template>

					<core-overview :toHide="['separator', 'blog']" />
				</core-card>
			</grid-column>
		</grid-row>

		<grid-row>
			<grid-column>
				<core-card
					slug="seoPostRanking"
					:header-text="strings.postRankingCard"
					:toggles="false"
					no-slide
				>
					<template #header-icon>
						<svg-web />
					</template>

					<template #tooltip>
						{{ strings.postRankingTooltip }}
					</template>

					<template #tabs>
						<core-main-tabs
							:tabs="postRankingTabs"
							:showSaveButton="false"
							:active="activePostRankingTab"
							@changed="value => activePostRankingTab = value"
							internal
						/>
					</template>

					<transition name="route-fade" mode="out-in">
						<component :is="activePostRankingTab"/>
					</transition>

					<card-footer
						buttonTarget="seo-statistics"
						:query="{ tab : activePostRankingTab }"
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

import CardFooter from '../partials/CardFooter'
import CoreCard from '@/vue/components/common/core/Card'
import CoreMainTabs from '@/vue/components/common/core/main/Tabs'
import CoreOverview from '@/vue/components/common/core/Overview'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import KeywordsGraph from '../partials/KeywordsGraph'
import SeoStatisticsOverview from '../partials/SeoStatisticsOverview'
import SvgList from '@/vue/components/common/svg/List'
import SvgQuery from '@/vue/components/common/svg/Query'
import SvgSpeed from '@/vue/components/common/svg/Speed'
import SvgStatistics from '@/vue/components/common/svg/Statistics'
import SvgWeb from '@/vue/components/common/svg/Web'

import TopKeywords from '../partials/TopKeywords'
import TopLosingPages from '../partials/TopLosingPages'
import TopPages from '../partials/TopPages'
import TopWinningPages from '../partials/TopWinningPages'
import WinningLosingKeywords from '../partials/WinningLosingKeywords'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CardFooter,
		CoreCard,
		CoreMainTabs,
		CoreOverview,
		GridColumn,
		GridRow,
		KeywordsGraph,
		SeoStatisticsOverview,
		SvgList,
		SvgQuery,
		SvgSpeed,
		SvgStatistics,
		SvgWeb,
		TopKeywords,
		TopLosingPages,
		TopPages,
		TopWinningPages,
		WinningLosingKeywords
	},
	data () {
		return {
			strings : {
				overallOptimizationCard    : __('Post Optimizations', td),
				overallOptimizationTooltip : __('Optimizing individual content for SEO is a low-hanging fruit of opportunity because it can often be done quickly and easily with a relatively small time investment. By making sure that each piece of content on your website is optimized for relevant keywords and follows best practices for on-page SEO, you can improve the visibility and ranking of that content in the search results. This can drive more traffic to your website and help you achieve better overall SEO results.', td),
				keywordsCard               : __('Keyword Rankings', td),
				keywordsTooltip            : __('These lists can be useful for understanding the performance of specific keywords and identifying opportunities for improvement. For example, the top winning keywords may be good candidates for further optimization or promotion, while the top losing keywords may need to be reevaluated and potentially modified or replaced.<br /><br />Note: This data is capped at the top 100 keywords per day to speed up processing and to help you prioritize your SEO efforts.', td),
				keywordPositionsCard       : __('Keyword Positions', td),
				keywordPositionsTooltip    : __('This graph is a visual representation of how well <strong>keywords are ranking in search results over time</strong> based on their position and average CTR. This can help you understand the performance of keywords and identify any trends or fluctuations.', td),
				statisticsCard             : __('SEO Statistics', td),
				statisticsTooltip          : __('The following SEO Statistics graphs are useful metrics for understanding the visibility of your website or pages in search results and can help you identify trends or changes over time.', td),
				postRankingCard            : __('Content Performance', td),
				postRankingTooltip         : __('These lists can be useful for understanding the performance of specific pages or posts and identifying opportunities for improvement. For example, the top winning content may be good candidates for further optimization or promotion, while the top losing may need to be reevaluated and potentially updated.', td)
			},
			activeKeywordsTab    : 'TopKeywords',
			activePostRankingTab : 'TopPages',
			keywordsTabs         : [
				{
					slug : 'TopKeywords',
					name : __('Top Keywords', td)
				},
				{
					slug : 'WinningLosingKeywords',
					name : __('Winning / Losing', td)
				}
			],
			postRankingTabs : [
				{
					slug : 'TopPages',
					name : __('Top Pages', td)
				},
				{
					slug : 'TopLosingPages',
					name : __('Top Losing', td)
				},
				{
					slug : 'TopWinningPages',
					name : __('Top Winning', td)
				}
			]
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
.aioseo-search-statistics-dashboard {
	.aioseo-seo-statistics-card {
		.header {
			.aioseo-tooltip .popper {
				max-width: 400px;
			}
		}
	}

	> .aioseo-row {
		> .aioseo-col {
			display: flex;
			flex-direction: column;

			.aioseo-card {
				flex: 1;
			}
		}
	}

	.aioseo-card.aioseo-keyword-positions-card {
		.content {
			padding-top: 0;
		}
	}

	.aioseo-search-statistics-keywords-graph {
		margin-bottom: 20px;
	}
}
</style>