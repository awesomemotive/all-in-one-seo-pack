<template>
	<div class="aioseo-seo-site-analysis-results">
		<template
			v-if="showGooglePreview && 'all-items' === section"
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
			v-for="(result, index) in filterResults(allResults.basic)"
			:key="index"
		>
			<core-seo-site-analysis-result
				:test="index"
				:result="result"
				:show-instructions="showInstructions"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('advanced')"
		>{{ strings.advanced }}</div>

		<template
			v-for="(result, index) in filterResults(allResults.advanced)"
			:key="index"
		>
			<core-seo-site-analysis-result
				:test="index"
				:result="result"
				:show-instructions="showInstructions"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('performance')"
		>{{ strings.performance }}</div>

		<template
			v-for="(result, index) in filterResults(allResults.performance)"
			:key="index"
		>
			<core-seo-site-analysis-result
				:test="index"
				:result="result"
				:show-instructions="showInstructions"
			/>
		</template>

		<div
			class="group-header"
			v-if="shouldShowGroup('security')"
		>{{ strings.security }}</div>

		<template
			v-for="(result, index) in filterResults(allResults.security)"
			:key="index"
		>
			<core-seo-site-analysis-result
				:test="index"
				:result="result"
				:show-instructions="showInstructions"
			/>
		</template>
	</div>
</template>

<script>
import { useTags } from '@/vue/composables/Tags'

import SiteAnalysis from '@/vue/classes/SiteAnalysis'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import CoreSeoSiteAnalysisResult from '@/vue/components/common/core/SeoSiteAnalysisResult'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			parseTags
		} = useTags({
			separator : undefined
		})

		return {
			parseTags
		}
	},
	components : {
		CoreGoogleSearchPreview,
		CoreSeoSiteAnalysisResult
	},
	props : {
		section : {
			type     : String,
			required : true
		},
		allResults : {
			type     : Object,
			required : true
		},
		showGooglePreview : Boolean,
		showInstructions  : Boolean
	},
	data () {
		return {
			searchPreviewHostname : '',
			searchPreviewUrl      : '',
			strings               : {
				basic       : __('Basic SEO', td),
				advanced    : __('Advanced SEO', td),
				performance : __('Performance', td),
				security    : __('Security', td)
			}
		}
	},
	methods : {
		filterResults (resultSet) {
			const results = { ...resultSet }

			// Filter out results that aren't rendered as rows.
			Object.keys(results).forEach(testName => {
				const testResult = results[testName]
				if (!SiteAnalysis.head(testName, testResult)) {
					const exceptions = [
						'keywords',
						'keywordsInTitleDescription',
						'searchPreview',
						'mobileSearchPreview',
						'mobileSnapshot'
					]

					if (exceptions.includes(testName)) {
						delete results[testName]
					}
				}
			})

			if ('all-items' === this.section) {
				return results
			}

			const sectionMap = {
				passed  : 'good-results',
				warning : 'recommended-improvements',
				error   : 'critical'
			}
			Object.keys(results).forEach(r => {
				const result = results[r]
				if (sectionMap[result.status] !== this.section) {
					delete results[r]
				}
			})

			return results
		},
		shouldShowGroup (group) {
			return Object.keys(this.filterResults(this.allResults[group])).length
		}
	},
	mounted () {
		if (!this.allResults?.advanced?.searchPreview) {
			return
		}

		const div = document.createElement('div')
		div.innerHTML = this.allResults.advanced.searchPreview

		const domain = div.querySelector('.domain')
		if (domain) {
			const urlObject = new URL(domain.innerText)

			this.searchPreviewUrl      = urlObject.href
			this.searchPreviewHostname = urlObject.host
		}
	}
}
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

		&:not(:first-child) {
			margin-top: 20px;
		}
	}
}
</style>