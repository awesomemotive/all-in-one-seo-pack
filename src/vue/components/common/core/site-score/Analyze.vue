<template>
	<div class="aioseo-seo-analysis">
		<div
			v-if="!analyzerStore.analyzeError"
			class="seo-analysis-score"
		>
			<core-site-score
				:loading="loading"
				:score="score"
				:description="description"
				:strokeWidth="1.75"
			/>
		</div>

		<div
			v-if="!analyzerStore.analyzeError"
			class="seo-analysis-description"
		>
			<h2>{{ strings.yourOverallSiteScore }}</h2>

			<div v-html="strings.goodResult" />

			<div v-html="strings.forBestResults" />

			<div class="d-flex">
				<svg-book />

				<a
					:href="links.getDocUrl('ultimateGuide')"
					target="_blank"
				>{{ strings.readUltimateSeoGuide }}</a>
			</div>
		</div>

		<div
			class="seo-analysis-error"
			v-if="analyzerStore.analyzeError && errorObject"
		>
			<svg-dannie-lab />

			<p class="error-title">{{ strings.anErrorOccurred }}</p>

			<p class="error-description" v-html="errorObject.description"/>

			<div class="error-action-buttons">
				<base-button
					v-for="(button, index) in errorObject.buttons"
					:key="index"
					:type="button.type"
					:tag="button.tag ? button.tag : 'button'"
					target="_blank"
					:href="button.url ? button.url : ''"
					size="medium"
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

import links from '@/vue/utils/links'
import {
	useAnalyzerStore
} from '@/vue/stores'

import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import { merge } from 'lodash-es'

import CoreSiteScore from '@/vue/components/common/core/site-score/Index'
import SvgBook from '@/vue/components/common/svg/Book'
import SvgDannieLab from '@/vue/components/common/svg/dannie/Lab'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup (props) {
		const {
			errorObject,
			strings
		} = useSeoSiteScore({
			score : ref(props.score)
		})

		return {
			analyzerStore     : useAnalyzerStore(),
			composableStrings : strings,
			errorObject,
			links
		}
	},
	components : {
		CoreSiteScore,
		SvgBook,
		SvgDannieLab
	},
	props : {
		score       : Number,
		loading     : Boolean,
		description : String,
		summary     : {
			type : Object,
			default () {
				return {}
			}
		}
	},
	data () {
		return {
			strings : merge({
				yourOverallSiteScore : __('Your Overall Site Score', td),
				goodResult           : sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Initial score range, 4 - Final score range.
					__('A very good score is between %1$s%3$d and %4$d%2$s.', td),
					'<strong>',
					'</strong>',
					50,
					75
				),
				forBestResults : sprintf(
					// Translators: 1 - Opening bold HTML tag, 2 - Closing bold HTML tag, 3 - Score.
					__('For best results, you should strive for %1$s%3$d and above%2$s.', td),
					'<strong>',
					'</strong>',
					70
				),
				readUltimateSeoGuide : __('Read the Ultimate WordPress SEO Guide', td)
			}, this.composableStrings)
		}
	}
}
</script>

<style lang="scss">
.aioseo-seo-analysis {
	display: flex;
	position: relative;
	flex: 1;
	justify-content: center;
	align-items: center;

	.seo-analysis-score {
		position: relative;
		width: 100%;
		max-width: 160px;
		margin-right: 32px;

		.aioseo-site-score {
			display: flex;
		}

		.aioseo-score-amount {
			.score {
				font-size: 40px;
				line-height: 1.2;
			}

			.total {
				font-size: 14px;
			}
		}

		.score-description {
			font-size: 13px;
		}

		svg {
			width: 100%;
			height: auto;
		}
	}

	.seo-analysis-description {
		h2 {
			font-size: 24px;
			line-height: 30px;
			margin-bottom: 12px;
		}

		.links {
			margin-top: 30px;
			font-size: 14px;
			font-weight: 600;

			.no-underline {
				padding-left: 5px;
			}
		}

		div[class] {
			margin-top: 12px;
		}

		svg.aioseo-book {
			width: 20px;
			height: 20px;
			margin: 0 10px 0 0;
			color: $blue;
		}
	}

	.seo-analysis-error {
		max-width: 740px;
		text-align: center;
		margin: 40px 0;

		p {
			&.error-title {
				margin: 20px 0 8px 0;

				font-size: 24px;
				font-weight: 700;
				line-height: 30px;
			}

			&.error-description {
				font-size: 16px;
				font-weight: 400;
				line-height: 24px;
			}
		}

		.error-action-buttons {
				display: flex;
				gap: 12px;
				margin-top: 20px;
				justify-content: center;
			}

		svg.aioseo-dannie-lab {
			width: 120px;
			height: 120px;
		}
	}
}
</style>