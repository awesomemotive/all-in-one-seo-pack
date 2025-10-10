<template>
	<core-modal
		:show="show"
		:classes="[
			'aioseo-ai-content-feature-modal',
			'aioseo-ai-content-faq-modal'
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

								<style-form :optionsKey="'faqs'" />
							</div>
						</div>
					</template>

					<template v-if="currentScreen === 'loading'">
						<loader :loaders="loaders" />
					</template>

					<template v-if="currentScreen === 'results'">
						<div class="results">
							<div
								v-for="faq in postEditorStore.currentPost.ai.faqs"
								:key="faq.id"
								class="faq"
							>
								<base-checkbox
									size="medium"
									@update:modelValue="checked => updateSelectedFaqs(checked, faq)"
								>
									<div class="faq-data">
										<div class="question">{{ faq.question }}</div>
										<div class="answer">{{ faq.answer }}</div>
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

				<credit-counter />
			</div>

			<div class="footer-right">
				<base-button
					v-if="currentScreen === 'settings' && 0 < postEditorStore.currentPost.ai.faqs.length"
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
					@click="event => generate(false)"
					:disabled="!aiContent.hasEnoughCredits(10)"
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

import SvgArrowBack from '@/vue/components/common/svg/ArrowBack'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCopy from '@/vue/components/common/svg/ai/Copy'
import SvgCircleCheckSolid from '@/vue/components/common/svg/circle/CheckSolid'
import SvgFaq from '@/vue/components/common/svg/ai/Faq'
import SvgRephrase from '@/vue/components/common/svg/ai/Rephrase'

import { __, _n } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'closeModal' ],
	setup (props, { emit }) {
		const aiContent       = useAiContent()
		const aiStore         = useAiStore()
		const postEditorStore = usePostEditorStore()
		const currentScreen   = ref(postEditorStore?.currentPost?.ai?.faqs?.length ? 'results' : 'settings')
		const error           = ref(false)
		const justCopied      = ref(false)
		const selected        = ref([])

		const generate = (rephrase = false) => {
			error.value         = false
			currentScreen.value = 'loading'
			selected.value      = []

			aiStore.generateFaqs(rephrase).then(() => {
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
			const faqsToCopy = selected.value.length ? selected.value : postEditorStore.currentPost.ai.faqs
			let content = ''

			if (isBlockEditor()) {
				content = faqsToCopy
					.map(faq => `**${faq.question}**\n${faq.answer}`)
					.join('\n\n')
			} else {
				content = faqsToCopy
					.map(faq => `<h3><strong>${faq.question}</strong></h3>\n<p>${faq.answer}</p>`)
					.join('\n\n')
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

		const updateSelectedFaqs = (checked, faq) => {
			if (checked) {
				if (!selected.value.includes(faq)) {
					selected.value.push(faq)
				}
			} else {
				selected.value = selected.value.filter(item => item.question !== faq.question)
			}
		}

		const insertBlockButtonText = computed(() => {
			if (!selected.value.length) {
				return __('Insert FAQ Block', td)
			}

			return _n(
				'Insert FAQ Block',
				'Insert FAQ Blocks',
				selected.value.length,
				td
			)
		})

		const insertBlock = () => {
			if (!selected.value.length) {
				return
			}

			const insertionPoint = window.wp.data.select('core/block-editor').getBlockInsertionPoint()
			const faqs = Object.assign([], selected.value.reverse())

			faqs.forEach(faq => {
				const { question, answer } = faq
				const paragraph            = window.wp.blocks.createBlock('core/paragraph', {
					content : answer
				})

				const faqBlock = window.wp.blocks.createBlock('aioseo/faq', {
					question
				}, [ paragraph ])

				setTimeout(() => {
					const selectedBlock  = insertionPoint?.rootClientId ? window.wp.data.select('core/block-editor').getBlock(insertionPoint.rootClientId) : null

					// If the selected block is a faq, insert the faq block after the selected block
					if (selectedBlock && 'aioseo/faq' === selectedBlock.name) {
						const blockIndex = window.wp.data.select('core/block-editor').getBlockIndex(selectedBlock.clientId)
						window.wp.data.dispatch('core/block-editor').insertBlock(
							faqBlock,
							blockIndex + 1
						)
					} else {
						// If we have a rootClientId(selected block), insert the faq block at the end of the list
						// because we can't insert the block inside the current block
						if (selectedBlock) {
							const allBlocks = window.wp.data.select('core/block-editor').getBlocks()
							window.wp.data.dispatch('core/block-editor').insertBlock(faqBlock, allBlocks.length)
						} else {
							// Otherwise, insert the faq block at the current insertion point
							window.wp.data.dispatch('core/block-editor').insertBlock(
								faqBlock,
								insertionPoint.index,
								insertionPoint.rootClientId
							)
						}
					}
				}, 100)
			})

			setTimeout(() => {
				emit('closeModal')
			}, 100)
		}

		const strings = {
			copy                : __('Copy', td),
			copied              : __('Copied!', td),
			settingsHeader      : __('Select tone and audience', td),
			generateButtonText  : __('Generate FAQs (10 credits)', td),
			rephrase            : __('Regenerate (5 credits)', td),
			viewPreviousResults : __('View Previous Results', td)
		}

		const loaders = [
			{
				slug  : 'faq',
				label : __('FAQ', td),
				icon  : 'faq',
				name  : __('FAQ', td)
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
			updateSelectedFaqs,
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
		SvgArrowBack,
		SvgClose,
		SvgCopy,
		SvgCircleCheckSolid,
		SvgFaq,
		SvgRephrase
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

<style lang="scss" scoped>
.results {
	.aioseo-checkbox {
		align-items: flex-start;
	}

	.faq {
		margin-bottom: 20px;

		.aioseo-checkbox {
			.form-checkbox-wrapper {
				margin-top: -2px;
			}
		}

		.faq-data {
			display: flex;
			flex-direction: column;
			cursor: pointer;

			.question {
				color: $font-color;
				margin-top: -3px;
				font-weight: 600;
				font-size: 14px
			}

			.answer {
				color: $font-color;
				font-weight: 400;
				margin-top: 8px;
			}
		}
	}
}
</style>