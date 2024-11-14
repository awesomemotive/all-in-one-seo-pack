<template>
	<div class="aioseo-search-statistics-keywords">
		<core-settings-row
			:name="strings.chooseYourKeywords"
			left-size="12"
			right-size="12"
			class="aioseo-settings-row--positions"
		>
			<template #name>
				{{ strings.keywordPositionsCard }}

				<core-tooltip>
					<svg-circle-question-mark/>

					<template #tooltip>
						<span v-html="strings.keywordPositionsTooltip"/>
					</template>
				</core-tooltip>
			</template>

			<template #content>
				<seo-statistics-overview
					:statistics="[ 'keywords', 'impressions', 'position' ]"
					:show-graph="false"
					view="side-by-side"
				/>

				<grid-row>
					<grid-column md="6">
						<keywords-graph legend-style="simple"/>
					</grid-column>

					<grid-column md="6">
						<keywords-distribution-graph/>
					</grid-column>
				</grid-row>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.keywordPerformanceCard"
			left-size="12"
			right-size="12"
			no-vertical-margin
			no-border
			class="aioseo-settings-row--performance"
		>
			<template #name>
				{{ strings.keywordPerformanceCard }}

				<core-tooltip>
					<svg-circle-question-mark/>

					<template #tooltip>
						<span v-html="strings.keywordPerformanceTooltip"/>
					</template>
				</core-tooltip>
			</template>

			<template #content>
				<keywords-table
					:keywords="searchStatisticsStore.data?.keywords?.paginated || defaultKeywords"
					:loading="searchStatisticsStore.loading.keywords"
					:columns="[ 'keywordSortable', 'clicksSortable', 'ctrSortable', 'impressionsSortable', 'positionSortable', 'buttons' ]"
					:append-columns="{
						all        : 'diffPosition',
						topLosing  : 'diffDecay',
						topWinning : 'diffDecay'
					}"
					:initialFilter="initialTableFilter"
					show-items-per-page
					show-table-footer
				/>
			</template>
		</core-settings-row>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import KeywordsDistributionGraph from '../partials/KeywordsDistributionGraph'
import KeywordsGraph from '../partials/KeywordsGraph'
import KeywordsTable from '../partials/KeywordsTable'
import SeoStatisticsOverview from '../partials/SeoStatisticsOverview'

import { __ } from '@/vue/plugins/translations'
import { removeParam } from '@/vue/utils/params'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreSettingsRow,
		CoreTooltip,
		GridColumn,
		GridRow,
		KeywordsDistributionGraph,
		KeywordsGraph,
		KeywordsTable,
		SeoStatisticsOverview,
		SvgCircleQuestionMark
	},
	data () {
		return {
			initialTableFilter : '',
			strings            : {
				keywordPositionsCard      : __('Keyword Positions', td),
				keywordPositionsTooltip   : __('This graph is a visual representation of how well <strong>keywords are ranking in search results over time</strong> based on their position and average CTR. This can help you understand the performance of keywords and identify any trends or fluctuations.', td),
				keywordPerformanceCard    : __('Keyword Performance', td),
				keywordPerformanceTooltip : __('This table displays the performance of keywords that your site ranks for over time, including metrics such as impressions, click-through rate, and average position in search results. It allows for easy analysis of how keywords are performing and identification of any underperforming keywords that may need to be optimized or replaced.', td)
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
	beforeMount () {
		const filterMap = {
			TopLosingKeywords  : 'topLosing',
			TopWinningKeywords : 'topWinning'
		}

		const params = new URLSearchParams(window.location?.search || '') || {}
		if (
			params.has('table-filter') ||
			this.$route?.query?.['table-filter']
		) {
			const tableFilter = params.get('table-filter') || this.$route.query['table-filter'] || 'all'

			this.initialTableFilter = filterMap[tableFilter]

			this.$route.query['table-filter'] = undefined

			removeParam('table-filter')
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
	.aioseo-settings-row {
		&--positions {
			margin-bottom: 24px;
			padding-bottom: 24px;
		}

		&--performance {
			--aioseo-gutter: 0;
		}

		.settings-name .name {
			--font-size: 16px;
		}
	}

	.track-keyword {
		&--tracked {
			color: $green;
		}
	}
}
</style>