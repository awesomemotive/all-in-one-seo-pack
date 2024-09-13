<template>
	<accordion
		:title="textPanelTitle"
		:componentClass="'aioseo-headline-analyzer-panel-character-count'"
		:hasIcon="true"
		:iconColor="classOnLength"
	>
		<div class="aioseo-headline-analyzer-words-block">
			<div class="aioseo-headline-analyzer-character-count-container">
				<span class="aioseo-headline-analyzer-status-on-character-length">
					{{ statusOnLength }}
				</span>
				<span
					class="aioseo-headline-analyzer-character-length"
					:class="classOnLength"
					v-html="characterDisplay"
				/>
			</div>
			<p>{{ descOnCharLength }}</p>
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
					focusable="false"
				>
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
					focusable="false"
				>
					<path
						fill-rule="evenodd"
						clip-rule="evenodd"
						d="M9.99 0C4.47 0 0 4.48 0 10C0 15.52 4.47 20 9.99 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 9.99 0ZM11 5.00002H9V11H11V5.00002ZM11 13H9V15H11V13ZM2.00002 10C2.00002 14.42 5.58002 18 10 18C14.42 18 18 14.42 18 10C18 5.58002 14.42 2.00002 10 2.00002C5.58002 2.00002 2.00002 5.58002 2.00002 10Z"
						fill="#005AE0"
					>
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
			textPanelTitle  : __('Character Count', td),
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
		characterLength () {
			return this.currentResult.result?.length ? this.currentResult.result.length : 0
		},
		characterLengthString () {
			return this.characterLength ? this.characterLength.toString() : ''
		},
		classOnLength () {
			if (19 >= this.characterLength) {
				return 'red'
			}
			if (20 <= this.characterLength && 34 >= this.characterLength) {
				return 'orange'
			}
			if (35 <= this.characterLength && 66 >= this.characterLength) {
				return 'green'
			}
			if (67 <= this.characterLength && 79 >= this.characterLength) {
				return 'orange'
			}
			if (80 <= this.characterLength) {
				return 'red'
			}

			return 'red'
		},
		statusOnLength () {
			if (34 >= this.characterLength) {
				return __('Too Short 🙃', td)
			}
			if (35 <= this.characterLength && 66 >= this.characterLength) {
				return __('Good 🙂', td)
			}
			if (67 <= this.characterLength) {
				return __('Too Long 😑', td)
			}

			return __('Too Short 🙃', td)
		},
		descOnCharLength () {
			if (34 >= this.characterLength) {
				return __(
					'You have space to add more keywords and power words to boost your rankings and click-through rate.',
					td
				)
			}
			if (35 <= this.characterLength && 66 >= this.characterLength) {
				return __(
					'Headlines that are about 55 characters long will display fully in search results and tend to get more clicks.',
					td
				)
			}
			if (67 <= this.characterLength) {
				return __(
					'At this length, it will get cut off in search results. Try reducing it to about 55 characters.',
					td
				)
			}

			return __(
				'You have space to add more keywords and power words to boost your rankings and click-through rate.',
				td
			)
		},
		characterDisplay () {
			let charDisplay = ''

			if (1 === this.characterLengthString.length) {
				charDisplay = `<span class="character-zero">0</span><span class="character-zero">0</span><span>${this.characterLength}</span>`
			}

			if (2 === this.characterLengthString.length) {
				charDisplay = '<span class="character-zero">0</span>'
				for (const char of this.characterLengthString) {
					charDisplay += `<span>${char}</span>`
				}
			}

			if (3 === this.characterLengthString.length) {
				for (const char of this.characterLengthString) {
					charDisplay += `<span>${char}</span>`
				}
			}

			return charDisplay
		}
	}
}
</script>