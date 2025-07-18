<template>
	<accordion
		:title="title"
		:componentClass="'aioseo-headline-analyzer-panel-word-balance'"
		:hasIcon="true"
		:iconColor="classOnScore"
	>
		<div class="aioseo-headline-analyzer-words-block">
			<h4>{{ scoreStatus }}</h4>
			<p>{{ textGuideline }}</p>
		</div>
		<words-block
			:title="textCommonWords"
			:value="currentResult?.result?.commonWordsPercentage ? Math.round(currentResult.result.commonWordsPercentage * 100) : 0"
			:goalValue="textTwentyThirty"
			:classOnScore="classOnCommonWords"
			:classOnScoreBg="classOnCommonWordsBg"
			:words="currentResult?.result?.commonWords ? currentResult.result.commonWords : []"
			:guideLine="guideLineOnCommonWords"
		/>
		<words-block
			:title="textUnCommonWords"
			:value="currentResult?.result?.uncommonWordsPercentage ? Math.round(currentResult.result.uncommonWordsPercentage * 100) : 0"
			:goalValue="textTenTwenty"
			:classOnScore="classOnUnCommonWords"
			:classOnScoreBg="classOnUnCommonWordsBg"
			:words="currentResult?.result?.uncommonWords ? currentResult.result.uncommonWords : []"
			:guideLine="guideLineOnUnCommonWords"
		/>
		<words-block
			:title="textEmotionalWords"
			:value="currentResult?.result?.emotionalWordsPercentage ? Math.round(currentResult.result.emotionalWordsPercentage * 100) : 0"
			:goalValue="textTenFifteen"
			:classOnScore="classOnEmotionalWords"
			:classOnScoreBg="classOnEmotionalWordsBg"
			:words="currentResult?.result?.emotionWords ? currentResult.result.emotionWords : []"
			:guideLine="guideLineOnEmotionalWords"
		/>
		<words-block
			:title="textPowerWords"
			:value="currentResult?.result?.powerWordsPercentage ? Math.round(currentResult.result.powerWordsPercentage * 100) : 0"
			:goalValue="textLeastOne"
			:classOnScore="classOnPowerWords"
			:classOnScoreBg="classOnPowerWordsBg"
			:words="currentResult?.result?.powerWords ? currentResult.result.powerWords : []"
			:guideLine="guideLineOnPowerWords"
		/>

		<!-- Icons Slot -->
		<template v-slot:icon>
			<template v-if="'green' == classOnScore">
				<svg
					width="20"
					height="20"
					viewBox="0 0 20 20"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					class="components-panel__icon"
					aria-hidden="true"
					focusable="false">
					<path
						fill-rule="evenodd"
						clip-rule="evenodd"
						d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18ZM8 12.17L14.59 5.58L16 7L8 15L4 11L5.41 9.59L8 12.17Z"
						fill="#00AA63">
					</path>
				</svg>
			</template>
			<template v-else>
				<svg
					width="20"
					height="20"
					viewBox="0 0 20 20"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					class="components-panel__icon"
					aria-hidden="true"
					focusable="false">
					<path
						fill-rule="evenodd"
						clip-rule="evenodd"
						d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM11 5.00002H9V11H11V5.00002ZM11 13H9V15H11V13ZM2.00002 10C2.00002 14.42 5.58002 18 10 18C14.42 18 18 14.42 18 10C18 5.58002 14.42 2.00002 10 2.00002C5.58002 2.00002 2.00002 5.58002 2.00002 10Z"
						fill="#005AE0">
					</path>
				</svg>
			</template>
		</template>

	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import WordsBlock from './partials/WordsBlock'
import { usePostEditorStore } from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Accordion,
		WordsBlock
	},
	data () {
		return {
			title              : __('Word Balance', td),
			textGuideline      : __('Compare the percentages of your results to the goal for each category and adjust as necessary.', td),
			textCommonWords    : __('Common Words', td),
			textTwentyThirty   : __('20-30%', td),
			textUnCommonWords  : __('Uncommon Words', td),
			textTenTwenty      : __('10-20%', td),
			textEmotionalWords : __('Emotional Words', td),
			textTenFifteen     : __('10-15%', td),
			textPowerWords     : __('Power Words', td),
			textLeastOne       : __('At least one', td),
			postEditorStore    : usePostEditorStore()
		}
	},
	computed : {
		currentResult () {
			if (this.postEditorStore.currentPost.headlineAnalyzer?.showNewData) {
				return this.postEditorStore.newHeadlineAnaylzerData.newResult
			}
			const currentResult = this.postEditorStore.currentPost.headlineAnalyzer?.data[Object.keys(this.postEditorStore.currentPost.headlineAnalyzer.data)?.[0]] || null
			return currentResult ? JSON.parse(currentResult) : {}
		},
		currentScore () {
			return this.currentResult?.score ? this.currentResult.score : 0
		},
		classOnScore () {
			return 40 > this.currentScore ? 'red' : 70 > this.currentScore ? 'orange' : 'green'
		},
		classOnCommonWords () {
			return 0 === this.currentResult.result?.commonWordsPercentage
				? 'red'
				: 0.2 > this.currentResult.result?.commonWordsPercentage
					? 'orange'
					: 'green'
		},
		classOnCommonWordsBg () {
			return 0 === this.currentResult.result?.commonWordsPercentage
				? 'red-bg'
				: 0.2 > this.currentResult.result?.commonWordsPercentage
					? 'orange-bg'
					: 'green-bg'
		},
		guideLineOnCommonWords () {
			return 0.2 > this.currentResult.result?.commonWordsPercentage
				? __('Your headline would be more likely to get clicks if it had more common words.', td)
				: __('Headlines with 20-30% common words are more likely to get clicks.', td)
		},
		classOnUnCommonWords () {
			return 0 === this.currentResult.result?.uncommonWordsPercentage
				? 'red'
				: 0.1 > this.currentResult.result?.uncommonWordsPercentage
					? 'orange'
					: 'green'
		},
		classOnUnCommonWordsBg () {
			return 0 === this.currentResult.result?.uncommonWordsPercentage
				? 'red-bg'
				: 0.1 > this.currentResult.result?.uncommonWordsPercentage
					? 'orange-bg'
					: 'green-bg'
		},
		guideLineOnUnCommonWords () {
			return 0.1 > this.currentResult.result?.uncommonWordsPercentage
				? __('Your headline would be more likely to get clicks if it had more uncommon words.', td)
				: __('Headlines with uncommon words are more likely to get clicks.', td)
		},
		classOnEmotionalWords () {
			return 0 === this.currentResult.result?.emotionalWordsPercentage
				? 'red'
				: 0.1 > this.currentResult.result?.emotionalWordsPercentage
					? 'orange'
					: 'green'
		},
		classOnEmotionalWordsBg () {
			return 0 === this.currentResult.result?.emotionalWordsPercentage
				? 'red-bg'
				: 0.1 > this.currentResult.result?.emotionalWordsPercentage
					? 'orange-bg'
					: 'green-bg'
		},
		guideLineOnEmotionalWords () {
			return __('Emotionally triggered headlines are likely to drive more clicks.', td)
		},
		classOnPowerWords () {
			return 0 === this.currentResult.result?.powerWords.length ? 'orange' : 'green'
		},
		classOnPowerWordsBg () {
			return 0 === this.currentResult.result?.powerWords.length ? 'orange' : 'green-bg'
		},
		guideLineOnPowerWords () {
			return __('Headlines with power words are more likely to get clicks.', td)
		},
		scoreStatus () {
			if (25 > this.currentScore) {
				return __('Not Looking Great', td)
			}
			if (50 > this.currentScore) {
				return __('Could Be Better', td)
			}
			if (60 > this.currentScore) {
				return __('Getting There', td)
			}
			if (75 > this.currentScore) {
				return __('Looks Good! 👍👍', td)
			}
			if (75 <= this.currentScore) {
				return __('Super! 🔥🔥🔥', td)
			}
			return false
		}
	}
}
</script>