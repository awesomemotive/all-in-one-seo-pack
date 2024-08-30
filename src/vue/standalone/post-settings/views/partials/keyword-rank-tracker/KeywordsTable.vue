<template>
	<core-wp-table
		ref="table"
		:id="tableId"
		:additional-filters="[]"
		:bulk-options="tableBulkOptions"
		:columns="tableColumns"
		:filters="[]"
		:initial-items-per-page="settingsStore.settings.tablePagination[changeItemsPerPageSlug]"
		:initial-page-number="pageNumber"
		:initial-search-term="searchTerm"
		:key="wpTableKey"
		:loading="wpTableLoading"
		:rows="keywords.rows"
		:selected-filters="{}"
		:show-bulk-actions="false"
		:show-header="false"
		:show-table-footer="false"
		:totals="{}"
		:show-items-per-page="false"
		:show-pagination="false"
		@filter-table="processFilterTable"
		@paginate="processPagination"
		@process-additional-filters="processAdditionalFilters"
		@process-bulk-action="{}"
		@process-change-items-per-page="processChangeItemsPerPage"
		@search="processSearch"
		@sort-column="processSort"
	>
		<template #name="{ row }">
			<div class="post-title">
				<b>{{ row.name }}</b>
			</div>
		</template>

		<template #position="{row}">
			<core-loader v-if="null === row.statistics" dark/>

			<div v-else>
				{{ formatRowStatistic(row, 'position') }}
			</div>
		</template>

		<template #history="{row}">
			<core-loader v-if="null === row.statistics" dark/>

			<div v-else>
				<graph
					v-if="positionHistorySeries(row).length"
					:series="positionHistorySeries(row)"
					:height="25"
					preset="overview"
					:chart-overrides="{
						tooltip: {
							y : {
								formatter : (value) => parseFloat(value).toFixed(2)
							}
						}
					}"
				/>
			</div>
		</template>

		<template #tracking="{ row, index }">
			<div class="tracking">
				<core-loader v-if="!!btnTrackingLoading[index]" dark/>

				<base-toggle
					v-model="row.tracking"
					@update:modelValue="value => toggleTracking(row, index, value)"
					:disabled="!!btnTrackingLoading[index]"
				/>
			</div>
		</template>

		<template #view="{ row }">
			<div
				class="btn-view"
				v-if="!!row.id"
			>
				<a
					:href="viewUrl(row)"
					target="_blank"
				>
					<core-tooltip
						type="action"
						:offset="'-80px,0'"
					>
						<svg-eye/>

						<template #tooltip>
							{{ strings.openInKrt }}
						</template>
					</core-tooltip>
				</a>
			</div>
		</template>
	</core-wp-table>
</template>

<script>
import {
	useKeywordRankTrackerStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { WpTable } from '@/vue/mixins/WpTable'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Graph from '@/vue/pages/search-statistics/views/partials/Graph'
import SvgEye from '@/vue/components/common/svg/Eye'

export default {
	setup () {
		const keywordRankTrackerStore = useKeywordRankTrackerStore()

		return {
			keywordRankTrackerStore,
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore(),
			fetchData     : keywordRankTrackerStore.fetchKeywords
		}
	},
	emits      : [],
	components : {
		CoreLoader,
		CoreTooltip,
		CoreWpTable,
		Graph,
		SvgEye
	},
	mixins : [ WpTable ],
	props  : {
		keywords : Object
	},
	computed : {
		tableBulkOptions () {
			return [
				{
					label : this.$constants.GLOBAL_STRINGS.delete,
					value : 'delete'
				},
				{
					label : this.strings.addToGroup,
					value : 'assignGroup'
				}
			]
		},
		tableColumns () {
			return [
				{
					slug     : 'name',
					label    : this.$t.__('Keyword', this.$td),
					sortable : true,
					sortDir  : 'name' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'name' === this.orderBy
				},
				{
					slug     : 'position',
					label    : this.$t.__('Position', this.$td),
					sortable : true,
					sortDir  : 'position' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'position' === this.orderBy,
					width    : '100px'
				},
				{
					slug  : 'history',
					label : this.$t.__('Position History', this.$td),
					width : '150px'
				},
				{
					slug  : 'tracking',
					label : this.$t.__('Tracking', this.$td),
					width : '100px'
				},
				{
					slug  : 'view',
					label : '',
					width : '40px'
				}
			]
		}
	},
	data () {
		return {
			tableId                : 'keyword-rank-tracker-keywords-table',
			changeItemsPerPageSlug : 'searchStatisticsKeywordRankTracker',
			selectedRows           : null,
			btnTrackingLoading     : [],
			strings                : {
				openInKeywordRankTracker : this.$t.__('Open in Keyword Rank Tracker', this.$td),
				position                 : this.$t.__('Position', this.$td),
				openInKrt                : this.$t.__('Open in Keyword Rank Tracker', this.$td)
			}
		}
	},
	methods : {
		formatRowStatistic (row, key) {
			let out = row.statistics?.[key] ?? ''
			switch (key) {
				case 'position':
					out = '' !== out ? Math.round(out).toFixed(0) : out
					break
			}

			return out
		},
		positionHistorySeries (row) {
			return row.statistics?.history
				? [ {
					name : this.strings.position,
					data : row.statistics.history.map(h => ({ x: h.date, y: h.position, label: h.position }))
				} ]
				: []
		},
		viewUrl (row) {
			return this.rootStore.aioseo.urls.aio.searchStatistics +
				`&search=${row.name}` +
				'&aioseo-scroll=keyword-rank-tracker-keywords-table' +
				'#/keyword-rank-tracker'
		},
		async toggleTracking (row, index, value) {
			this.btnTrackingLoading[index] = true
			row.tracking = !row.tracking

			try {
				if (row.id) {
					await this.keywordRankTrackerStore.deleteKeywords([ row.id ])
				} else {
					await this.keywordRankTrackerStore.insertKeywords({ keywords: [ row.name ] })
				}

				await this.keywordRankTrackerStore.fetchKeywords()
					.then(() => {
						this.keywordRankTrackerStore.maybeFetchStatistics({ context: 'keywords' })
					})
			} catch (error) {
				row.tracking = !value

				console.error(error)
			} finally {
				this.btnTrackingLoading = []
			}
		}
	}
}
</script>

<style lang="scss">
#keyword-rank-tracker-keywords-table {
	tr.main-row td {
		padding: 12px 10px;
	}

	.btn-view {
		.aioseo-tooltip {
			margin: 0;
		}

		a,
		svg {
			margin-top: 2px;
			color: $black2;
			height: 17px;
			width: 17px;

			&:hover {
				color: $blue;
			}
		}

		a {
			display: flex;
		}
	}

	.aioseo-loading-spinner {
		height: 22px;
		width: 22px;
	}
}
</style>