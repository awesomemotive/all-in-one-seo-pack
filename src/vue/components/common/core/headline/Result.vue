<template>
	<div class="aioseo-headline-result">
		<div class="box box--large">
			<div class="box-title">{{ strings.wordBalance }}</div>
			<div class="box-icon">
				<component
					:is="wordBalance.icon"
					:class="wordBalance.class"
				/>
			</div>
			<strong
				class="box-result"
				:class="wordBalance.class"
			>
				{{ wordBalance.result }}
			</strong>
			<div class="words">
				<div
					v-for="(item, index) in wordBalance.words"
					:key="index"
				>
					<div class="words-title">
						{{ item.title }}
						<core-tooltip
							v-if="item.help"
						>
							<svg-circle-question-mark />

							<template #tooltip>
								<div v-html="item.help" />
							</template>
						</core-tooltip>
					</div>
					<div
						class="words-percent"
						:class="item.class"
						>
						{{ item.percent }} %
					</div>
					<div class="words-goal">{{ strings.goal }} {{ item.goal }}</div>
					<div class="bar">
						<div
							class="bar-progress"
							:class="item.class"
							:style="{width: item.bar + '%'}"
						/>
					</div>
					<div class="keywords">
						<div
							v-for="(word, index) in item.words"
							:key="index"
							class="keywords-item"
						>
							{{ word }}
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="box">
			<div class="box-icon">
				<div class="score">
					<svg-seo-site-score
						:score="characterCount.percent"
						:scoreColor="characterCount.class"
						:strokeWidth="2"
					/>
					<span class="number">{{ result.length }}</span>
				</div>
			</div>
			<div>
				<div class="box-title">{{ strings.characterCount }}</div>
				<strong
					class="box-result has-icon"
					:class="characterCount.class"
				>
					<component
						:is="characterCount.icon"
						:class="characterCount.class"
					/>
					{{ characterCount.result }}
				</strong>
				<p class="box-guideline">
					{{ characterCount.guideline }}
				</p>
			</div>
		</div>

		<div class="box">
			<div class="box-icon">
				<div class="score">
					<svg-seo-site-score
						:score="wordCount.percent"
						:scoreColor="wordCount.class"
						:strokeWidth="2"
					/>
					<span class="number">{{ result.wordCount }}</span>
				</div>
			</div>
			<div>
				<div class="box-title">{{ strings.wordCount }}</div>
				<strong
					class="box-result has-icon"
					:class="wordCount.class"
				>
					<component
						:is="wordCount.icon"
						:class="wordCount.class"
					/>
					{{ wordCount.result }}
				</strong>
				<p class="box-guideline">
					{{ wordCount.guideline }}
				</p>
			</div>
		</div>

		<div class="box">
			<div class="box-icon">
				<component
					:is="sentiment.icon"
					:class="sentiment.class"
				/>
			</div>
			<div>
				<div class="box-title">{{ strings.sentiment }}</div>
				<strong
					class="box-result"
					:class="sentiment.class"
				>
					{{ sentiment.result }}
				</strong>
				<p class="box-guideline">
					<strong>{{ sentiment.headline }}</strong> {{ sentiment.guideline }}
				</p>
			</div>
		</div>

		<div class="box">
			<div class="box-icon">
				<component
					:is="headlineTypes.icon"
					:class="headlineTypes.class"
				/>
			</div>
			<div>
				<div class="box-title">{{ strings.headlineType }}</div>
				<strong
					class="box-result"
					:class="headlineTypes.class"
				>
					{{ headlineTypes.result }}
				</strong>
				<p
					v-html="headlineTypes.guideline"
					class="box-guideline"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgFaceNegative from '@/vue/components/common/svg/face/Negative'
import SvgFaceNeutral from '@/vue/components/common/svg/face/Neutral'
import SvgFaceSmile from '@/vue/components/common/svg/face/Smile'
import SvgList from '@/vue/components/common/svg/List'
import SvgSeoSiteScore from '@/vue/components/common/svg/seo-site-score/Index'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	wordBalance    : __('Word balance', td),
	characterCount : __('Character Count', td),
	sentiment      : __('Sentiment', td),
	wordCount      : __('Word Count', td),
	headlineType   : __('Headline Type', td),
	goal           : __('Goal: ', td)
}

const props = defineProps({
	result : {
		type     : Object,
		required : true
	}
})

const wordBalance = computed(() => {
	const result = props.result

	const words = {
		common : {
			title   : __('Common Words', td),
			help    : __('Headlines with 20-30% common words are more likely to get clicks.', td),
			class   : 0 === result.commonWordsPercentage ? 'red' : 0.2 > result.commonWordsPercentage ? 'orange' : 'green',
			words   : result.commonWords,
			percent : Math.round(result.commonWordsPercentage * 100),
			bar     : (100 * result.commonWordsPercentage) / 0.3,
			goal    : __('20-30%', td)
		},
		uncommon : {
			title   : __('Uncommon Words', td),
			help    : __('Your headline would be more likely to get clicks if it had more uncommon words.', td),
			class   : 0 === result.uncommonWordsPercentage ? 'red' : 0.1 > result.uncommonWordsPercentage ? 'orange' : 'green',
			words   : result.uncommonWords,
			percent : Math.round(result.uncommonWordsPercentage * 100),
			bar     : (100 * result.uncommonWordsPercentage) / 0.2,
			goal    : __('10-20%', td)
		},
		emotional : {
			title   : __('Emotional Words', td),
			help    : __('Emotionally triggered headlines are likely to drive more clicks.', td),
			class   : 0 === result.emotionalWordsPercentage ? 'red' : 0.1 > result.emotionalWordsPercentage ? 'orange' : 'green',
			words   : result.emotionWords,
			percent : Math.round(result.emotionalWordsPercentage * 100),
			bar     : (100 * result.emotionalWordsPercentage) / 0.15,
			goal    : __('10-15%', td)
		},
		power : {
			title   : __('Power Words', td),
			help    : __('Headlines with power words are more likely to get clicks.', td),
			class   : 0 === result.powerWordsPercentage ? 'red' : 'green',
			words   : result.powerWords,
			percent : Math.round(result.powerWordsPercentage * 100),
			bar     : (100 * result.powerWordsPercentage) / 0.1,
			goal    : __('at least one', td)
		}
	}

	const allGood = Object.values(words).every(word => 'green' === word.class)

	return {
		result : allGood ? __('All good', td) : __('Needs improvement', td),
		icon   : allGood ? SvgCircleCheck : SvgCircleExclamation,
		class  : allGood ? 'green' : 'orange',
		words
	}
})

const characterCount = computed(() => {
	let color, result, icon, guideline
	const length  = props.result.length
	const percent = Math.round((100 * length) / 65) // 65 is the limit for a good grade.

	switch (true) {
		case 19 >= length:
		case 80 <= length:
			color = 'red'
			icon  = SvgCircleClose
			break
		case 20 <= length && 34 >= length:
		case 67 <= length && 79 >= length:
			color = 'orange'
			icon  = SvgCircleExclamation
			break
		case 35 <= length && 66 >= length:
			color = 'green'
			icon  = SvgCircleCheck
			break
	}

	switch (true) {
		case 34 >= length:
			result = __('Too Short', td)
			guideline = __('You have space to add more keywords and power words to boost your rankings and click-through rate.', td)
			break
		case 35 <= length && 66 >= length:
			result = __('Good', td)
			guideline = __('Headlines that are about 55 characters long will display fully in search results and tend to get more clicks.', td)
			break
		case 67 <= length:
			result = __('Too Long', td)
			guideline = __('At this length, it will get cut off in search results. Try reducing it to about 55 characters.', td)
			break
	}

	return {
		result,
		icon,
		percent,
		guideline,
		class : color
	}
})

const wordCount = computed(() => {
	const wordCountResult = props.result.wordCount
	const percent         = Math.round((100 * wordCountResult) / 9) // 9 is the limit for a good grade.

	switch (true) {
		case 4 >= wordCountResult:
			return {
				result    : __('Not Enough Words', td),
				percent   : percent,
				class     : 'red',
				icon      : SvgCircleClose,
				guideline : __('Your headline doesn’t use enough words. You have more space to add keywords and power words to improve your SEO and get more engagement.', td)
			}
		case 5 <= wordCountResult && 9 >= wordCountResult:
			return {
				result    : __('Good', td),
				percent   : percent,
				class     : 'green',
				icon      : SvgCircleCheck,
				guideline : __('Your headline has the right amount of words. Headlines are more likely to be clicked on in search results if they have about 6 words.', td)
			}
		case 10 <= wordCountResult && 11 >= wordCountResult:
			return {
				result    : __('Reduce Word Count', td),
				percent   : percent,
				class     : 'orange',
				icon      : SvgCircleExclamation,
				guideline : __('Headlines are more likely to be clicked on in search results if they have about 6 words.', td)
			}
		default:
			return {
				result    : __('Too Many Words', td),
				percent   : percent,
				class     : 'red',
				icon      : SvgCircleClose,
				guideline : __('Your headline has too many words. Long headlines will get cut off in search results and won’t get as many clicks.', td)
			}
	}
})

const sentiment = computed(() => {
	switch (props.result.sentiment) {
		case 'neu':
			return {
				result    : __('Neutral', td),
				icon      : SvgFaceNeutral,
				class     : 'orange',
				headline  : __('Your headline has a neutral sentiment.', td),
				guideline : __('Headlines that are strongly positive or negative tend to get more engagement than neutral ones.', td)
			}
		case 'pos':
			return {
				result    : __('Positive', td),
				icon      : SvgFaceSmile,
				class     : 'green',
				headline  : __('Your headline has a positive sentiment.', td),
				guideline : __('Positive headlines tend to get better engagement than neutral or negative ones.', td)
			}
		default:
			return {
				result    : __('Negative', td),
				icon      : SvgFaceNegative,
				class     : 'red',
				headline  : __('Your headline has a negative sentiment.', td),
				guideline : __('Negative headlines are attention-grabbing and tend to perform better than neutral ones.', td)
			}
	}
})

const headlineTypes = computed(() => {
	return {
		result    : props.result.headlineTypes.join(', '),
		icon      : SvgList,
		class     : 'blue',
		guideline : sprintf(
			// Translators: 1 - HTML line break tag, 2 - Opening HTML link tag, 3 - Closing HTML link tag.
			__('Headlines that are lists and how-to get more engagement on average than other types of headlines. %1$s%2$sLearn More%3$s →', td),
			'<br/><br/>',
			'<a href="https://optinmonster.com/why-these-21-headlines-went-viral-and-how-you-can-copy-their-success/" target="_blank" className="aioseo-headline-analyzer-link"><span>',
			'</span></a>'
		)
	}
})
</script>

<style lang="scss">
.aioseo-headline-result {
	--aioseo-gutter: 20px;

	@include aioseoGrid(2, 255px);

	.box {
		padding: 20px;
		border: 1px solid #E8E8EB;
		display: flex;
		color: $black2;

		@media (max-width: 1200px) {
			padding: 20px;
		}

		&-icon {
			margin-right: 20px;
			flex: 0 0 90px;
			padding-top: 5px;
			line-height: 0;
			text-align: center;

			@media (max-width: 1200px) {
				flex: 0 0 40px;
				margin-right: 12px;
			}

			> svg {
				width: 52px;
			}

			.score {
				position: relative;

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
					font-size: 32px;
					line-height: 40px;

					@media (max-width: 1200px) {
						font-size: 16px;
					}
				}
			}
		}

		&-title {
			color: #000000;
			font-weight: 600;
			margin-bottom: 4px;
			font-size: $font-md;
			line-height: 22px;
		}

		&-result {
			margin-bottom: 12px;
			display: inline-block;
			font-weight: 600;
			font-size: $font-md;
			line-height: 22px;

			&.has-icon {
				display: flex;
				align-items: center;
				line-height: 1;

				svg {
					width: 16px;
					margin-right: 5px;
				}
			}
		}

		&-guideline {
			margin: 0;

			strong {
				display: inline;
			}
		}

		&--large {
			grid-column: 1 / -1;
			display: flex;
			align-items: center;
			flex-wrap: wrap;

			.box-title {
				margin-bottom: 0;
				margin-right: 16px;
			}

			.box-icon {
				margin-right: 6px;
				flex: 0 0 auto;
				padding-top: 0;

				svg {
					width: 20px;
				}
			}

			.box-result {
				font-size: 14px;
				line-height: 150%;
				margin-bottom: 0;
			}
		}
	}

	.words {
		flex: 1 0 100%;
		margin-top: 20px;
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 50px;

		@media (max-width: 1280px) {
			gap: 20px;
			grid-template-columns: repeat(2, 1fr);
		}

		@media (max-width: 480px) {
			grid-template-columns: repeat(1, 1fr);
		}

		&-title {
			font-weight: $font-bold;
			color: $black2;
			margin-bottom: 12px;
		}

		&-percent {
			font-weight: $font-bold;
			font-size: 32px;
			line-height: 40px;
			line-height: 100%;
			margin-bottom: 4px;
		}

		&-goal {
			color: $placeholder-color;
			margin-bottom: 4px;
		}

		.bar {
			max-width: 123px;

			@media (max-width: 782px) {
				width: 100%;
			}
		}
	}

	.bar {
		position: relative;
		height: 5px;
		width: 100%;
		background: $gray;
		border-radius: 50px;
		overflow: hidden;
		margin-bottom: 12px;

		&-progress {
			position: absolute;
			top: 0;
			left: 0;
			height: 100%;
			min-width: 3px;

			&.green {
				background-color: $green;
			}
			&.blue {
				background-color: $blue;
			}
			&.orange {
				background-color: $orange2;
			}
			&.red {
				background-color: $red;
			}
		}
	}

	.keywords {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;

		&-item {
			font-size: 12px;
			line-height: 18px;
			color: #434960;
			font-weight: 600;
			background: #F3F4F5;
			padding: 0 8px;
			border-radius: 3px;
			display: inline-block;
		}
	}

	.aioseo-tooltip {
		display: inline-block;
		margin: 0 0 0 5px;
		vertical-align: middle;
	}
	.green {
		color: $green;
		fill: $green;
	}
	.blue {
		color: $blue;
		fill: $blue;
	}
	.orange {
		color: $orange2;
		fill: $orange2;
	}
	.red {
		color: $red;
		fill: $red;
	}
}
</style>