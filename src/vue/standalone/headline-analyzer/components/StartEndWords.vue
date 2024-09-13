<template>
	<accordion
		:title="textPanelTitle"
		:componentClass="'aioseo-headline-analyzer-panel-beginning-ending-words'"
	>
		<div class="aioseo-headline-analyzer-panel-first-block">
			<template v-if="beginningWords">
				<ul class="aioseo-headline-analyzer-word-begining-title">
					<li>{{ begginingWordsText }}</li>
				</ul>
				<div class="aioseo-headline-analyzer-words beginning">
					<span>{{ beginningWords }}</span>
				</div>
			</template>
			<template v-if="endingWords">
				<ul class="aioseo-headline-analyzer-word-begining-title">
					<li>{{ endingWordsText }}</li>
				</ul>
				<div class="aioseo-headline-analyzer-words beginning">
					<span>{{ endingWords }}</span>
				</div>
			</template>
			<p class="aioseo-headline-analyzer-words-guideline">
				{{ guideLineText }}
			</p>
		</div>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { usePostEditorStore } from '@/vue/stores'

import { decodeHtml } from '../assets/js/functions'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Accordion
	},
	data () {
		return {
			textPanelTitle     : __('Beginning & Ending Words', td),
			guideLineText      : __('Most readers only look at the first and last 3 words of a headline before deciding whether to click.', td),
			begginingWordsText : __('Beginning Words', td),
			endingWordsText    : __('Ending Words', td),
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
		words () {
			return this.currentResult.result?.originalExplodedHeadline ? this.currentResult.result.originalExplodedHeadline : []
		},
		beginningWords () {
			let beginningWords = ''

			if (6 <= this.words.length) {
				beginningWords = this.words.slice(0, 3).join(' ')
			} else if (3 < this.words.length && 5 >= this.words.length) {
				beginningWords = this.words.slice(0, 3).join(' ')
			} else {
				beginningWords = this.words.slice(0, 3).join(' ')
			}

			return decodeHtml(beginningWords)
		},
		endingWords () {
			let endingWords = ''

			if (6 <= this.words.length) {
				endingWords = this.words.slice(-3).join(' ')
			} else if (3 < this.words.length && 5 >= this.words.length) {
				endingWords = this.words.slice(3).join(' ')
			}

			return decodeHtml(endingWords)
		}
	}
}
</script>