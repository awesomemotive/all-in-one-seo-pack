<template>
	<core-modal
		:show="show"
		:classes="[
			'aioseo-ai-content-feature-modal',
			'aioseo-ai-content-key-points-modal'
		]"
		@close="$emit('closeModal', true)"
	>
		<template #header>
			<div class="header-left">
				<svg-arrow-back
					v-if="currentScreen === 'results'"
					@click="currentScreen = 'settings'"
				/>

				<component
					:is="`svg-${feature.svg}`"
					class="aioseo-ai-content-feature-modal-icon"
				/>

				<span>{{ feature.strings.name }}</span>
			</div>

			<div class="header-right">
				<button
					class="close"
					type="button"
					@click.stop="$emit('closeModal', true)"
				>
					<svg-close @click="$emit('closeModal', true)" />
				</button>
			</div>
		</template>

		<template #body>
			<div class="aioseo-modal-body aioseo-ai-content-feature-modal-body">
				<div class="aioseo-ai-content-feature-modal-body-main">
					<template v-if="currentScreen === 'settings'">
						<div class="settings">
							<div class="settings-left">
								<step-header
									style="margin-left: -40px;"
									:header="strings.settingsHeader"
								/>

								<style-form optionsKey="keyPoints" />
							</div>
						</div>
					</template>

					<template v-if="currentScreen === 'loading'">
						<loader :loaders="loaders" />
					</template>

					<template v-if="currentScreen === 'results'">
						<div class="results">
							<div
								v-for="keyPoint in postEditorStore.currentPost.ai.keyPoints"
								:key="keyPoint.id"
								class="key-point"
							>
								<base-checkbox
									size="medium"
									@update:modelValue="checked => updateSelectedKeyPoints(checked, keyPoint)"
								>
									<div class="key-point-data">
										<span class="title">{{ keyPoint.title }}:</span>
										<span class="explanation">{{ keyPoint.explanation }}</span>
									</div>
								</base-checkbox>
							</div>
						</div>
					</template>
				</div>
			</div>
		</template>

		<template #footer>
			<div class="footer-left">
				<base-button
					v-if="currentScreen === 'results'"
					class="rephrase-button"
					size="small"
					type="gray"
					@click="event => generate(true)"
					:disabled="!aiContent.hasEnoughCredits(5)"
				>
					<svg-rephrase />

					{{ strings.rephrase }}
				</base-button>

				<credit-counter parent-component-context="modal" />
			</div>

			<div class="footer-right">
				<base-button
					v-if="currentScreen === 'settings' && 0 < postEditorStore.currentPost.ai.keyPoints.length"
					class="view-button"
					size="small"
					type="gray"
					@click="event => currentScreen = 'results'"
				>
					<span>{{ strings.viewPreviousResults }}</span>
				</base-button>

				<base-button
					v-if="currentScreen === 'settings'"
					class="generate-button"
					size="small"
					type="blue"
					@click="_event => generate(false)"
					:disabled="!aiContent.hasEnoughCredits(aiContent.getFeatureCost('keyPoints'))"
				>
					{{ strings.generateButtonText }}
				</base-button>

				<base-button
					v-if="currentScreen === 'results'"
					class="copy-button"
					size="small"
					type="gray"
					v-clipboard:copy="doCopyContent"
					v-clipboard:success="onCopy"
				>
					<svg-copy
						v-if="!justCopied"
					/>

					<svg-circle-check-solid
						v-if="justCopied"
					/>

					<span>{{ copyText }}</span>
				</base-button>

				<base-button
					v-if="currentScreen === 'results' && isBlockEditor()"
					size="small"
					type="blue"
					class="block-button"
					@click="insertBlock"
					:disabled="!selected.length"
				>
					{{ insertBlockButtonText }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script>
import { computed, ref, onUpdated, watch } from 'vue'

import { useAiContent } from '@/vue/composables/AiContent'
import {
	useAiStore,
	usePostEditorStore
} from '@/vue/stores'

import { isBlockEditor } from '@/vue/utils/context'
import { copyContent } from './utils'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'

import Loader from './Loader'
import StepHeader from './StepHeader.vue'
import StyleForm from './StyleForm.vue'

import SvgClose from '@/vue/components/common/svg/Close'
import SvgCopy from '@/vue/components/common/svg/ai/Copy'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgKeyPoints from '@/vue/components/common/svg/ai/KeyPoints'
import SvgRephrase from '@/vue/components/common/svg/ai/Rephrase'
import SvgArrowBack from '@/vue/components/common/svg/ArrowBack'

import { __, _n, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'closeModal' ],
	setup (props, { emit }) {
		const aiContent       = useAiContent()
		const aiStore         = useAiStore()
		const postEditorStore = usePostEditorStore()
		const currentScreen   = ref(postEditorStore.currentPost.ai.keyPoints.length ? 'results' : 'settings')
		const error           = ref(false)
		const justCopied      = ref(false)
		const selected        = ref([])

		const generate = (rephrase = false) => {
			error.value         = false
			currentScreen.value = 'loading'
			selected.value      = []

			aiStore.generateKeyPoints(rephrase).then(() => {
				currentScreen.value = 'results'
			}).catch(() => {
				currentScreen.value = 'settings'
				error.value         = true
			})
		}

		const copyText = computed(() => {
			return !justCopied.value ? strings.copy : strings.copied
		})

		const doCopyContent = computed(() => {
			const keyPointsToCopy = selected.value.length ? selected.value : postEditorStore.currentPost.ai.keyPoints
			let content = ''

			if (isBlockEditor()) {
				content = keyPointsToCopy
					.map(keyPoint => `• **${keyPoint.title}:** ${keyPoint.explanation}`)
					.join('\n')
			} else {
				const html = [ '<ul>' ]
				keyPointsToCopy.forEach(keyPoint => {
					html.push(`<li><strong>${keyPoint.title}:</strong> ${keyPoint.explanation}</li>`)
				})
				html.push('</ul>')
				content = html.join('\n')
			}

			return content
		})

		const onCopy = (value) => {
			justCopied.value = true

			copyContent(value?.text)

			setTimeout(() => {
				justCopied.value = false
			}, 2000)
		}

		const updateSelectedKeyPoints = (checked, keyPoint) => {
			if (checked) {
				if (!selected.value.includes(keyPoint)) {
					selected.value.push(keyPoint)
				}
			} else {
				selected.value = selected.value.filter(item => item.title !== keyPoint.title)
			}
		}

		const insertBlockButtonText = computed(() => {
			if (!selected.value.length) {
				return __('Insert Key Points Block', td)
			}

			return _n(
				'Insert Key Points Block',
				'Insert Key Points Blocks',
				selected.value.length,
				td
			)
		})

		const insertListItemBlock = (insertionPoint) => {
			const items = []
			selected.value.forEach(keyPoint => {
				const { explanation, title } = keyPoint
				const listItem = window.wp.blocks.createBlock('core/list-item', {
					content : `<strong>${title}:</strong> ${explanation}`
				})

				items.push(listItem)
			})

			setTimeout(() => {
				window.wp.data.dispatch('core/block-editor').insertBlocks(
					items,
					insertionPoint.index,
					insertionPoint.rootClientId
				)
			}, 100)
		}

		const insertListBlock = (insertionPoint) => {
			const items = []
			selected.value.forEach(keyPoint => {
				const { explanation, title } = keyPoint
				items.push(`<li><strong>${title}:</strong> ${explanation}</li>`)
			})

			const listBlock = window.wp.blocks.createBlock('core/list', {
				type   : 'ul',
				values : items.join('')
			})

			const keyPointsBlock = window.wp.blocks.createBlock('aioseo/key-points', {}, [ listBlock ])

			setTimeout(() => {
				// If we have a rootClientId(selected block), insert the block at the end of the list
				// because we can't insert the block inside the current block
				if (insertionPoint?.rootClientId) {
					const allBlocks = window.wp.data.select('core/block-editor').getBlocks()
					window.wp.data.dispatch('core/block-editor').insertBlock(keyPointsBlock, allBlocks.length)
				} else {
					// Otherwise, insert the block at the current insertion point
					window.wp.data.dispatch('core/block-editor').insertBlock(
						keyPointsBlock,
						insertionPoint.index,
						insertionPoint.rootClientId
					)
				}
			}, 100)
		}

		const insertBlock = () => {
			if (!selected.value.length) {
				return
			}

			const insertionPoint = window.wp.data.select('core/block-editor').getBlockInsertionPoint()
			const selectedBlock = insertionPoint?.rootClientId ? window.wp.data.select('core/block-editor').getBlock(insertionPoint.rootClientId) : null

			// If the selected block is a list, insert a list item
			if (selectedBlock && 'core/list' === selectedBlock.name) {
				insertListItemBlock(insertionPoint)
			} else {
				// Otherwise, insert a new list block
				insertListBlock(insertionPoint)
			}

			setTimeout(() => {
				emit('closeModal')
			}, 100)
		}

		const strings = {
			copy               : __('Copy', td),
			copied             : __('Copied!', td),
			settingsHeader     : __('Select tone and audience', td),
			generateButtonText : sprintf(
				// Translators: 1 - Number of credits.
				__('Generate Key Points (%1$d credits)', td),
				aiContent.getFeatureCost('keyPoints')
			),
			rephrase            : __('Regenerate (5 credits)', td),
			viewPreviousResults : __('View Previous Results', td)
		}

		const loaders = [
			{
				slug  : 'key-points',
				label : __('Key Points', td),
				icon  : 'key-points',
				name  : __('Key Points', td)
			}
		]

		watch(currentScreen, (value, oldValue) => {
			if ('results' === value && 'results' !== oldValue) {
				selected.value = []
			}
		})

		onUpdated(() => {
			selected.value = []
		})

		return {
			aiContent,
			aiStore,
			postEditorStore,
			currentScreen,
			error,
			generate,
			copyText,
			doCopyContent,
			justCopied,
			onCopy,
			updateSelectedKeyPoints,
			selected,
			insertBlockButtonText,
			insertBlock,
			strings,
			loaders,
			isBlockEditor
		}
	},
	components : {
		BaseCheckbox,
		CoreModal,
		CreditCounter,
		Loader,
		StepHeader,
		StyleForm,
		SvgClose,
		SvgCopy,
		SvgCircleCheckSolid,
		SvgKeyPoints,
		SvgRephrase,
		SvgArrowBack
	},
	props : {
		feature : {
			type     : Object,
			required : true
		},
		show : {
			type : Boolean,
			default () {
				return false
			}
		}
	}
}
</script>

<style lang="scss">
.results {
	.aioseo-checkbox {
		align-items: flex-start;
	}

	.key-point {
		margin-bottom: 20px;

		.aioseo-checkbox {
			.form-checkbox-wrapper {
				margin-top: -2px;
			}
		}

		.key-point-data {
			margin-top: -5px;
			margin-left: 10px;
			cursor: pointer;

			.title {
				font-weight: 600;
			}

			.explanation {
				margin-left: 10px;
			}
		}
	}
}
</style>