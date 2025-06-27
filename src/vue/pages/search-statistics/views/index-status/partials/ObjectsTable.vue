<template>
	<core-wp-table
		ref="table"
		:id="tableId"
		:additional-filters="Object.values(indexStatusStore.options?.table?.additionalFilters || {})"
		:selected-filters="parseSelectedFilters"
		:bulk-options="[]"
		:columns="tableColumns"
		:filters="[]"
		:initial-items-per-page="settingsStore.settings.tablePagination[changeItemsPerPageSlug]"
		:initial-page-number="pageNumber"
		:initial-search-term="paginatedRows?.searchTerm || searchTerm"
		:key="wpTableKey"
		:loading="wpTableLoading || indexStatusStore.objects.fetching"
		:rows="paginatedRows.rows"
		:show-bulk-actions="false"
		:show-header="showHeader"
		:show-table-footer="showTableFooter"
		:totals="paginatedRows.totals"
		show-items-per-page
		@filter-table="processFilterTable"
		@paginate="processPagination"
		@process-additional-filters="(args) => processAdditionalFilters({filters: args.filters, term: args.searchTerm, number: args.pageNumber})"
		@process-change-items-per-page="processChangeItemsPerPage"
		@search="processSearch"
		@sort-column="processSort"
	>
		<template #title="{ row, index, editRow }">
			<div class="post-title">
				<a
					href="#"
					@click.prevent="toggleRow(index, row, editRow)"
					class="allow-click"
				>
					{{ row.objectTitle }}
				</a>
			</div>

			<div
				class="row-actions"
				v-if="row?.objectId"
			>
				<span class="edit">
					<a
						:href="row?.permalink || '#'"
						target="_blank"
					>
						{{ viewPost(row?.postTypeLabels?.singular || null) }}
					</a> |

					<a
						:href="row?.editLink || '#'"
						target="_blank"
					>
						{{ editPost(row?.postTypeLabels?.singular || null) }}
					</a> |

					<a
						href="#"
						@click.prevent.exact="refreshObjectStatus(row)"
					>
						{{ strings.refreshStatus }}
					</a>
				</span>
			</div>
		</template>

		<template #edit-row="{ row }">
			<div class="inner-row">
				<div class="inner-row__heading">{{ strings.indexStatusResult }}</div>

				<table class="inner-table inner-table--index-status">
					<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					</thead>

					<tbody>
					<tr v-if="!row.lastCrawlTime">
						<td colspan="4">
							<span v-if="row.coverageState && row.indexingState !== 'INDEXING_ALLOWED'">{{ row.coverageState }}. </span>

							<span v-else>{{ indexStatusStrings.noResultsYetLowercase }}. </span>

							<a
								v-if="row?.inspectionResultLink"
								class="inner-table__inspection-link"
								:href="row.inspectionResultLink"
								target="_blank"
							>
								{{ indexStatusStrings.inspectOnGsc }}

								<svg-link-external
									width="12"
									height="12"
								/>
							</a>
						</td>
					</tr>

					<template v-else>
						<tr>
							<th scope="col">{{ indexStatusItems.pageFetchState.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.pageFetchState.getIcon(row.pageFetchState)"
										width="20"
										height="20"
									/>

									{{ indexStatusItems.pageFetchState.parseValue(row.pageFetchState).value }}
								</div>
							</td>

							<th scope="col">{{ indexStatusItems.crawledAs.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.crawledAs.getIcon(row.crawledAs)"
										width="20"
										height="20"
										color="#8C8F9A"
									/>

									{{ indexStatusItems.crawledAs.parseValue(row.crawledAs).value }}
								</div>
							</td>
						</tr>

						<tr>
							<th scope="col">{{ indexStatusItems.indexingState.title }}</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.indexingState.getIcon(row.indexingState)"
										width="20"
										height="20"
									/>

									<div v-html="parseInnerRowValue(row, 'indexingState')"/>
								</div>
							</td>

							<th scope="col">{{ indexStatusItems.lastCrawlTime.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.lastCrawlTime.getIcon(row.lastCrawlTime)"
										width="20"
										height="20"
										color="#8C8F9A"
									/>

									{{ indexStatusItems.lastCrawlTime.parseValue(row.lastCrawlTime).value }}
								</div>
							</td>
						</tr>

						<tr>
							<th scope="col">{{ indexStatusItems.verdict.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.verdict.getIcon(row.verdict)"
										width="20"
										height="20"
									/>

									<div v-html="parseInnerRowValue(row, 'verdict')"/>
								</div>
							</td>

							<th scope="col">{{ indexStatusItems.googleCanonical.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.googleCanonical.getIcon()"
										width="20"
										height="20"
										color="#8C8F9A"
									/>

									<span v-html="indexStatusItems.googleCanonical.parseValue(row.googleCanonical).value"/>
								</div>
							</td>
						</tr>

						<tr>
							<th scope="col">{{ indexStatusItems.robotsTxtState.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.robotsTxtState.getIcon(row.robotsTxtState)"
										width="20"
										height="20"
									/>

									<div v-html="parseInnerRowValue(row, 'robotsTxtState')"/>
								</div>
							</td>

							<th scope="col">{{ indexStatusItems.userCanonical.title }}:</th>
							<td>
								<div class="inner-table__col inner-table__col--items-start">
									<component
										:is="indexStatusItems.userCanonical.getIcon()"
										width="20"
										height="20"
										color="#8C8F9A"
									/>

									<span v-html="indexStatusItems.userCanonical.parseValue(row.userCanonical).value"/>
								</div>
							</td>
						</tr>

						<tr v-if="row?.inspectionResultLink">
							<td colspan="4">
								<a
									class="inner-table__inspection-link button-secondary"
									:href="row.inspectionResultLink"
									target="_blank"
								>
									{{ indexStatusStrings.inspectOnGsc }}

									<svg-link-external width="12" height="12"/>
								</a>
							</td>
						</tr>
					</template>
					</tbody>
				</table>

				<template v-if="row.lastCrawlTime">
					<div class="inner-row__heading">
						{{ strings.sitemaps }}

						<core-tooltip placement="right">
							<svg-circle-question-mark />

							<template #tooltip>
								{{ strings.sitemapsHelp }}
							</template>
						</core-tooltip>
					</div>

					<table class="inner-table">
						<tbody>
						<template v-if="row.sitemap.length">
							<tr
								v-for="(sitemap, i) in row.sitemap"
								:key="`sitemap-${i}`"
							>
								<td class="inner-table__col">
									<svg-link
										:width="20"
										:height="20"
										color="#8C8F9A"
									/>

									<a
										:href="sitemap"
										target="_blank"
									>
										{{ sitemap }}
									</a>
								</td>
							</tr>
						</template>

						<tr v-else>
							<td>{{ strings.notAvailable }}</td>
						</tr>
						</tbody>
					</table>

					<div class="inner-row__heading">
						{{ strings.referringPages }}

						<core-tooltip placement="right">
							<svg-circle-question-mark />

							<template #tooltip>
								{{ strings.referringPagesHelp }}
							</template>
						</core-tooltip>
					</div>

					<table class="inner-table">
						<tbody>
						<template v-if="row.referringUrls.length">
							<tr
								v-for="(url, i) in row.referringUrls"
								:key="`sitemap-${i}`"
							>
								<td class="inner-table__col">
									<svg-link
										:width="20"
										:height="20"
										color="#8C8F9A"
									/>

									<a
										:href="url"
										target="_blank"
									>
										{{ url }}
									</a>
								</td>
							</tr>
						</template>

						<tr v-else>
							<td>{{ strings.notAvailable }}</td>
						</tr>
						</tbody>
					</table>

					<div class="inner-row__heading">
						{{ strings.richResultTypes }}

						<core-tooltip placement="right">
							<svg-circle-question-mark />

							<template #tooltip>
								{{ strings.richResultTypesHelp }}
							</template>
						</core-tooltip>
					</div>

					<table class="inner-table inner-table--rich-results">
						<thead>
						<tr>
							<th></th>
							<th></th>
							<th></th>
						</tr>
						</thead>

						<tbody>
						<template v-if="(row.richResultsResult?.detectedItems || []).length">
							<tr
								v-for="(result, i) in parseRichResults(row.richResultsResult)"
								:key="`rich-result-type-${i}`"
							>
								<th scope="col">
									<div class="inner-table__col inner-table__col--items-start">
										<component
											:is="result.icon"
											color="#8C8F9A"
											fill="currentColor"
										/>

										<span class="rich-result-type__label">{{ result.label }}</span>
									</div>
								</th>

								<td colspan="2">
									<div
										class="rich-result-type-item"
										v-for="(item, j) in result.items"
										:key="`rich-result-type-item-${j}`"
									>
										<b>{{ item.name }}</b>

										<div
											v-if="item.issues.length"
											class="rich-result-type-item-issues"
										>
											<b>{{ strings.issues }}</b>

											<template
												v-for="(issue, k) in item.issues"
												:key="`rich-result-type-item-issue-${k}`"
											>
												<div class="inner-table__col">
													<component :is="issue.icon()" />

													{{ issue.message }}
												</div>
											</template>
										</div>

										<div v-else>
											<div class="inner-table__col">
												<svg-circle-check
													width="20"
													height="20"
												/>

												{{ strings.noIssues }}
											</div>
										</div>
									</div>
								</td>
							</tr>
						</template>

						<tr v-else>
							<td>{{ strings.notAvailable }}</td>
						</tr>

						<tr v-if="row?.richResultsTestLink">
							<td colspan="3">
								<a
									class="button-secondary"
									:href="row.richResultsTestLink"
									target="_blank"
								>
									{{ indexStatusStrings.testWithGoogle }}

									<svg-link-external width="12" height="12"/>
								</a>
							</td>
						</tr>
						</tbody>
					</table>
				</template>
			</div>
		</template>

		<template #verdict="{row}">
			<div :title="indexStatusItems.verdict.parseValue(row.verdict).value">
				<component
					:is="indexStatusItems.verdict.getIcon(row.verdict)"
					width="20"
					height="20"
				/>
			</div>
		</template>

		<template #robotsTxt="{row}">
			<div
				v-if="row.lastCrawlTime"
				:title="indexStatusItems.robotsTxtState.parseValue(row.robotsTxtState).value"
			>
				<component
					:is="indexStatusItems.robotsTxtState.getIcon(row.robotsTxtState)"
					width="20"
					height="20"
				/>
			</div>
		</template>

		<template #crawledAs="{row}">
			<div
				v-if="row.lastCrawlTime"
				:title="indexStatusItems.crawledAs.parseValue(row.crawledAs).value"
			>
				<component
					:is="indexStatusItems.crawledAs.getIcon(row.crawledAs)"
					width="20"
					height="20"
					color="#8C8F9A"
				/>
			</div>
		</template>

		<template #pageFetch="{row}">
			<div
				v-if="row.lastCrawlTime"
				:title="indexStatusItems.pageFetchState.parseValue(row.pageFetchState).value"
			>
				<component
					:is="indexStatusItems.pageFetchState.getIcon(row.pageFetchState)"
					width="20"
					height="20"
				/>
			</div>
		</template>

		<template #richResults="{row}">
			<template v-if="(row.richResultsResult?.detectedItems || []).length">
				<div
					class="rich-result-type"
					v-for="result in parseRichResults(row.richResultsResult)"
					:key="`rich-result-type-${result.label}`"
				>
					<component
						:is="result.icon"
						fill="currentColor"
					/>

					<span
						class="rich-result-type__label"
						:title="result.label"
					>{{ result.label }}</span>
				</div>
			</template>
		</template>

		<template #lastCrawlTime="{row}">
			{{ indexStatusItems.lastCrawlTime.parseValue(row.lastCrawlTime).value }}
		</template>

		<template #buttons="{ row, index, editRow }">
			<base-button
				@click="toggleRow(index, row, editRow)"
				:type="table?.activeRow === index ? 'blue' : 'gray'"
				:disabled="wpTableLoading"
				:class="{ 'active': table?.activeRow === index }"
				class="btn-toggle-row allow-click"
			>
				<svg-caret width="20"/>
			</base-button>

			<div
				v-if="loadingRows.has(row.id)"
				class="loader-overlay-table"
			>
				<core-loader />
			</div>
		</template>
	</core-wp-table>
</template>

<script setup>
import { computed, ref, onBeforeMount, watch } from 'vue'

import {
	useIndexStatusStore,
	useSearchStatisticsStore,
	useSettingsStore
} from '@/vue/stores'

import { debounce } from 'lodash'
import { __ } from '@/vue/plugins/translations'
import { useIndexStatus } from '@/vue/composables/IndexStatus'
import { usePostTypes } from '@/vue/composables/PostTypes'
import { useWpTable } from '@/vue/composables/WpTable'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgLink from '@/vue/components/common/svg/Link'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'

const td                     = import.meta.env.VITE_TEXTDOMAIN
const indexStatusStore       = useIndexStatusStore()
const searchStatisticsStore  = useSearchStatisticsStore()
const settingsStore          = useSettingsStore()
const changeItemsPerPageSlug = 'searchStatisticsIndexStatus'
const tableId                = 'index-status-objects-table'
const strings                = {
	richResults         : __('Rich Results', td),
	richResultTypes     : __('Rich Result Types', td),
	richResultTypesHelp : __('Results of the Google Rich Results Test. These are the different schema graphs that are added to the post. Empty if no rich results were found.', td),
	indexStatusResult   : __('Index Status Result', td),
	sitemaps            : __('Sitemaps', td),
	sitemapsHelp        : __('Any sitemaps that this URL was listed in, as known by Google. Not guaranteed to be an exhaustive list, especially if Google did not discover this URL through a sitemap. Empty if no entries in sitemaps were found.', td),
	referringPages      : __('Referring URLs', td),
	referringPagesHelp  : __('URLs that link to the inspected URL, directly and indirectly.', td),
	issues              : __('Issues', td),
	noIssues            : __('No issues', td),
	notAvailable        : __('N/A', td),
	refreshStatus       : __('Refresh Status', td)
}

const props = defineProps({
	paginatedRows   : Object,
	selectedStatus  : String,
	showTableFooter : {
		type    : Boolean,
		default : true
	},
	showHeader : {
		type    : Boolean,
		default : true
	}
})

const loadingRows = ref(new Set())
const table       = ref(null)

const {
	items: indexStatusItems,
	parseRichResults,
	strings: indexStatusStrings
} = useIndexStatus()

const {
	editPost,
	viewPost
} = usePostTypes()

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
	selectedFilters,
	wpTableKey,
	wpTableLoading
} = useWpTable({
	changeItemsPerPageSlug,
	fetchData       : indexStatusStore.fetchIndexStatusObjects,
	tableId,
	tableRef        : table.value,
	selectedFilters : {}
})

const parseSelectedFilters = computed(() => {
	if (props.selectedStatus) {
		return { ...selectedFilters.value, status: props.selectedStatus }
	}

	return selectedFilters.value
})

const pageNumber = computed(() => {
	return props.paginatedRows.totals.page
})

const tableColumns = computed(() => {
	return [
		{
			slug     : 'title',
			label    : __('Title', td),
			sortable : 1 < props.paginatedRows.totals.total,
			sortDir  : 'title' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'title' === orderBy.value
		},
		{
			slug  : 'verdict',
			label : indexStatusItems.verdict.title,
			width : '110px'
		},
		{
			slug  : 'robotsTxt',
			label : indexStatusItems.robotsTxtState.title,
			width : '110px'
		},
		{
			slug  : 'pageFetch',
			label : indexStatusItems.pageFetchState.title,
			width : '110px'
		},
		{
			slug  : 'crawledAs',
			label : indexStatusItems.crawledAs.title,
			width : '120px'
		},
		{
			slug  : 'richResults',
			label : strings.richResults,
			width : '135px'
		},
		{
			slug     : 'lastCrawlTime',
			label    : indexStatusItems.lastCrawlTime.title,
			sortable : 1 < props.paginatedRows.totals.total,
			sortDir  : 'lastCrawlTime' === orderBy.value ? orderDir.value : 'asc',
			sorted   : 'lastCrawlTime' === orderBy.value,
			width    : '120px'
		},
		{
			slug  : 'buttons',
			label : '',
			width : '60px'
		}
	]
})

const toggleRow = async (index, _row, editRow) => {
	editRow(index)
}

const parseInnerRowValue = (row, key) => {
	const parsed = indexStatusItems[key].parseValue(row[key])
	let description = parsed?.description

	if ('verdict' === key && row.coverageState) {
		description = row.coverageState
	}

	if (!description) {
		return `${parsed.value}`
	}

	return `${parsed.value} <div class="inner-row__desc">${description}</div>`
}

const refreshObjectStatus = async (row) => {
	table.value.activeRow = null

	loadingRows.value.add(row.id)

	try {
		await searchStatisticsStore.getInspectionResult({
			paths : row.path,
			force : true
		})
	} catch (error) {
		console.error(error)
	} finally {
		loadingRows.value.delete(row.id)

		try {
			// When "Refresh Status" runs on multiple rows, fetch objects only after all refreshes complete.
			if (!loadingRows.value.size) {
				await Promise.all([
					indexStatusStore.fetchIndexStatusOverview(),
					indexStatusStore.fetchIndexStatusObjects()
				])
			}
		} catch (error) {
			console.error(error)
		}
	}
}

const maybeGetInspectionResults = debounce(async (rows) => {
	try {
		const paths = rows.map(row => {
			loadingRows.value.add(row.id)

			return row.path
		})

		await searchStatisticsStore.getInspectionResult({ paths })
	} catch (error) {
		console.error(error)
	} finally {
		rows.forEach(row => {
			loadingRows.value.delete(row.id)
		})

		try {
			await Promise.all([
				indexStatusStore.fetchIndexStatusOverview(),
				indexStatusStore.fetchIndexStatusObjects()
			])
		} catch (error) {
			console.error(error)
		}
	}
}, 2000)

onBeforeMount(async () => {
	if (
		searchStatisticsStore.isConnected &&
		!searchStatisticsStore.shouldShowSampleReports &&
		!indexStatusStore.objects.paginated.rows.length
	) {
		try {
			await Promise.all([
				indexStatusStore.fetchIndexStatusOverview(),
				indexStatusStore.fetchIndexStatusObjects()
			])
		} catch (error) {
			console.error(error)
		}
	}
})

watch(() => props.paginatedRows.rows, (newVal) => {
	const rows = newVal.filter(row => !row.isInspectionValid)
	if (rows.length) {
		maybeGetInspectionResults(rows)
	}
})
</script>

<style lang="scss">
#index-status-objects-table {
	thead th.manage-column,
	tfoot th.manage-column {
		font-size: 13px;
	}

	th.verdict,
	td.verdict,
	th.robotsTxt,
	td.robotsTxt,
	th.crawledAs,
	td.crawledAs,
	th.pageFetch,
	td.pageFetch {
		text-align: center;
	}

	td.lastCrawlTime {
		line-height: normal;
	}

	.tablenav.top {
		display: flex;
		justify-content: space-between;
		position: relative;

		.aioseo-wp-additional-filters {
			flex: 1 1 auto;

			select {
				margin-bottom: 8px;
				margin-right: 8px;
				max-width: 10rem;

				&[name="postType"] {
					bottom: 100%;
					position: absolute;
				}
			}

			button {
				float: left;
			}
		}

		.pagination {
			flex: 0 0 auto;
		}
	}

	.main-row {
		td.richResults {
			.rich-result-type {
				align-items: center;
				display: flex;
				gap: 4px;
				line-height: normal;

				+ .rich-result-type {
					margin-top: 6px;
				}

				&__label {
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}

				svg {
					color: #8C8F9A;
					flex: 0 0 15px;
					height: 15px;
					width: 15px;
				}
			}
		}

		.loader-overlay-table {
			left: 0;
			top: 0;
		}
	}

	.inner-row {
		background-color: #fff;
		border: 1px solid $border;
		font-size: 14px;
		line-height: normal;
		overflow-x: auto;
		padding: 15px;

		&__heading {
			align-items: center;
			background-color: $background;
			border-radius: 4px;
			color: #141B38;
			display: flex;
			font-weight: 700;
			margin-bottom: 8px;
			margin-top: 12px;
			padding: 12px;
		}

		&__desc {
			color: #8C8F9A;
			font-size: 12px;
			margin-top: 2px;
		}

		.inner-table {
			border-collapse: collapse;
			table-layout: fixed;

			&__col {
				align-items: center;
				column-gap: 8px;
				display: flex;
				word-break: break-word;

				&--items-start {
					align-items: flex-start;
				}

				svg {
					flex: 0 0 20px;
					width: 20px;
				}
			}

			&__inspection-link {
				align-items: center;
				display: inline-flex;
				gap: 5px;
				line-height: 1;
			}

			thead {
				th {
					border: none;
					height: 0;
				}
			}

			tbody {
				vertical-align: top;
			}

			th {
				font-weight: 600;
			}

			th,
			td {
				color: #141B38;
				line-height: normal;
			}

			&--index-status {
				thead {
					th {
						&:nth-child(1) {
							width: 16%;
						}

						&:nth-child(2) {
							width: 32%;
						}

						&:nth-child(3) {
							width: 20%;
						}

						&:nth-child(4) {
							width: 32%;
						}
					}
				}
			}

			&--rich-results {
				tbody {
					tr + tr {
						border-top: 1px solid $border;
					}
				}

				th {
					&:nth-child(1) {
						width: 165px;
					}
				}

				.rich-result-type-item {
					display: grid;
					gap: 16px;
					grid-template-columns: 2fr 6fr;
				}

				.rich-result-type-item-issues {
					display: grid;
					gap: 5px;
				}
			}
		}
	}

	.btn-toggle-row {
		display: flex;
		height: 26px;
		margin: 0 auto;
		padding: 0;
		width: 30px;

		svg {
			transform: rotate(-90deg);
			transition: transform 0.3s;
		}

		&.active {
			svg {
				transform: rotate(0);
			}
		}
	}

	.aioseo-circle-check {
		color: $green;
	}

	.aioseo-circle-exclamation {
		color: $orange;
	}

	.aioseo-circle-close {
		color: $red;
	}

	.allow-click {
		cursor: pointer;
	}
}
</style>