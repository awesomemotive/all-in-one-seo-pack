<template>
	<div class="aioseo-analyze-competitor-site">
		<core-analyze-competitor-site-header>
			<core-analyze
				:header="strings.enterCompetitorUrl"
				:description="strings.performInDepthAnalysis"
				:inputError="inputError"
				:isAnalyzing="isAnalyzing"
				:analyzeTime="analyzeTime"
				placeholder="https://competitorwebsite.com"
				@startAnalyzing="startAnalyzing"
			>
				<template #errors>
					<div
						v-if="inputError"
						class="aioseo-description aioseo-error"
					>
						{{ strings.pleaseEnterValidUrl }}
					</div>

					<div
						class="analyze-errors aioseo-description aioseo-error"
						v-if="'competitor-site' === analyzerStore.analyzer && analyzerStore.analyzeError"
						v-html="analyzerStore.analyzeError"
					/>
				</template>
			</core-analyze>

			<template #competitor-results>
				<core-card
					v-for="(results, site) in competitorResults"
					:key="site"
					:id="'aioseo-competitor-results' + hashCode(site)"
					:slug="'analyzeCompetitorSite' + site"
					:save-toggle-status="false"
					class="aioseo-competitor-results-wrapper"
				>
					<template #header>
						<core-analyze-score
							:score="parseResults(results).score"
						/>

						<span>{{ site }}</span>

						<svg-trash
							v-if="!isAnalyzing"
							@click.native="startDeleteSite(site)"
						/>
					</template>

					<div class="competitor-results-main">
						<core-site-score-competitor
							:site="site"
							:score="parseResults(results).score"
							:loading="analyzerStore.analyzing"
							:summary="getSummary(parseResults(results).results)"
							:mobile-snapshot="parseResults(results).results.advanced.mobileSnapshot"
						/>

						<div class="competitor-results-body">
							<core-seo-site-analysis-results
								section="all-items"
								:all-results="parseResults(results).results"
								show-google-preview
							/>
						</div>
					</div>
				</core-card>
			</template>
		</core-analyze-competitor-site-header>
	</div>
</template>

<script>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import links from '@/vue/utils/links'
import {
	useAnalyzerStore,
	useSettingsStore
} from '@/vue/stores'

import { merge } from 'lodash-es'
import { useScrollTo } from '@/vue/composables/ScrollTo'
import { useSeoSiteScore } from '@/vue/composables/SeoSiteScore'

import { isUrl } from '@/vue/utils/helpers'

import CoreAnalyze from '@/vue/components/common/core/analyze/Index'
import CoreAnalyzeScore from '@/vue/components/common/core/analyze/Score'
import CoreAnalyzeCompetitorSiteHeader from '@/vue/components/AIOSEO_VERSION/core/analyze/CompetitorSiteHeader'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSeoSiteAnalysisResults from '@/vue/components/common/core/SeoSiteAnalysisResults'
import CoreSiteScoreCompetitor from '@/vue/components/common/core/site-score/Competitor'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			strings
		} = useSeoSiteScore()

		const { scrollTo } = useScrollTo()

		return {
			analyzerStore     : useAnalyzerStore(),
			settingsStore     : useSettingsStore(),
			composableStrings : strings,
			scrollTo
		}
	},
	components : {
		CoreAnalyze,
		CoreAnalyzeScore,
		CoreAnalyzeCompetitorSiteHeader,
		CoreCard,
		CoreSeoSiteAnalysisResults,
		CoreSiteScoreCompetitor,
		SvgTrash
	},
	data () {
		return {
			competitorUrl     : null,
			isAnalyzing       : false,
			inputError        : false,
			competitorResults : {},
			analyzeTime       : 8,
			strings           : merge(this.composableStrings, {
				enterCompetitorUrl     : __('Enter Competitor URL', td),
				performInDepthAnalysis : __('Perform in-depth SEO Analysis of your competitor\'s website.', td),
				analyze                : __('Analyze', td),
				pleaseEnterValidUrl    : __('Please enter a valid URL.', td)
			})
		}
	},
	watch : {
		'analyzerStore.analyzeError' (newValue) {
			if (newValue) {
				this.isAnalyzing = false
			}
		}
	},
	computed : {
		getError () {
			switch (this.analyzerStore.analyzeError) {
				case 'invalid-url':
					return __('The URL provided is invalid.', td)
				case 'missing-content':
					return sprintf(
						'%1$s %2$s',
						__('We were unable to parse the content for this site.', td),
						links.getDocLink(GLOBAL_STRINGS.learnMore, 'seoAnalyzerIssues', true)
					)
				case 'invalid-token':
					return sprintf(
						// Translators: 1 - The plugin short name ('AIOSEO').
						__('Your site is not connected. Please connect to %1$s, then try again.', td),
						import.meta.env.VITE_SHORT_NAME
					)
			}

			return this.analyzerStore.analyzeError
		}
	},
	methods : {
		parseResults (results) {
			return JSON.parse(results)
		},
		getSummary (results) {
			return {
				recommended : this.analyzerStore.recommendedCount(results),
				critical    : this.analyzerStore.criticalCount(results),
				good        : this.analyzerStore.goodCount(results)
			}
		},
		startAnalyzing (competitorUrl) {
			this.inputError = false

			this.competitorUrl = competitorUrl
			if (!this.competitorUrl.startsWith('http://') && !this.competitorUrl.startsWith('https')) {
				this.competitorUrl = 'https://' + this.competitorUrl
			}

			if (!isUrl(this.competitorUrl)) {
				this.inputError = true
				return
			}

			this.analyzerStore.analyzing    = true
			this.analyzerStore.analyzeError = false
			this.analyzerStore.runSiteAnalyzer({
				url : this.competitorUrl
			})

			this.isAnalyzing = true
			setTimeout(this.checkStatus, this.analyzeTime * 1000)

			this.closeAllCards()
		},
		checkStatus () {
			this.isAnalyzing = false
			if (this.analyzerStore.analyzing) {
				this.$nextTick(() => {
					this.isAnalyzing = true

					// Don't allow it to go too fast.
					if (2 > this.analyzeTime) {
						this.analyzeTime = 8
					}

					this.analyzeTime = this.analyzeTime / 2
					setTimeout(this.checkStatus, this.analyzeTime * 1000)
				})

				return
			}

			this.$nextTick(() => {
				this.competitorResults = this.analyzerStore.getCompetitorSiteAnalysisResults // get results first so that we can get proper index for card

				const keys     = Object.keys(this.competitorResults)
				const keyIndex = -1 === keys.indexOf(this.competitorUrl) ? 0 : keys.indexOf(this.competitorUrl)

				this.toggleCard(keyIndex)
				this.scrollTo('aioseo-competitor-results' + this.hashCode(keys[keyIndex]))

				this.competitorUrl = null
			})
		},
		startDeleteSite (site) {
			this.closeAllCards()

			delete this.competitorResults[site]

			this.analyzerStore.deleteCompetitorSite(site)
				.then(() => {
					this.competitorResults = this.analyzerStore.getCompetitorSiteAnalysisResults
				})
		},
		closeAllCards () {
			const keys = Object.keys(this.competitorResults)
			keys.forEach(key => {
				this.settingsStore.closeCard('analyzeCompetitorSite' + key)
			})
		},
		toggleCard (index) {
			const keys = Object.keys(this.competitorResults)
			this.settingsStore.toggleCard({ slug: 'analyzeCompetitorSite' + keys[index] })
		},
		hashCode (string) {
			if (!string) {
				return
			}
			let hash = 0, i, chr
			for (i = 0; i < string.length; i++) {
				chr   = string.charCodeAt(i)
				hash  = ((hash << 5) - hash) + chr
				hash |= 0 // Convert to 32bit integer
			}
			return hash
		}
	},
	mounted () {
		this.analyzerStore.analyzeError = false
		this.competitorResults          = this.analyzerStore.getCompetitorSiteAnalysisResults

		this.toggleCard(0)
	}
}
</script>

<style lang="scss">
.aioseo-analyze-competitor-site {
	.header .aioseo-trash {
		width: 20px;
		height: 20px;
		color: $placeholder-color;
		margin-left: 14px;
		cursor: pointer;

		&:hover {
			color: $black2;
		}
	}

	.aioseo-competitor-results-wrapper {
		scroll-margin-top: 92px;
	}

	.competitor-results-main {
		display: flex;
		flex-direction: row;
		font-size: 16px;
		align-items: flex-start;

		.competitor-results-body {
			flex: 1;
			margin-left: 30px;
			min-width: 0; // @link: https://weblog.west-wind.com/posts/2016/feb/15/flexbox-containers-pre-tags-and-managing-overflow
		}
	}

	.aioseo-seo-site-score-cta {
		position: absolute;
		left: 50%;
		top: 50%;
		transform: translateX(-50%) translateY(-50%);
		background-color: #fff;
		padding: 20px;
		border: 1px solid $border;
		box-shadow: 0px 2px 10px rgba(0, 90, 224, 0.2);
		color: $black;
		font-size: 16px;
		font-weight: 600;
		width: 82%;
		max-width: 500px;
		text-align: center;
	}
}
</style>