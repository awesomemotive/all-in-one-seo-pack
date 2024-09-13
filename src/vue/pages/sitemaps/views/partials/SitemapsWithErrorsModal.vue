<template>
	<core-modal
		:show="display"
		@close="$emit('close')"
		:classes="[ 'aioseo-sitemaps-errors-modal' ]"
	>
		<template #headerTitle>
			{{ strings.sitemapErrors }}
		</template>

		<template #body>
			<div class="aioseo-modal-body">
				<div v-if="searchStatisticsStore.sitemapsWithErrors.length > 0">
					<core-alert-actionable
						:title="strings.thereAreSitemapsWithErrors"
						:text="strings.aioseoHasFoundSomeErrorsInSitemaps"
						type="yellow"
						size="small"
					/>

					<core-wp-table
						:id="tableId"
						:columns="columns"
						:rows="rows"
						:bulk-options="bulkOptions"
						:totals="totals"
						:showSearch="false"
						:loading="loading"
						@paginate="processPagination"
						@process-bulk-action="processBulkAction"
					>
						<template #sitemap="{ row }">
							<a :href="escUrl(row.path)" target="_blank" rel="noopener">{{ row.path }}</a>
						</template>

						<template #actions="{ row }">
							<a
								href="#"
								@click.prevent="() => ignoreSitemap(row.path)"
							>
								{{ strings.ignoreSitemap }}
							</a>

							<template v-if="row.detailsUrl !== ''"> |
								<a
									:href="escUrl(row.detailsUrl)"
									target="_blank"
									rel="noopener"
								>
									{{ strings.viewDetails }}
								</a>
							</template>

							<template v-if="canRemoveSitemap(row)"> |
								<a
									href="#"
									class="aioseo-sitemaps-errors-remove"
									@click.prevent="() => deleteSitemap(row.path)"
								>
									{{ strings.removeSitemap }}
								</a>
							</template>
						</template>
					</core-wp-table>
				</div>

				<div v-else class="no-errors">
					<svg-circle-check />

					<p>{{ strings.allErrorsResolved }}</p>
				</div>
			</div>
		</template>
	</core-modal>
</template>

<script>
import {
	useOptionsStore,
	useSearchStatisticsStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { useGoogleSearchConsole } from '@/vue/composables/GoogleSearchConsole'
import { useWpTable } from '@/vue/composables/WpTable'

import { escUrl } from '@/vue/utils/formatting'
import { merge } from 'lodash-es'

import CoreAlertActionable from '@/vue/components/common/core/alert/Actionable'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			strings
		} = useGoogleSearchConsole()

		const tableId = 'sitemapErrorsTable'
		const {
			pageNumber,
			processPagination,
			resultsPerPage
		} = useWpTable({
			changeItemsPerPageSlug : 'searchConsoleSitemapErrors',
			fetchData              : () => Promise.resolve(),
			resultsPerPage         : 5,
			tableId
		})

		return {
			composableStrings     : strings,
			optionsStore          : useOptionsStore(),
			pageNumber,
			processPagination,
			resultsPerPage,
			rootStore             : useRootStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			settingsStore         : useSettingsStore(),
			tableId
		}
	},
	components : {
		CoreAlertActionable,
		CoreModal,
		CoreWpTable,
		SvgCircleCheck
	},
	props : {
		display : {
			type    : Boolean,
			default : false
		},
		sitemaps : {
			type    : Array,
			default : () => []
		}
	},
	data () {
		return {
			loading     : false,
			bulkOptions : [
				{
					label : __('Remove', td),
					value : 'remove'
				},
				{
					label : __('Ignore', td),
					value : 'ignore'
				}
			],
			strings : merge(this.composableStrings, {
				aioseoHasFoundSomeErrorsInSitemaps : sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__('%1$s has found some errors in sitemaps that were previously submitted to Google Search Console. Since %1$s manages your sitemaps, these additional sitemaps can be removed.', td),
					import.meta.env.VITE_SHORT_NAME
				),
				allErrorsResolved          : __('All errors have been resolved', td),
				removeSitemap              : __('Remove', td),
				ignoreSitemap              : __('Ignore', td),
				viewDetails                : __('Details', td),
				sitemapErrors              : __('Sitemap Errors', td),
				thereAreSitemapsWithErrors : __('There are sitemaps with errors', td)
			})
		}
	},
	computed : {
		totals () {
			return {
				page  : 1,
				pages : Math.ceil(this.searchStatisticsStore.sitemapsWithErrors.length / this.resultsPerPage),
				total : this.searchStatisticsStore.sitemapsWithErrors.length
			}
		},
		rows () {
			const offset = 1 === this.pageNumber ? 0 : (this.pageNumber - 1) * this.resultsPerPage

			return this.searchStatisticsStore.sitemapsWithErrors.slice(offset, offset + this.resultsPerPage)
		},
		columns () {
			return [
				{
					slug  : 'sitemap',
					label : __('Sitemap', td)
				},
				{
					slug  : 'actions',
					label : __('Actions', td),
					width : '220px'
				}
			]
		}
	},
	methods : {
		escUrl,
		// Placeholder method.
		fetchData () {
			return Promise.resolve()
		},
		processBulkAction ({ action, selectedRows }) {
			if (!selectedRows.length) {
				return
			}

			const selectedSitemaps = []
			if (Array.isArray(selectedRows)) {
				selectedRows.forEach((suggestionIndex) => {
					selectedSitemaps.push(this.rows[suggestionIndex].path)
				})
			} else {
				selectedSitemaps.push(selectedRows.path)
			}

			switch (action) {
				case 'remove':
					this.loading = true
					this.searchStatisticsStore.deleteSitemap({
						sitemap : selectedSitemaps
					}).finally(() => {
						this.loading = false
					})
					break
				case 'ignore':
					this.loading = true
					this.searchStatisticsStore.ignoreSitemap({
						sitemap : selectedSitemaps
					}).finally(() => {
						this.loading = false
					})
					break
			}
		},
		deleteSitemap (sitemap) {
			this.loading = true
			this.searchStatisticsStore.deleteSitemap({
				sitemap
			}).finally(() => {
				this.loading = false
			})
		},
		ignoreSitemap (sitemap) {
			this.loading = true
			this.searchStatisticsStore.ignoreSitemap({
				sitemap
			}).finally(() => {
				this.loading = false
			})
		},
		canRemoveSitemap (sitemap) {
			const internalSitemaps = this.rootStore.aioseo.data.sitemapUrls

			return !internalSitemaps.includes(sitemap.path)
		}
	}
}
</script>

<style lang="scss">
.aioseo-sitemaps-errors-modal {
	.aioseo-modal-body {
		padding: 24px;

		.aioseo-alert {
			margin-bottom: 16px;
		}
	}

	.aioseo-sitemaps-errors-remove {
		color: $red !important;
		text-decoration: none;
		cursor: pointer;
	}

	.no-errors {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		min-height: 400px;

		svg {
			width: 50px;
			height: 50px;
			margin-bottom: 10px;
			color: $green;
		}

		p {
			font-size: 18px;
			font-weight: 700;
			line-height: 25px;
			text-align: center;
			margin: 0;
		}
	}

	thead .check-column input[type="checkbox"],
	tfoot .check-column input[type="checkbox"] {
		display: none;
	}
}
</style>