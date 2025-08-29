<template>
	<div class="aioseo-search-statistics-keywords-table">
		<core-wp-table
			ref="table"
			class="keywords-table"
			:id="tableId"
			:columns="tableColumns"
			:rows="Object.values(keywords.rows)"
			:totals="keywords.totals"
			:filters="getFilters"
			:additional-filters="keywords.additionalFilters"
			:loading="loading"
			:initial-page-number="keywords.totals.page || 1"
			:initial-search-term="searchTerm"
			:initial-items-per-page="settingsStore.settings.tablePagination[changeItemsPerPageSlug]"
			:show-header="showHeader"
			:show-bulk-actions="false"
			:show-table-footer="showTableFooter"
			:show-items-per-page="showItemsPerPage && !searchStatisticsStore.shouldShowSampleReports"
			show-pagination
			:blur-rows="showUpsell"
			@filter-table="processFilter"
			@process-additional-filters="processAdditionalFilters"
			@paginate="processPagination"
			@process-change-items-per-page="processChangeItemsPerPage"
			@search="processSearch"
			@sort-column="processSort"
		>
			<template #keyword="{ row, index, editRow }">
				<div class="post-title">
					<core-tooltip v-if="shouldLimitText(row.keyword)">
						<a
							class="limit-line"
							href="#"
							@click.prevent="editRow(index); toggleRow(index)"
						>
							{{ sanitizeString(row.keyword) }}
						</a>

						<template #tooltip>
							{{ sanitizeString(row.keyword) }}
						</template>
					</core-tooltip>

					<a
						v-else
						href="#"
						@click.prevent="editRow(index); toggleRow(index)"
					>
						{{ sanitizeString(row.keyword) }}
					</a>
				</div>

				<div class="row-actions">
					<span class="edit">
						<a
							:href="viewInGoogleLink(row.keyword)"
							target="_blank"
						>
							{{ strings.viewInGoogle }}

							<svg-external/>
						</a> |

						<a
							v-if="!isTrackingKeyword(row)"
							href="#"
							@click.prevent.exact="maybeTrackKeyword(row)"
						>
							{{ strings.addKeyword }}
						</a>
					</span>

					<span
						v-if="isTrackingKeyword(row)"
						class="delete"
					>
						<a
							href="#"
							@click.prevent.exact="maybeUntrackKeyword(row)"
						>
							{{ strings.removeFromKrt }}
						</a>
					</span>
				</div>
			</template>

			<template #clicks="{ row }">
				{{ row.clicks }}
			</template>

			<template #ctr="{ row }">
				{{ parseFloat(row.ctr) }}%
			</template>

			<template #impressions="{ row }">
				{{ numbers.compactNumber(row.impressions) }}
			</template>

			<template #position="{ row }">
				{{ Math.round(row.position).toFixed(0) }}
			</template>

			<template #diffPosition="{ row }">
				<statistic
					v-if="row.difference.comparison"
					type="position"
					:difference="row.difference.position"
					:showCurrent="false"
					tooltip-offset="-100px,0"
				/>
			</template>

			<template #diffDecay="{ row }">
				<statistic
					v-if="row.difference.comparison"
					type="decay"
					:difference="row.difference.decay"
					:showCurrent="false"
					tooltip-offset="-100px,0"
				/>
			</template>

			<template #buttons="{ row, index, column, editRow }">
				<div class="">
					<slot
						name="buttons"
						:row="row"
						:column="column"
						:index="index"
					/>

					<base-button
						type="gray"
						class="toggle-row-button"
						:class="{ active: isRowActive(index) }"
						@click="editRow(index); toggleRow(index)"
					>
						<svg-caret />
					</base-button>
				</div>
			</template>

			<template #edit-row="{ index }">
				<keyword-inner :paginated-rows="keywords.rows[index]"/>
			</template>

			<template #cta>
				<cta
					v-if="showUpsell"
					:cta-link="links.getPricingUrl('search-statistics', 'search-statistics-upsell', null, rootStore.isPro ? 'pricing' : 'liteUpgrade')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="links.getUpsellUrl('search-statistics', 'search-statistics-upsell', rootStore.isPro ? 'pricing' : 'liteUpgrade')"
					:hide-bonus="!licenseStore.isUnlicensed"
				>
					<template #header-text>
						{{ strings.ctaHeader }}
					</template>
				</cta>
			</template>

			<template #tablenav>
				<slot name="tablenav" />
			</template>
		</core-wp-table>
	</div>
</template>

<script>
import { ref, computed } from 'vue'

import links from '@/vue/utils/links'
import {
	useKeywordRankTrackerStore,
	useLicenseStore,
	useRootStore,
	useSearchStatisticsStore,
	useSettingsStore
} from '@/vue/stores'

import numbers from '@/vue/utils/numbers'
import { clone } from 'lodash-es'

import { useTable } from '@/vue/pages/search-statistics/composables/Table'
import { useWpTable } from '@/vue/composables/WpTable'

import { sanitizeString } from '@/vue/utils/strings'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import KeywordInner from './KeywordInner'
import Statistic from './Statistic'

import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgExternal from '@/vue/components/common/svg/External'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const tableId = 'aioseo-search-statistics-keywords-table'
		const changeItemsPerPageSlug = computed(() => {
			if (props.postDetail) {
				return 'searchStatisticsPostDetailKeywords'
			}

			return 'searchStatisticsKeywordRankings'
		})

		const viewInGoogleLink = (keyword) => {
			return `https://www.google.com/search?q=${encodeURIComponent(keyword)}`
		}

		const isFetching   = ref(false)
		const searchStatisticsStore = useSearchStatisticsStore()
		const fetchData = (payload) => {
			isFetching.value   = true

			if ('' !== props.page) {
				payload = { ...payload, page: props.page }
			}

			if (props.postDetail) {
				return searchStatisticsStore.updatePostDetailKeywords(payload).finally(() => {
					isFetching.value = false
				})
			}

			return searchStatisticsStore.updateKeywords(payload).finally(() => {
				isFetching.value = false
			})
		}

		const showUpsell = ref(false)
		const {
			orderBy,
			orderDir,
			processFilter
		} = useTable({
			processFilterTable : (tableFilter) => processFilterTable(tableFilter),
			showUpsell
		})

		const {
			filter,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFilterTable,
			processPagination,
			processSearch,
			processSort,
			searchTerm
		} = useWpTable({
			changeItemsPerPageSlug : changeItemsPerPageSlug.value,
			fetchData,
			orderBy,
			orderDir,
			tableId
		})

		return {
			changeItemsPerPageSlug,
			filter,
			keywordRankTrackerStore : useKeywordRankTrackerStore(),
			licenseStore            : useLicenseStore(),
			links,
			orderBy,
			orderDir,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFilter,
			processPagination,
			processSearch,
			processSort,
			rootStore               : useRootStore(),
			searchStatisticsStore,
			searchTerm,
			settingsStore           : useSettingsStore(),
			showUpsell,
			tableId,
			viewInGoogleLink
		}
	},
	components : {
		CoreTooltip,
		CoreWpTable,
		Cta,
		KeywordInner,
		Statistic,
		SvgCaret,
		SvgExternal
	},
	props : {
		keywords : Object,
		loading  : {
			type : Boolean,
			default () {
				return false
			}
		},
		showHeader : {
			type : Boolean,
			default () {
				return true
			}
		},
		showTableFooter  : Boolean,
		showItemsPerPage : Boolean,
		columns          : {
			type : Array,
			default () {
				return [ 'keyword', 'clicks', 'ctr', 'impressions', 'position', 'diffPosition', 'buttons' ]
			}
		},
		appendColumns : {
			type : Object,
			default () {
				return {}
			}
		},
		postDetail : {
			type : Boolean,
			default () {
				return false
			}
		},
		refreshOnLoad : {
			type : Boolean,
			default () {
				return true
			}
		},
		page : {
			type : String,
			default () {
				return ''
			}
		},
		initialFilter : String
	},
	data () {
		return {
			numbers,
			activeRow       : -1,
			interval        : null,
			sortableColumns : [],
			strings         : {
				addKeyword    : __('Add to KRT', td),
				removeFromKrt : __('Remove from KRT', td),
				viewInGoogle  : __('View in Google', td),
				position      : __('Position', td),
				ctaButtonText : __('Unlock Keyword Tracking', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Keyword Tracking is a %1$s Feature', td),
					'PRO'
				)
			}
		}
	},
	computed : {
		getFilters () {
			// If these are the sample reports, let's hide all the filters.
			if (this.searchStatisticsStore.shouldShowSampleReports) {
				return []
			}

			return this.keywords.filters
		},
		allColumns () {
			const columns = clone(this.columns)

			// Get the active filter from this.keywords.filters array.
			const activeFilter = this.keywords?.filters?.find(f => f.active) || {}

			if (this.appendColumns[activeFilter.slug || 'all']) {
				columns.push(this.appendColumns[activeFilter.slug || 'all'])
			}

			return columns.map(column => {
				// In order to have this column sortable, add the "Sortable" suffix to the column name.
				if (column.endsWith('Sortable')) {
					column = column.replace('Sortable', '')
					this.sortableColumns.push(column)
				}

				return column
			})
		},
		tableColumns () {
			return [
				{
					slug  : 'keyword',
					label : __('Keyword', td)
				},
				{
					slug  : 'clicks',
					label : __('Clicks', td),
					width : '80px'
				},
				{
					slug  : 'ctr',
					label : __('Avg. CTR', td),
					width : '100px'
				},
				{
					slug  : 'impressions',
					label : __('Impressions', td),
					width : '120px'
				},
				{
					slug  : 'position',
					label : __('Position', td),
					width : '85px'
				},
				{
					slug  : 'diffDecay',
					label : __('Diff', td),
					width : '95px'
				},
				{
					slug  : 'diffPosition',
					label : __('Diff', td),
					width : '80px'
				},
				{
					slug  : 'buttons',
					label : '',
					width : this.hasSlot('buttons') ? '240px' : '40px'
				}
			].filter(column => this.allColumns.includes(column.slug))
				.map(column => {
					column.sortable = this.isSortable && this.sortableColumns.includes(column.slug)

					if (column.sortable) {
						column.sortDir = column.slug === this.orderBy ? this.orderDir : 'asc'
						column.sorted  = column.slug === this.orderBy
					}

					return column
				})
				.filter(column => !this.searchStatisticsStore.shouldShowSampleReports || 'buttons' !== column.slug)
		},
		isSortable () {
			return 'all' === this.filter && (this.rootStore.isPro && !this.licenseStore.isUnlicensed)
		}
	},
	methods : {
		isTrackingKeyword (row) {
			return this.keywordRankTrackerStore.keywords.all.rows.find(r => r.name === row.keyword)
		},
		sanitizeString,
		isRowActive (index) {
			return index === this.activeRow
		},
		toggleRow (index) {
			if (this.activeRow === index) {
				this.activeRow = -1
				return
			}
			this.activeRow = index
		},
		hasSlot (name = 'default') {
			return !!this.$slots[name]
		},
		shouldLimitText (line) {
			return 120 < sanitizeString(line).length
		},
		maybeTrackKeyword (row) {
			this.keywordRankTrackerStore.parentActiveTab = 'rank-tracker'
			this.keywordRankTrackerStore.toggleModal({
				modal           : 'modalOpenAddKeywords',
				open            : true,
				relatedKeywords : [ row.keyword ]
			})
		},
		maybeUntrackKeyword (row) {
			this.keywordRankTrackerStore.parentActiveTab = 'rank-tracker'
			this.keywordRankTrackerStore.toggleModal({
				modal    : 'modalOpenDeleteKeywords',
				open     : true,
				keywords : [ this.keywordRankTrackerStore.keywords.all.rows.find(r => r.name === row.keyword) ]
			})
		}
	},
	async mounted () {
		if (this.initialFilter) {
			this.processFilter({
				slug : this.initialFilter
			})
		}

		this.orderBy  = this.defaultSorting?.orderBy || this.orderBy
		this.orderDir = this.defaultSorting?.orderDir || this.orderDir
	}
}
</script>

<style lang="scss">
.aioseo-app .aioseo-search-statistics-keywords-table {
	position: relative;

	.limit-line {
		overflow: hidden;
		text-overflow: ellipsis;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		line-clamp: 2;
		-webkit-box-orient: vertical;
	}

	.aioseo-tooltip {
		margin-left: 0;
	}

	.subsubsub {
		position: absolute;
		top: 65px;
	}

	.manage-column {
		&.buttons {
			> div {
				display: flex;
				align-items: center;
				justify-content: end;
			}
		}

		&.diffPosition,
		&.diffDecay {
			.statistic {
				.aioseo-tooltip {
					justify-content: start;
				}

				.statistic-direction {
					margin-left: 0;
				}
			}
		}
	}

	.text-button {
		display: flex;
		align-items: center;
		color: $blue;
		font-weight: 400;
		font-size: 14px;
		margin-right: 10px;

		svg {
			min-width: 16px;
			min-height: 16px;
			width: 16px;
			height: 16px;
			margin-right: 5px;
		}
	}

	tbody .keyword .row-actions {
		a {
			display: inline-flex;
			align-items: center;
			font-weight: normal;

			svg {
				margin-left: 3px;
				width: 12px;
				height: 12px;
			}
		}
	}

	.toggle-row-button {
		display: inline-flex;
		width: 30px;
		height: 26px;
		padding: 0;
		justify-content: center;
		align-items: center;
		background-color: white;
		border: 1px solid $gray;
		border-radius: 3px;
		margin-left: 10px;

		&:hover {
			cursor: pointer;
			background-color: #F9F9FA;
		}

		&.active {
			background-color: $blue;
			border: 1px solid $blue;

			&:hover {
				background-color: $blue2;
			}

			svg {
				&.aioseo-caret {
					color: white;
					transform: rotate(-360deg);
				}
			}
		}

		svg {
			&.aioseo-caret {
				margin: 0;
				height: 20px;
				width: 20px;
				transform: rotate(-90deg);
				color: $placeholder-color;
				transition: transform 0.3s;
			}
		}
	}

	@media (max-width: 1300px) {
		.manage-column {
			&.keyword {
				width: 160px !important;
			}
		}
	}

	.aioseo-wp-table {
		&.pagination-hidden {
			.search-box {
				position: relative;
				top: 45px;
			}

			.subsubsub {
				margin-bottom: 9px;
			}
		}
	}
}
</style>