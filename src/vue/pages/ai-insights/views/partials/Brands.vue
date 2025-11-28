<template>
	<div class="brands-partial">
		<core-wp-table
			:columns="tableColumns"
			:rows="paginatedRows"
			:totals="tableTotals"
			:initial-page-number="currentPage"
			:show-search="false"
			:show-pagination="true"
			:show-bulk-actions="false"
			:show-header="true"
			:show-table-footer="true"
			:initial-items-per-page="10"
			@paginate="handlePagination"
			@sort-column="handleSort"
		>
			<template #tablenav>
				<div v-if="props.report?.brands_mentioned" class="brands-mentioned">
					{{ formatBrandsMentioned(props.report.brands_mentioned) }}
				</div>
			</template>

			<template #brandHeaderFooter>
				{{ strings.brand }}
			</template>

			<template #rankHeaderFooter>
				{{ strings.rank }}
			</template>

			<template #openaiHeaderFooter>
				<span class="provider-header">
					<SvgOpenAI />
					{{ strings.openAi }}
				</span>
			</template>

			<template #anthropicHeaderFooter>
				<span class="provider-header">
					<SvgAnthropic />
					{{ strings.anthropic }}
				</span>
			</template>

			<template #geminiHeaderFooter>
				<span class="provider-header">
					<SvgGemini />
					{{ strings.gemini }}
				</span>
			</template>

			<template #deepseekHeaderFooter>
				<span class="provider-header">
					<SvgDeepSeek />
					{{ strings.deepSeek }}
				</span>
			</template>

			<template #perplexityHeaderFooter>
				<span class="provider-header">
					<SvgPerplexity />
					{{ strings.perplexity }}
				</span>
			</template>

			<template #brand="{ row }">
				{{ row.name }}
			</template>

			<template #rank="{ row }">
				<span class="rank-cell rank-cell-avg">
					{{ formatRank(row.rank) }}
				</span>
			</template>

			<template #openai="{ row }">
				<span
					class="rank-cell"
					:class="getRankClass(row.scores.openai)"
				>
					{{ row.scores.openai || '-' }}
				</span>
			</template>

			<template #anthropic="{ row }">
				<span
					class="rank-cell"
					:class="getRankClass(row.scores.anthropic)"
				>
					{{ row.scores.anthropic || '-' }}
				</span>
			</template>

			<template #gemini="{ row }">
				<span
					class="rank-cell"
					:class="getRankClass(row.scores.gemini)"
				>
					{{ row.scores.gemini || '-' }}
				</span>
			</template>

			<template #deepseek="{ row }">
				<span
					class="rank-cell"
					:class="getRankClass(row.scores.deepseek)"
				>
					{{ row.scores.deepseek || '-' }}
				</span>
			</template>

			<template #perplexity="{ row }">
				<span
					class="rank-cell"
					:class="getRankClass(row.scores.perplexity)"
				>
					{{ row.scores.perplexity || '-' }}
				</span>
			</template>
		</core-wp-table>
	</div>
</template>

<script setup>
import { computed, ref } from 'vue'

import { __, _n, sprintf } from '@/vue/plugins/translations'

import CoreWpTable from '@/vue/components/common/core/wp/Table'

import SvgOpenAI from '@/vue/components/common/svg/ai/providers/OpenAI'
import SvgAnthropic from '@/vue/components/common/svg/ai/providers/Anthropic'
import SvgGemini from '@/vue/components/common/svg/ai/providers/Gemini'
import SvgDeepSeek from '@/vue/components/common/svg/ai/providers/DeepSeek'
import SvgPerplexity from '@/vue/components/common/svg/ai/providers/Perplexity'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	report : {
		type     : Object,
		required : true
	}
})

const strings = {
	brand      : __('Brand', td),
	rank       : __('Rank', td),
	openAi     : __('OpenAI', td),
	anthropic  : __('Claude', td),
	gemini     : __('Gemini', td),
	deepSeek   : __('DeepSeek', td),
	perplexity : __('Perplexity', td)
}

// Current page for pagination
const currentPage = ref(1)
const itemsPerPage = 10

// Sorting state
const sortColumn = ref('rank')
const sortDirection = ref('asc')

// Define table columns
const tableColumns = computed(() => [
	{
		slug     : 'brand',
		label    : strings.brand,
		sortable : false,
		width    : '30%',
		sorted   : false
	},
	{
		slug     : 'rank',
		label    : strings.rank,
		sortable : true,
		width    : '100px',
		sorted   : 'rank' === sortColumn.value,
		sortDir  : 'rank' === sortColumn.value ? sortDirection.value : 'asc'
	},
	{
		slug     : 'openai',
		label    : strings.openAi,
		sortable : true,
		width    : '120px',
		sorted   : 'openai' === sortColumn.value,
		sortDir  : 'openai' === sortColumn.value ? sortDirection.value : 'asc'
	},
	{
		slug     : 'anthropic',
		label    : strings.anthropic,
		sortable : true,
		width    : '120px',
		sorted   : 'anthropic' === sortColumn.value,
		sortDir  : 'anthropic' === sortColumn.value ? sortDirection.value : 'asc'
	},
	{
		slug     : 'gemini',
		label    : strings.gemini,
		sortable : true,
		width    : '120px',
		sorted   : 'gemini' === sortColumn.value,
		sortDir  : 'gemini' === sortColumn.value ? sortDirection.value : 'asc'
	},
	{
		slug     : 'deepseek',
		label    : strings.deepSeek,
		sortable : true,
		width    : '120px',
		sorted   : 'deepseek' === sortColumn.value,
		sortDir  : 'deepseek' === sortColumn.value ? sortDirection.value : 'asc'
	},
	{
		slug     : 'perplexity',
		label    : strings.perplexity,
		sortable : true,
		width    : '120px',
		sorted   : 'perplexity' === sortColumn.value,
		sortDir  : 'perplexity' === sortColumn.value ? sortDirection.value : 'asc'
	}
])

// Transform brands into table rows format
const tableRows = computed(() => {
	if (!props.report?.brands) {
		return []
	}

	const brands = Object.entries(props.report.brands).map(([ name, scores ]) => {
		return {
			name,
			rank   : scores.rank || 0,
			scores : {
				openai     : scores.openai || null,
				anthropic  : scores.anthropic || null,
				gemini     : scores.gemini || null,
				deepseek   : scores.deepseek || null,
				perplexity : scores.perplexity || null
			}
		}
	})

	// Sort based on current sort column and direction
	return brands.sort((a, b) => {
		let aValue, bValue

		if ('rank' === sortColumn.value) {
			aValue = a.rank || 0
			bValue = b.rank || 0
		} else {
			// For provider columns, get the score value
			aValue = a.scores[sortColumn.value] || null
			bValue = b.scores[sortColumn.value] || null

			// Handle null values - put them at the end
			if (null === aValue && null === bValue) return 0
			if (null === aValue) return 1
			if (null === bValue) return -1
		}

		// Convert to numbers for comparison
		const aNum = 'number' === typeof aValue ? aValue : parseFloat(aValue) || 0
		const bNum = 'number' === typeof bValue ? bValue : parseFloat(bValue) || 0

		if ('asc' === sortDirection.value) {
			return aNum - bNum
		} else {
			return bNum - aNum
		}
	})
})

// Calculate totals for pagination
const tableTotals = computed(() => {
	const total = tableRows.value.length
	const pages = Math.ceil(total / itemsPerPage)

	return {
		total,
		pages,
		per_page : itemsPerPage
	}
})

// Paginated rows based on current page
const paginatedRows = computed(() => {
	const start = (currentPage.value - 1) * itemsPerPage
	const end = start + itemsPerPage
	return tableRows.value.slice(start, end)
})

// Handle pagination event
const handlePagination = (page) => {
	currentPage.value = page
}

// Handle sort event
const handleSort = (column) => {
	// Reset to first page when sorting changes
	currentPage.value = 1

	// Toggle sort direction if clicking the same column, otherwise set to ascending
	if (column.slug === sortColumn.value) {
		sortDirection.value = 'asc' === sortDirection.value ? 'desc' : 'asc'
	} else {
		sortColumn.value = column.slug
		sortDirection.value = 'asc'
	}
}

const formatRank = (rank) => {
	if (!rank) {
		return '-'
	}

	// Round to 1 decimal place
	return parseFloat(rank.toFixed(1))
}

// Get CSS class based on rank value
const getRankClass = (rank) => {
	if (!rank || '-' === rank || null === rank) {
		return ''
	}

	const rankNum = parseInt(rank, 10)

	return `rank-cell-${rankNum}`
}

// Format brands mentioned count
const formatBrandsMentioned = (count) => {
	const num = parseInt(count, 10)

	return sprintf(
		_n('%1$s brand mentioned', '%1$s brands mentioned', num, td),
		num
	)
}
</script>

<style lang="scss" scoped>
.brands-partial {
	overflow-x: auto;
	margin-bottom: 24px;

	:deep(.provider-header) {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		line-height: 36px; // Same as the table header height.
		justify-content: center;

		svg {
			width: 20px;
			height: 20px;
			flex-shrink: 0;
		}
	}

	:deep(.sortable) {
		a {
			justify-content: center;
			color: inherit;

			.provider-header {
				margin: 0;
			}

			.sorting-indicator {
				flex-shrink: 0;
			}
		}
	}

	:deep(.manage-column) {
		&:not(.brand) {
			text-align: center;
		}
	}

	:deep(.rank-cell) {
		display: block;
		padding: 12px 0;

		&.rank-cell-avg {
			background-color: #E8E8EB;
		}

		&.rank-cell-1 {
			background-color: #60A5FA;
		}

		&.rank-cell-2 {
			background-color: #93C5FD;
		}

		&.rank-cell-3 {
			background-color: #DBEAFE;
		}
	}

	.brands-mentioned {
		display: inline-block;
		margin-right: 16px;
		color: $black;
		line-height: 30px;
	}
}
</style>