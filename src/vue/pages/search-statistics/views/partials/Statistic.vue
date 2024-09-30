<template>
	<div
		class="statistic"
		:class="[{
			'no-margin' : !showCurrent
		}, `statistic-${type}`]"
	>
		<span v-if="showCurrent && (total || showZeroValues)">
			<span
				class="statistic-direction"
				:class="{
					up   : 0 < total,
					down : 0 > total
				}"
			>
				<svg-caret-solid
					v-if="0 !== total && 'decayPercent' === type"
					:direction="0 < total ? 'up' : 'down'"
				/>
				{{ formatStatistic(type, total) }}
			</span>
		</span>

		<core-tooltip
			v-if="showDifference && 0 !== differenceRounded"
			:offset="tooltipOffset"
		>
			<span
				class="statistic-direction"
				:class="{
					up   : 0 < differenceRounded,
					down : 0 >= differenceRounded
				}"
			>
				<svg-caret-solid :direction="0 < differenceRounded ? 'up' : 'down'" />
				{{ formatStatistic(type, differenceRounded) }}
			</span>

			<template #tooltip>
				<span v-html="compareDescription(Math.abs(differenceRounded), 0 < differenceRounded ? 'up' : 'down')" />
			</template>
		</core-tooltip>
	</div>
</template>

<script>
import {
	useRootStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import { DateTime } from 'luxon'
import numbers from '@/vue/utils/numbers'
import dateFormat from '@/vue/utils/dateFormat'

import { useStatistic } from '@/vue/pages/search-statistics/composables/Statistic'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCaretSolid from '@/vue/components/common/svg/CaretSolid'

import { __, _n, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { formatStatistic } = useStatistic()

		return {
			formatStatistic,
			rootStore             : useRootStore(),
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreTooltip,
		SvgCaretSolid
	},
	props : {
		total       : [ Number, String ],
		difference  : [ Number, String ],
		showCurrent : {
			type : Boolean,
			default () {
				return true
			}
		},
		showDifference : {
			type : Boolean,
			default () {
				return true
			}
		},
		showZeroValues : {
			type : Boolean,
			default () {
				return false
			}
		},
		type : {
			type     : String,
			required : true
		},
		tooltipOffset : {
			type : String,
			default () {
				return '0,0'
			}
		}
	},
	computed : {
		differenceRounded () {
			if ('position' === this.type) {
				return parseInt(Math.round(this.difference)) * -1
			}

			return this.difference
		}
	},
	methods : {
		compareDescription (number, direction) {
			const directionI18n = 'up' === direction
				? __('Up', td)
				: __('Down', td)

			const startDate = this.searchStatisticsStore.shouldShowSampleReports
				? DateTime.now().minus({ days: 14 }).toFormat('yyyy-MM-dd')
				: this.searchStatisticsStore.range.compareStart
			const endDate = this.searchStatisticsStore.shouldShowSampleReports
				? DateTime.now().minus({ days: 7 }).toFormat('yyyy-MM-dd')
				: this.searchStatisticsStore.range.compareEnd

			const compareStart = new Date(`${startDate} 00:00:00`)
			const compareEnd   = new Date(`${endDate} 00:00:00`)

			return sprintf(
				// Translators: 1 - The direction (up or down), 2 - The difference, 3 - "in search results", 4 - The first date, 5 - The second date.
				__('%1$s %2$s %3$s compared to the previous period (%4$s - %5$s)', td),
				directionI18n,
				'<strong>' + this.points(number) + '</strong>',
				'position' === this.type
					? __('in search results', td)
					: '',
				'<strong>' + dateFormat(compareStart, this.rootStore.aioseo.data.dateFormat) + '</strong>',
				'<strong>' + dateFormat(compareEnd, this.rootStore.aioseo.data.dateFormat) + '</strong>'
			)
		},
		points (number) {
			switch (this.type) {
				case 'clicks':
					return sprintf(
						// Translators: 1 - The number of clicks.
						_n('%s click', '%s clicks', parseInt(this.formatStatistic('clicks', number)), td),
						numbers.compactNumber(number)
					)
				case 'impressions':
					return sprintf(
						// Translators: 1 - The number of impressions.
						_n('%s impression', '%s impressions', parseInt(this.formatStatistic('impressions', number)), td),
						numbers.compactNumber(number)
					)
				case 'ctr':
					return this.formatStatistic('ctr', number)
				case 'position':
					number = parseInt(Math.round(number))
					return sprintf(
						// Translators: 1 - The number of spots.
						_n('%s spot', '%s spots', parseInt(this.formatStatistic('position', number)), td),
						number
					)
				case 'keywords':
					return sprintf(
						// Translators: 1 - The number of keywords.
						_n('%s keyword', '%s keywords', parseInt(this.formatStatistic('keywords', number)), td),
						numbers.compactNumber(number)
					)
				case 'decay':
				case 'diffDecay':
					return sprintf(
						// Translators: 1 - The number of points.
						_n('%s point', '%s points', parseInt((this.formatStatistic('keywords', number))), td),
						numbers.compactNumber(number)
					)
				default:
					return ''
			}
		}
	}
}
</script>

<style lang="scss">
.statistic {
	display: flex;
	align-items: center;
	font-size: 14px;

	> span {
		display: inline-flex;
		flex: 1;
	}

	.aioseo-tooltip {
		margin-left: 0;
		justify-content: flex-end;
	}

	&-direction {
		display: flex;
		align-items: center;
		margin-left: 12px;
		font-weight: 700;

		&.up {
			color: $green;
		}

		&.down {
			color: $red;
		}
	}

	&-loss,
	&-drop {
		font-weight: 700;
		font-size: 14px;
		line-height: 22px;
	}

	&.no-margin {
		.aioseo-tooltip {
			justify-content: center;
		}

		.statistic-direction {
			margin-left: 0;
		}
	}

	svg {
		width: 12px;
		height: 12px;
		margin-right: 6px;
	}
}
</style>