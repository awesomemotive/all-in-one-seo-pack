<template>
	<div
		class="aioseo-seo-statistics-overview"
		:class="{
			[view] : true,
			'hide-graph' : !showGraph
		}"
		:style="style"
	>
		<div
			v-for="(statistics, index) in seoStatistics"
			:key="index"
			class="statistics"
		>
			<div
				:class="[{
					blurred : searchStatisticsStore.loading.seoStatistics,
				}]"
			>
				<div class="statistics-title">
					{{ statistics.label }}

					<core-tooltip v-if="statistics.tooltip">
						<svg-circle-question-mark />

						<template #tooltip>
							<span v-html="statistics.tooltip" />
						</template>
					</core-tooltip>
				</div>

				<div class="statistics-current">
					<div class="statistics-current-total">
						{{ formatStatistic(statistics.name, statistics.data.total) }}
					</div>

					<statistic
						class="statistics-current-difference"
						:class="'statistics-current-difference--' + statistics.data.direction"
						:difference="statistics.data.difference"
						:type="statistics.name"
						:showCurrent="false"
						tooltip-offset="-90px,0"
					/>
				</div>

				<div
					v-if="showGraph"
					class="statistics-chart"
				>
					<graph
						:series="[{ name: statistics.label, data: statistics.data.chart }]"
						:height="60"
						preset="overview"
						:invert-y-axis="'position' === statistics.name"
					/>
				</div>
			</div>

			<core-loader v-if="searchStatisticsStore.loading.seoStatistics" dark />
		</div>
	</div>
</template>

<script>
import {
	useRootStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import StatisticMixin from '../../mixins/Statistic.js'
import dateFormat from '@/vue/utils/dateFormat'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Graph from './Graph'
import Statistic from './Statistic'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
export default {
	setup () {
		return {
			rootStore             : useRootStore(),
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreLoader,
		CoreTooltip,
		Graph,
		Statistic,
		SvgCircleQuestionMark
	},
	mixins : [ StatisticMixin ],
	data () {
		return {
			statisticsStrings : [
				{
					name    : 'impressions',
					label   : this.$t.__('Search Impressions', this.$td),
					tooltip : this.$t.sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
						this.$t.__('The %1$stotal number of times your website appeared in search results%2$s within the selected timeframe.', this.$td),
						'<strong>',
						'</strong>'
					)
				},
				{
					name    : 'clicks',
					label   : this.$t.__('Total Clicks', this.$td),
					tooltip : this.$t.sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
						this.$t.__('The %1$stotal number of clicks that your website received from search results%2$s within the selected timeframe.', this.$td),
						'<strong>',
						'</strong>'
					)
				},
				{
					name    : 'ctr',
					label   : this.$t.__('Avg. CTR', this.$td),
					tooltip : this.$t.sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
						this.$t.__('The %1$saverage click-through rate of your content in search results%2$s within the selected timeframe.', this.$td),
						'<strong>',
						'</strong>'
					)
				},
				{
					name    : 'position',
					label   : this.$t.__('Avg. Position', this.$td),
					tooltip : this.$t.sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
						this.$t.__('The %1$saverage position of your content in search results%2$s within the selected timeframe.', this.$td),
						'<strong>',
						'</strong>'
					)
				},
				{
					name    : 'keywords',
					label   : this.$t.__('Total Keywords', this.$td),
					tooltip : this.$t.sprintf(
						// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
						this.$t.__('The %1$stotal number of keywords that your website ranks for in search results%2$s within the selected timeframe.', this.$td),
						'<strong>',
						'</strong>'
					)
				}
			]
		}
	},
	props : {
		statistics : {
			type : Array,
			default () {
				return []
			}
		},
		statisticsData : {
			type : Object,
			default () {
				return null
			}
		},
		view : {
			type    : String,
			default : 'grid'
		},
		showGraph : {
			type    : Boolean,
			default : true
		}
	},
	computed : {
		seoStatistics () {
			const statistics = []
			this.statistics.forEach(key => {
				const statisticObject = this.statisticsStrings.find(stats => stats.name === key)
				if (statisticObject) {
					statistics.push({
						...statisticObject,
						data : this.getData(key)
					})
				}
			})

			return statistics
		},
		style () {
			const styles = []
			switch (this.view) {
				case 'side-by-side':
					styles.push({
						'grid-template-columns' : `repeat(${this.statistics.length}, 1fr)`
					})
					break
				case 'grid':
					styles.push({
						'grid-template-columns' : `repeat(${Math.ceil(this.statistics.length / 2)}, 1fr)`,
						'grid-template-rows'    : `repeat(${Math.ceil(this.statistics.length / 2)}, 1fr)`
					})
					break
			}

			return styles
		}
	},
	methods : {
		getData (key) {
			const data = this.statisticsData
				? this.statisticsData
				: this.searchStatisticsStore.data?.seoStatistics?.statistics

			if (!data) {
				return {
					total      : 0,
					difference : 0,
					direction  : 'up',
					chart      : []
				}
			}

			return {
				total      : data[key] || 0,
				difference : data.difference ? Math.abs(data.difference[key]) || 0 : 0,
				direction  : data.difference && 0 > data.difference[key] ? 'down' : 'up',
				chart      : this.searchStatisticsStore.data?.seoStatistics?.intervals?.map((tick) => ({
					x : dateFormat(new Date(tick.date + ' 00:00:00'), this.rootStore.aioseo.data.dateFormat),
					y : tick[key] ? tick[key] : 0
				}))
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-statistics-overview {
	max-width: 1000px;
	margin: 0 auto;

	.statistics {
		position: relative;

		&-title {
			flex: 1 1 auto;
			display: flex;
			align-items: center;
			margin-bottom: 14px;
		}

		&-current {
			display: flex;
			align-items: center;
			font-weight: 700;
			font-size: 28px;
			color: $black2-hover;
			margin-bottom: 16px;

			&-total {
				flex: 1 1 auto;
			}

			&-difference {
				display: flex;
				align-items: center;
				font-size: 16px;

				&-direction {
					display: flex;
					margin-right: 9px;

					svg {
						vertical-align: middle;
						width: 11px;
						height: 6px;
					}
				}

				&--up {
					color: $green;
				}

				&--down {
					color: $red;
				}
			}
		}

		.blurred {
			filter: blur(2px);
			pointer-events: none;
			user-select: none;
		}
	}

	&.hide-graph {
		.statistics {
			&-current {
				&-total {
					max-width: 100px;
				}
			}
		}
	}

	&.grid {
		background-color: $border;
		grid-gap: 1px;
		display: grid;
		$gapSize: 15px;

		.statistics {
			background-color: $white;

			&:nth-child(odd) {
				padding-right: $gapSize;
			}

			&:nth-child(even) {
				padding-left: $gapSize;
			}

			&:nth-child(n+3) {
				padding-top: $gapSize;
			}

			&:nth-last-child(n+3) {
				padding-bottom: $gapSize;
			}

			&:nth-child(2n+1):not(:nth-last-child(2)) {
				&::after {
					content: '';
					$width: calc($gapSize * 2 + 18px);
					$height: calc($gapSize * 2 + 2px);
					position: absolute;
					bottom: - calc($height / 2 + 1px);
					right: - calc($width / 2 + 1px);
					width: $width;
					height: $height;
					background: $white;
				}
			}
		}
	}

	&.side-by-side {
		grid-gap: 40px;
		display: grid;

		.statistics {
			&:not(:last-child) {
				&::after {
					content: '';
					position: absolute;
					bottom: 0;
					right: -20px;
					width: 1px;
					height: 100%;
					background: $border;
				}
			}

			.statistics-current-total {
				flex: 0 1 auto;
				margin-right: 16px;
			}
		}

		@media (max-width: 1024px) {
			grid-template-columns: repeat(2, 1fr);

			.statistics {
				&::after {
					display: none;
				}
			}
		}
	}

	.aioseo-loading-spinner {
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		margin: auto;
	}
}
</style>