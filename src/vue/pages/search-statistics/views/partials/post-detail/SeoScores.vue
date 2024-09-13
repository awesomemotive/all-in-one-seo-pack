<template>
	<div class="aioseo-search-statistics-seo-scores">
		<div
			v-for="(setting, index) in seoScores"
			:key="index"
			class="item"
		>
			<div v-if="false !== setting.score" class="score">
				<svg-seo-site-score
					:score="setting.score"
					:scoreColor="setting.color"
					:strokeWidth="2"
				/>
				<span class="number">{{setting.score}}</span>
			</div>

			<div v-if="loading && false === setting.score" class="score score-loading">
				<core-loader dark />
			</div>

			<div v-if="!loading && false === setting.score" class="score score-error">
				<svg-circle-close />
			</div>

			<div class="score-title">{{setting.title}}</div>
			<div class="score-legend"></div>
		</div>
	</div>
</template>

<script>
import CoreLoader from '@/vue/components/common/core/Loader'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgSeoSiteScore from '@/vue/components/common/svg/seo-site-score/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		CoreLoader,
		SvgCircleClose,
		SvgSeoSiteScore
	},
	props : {
		scores  : Object,
		loading : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	computed : {
		seoScores () {
			if (!this.scores) {
				return []
			}

			return [ {
				title : __('Headline Analysis', td),
				score : this.scores.headline,
				color : this.getColor(this.scores.headline)
			}, /* { // TODO: Add this back in the next release.
				title : __('SEO Analysis', td),
				score : this.scores.seoAnalysis,
				color : this.getColor(this.scores.seoAnalysis)
			}, */ {
				title : __('TruSEO Score', td),
				score : this.scores.truSeo,
				color : this.getColor(this.scores.truSeo)
			} ]
		}
	},
	methods : {
		getColor (score) {
			switch (true) {
				case 30 >= score:
					return 'red'
				case 70 >= score:
					return 'orange'
				default:
					return 'green'
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-search-statistics-seo-scores {
	display: flex;
	justify-content: space-between;

	.item {
		flex-basis: 50%;
		/* max-width: 164px; */
	}

	.score {
		position: relative;
		max-width: 145px;
		min-height: 145px;
		margin: 0 auto 20px;

		&-error {
			color: $red;
		}

		&-loading {
			.aioseo-loading-spinner {
				width: 50px;
				height: 50px;
				position: relative;
				margin: 0 auto;
			}
		}

		.number {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			color: $black;
			font-weight: bold;
			font-size: 40px;
			line-height: 100%;

			@media (max-width: 1200px) {
				font-size: 16px;
			}
		}
	}

	.score-title {
		text-align: center;
		color: $black;
		font-size: 14px;
		font-weight: 700;
		margin-bottom: 10px;
	}
}
</style>