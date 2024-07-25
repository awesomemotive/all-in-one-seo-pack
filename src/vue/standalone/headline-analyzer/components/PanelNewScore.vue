<template>
	<accordion
		:title="textPanelTitle"
		componentClass="aioseo-headline-analyzer-panel-tab-new-score"
	>
		<div class="aioseo-headline-analyzer-panel-first-block">
			<div class="aioseo-headline-analyzer-new-score-panel">
				<p>{{ veryGoodScore }} {{ forBetterResults }}</p>
				<h4>“{{ newTitle }}”</h4>
				<div class="aioseo-headline-analyzer-pie-chart-container">
					<span class="aioseo-headline-analyzer-new-score" :class="classOnNewScore">
						{{ newScore }}
					</span>
					<pie-chart
						:color="barColor"
						:currentScore="newScore">
					</pie-chart>
					<span class="aioseo-headline-analyzer-score-difference" :class="classOnNewScore">
						{{ newScore > currentScore ? '+ ' : newScore === currentScore ? '' : '- ' }}
						{{ scoreDifference }}
					</span>
					<div v-if="statusOnScore" class="aioseo-headline-analyzer-score-status" :class="classOnNewScore">
						<span>{{ statusOnScore }}</span>
					</div>
				</div>
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

const { sprintf } = window.wp.i18n
export default {
	data () {
		return {
			veryGoodScore : sprintf(
				// Translators: 1 - Initial score range, 2 - Final score range.
				this.$t.__('A very good score is between %1$d and %2$d.', this.$td),
				70,
				100
			),
			forBetterResults : sprintf(
				// Translators: 1 - Score.
				this.$t.__('For best results, you should strive for %1$d and above.', this.$td),
				70
			),
			textPanelTitle   : this.$t.__('New Score', this.$td),
			textCurrentScore : this.$t.__('Current Score', this.$td),
			postEditorStore  : usePostEditorStore()
		}
	},
	components : {
		Accordion,
		PieChart
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
			return this.newResult.sentence
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
				return this.$t.__('Not Looking Great', this.$td)
			}
			if (50 > this.newScore) {
				return this.$t.__('Could Be Better', this.$td)
			}
			if (60 > this.newScore) {
				return this.$t.__('Getting There', this.$td)
			}
			if (75 > this.newScore) {
				return this.$t.__('Looks Good! 👍👍', this.$td)
			}
			if (75 <= this.newScore) {
				return this.$t.__('Super! 🔥🔥🔥', this.$td)
			}
			return false
		}
	}
}
</script>