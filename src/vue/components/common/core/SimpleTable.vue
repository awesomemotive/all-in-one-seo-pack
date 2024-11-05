<template>
	<div class="aioseo-simple-table aioseo-wp-table">
		<div
			class="header aioseo-wp-table-header"
			v-if="!hideHeader"
		>
			<core-wp-pagination
				v-if="!disablePagination && hasPagination && !hideHeaderPagination"
				:totals="paginationTotals"
				:initial-page-number="currentPage"
				:disable-table="0 === rows.length"
				@paginate="page => processPaginate(page)"
			/>
			<div
				v-if="!disableSort && !hideSortDropdown"
				class="sort"
			>
				<div>
					{{ strings.sortBy }}:
				</div>
				<base-select
					:searchable="false"
					:options="getSortableColumnsOptions"
					:modelValue="getSortableColumnsOptions.find(col => col.value === currentSort.slug)"
					@update:modelValue="option => doSort(option)"
					size="small"
					:placeholder="getSortableColumnsOptions[0]?.label"
				>

				</base-select>
			</div>
			<div
				v-if="!disableExport"
				class="export"
			>
				<base-button
					size="small"
					type="gray"
					@click="() => this.export()"
				>
					<svg-download/>
					{{ strings.csv }}
				</base-button>
			</div>
		</div>
		<div class="wp-table">
			<table
				ref="table"
				aria-label="Table"
			>
				<thead>
				<tr>
					<th
						scope="col"
						v-for="(column, index) in columns"
						:key="index"
						:style="{ width: column.width }"
						:class="[{
							sortable : !disableSort && column.sortable && sortableColumns,
							asc      : 'asc' === currentSort.dir && column.sortable,
							desc     : 'desc' === currentSort.dir && column.sortable,
							sorted   : column.sortable && column.slug === currentSort.column,
						}, column.slug]"
						@click.prevent="sortableColumns ? doSort(column, true) : false"
					>
						<span>{{ column.label }}</span>
						<span
							class="sort-indicator"
							v-if="column.sortable && sortableColumns && column.slug === currentSort.slug"
						>
							<span
								v-if="'asc' === currentSort.dir"
								class="dashicons dashicons-arrow-up"
							></span>
							<span
								v-else
								class="dashicons dashicons-arrow-down"
							></span>
						</span>
					</th>
				</tr>
				</thead>

				<tbody v-if="paginatedRows">
				<div
					class="loader-overlay"
					v-if="loading"
				>
					<core-loader/>
				</div>

				<template
					v-for="(row, index) in paginatedRows"
					:key="index + '_' + row.id"
				>
					<tr
						class="main-row"
						:class="{
								even    : 0 === index % 2,
								enabled : row.enabled || !row.hasOwnProperty('enabled')
							}"
						:data-row-id="(row.rowIndex && row[row.rowIndex]) || row.id || row.url || index"
					>
						<td
							class="manage-column"
							:class="column.slug"
							v-for="(column, i) in columns"
							:key="i"
						>
							<template
								v-if="$slots[column.slug]"
							>
								<slot
									:name="column.slug"
									:row="row"
									:column="row[column.slug]"
									:index="index"
								/>
							</template>

							<span
								v-if="!$slots[column.slug]"
							>
									{{ row[column.slug] }}
								</span>
						</td>
					</tr>
				</template>

				<template
					v-if="!rows.length"
				>
					<td :colspan="columns.length + 1">
						<div class="no-results">
							<span v-if="!loading">{{ strings.noResults }}</span>
						</div>
					</td>
				</template>
				</tbody>

				<tfoot
					v-if="showTableFooter"
				>
				<tr>
					<th
						scope="col"
						v-for="(column, index) in columns"
						:key="index"
						:style="{ width: column.width }"
						class="manage-column"
					>
						{{ column.label }}
					</th>
				</tr>
				</tfoot>
			</table>
		</div>
		<div
			class="bottom"
			v-if="!disablePagination && hasPagination"
		>
			<core-wp-pagination
				:totals="paginationTotals"
				:initial-page-number="currentPage"
				:disable-table="0 === rows.length"
				@paginate="page => processPaginate(page)"
			/>
		</div>
	</div>
</template>

<script>
import CoreLoader from '@/vue/components/common/core/Loader'
import { arrayToCsv } from '@/vue/utils/csv'
import { downloadFile } from '@/vue/utils/download'
import SvgDownload from '@/vue/components/common/svg/Download'
import CoreWpPagination from '@/vue/components/common/core/wp/Pagination'
import BaseSelect from '@/vue/components/common/base/Select'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreLoader,
		SvgDownload,
		CoreWpPagination,
		BaseSelect
	},
	props : {
		columns : {
			type     : Array,
			required : true
		},
		rows : {
			type     : Array,
			required : true
		},
		loading         : Boolean,
		showTableFooter : {
			type : Boolean,
			default () {
				return false
			}
		},
		disablePagination : {
			type : Boolean,
			default () {
				return false
			}
		},
		hideHeaderPagination : {
			type : Boolean,
			default () {
				return false
			}
		},
		hideSortDropdown : {
			type : Boolean,
			default () {
				return false
			}
		},
		disableExport : {
			type : Boolean,
			default () {
				return false
			}
		},
		disableSort : {
			type : Boolean,
			default () {
				return false
			}
		},
		hideHeader : {
			type : Boolean,
			default () {
				return false
			}
		},
		sortableColumns : {
			type : Boolean,
			default () {
				return false
			}
		},
		perPage : {
			type : Number,
			default () {
				return 20
			}
		},
		exportColumns : {
			type : Array,
			default () {
				return []
			}
		},
		exportFileName : String,
		sort           : Object
	},
	data () {
		return {
			strings : {
				items     : __('items', td),
				noResults : __('No items found.', td),
				sortBy    : __('Sort by', td),
				csv       : __('CSV', td),
				position  : __('Position', td)
			},
			currentSort : {},
			currentPage : 1
		}
	},
	watch : {
		sort (newVal) {
			this.currentSort = newVal
		}
	},
	computed : {
		getExportFileName () {
			const exportName = this.exportFileName || 'entries.csv'

			return exportName.replace('/.csv$|$/', '.csv')
		},
		paginationTotals () {
			return {
				page  : 1,
				pages : Math.ceil(this.rows.length / this.perPage),
				total : this.rows.length
			}
		},
		paginatedRows () {
			return this.rows.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage)
		},
		hasPagination () {
			return 1 < this.paginationTotals.pages
		},
		getSortableColumnsOptions () {
			const columns = this.columns.filter(column => column.sortable)
			return columns.map(({ ...column }) => {
				column.label = '#' === column.label ? this.strings.position : column.label
				column.value = column.slug
				return column
			})
		}
	},
	methods : {
		doSort (column, orderToggle = false) {
			this.currentSort.slug = column.slug
			this.currentSort.column = column.sortBy ?? column.slug
			if (orderToggle) {
				this.currentSort.dir = 'asc' === this.currentSort.dir ? 'desc' : 'asc'
			} else {
				this.currentSort.dir = column.sortOrder ? column.sortOrder : ('asc' === this.currentSort.dir ? 'desc' : 'asc')
			}

			this.$emit('sort-column', this.currentSort)
		},
		processPaginate (page) {
			this.currentPage = page
		},
		export () {
			// Determine which columns to export.
			const colsToExport = this.exportColumns || this.columns

			// Map data do export same as exportColumns.
			let exportData = this.rows.map((row) => {
				const newRow = []
				colsToExport.forEach((col) => {
					newRow[col.slug] = col?.value ? col.value(row) : row[col.slug]
				})

				return newRow
			})

			// Extract headers.
			const header = colsToExport.map(col => col.label)

			// Add headers and data.
			exportData = [ header ].concat(exportData)

			// Download.
			downloadFile(arrayToCsv(exportData), this.getExportFileName)
		}
	},
	mounted () {
		if (this.sort) {
			this.currentSort = this.sort
		}
	}
}
</script>

<style lang="scss">
.aioseo-simple-table {
	table {
		width: 100%;
		border-spacing: 0;

		* {
			font-size: 15px;
		}

		thead, tfoot {
			th {
				text-align: left;
				color: $placeholder-color;
				padding: 0 10px 12px;
				white-space: nowrap;
				font-size: 14px;
				font-weight: 400;
				align-items: center;

				&.sortable {
					cursor: pointer;

					a {
						display: flex;
						align-items: center;
						height: 36px;
						padding: 0;
						line-height: 36px;
					}

					.sort-indicator {
						position: relative;
						top: 3px;
					}
				}
			}
		}

		tbody {
			tr {
				td {
					padding: 12px 12px;
					font-size: 15px;
					vertical-align: top;
				}

				&:nth-child(2n-1) {
					td {
						background-color: $box-background;
					}
				}
			}
		}
	}

	.header {
		display: flex;
		flex-direction: row;
		padding-bottom: 32px;

		.sort {
			display: flex;
			flex-direction: row;
			align-items: center;
			gap: 10px;

			> * {
				text-wrap: nowrap;
			}

			.aioseo-select {
				min-width: 154px;
			}
		}
	}

	.bottom {
		padding-top: 20px;
	}

	.filters {
		display: flex;
		flex-direction: row;

		.filter-item {
			display: flex;
			flex-direction: row;
			align-items: center;

			.label {
				white-space: nowrap;
				padding-right: 10px;
			}
		}
	}

	.export {
		margin-left: auto;

		svg {
			width: 14px;
			height: 14px;
			margin-right: 5px;
		}
	}
}
</style>