<template>
	<div>
		<template v-if="postTitle">
			<div class="aioseo-inline-buttons">
				<button
					@click="switchTab('current-score')"
					class='aioseo-switcher-button'
					:class="{ active : activeTab === 'current-score' }"
				>
					{{ currentScore }}
				</button>
				<button
					@click="switchTab('new-headline')"
					class='aioseo-switcher-button'
					:class="{ active : activeTab === 'new-headline' }"
				>
					{{ currentHeadline }}
				</button>
			</div>

			<!-- Tabs -->
			<tab-current-score v-show="activeTab === 'current-score'"/>
			<tab-new-score v-show="activeTab === 'new-headline'" />

			<!-- Main Panels/Accordions -->
			<panel-new-score v-if="postEditorStore?.newHeadlineAnaylzerData?.newResult" />
			<previous-scores v-if="postEditorStore.currentPost?.headlineAnalyzer?.previousHeadlines?.length > 1 || postEditorStore.currentPost?.headlineAnalyzer?.showPrevScores" />
			<word-balance />
			<sentiment />
			<panel-type />
			<character-count />
			<word-count />
			<start-end-words />
			<search-preview />
		</template>

		<p v-else class="aioseo-headline-analyzer-empty-title-warning">
			{{ emptyTitleWarning }}
		</p>

		<!-- Notice -->
		<div class="aioseo-headline-analyzer-bottom-notice">
			<p v-html="headlineAnalyzerNotice"></p>
		</div>
	</div>
</template>

<script>
import TabNewScore from './TabNewScore'
import PanelNewScore from './PanelNewScore'
import TabCurrentScore from './TabCurrentScore'
import PreviousScores from './PreviousScores'
import WordBalance from './WordBalance'
import Sentiment from './Sentiment'
import PanelType from './PanelType'
import CharacterCount from './CharacterCount'
import WordCount from './WordCount'
import StartEndWords from './StartEndWords'
import SearchPreview from './SearchPreview'

import { usePostEditorStore, useRootStore } from '@/vue/stores'
import { decodeHtml } from '../assets/js/functions'
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		TabCurrentScore,
		TabNewScore,
		PanelNewScore,
		PreviousScores,
		WordBalance,
		Sentiment,
		PanelType,
		CharacterCount,
		WordCount,
		StartEndWords,
		SearchPreview
	},
	data () {
		return {
			currentScore      : __('Current Score', td),
			currentHeadline   : __('Try New Headline', td),
			emptyTitleWarning : __('Write your post title to see the analyzer data. This Headline Analyzer tool enables you to write irresistible SEO headlines that drive traffic, shares, and rank better in search results.', td),
			activeTab         : 'current-score',
			postEditorStore   : usePostEditorStore(),
			rootStore         : useRootStore()
		}
	},
	computed : {
		postTitle () {
			return decodeHtml(this.postEditorStore.currentPost?.headlineAnalyzer?.headline || '')
		},
		headlineAnalyzerNotice () {
			return sprintf(
				// Translators: 1 - The short plugin name ("AIOSEO"), 2 - Opening HTML link/span tag, 3 - Closing HTML span tag, 4 - Closing HTML link tag.
				__('This Headline Analyzer is part of %1$s to help you increase your traffic. %2$sAnalyze your site further here%3$s →%4$s', td),
				import.meta.env.VITE_SHORT_NAME,
				sprintf(
					'<a href="%1$s" class="aioseo-headline-analyzer-link" target="_blank"><span>',
					this.rootStore.aioseo.urls.aio.seoAnalysis
				),
				'</span>',
				'</a>'
			)
		}
	},
	methods : {
		switchTab (tabName) {
			this.activeTab = tabName
		}
	}
}
</script>