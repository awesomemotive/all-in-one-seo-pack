<template>
	<div class="aioseo-writing-assistant-competitors-sidebar">
		<template v-if="writingAssistantStore.hasReport">
			<report-your-content />
			<div class="aioseo-writing-assistant-competitors-sidebar__competitors">
				<div class="aioseo-writing-assistant-competitors-sidebar__competitors-filter">
					<div>
						{{ writingAssistantStore.getCompetitors.length }} {{ strings.competitorsFilter }}:
					</div>
					<base-select
						:options="filters"
						:modelValue="filterValue"
						@update:modelValue="value => writingAssistantStore.doCompetitorSort(value.value)"
						size="small"
						:searchable="false"
						:placeholder="strings.filterPlaceholder"
					/>
				</div>
				<div class="aioseo-writing-assistant-competitors-sidebar__competitors-list">
					<competitor
						v-for="(competitor, index) in writingAssistantStore.getCompetitors"
						:key="index"
						:competitor="competitor"
					/>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import Competitor from '../partials/sidebar/Competitor'
import ReportYourContent from '@/vue/standalone/writing-assistant/views/report/YourContent'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		Competitor,
		ReportYourContent
	},
	props : {
		maxCompetitors : Number
	},
	data () {
		return {
			strings : {
				competitors       : __('Competitors', td),
				competitorsFilter : __('Competitors Sorted By', td),
				filterPlaceholder : __('Click to Sort...', td)
			},
			filters : [
				{
					label : __('Position', td),
					value : {
						slug   : 'googlePosition',
						column : 'googlePosition',
						dir    : 'asc'
					}
				},
				{
					label : __('Article', td),
					value : {
						slug   : 'competitor',
						column : 'title',
						dir    : 'asc'
					}
				},
				{
					label : __('Readability', td),
					value : {
						slug   : 'readabilityGrade',
						column : 'readabilityScore',
						dir    : 'asc'
					}
				},
				{
					label : __('Word Count', td),
					value : {
						slug   : 'wordCount',
						column : 'wordCount',
						dir    : 'desc'
					}
				},
				{
					label : __('Grade', td),
					value : {
						slug   : 'grade',
						column : 'gradeScore',
						dir    : 'desc'
					}
				}
			]
		}
	},
	computed : {
		filterValue () {
			return this.filters.find(a => a.value.column === this.writingAssistantStore.sortFilters.competitors?.column)
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-competitors-sidebar {
	display: flex;
	flex-direction: column;
	gap: 12px;

	&__competitors {
		background: $box-background;
		padding: 20px 12px;
		margin: 0 -16px;
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__competitors-filter {
		background: $box-background;
		display: flex;
		align-items: center;
		gap: 10px;

		.aioseo-select {
			max-width: 130px;
		}
	}

	&__competitors-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}
}
</style>