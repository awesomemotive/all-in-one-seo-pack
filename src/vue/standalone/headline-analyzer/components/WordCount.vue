<template>
	<accordion
		:title="textWordCount"
		:componentClass="'aioseo-headline-analyzer-panel-word-count'"
		:hasIcon="true"
		:iconColor="classOnLength"
	>
		<div class="aioseo-headline-analyzer-words-block">
			<div class="aioseo-headline-analyzer-word-counter">
				<span class="aioseo-headline-analyzer-status-on-word-length">
					{{ statusOnLength }}
				</span>
				<span
					class="aioseo-headline-analyzer-word-length"
					:class="classOnLength"
					v-html="wordCountDisplay"
				/>
			</div>
			<p>{{ descOnWordLength }}</p>
		</div>

		<!-- Icons Slot -->
		<template v-slot:icon>
			<template v-if="'green' == classOnLength">
				<svg
					width="20"
					height="20"
					viewBox="0 0 20 20"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					class="components-panel__icon"
					aria-hidden="true"
					focusable="false">
					<path
						fill-rule="evenodd"
						clip-rule="evenodd"
						d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18ZM8 12.17L14.59 5.58L16 7L8 15L4 11L5.41 9.59L8 12.17Z"
						fill="#00AA63">
					</path>
				</svg>
			</template>
			<template v-else>
				<svg
					width="20"
					height="20"
					viewBox="0 0 20 20"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
					class="components-panel__icon"
					aria-hidden="true"
					focusable="false">
					<path
						fill-rule="evenodd"
						clip-rule="evenodd"
						d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM11 5.00002H9V11H11V5.00002ZM11 13H9V15H11V13ZM2.00002 10C2.00002 14.42 5.58002 18 10 18C14.42 18 18 14.42 18 10C18 5.58002 14.42 2.00002 10 2.00002C5.58002 2.00002 2.00002 5.58002 2.00002 10Z"
						fill="#005AE0">
					</path>
				</svg>
			</template>
		</template>
	</accordion>
</template>

<script>
import Accordion from './partials/Accordion'
import { usePostEditorStore } from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	components : {
		Accordion
	},
	data () {
		return {
			textWordCount   : __('Word Count', td),
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
		currentScore () {
			return this.currentResult?.score ? this.currentResult.score : 0
		},
		wordLength () {
			return this.currentResult.result?.wordCount ? this.currentResult.result.wordCount : 0
		},
		wordLengthString () {
			return this.wordLength ? this.wordLength.toString() : ''
		},
		classOnLength () {
			let classOnLength = ''

			if (4 >= this.wordLength) {
				classOnLength = 'red'
			} else if (5 <= this.wordLength && 9 >= this.wordLength) {
				classOnLength = 'green'
			} else if (10 <= this.wordLength && 11 >= this.wordLength) {
				classOnLength = 'orange'
			} else {
				classOnLength = 'red'
			}

			return classOnLength
		},
		statusOnLength () {
			let statusOnLength = ''

			if (4 >= this.wordLength) {
				statusOnLength = __('Not Enough Words 🙃', td)
			} else if (5 <= this.wordLength && 9 >= this.wordLength) {
				statusOnLength = __('Good 🙂', td)
			} else if (10 <= this.wordLength && 11 >= this.wordLength) {
				statusOnLength = __('Reduce Word Count 🙂', td)
			} else {
				statusOnLength = __('Too Many Words 😑', td)
			}

			return statusOnLength
		},
		descOnWordLength () {
			let descOnWordLength = ''

			if (4 >= this.wordLength) {
				descOnWordLength = __('Your headline doesn’t use enough words. You have more space to add keywords and power words to improve your SEO and get more engagement.', td)
			} else if (5 <= this.wordLength && 9 >= this.wordLength) {
				descOnWordLength = __('Your headline has the right amount of words. Headlines are more likely to be clicked on in search results if they have about 6 words.', td)
			} else {
				descOnWordLength = __('Your headline has too many words. Long headlines will get cut off in search results and won’t get as many clicks.', td)
			}

			return descOnWordLength
		},
		wordCountDisplay () {
			let wordCountDisplay = ''

			if (1 === this.wordLengthString.length) {
				wordCountDisplay = `<span class="character-zero">0</span><span class="character-zero">0</span><span>${this.wordLength}</span>`
			}

			if (2 === this.wordLengthString.length) {
				wordCountDisplay = '<span class="character-zero">0</span>'
				for (const char of this.wordLengthString) {
					wordCountDisplay += `<span>${char}</span>`
				}
			}

			if (3 === this.wordLengthString.length) {
				for (const char of this.wordLengthString) {
					wordCountDisplay += `<span>${char}</span>`
				}
			}

			return wordCountDisplay
		}
	}
}
</script>