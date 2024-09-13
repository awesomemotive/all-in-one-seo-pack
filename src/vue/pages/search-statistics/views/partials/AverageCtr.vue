<template>
	<div class="aioseo-search-statistics-average-ctr">
		<div class="average-ctr-title">{{ strings.title }}</div>

		<core-tooltip>
			<svg-circle-question-mark />

			<template #tooltip>
				<span v-html="strings.tooltip" />
			</template>
		</core-tooltip>

		<core-loader
			v-if="searchStatisticsStore.loading.keywords"
			dark
		/>

		<statistic
			v-if="!searchStatisticsStore.loading.keywords && searchStatisticsStore.data?.seoStatistics?.statistics?.ctr"
			type="ctr"
			:total="searchStatisticsStore.data.seoStatistics.statistics.ctr"
			:difference="searchStatisticsStore.data.seoStatistics.statistics.difference.ctr"
		/>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import Statistic from './Statistic'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore()
		}
	},
	components : {
		CoreLoader,
		CoreTooltip,
		Statistic,
		SvgCircleQuestionMark
	},
	data () {
		return {
			strings : {
				title   : __('Average CTR', td),
				tooltip : __('Average CTR (click-through rate), is the ratio of clicks to impressions for your site content, and it measures how well the website or page is performing in terms of attracting clicks from the search results.', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-average-ctr {
	display: flex;
	flex-direction: row;
	align-items: center;
	margin: 25px 0;

	&-title {
		font-weight: 400;
		font-size: 14px;
	}

	.average-ctr-title {
		color: #434960;
	}

	.aioseo-tooltip {
		margin-left: 8px;

		svg.aioseo-circle-question-mark {
			width: 12px;
			height: 12px;
		}
	}

	.aioseo-loading-spinner {
		width: 20px;
		height: 20px;
		margin-left: 12px;
		position: relative;
	}

	.statistic {
		margin-left: 12px;
		font-weight: 700;
		font-size: 18px;

		&-direction {
			font-weight: 700;
			font-size: 16px;
		}
	}
}
</style>