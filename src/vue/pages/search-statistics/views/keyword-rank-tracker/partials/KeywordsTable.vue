<template>
	<core-wp-table
		ref="table"
		:id="tableId"
		:additional-filters="tableAdditionalFilters"
		:bulk-options="tableBulkOptions"
		:columns="tableColumns"
		:filters="tableFilters"
		:initial-items-per-page="settingsStore.settings.tablePagination[changeItemsPerPageSlug]"
		:initial-page-number="pageNumber"
		:initial-search-term="paginatedKeywords?.searchTerm || searchTerm"
		:key="wpTableKey"
		:loading="wpTableLoading"
		:rows="paginatedKeywords.rows"
		show-bulk-actions
		:show-header="showHeader"
		:show-table-footer="showTableFooter"
		:totals="paginatedKeywords.totals"
		show-items-per-page
		@filter-table="processFilterTable"
		@paginate="processPagination"
		@process-additional-filters="(args) => processAdditionalFilters({filters: args.filters, term: args.searchTerm, number: args.pageNumber})"
		@process-bulk-action="processBulkAction"
		@process-change-items-per-page="processChangeItemsPerPage"
		@search="processSearch"
		@sort-column="processSort"
	>
		<template #filters="{slug, active}">
			<button
				type="button"
				:class="[
					`btn-filter-favorited button ${slug}`,
					{ 'btn-filter-favorited--not-active': !active }
				]"
				:disabled="wpTableLoading"
				tabindex="-1"
			>
				<svg-star :active="true"/>
			</button>
		</template>

		<template #favorited="{ row, index }">
			<div class="btn-favorite">
				<base-button
					class="btn-favorite__button"
					:class="{ 'btn-favorite__button--active': row.favorited }"
					:loading="btnFavoriteLoading[index]"
					@click.exact="toggleFavorite(row, index)"
				>
					<svg-star
						width="20"
						:active="row.favorited"
					/>
				</base-button>
			</div>
		</template>

		<template #name="{ row }">
			<div class="post-title">
				<b>{{ row.name }}</b>
			</div>

			<div class="row-actions">
				<span class="edit">
					<a
						class="view"
						:href="viewInGoogleLink(row.name)"
						target="_blank"
					>
						{{ strings.viewInGoogle }}
						<svg-external />
					</a> |

					<a
						v-if="row.groups.length"
						href="#"
						@click.prevent.exact="keywordRankTrackerStore.toggleModal({modal: 'modalOpenAssignGroups', open: true, keywords: [row], fetchKeywordsCallback: fetchData})"
					>
						{{ strings.editGroup }}
					</a>

					<a
						v-else
						href="#"
						@click.prevent.exact="keywordRankTrackerStore.toggleModal({modal: 'modalOpenAssignGroups', open: true, keywords: [row], fetchKeywordsCallback: fetchData})"
					>
						{{ strings.addToGroup }}
					</a> |
				</span>

				<span class="delete">
					<a
						href="#"
						@click.prevent.exact="keywordRankTrackerStore.toggleModal({modal: 'modalOpenDeleteKeywords', open: true, keywords: [row], fetchKeywordsCallback: fetchData})"
					>
						{{ GLOBAL_STRINGS.delete }}
					</a>
				</span>
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
	</core-wp-table>
</template>

<script setup>
import { ref, computed } from 'vue'

import {
	useKeywordRankTrackerStore,
	useSettingsStore
} from '@/vue/stores'

import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import { __ } from '@/vue/plugins/translations'
import { useWpTable } from '@/vue/composables/WpTable'

import numbers from '@/vue/utils/numbers'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Graph from '../../partials/Graph'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgStar from '@/vue/components/common/svg/Star'

const td                      = import.meta.env.VITE_TEXTDOMAIN
const keywordRankTrackerStore = useKeywordRankTrackerStore()
const settingsStore           = useSettingsStore()
const changeItemsPerPageSlug  = 'searchStatisticsKeywordRankTracker'
const tableId                 = 'keyword-rank-tracker-keywords-table'
const strings                 = {
	addToGroup   : __('Add to Group', td),
	editGroup    : __('Edit Group', td),
	position     : __('Position', td),
	viewInGoogle : __('View in Google', td)
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
	paginatedKeywords     : Object,
	showAdditionalFilters : {
		type    : Boolean,
		default : true
	},
	showTableFooter : {
		type    : Boolean,
		default : true
	},
	showHeader : {
		type    : Boolean,
		default : true
	},
	fetchData : {
		type : Function,
		default (args) {
			const keywordRankTrackerStore = useKeywordRankTrackerStore()
			return keywordRankTrackerStore.fetchKeywords(args)
		}
	}
})

const table              = ref(null)
const btnFavoriteLoading = ref([])

const {
	orderBy,
	orderDir,
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
	changeItemsPerPageSlug,
	fetchData : props.fetchData,
	tableId,
	tableRef  : table.value
})

const pageNumber = computed(() => {
	return props.paginatedKeywords.totals.page
})

const tableAdditionalFilters = computed(() => {
	if (!props.showAdditionalFilters || !keywordRankTrackerStore.groups.count) {
		return []
	}

	const options = [
		{ label: __('All Groups', td), value: 'all' },
		...keywordRankTrackerStore.groups.all.rows.map(r => {
			return {
				...r,
				label : keywordRankTrackerStore.favoriteGroup.label === r.label ? '&starf;' : r.label
			}
		})
	]

	return [
		{
			label   : __('Filter by Group', td),
			name    : 'group',
			options : options
		}
	]
})

const tableFilters = computed(() => [
	{
		slug   : 'all',
		name   : 'All',
		active : 'all' === props.paginatedKeywords.filter
	},
	{
		slug   : 'favorited',
		name   : 'Favorited',
		active : 'favorited' === props.paginatedKeywords.filter
	}
])

const tableColumns = computed(() => {
	return [
		{
			slug  : 'favorited',
			label : '',
			width : '50px'
		},
		{
			slug     : 'name',
			label    : __('Keyword', td),
			sortable : 1 < props.paginatedKeywords.totals.total,
			sortDir  : 'name' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'name' === orderBy.value
		},
		{
			slug     : 'clicks',
			label    : __('Clicks', td),
			sortable : 1 < props.paginatedKeywords.totals.total,
			sortDir  : 'clicks' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'clicks' === orderBy.value,
			width    : '100px'
		},
		{
			slug     : 'ctr',
			label    : __('Avg. CTR', td),
			sortable : 1 < props.paginatedKeywords.totals.total,
			sortDir  : 'ctr' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'ctr' === orderBy.value,
			width    : '100px'
		},
		{
			slug     : 'impressions',
			label    : __('Impressions', td),
			sortable : 1 < props.paginatedKeywords.totals.total,
			sortDir  : 'impressions' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'impressions' === orderBy.value,
			width    : '110px'
		},
		{
			slug     : 'position',
			label    : __('Position', td),
			sortable : 1 < props.paginatedKeywords.totals.total,
			sortDir  : 'position' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'position' === orderBy.value,
			width    : '100px'
		},
		{
			slug  : 'history',
			label : __('Position History', td),
			width : '140px'
		}
	]
})

const formatRowStatistic = (row, key) => {
	let out = row.statistics?.[key] ?? ''
	switch (key) {
		case 'ctr':
			out = ('' !== out ? numbers.compactNumber(out) + '%' : out)
			break
		case 'impressions':
			out = '' !== out ? numbers.compactNumber(out) : out
			break
		case 'position':
			out = '' !== out ? Math.round(out).toFixed(0) : out
			break
	}

	return out
}

const processBulkAction = ({ action, selectedRows }) => {
	if (!selectedRows.length) {
		return
	}

	selectedRows = props.paginatedKeywords.rows.filter(v => selectedRows.includes(String(v.id)))

	if ('delete' === action) {
		keywordRankTrackerStore.toggleModal({
			modal                 : 'modalOpenDeleteKeywords',
			open                  : true,
			keywords              : selectedRows,
			fetchKeywordsCallback : props.fetchData
		})
	}

	if ('assignGroup' === action) {
		keywordRankTrackerStore.toggleModal({
			modal                 : 'modalOpenAssignGroups',
			open                  : true,
			keywords              : selectedRows.map(r => ({ ...r, groups: [] })), // Force the assignment operation to be 'create' by eliminating groups.
			fetchKeywordsCallback : props.fetchData
		})
	}
}

const positionHistorySeries = (row) => {
	return row.statistics?.history
		? [ {
			name : strings.position,
			data : row.statistics.history.map(h => ({ x: h.date, y: h.position }))
		} ]
		: []
}

const toggleFavorite = async (row, index) => {
	btnFavoriteLoading.value[index] = true

	try {
		await keywordRankTrackerStore.updateKeyword({
			id      : row.id,
			payload : { favorited: !row.favorited }
		})
		await props.fetchData()
		await keywordRankTrackerStore.fetchGroups()

		keywordRankTrackerStore.maybeFetchStatistics({ context: 'groups' })
	} catch (error) {
		console.error(error)
	} finally {
		btnFavoriteLoading.value = []
	}
}

const viewInGoogleLink = (keyword) => {
	return `https://www.google.com/search?q=${encodeURIComponent(keyword)}`
}
</script>

<style lang="scss">
#keyword-rank-tracker-keywords-table {
	.btn-filter-favorited {
		align-items: center;
		background-color: #fff;
		border-color: $input-border;
		display: none;
		padding-left: 8px;
		padding-right: 8px;

		svg {
			min-width: 20px;
			width: 20px;
		}

		a:has(&) {
			display: block;
		}

		&--not-active {
			color: $orange;
			display: inline-flex;

			&.favorited {
				color: $placeholder-color;
			}
		}
	}

	tbody .name .row-actions {
		.edit a.view {
			display: inline-flex;
			align-items: center;
			color: $blue;
			font-weight: normal;

			svg {
				margin-left: 3px;
				width: 12px;
				height: 12px;
			}
		}
	}

	.btn-favorite {
		line-height: 2;
		text-align: center;

		&__button {
			background: none;
			border: none;
			box-shadow: none;
			color: $placeholder-color;
			cursor: pointer;
			height: auto;
			margin: 0;
			padding: 0;
			width: auto;

			&--active {
				color: $orange;
			}
		}
	}

	&.inner-table {
		padding: 0;
	}
}
</style>