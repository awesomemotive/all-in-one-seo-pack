<template>
	<div class="keyword-rank-tracker-summary">
		<div
			v-for="(vision, index) in visions"
			:key="index"
			class="keyword-rank-tracker-summary__vision"
		>
			<div class="keyword-rank-tracker-summary__vision__header">
				{{ vision.label }}

				<core-tooltip v-if="vision.tooltip">
					<svg-circle-question-mark/>

					<template #tooltip>
						<span v-html="vision.tooltip"/>
					</template>
				</core-tooltip>
			</div>

			<core-loader v-if="loading && 'keywords' !== vision.name" dark/>

			<div
				class="keyword-rank-tracker-summary__vision__body"
				:class="{'keyword-rank-tracker-summary__vision__body--invisible' : loading && 'keywords' !== vision.name}"
			>
				{{ totals[vision.name] }}
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import {
	useKeywordRankTrackerStore
} from '@/vue/stores'

import numbers from '@/vue/utils/numbers'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

const keywordRankTrackerStore = useKeywordRankTrackerStore()

const visions = [
	{
		name    : 'keywords',
		label   : 'Total Keywords',
		tooltip : sprintf(
			// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
			__('The %1$stotal number of keywords that are being tracked%2$s for your website.', td),
			'<strong>',
			'</strong>'
		)
	},
	{
		name    : 'impressions',
		label   : 'Search Impressions',
		tooltip : sprintf(
			// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
			__('The %1$stotal number of impressions your tracked keywords have aggregated in search results%2$s within the selected timeframe.', td),
			'<strong>',
			'</strong>'
		)
	},
	{
		name    : 'clicks',
		label   : 'Clicks',
		tooltip : sprintf(
			// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
			__('The %1$stotal number of clicks your tracked keywords have aggregated from search results%2$s within the selected timeframe.', td),
			'<strong>',
			'</strong>'
		)
	},
	{
		name    : 'ctr',
		label   : 'Avg. CTR',
		tooltip : sprintf(
			// Translators: 1 - Opening HTML strong tag, 2 - Closing HTML strong tag.
			__('The %1$saverage click-through rate of your tracked keywords in search results%2$s within the selected timeframe.', td),
			'<strong>',
			'</strong>'
		)
	}
]

const loading = computed(() => keywordRankTrackerStore.isFetchingStatistics)
const totals  = computed(() => {
	const rows = keywordRankTrackerStore.keywords.all.rows.filter(r => r.statistics)

	const clicks = rows.length
		? numbers.compactNumber(rows.map(r => r.statistics.clicks).reduce((a, b) => a + b, 0))
		: 0

	const impressions = rows.length
		? numbers.compactNumber(rows.map(r => r.statistics.impressions).reduce((a, b) => a + b, 0))
		: 0

	const position = rows.length
		? (rows.map(r => Number(r.statistics.position)).reduce((a, b) => a + b, 0) / rows.length).toFixed(0)
		: 0

	const ctr = rows.length
		? (rows.map(r => Number(r.statistics.ctr)).reduce((a, b) => a + b, 0) / rows.length).toFixed(2) + '%'
		: 0

	return {
		keywords    : keywordRankTrackerStore.keywords.all.rows.length,
		clicks      : clicks,
		impressions : impressions,
		position    : position,
		ctr         : ctr
	}
})
</script>

<style lang="scss" scoped>
.keyword-rank-tracker-summary {
	display: grid;
	gap: 12px;
	grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
	max-width: 1000px;
	margin: 0 auto;

	&__vision {
		position: relative;

		&:not(:last-child) {
			border-right: 1px solid $border;
			margin-right: 12px;
			padding-right: 12px;
		}
	}

	&__vision__header {
		align-items: center;
		display: flex;
		margin-bottom: 14px;
	}

	&__vision__body {
		align-items: center;
		color: $black2-hover;
		display: flex;
		font-size: 28px;
		font-weight: 700;
		margin-bottom: 16px;

		&--invisible {
			visibility: hidden;
		}
	}

	.aioseo-loading-spinner {
		top: 50%;
		transform: translateY(-50%);
	}
}
</style>