<template>
	<div class="aioseo-seo-site-analysis-all-urls-results">
		<core-wp-table
			ref="seoAnalysisAllUrlsTable"
			class="posts-table"
			:id="tableId"
			:columns="columns"
			:initial-page-number="pageNumber"
			:initial-search-term="searchTerm"
			:initial-items-per-page="settingsStore.settings.tablePagination.seoAnalysis"
			:showBulkActions="false"
			show-items-per-page
			:key="wpTableKey"
			:loading="wpTableLoading"
			:rows="analyzerStore.allUrlsResults.rows"
			:totals="analyzerStore.allUrlsResults.totals"
		>
			<template #edit-row="{ row }">
				<site-audit-url-details
					v-if="!row?.needsRefresh"
					:result="row"
				/>
			</template>

			<template #count="{ row }">
				<div class="aioseo-seo-site-analysis-all-urls-results__result-count">
					<base-badge
						v-if="getCountAmount(row)"
						:color="getCountColor(row)"
						:text="getCountAmount(row)"
					/>

					<span
						v-else
						class="icon"
						:class="getCountColor(row)"
					>
						<svg-circle-check />
					</span>
				</div>
			</template>

			<template #title="{ row }">
				<div class="aioseo-seo-site-analysis-all-urls-results__object-title">
					<button type="button">
						<span v-html="softSanitizeHtml(row.title)" />
					</button>

					<post-status-badge
						v-if="row.status"
						:status="row.status"
					/>
				</div>

        <div
					v-if="row.permalink"
					class="row-actions"
				>
					<span v-if="row.permalink">
						<a
							class="view"
							:href="row.permalink"
							target="_blank"
							rel="noopener noreferrer"
						>
							<span>{{ strings.viewObject }} {{ row.subtype.label }}</span>
						</a>
					</span>
				</div>
			</template>

			<template #keyword="{ row }">
				<site-audit-url-keyword-cell
					:key="row.id"
					:row="row"
					@keyword-added="keywordAdded(row)"
				/>
			</template>

			<template #toggle_issues>
				<div class="aioseo-seo-site-analysis-all-urls-results__result-toggle">
					<svg-caret />
				</div>
			</template>
		</core-wp-table>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import {
	useAnalyzerStore,
	useSettingsStore
} from '@/vue/stores'

import { useWpTable } from '@/vue/composables/WpTable'

import BaseBadge from '@/vue/components/common/base/Badge'
import PostStatusBadge from '@/vue/components/common/seo-analysis/PostStatusBadge'
import CoreWpTable from '@/vue/components/common/core/wp/Table'
import SiteAuditUrlDetails from '@/vue/pages/seo-analysis/partials/pro/SiteAuditUrlDetails'
import SiteAuditUrlKeywordCell from '@/vue/pages/seo-analysis/partials/pro/SiteAuditUrlKeywordCell'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import { softSanitizeHtml } from '@/vue/utils/strings'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const analyzerStore = useAnalyzerStore()
const settingsStore = useSettingsStore()
const table         = ref(null)
const tableId       = 'aioseo-seo-analysis-all-urls-wp-table'
const strings       = {
	viewObject : __('View', td)
}

const {
	pageNumber,
	searchTerm,
	wpTableKey,
	wpTableLoading
} = useWpTable({
	changeItemsPerPageSlug : 'seoAnalysis',
	fetchData              : analyzerStore.fetchAllUrls,
	tableId,
	tableRef               : table.value
})

const columns = computed(() => {
	return [
		{
			slug  : 'count',
			label : '',
			width : '50px'
		},
		{
			slug  : 'title',
			label : __('Title', td),
			width : '100%'
		},
		{
			slug  : 'keyword',
			label : __('Focus Keyword', td),
			width : '100%'
		},
		{
			slug  : 'toggle_issues',
			label : '',
			width : '50px'
		}
	]
})

const getCountColor = (row) => {
	return row.counts?.error ? 'red' : row.counts?.warning ? 'orange' : 'green'
}

const getCountAmount = (row) => {
	return row.counts?.error || row.counts?.warning || 0
}
</script>

<style lang="scss">
.aioseo-seo-site-analysis-all-urls-results {
	margin-top: 8px;

	&__object-title {
		display: flex;
		gap: 8px;

		> button {
			all: unset;
			font-weight: bold;
			cursor: pointer;
		}
	}

	&__result-toggle {
		width: 30px;
		height: 26px;
		border: 1px solid $gray;
		border-radius: 3px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;

		&:not(.needs-refresh) {
			&.active,
			&:hover {
				background-color: $blue;

				svg {
					color: #fff;
				}
			}

			&.active {
				svg {
					transform: rotate(-180deg);
				}
			}
		}

		svg {
			width: 100%;
			max-width: 20px;
			height: auto;
			color: $black;
			transform: rotate(-90deg);
			transition: transform 0.3s;
		}
	}

	&__result-count {
		.icon {
			svg {
				width: 28px;
				height: 28px;
			}

			&.green {
				color: $green;
			}

			&.orange {
				color: $orange;
			}

			&.red {
				color: $red;
			}
		}
	}

	.aioseo-wp-table .wp-table tr td.edit-row-content .wrapper .border {
		padding: 0;
    margin: 0;
	}
}
</style>