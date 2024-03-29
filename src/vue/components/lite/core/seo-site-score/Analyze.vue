<template>
	<div class="aioseo-seo-site-score">
		<core-blur
			v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
		>
			<core-site-score-analyze
				:score="85"
				:description="description"
			/>
		</core-blur>

		<div
			v-if="!optionsStore.internalOptions.internal.siteAnalysis.connectToken"
			class="aioseo-seo-site-score-cta"
		>
			<a
				href="#"
				@click.prevent="openPopup(rootStore.aioseo.urls.connect)"
			>{{ connectWithAioseo }}</a> {{ strings.toSeeYourSiteScore }}
		</div>

		<core-site-score-analyze
			v-if="optionsStore.internalOptions.internal.siteAnalysis.connectToken"
			:score="score"
			:description="description"
			:loading="analyzing"
			:summary="getSummary"
		/>
	</div>
</template>

<script>
import {
	useAnalyzerStore,
	useConnectStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { popup } from '@/vue/utils/popup'
import { useSeoSiteScore } from '@/vue/composables'
import { SeoSiteScore } from '@/vue/mixins/SeoSiteScore'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreSiteScoreAnalyze from '@/vue/components/common/core/site-score/Analyze'
export default {
	setup () {
		const { strings } = useSeoSiteScore()

		return {
			analyzerStore : useAnalyzerStore(),
			connectStore  : useConnectStore(),
			optionsStore  : useOptionsStore(),
			rootStore     : useRootStore(),
			strings
		}
	},
	components : {
		CoreBlur,
		CoreSiteScoreAnalyze
	},
	mixins : [ SeoSiteScore ],
	data () {
		return {
			score : 0
		}
	},
	watch : {
		'optionsStore.internalOptions.internal.siteAnalysis.score' (newVal) {
			this.score = newVal
		}
	},
	computed : {
		getSummary () {
			return {
				recommended : this.analyzerStore.recommendedCount(),
				critical    : this.analyzerStore.criticalCount(),
				good        : this.analyzerStore.goodCount()
			}
		}
	},
	methods : {
		openPopup (url) {
			popup(
				url,
				this.connectWithAioseo,
				600,
				630,
				true,
				[ 'token' ],
				this.completedCallback,
				this.closedCallback
			)
		},
		completedCallback (payload) {
			return this.connectStore.saveConnectToken(payload.token)
		},
		closedCallback (reload) {
			if (reload) {
				this.analyzerStore.runSiteAnalyzer()
			}

			this.analyzerStore.analyzing = true
		}
	},
	mounted () {
		if (!this.optionsStore.internalOptions.internal.siteAnalysis.score && this.optionsStore.internalOptions.internal.siteAnalysis.connectToken) {
			this.analyzerStore.analyzing = true
			this.analyzerStore.runSiteAnalyzer()
		}

		this.score = this.optionsStore.internalOptions.internal.siteAnalysis.score
	}
}
</script>

<style lang="scss">
.aioseo-seo-site-score {
	.aioseo-blur {
		display: flex;
		align-items: center;
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