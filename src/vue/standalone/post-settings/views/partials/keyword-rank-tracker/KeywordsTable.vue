<template>
	<core-wp-table
		ref="table"
		:id="tableId"
		:additional-filters="[]"
		:bulk-options="tableBulkOptions"
		:columns="tableColumns"
		:filters="[]"
		:initial-items-per-page="100"
		:initial-page-number="pageNumber"
		:initial-search-term="searchTerm"
		:key="wpTableKey"
		:loading="wpTableLoading"
		:rows="paginatedKeywords.rows"
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
			<core-loader v-if="!!btnTrackingLoading[index]" dark/>

			<base-toggle
				v-model="row.tracking"
				@update:modelValue="value => toggleTracking(row, index, value)"
				:disabled="!!btnTrackingLoading[index]"
			/>
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

<script setup>
import { ref, computed } from 'vue'

import {
	useKeywordRankTrackerStore,
	useRootStore
} from '@/vue/stores'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import { __ } from '@/vue/plugins/translations'
import { useWpTable } from '@/vue/composables/WpTable'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Graph from '@/vue/pages/search-statistics/views/partials/Graph'
import SvgEye from '@/vue/components/common/svg/Eye'

const td                      = import.meta.env.VITE_TEXTDOMAIN
const keywordRankTrackerStore = useKeywordRankTrackerStore()
const rootStore               = useRootStore()
const tableId                 = 'keyword-rank-tracker-keywords-table'
const strings                 = {
	openInKeywordRankTracker : __('Open in Keyword Rank Tracker', td),
	position                 : __('Position', td),
	openInKrt                : __('Open in Keyword Rank Tracker', td)
}
const tableBulkOptions        = [
	{
		label : GLOBAL_STRINGS.delete,
		value : 'delete'
	},
	{
		label : strings.addToGroup,
		value : 'assignGroup'
	}
]

const props = defineProps({
	paginatedKeywords : Object,
	itemsPerPage      : Number
})

const table              = ref(null)
const btnTrackingLoading = ref([])

const {
	orderBy,
	orderDir,
	pageNumber,
	processAdditionalFilters,
	processChangeItemsPerPage,
	processFilterTable,
	processPagination,
	processSearch,
	processSort,
	searchTerm,
	wpTableKey,
	wpTableLoading
} = useWpTable({
	fetchData      : keywordRankTrackerStore.fetchKeywords,
	tableId,
	tableRef       : table.value,
	resultsPerPage : props.itemsPerPage
})

const tableColumns = computed(() => [
	{
		slug     : 'name',
		label    : __('Keyword', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'name' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'name' === orderBy.value
	},
	{
		slug     : 'position',
		label    : __('Position', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'position' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'position' === orderBy.value,
		width    : '100px'
	},
	{
		slug  : 'history',
		label : __('Position History', td),
		width : '150px'
	},
	{
		slug  : 'tracking',
		label : __('Tracking', td),
		width : '100px'
	},
	{
		slug  : 'view',
		label : '',
		width : '40px'
	}
])

const formatRowStatistic = (row, key) => {
	let out = row.statistics?.[key] ?? ''
	switch (key) {
		case 'position':
			out = '' !== out ? Math.round(out).toFixed(0) : out
			break
	}

	return out
}

const positionHistorySeries = (row) => {
	return row.statistics?.history
		? [ {
			name : strings.position,
			data : row.statistics.history.map(h => ({ x: h.date, y: h.position, label: h.position }))
		} ]
		: []
}

const viewUrl = (row) => {
	return rootStore.aioseo.urls.aio.searchStatistics +
		`&search=${encodeURIComponent(row.name)}` +
		'&aioseo-scroll=keyword-rank-tracker-keywords-table' +
		'#/keyword-rank-tracker'
}

const toggleTracking = async (row, index) => {
	btnTrackingLoading.value[index] = true

	try {
		if (row.id) {
			await keywordRankTrackerStore.deleteKeywords([ row.id ])
		} else {
			await keywordRankTrackerStore.insertKeywords({ keywords: [ row.name ] })
				.then(() => {
					row.tracking = true
				})
				.catch(() => {
					row.tracking = false
				})
		}

		// Pass the paginated keywords to prevent the toggle from being unchecked.
		await keywordRankTrackerStore.fetchKeywords({ rows: props.paginatedKeywords.rows })
			.then(() => {
				keywordRankTrackerStore.maybeFetchStatistics({ context: 'keywords' })
			})
	} catch (error) {
		console.error(error)
	} finally {
		btnTrackingLoading.value = []
	}
}
</script>

<style lang="scss">
#keyword-rank-tracker-keywords-table {
	table {
		td {
			position: relative;
			vertical-align: middle;

			&.tracking {
				line-height: 1;
			}
		}
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
		height: 18px;
		top: 50%;
		transform: translateY(-50%);
		width: 18px;
	}
}
</style>