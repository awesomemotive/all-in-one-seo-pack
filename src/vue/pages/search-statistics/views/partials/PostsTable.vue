<template>
	<div class="aioseo-search-statistics-post-table">
		<core-wp-table
			ref="table"
			class="posts-table"
			:id="tableId"
			:columns="tableColumns"
			:rows="Object.values(posts.rows)"
			:totals="posts.totals"
			:filters="getFilters"
			:additional-filters="posts.additionalFilters"
			:selected-filters="selectedFilters"
			:loading="isLoading"
			:initial-page-number="pageNumber"
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
			@additional-filter-option-selected="processAdditionalFilterOptionSelected"
			@paginate="processPagination"
			@process-change-items-per-page="processChangeItemsPerPage"
			@search="processSearch"
			@sort-column="processSort"
		>
			<template #row="{ index }">
				<div class="object-row">
					{{ index + 1 }}
				</div>
			</template>

			<template #postTitle="{ row }">
				<div class="object-title">
					<a
						v-if="row.objectId && 'post' === row.objectType && searchStatisticsStore.isConnected"
						href="#"
						@click.prevent="openPostDetail(row)"
					>
						{{ row.objectTitle }}
					</a>

					<span
						v-else
						class="object-title"
					>
						{{ row.objectTitle }}
					</span>
				</div>

				<object-actions :row="row" />

				<div
					class="row-actions"
					v-if="row.objectId && 'post' === row.objectType"
				>
					<span>
						<a
							class="view"
							:href="row.context.permalink"
							target="_blank"
						>
							<span>{{ viewPost(row.context.postType?.singular) }}</span>
						</a> |
					</span>

					<span>
						<a
							class="edit"
							:href="row.context.editLink"
							target="_blank"
						>
							<span>{{ editPost(row.context.postType?.singular) }}</span>
						</a>
					</span>
				</div>
			</template>

			<template #seoScore="{ row }">
				<core-score-button
					v-if="row.seoScore"
					class="table-score-button"
					:score="row.seoScore"
				/>
			</template>

			<template #indexStatus="{ row }">
				<index-status
					v-if="!searchStatisticsStore.shouldShowSampleReports"
					:result="row.inspectionResult"
					:loading="row.inspectionResultLoading"
				/>

				<index-status-pro
					v-if="searchStatisticsStore.shouldShowSampleReports"
					:result="row.inspectionResult"
					:loading="row.inspectionResultLoading"
				/>
			</template>

			<template #clicks="{ row }">
				{{ numbers.compactNumber(row.clicks) }}
			</template>

			<template #impressions="{ row }">
				{{ numbers.compactNumber(row.impressions) }}
			</template>

			<template #position="{ row }">
				{{ Math.round(row.position).toFixed(0) }}
			</template>

			<template #lastUpdated="{ row }">
				{{ row.context.lastUpdated || '-' }}
			</template>

			<template #decay="{ row }">
				<statistic
					type="decay"
					:show-difference="false"
					:total="row.decay"
					:showZeroValues="true"
					class="no-margin"
				/>
			</template>

			<template #decayPercent="{ row }">
				<statistic
					type="decayPercent"
					:show-difference="false"
					:total="row.decayPercent"
					:showZeroValues="true"
					class="no-margin"
				/>
			</template>

			<template #performance="{ row }">
				<graph-decay
					:points="row.points"
					:peak="row.peak"
					:recovering="row.recovering"
					:height="38"
				/>
			</template>

			<template #diffPosition="{ row }">
				<statistic
					v-if="row.difference.comparison"
					type="position"
					:show-original="false"
					:difference="row.difference.position"
					tooltip-offset="-100px,0"
				/>
			</template>

			<template #diffDecay="{ row }">
				<statistic
					v-if="row.difference.comparison"
					type="diffDecay"
					:show-original="false"
					:difference="row.difference.decay"
					tooltip-offset="-100px,0"
				/>
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
		</core-wp-table>
	</div>
</template>

<script>
import { ref } from 'vue'

import {
	useLicenseStore,
	useOptionsStore,
	useRootStore,
	useSearchStatisticsStore,
	useSettingsStore
} from '@/vue/stores'

import links from '@/vue/utils/links'

import { usePostTypes } from '@/vue/composables/PostTypes'
import { useTable } from '@/vue/pages/search-statistics/composables/Table'
import { useWpTable } from '@/vue/composables/WpTable'

import license from '@/vue/utils/license'
import numbers from '@/vue/utils/numbers'
import { clone } from 'lodash-es'

import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import GraphDecay from './GraphDecay'
import IndexStatus from '@/vue/components/AIOSEO_VERSION/search-statistics/IndexStatus'
import IndexStatusPro from '@/vue/components/pro/search-statistics/IndexStatus'
import ObjectActions from './AIOSEO_VERSION/ObjectActions'
import Statistic from './Statistic'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const {
			editPost,
			viewPost
		} = usePostTypes()

		const searchStatisticsStore = useSearchStatisticsStore()

		const processAdditionalFilterOptionSelected = ({ name, selectedValue }) => {
			selectedFilters[name] = selectedValue
		}

		const resetSelectedFilters = () => {
			selectedFilters.value.postType = ''
			processAdditionalFilterOptionSelected({ name: 'postType', selectedValue: '' })
		}
		const fetchData = (payload) => {
			if ('function' === typeof searchStatisticsStore[props.updateAction]) {
				return searchStatisticsStore[props.updateAction](payload)
			}
		}

		const selectedFilters = ref({})
		const showUpsell      = ref(false)
		const {
			openPostDetail,
			orderBy,
			orderDir,
			processFilter,
			resultsPerPage
		} = useTable({
			processFilterTable : (tableFilter) => processFilterTable(tableFilter),
			showUpsell
		})

		const changeItemsPerPageSlug = 'searchStatisticsSeoStatistics'
		const tableId                = 'aioseo-search-statistics-post-table'
		const {
			filter,
			pageNumber,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFilterTable,
			processPagination,
			processSearch,
			processSort,
			searchTerm
		} = useWpTable({
			changeItemsPerPageSlug,
			fetchData,
			orderBy,
			orderDir,
			resetSelectedFilters,
			resultsPerPage,
			selectedFilters,
			tableId
		})

		return {
			changeItemsPerPageSlug,
			editPost,
			filter,
			licenseStore  : useLicenseStore(),
			links,
			openPostDetail,
			optionsStore  : useOptionsStore(),
			orderBy,
			orderDir,
			pageNumber,
			processAdditionalFilterOptionSelected,
			processAdditionalFilters,
			processChangeItemsPerPage,
			processFilter,
			processPagination,
			processSearch,
			processSort,
			rootStore     : useRootStore(),
			searchStatisticsStore,
			searchTerm,
			selectedFilters,
			settingsStore : useSettingsStore(),
			showUpsell,
			tableId,
			viewPost
		}
	},
	components : {
		CoreScoreButton,
		CoreWpTable,
		Cta,
		GraphDecay,
		IndexStatus,
		IndexStatusPro,
		ObjectActions,
		Statistic
	},
	props : {
		posts      : Object,
		isLoading  : Boolean,
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
				return [ 'postTitle', 'seoScore', 'clicks', 'impressions', 'position' ]
			}
		},
		appendColumns : {
			type : Object,
			default () {
				return {}
			}
		},
		defaultSorting : {
			type : Object,
			default () {
				return {}
			}
		},
		initialFilter : {
			type : String,
			default () {
				return ''
			}
		},
		updateAction : {
			type : String,
			default () {
				return 'updateSeoStatistics'
			}
		}
	},
	data () {
		return {
			numbers,
			sortableColumns : [],
			strings         : {
				position      : __('Position', td),
				ctaButtonText : __('Unlock Post Tracking', td),
				ctaHeader     : sprintf(
					// Translators: 1 - "PRO".
					__('Post Tracking is a %1$s Feature', td),
					'PRO'
				)
			},
			license
		}
	},
	watch : {
		isLoading (loading) {
			if (!loading) {
				this.$nextTick(() => {
					this.loadInspectionResult()
				})
			}
		}
	},
	computed : {
		getFilters () {
			// If these are the sample reports, let's hide all the filters.
			if (this.searchStatisticsStore.shouldShowSampleReports) {
				return []
			}

			return this.posts.filters
		},
		allColumns () {
			const columns = clone(this.columns)

			// Get the active filter from this.posts.filters array.
			const activeFilter = this.posts?.filters?.find(f => f.active) || {}

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
					slug  : 'row',
					label : '#',
					width : '40px'
				},
				{
					slug  : 'postTitle',
					label : __('Title', td),
					width : '100%'
				},
				{
					slug  : 'seoScore',
					label : __('TruSEO Score', td),
					width : '130px'
				},
				{
					slug        : 'indexStatus',
					label       : __('Indexed', td),
					width       : '80px',
					coreFeature : 'index-status'
				},
				{
					slug  : 'clicks',
					label : __('Clicks', td),
					width : '80px'
				},
				{
					slug  : 'impressions',
					label : __('Impressions', td),
					width : '110px'
				},
				{
					slug  : 'position',
					label : __('Position', td),
					width : '90px'
				},
				{
					slug  : 'lastUpdated',
					label : __('Last Updated On', td),
					width : '160px'
				},
				{
					slug  : 'decay',
					label : __('Loss', td),
					width : '140px'
				},
				{
					slug  : 'decayPercent',
					label : __('Drop (%)', td),
					width : '120px'
				},
				{
					slug  : 'performance',
					label : __('Performance Score', td),
					width : '150px'
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
				}
			].filter(column => {
				if (column.coreFeature) {
					if ((!this.rootStore.isPro || this.licenseStore.isUnlicensed) && !this.searchStatisticsStore.shouldShowSampleReports) {
						return false
					}

					if (!this.license.hasCoreFeature('search-statistics', column.coreFeature) && !this.searchStatisticsStore.shouldShowSampleReports) {
						return false
					}
				}

				if ('seoScore' === column.slug) {
					return this.optionsStore.options.advanced.truSeo
				}

				return this.allColumns.includes(column.slug)
			}).map(column => {
				column.sortable = this.isSortable && this.sortableColumns.includes(column.slug)

				if (column.sortable) {
					column.sortDir = column.slug === this.orderBy ? this.orderDir : 'asc'
					column.sorted  = column.slug === this.orderBy
				}

				return column
			})
		},
		isSortable () {
			return 'all' === this.filter && (this.rootStore.isPro && !this.licenseStore.isUnlicensed)
		}
	},
	methods : {
		loadInspectionResult () {
			if (!this.posts?.rows || this.searchStatisticsStore.quotaExceeded.urlInspection) {
				return
			}

			const rowsArray      = Object.values(this.posts.rows)
			const missingResults = rowsArray.filter(post => !post.inspectionResult || 0 === post.inspectionResult?.length)
			if (!missingResults.length) {
				return
			}

			missingResults.forEach(post => {
				const row = rowsArray.find(row => row.page === post.page)
				if (row) {
					row.inspectionResultLoading = true
				}
			})

			this.searchStatisticsStore.getInspectionResult({ paths: missingResults.map(post => post.page) })
				.then(response => {
					missingResults.forEach(post => {
						const row = rowsArray.find(row => row.page === post.page)
						if (row) {
							row.inspectionResult        = response[post.page]
							row.inspectionResultLoading = false
						}
					})
				})
		}
	},
	mounted () {
		if (this.initialFilter) {
			this.processFilter({
				slug : this.initialFilter
			})
		}

		this.loadInspectionResult()

		this.orderBy  = this.defaultSorting?.orderBy || this.orderBy
		this.orderDir = this.defaultSorting?.orderDir || this.orderDir
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-post-table {
	.posts-table {
		.manage-column {
			&.postTitle {
				display: flex;
				flex-wrap: wrap;
				align-items: center;

				.object-title {
					font-weight: 700;
					width: 100%;
					padding-bottom: 5px;
				}

				.row-actions {
					padding-top: 0;
				}
			}

			.table-score-button {
				display: flex;
				align-content: center;
				align-items: center;
				justify-content: center;
				width: 70px;
				height: 30px;
				padding: 5px;
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

		thead {
			tr th.manage-column,
			tr td.manage-column  {
				font-size: 13px;
			}
		}
	}

	/* For CTA purpose */
	@at-root {
		.aioseo-blur {
			.wp-list-table {
				min-height: 520px;

				.no-results {
					min-height: 500px;
				}
			}
		}
	}

	.blurred {
		min-height: 360px;
	}
}
</style>