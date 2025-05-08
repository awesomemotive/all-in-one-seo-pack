<template>
	<div>
		<core-card
			slug="presenceOnGoogle"
			:header-text="strings.presenceOnGoogle"
			:toggles="false"
			no-slide
		>
			<template #header-icon>
				<svg-google
					width="24"
					height="24"
				/>
			</template>

			<template #tooltip>
				{{ strings.tooltipPresenceOnGoogle }}
			</template>

			<div class="aioseo-donut-chart-with-legend-wrapper">
				<core-loader
					v-if="null === indexStatusStore.overview"
					dark
				/>

				<core-donut-chart-with-legend
					v-else
					:total="indexStatusStore.overview.post.total"
					:parts="parseParts"
					:label="strings.totalUrls"
					@on-label-click="status => changeSelectedStatus(status)"
				/>
			</div>
		</core-card>

		<core-card
			slug="indexOfIndividual"
			:header-text="strings.indexOfIndividual"
			:toggles="false"
			no-slide
		>
			<template #tooltip>
				{{ strings.tooltipIndexOfIndividual }}
			</template>

			<objects-table
				ref="refObjectsTable"
				:paginated-rows="indexStatusStore.objects.paginated"
				:selected-status="selectedStatus"
			/>
		</core-card>
	</div>
</template>

<script setup>
import { computed, onBeforeMount, ref, nextTick } from 'vue'

import {
	useIndexStatusStore,
	useSearchStatisticsStore
} from '@/vue/stores'

import CoreCard from '@/vue/components/common/core/Card'
import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import CoreLoader from '@/vue/components/common/core/Loader'
import ObjectsTable from './partials/ObjectsTable'
import SvgGoogle from '@/vue/components/common/svg/logo/GoogleSmall'

import { __ } from '@/vue/plugins/translations'

const indexStatusStore      = useIndexStatusStore()
const searchStatisticsStore = useSearchStatisticsStore()

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	presenceOnGoogle         : __('Presence on Google', td),
	tooltipPresenceOnGoogle  : __('View at a glance how many of your posts have been indexed and discovered by Google.', td),
	indexOfIndividual        : __('Post Index Status', td),
	tooltipIndexOfIndividual : __('The table below shows the index status of each of your posts, along with any extra information from Google that may be relevant as to why they are not indexed.', td),
	totalUrls                : __('Total Posts', td)
}

const refObjectsTable = ref(null)
const selectedStatus  = ref('')

const parseParts = computed(() => {
	const overview = indexStatusStore.overview
	if (!overview) {
		return []
	}

	// Get relevant options from the store.
	const statusOptions = indexStatusStore.options.table.additionalFilters.statusOptions.options
		.filter(option => !!option?.value)
		.map(option => ({
			...option,
			regex     : new RegExp(option.value, 'i'),
			name      : option.label,
			emitValue : option.value,
			link      : '#'
		}))

	// Track counted posts for calculating the remainder later.
	let countedPosts = 0

	// Process each status category.
	const processedParts = statusOptions.map((part, index) => {
		// Find posts matching this status and sum their counts.
		const matchingResults = overview.post.results.filter(result => part.regex.test(result?.coverageState))
		const count           = matchingResults.reduce((total, curr) => total + Number(curr.count || 0), 0)

		countedPosts += count

		return {
			...part,
			count,
			ratio : 0 === index
				? 100
				: overview.post.total ? ((count / overview.post.total) * 100) : 0
		}
	}).map(part => {
		if ('unknown|excluded|invalid|error' === part.value) {
			// Add uncounted posts to this category.
			const uncountedPosts = overview.post.total - countedPosts

			return {
				...part,
				count : part.count + uncountedPosts,
				ratio : overview.post.total ? (((part.count + uncountedPosts) / overview.post.total) * 100) : 0
			}
		}
		return part
	})

	processedParts.forEach((part, index) => {
		if (0 === index) {
			return part
		}

		processedParts.forEach((part2, index2) => {
			if (index < index2) {
				part.ratio = part.ratio + part2.ratio
			}

			return part
		})

		return part
	})

	return processedParts
})

const changeSelectedStatus = async (status) => {
	selectedStatus.value = status

	await nextTick()

	refObjectsTable.value.$el.querySelector('.aioseo-wp-additional-filters button').click()

	selectedStatus.value = ''
}

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
</script>

<style lang="scss" scoped>
.aioseo-donut-chart-with-legend-wrapper {
	text-align: center;

	.aioseo-loading-spinner {
		left: 50%;
		position: relative;
		transform: translateX(-50%);
	}
}

.aioseo-donut-chart-with-legend {
	display: inline-flex;

	:deep(.legend-label) {
		cursor: pointer;
	}
}
</style>