<template>
    <accordion
		:title="textPanelTitle"
		componentClass="aioseo-headline-analyzer-panel-tab-new-score-form"
	>
		<div class="aioseo-headline-analyzer-new-tab">
			<div class="aioseo-headline-analyzer-new-score-form-block">
				<form @submit.prevent="fetchNewHeadlineData">
					<div class="components-base-control aioseo-headline-analyzer-input-field css-qy3gpb ej5x27r4">
						<div class="components-base-control__field css-1t5ousf ej5x27r3">
							<label class="components-base-control__label css-1v57ksj ej5x27r2" for="inspector-text-control-0">
								{{ textNewHeadlineInputLabel }}
							</label>
							<input class="components-text-control__input" type="text" id="inspector-text-control-0" v-model="newHeadline" />
						</div>
					</div>
					<button type="submit" :disabled="!newHeadline" class="components-button aioseo-headline-analyzer-button">{{ textAnalyze }}</button>
				</form>
			</div>
		</div>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { fetchData } from '../assets/js/initAnalyzerData'

import { usePostEditorStore } from '@/vue/stores'

export default {
	data () {
		return {
			textPanelTitle            : this.$t.__('Try New Headline', this.$td),
			textNewHeadlineInputLabel : this.$t.__('Enter a different headline than your post title to see how it compares.', this.$td),
			textAnalyze               : this.$t.__('Analyze Headline', this.$td),
			newHeadline               : '',
			postEditorStore           : usePostEditorStore()
		}
	},
	components : {
		Accordion
	},
	methods : {
		async fetchNewHeadlineData () {
			let newHeadlineData = null

			// Check if data already exists (memoization).
			newHeadlineData = this.postEditorStore.currentPost.headlineAnalyzer.previousHeadlines.find(item => item.headline === this.newHeadline)

			if (!newHeadlineData) {
				// Fetch new headline data.
				const fetchedData = await fetchData(this.newHeadline)
				if (fetchedData) {
					// Save new data to store.
					this.postEditorStore.updateNewHeadlineAnalyzerData(fetchedData.data, fetchedData.headline)
					// Show new data.
					this.postEditorStore.toggleShowNewHeadlineAnalyzerData(true)
					// Reset the data.
					this.newHeadline = ''
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
				// Reset the data.
				this.newHeadline = ''
			}
		}
	}
}
</script>