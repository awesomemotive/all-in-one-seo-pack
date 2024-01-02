<template>
	<core-card
		v-if="optionsStore.options.sitemap.general.enable"
		slug="additionalPages"
		:toggles="optionsStore.options.sitemap.general.additionalPages.enable"
	>
		<template #header>
			<base-toggle
				v-model="optionsStore.options.sitemap.general.additionalPages.enable"
			/>

			<span>{{ strings.additionalPages }}</span>
		</template>

		<template #tooltip>
			{{ strings.additionalPagesTooltip }}
		</template>

		<div class="aioseo-additional-pages">
			<add-additional-page
				:getPaginatedIndex="getPaginatedIndex"
				:getParsedPages="getParsedPages"
				@process-page-add-and-update="processPageAddAndUpdate"
			/>

			<core-wp-table
				ref="table"
				class="additional-pages-table"
				:id="tableId"
				:bulk-options="bulkOptions"
				:columns="columns"
				:initial-items-per-page="settingsStore.settings.tablePagination.sitemapAdditionalPages"
				:initial-page-number="pageNumber"
				:key="wpTableKey"
				:loading="wpTableLoading"
				:rows="rows"
				:search-label="strings.searchUrls"
				:show-search="true"
				:totals="totals"
				show-items-per-page
				@paginate="processPagination"
				@process-bulk-action="processBulkAction"
				@process-change-items-per-page="processChangeItemsPerPage"
				@search="processSearch"
				@sort-column="processSort"
			>
				<template #url="{ row, index, editRow }">
					<a
						class="post-title"
						href="#"
						@click.prevent.stop="editRow(index)"
					>
						{{ row.url }}
					</a>

					<div class="row-actions">
						<span>
							<a
								class="edit"
								href="#"
								@click.prevent.stop="editRow(index)"
							>
								<span>{{ strings.edit }}</span>
							</a> |
						</span>

						<span>
							<a
								class="delete"
								href="#"
								@click.prevent.stop="maybeProcessDelete(row.url)"
							>
								<span>{{ strings.delete }}</span>
							</a>
						</span>
					</div>
				</template>

				<template #edit-row="{ index, editRow }">
					<add-additional-page
						:index="index"
						:rowPage="rowPage( index )"
						:editedPage="editedPage"
						:getPaginatedIndex="getPaginatedIndex"
						:getParsedPages="getParsedPages"
						inTable
						@cancel="editRow(null)"
						@process-page-add-and-update="processPageAddAndUpdate"
						@process-page-edit="processPageEdit"
					/>
				</template>

				<template #page-actions="{ row }">
					<div class="page-actions">
						<core-tooltip
							type="action"
						>
							<svg-trash
								@click.native="maybeProcessDelete(row.url)"
							/>

							<template #tooltip>
								{{ strings.delete }}
							</template>
						</core-tooltip>
					</div>
				</template>
			</core-wp-table>

			<core-modal
				:show="showDeleteModal"
				no-header
				@close="showDeleteModal = false"
				:classes="[ 'aioseo-additional-pages-modal' ]"
			>
				<template #body >
					<div class="aioseo-modal-body delete">
						<button
							class="close"
							@click.stop="showDeleteModal = false"
						>
						</button>

						<h3>{{ areYouSureDeleteLink }}</h3>

						<div class="reset-description">
							{{strings.thisWillRemoveLink}}
						</div>

						<base-button
							type="blue"
							size="medium"
							@click="processPageDelete"
							:loading="deletingRow"
						>
							{{ yesDeleteLink }}
						</base-button>

						<base-button
							type="gray"
							size="medium"
							@click="showDeleteModal = false"
						>
							{{ strings.noChangedMind }}
						</base-button>
					</div>
				</template>
			</core-modal>
		</div>
	</core-card>
</template>

<script>
import {
	useOptionsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { WpTable } from '@/vue/mixins/WpTable'
import AddAdditionalPage from './partials/AddAdditionalPage'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import CoreCard from '@/vue/components/common/core/Card'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreModal from '@/vue/components/common/core/modal/Index'
import SvgTrash from '@/vue/components/common/svg/Trash'

export default {
	setup () {
		return {
			optionsStore  : useOptionsStore(),
			rootStore     : useRootStore(),
			settingsStore : useSettingsStore()
		}
	},
	mixins     : [ WpTable ],
	components : {
		AddAdditionalPage,
		CoreWpTable,
		CoreCard,
		CoreTooltip,
		CoreModal,
		SvgTrash
	},
	data () {
		return {
			tableId                : 'sitemap-additional-pages',
			changeItemsPerPageSlug : 'sitemapAdditionalPages',
			page                   : {},
			editedPage             : {},
			resultsPerPage         : 10,
			searchResults          : null,
			deletingRow            : false,
			activeRow              : -1,
			showDeleteModal        : false,
			shouldDeleteURL        : null,
			selectedRows           : null,
			bulkOptions            : [
				{ label: this.$t.__('Delete', this.$td), value: 'delete' }
			],
			strings : {
				searchUrls             : this.$t.__('Search URLs', this.$td),
				edit                   : this.$t.__('Edit', this.$td),
				delete                 : this.$t.__('Delete', this.$td),
				additionalPages        : this.$t.__('Additional Pages', this.$td),
				additionalPagesTooltip : this.$t.__('You can use this section to add any URLs to your sitemap which aren\'t a part of your WordPress installation. For example, if you have a contact form that you would like to be included on your sitemap you can enter the information manually.', this.$td),
				areYouSureDeleteLink   : this.$t.__('Are you sure you want to delete this page?', this.$td),
				areYouSureDeleteLinks  : this.$t.__('Are you sure you want to delete these pages?', this.$td),
				thisWillRemoveLink     : this.$t.__('This will permanently remove this page from the additional pages sitemap.', this.$td),
				thisWillRemoveLinks    : this.$t.__('This will permanently remove the selected pages from the additional pages sitemap.', this.$td),
				yesDeleteLink          : this.$t.__('Delete Page', this.$td),
				yesDeleteLinks         : this.$t.__('Delete Selected Pages', this.$td),
				noChangedMind          : this.$t.__('No, I changed my mind', this.$td)
			}
		}
	},
	computed : {
		currentPages () { return this.searchResults || this.getParsedPages() },
		rows () {
			const rows  = this.currentPages.map(page => ({
				url          : page.url,
				priority     : page.priority && page.priority.label ? page.priority.label : '',
				frequency    : page.frequency && page.frequency.label ? page.frequency.label : '',
				lastModified : page.lastModified
			}))

			const offset = 1 === this.pageNumber ? 0 : (this.pageNumber - 1) * this.resultsPerPage

			return rows.slice(offset, offset + this.resultsPerPage)
		},
		totals () {
			return {
				page  : 1,
				pages : Math.ceil(this.currentPages.length / this.resultsPerPage),
				total : this.currentPages.length
			}
		},
		columns () {
			return [
				{
					slug  : 'url',
					label : this.$t.__('Page URL', this.$td)
				},
				{
					slug  : 'priority',
					label : this.$t.__('Priority', this.$td),
					width : '90px'
				},
				{
					slug  : 'frequency',
					label : this.$t.__('Frequency', this.$td),
					width : '90px'
				},
				{
					slug  : 'lastModified',
					label : this.$t.__('Last Modified', this.$td),
					width : '180px'
				},
				{
					slug  : 'page-actions',
					label : '',
					width : '40px'
				}
			]
		},
		areYouSureDeleteLink () {
			return Array.isArray(this.shouldDeleteURL) ? this.strings.areYouSureDeleteLinks : this.strings.areYouSureDeleteLink
		},
		yesDeleteLink () {
			return Array.isArray(this.shouldDeleteURL) ? this.strings.yesDeleteLinks : this.strings.yesDeleteLink
		},
		thisWillRemoveLink () {
			return Array.isArray(this.shouldDeleteURL) ? this.strings.thisWillRemoveLinks : this.strings.thisWillRemoveLink
		}
	},
	methods : {
		// Placeholder method.
		fetchData () {
			return Promise.resolve()
		},
		processSearch (searchTerm) {
			this.$refs.table.editRow(null)
			this.pageNumber = 1

			if ('' === searchTerm) {
				this.searchResults = null
				this.searchTerm    = null
				return
			}

			if (!searchTerm) {
				searchTerm = this.searchTerm
			}

			this.wpTableLoading = true
			this.searchResults  = this.getParsedPages().filter(page => page.url.includes(searchTerm))
			this.searchTerm     = searchTerm
			this.wpTableLoading = false
		},
		processBulkAction ({ action, selectedRows }) {
			if (!selectedRows.length) {
				return
			}

			if ('delete' === action) {
				this.showDeleteModal = true
				this.shouldDeleteURL = selectedRows
			}
		},
		processPageDelete () {
			this.wpTableLoading = true

			if (Array.isArray(this.shouldDeleteURL)) {
				this.shouldDeleteURL.forEach(url => {
					this.deletePage(url)
				})
			} else {
				this.deletePage(this.shouldDeleteURL)
			}

			this.showDeleteModal = false
			this.wpTableLoading = false
		},
		deletePage (url) {
			const pages = []
			this.getParsedPages().forEach((r) => {
				if (r.url !== url) { pages.push(JSON.stringify(r)) }
			})

			this.optionsStore.options.sitemap.general.additionalPages.pages = pages

			if (this.searchResults) this.processSearch()
		},
		maybeProcessDelete (row) {
			this.showDeleteModal = true
			this.shouldDeleteURL = row
		},
		processPageEdit (page) {
			this.editedPage = page
		},
		processPageAddAndUpdate () {
			this.processSearch(this.searchTerm || '')
		},
		rowPage (index) {
			return this.searchResults ? this.searchResults[this.getPaginatedIndex(index)]  : this.getParsedPages()[this.getPaginatedIndex(index)]
		},
		getPaginatedIndex (index) {
			return (this.pageNumber - 1) * this.resultsPerPage + index
		},
		getParsedPages () {
			return this.optionsStore.options.sitemap.general.additionalPages.pages.map(page => JSON.parse(page))
		}
	}
}
</script>

<style lang="scss">
.aioseo-additional-pages {
	.aioseo-wp-table.additional-pages-table {
		border-top: 1px solid $border;
		padding-top: 20px;

		.wp-table {
			.post-title {
				color: $black;

				&:hover {
					color: $blue;
				}
			}

			.page-actions {
				svg.aioseo-trash {
					width: 20px;
					height: 20px;
					color: $placeholder-color;
					cursor: pointer;
					transition: color 0.1s ease;

					&:hover {
						color: $red;
					}
				}
			}

			.aioseo-tooltip {
				display: inline-block;
				margin-left: 0;
			}

			.edit-row-content {
				border-top: 1px solid $border;
				padding: 0;

				.border {
					padding: 0;
					border: none;
				}
			}
		}
	}
}

.aioseo-additional-pages-modal {
	.aioseo-modal-body {
		padding: 20px 40px 40px;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;

		button.close {
			position: absolute;
			right: 11px;
			top: 11px;
			width: 24px;
			height: 24px;
			background-color: #fff;
			border: none;
			display: flex;
			align-items: center;

			svg.aioseo-close {
				cursor: pointer;
				width: 14px;
				height: 14px;
			}
		}

		h3 {
			font-size: 20px;
			margin-bottom: 16px;
		}

		.label {
			padding-bottom: 5px;
			color: $black;
			font-size: 14px;
			font-weight: bold;
		}

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
		}

		.aioseo-button:not(.close):not(.import) {
			margin-top: 16px;
		}
	}

	.modal-wrapper .modal-container {
		max-width: 650px;
	}
}
</style>