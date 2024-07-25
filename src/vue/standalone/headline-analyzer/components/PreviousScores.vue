<template>
	<accordion
		:title="previousScoresText"
		componentClass="aioseo-headline-analyzer-panel-previous-scores"
	>
		<div class="aioseo-headline-analyzer-panel-first-block">
			<ul v-if="previousScores.length > 0" class='aioseo-headline-analyzer-previous-scores'>
				<li v-for="(item, index) in previousScores" :key="index" @click.stop="fetchPrevScoreHeadline(item.headline)">
					<span class="aioseo-headline-analyzer-score" :class="classOnScore(item.score)">{{ item.score || 'N/A' }}</span>
					<span class="aioseo-headline-analyzer-score-text">{{ decodeHtml(item.headline) }}</span>
				</li>
			</ul>
		</div>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { fetchData } from '../assets/js/initAnalyzerData'
import { usePostEditorStore } from '@/vue/stores'

import { decodeHtml } from '../assets/js/functions'

export default {
	data () {
		return {
			previousScoresText : this.$t.__('Previous Scores', this.$td),
			postEditorStore    : usePostEditorStore()
		}
	},
	components : {
		Accordion
	},
	methods : {
		classOnScore (score) {
			if (!score) {
				return 'gray-bg'
			}

			return 40 > score
				? 'red-bg'
				: 70 > score
					? 'orange-bg'
					: 'green-bg'
		},
		async fetchPrevScoreHeadline (headline) {
			let newHeadlineData = null

			// Check if data already exists (memoization).
			newHeadlineData = this.postEditorStore.currentPost.headlineAnalyzer.previousHeadlines.find(item => item.headline === headline)

			if (!newHeadlineData) {
				// Fetch new headline data.
				const fetchedData = await fetchData(headline)

				if (fetchedData) {
					// Save new data to store.
					this.postEditorStore.updateNewHeadlineAnalyzerData(fetchedData.data, fetchedData.headline)
					// Show new data.
					this.postEditorStore.toggleShowNewHeadlineAnalyzerData(true)
				}
			} else {
				const result = {
					data : {
						[newHeadlineData.headline] : JSON.stringify(newHeadlineData.result)
					},
					headline : newHeadlineData.headline
				}

				// Save new data to store.
				this.postEditorStore.updateNewHeadlineAnalyzerData(result.data, result.headline)
				// Show new data.
				this.postEditorStore.toggleShowNewHeadlineAnalyzerData(true)
			}
		},
		decodeHtml (html) {
			return decodeHtml(html)
		}
	},
	computed : {
		postTitle () {
			return this.postEditorStore.currentPost?.headlineAnalyzer?.headline ? this.postEditorStore.currentPost.headlineAnalyzer.headline : ''
		},
		previousScores () {
			const headlines = JSON.parse(JSON.stringify(this.postEditorStore?.currentPost?.headlineAnalyzer?.previousHeadlines || []))
			headlines.pop()
			return headlines.reverse()
		}
	}
}
</script>