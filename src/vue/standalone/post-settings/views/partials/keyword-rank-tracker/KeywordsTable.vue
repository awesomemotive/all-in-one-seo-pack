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

		<template #clicks="{row}">
			<core-loader v-if="null === row.statistics" dark/>

			<div v-else>
				{{ formatRowStatistic(row, 'clicks') }}
			</div>
		</template>

		<template #ctr="{row}">
			<core-loader v-if="null === row.statistics" dark/>

			<div v-else>
				{{ formatRowStatistic(row, 'ctr') }}
			</div>
		</template>

		<template #impressions="{row}">
			<core-loader v-if="null === row.statistics" dark/>

			<div v-else>
				{{ formatRowStatistic(row, 'impressions') }}
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

import numbers from '@/vue/utils/numbers'

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
	position  : __('Position', td),
	openInKrt : __('Open in Keyword Rank Tracker', td)
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
		slug     : 'clicks',
		label    : __('Clicks', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'clicks' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'clicks' === orderBy.value,
		width    : '80px'
	},
	{
		slug     : 'ctr',
		label    : __('CTR', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'ctr' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'ctr' === orderBy.value,
		width    : '68px'
	},
	{
		slug     : 'impressions',
		label    : __('Impressions', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'impressions' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'impressions' === orderBy.value,
		width    : '110px'
	},
	{
		slug     : 'position',
		label    : __('Position', td),
		sortable : 1 < props.paginatedKeywords.rows.length,
		sortDir  : 'position' === orderBy.value ? orderDir.value : 'asc',
		sorted   : 'position' === orderBy.value,
		width    : '90px'
	},
	{
		slug  : 'history',
		label : __('Position History', td),
		width : '140px'
	},
	{
		slug  : 'tracking',
		label : __('Tracking', td),
		width : '85px'
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
		case 'ctr':
			out = ('' !== out ? parseFloat(out) + '%' : out)
			break
		case 'clicks':
		case 'impressions':
			out = '' !== out ? numbers.compactNumber(out) : out
			break
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
		th,
		td {
			vertical-align: middle;
			font-size: 13px;
		}

		td {
			border: none;
			position: relative;

			&.name,
			&.tracking {
				line-height: 1;
			}
		}
	}

	.btn-view {
		.aioseo-tooltip {
			margin: 0;

			.popper {
				width: 200px;
			}
		}

		a,
		svg {
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