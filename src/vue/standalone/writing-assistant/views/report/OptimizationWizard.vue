<template>
	<div class="aioseo-writing-assistant-optimization">
		<core-wp-table
			:columns="getColumns"
			:rows="writingAssistantStore.getKeywordsPaged"
			:filters="[]"
			:show-search="true"
			:totals="{ total: writingAssistantStore.getKeywordsSearched.length, pages: writingAssistantStore.getKeywordsPages }"
			@sort-column="sort => writingAssistantStore.doOptimizationWizardSort(sort)"
			@process-change-items-per-page="perPage => writingAssistantStore.doOptimizationWizardPerPage(perPage)"
			@paginate="page => writingAssistantStore.sortFilters.optimizationWizard.page = page"
			:initial-page-number="writingAssistantStore.sortFilters.optimizationWizard.page"
			:show-items-per-page="true"
			:initial-items-per-page="writingAssistantStore.sortFilters.optimizationWizard.perPage"
			:export-columns="exportColumns"
			:export-file-name="strings.csvFilename"
			:export-data="writingAssistantStore.getKeywords"
			:showBulkActions="false"
			@search="search => writingAssistantStore.doOptimizationWizardSearch(search)"
		>
			<template #text="{ row }">
				<div class="aioseo-writing-assistant-optimization__term-title">{{ row.text }}</div>
				<div
					v-if="row.similar"
					class="aioseo-writing-assistant-optimization__similar-terms"
				>
					{{row.similar.join(', ')}}
				</div>
				<div class="aioseo-writing-assistant-optimization__actions">
					<div
						class="aioseo-writing-assistant-optimization__click-examples"
						v-if="row.phrases"
						@click="showExamples = row.text"
					>
						<svg-examples /> {{ strings.seeExamples }}
					</div>
				</div>

				<core-modal
					:show="row.text === showExamples"
					@close="showExamples = ''"
				>
					<template #headerTitle>
						"{{ row.text }}" {{ strings.usageExamples }}
					</template>
					<template #body>
						<keyword-examples :keyword="row" />
					</template>
				</core-modal>
			</template>
			<template #headingPresence="{ row }">
				<keyword-heading-presence :keyword="row" />
			</template>
			<template #contentCount="{ row }">
				<keyword-uses :keyword="row" />
			</template>
			<template #importance="{ row }">
				<keyword-importance :keyword="row" />
			</template>
		</core-wp-table>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import KeywordUses from '@/vue/standalone/writing-assistant/views/partials/keyword/Uses'
import KeywordHeadingPresence from '@/vue/standalone/writing-assistant/views/partials/keyword/HeadingPresence'
import KeywordImportance from '@/vue/standalone/writing-assistant/views/partials/keyword/Importance'
import KeywordExamples from '@/vue/standalone/writing-assistant/views/partials/keyword/Examples'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreWpTable from '@/vue/components/common/core/wp/Table'

import { __ } from '@/vue/plugins/translations'
import SvgExamples from '@/vue/components/common/svg/link/External'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		SvgExamples,
		KeywordUses,
		KeywordHeadingPresence,
		KeywordImportance,
		KeywordExamples,
		CoreModal,
		CoreWpTable
	},
	data () {
		return {
			strings : {
				term                  : __('Term', td),
				headingPresence       : __('Heading Presence', td),
				uses                  : __('Uses', td),
				importance            : __('Importance', td),
				typical               : __('Typical', td),
				seeExamples           : __('See Examples', td),
				usageExamples         : __('Usage Examples', td),
				csvFilename           : __('optimization-wizard.csv', td),
				headingPresenceLevels : {
					high       : __('High', td),
					mid        : __('Moderate', td),
					low        : __('Low', td),
					noPresence : __('n/a', td)
				}
			},
			showExamples : ''
		}
	},
	computed : {
		getColumns () {
			const columns = [
				{
					slug     : 'text',
					label    : this.strings.term,
					sortable : true
				},
				{
					slug     : 'headingPresence',
					label    : this.strings.headingPresence,
					sortable : true
				},
				{
					slug     : 'contentCount',
					label    : this.strings.uses,
					sortable : true
				},
				{
					slug     : 'importance',
					label    : this.strings.importance,
					width    : '110px',
					sortable : true
				}
			]

			return columns.map(col => {
				col.sorted = col.slug === this.writingAssistantStore.sortFilters.optimizationWizard.slug
				col.sortDir = col.sorted ? this.writingAssistantStore.sortFilters.optimizationWizard.sortDir : ''

				// Invert sort indicator.
				if (col?.invertSort) {
					col.sortDir = 'asc' === col.sortDir ? 'desc' : 'asc'
				}
				return col
			})
		},
		exportColumns () {
			return [
				{
					slug  : 'text',
					label : this.strings.term
				},
				{
					slug  : 'headingPresenceLevel',
					label : this.strings.headingPresence,
					value : (row) => this.strings.headingPresenceLevels[row.headingPresenceLevel] || this.strings.headingPresenceLevels.noPresence
				},
				{
					slug  : 'contentCount',
					label : this.strings.uses
				},
				{
					slug  : 'countTypical',
					label : this.strings.typical,
					value : (row) => row.lowerCount < row.higherCount ? row.lowerCount + ' - ' + row.higherCount : row.higherCount
				},
				{
					slug  : 'importance',
					label : this.strings.importance
				}
			]
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant-optimization {
	&__term-title {
		font-weight: bold;
	}

	&__similar-terms {
		color: $placeholder-color;
	}

	.contentCount {
		.aioseo-writing-assistant__keyword-uses-typical {
			display: block;
			padding-left: 0;
		}
	}

	&__click-examples {
		color: $blue;
		cursor: pointer;
		font-size: 13px !important;

		svg {
			width: 12px;
			height: 12px;
			margin-right: 2px;
			position: relative;
			top: 1px;
		}
	}

	.main-row:hover .actions {
		opacity: 1;
	}
}
</style>