<template>
	<div class="aioseo-search-statistics-post-table">
		<core-wp-table
			ref="table"
			class="posts-table"
			:id="tableId"
			:columns="tableColumns"
			:rows="Object.values(posts.rows)"
			:totals="posts.totals"
			:filters="posts.filters"
			:additional-filters="posts.additionalFilters"
			:selected-filters="selectedFilters"
			:loading="isLoading"
			:initial-page-number="pageNumber"
			:initial-search-term="searchTerm"
			:initial-items-per-page="settingsStore.settings.tablePagination[changeItemsPerPageSlug]"
			:show-header="showHeader"
			:show-bulk-actions="false"
			:show-table-footer="showTableFooter"
			:show-items-per-page="showItemsPerPage"
			show-pagination
			:blur-rows="showUpsell"
			@filter-table="processFilter"
			@process-additional-filters="processAdditionalFilters"
			@additional-filter-option-selected="processAdditionaFilterOptionSelected"
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
						v-if="row.objectId && 'post' === row.objectType"
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
							<span>{{ viewPost(row.context.postType.singular) }}</span>
						</a> |
					</span>

					<span>
						<a
							class="edit"
							:href="row.context.editLink"
							target="_blank"
						>
							<span>{{ editPost(row.context.postType.singular) }}</span>
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
					:result="row.inspectionResult?.indexStatusResult"
					:result-link="row.inspectionResult?.inspectionResultLink"
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
					:cta-link="$links.getPricingUrl('search-statistics', 'search-statistics-upsell')"
					:button-text="strings.ctaButtonText"
					:learn-more-link="$links.getUpsellUrl('search-statistics', 'search-statistics-upsell', $isPro ? 'pricing' : 'liteUpgrade')"
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
import {
	useLicenseStore,
	useSearchStatisticsStore,
	useSettingsStore,
	useOptionsStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import numbers from '@/vue/utils/numbers'
import { clone } from 'lodash-es'
import { WpTable } from '@/vue/mixins/WpTable'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Cta from '@/vue/components/common/cta/Index'
import GraphDecay from './GraphDecay'
import IndexStatus from '@/vue/components/AIOSEO_VERSION/search-statistics/IndexStatus'
import ObjectActions from './AIOSEO_VERSION/ObjectActions'
import PostTypesMixin from '@/vue/mixins/PostTypes.js'
import Statistic from './Statistic'
import Table from '../../mixins/Table.js'
export default {
	setup () {
		return {
			licenseStore          : useLicenseStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore(),
			optionsStore          : useOptionsStore()
		}
	},
	components : {
		CoreScoreButton,
		CoreWpTable,
		Cta,
		GraphDecay,
		IndexStatus,
		ObjectActions,
		Statistic
	},
	mixins : [ PostTypesMixin, WpTable, Table ],
	data () {
		return {
			numbers,
			tableId                : 'aioseo-search-statistics-post-table',
			changeItemsPerPageSlug : 'searchStatisticsSeoStatistics',
			showUpsell             : false,
			sortableColumns        : [],
			strings                : {
				position      : this.$t.__('Position', this.$td),
				ctaButtonText : this.$t.__('Unlock Post Tracking', this.$td),
				ctaHeader     : this.$t.sprintf(
					// Translators: 1 - "PRO".
					this.$t.__('Post Tracking is a %1$s Feature', this.$td),
					'PRO'
				)
			},
			license
		}
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
	computed : {
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
					label : this.$t.__('Title', this.$td),
					width : '100%'
				},
				{
					slug  : 'seoScore',
					label : this.$t.__('TruSEO Score', this.$td),
					width : '130px'
				},
				{
					slug        : 'indexStatus',
					label       : this.$t.__('Indexed', this.$td),
					width       : '80px',
					coreFeature : 'index-status'
				},
				{
					slug  : 'clicks',
					label : this.$t.__('Clicks', this.$td),
					width : '80px'
				},
				{
					slug  : 'impressions',
					label : this.$t.__('Impressions', this.$td),
					width : '110px'
				},
				{
					slug  : 'position',
					label : this.$t.__('Position', this.$td),
					width : '90px'
				},
				{
					slug  : 'lastUpdated',
					label : this.$t.__('Last Updated On', this.$td),
					width : '160px'
				},
				{
					slug  : 'decay',
					label : this.$t.__('Loss', this.$td),
					width : '140px'
				},
				{
					slug  : 'decayPercent',
					label : this.$t.__('Drop (%)', this.$td),
					width : '120px'
				},
				{
					slug  : 'performance',
					label : this.$t.__('Performance Score', this.$td),
					width : '150px'
				},
				{
					slug  : 'diffDecay',
					label : this.$t.__('Diff', this.$td),
					width : '95px'
				},
				{
					slug  : 'diffPosition',
					label : this.$t.__('Diff', this.$td),
					width : '80px'
				}
			].filter(column => {
				if (column.coreFeature) {
					if (!this.$isPro || this.licenseStore.isUnlicensed) {
						return false
					}

					if (!this.license.hasCoreFeature('search-statistics', column.coreFeature)) {
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
			return 'all' === this.filter && (this.$isPro && !this.licenseStore.isUnlicensed)
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
	methods : {
		resetSelectedFilters () {
			this.selectedFilters.postType = ''
			this.processAdditionaFilterOptionSelected({ name: 'postType', selectedValue: '' })
		},
		fetchData (payload) {
			if ('function' === typeof this.searchStatisticsStore[this.updateAction]) {
				return this.searchStatisticsStore[this.updateAction](payload)
			}
		},
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

			this.searchStatisticsStore.getInspectionResult(missingResults.map(post => post.page))
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