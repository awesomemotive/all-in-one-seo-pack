<template>
	<core-wp-table
		ref="table"
		:id="tableId"
		:additional-filters="[]"
		:columns="tableColumns"
		:filters="[]"
		:initial-items-per-page="itemsPerPage"
		:initial-page-number="pageNumber"
		:initial-search-term="''"
		:key="wpTableKey"
		:loading="wpTableLoading"
		:rows="paginatedRows.pages || []"
		:show-bulk-actions="false"
		:show-header="false"
		:show-table-footer="true"
		:totals="totals"
		:show-items-per-page="true"
		@paginate="processPagination"
		:show-search="false"
		:show-pagination="true"
		@process-change-items-per-page="processChangeItemsPerPage"
	>
		<template #post_title="{ row }">
			<div class="post-title">
				<b>{{ row.objectTitle }}</b>
			</div>

			<div
				class="row-actions"
				v-if="row?.objectId"
			>
				<span class="edit">
					<a
						:href="row.context.permalink"
						target="_blank"
					>
						{{ viewPost(row.context.postType.singular) }}
					</a> |

					<a
						:href="row.context.editLink"
						target="_blank"
					>
						{{ editPost(row.context.postType.singular) }}
					</a>
				</span>
			</div>
		</template>

		<template #clicks="{ row }">
			{{ numbers.compactNumber(row.clicks) }}
		</template>

		<template #ctr="{ row }">
			{{ parseFloat(row.ctr) }}%
		</template>

		<template #impressions="{ row }">
			{{ numbers.compactNumber(row.impressions) }}
		</template>

		<template #position="{ row }">
			<statistic
				v-if="row.difference.comparison"
				type="position"
				:total="row.position"
				:difference="row.difference.position"
				:tooltip-offset="'-150px,0'"
			/>
		</template>
	</core-wp-table>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

import { __ } from '@/vue/plugins/translations'
import { usePostTypes } from '@/vue/composables/PostTypes'
import { useWpTable } from '@/vue/composables/WpTable'

import numbers from '@/vue/utils/numbers'

import CoreWpTable from '@/vue/components/common/core/wp/Table'
import Statistic from './Statistic'
import {
	useSearchStatisticsStore
} from '@/vue/stores'

const td                    = import.meta.env.VITE_TEXTDOMAIN
const searchStatisticsStore = useSearchStatisticsStore()
const tableId               = 'search-statistics-keywords-inner-table'

const props = defineProps({
	paginatedRows : Object
})

const itemsPerPage = ref(5)
const table        = ref(null)

const {
	editPost,
	viewPost
} = usePostTypes()

const {
	offset,
	processChangeItemsPerPage,
	processPagination,
	wpTableKey,
	wpTableLoading
} = useWpTable({
	fetchData () {
		return fetchRows({
			keywords : [ props.paginatedRows.keyword ],
			limit    : itemsPerPage.value,
			offset   : offset.value
		})
			.catch(error => {
				console.error(error)
			})
	},
	tableId,
	tableRef       : table.value,
	resultsPerPage : itemsPerPage
})

const totals = computed(() => {
	return props.paginatedRows.totals || {
		page  : 1,
		pages : 0,
		total : 0
	}
})

const pageNumber = computed(() => {
	return totals.value.page
})

const tableColumns = computed(() => [
	{
		slug  : 'post_title',
		label : __('Title', td),
		width : '100%'
	},
	{
		slug  : 'clicks',
		label : __('Clicks', td),
		width : '120px'
	},
	{
		slug  : 'ctr',
		label : __('Avg. CTR', td),
		width : '120px'
	},
	{
		slug  : 'impressions',
		label : __('Impressions', td),
		width : '120px'
	},
	{
		slug  : 'position',
		label : __('Position', td),
		width : '120px'
	}
])

const fetchRows = (payload) => {
	return searchStatisticsStore.getPagesByKeywords(payload)
		.catch(() => searchStatisticsStore.getPagesByKeywords(payload))
		.catch(() => searchStatisticsStore.getPagesByKeywords(payload))
		.then(paginated => {
			props.paginatedRows.pages  = Object.values(paginated?.rows || {})
			props.paginatedRows.totals = paginated?.totals || {}
		})
}

onMounted(() => {
	const keyword = props.paginatedRows.keyword

	wpTableLoading.value = true

	fetchRows({
		keywords : [ keyword ],
		limit    : itemsPerPage.value,
		offset   : offset.value
	})
		.catch(error => {
			console.error(error)
		})
		.finally(() => {
			wpTableLoading.value = false
		})
})
</script>

<style lang="scss" scoped>
#search-statistics-keywords-inner-table {
	padding: 0;

	tr {
		.row-actions {
			position: relative;
		}

		&:hover .row-actions {
			position: static;
		}
	}
}
</style>