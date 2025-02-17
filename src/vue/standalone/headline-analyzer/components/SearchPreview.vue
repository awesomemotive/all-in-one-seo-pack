<template>
	<accordion
		:title="panelTitle"
		:componentClass="'aioseo-headline-analyzer-panel-search-preview'"
	>
		<div class="aioseo-headline-analyzer-panel-first-block">
			<div class="aioseo-headline-analyzer-search-prevew-wrap">
				<p class="aioseo-headline-analyzer-post-url">
					<a :href="encodedPostUrl" target="_blank">
						{{ postUrl }}
					</a>
				</p>
				<h4
					v-if="postEditorStore?.newHeadlineAnaylzerData?.newResult
						&& postEditorStore?.currentPost?.headlineAnalyzer?.newData.showPreview"
				>
					{{ newTitle }}
				</h4>
				<h4 v-else>
					{{ postTitle }}
				</h4>
				<p>{{ descText }}</p>
			</div>
		</div>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { usePostEditorStore } from '@/vue/stores'
import { decodeHtml } from '../assets/js/functions'

import { __ } from '@/vue/plugins/translations'

const { select } = window.wp.data
const td         = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Accordion
	},
	data () {
		return {
			panelTitle      : __('Search Preview', td),
			descText        : __('Here is how your headline will look like in Google search results page.', td),
			postUrl         : select('core/editor').getPermalink(),
			postEditorStore : usePostEditorStore()
		}
	},
	computed : {
		currentResult () {
			if (this.postEditorStore.currentPost.headlineAnalyzer?.showNewData) {
				return this.postEditorStore.newHeadlineAnaylzerData.newResult
			}
			const currentResult = this.postEditorStore.currentPost.headlineAnalyzer.data[Object.keys(this.postEditorStore.currentPost.headlineAnalyzer.data)?.[0]] || null
			return currentResult ? JSON.parse(currentResult) : {}
		},
		postTitle () {
			return decodeHtml(this.postEditorStore.currentPost?.headlineAnalyzer?.headline || '')
		},
		newResult () {
			return this.postEditorStore.newHeadlineAnaylzerData.newResult
		},
		newTitle () {
			return decodeHtml(this.postEditorStore.newHeadlineAnaylzerData.newTitle || '')
		},
		encodedPostUrl () {
			return this.postUrl ? encodeURI(this.postUrl) : '#'
		}
	}
}

</script>