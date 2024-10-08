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
		:loading="tableLoading"
		:rows="paginatedKeywords.rows"
		show-bulk-actions
		:show-header="showHeader"
		:show-table-footer="showTableFooter"
		:totals="paginatedKeywords.totals"
		show-items-per-page
		@filter-table="processFilterTable"
		@paginate="processPagination"
		@process-additional-filters="processAdditionalFilters"
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
				:disabled="tableLoading"
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

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import {
	useKeywordRankTrackerStore,
	useSettingsStore
} from '@/vue/stores'

import numbers from '@/vue/utils/numbers'

import { useWpTable } from '@/vue/composables/WpTable'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Graph from '../../partials/Graph'
import SvgStar from '@/vue/components/common/svg/Star'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [],
	setup (props) {
		const changeItemsPerPageSlug = 'searchStatisticsKeywordRankTracker'
		const tableId                = 'keyword-rank-tracker-keywords-table'
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
			changeItemsPerPageSlug,
			fetchData : props.fetchData,
			tableId
		})

		return {
			GLOBAL_STRINGS,
			changeItemsPerPageSlug,
			keywordRankTrackerStore : useKeywordRankTrackerStore(),
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
			settingsStore           : useSettingsStore(),
			tableId,
			wpTableKey,
			wpTableLoading
		}
	},
	components : {
		CoreLoader,
		CoreWpTable,
		Graph,
		SvgStar
	},
	props : {
		paginatedKeywords : Object,
		showTableFooter   : {
			type : Boolean,
			default () {
				return true
			}
		},
		showHeader : {
			type : Boolean,
			default () {
				return true
			}
		},
		fetchData : {
			type : Function,
			default (args) {
				const keywordRankTrackerStore = useKeywordRankTrackerStore()
				return keywordRankTrackerStore.fetchKeywords(args)
			}
		}
	},
	data () {
		return {
			selectedRows       : null,
			btnFavoriteLoading : [],
			strings            : {
				addToGroup : __('Add to Group', td),
				editGroup  : __('Edit Group', td),
				position   : __('Position', td)
			}
		}
	},
	computed : {
		tableAdditionalFilters () {
			const options = [
				{ label: __('All Groups', td), value: 'all' }
			]

			options.push(...this.keywordRankTrackerStore.groups.all.rows)

			return [
				{
					label   : __('Filter by Group', td),
					name    : 'group',
					options : options
				}
			]
		},
		tableBulkOptions () {
			return [
				{
					label : GLOBAL_STRINGS.delete,
					value : 'delete'
				},
				{
					label : this.strings.addToGroup,
					value : 'assignGroup'
				}
			]
		},
		tableFilters () {
			return [
				{
					slug   : 'all',
					name   : 'All',
					active : 'all' === this.paginatedKeywords.filter
				},
				{
					slug   : 'favorited',
					name   : 'Favorited',
					active : 'favorited' === this.paginatedKeywords.filter
				}
			]
		},
		tableColumns () {
			return [
				{
					slug  : 'favorited',
					label : '',
					width : '50px'
				},
				{
					slug     : 'name',
					label    : __('Keyword', td),
					sortable : 1 < this.paginatedKeywords.totals.total,
					sortDir  : 'name' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'name' === this.orderBy
				},
				{
					slug     : 'clicks',
					label    : __('Clicks', td),
					sortable : 1 < this.paginatedKeywords.totals.total,
					sortDir  : 'clicks' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'clicks' === this.orderBy,
					width    : '100px'
				},
				{
					slug     : 'ctr',
					label    : __('Avg. CTR', td),
					sortable : 1 < this.paginatedKeywords.totals.total,
					sortDir  : 'ctr' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'ctr' === this.orderBy,
					width    : '100px'
				},
				{
					slug     : 'impressions',
					label    : __('Impressions', td),
					sortable : 1 < this.paginatedKeywords.totals.total,
					sortDir  : 'impressions' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'impressions' === this.orderBy,
					width    : '110px'
				},
				{
					slug     : 'position',
					label    : __('Position', td),
					sortable : 1 < this.paginatedKeywords.totals.total,
					sortDir  : 'position' === this.orderBy ? this.orderDir : 'asc',
					sorted   : 'position' === this.orderBy,
					width    : '100px'
				},
				{
					slug  : 'history',
					label : __('Position History', td),
					width : '140px'
				}
			]
		},
		tableLoading () {
			return this.wpTableLoading
		}
	},
	methods : {
		formatRowStatistic (row, key) {
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
		},
		processBulkAction ({ action, selectedRows }) {
			if (!selectedRows.length) {
				return
			}

			selectedRows = this.paginatedKeywords.rows.filter(v => selectedRows.includes(String(v.id)))

			if ('delete' === action) {
				this.keywordRankTrackerStore.toggleModal({
					modal                 : 'modalOpenDeleteKeywords',
					open                  : true,
					keywords              : selectedRows,
					fetchKeywordsCallback : this.fetchData
				})
			}

			if ('assignGroup' === action) {
				this.keywordRankTrackerStore.toggleModal({
					modal                 : 'modalOpenAssignGroups',
					open                  : true,
					keywords              : selectedRows.map(r => ({ ...r, groups: [] })), // Force the assignment operation to be 'create' by eliminating groups.
					fetchKeywordsCallback : this.fetchData
				})
			}
		},
		positionHistorySeries (row) {
			return row.statistics?.history
				? [ {
					name : this.strings.position,
					data : row.statistics.history.map(h => ({ x: h.date, y: h.position }))
				} ]
				: []
		},
		async toggleFavorite (row, index) {
			this.btnFavoriteLoading[index] = true

			try {
				await this.keywordRankTrackerStore.updateKeyword({
					id      : row.id,
					payload : { favorited: !row.favorited }
				})
				await this.fetchData()
				this.keywordRankTrackerStore.fetchGroups()
					.then(() => {
						this.keywordRankTrackerStore.maybeFetchStatistics({
							context : 'groups'
						})
					})
			} catch (error) {
				console.error(error)
			} finally {
				this.btnFavoriteLoading = []
			}
		}
	}
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