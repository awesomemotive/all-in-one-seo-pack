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
						id="sitemapErrorsTable"
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

import { escUrl } from '@/vue/utils/formatting'
import { WpTable } from '@/vue/mixins/WpTable'
import { useSearchConsole } from '@/vue/composables'
import { merge } from 'lodash-es'
import CoreAlertActionable from '@/vue/components/common/core/alert/Actionable'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'

export default {
	setup () {
		const {
			strings
		} = useSearchConsole()

		return {
			optionsStore          : useOptionsStore(),
			searchStatisticsStore : useSearchStatisticsStore(),
			rootStore             : useRootStore(),
			settingsStore         : useSettingsStore(),
			composableStrings     : strings
		}
	},
	mixins     : [ WpTable ],
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
			resultsPerPage : 5,
			pageNumber     : 1,
			loading        : false,
			bulkOptions    : [
				{
					label : this.$t.__('Remove', this.$td),
					value : 'remove'
				},
				{
					label : this.$t.__('Ignore', this.$td),
					value : 'ignore'
				}
			],
			strings : merge(this.composableStrings, {
				aioseoHasFoundSomeErrorsInSitemaps : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					this.$t.__('%1$s has found some errors in sitemaps that were previously submitted to Google Search Console. Since %1$s manages your sitemaps, these additional sitemaps can be removed.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				allErrorsResolved          : this.$t.__('All errors have been resolved', this.$td),
				removeSitemap              : this.$t.__('Remove', this.$td),
				ignoreSitemap              : this.$t.__('Ignore', this.$td),
				viewDetails                : this.$t.__('Details', this.$td),
				sitemapErrors              : this.$t.__('Sitemap Errors', this.$td),
				thereAreSitemapsWithErrors : this.$t.__('There are sitemaps with errors', this.$td)
			})
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
					label : this.$t.__('Sitemap', this.$td)
				},
				{
					slug  : 'actions',
					label : this.$t.__('Actions', this.$td),
					width : '220px'
				}
			]
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