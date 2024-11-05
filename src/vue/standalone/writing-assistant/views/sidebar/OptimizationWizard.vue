<template>
	<div class="aioseo-writing-assistant-optimization-sidebar">
		<div class="aioseo-writing-assistant-optimization-sidebar__generate-report">
			<generate-report button-size="medium" />
			<reports-remaining />
		</div>
		<report-your-content />
		<div
			class="aioseo-writing-assistant-optimization-sidebar__keywords"
			v-if="writingAssistantStore.getKeywords.length"
		>
			<div class="aioseo-writing-assistant-optimization-sidebar__keywords-filter">
				<div>
					{{ strings.sortBy }}
				</div>
				<base-select
					:options="filters"
					:modelValue="filterValue"
					@update:modelValue="value => writingAssistantStore.doOptimizationWizardSort(value.value)"
					size="small"
					:searchable="false"
					:placeholder="strings.filterPlaceholder"
				/>
			</div>
			<div class="aioseo-writing-assistant-optimization-sidebar__keywords-list">
				<keyword
					v-for="keyword in writingAssistantStore.getKeywords"
					:key="keyword"
					:keyword="keyword"
				/>
			</div>
		</div>

		<report-details
			:open="false"
		/>
		<report-history
			:open="false"
			:hide-columns="[ 'region', 'language' ]"
		/>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import Keyword from '../partials/sidebar/Keyword'
import BaseSelect from '@/vue/components/common/base/Select'
import ReportsRemaining from '@/vue/standalone/writing-assistant/views/partials/seoboost/ReportsRemaining'
import GenerateReport from '@/vue/standalone/writing-assistant/views/report/GenerateReport'
import ReportDetails from '@/vue/standalone/writing-assistant/views/report/Details'
import ReportHistory from '@/vue/standalone/writing-assistant/views/report/History'
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
		ReportsRemaining,
		GenerateReport,
		ReportDetails,
		ReportHistory,
		ReportYourContent,
		Keyword,
		BaseSelect
	},
	data () {
		return {
			strings : {
				sortBy            : __('Sort By', td),
				filterPlaceholder : __('Click to Sort...', td)
			},
			filters : [
				{
					label : __('Term (A-Z)', td),
					value : {
						slug  : 'text',
						order : 'asc'
					}
				},
				{
					label : __('Term (Z-A)', td),
					value : {
						slug  : 'text',
						order : 'desc'
					}
				},
				{
					label : __('Heading Presence (Highest)', td),
					value : {
						slug  : 'headingPresence',
						order : 'desc'
					}
				},
				{
					label : __('Heading Presence (Lowest)', td),
					value : {
						slug  : 'headingPresence',
						order : 'asc'
					}
				},
				{
					label : __('Uses (Highest)', td),
					value : {
						slug  : 'contentCount',
						order : 'desc'
					}
				},
				{
					label : __('Uses (Lowest)', td),
					value : {
						slug  : 'contentCount',
						order : 'asc'
					}
				},
				{
					label : __('Importance (Highest)', td),
					value : {
						slug  : 'importance',
						order : 'desc'
					}
				},
				{
					label : __('Importance (Lowest)', td),
					value : {
						slug  : 'importance',
						order : 'asc'
					}
				}
			]
		}
	},
	computed : {
		filterValue () {
			return this.filters.find(a => a.value.slug === this.writingAssistantStore.sortFilters.optimizationWizard?.slug && a.value.order === this.writingAssistantStore.sortFilters.optimizationWizard?.sortDir)
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-optimization-sidebar {
	display: flex;
	flex-direction: column;
	gap: 20px;

	&__generate-report {
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	&__keywords {
		background: $box-background;
		padding: 16px 6px;
		display: flex;
		gap: 12px;
		flex-direction: column;
		margin: 0 -16px;

		.aioseo-writing-assistant__keyword-heading-presence {
			&-exists {
				background: $border;
			}

			&-icon {
				margin-right: 4px;
			}
		}
	}

	&__keywords-list {
		display: flex;
		flex-direction: column;
		gap: 12px;
	}

	&__keywords-filter {
		display: flex;
		align-items: center;
		gap: 12px;

		> div {
			white-space: nowrap;
		}

		.aioseo-select {
			width: 187px;
		}
	}

	&__summary {
		padding: 0 0 28px;
		display: flex;

		> * {
			flex-grow: 1;
		}
	}

	.aioseo-collapse-box__header {
		justify-content: space-between;
		font-size: 16px;
	}

	.aioseo-collapse-box {
		.aioseo-collapse-box__header {
			background: none;
			padding: 12px 0;
			border: 0;
			margin-bottom: 0;
		}
	}
}
</style>