<template>
    <accordion
		:title="textPanelTitle"
		:componentClass="'aioseo-headline-analyzer-panel-sentiment'"
		:hasIcon="true"
		:iconColor="classOnSentiment"
	>
        <div class="aioseo-headline-analyzer-panel-first-block">
            <h4>{{ sentiment }}</h4>
            <p v-if="'neu' === this.currentResult.result?.sentiment">
                <strong>{{ textNeutralSentiment }}</strong><br />{{ textNeutralSentimentGuideline }}
            </p>
            <p v-if="'pos' === this.currentResult.result?.sentiment">
                <strong>{{ textPositiveSentiment }}</strong><br />{{ textPositiveSentimentGuideline }}
            </p>
            <p v-if="'neg' === this.currentResult.result?.sentiment">
                <strong>{{ textNegativeSentiment }}</strong><br />{{ textNegativeSentimentGuideline }}
            </p>
        </div>

        <!-- Icons Slot -->
        <template v-slot:icon>
            <template v-if="'neu' == currentResult.result?.sentiment">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="components-panel__icon" aria-hidden="true" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM5 7.5C5 6.67157 5.67157 6 6.5 6C7.32843 6 8 6.67157 8 7.5C8 8.32843 7.32843 9 6.5 9C5.67157 9 5 8.32843 5 7.5ZM13.5 6C12.6716 6 12 6.67157 12 7.5C12 8.32843 12.6716 9 13.5 9C14.3284 9 15 8.32843 15 7.5C15 6.67157 14.3284 6 13.5 6ZM7 13.5V12H13V13.5H7ZM2 10C2 14.42 5.58 18 10 18C14.42 18 18 14.42 18 10C18 5.58 14.42 2 10 2C5.58 2 2 5.58 2 10Z" fill="#005AE0"></path></svg>
            </template>
            <template v-else-if="'pos' == currentResult.result?.sentiment">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="components-panel__icon" aria-hidden="true" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM6.5 6C5.67157 6 5 6.67157 5 7.5C5 8.32843 5.67157 9 6.5 9C7.32843 9 8 8.32843 8 7.5C8 6.67157 7.32843 6 6.5 6ZM13.5 6C12.6716 6 12 6.67157 12 7.5C12 8.32843 12.6716 9 13.5 9C14.3284 9 15 8.32843 15 7.5C15 6.67157 14.3284 6 13.5 6ZM6.55 12C7.25 13.19 8.52 14 10 14C11.48 14 12.75 13.19 13.45 12H15.12C14.32 14.05 12.33 15.5 10 15.5C7.67 15.5 5.68 14.05 4.88 12H6.55ZM2 10C2 14.42 5.58 18 10 18C14.42 18 18 14.42 18 10C18 5.58 14.42 2 10 2C5.58 2 2 5.58 2 10Z" fill="#00AA63"></path></svg>
            </template>
            <template v-else>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="components-panel__icon" aria-hidden="true" focusable="false"><path fill-rule="evenodd" clip-rule="evenodd" d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM6.5 6C5.67157 6 5 6.67157 5 7.5C5 8.32843 5.67157 9 6.5 9C7.32843 9 8 8.32843 8 7.5C8 6.67157 7.32843 6 6.5 6ZM13.5 6C12.6716 6 12 6.67157 12 7.5C12 8.32843 12.6716 9 13.5 9C14.3284 9 15 8.32843 15 7.5C15 6.67157 14.3284 6 13.5 6ZM4.88 15.5C5.68 13.45 7.67 12 10 12C12.33 12 14.32 13.45 15.12 15.5H13.45C12.75 14.31 11.48 13.5 10 13.5C8.52 13.5 7.24 14.31 6.55 15.5H4.88ZM2 10C2 14.42 5.58 18 10 18C14.42 18 18 14.42 18 10C18 5.58 14.42 2 10 2C5.58 2 2 5.58 2 10Z" fill="#DF2A4A"></path></svg>
            </template>
        </template>

    </accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { usePostEditorStore } from '@/vue/stores'
export default {
	data () {
		return {
			textPanelTitle                 : this.$t.__('Sentiment', this.$td),
			textNeutralSentiment           : this.$t.__('Neutral Sentiment', this.$td),
			textNeutralSentimentGuideline  : this.$t.__('Headlines that are strongly positive or negative tend to get more engagement than neutral ones.', this.$td),
			textPositiveSentiment          : this.$t.__('Your headline has a positive sentiment.', this.$td),
			textPositiveSentimentGuideline : this.$t.__('Positive headlines tend to get better engagement than neutral or negative ones.', this.$td),
			textNegativeSentiment          : this.$t.__('Your headline has a negative sentiment.', this.$td),
			textNegativeSentimentGuideline : this.$t.__('Negative headlines are attention-grabbing and tend to perform better than neutral ones.', this.$td),
			postEditorStore                : usePostEditorStore()
		}
	},
	components : {
		Accordion
	},
	computed : {
		currentResult () {
			if (this.postEditorStore.currentPost.headlineAnalyzer?.showNewData) {
				return this.postEditorStore.newHeadlineAnaylzerData.newResult
			}
			const currentResult = this.postEditorStore.currentPost.headlineAnalyzer?.data[Object.keys(this.postEditorStore.currentPost.headlineAnalyzer.data)?.[0]] || null
			return currentResult ? JSON.parse(currentResult) : {}
		},
		sentiment () {
			return 'neu' === this.currentResult.result?.sentiment
				? this.$t.__('Neutral', this.$td)
				: 'pos' === this.currentResult.result?.sentiment
					? this.$t.__('Positive', this.$td)
					: this.$t.__('Negative', this.$td)
		},
		classOnSentiment () {
			return 'neu' === this.currentResult.result?.sentiment
				? 'orange'
				: 'pos' === this.currentResult.result?.sentiment
					? 'green'
					: 'red'
		}
	}
}
</script>