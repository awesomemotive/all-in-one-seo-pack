<template>
	<div class="aioseo-seo-site-analysis-results">
		<template
			v-if="showGooglePreview && 'all-items' === section"
		>
			<core-google-search-preview
				:domain="searchPreviewDomain"
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
import { Tags } from '@/vue/mixins'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import CoreSeoSiteAnalysisResult from '@/vue/components/common/core/SeoSiteAnalysisResult'
export default {
	components : {
		CoreGoogleSearchPreview,
		CoreSeoSiteAnalysisResult
	},
	mixins : [ Tags ],
	props  : {
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
			separator           : undefined,
			searchPreviewDomain : null,
			strings             : {
				basic       : this.$t.__('Basic SEO', this.$td),
				advanced    : this.$t.__('Advanced SEO', this.$td),
				performance : this.$t.__('Performance', this.$td),
				security    : this.$t.__('Security', this.$td)
			}
		}
	},
	methods : {
		filterResults (resultSet) {
			const results = { ...resultSet }

			const deprecatedChecks = [
				'searchPreview',
				'mobileSearchPreview',
				'mobileSnapshot',
				'keywords',
				'keywordsInTitleDescription'
			]

			deprecatedChecks.forEach((name) => {
				if (results[name]) {
					delete results[name]
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
		if (this.allResults.advanced?.searchPreview ?? null) {
			const div = document.createElement('div')
			div.innerHTML = this.allResults.advanced.searchPreview

			const domain = div.querySelector('.domain')
			if (domain) {
				this.searchPreviewDomain = domain.innerText
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-site-analysis-results {
	padding-top: 12px;

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