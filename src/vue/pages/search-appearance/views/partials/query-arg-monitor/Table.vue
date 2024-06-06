<template>
	<div class="aioseo-query-args-table">
		<core-alert
			v-if="showAlert"
			:type="alertType"
			size="medium"
			v-html="alertDescription"
		/>

		<core-wp-table
			:id="tableId"
			:bulk-options="bulkOptions"
			:columns="columns"
			:filters="filters"
			:initial-items-per-page="settingsStore.settings.tablePagination.queryArgs"
			:initial-page-number="pageNumber"
			:initial-search-term="searchTerm"
			:key="wpTableKey"
			:loading="wpTableLoading"
			:rows="rows"
			:search-label="strings.searchUrls"
			:selected-filters="selectedFilters"
			:show-bulk-actions="showBulkActions"
			:show-header="showHeader"
			:show-table-footer="showTableFooter"
			:totals="totals"
			show-items-per-page
			@search="processSearch"
			@process-bulk-action="maybeDoBulkAction"
			@filter-table="doProcessFilterTable"
			@paginate="doProcessPagination"
			@process-change-items-per-page="processChangeItemsPerPage"
		>
			<template #actionsHeaderFooter>
				{{ strings.actions }}
				<core-tooltip
					placement="left"
				>
					<svg-circle-question-mark />
					<template #tooltip>
						{{ strings.crawlCleanupTooltip }}
					</template>
				</core-tooltip>
			</template>

			<template #slug="{ row }">
				{{ getSlug(row) }}
			</template>

			<template #hits="{ row }">
				{{ parseInt(row.hits).toLocaleString() }}
			</template>

			<template #regex="{ row }">
				{{ (!row.regex || '0' === row.regex) ? strings.no : strings.yes }}
			</template>

			<template #actions="{ row }">
				<div class="actions-query-args">
					<div
						v-if="isBlockedSelected"
					>
						<span
							@click="maybeDoBulkAction( {
									action: 'unblock',
									selectedRows: [ row.id ]
								}
							)"
						>
							{{ strings.unblock }}
						</span>
					</div>
					<div
						v-if="!isBlockedSelected"
					>
						<div
							v-if="hasRegexBlock(row.id)"
						>
							<span class="no-link">
								{{ strings.blockedByRegex }}
								{{ hasRegexBlock(row.id) }}
							</span>
						</div>
						<div
							v-else
						>
							<span
								@click="maybeDoBulkAction( {
										action: 'block-key',
										selectedRows: [ row.id ]
									}
								)"
							>
								{{ strings.blockKey }}
							</span>
							<span class="separator">|</span>
							<span
								@click="maybeDoBulkAction( {
										action: 'block-key-value',
										selectedRows: [ row.id ]
									}
								)"
							>
								{{ strings.blockKeyValue }}
							</span>
						</div>
					</div>
				</div>
			</template>
		</core-wp-table>

		<core-modal
			:show="showModal"
			no-header
			:classes="[ 'block-arg-modal' ]"
			@close="showModal = false"
		>
			<template #body >
				<div class="aioseo-modal-body delete">
					<button
						class="close"
					>
						<svg-close @click="showModal = false" />
					</button>

					<h3>{{ modalMessagemTitle }}</h3>

					<div class="reset-description">
						{{ modalMessageDescription }}
					</div>

					<base-button
						v-if="showDeleteModal"
						type="blue"
						size="medium"
						@click="processQueryDelete"
						@click.stop="showModal = false"
					>
						{{ yesDeleteQuery }}
					</base-button>

					<base-button
						v-if="showBlockModal"
						type="blue"
						size="medium"
						@click="processQueryBlock"
						@click.stop="showModal = false"
					>
						{{ yesBlockQuery }}
					</base-button>

					<base-button
						v-if="showUnblockModal"
						type="blue"
						size="medium"
						@click="processQueryUnblock"
						@click.stop="showModal = false"
					>
						{{ yesUnblockQuery }}
					</base-button>

					<base-button
						type="gray"
						size="medium"
						@click="showModal = false"
					>
						{{ strings.noChangedMind }}
					</base-button>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>

import {
	useOptionsStore,
	useSettingsStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgClose from '@/vue/components/common/svg/Close'
import { Date } from '@/vue/mixins/Date'
import { debounce } from '@/vue/utils/debounce'

export default {
	setup () {
		return {
			optionsStore  : useOptionsStore(),
			settingsStore : useSettingsStore()
		}
	},
	mixins     : [ Date ],
	components : {
		CoreAlert,
		CoreModal,
		CoreTooltip,
		CoreWpTable,
		SvgCircleQuestionMark,
		SvgClose
	},
	props : {
		showBulkActions : {
			type : Boolean,
			default () {
				return true
			}
		},
		showTableFooter : {
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
		blockArgs : []
	},
	data () {
		return {
			tableId          : 'aioseo-query-args-wp-table',
			showAlert        : false,
			alertDescription : null,
			alertType        : null,
			strings          : {
				delete                 : this.$t.__('Delete', this.$td),
				regex                  : this.$t.__('Regex', this.$td),
				key                    : this.$t.__('Key', this.$td),
				value                  : this.$t.__('Value', this.$td),
				yes                    : this.$t.__('Yes', this.$td),
				no                     : this.$t.__('No', this.$td),
				at                     : this.$t.__('at', this.$td),
				blockKey               : this.$t.__('Block Key', this.$td),
				blockKeyValue          : this.$t.__('Block Key & Value', this.$td),
				unblock                : this.$t.__('Unblock', this.$td),
				success                : this.$t.__('The query arg has been successfully added to the blocklist.', this.$td),
				unsuccess              : this.$t.__('An error occurred while trying to add the query arg to the blocklist. Please try again later.', this.$td),
				unblockSuccess         : this.$t.__('The query arg has been successfully unblocked.', this.$td),
				unblockUnsuccess       : this.$t.__('An error occurred while trying to remove the query arg from the blocklist. Please try again later.', this.$td),
				deleteSuccess          : this.$t.__('The query arg has been successfully deleted.', this.$td),
				deleteUnsuccess        : this.$t.__('An error occurred while trying to delete the query arg. Please try again later.', this.$td),
				sureDeleteQuery        : this.$t.__('Are you sure you want to delete this query arg?', this.$td),
				sureDeleteQueries      : this.$t.__('Are you sure you want to delete these query args?', this.$td),
				sureBlockQuery         : this.$t.__('Are you sure you want to block this query arg?', this.$td),
				sureBlockQueries       : this.$t.__('Are you sure you want to block these query args?', this.$td),
				sureUnblockQuery       : this.$t.__('Are you sure you want to unblock this query arg?', this.$td),
				sureUnblockQueries     : this.$t.__('Are you sure you want to unblock these query args?', this.$td),
				thisWillRemoveQuery    : this.$t.__('This action will permanently remove this query arg.', this.$td),
				thisWillRemoveQueries  : this.$t.__('This action will permanently remove the selected query args.', this.$td),
				thisWillBlockQuery     : this.$t.__('This action will block this query arg.', this.$td),
				thisWillBlockQueries   : this.$t.__('This action will block the selected query args.', this.$td),
				thisWillUnblockQuery   : this.$t.__('This action will unblock this query arg.', this.$td),
				thisWillUnblockQueries : this.$t.__('This action will unblock the selected query args.', this.$td),
				yesDeleteQuery         : this.$t.__('Delete Query Arg', this.$td),
				yesDeleteQueries       : this.$t.__('Delete Query Args', this.$td),
				yesBlockQuery          : this.$t.__('Block Query Arg', this.$td),
				yesBlockQueries        : this.$t.__('Block Query Args', this.$td),
				yesUnblockQuery        : this.$t.__('Unblock Query Arg', this.$td),
				yesUnblockQueries      : this.$t.__('Unblock Query Args', this.$td),
				noChangedMind          : this.$t.__('No, I changed my mind', this.$td),
				actions                : this.$t.__('Actions', this.$td),
				crawlCleanupTooltip    : this.$t.__('The "Block key" blocks both the key and all of its values, while "Block Key & values" lets you selectively block only certain values, without blocking the entire key.', this.$td),
				blockedArgExists       : this.$t.__('The query arg you\'ve entered is already being blocked.', this.$td),
				blockedByRegex         : this.$t.__('Blocked by regex: ', this.$td)
			},
			showModal        : false,
			showDeleteModal  : false,
			showBlockModal   : false,
			showUnblockModal : false,
			rows             : [],
			regexMatches     : [],
			filters          : [],
			selectedFilters  : {
				slug   : 'unblocked',
				name   : this.$t.__('Unblocked', this.$td),
				count  : 0,
				active : true
			},
			totals : {
				total : 0,
				pages : 0,
				page  : 1
			},
			pageNumber              : 1,
			wpTableKey              : 0,
			searchTerm              : null,
			wpTableLoading          : false,
			limit                   : this.settingsStore.settings.tablePagination.queryArgs,
			isMultipleSelected      : false,
			modalMessagemTitle      : null,
			modalMessageDescription : null
		}
	},
	computed : {
		columns () {
			const columns = [
				{
					slug  : 'slug',
					label : this.$t.__('URL', this.$td),
					with  : '100%'
				}
			]
			if ('blocked' === this.selectedFilters.slug) {
				columns.push({
					slug  : 'regex',
					label : this.$t.__('Regex', this.$td),
					width : '60px'
				})
			}

			columns.push({
				slug  : 'hits',
				label : this.$t.__('Hits', this.$td),
				width : '110px'
			},
			{
				slug  : 'updated',
				label : this.$t.__('Last Accessed', this.$td),
				width : '250px'
			},
			{
				slug  : 'actions',
				label : this.$t.__('Actions', this.$td),
				width : this.isBlockedSelected ? '100px' : '220px'
			})

			return columns
		},
		isBlockedSelected () {
			return 'blocked' === this.selectedFilters.slug
		},
		bulkOptions () {
			const options = []

			if ('unblocked' === this.selectedFilters.slug) {
				options.push({ label: this.$t.__('Block Key', this.$td), value: 'block-key' })
				options.push({ label: this.$t.__('Block Key & Value', this.$td), value: 'block-key-value' })
			}

			if ('blocked' === this.selectedFilters.slug) {
				options.push({ label: this.$t.__('Unblock', this.$td), value: 'unblock' })
			}

			options.push({ label: this.$t.__('Delete', this.$td), value: 'delete' })

			return options
		},
		yesDeleteQuery () {
			return this.isMultipleSelected ? this.strings.yesDeleteQueries : this.strings.yesDeleteQuery
		},
		yesBlockQuery () {
			return this.isMultipleSelected ? this.strings.yesBlockQueries : this.strings.yesBlockQuery
		},
		yesUnblockQuery () {
			return this.isMultipleSelected ? this.strings.yesUnblockQueries : this.strings.yesUnblockQuery
		}
	},
	methods : {
		fetchData () {
			this.wpTableLoading = true
			http.post(links.restUrl('crawl-cleanup'))
				.send({
					slug       : this.slug,
					limit      : this.limit ? this.limit : this.settingsStore.settings.tablePagination.queryArgs,
					offset     : 1 === this.pageNumber ? 0 : (this.pageNumber - 1) * this.limit,
					searchTerm : this.searchTerm,
					filter     : this.selectedFilters ? this.selectedFilters.slug : 'unblocked'
				})
				.then(response => {
					this.filters        = response.body.filters
					this.rows           = response.body.rows
					this.regexMatches   = response.body.regex
					this.totals         = response.body.totals
					this.pageNumber     = response.body.totals.page

					this.wpTableLoading = false
				})
		},
		getSlug (row) {
			if ('blocked' === this.selectedFilters.slug) {
				if (row.regex) {
					return row.regex
				}

				return '?' + row.key + '=' + (row.value ? row.value : '*')
			}

			return row.slug
		},
		blockKey (data) {
			http.post(links.restUrl('crawl-cleanup/block/'))
				.send(data)
				.then(response => {
					if (false === response.body.success) {
						this.openAlert(this.strings.unsuccess, 'red')
						if (response.body.error && 1 === response.body.error) {
							this.openAlert(this.strings.blockedArgExists, 'yellow')
						}
					}

					this.fetchData()
				})
		},
		unblockKey (data) {
			http.post(links.restUrl('crawl-cleanup/delete-blocked/'))
				.send(data)
				.then(response => {
					if (false === response.body.success) {
						this.openAlert(this.strings.unblockUnsuccess, 'red')
					}

					this.fetchData()
				})
				.catch(error => {
					console.error(error)
				})
		},
		openAlert (message, type = 'green') {
			this.showAlert        = true
			this.alertDescription = message
			this.alertType        = type
		},
		processSearch (searchTerm) {
			this.searchTerm = searchTerm
			this.pageNumber = 1
			this.fetchData()
		},
		maybeDoBulkAction ({ action, selectedRows }) {
			if (!action || !selectedRows.length) {
				return
			}

			this.showDeleteModal         = 'delete' === action
			this.showUnblockModal        = 'unblock' === action
			this.showBlockModal          = 'block-key' === action || 'block-key-value' === action

			this.showModal               = this.showDeleteModal || this.showBlockModal || this.showUnblockModal
			this.action                  = action
			this.selectedRows            = selectedRows

			this.isMultipleSelected      = Array.isArray(selectedRows) && 1 < selectedRows.length
			this.modalMessagemTitle      = this.areYouSureDeleteQuery()
			this.modalMessageDescription = this.thisWillRemoveQuery()

			if ('block-key' === action || 'block-key-value' === action) {
				this.modalMessageDescription = this.thisWillBlockQuery()
				this.modalMessagemTitle      = this.areYouSureBlockQuery()
			}
			if ('unblock' === this.action) {
				this.modalMessageDescription = this.thisWillUnblockQuery()
				this.modalMessagemTitle      = this.areYouSureUnblockQuery()
			}
		},
		areYouSureDeleteQuery () {
			return this.isMultipleSelected ? this.strings.sureDeleteQueries : this.strings.sureDeleteQuery
		},
		areYouSureBlockQuery () {
			return this.isMultipleSelected ? this.strings.sureBlockQueries : this.strings.sureBlockQuery
		},
		areYouSureUnblockQuery () {
			return this.isMultipleSelected ? this.strings.sureUnblockQueries : this.strings.sureUnblockQuery
		},
		thisWillRemoveQuery () {
			return this.isMultipleSelected ? this.strings.thisWillRemoveQueries : this.strings.thisWillRemoveQuery
		},
		thisWillBlockQuery () {
			return this.isMultipleSelected ? this.strings.thisWillBlockQueries : this.strings.thisWillBlockQuery
		},
		thisWillUnblockQuery () {
			return this.isMultipleSelected ? this.strings.thisWillUnblockQueries : this.strings.thisWillUnblockQuery
		},
		processQueryDelete () {
			this.wpTableLoading = true

			if ('blocked' === this.selectedFilters.slug) {
				http.post(links.restUrl('crawl-cleanup/delete-blocked/'))
					.send(this.selectedRows)
					.then(response => {
						this.showModal      = false
						this.wpTableLoading = false

						this.openAlert(this.strings.deleteSuccess)

						if (false === response.body.success) {
							this.openAlert(this.strings.deleteSuccess, 'red')
						}

						this.fetchData()
					})
					.catch(error => {
						console.error(error)
					})
			}
			if ('unblocked' === this.selectedFilters.slug) {
				http.post(links.restUrl('crawl-cleanup/delete-unblocked/'))
					.send(this.selectedRows)
					.then(response => {
						this.showModal      = false
						this.wpTableLoading = false

						this.openAlert(this.strings.deleteSuccess)

						if (false === response.body.success) {
							this.openAlert(this.strings.deleteUnsuccess, 'red')
						}

						this.fetchData()
					})
					.catch(error => {
						console.error(error)
					})
			}
		},
		processQueryBlock () {
			// The filter is to remove unsaved options
			const selected = this.selectedRows.map((opt) => {
				const r = this.rows.find(row => row.id === opt)
				if (undefined === r) {
					return null
				}
				return {
					key   : r.key,
					value : 'block-key-value' === this.action ? r.value : null,
					regex : r.regex
				}
			}).filter(r => null !== r)
			this.blockKey(selected)
		},
		processQueryUnblock () {
			this.unblockKey(this.selectedRows)
		},
		processChangeItemsPerPage (number) {
			this.limit      = number
			this.pageNumber = 1
			this.fetchData()
		},
		doProcessFilterTable (filter) {
			this.pageNumber      = 1
			this.searchTerm      = null
			this.selectedFilters = filter
			this.fetchData()
		},
		doProcessPagination (pageNumber) {
			this.pageNumber = pageNumber
			this.fetchData()
		},
		hasRegexBlock (logID) {
			if (this.regexMatches[logID]) {
				return this.regexMatches[logID]
			}
			return false
		}
	},
	watch : {
		showAlert (showAlert) {
			if (!showAlert) {
				return
			}

			debounce(() => {
				this.showAlert = false
			}, 5000)
		}
	},
	mounted () {
		this.fetchData()

		window.aioseoBus.$on('blockArgAdded', (response) => {
			this.wpTableLoading = true

			// Set it to show blocked list after include.
			this.selectedFilters = { slug: 'blocked' }

			if (false === response.body.success) {
				this.openAlert(this.strings.unsuccess, 'red')
				if (response.body.error && 1 === response.body.error) {
					this.openAlert(this.strings.blockedArgExists, 'yellow')
				}
				this.wpTableLoading = false
			}

			this.fetchData()
		})
	}
}
</script>

<style lang="scss">
.aioseo-query-args-table {
	margin-top: 20px;

	thead, tfoot {
		th {
			white-space: nowrap;
		}
	}

	.aioseo-wp-table .widefat .check-column {
		padding: 11px 0 11px 3px;
	}

	.aioseo-tooltip {
		display: inline-block;
		margin: 0;
		line-height: 10px;
		vertical-align: sub;

		svg {
			cursor: pointer;
			height: 16px;
			width: 16px;
		}
	}

	.aioseo-alert {
		margin: 20px 0;
	}

	.actions-query-args {
		div {
			display: flex;
			align-items: start;
			gap: 5px;

			span {
				cursor: pointer;
				color: $blue;

				&.separator {
					color: #8C8F9A;
				}

				&.no-link {
					cursor: text;
					color: $black;
				}
			}
		}
	}
}

.block-arg-modal {
	.modal-container {
		max-width: 650px !important;
	}

	.aioseo-modal-body {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;
		margin: 20px 0 50px 0;

		button.close {
			position: absolute;
			right: 11px;
			top: 0;
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

		.reset-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 16px;
			text-align: center;
			max-width: 515px;
		}

		.aioseo-button:not(.close):not(.import) {
			margin-top: 16px;
		}
	}
}
</style>