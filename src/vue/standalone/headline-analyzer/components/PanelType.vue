<template>
	<accordion
		:title="panelTitle"
		:componentClass="'aioseo-headline-analyzer-panel-types'"
		:hasExtraTxt="true"
	>
		<div class="aioseo-headline-analyzer-words-block">
			<h4>{{ headlineTypes }}</h4>
			<p v-html="typeLinkText"/>
		</div>

		<template v-slot:extraTxt>
			<span>{{ headlineTypes }}</span>
		</template>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { usePostEditorStore } from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Accordion
	},
	data () {
		return {
			panelTitle   : __('Headline Type', td),
			typeLinkText : sprintf(
				// Translators: 1 - HTML line break tag, 2 - Opening HTML link tag, 3 - Closing HTML link tag.
				__('Headlines that are lists and how-to get more engagement on average than other types of headlines. %1$s%2$sLearn More%3$s →', td),
				'<br /><br />',
				'<a href="https://optinmonster.com/why-these-21-headlines-went-viral-and-how-you-can-copy-their-success/" target="_blank" class="aioseo-headline-analyzer-link"><span>',
				'</span></a>'
			),
			postEditorStore : usePostEditorStore()
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
		headlineTypes () {
			return this.currentResult.result?.headlineTypes?.join(', ')
		}
	}
}
</script>