<template>
	<div class="aioseo-redirects-table">
		<core-wp-table
			ref="table"
			:id="tableId"
			:additional-filters="additionalFilters"
			:bulk-options="bulkOptions"
			:columns="columns"
			:filters="redirectsStore.filters"
			:initial-items-per-page="settingsStore.settings.tablePagination.redirects"
			:initial-page-number="pageNumber"
			:initial-search-term="searchTerm"
			:key="wpTableKey"
			:loading="wpTableLoading"
			:rows="getRows"
			:search-label="strings.searchUrls"
			:selected-filters="redirectsStore.selectedFilters"
			:show-bulk-actions="showBulkActions"
			:show-header="showHeader"
			:show-table-footer="showTableFooter"
			:totals="redirectsStore.totals.main"
			show-items-per-page
			@filter-table="processFilterTable"
			@paginate="processPagination"
			@process-additional-filters="processAdditionalFilters"
			@process-bulk-action="processBulkAction"
			@process-change-items-per-page="processChangeItemsPerPage"
			@search="processSearch"
			@sort-column="processSort"
		>
			<template #source_url="{ row, index, column, editRow }">
				<strong>
					<a
						class="edit-link"
						href="#"
						@click.prevent="editRow(index)"
					>{{ column }}</a>
				</strong>

				<div class="row-actions">
					<span class="edit">
						<a
							href="#"
							@click.prevent="editRow(index)"
						>{{ strings.edit }}</a> |
					</span>

					<span
						class="test"
						v-if="row.enabled && !redirectHasUnPublishedPost(row)"
					>
						<a
							href="#"
							@click.prevent="showRedirectTest(row)"
						>{{ strings.checkRedirect }}</a> |
					</span>

					<span class="trash">
						<a
							class="submitdelete"
							href="#"
							@click.prevent="maybeDeleteRow(index)"
						>{{ strings.delete }}</a>
					</span>
				</div>
			</template>

			<template #target_url="{ row }">
				{{ 400 <= row.type ? '-' : row.target_url }}
			</template>

			<template #type="{ column }">
				{{ getColumnLabel(column) }}
			</template>

			<template #enabled="{ column, row }">
				<base-toggle
					:modelValue="column"
					@update:modelValue="toggleInput(row, column)"
				/>
			</template>

			<template #edit-row="{ row, editRow }">
				<core-add-redirection
					edit
					@cancel="editRow(null)"
					@added-redirect="editRow(null)"
					:url="{
						id          : row.id,
						url         : row.source_url,
						regex       : row.regex,
						ignoreSlash : row.ignore_slash,
						ignoreCase  : row.ignore_case,
						showOptions : true,
						errors      : [],
						warnings    : []
					}"
					:target="row.target_url"
					:type="row.type"
					:query-params="row.query_parameters"
					:custom-rules="row.rules"
					:is-table="true"
				/>
			</template>
		</core-wp-table>

		<core-modal
			:show="modalOpened"
			@close="modalOpened = false"
		>
			<template #headerTitle>
				{{ strings.redirectTest }}
			</template>

			<template #body>
				<div class="test-redirect-modal">
					<div class="aioseo-description">
						{{ strings.redirectTestDescription }}
					</div>

					<div class="test-redirect-input">
						<base-input
							class="test-redirect-url"
							size="medium"
							prependIcon="link"
							:placeholder="strings.enterUrlPlaceholder"
							v-model="testUrl"
						/>

						<base-button
							size="medium"
							type="blue"
							:loading="testLoading"
							@click="processRedirectTest"
						>
							{{ strings.test }}
						</base-button>
					</div>

					<div
						v-if="testResults"
						class="test-redirect-results"
					>
						<core-alert
							:type="'success' === testResults.type ? 'green' : 'red'"
						>
							{{ testResults.message }}
						</core-alert>
					</div>
				</div>
			</template>
		</core-modal>
	</div>
</template>

<script>
import {
	useNotificationsStore,
	useRedirectsStore,
	useSettingsStore
} from '@/vue/stores'

import {
	useRedirect
} from '@/vue/composables/redirects/Redirect'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import BaseButton from '@/vue/components/common/base/Button'
import BaseInput from '@/vue/components/common/base/Input'
import BaseToggle from '@/vue/components/common/base/Toggle'
import CoreAddRedirection from '@/vue/components/common/core/add-redirection/Index'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreWpTable from '@/vue/components/common/core/wp/Table'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			redirectHasUnPublishedPost
		} = useRedirect()

		return {
			notificationsStore : useNotificationsStore(),
			redirectsStore     : useRedirectsStore(),
			settingsStore      : useSettingsStore(),
			redirectHasUnPublishedPost
		}
	},
	components : {
		BaseButton,
		BaseInput,
		BaseToggle,
		CoreAddRedirection,
		CoreAlert,
		CoreModal,
		CoreWpTable
	},
	props : {
		excludeColumns  : Array,
		disableSource   : Boolean,
		showBulkActions : {
			type    : Boolean,
			default : true
		},
		showHeader : {
			type    : Boolean,
			default : true
		},
		showTableFooter : {
			type    : Boolean,
			default : true
		}
	},
	data () {
		return {
			wpTableKey     : 0,
			wpTableLoading : false,
			modalOpened    : false,
			testLoading    : false,
			testResults    : null,
			testUrl        : '',
			strings        : {
				searchUrls               : __('Search URLs', td),
				edit                     : __('Edit', td),
				checkRedirect            : __('Check Redirect', td),
				delete                   : __('Delete', td),
				redirectTest             : __('Redirect Test', td),
				redirectTestDescription  : __('Enter a URL below to see if it redirects correctly. This will test the actual redirect and show you what happens.', td),
				enterUrlPlaceholder      : __('Enter a URL to test...', td),
				test                     : __('Test', td),
				sourceUrl                : __('Source URL', td),
				targetUrl                : __('Target URL', td),
				type                     : __('Type', td),
				enabled                  : __('Enabled', td),
				areYouSureDelete         : __('Are you sure you want to delete this redirect?', td),
				areYouSureDeleteSelected : __('Are you sure you want to delete the selected redirect(s)?', td),
				bulkActionCannotBeUndone : __('This action cannot be undone.', td),
				redirectTypes            : {
					301 : __('301 Moved Permanently', td),
					302 : __('302 Found', td),
					307 : __('307 Temporary Redirect', td),
					308 : __('308 Permanent Redirect', td),
					400 : __('400 Bad Request', td),
					401 : __('401 Unauthorized', td),
					403 : __('403 Forbidden', td),
					404 : __('404 Not Found', td),
					410 : __('410 Gone', td),
					500 : __('500 Internal Server Error', td)
				}
			}
		}
	},
	computed : {
		tableId () {
			return this.disableSource ? 'aioseo-post-redirects-table' : 'aioseo-redirects-table'
		},
		pageNumber () {
			return this.$route.query.page ? parseInt(this.$route.query.page) : 1
		},
		searchTerm () {
			return this.$route.query.search || null
		},
		getRows () {
			return this.redirectsStore.rows.map(row => {
				return {
					...row,
					source_url : this.disableSource ? '' : row.source_url
				}
			})
		},
		columns () {
			const defaultColumns = [
				{
					slug     : 'source_url',
					label    : this.strings.sourceUrl,
					width    : '300px',
					sortable : true
				},
				{
					slug     : 'target_url',
					label    : this.strings.targetUrl,
					width    : '300px',
					sortable : true
				},
				{
					slug     : 'type',
					label    : this.strings.type,
					width    : '120px',
					sortable : true
				},
				{
					slug     : 'enabled',
					label    : this.strings.enabled,
					width    : '80px',
					sortable : true
				}
			]

			if (this.excludeColumns && this.excludeColumns.length) {
				return defaultColumns.filter(column => !this.excludeColumns.includes(column.slug))
			}

			return defaultColumns
		},
		bulkOptions () {
			return [
				{
					label : __('Delete', td),
					value : 'delete'
				}
			]
		},
		additionalFilters () {
			return []
		}
	},
	methods : {
		processFilterTable ({ slug, values }) {
			this.redirectsStore.selectedFilters[slug] = values
			this.wpTableLoading = true
			this.redirectsStore.getRedirects()
				.then(() => {
					this.wpTableLoading = false
				})
		},
		processPagination (pageNumber) {
			this.redirectsStore.totals.main.page = pageNumber
			this.wpTableLoading = true
			this.redirectsStore.getRedirects()
				.then(() => {
					this.wpTableLoading = false
				})
		},
		processAdditionalFilters (filters) {
			Object.keys(filters).forEach(key => {
				this.redirectsStore.selectedFilters[key] = filters[key]
			})

			this.wpTableLoading = true
			this.redirectsStore.getRedirects()
				.then(() => {
					this.wpTableLoading = false
				})
		},
		processBulkAction ({ action, selectedRows }) {
			if ('delete' === action) {
				this.notificationsStore.show({
					type    : 'warning',
					title   : this.strings.areYouSureDeleteSelected,
					message : this.strings.bulkActionCannotBeUndone,
					actions : [
						{
							label  : __('Yes, delete the redirects', td),
							action : 'processDeleteSelected',
							data   : selectedRows
						}
					]
				})
			}
		},
		processDeleteSelected (redirectIds) {
			http.post(links.restUrl('redirects/delete'))
				.send({
					redirectIds
				})
				.then(() => {
					this.redirectsStore.getRedirects()
				})
		},
		processChangeItemsPerPage (itemsPerPage) {
			this.settingsStore.changeItemsPerPage({ slug: 'redirects', itemsPerPage })
		},
		processSearch (searchTerm) {
			this.wpTableLoading = true
			this.redirectsStore.getRedirects({ searchTerm })
				.then(() => {
					this.wpTableLoading = false
				})
		},
		processSort ({ column, direction }) {
			this.redirectsStore.sort.main = column
			this.redirectsStore.sortDir.main = direction
			this.wpTableLoading = true
			this.redirectsStore.getRedirects()
				.then(() => {
					this.wpTableLoading = false
				})
		},
		maybeDeleteRow (index) {
			this.notificationsStore.show({
				type    : 'warning',
				title   : this.strings.areYouSureDelete,
				message : this.strings.bulkActionCannotBeUndone,
				actions : [
					{
						label  : __('Yes, delete the redirect', td),
						action : 'processDeleteRow',
						data   : index
					}
				]
			})
		},
		processDeleteRow (index) {
			const redirect = this.redirectsStore.rows[index]
			http.post(links.restUrl('redirects/delete'))
				.send({
					redirectIds : [ redirect.id ]
				})
				.then(() => {
					this.redirectsStore.getRedirects()
				})
		},
		toggleInput (row, enabled) {
			http.post(links.restUrl('redirects/update'))
				.send({
					id : row.id,
					enabled
				})
				.then(() => {
					this.redirectsStore.getRedirects()
				})
		},
		showRedirectTest (row) {
			this.testUrl = `${this.redirectsStore.options.server.home}${row.source_url}`
			this.testResults = null
			this.modalOpened = true
		},
		processRedirectTest () {
			if (!this.testUrl) {
				return
			}

			this.testLoading = true
			this.testResults = null

			http.post(links.restUrl('redirects/test'))
				.send({
					url : this.testUrl
				})
				.then(response => {
					this.testResults = response.body
					this.testLoading = false
				})
				.catch(() => {
					this.testLoading = false
				})
		},
		getColumnLabel (column) {
			return this.strings.redirectTypes[column] || column
		}
	},
	mounted () {
		if (!this.redirectsStore.rows.length) {
			this.wpTableLoading = true
			this.redirectsStore.getRedirects()
				.then(() => {
					this.wpTableLoading = false
				})
		}
	}
}
</script>

<style lang="scss">
.aioseo-redirects-page {
	.aioseo-redirects-table {
		margin-top: 40px;
	}
}

.aioseo-redirects-table {
	.test-redirect-modal {
		.aioseo-description {
			font-size: 16px;
			color: $black;
			margin-bottom: 20px;
		}

		.test-redirect-input {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 20px;

			.test-redirect-url {
				flex: 1;
			}
		}

		.test-redirect-results {
			margin-top: 20px;
		}
	}
}
</style>