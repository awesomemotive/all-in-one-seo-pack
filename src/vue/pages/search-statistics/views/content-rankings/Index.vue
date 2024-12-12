<template>
	<div class="aioseo-search-statistics-content-rankings">
		<core-alert
			v-if="!settingsStore.settings.dismissedAlerts?.searchStatisticsContentRankings"
			class="description"
			type="blue"
			show-close
			@close-alert="() => settingsStore.dismissAlert('searchStatisticsContentRankings')"
		>
			{{ strings.alert }}
		</core-alert>

		<div class="aioseo-search-statistics-content-rankings__title">
			<h2>{{ strings.title }}</h2>

			<core-tooltip :offset="'200px,0'">
				<svg-circle-question-mark />

				<template #tooltip>
					{{ strings.alert }}
				</template>
			</core-tooltip>
		</div>

		<posts-table
			:posts="searchStatisticsStore.data?.contentRankings?.paginated || defaultPages"
			:columns="[ 'postTitleSortable', 'indexStatus', 'lastUpdatedSortable', 'decaySortable', 'decayPercentSortable', 'performance' ]"
			:default-sorting="{
				orderBy  : 'decay',
				orderDir : 'asc'
			}"
			:isLoading="searchStatisticsStore.loading.contentRankings"
			updateAction="updateContentRankings"
			show-items-per-page
			show-table-footer
		/>
	</div>
</template>

<script>
import {
	useSearchStatisticsStore,
	useSettingsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import PostsTable from '../partials/PostsTable'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore()
		}
	},
	components : {
		CoreAlert,
		CoreTooltip,
		PostsTable,
		SvgCircleQuestionMark
	},
	data () {
		return {
			strings : {
				title : __('Content Rankings', td),
				alert : __('The Content Rankings report provides valuable insights into the performance of your content in search results and helps you optimize your posts for better results. This report is generated on a monthly basis, covering the past 12 months leading up to the current month. By regularly reviewing this report, you can identify trends in your post rankings and make informed decisions to improve your content\'s visibility and ultimately increase rankings in search results.', td)
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
	mounted () {
		if (this.searchStatisticsStore.isConnected) {
			this.searchStatisticsStore.loadInitialData()
		}
	}
}
</script>

<style lang="scss">
@use 'sass:color';

.aioseo-search-statistics-content-rankings {
	&__title {
		display: inline-flex;

		.aioseo-tooltip {
			.popper {
				width: 600px;
				max-width: 600px;

				h2 {
					font-weight: 700;
					font-size: 14px;
					line-height: 125%;
					color: $black2-hover;
				}
			}

			svg.aioseo-circle-question-mark {
				width: 17px;
				height: 17px;
				color: $placeholder-color;
				transition: background-color 0.2s ease;

				&:hover {
					color: color.adjust($placeholder-color, $lightness: -20%);
				}
			}
		}
	}
}
</style>