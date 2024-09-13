<template>
	<div class="aioseo-headline-analyzer">
		<core-card
			slug="analyzeNewHeadline"
			hide-header
			no-slide
			:toggles="false"
		>
			<core-analyze
				:header="strings.enterYourHeadline"
				:description="strings.writeIrresistibleSEOHeadlines"
				:isAnalyzing="isAnalyzing"
				:analyzeTime="analyzeTime"
				@startAnalyzing="startAnalyzing"
				:placeholder="strings.placeholder"
			>
				<template #errors>
					<div
						class="analyze-errors aioseo-description aioseo-error"
						v-if="'headline' === analyzerStore.analyzer && analyzerStore.analyzeError"
						v-html="analyzerStore.analyzeError"
					/>
				</template>
			</core-analyze>
		</core-card>

		<core-card
			v-for="(result, headline) in headlines"
			:key="headline"
			:id="'aioseo-headline-result' + hashCode(headline)"
			:slug="'analyzeHeadline' + headline"
			:save-toggle-status="false"
			class="aioseo-headline-result-wrapper"
		>
			<template #header>
				<core-analyze-score
					:score="parseResult(result).score"
				/>

				<span>&ldquo;{{ headline }}&rdquo;</span>

				<svg-trash
					@click.native="startDeleteheadline(headline)"
				/>
			</template>

			<div class="headline-result-main">
				<core-headline-score
					:score="parseResult(result).score"
					:loading="analyzerStore.analyzing"
				/>

				<div class="headline-result-body">
					<core-headline-result
						:result="parseResult(result).result"
					/>
				</div>
			</div>
		</core-card>
	</div>
</template>

<script>
import {
	useAnalyzerStore,
	useSettingsStore
} from '@/vue/stores'
import { useScrollTo } from '@/vue/composables/ScrollTo'

import CoreAnalyze from '@/vue/components/common/core/analyze/Index'
import CoreAnalyzeScore from '@/vue/components/common/core/analyze/Score'
import CoreCard from '@/vue/components/common/core/Card'
import CoreHeadlineResult from '@/vue/components/common/core/headline/Result'
import CoreHeadlineScore from '@/vue/components/common/core/headline/Score'
import SvgTrash from '@/vue/components/common/svg/Trash'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const { scrollTo } = useScrollTo()

		return {
			analyzerStore : useAnalyzerStore(),
			settingsStore : useSettingsStore(),
			scrollTo
		}
	},
	components : {
		CoreAnalyze,
		CoreAnalyzeScore,
		CoreCard,
		CoreHeadlineResult,
		CoreHeadlineScore,
		SvgTrash
	},
	data () {
		return {
			headline    : null,
			headlines   : {},
			isAnalyzing : false,
			analyzeTime : 2,
			strings     : {
				enterYourHeadline             : __('Enter Your Headline', td),
				placeholder                   : __('Top 10 Ways to Increase Traffic', td),
				writeIrresistibleSEOHeadlines : sprintf(
					// Translators: 1 - HTML Line break tag.
					__('Our Headline Analyzer tool enables you to write irresistible SEO headlines%1$sthat drive traffic, shares, and rank better in search results.', td),
					'<br>'
				),
				analyze : __('Analyze', td)
			}
		}
	},
	watch : {
		'analyzerStore.analyzeError' (newValue) {
			if (newValue) {
				this.isAnalyzing = false
			}
		}
	},
	methods : {
		parseResult (results) {
			return JSON.parse(results)
		},
		startAnalyzing (headline) {
			this.headline = headline
			this.analyzerStore.analyzing    = true
			this.analyzerStore.analyzeError = false
			this.analyzerStore.runHeadlineAnalyzer({
				headline            : this.headline,
				shouldStoreHeadline : true
			})

			this.isAnalyzing = true
			setTimeout(this.checkStatus, this.analyzeTime * 1000)

			this.closeAllCards()
		},
		checkStatus () {
			this.isAnalyzing = false
			if (this.analyzing) {
				this.$nextTick(() => {
					this.isAnalyzing = true

					// Don't allow it to go too fast.
					if (2 > this.analyzeTime) {
						this.analyzeTime = 1
					}

					this.analyzeTime = this.analyzeTime / 2
					setTimeout(this.checkStatus, this.analyzeTime * 1000)
				})

				return
			}

			this.headline  = null
			this.headlines = this.analyzerStore.getHeadlineAnalysisResults
			this.toggleFirstCard()

			this.$nextTick(() => {
				const keys = Object.keys(this.headlines)

				this.scrollTo('aioseo-headline-result' + this.hashCode(keys[0]))
			})
		},
		startDeleteheadline (headline) {
			this.closeAllCards()

			delete this.headlines[headline]

			this.analyzerStore.deleteHeadline(headline)
				.then(() => {
					this.headlines = this.analyzerStore.getHeadlineAnalysisResults
				})
		},
		closeAllCards () {
			const keys = Object.keys(this.headlines)
			keys.forEach(key => {
				this.settingsStore.closeCard('analyzeHeadline' + key)
			})
		},
		toggleFirstCard () {
			const keys = Object.keys(this.headlines)
			this.settingsStore.toggleCard({ slug: 'analyzeHeadline' + keys[0] })
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
		this.headlines                  = this.analyzerStore.getHeadlineAnalysisResults

		this.toggleFirstCard()
	}
}
</script>

<style lang="scss">
.aioseo-headline-analyzer {
	.aioseo-trash {
		width: 20px;
		height: 20px;
		color: $placeholder-color;
		margin-left: 14px;
		cursor: pointer;

		&:hover {
			color: $black2;
		}
	}

	.aioseo-headline-result-wrapper {
		scroll-margin-top: 92px;
	}

	.headline-result-main {
		display: flex;
		flex-direction: row;
		font-size: 14px;
		align-items: flex-start;
		color: $black;

		.headline-result-body {
			flex: 1;
			margin-left: 30px;
			min-width: 0; // @link: https://weblog.west-wind.com/posts/2016/feb/15/flexbox-containers-pre-tags-and-managing-overflow

			@media (max-width: 782px) {
				margin-left: 0;
			}
		}

		@media (max-width: 782px) {
			display: block;

			.aioseo-headline-score {
				margin-bottom: 30px;
			}
		}
	}
}
</style>