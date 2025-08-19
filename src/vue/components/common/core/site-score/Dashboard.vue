<template>
	<div class="aioseo-site-score-dashboard">
		<core-donut-chart-with-legend
			v-if="!analyzerStore.analyzeError && (loading || score)"
			:parts="sortedParts"
			:total="parseInt(score)"
			:label="scoreDescription"
			maxTotal="100"
			:link="chartLink"
			:loading="loading"
			:loadingText="strings.analyzing"
		/>

		<div
			class="seo-analysis-error"
			v-if="(!score && !loading) || (analyzerStore.analyzeError && errorObject)"
		>
			<svg-dannie-lab />

			<p class="error-title">{{ strings.anErrorOccurred }}</p>

			<p v-if="errorObject?.description" class="error-description" v-html="errorObject.description"/>

			<div v-if="errorObject?.buttons" class="error-action-buttons">
				<base-button
					v-for="(button, index) in errorObject.buttons"
					:key="index"
					:type="button.type"
					:tag="button.tag ? button.tag : 'button'"
					target="_blank"
					:href="button.url ? button.url : ''"
					size="small"
					:loading="button?.runAgain && analyzerStore.analyzing"
					@click="button?.runAgain ? analyzerStore.runSiteAnalyzer() : ''"
				>
					{{ button.text }}
				</base-button>
			</div>
		</div>
	</div>
</template>

<script>
import { ref } from 'vue'

import {
	useAnalyzerStore,
	useRootStore,
	useLicenseStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'
import { getSortedParts } from '@/vue/pages/seo-analysis/utils'

import CoreDonutChartWithLegend from '@/vue/components/common/core/DonutChartWithLegend'
import SvgDannieLab from '@/vue/components/common/svg/dannie/Lab'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const {
			description,
			errorObject,
			strings
		} = useSeoSiteScore({
			score : ref(props.score)
		})

		return {
			analyzerStore     : useAnalyzerStore(),
			composableStrings : strings,
			description,
			errorObject,
			rootStore         : useRootStore(),
			licenseStore      : useLicenseStore()
		}
	},
	components : {
		CoreDonutChartWithLegend,
		SvgDannieLab
	},
	props : {
		score   : Number,
		loading : Boolean,
		summary : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			allowed,
			strings : {
				...this.composableStrings,
				analyzing : __('Analyzing...', td)
			},
			scoreDescription :	this.description
		}
	},
	watch : {
		score : {
			handler (newVal) {
				const { description } = useSeoSiteScore({
					score : ref(newVal)
				})

				this.scoreDescription = description
			}
		}
	},
	computed : {
		getError () {
			switch (this.analyzerStore.analyzeError) {
				case 'invalid-url':
					return __('The URL provided is invalid.', td)
				case 'missing-content':
					return __('We were unable to parse the content for this site.', td)
				case 'invalid-token':
					return sprintf(
						// Translators: 1 - The plugin short name ('AIOSEO').
						__('Your site is not connected. Please connect to %1$s, then try again.', td),
						import.meta.env.VITE_SHORT_NAME
					)
			}

			return this.analyzerStore.analyzeError
		},
		sortedParts () {
			// If loading, set all counts to 10 so the graph can load
			const goodCount     = this.loading ? 10 : this.analyzerStore.goodCount('homepage')
			const warningsCount = this.loading ? 10 : this.analyzerStore.recommendedCount('homepage')
			const criticalCount = this.loading ? 10 : this.analyzerStore.criticalCount('homepage')
			const totalCount    = goodCount + warningsCount + criticalCount

			const sortedParts = getSortedParts({
				good     : goodCount,
				warnings : warningsCount,
				issues   : criticalCount,
				total    : totalCount
			}, 'homepage')

			// If loading, reset all counts to 0 after parsing/sorting, so the labels are correct.
			if (this.loading) {
				return sortedParts.map(part => {
					part.count = 0

					return part
				})
			}

			return sortedParts
		},
		chartLink () {
			if (!this.allowed('aioseo_seo_analysis_settings')) {
				return ''
			}

			return `<div class="links">
				<a href="${this.rootStore.aioseo.urls.aio.seoAnalysis}">${this.strings.completeSiteAuditChecklist}</a>
				<a href="${this.rootStore.aioseo.urls.aio.seoAnalysis}" class="no-underline">&rarr;</a>
			</div>`
		}
	}
}
</script>

<style lang="scss">
.aioseo-site-score-dashboard {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: flex-start;

	.analyze-errors {
		align-items: center;
		justify-content: center;
		margin-left: auto;
		margin-right: auto;
		text-align: center;
	}

	.links {
		margin-top: 18px;
		font-size: 14px;
		font-weight: 600;
	}

	.seo-analysis-error {
		max-width: 740px;
		text-align: center;
		margin: 12px 0;

		p {
			&.error-title {
				margin: 12px 0 6px 0;

				font-size: 16px;
				font-weight: 700;
				line-height: 30px;
			}

			&.error-description {
				font-weight: 400;
				line-height: 24px;
			}
		}

		.error-action-buttons {
				display: flex;
				gap: 12px;
				margin-top: 12px;
				justify-content: center;
			}

		svg.aioseo-dannie-lab {
			width: 120px;
			height: 120px;
		}
	}
}
</style>