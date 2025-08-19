<template>
	<div class="aioseo-seo-site-analysis-results">
		<template
			v-if="showGooglePreview"
		>
			<core-google-search-preview
				:hostname="searchPreviewHostname"
				:url="searchPreviewUrl"
				:title="parseTags(allResults.basic.title.value)"
				:description="parseTags(allResults.basic.description.value)"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('basic')"
		>{{ strings.basic }}</div>

		<template
			v-for="(result, idx) in filterResults(allResults.basic)"
			:key="idx"
		>
			<core-seo-site-analysis-result
				:test="idx"
				:result="result"
				:show-instructions="showInstructions"
				:active-row="activeRow === `basic-${idx}`"
				@toggle-active="toggleRow(`basic-${idx}`)"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('advanced')"
		>{{ strings.advanced }}</div>

		<template
			v-for="(result, idx) in filterResults(allResults.advanced)"
			:key="idx"
		>
			<core-seo-site-analysis-result
				:test="idx"
				:result="result"
				:show-instructions="showInstructions"
				:active-row="activeRow === `advanced-${idx}`"
				@toggle-active="toggleRow(`advanced-${idx}`)"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('performance')"
		>{{ strings.performance }}</div>

		<template
			v-for="(result, idx) in filterResults(allResults.performance)"
			:key="idx"
		>
			<core-seo-site-analysis-result
				:test="idx"
				:result="result"
				:show-instructions="showInstructions"
				:active-row="activeRow === `performance-${idx}`"
				@toggle-active="toggleRow(`performance-${idx}`)"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('security')"
		>{{ strings.security }}</div>

		<template
			v-for="(result, idx) in filterResults(allResults.security)"
			:key="idx"
		>
			<core-seo-site-analysis-result
				:test="idx"
				:result="result"
				:show-instructions="showInstructions"
				:active-row="activeRow === `security-${idx}`"
				@toggle-active="toggleRow(`security-${idx}`)"
			/>
		</template>

		<div
			class="no-results"
			v-if="hasNoResults()"
		>
			{{ strings.noResults }}
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTags } from '@/vue/composables/Tags'
import SiteAnalysis from '@/vue/classes/SiteAnalysis'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import CoreSeoSiteAnalysisResult from '@/vue/components/common/core/SeoSiteAnalysisResult'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// Props
const props = defineProps({
	section : {
		type     : String,
		required : true
	},
	allResults : {
		type     : Object,
		required : true
	},
	site : {
		type     : String,
		required : false
	},
	showGooglePreview : Boolean,
	showInstructions  : Boolean
})

const { parseTags } = useTags({
	separator : undefined
})

const searchPreviewHostname = ref('')
const searchPreviewUrl = ref('')
const activeRow = ref(null)

const strings = computed(() => ({
	basic       : __('Basic SEO', td),
	advanced    : __('Advanced SEO', td),
	performance : __('Performance SEO', td),
	security    : __('Security SEO', td),
	noResults   : __('No items found.', td)
}))

function hasNoResults () {
	return Object.keys(props.allResults).every(group => 0 === Object.keys(props.allResults[group]).length)
}

function filterResults (resultSet) {
	return SiteAnalysis.getFilteredResults(resultSet, props.section)
}

function shouldShowGroup (group) {
	return Object.keys(filterResults(props.allResults[group])).length
}

function toggleRow (index) {
	if (activeRow.value === index) {
		activeRow.value = null
		return
	}

	activeRow.value = index
}

onMounted(() => {
	if (!props.allResults?.advanced?.searchPreview) {
		return
	}

	if (props.site) {
		const urlObject = new URL(props.site)

		searchPreviewUrl.value = urlObject.href
		searchPreviewHostname.value = urlObject.host
	}
})
</script>

<style lang="scss">
.aioseo-seo-site-analysis-results {
	padding-top: 12px;

	.aioseo-google-search-preview {
		border: 1px solid $border;
		padding: 16px;
	}

	.group-header {
		font-size: 16px;
		line-height: 24px;
		font-weight: 600;
		padding: 12px;
		background-color: $blue4;
		border-radius: 4px;

		&:not(:first-child) {
			margin-top: 20px;
		}
	}

	.no-results {
		margin-top: 20px;
		padding: 12px;
	}
}
</style>