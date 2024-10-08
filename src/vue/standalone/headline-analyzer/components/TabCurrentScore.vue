<template>
	<accordion
		:title="textScore"
		componentClass="aioseo-headline-analyzer-panel-score"
	>
		<div class="aioseo-headline-analyzer-current-score-tab aioseo-headline-analyzer-panel-first-block">
			<h4 class="aioseo-headline-analyzer-current-title">
				"{{ postTitle }}"
			</h4>
			<div class="aioseo-headline-analyzer-pie-chart-container">
				<div class='aioseo-headline-analyzer-current-score' :class="classOnScore">
					{{ currentScore }}<span class="aioseo-headline-analyzer-total-out-of-score">/ 100</span>
				</div>
				<div v-if="scoreStatus" class='aioseo-headline-analyzer-score-status' :class="classOnScore"><span>{{ scoreStatus }}</span></div>
				<pie-chart :color="barColor" :currentScore="currentScore"></pie-chart>
			</div>
			<p>{{ veryGoodScore }} {{ forBetterResults }}</p>
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
			textScore       : __('Score', td),
			postEditorStore : usePostEditorStore()
		}
	},
	computed : {
		postTitle () {
			return decodeHtml(this.postEditorStore.currentPost?.headlineAnalyzer?.headline || '')
		},
		currentResult () {
			const currentResult = this.postEditorStore.currentPost.headlineAnalyzer?.data[Object.keys(this.postEditorStore.currentPost.headlineAnalyzer.data)?.[0]] || null
			return currentResult ? JSON.parse(currentResult) : {}
		},
		currentScore () {
			return this.currentResult?.score ? this.currentResult.score : 0
		},
		classOnScore () {
			return 40 > this.currentScore ? 'red' : 70 > this.currentScore ? 'orange' : 'green'
		},
		barColor () {
			return 'red' === this.classOnScore
				? '#df2a4a'
				: 'orange' === this.classOnScore
					? '#F2994A'
					: '#00aa63'
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