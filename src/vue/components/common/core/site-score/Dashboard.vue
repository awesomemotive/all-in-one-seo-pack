<template>
	<div class="aioseo-site-score-dashboard">
		<div
			v-if="!analyzerStore.analyzeError && (loading || score)"
			class="aioseo-seo-site-score-score"
		>
			<core-site-score
				:loading="loading"
				:score="score"
				:description="scoreDescription"
				:strokeWidth="1.75"
			/>
		</div>

		<div
			v-if="!analyzerStore.analyzeError && (loading || score)"
			class="aioseo-seo-site-score-recommendations"
		>
			<div class="critical">
				<span class="round red">{{ summary.critical || 0 }}</span>
				{{ strings.criticalIssues }}
			</div>
			<div class="recommended">
				<span class="round blue">{{ summary.recommended || 0 }}</span>
				{{ strings.recommendedImprovements }}
			</div>
			<div class="good">
				<span class="round green">{{ summary.good || 0 }}</span>
				{{ strings.goodResults }}
			</div>

			<div class="links" v-if="allowed('aioseo_seo_analysis_settings')">
				<a :href="rootStore.aioseo.urls.aio.seoAnalysis">{{ strings.completeSiteAuditChecklist }}</a>
				<a :href="rootStore.aioseo.urls.aio.seoAnalysis" class="no-underline">&rarr;</a>
			</div>
		</div>

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
	useRootStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import CoreSiteScore from '@/vue/components/common/core/site-score/Index'
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
			rootStore         : useRootStore()
		}
	},
	components : {
		CoreSiteScore,
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
			strings          : this.composableStrings,
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

	.aioseo-seo-site-score-score {
		position: relative;
		width: 100%;
		max-width: 200px;
		margin-right: 1rem;

		svg {
			width: 100%;
			height: auto;
		}
	}

	.aioseo-seo-site-score-recommendations {
		> div:not(.links) {
			display: flex;
			align-items: center;
			font-size: 14px;
			color: $black;
			font-weight: 600;
			margin-bottom: 10px;

			.round {
				position: relative;
				border-radius: 50%;
				width: 24px;
				min-width: 24px;
				max-width: 24px;
				height: 24px;
				line-height: 24px;
				display: flex;
				align-items: center;
				justify-content: center;
				margin-right: 10px;
				font-size: 12px;
				color: #fff;
				font-weight: 600;

				&.red {
					background-color: $red;
				}

				&.blue {
					background-color: $blue;
				}

				&.orange {
					background-color: $orange;
				}

				&.green {
					background-color: $green;
				}
			}
		}

		.links {
			margin-top: 18px;
			font-size: 14px;
			font-weight: 600;

			.no-underline {
				padding-left: 5px;
			}
		}
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