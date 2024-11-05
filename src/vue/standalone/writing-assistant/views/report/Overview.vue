<template>
	<div class="aioseo-writing-assistant-report__overview">
		<div>
			<current-keyword />
		</div>
		<div>
			<div class="aioseo-writing-assistant-report__overview-title">
				{{ strings.gradeSummary }}
			</div>
			<div class="aioseo-writing-assistant-report__overview-summaries">
				<template
					v-for="(summary, index) in summaries"
					:key="index">
					<div class="aioseo-writing-assistant-report__overview-summaries-item">
						<div class="aioseo-writing-assistant-report__overview-summaries-item-title">
							{{ getItemIndexTitle(index) }}
						</div>

						<GradeRound :grade="summary.wordCount ? summary.grade : '-'"/>
						<WordCount :summary="summary"/>
						<Readability :summary="summary"/>
					</div>
				</template>
			</div>
		</div>

		<div>
			<div class="aioseo-writing-assistant-report__overview-title">
				{{ strings.topCompetitor }}
			</div>
			<competitors
				:maxCompetitors="5"
				:hideColumns="['googlePosition']"
				:disable-export="true"
				:disable-sort="true"
				:hide-header="true"
				:use-raw-data="true"
			/>
			<div>
				<span
					class="aioseo-writing-assistant-report__overview-see-all"
					@click="$emit('change-tab', 'competitors')"
				>
					{{ strings.seeAll }}&nbsp;&rarr;
				</span>
			</div>
		</div>

		<div class="aioseo-writing-assistant-report__overview-report-info">
			<report-details />
			<report-history />
		</div>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import Competitors from './Competitors'
import GradeRound from '../partials/GradeRound'
import WordCount from '../partials/summary/WordCount'
import Readability from '../partials/summary/Readability'
import ReportDetails from '@/vue/standalone/writing-assistant/views/report/Details'
import ReportHistory from '@/vue/standalone/writing-assistant/views/report/History'
import CurrentKeyword from '@/vue/standalone/writing-assistant/views/report/CurrentKeyword'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		Competitors,
		GradeRound,
		WordCount,
		Readability,
		ReportHistory,
		ReportDetails,
		CurrentKeyword
	},
	data () {
		return {
			strings : {
				gradeSummary   : __('Grade Summary', td),
				topCompetitor  : __('Top Competitor Content', td),
				yourContent    : __('Your Content', td),
				typicalTop     : __('Typical Top', td),
				typical        : __('Typical', td),
				wordCount      : __('words', td),
				readability    : __('readability', td),
				seeAll         : __('See All', td),
				missingContent : __('You must first add content before the score can be determined.', td)
			}
		}
	},
	computed : {
		summaries () {
			return {
				1 : this.writingAssistantStore.getContentAnalysis,
				...this.writingAssistantStore.getKeywordCompetitorsSummary
			}
		}
	},
	methods : {
		getItemIndexTitle (index) {
			if ('1' === index) {
				return this.strings.yourContent
			}

			if (-1 === index.indexOf('-')) {
				return this.strings.typicalTop + ' ' + index
			}

			return this.strings.typical + ' ' + index
		},
		isAnalyzingContent (index) {
			return '1' === index && this.writingAssistantStore.analyzingContent
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-report__overview {
	display: flex;
	flex-direction: column;
	gap: 32px;

	&-title {
		font-weight: 700;
		font-size: 16px;
		padding: 0 0 20px;
		color: $black;
	}

	&-summaries {
		display: flex;
		gap: 14px;
		flex-wrap: nowrap;
		justify-content: space-evenly;

		&-item {
			max-width: 275px;
			padding: 24px;
			text-align: center;
			background-color: $box-background;
			flex-grow: 1;
			display: flex;
			flex-direction: column;
			gap: 20px;

			&.analyzing {
				position: relative;

				* {
					opacity: 0.3;
				}

				.aioseo-loading-spinner {
					position: absolute;
					top: 50%;
					left: 50%;
					transform: translate(-50%, -50%);
				}
			}

			&-title {
				font-weight: 700;
				font-size: 15px;
			}

			&:first-child {
				background: none;
				border: 1px solid $border;
			}

			.aioseo-writing-assistant__grade-value {
				font-size: 18px;
				min-width: 48px;
				padding: 7px 0;
			}
		}
	}

	&-see-all {
		font-weight: 700;
		font-size: 14px;
		line-height: 130%;
		margin-top: 20px;
		display: block;
		cursor: pointer;
		color: $blue;
		text-decoration: underline;
	}

	&-report-info {
		display: flex;
		gap: 20px;

		> div {
			flex-grow: 0;
			flex-basis: calc(100% / 2);
		}
	}
}
</style>