<template>
	<div class="aioseo-writing-assistant__competitors">
		<core-wp-table
			:columns="getColumns"
			:rows="rowData"
			:filters="[]"
			:show-search="true"
			:totals="{ total: writingAssistantStore.getCompetitorsSearched.length, pages: writingAssistantStore.getCompetitorsPages }"
			@sort-column="sort => writingAssistantStore.doCompetitorSort(sort)"
			@process-change-items-per-page="perPage => writingAssistantStore.doCompetitorPerPage(perPage)"
			@paginate="page => writingAssistantStore.sortFilters.competitors.page = page"
			:showItemsPerPage="true"
			:initialItemsPerPage="writingAssistantStore.sortFilters.competitors.perPage"
			:export-columns="!disableExport ? exportColumns : []"
			:export-file-name="strings.csvFilename"
			:export-data="writingAssistantStore.getCompetitors"
			:showBulkActions="false"
			@search="search => writingAssistantStore.doCompetitorSearch(search)"
			:show-header="!hideHeader"
			:show-pagination="!hideHeader"
			:show-items-per-page="!hideHeader"
			:show-table-footer="!hideHeader"
		>
			<template #title="{ row }">
				<div>
					<div class="aioseo-writing-assistant__competitors-favicon">
						<favicon :domain="row.url" />
					</div>
					<div>
						<div class="aioseo-writing-assistant__competitors-title">{{ row.title || row.url }}</div>
						<div class="aioseo-writing-assistant__competitors-url">
							<a
								:href="row.url"
								:title="row.url"
								target="_blank"
							>
								{{ row.url }}
							</a>
						</div>
					</div>
				</div>
			</template>
			<template #readabilityScore="{ row }">
				{{  row.wasAnalyzed ? row.readabilityGrade : '' }}
				<could-not-be-analyzed v-if="!row.wasAnalyzed" />
			</template>
			<template #wordCount="{ row }">
				{{ row.wasAnalyzed ? row.wordCount : '' }}
				<could-not-be-analyzed v-if="!row.wasAnalyzed" />
			</template>
			<template #gradeScore="{ row }">
				<grade-round
					v-if="row.wasAnalyzed"
				    :grade="row.grade"
				/>
				<could-not-be-analyzed v-if="!row.wasAnalyzed" />
			</template>
		</core-wp-table>
	</div>
</template>

<script>
import { useWritingAssistantStore } from '@/vue/stores'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import GradeRound from '../partials/GradeRound'
import Favicon from '../partials/competitor/Favicon'
import CouldNotBeAnalyzed from '../partials/competitor/CouldNotBeAnalyzed.vue'

import {
	cloneObject,
	decodeSpecialChars
} from '@/vue/utils/helpers'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		return {
			writingAssistantStore : useWritingAssistantStore()
		}
	},
	components : {
		CoreWpTable,
		GradeRound,
		Favicon,
		CouldNotBeAnalyzed
	},
	props : {
		maxCompetitors : Number,
		hideColumns    : Array,
		disableExport  : {
			type    : Boolean,
			default : false
		},
		disableSort : {
			type    : Boolean,
			default : false
		},
		hideHeader : {
			type    : Boolean,
			default : false
		},
		useRawData : {
			type    : Boolean,
			default : false
		}
	},
	data () {
		return {
			strings : {
				sortBy           : __('Sort By', td),
				csvFilename      : __('competitors.csv', td),
				position         : __('#', td),
				article          : __('Article', td),
				readabilityGrade : __('Readability', td),
				wordCount        : __('Words', td),
				grade            : __('Grade', td),
				url              : __('URL', td)
			}
		}
	},
	computed : {
		getColumns () {
			let columns = [
				{
					slug     : 'googlePosition',
					label    : this.strings.position,
					sortable : true,
					width    : '35px'
				},
				{
					slug     : 'title',
					label    : this.strings.article,
					sortable : true
				},
				{
					slug       : 'readabilityScore',
					label      : this.strings.readabilityGrade,
					sortable   : true,
					width      : '130px',
					invertSort : true
				},
				{
					slug     : 'wordCount',
					label    : this.strings.wordCount,
					sortable : true,
					width    : '95px'
				},
				{
					slug     : 'gradeScore',
					label    : this.strings.grade,
					width    : '95px',
					sortable : true
				}
			]

			if (this.hideColumns) {
				columns = columns.filter(col => {
					return -1 === this.hideColumns.indexOf(col.slug)
				})
			}

			if (this.disableSort) {
				columns = columns.map(col => {
					col.sortable = false
					return col
				})
			}

			columns.map(col => {
				col.sorted = col.slug === this.writingAssistantStore.sortFilters.competitors.slug
				col.sortDir = col.sorted ? this.writingAssistantStore.sortFilters.competitors.sortDir : ''

				// Invert sort indicator.
				if (col?.invertSort) {
					col.sortDir = 'asc' === col.sortDir ? 'desc' : 'asc'
				}
				return col
			})

			return columns
		},
		exportColumns () {
			return [
				{
					slug  : 'googlePosition',
					label : this.strings.position
				},
				{
					slug  : 'title',
					label : this.strings.article
				},
				{
					slug  : 'url',
					label : this.strings.url
				},
				{
					slug  : 'readabilityGrade',
					label : this.strings.readabilityGrade
				},
				{
					slug  : 'wordCount',
					label : this.strings.wordCount
				},
				{
					slug  : 'grade',
					label : this.strings.grade
				}
			]
		},
		rowData () {
			let competitors = this.useRawData ? this.writingAssistantStore.getCompetitors : cloneObject(this.writingAssistantStore.getCompetitorsPaged)

			if (this.disableSort) {
				competitors = competitors.sort((a, b) => {
					return a.googlePosition - b.googlePosition
				})
			}

			if (this.maxCompetitors) {
				competitors = competitors.slice(0, this.maxCompetitors)
			}

			competitors.map(competitor => {
				competitor.title = decodeSpecialChars(competitor.title)
				return competitor
			})

			return competitors
		}
	}
}
</script>

<style lang="scss">
.aioseo-writing-assistant__competitors {
	th.googlePosition,
	.manage-column.googlePosition {
		padding-right: 0;

		span {
			word-wrap: normal;
		}
	}

	tbody .manage-column.title {
		position: relative;
		padding-left: 40px;
	}

	&-favicon {
		position: absolute;
		top: 8px;
		left: 10px;

		img {
			width: 20px !important;
			max-width: none;
		}
	}

	&-title {
		font-weight: 700;
		font-size: 15px;
		line-height: 21px;
		word-wrap: break-word;
	}

	&-url {
		font-size: 15px;

		a {
			word-wrap: break-word;
		}
	}

	.grade {
		text-align: center !important;
	}
}
</style>