<template>
	<div class="aioseo-seo-analysis">
		<div
			v-if="!analyzerStore.analyzeError"
			class="seo-analysis-score"
		>
			<core-site-score
				v-if="loading"
				:loading="true"
				:score="parseInt(score)"
				:description="description"
				:strokeWidth="1.75"
			/>

			<core-donut-chart
				v-else
				:parts="sortedParts"
				:total="parseInt(score)"
				:label="description"
				maxTotal="100"
				:animatedNumber="false"
				is-label-colored
			/>
		</div>

		<div
			v-if="!analyzerStore.analyzeError"
			class="seo-analysis-description"
		>
			<h2>{{ strings.yourHomepageScore }}</h2>

			<span v-if="rootStore?.aioseo?.urls?.home"
				class="seo-analysis-homepage-url"
			>
				<a target="_blank" :href="rootStore.aioseo.urls.home">{{ rootStore.aioseo.urls.home }}</a>
			</span>

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
			v-if="!analyzerStore.analyzeError && analyzerStore.homeResults?.results?.advanced?.mobileSnapshot"
			class="seo-analysis-mobile-snapshot"
		>
			<div class="mobile-snapshot-image">
				<img
					class="mobile-snapshot-image__frame"
					:src="getAssetUrl(iphoneFrame)"
					alt="Mobile Snapshot iPhone Frame"
				/>

				<img
					class="mobile-snapshot-image__content"
					alt="Mobile Snapshot"
					:src="analyzerStore.homeResults?.results?.advanced?.mobileSnapshot"
				/>
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
	useAnalyzerStore,
	useRootStore
} from '@/vue/stores'

import { getAssetUrl } from '@/vue/utils/helpers'
import iphoneFrame from '@/vue/assets/images/seo-analysis/iphone-frame.png'

import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'
import { getSortedParts } from '@/vue/pages/seo-analysis/utils'

import { merge } from 'lodash-es'

import CoreDonutChart from '@/vue/components/common/core/DonutChart'
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
			rootStore         : useRootStore(),
			composableStrings : strings,
			errorObject,
			links,
			getAssetUrl,
			iphoneFrame
		}
	},
	components : {
		CoreDonutChart,
		CoreSiteScore,
		SvgBook,
		SvgDannieLab
	},
	props : {
		score       : [ Number, String ],
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
				yourHomepageScore : __('Your Homepage Score:', td),
				goodResult        : sprintf(
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
	},
	computed : {
		sortedParts () {
			return getSortedParts({
				good     : this.summary.good,
				warnings : this.summary?.warnings || this.summary?.recommended,
				issues   : this.summary?.issues || this.summary?.critical,
				total    : this.summary?.total || this.summary?.good + this.summary?.recommended + this.summary?.critical
			})
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
	gap: 40px;

	.seo-analysis-score {
		position: relative;
		width: 100%;
		max-width: 160px;

		.aioseo-site-score {
			display: flex;
		}

		.score-analyzing {
			margin-top: 0 !important;
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

	.seo-analysis-mobile-snapshot {
		display: none;
		width: 245px;

		@media screen and (min-width: 1042px) {
			display: block;
		}

		.mobile-snapshot {
			&-image {
				position: relative;
				padding: 10px 4px 0;
				width: 100%;
				height: 445px;
				overflow: hidden;
				border-radius: 35px;

				&__frame {
					position: absolute;
					z-index: 2;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
				}

				&__content {
					width: 100%;
					height: 100%;
				}
			}
		}
	}

	.seo-analysis-homepage-url {
		display: block;
		padding: 8px 12px;
		background-color: $background;
		border-radius: 4px;
		font-weight: bold;
		margin-bottom: 12px;
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