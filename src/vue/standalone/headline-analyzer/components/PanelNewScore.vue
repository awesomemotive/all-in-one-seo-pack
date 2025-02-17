<template>
	<accordion
		:title="textPanelTitle"
		componentClass="aioseo-headline-analyzer-panel-tab-new-score"
	>
		<div class="aioseo-headline-analyzer-panel-first-block">
			<div class="aioseo-headline-analyzer-new-score-panel">
				<p>{{ veryGoodScore }} {{ forBetterResults }}</p>
				<h4>“{{ decodeHtml(newTitle) }}”</h4>
				<div class="aioseo-headline-analyzer-pie-chart-container">
					<span class="aioseo-headline-analyzer-new-score" :class="classOnNewScore">
						{{ newScore }}
					</span>
					<pie-chart
						:color="barColor"
						:currentScore="newScore">
					</pie-chart>
					<span class="aioseo-headline-analyzer-score-difference" :class="classOnNewScore" v-if="0 !== scoreDifference">
						{{ newScore > currentScore ? '+ ' : newScore === currentScore ? '' : '- ' }}
						{{ scoreDifference }}
					</span>
					<div v-if="statusOnScore" class="aioseo-headline-analyzer-score-status" :class="classOnNewScore">
						<span>{{ statusOnScore }}</span>
					</div>
				</div>
				<button type="button" @click="applyHeadline" class="components-button aioseo-headline-analyzer-button">{{ applyHeadlineText }}</button>
				<div class="current-score">
					<span class="aioseo-headline-analyzer-score" :class="classOnCurrentScoreBg">
						{{ currentScore }}
					</span>
					<div class="aioseo-headline-analyzer-current-score-content">
						<h5>{{ textCurrentScore }}</h5>
						<p>{{ postTitle }}</p>
					</div>
				</div>
			</div>
		</div>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import PieChart from './partials/PieChart'

import { usePostEditorStore } from '@/vue/stores'
import { decodeHtml } from '../assets/js/functions'
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const { dispatch } = window.wp.data

export default {
	components : {
		Accordion,
		PieChart
	},
	data () {
		return {
			veryGoodScore : sprintf(
				// Translators: 1 - Initial score range, 2 - Final score range.
				__('A very good score is between %1$d and %2$d.', td),
				70,
				100
			),
			forBetterResults : sprintf(
				// Translators: 1 - Score.
				__('For best results, you should strive for %1$d and above.', td),
				70
			),
			textPanelTitle    : __('New Score', td),
			textCurrentScore  : __('Current Score', td),
			applyHeadlineText : __('Apply Headline', td),
			postEditorStore   : usePostEditorStore()
		}
	},
	computed : {
		postTitle () {
			return decodeHtml(this.postEditorStore.currentPost?.headlineAnalyzer?.headline || '')
		},
		currentResult () {
			const currentResult = this.postEditorStore.currentPost.headlineAnalyzer.data[Object.keys(this.postEditorStore.currentPost.headlineAnalyzer.data)?.[0]] || null
			return currentResult ? JSON.parse(currentResult) : {}
		},
		currentScore () {
			return this.currentResult.score
		},
		newResult () {
			return this.postEditorStore.newHeadlineAnaylzerData.newResult
		},
		newTitle () {
			return decodeHtml(this.postEditorStore.newHeadlineAnaylzerData.newTitle || '')
		},
		newScore () {
			return this.newResult.score
		},
		classOnNewScore () {
			return 40 > this.newScore ? 'red' : 70 > this.newScore ? 'orange' : 'green'
		},
		barColor () {
			return 'red' === this.classOnNewScore ? '#df2a4a' : 'orange' === this.classOnNewScore ? '#F2994A' : '#00aa63'
		},
		classOnCurrentScoreBg () {
			return 40 > this.currentScore
				? 'red-bg'
				: 70 > this.currentScore
					? 'orange-bg'
					: 'green-bg'
		},
		scoreDifference () {
			return Math.abs(this.newScore - this.currentScore)
		},
		statusOnScore () {
			if (25 > this.newScore) {
				return __('Not Looking Great', td)
			}
			if (50 > this.newScore) {
				return __('Could Be Better', td)
			}
			if (60 > this.newScore) {
				return __('Getting There', td)
			}
			if (75 > this.newScore) {
				return __('Looks Good! 👍👍', td)
			}
			if (75 <= this.newScore) {
				return __('Super! 🔥🔥🔥', td)
			}
			return false
		}
	},
	methods : {
		applyHeadline () {
			dispatch('core/editor').editPost({ title: this.newTitle })
		},
		decodeHtml
	}
}
</script>