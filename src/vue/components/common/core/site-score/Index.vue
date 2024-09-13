<template>
	<div class="aioseo-site-score">
		<svg-seo-site-score-loading
			v-if="loading"
		/>

		<svg-seo-site-score
			v-if="!loading"
			:score="score"
			:strokeWidth="strokeWidth"
		/>

		<div class="aioseo-score-amount-wrapper">
			<div
				v-if="!loading"
				class="aioseo-score-amount"
			>
				<span class="score">{{ score }}</span>
				<span class="total">/ 100</span>
			</div>

			<div
				v-if="!loading"
				class="score-description"
				v-html="description"
			/>

			<div
				v-if="loading"
				class="score-analyzing"
			>
				{{ strings.analyzing }}
			</div>
		</div>
	</div>
</template>

<script>
import SvgSeoSiteScore from '@/vue/components/common/svg/seo-site-score/Index'
import SvgSeoSiteScoreLoading from '@/vue/components/common/svg/seo-site-score/Loading'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		SvgSeoSiteScore,
		SvgSeoSiteScoreLoading
	},
	props : {
		score       : Number,
		loading     : Boolean,
		description : String,
		strokeWidth : {
			type : Number,
			default () {
				return 1.75
			}
		}
	},
	data () {
		return {
			strings : {
				analyzing : __('Analyzing...', td)
			}
		}
	}
}
</script>

<style lang="scss">
.aioseo-score-amount-wrapper {
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
	margin: 20px;

	.aioseo-score-amount {
		line-height: 1.2;

		.score {
			font-size: 50px;
			font-weight: $font-bold;
			color: inherit;
		}

		.total {
			font-size: 18px;
			line-height: 1.5;
			font-weight: $font-bold;
			color: $placeholder-color;
			padding-left: 3px;
		}
	}

	.score-description {
		max-width: 80%;
		text-align: center;
		font-size: 16px;
		font-weight: $font-bold;
		line-height: 1.3;
		color: inherit;
	}

	.score-analyzing {
		margin-top: 16px;
		font-size: 24px;
	}
}

.aioseo-site-score {
	display: flex;

	&:has(.stroke-red) {

		.aioseo-score-amount-wrapper {
			color: $red;
		}
	}

	&:has(.stroke-orange) {

		.aioseo-score-amount-wrapper {
			color: $orange;
		}
	}

	&:has(.stroke-green) {

		.aioseo-score-amount-wrapper {
			color: $green;
		}
	}
}

</style>