<template>
	<core-modal
		:show="show"
		:classes="[
			'aioseo-ai-content-feature-modal',
			'aioseo-ai-content-image-generator-modal'
		]"
		@close="initCloseModal"
	>
		<template #headerTitle>
			<div class="header-left">
				<svg-arrow-back
					v-if="aiImageGeneratorStore.currentScreen === 'results' || !!aiImageGeneratorStore.images.selected.length"
					@click="aiImageGeneratorStore.switchScreen('generate')"
				/>

				<svg-image-generator class="aioseo-ai-content-feature-modal-icon" />

				{{ feature.strings.name }}
			</div>
		</template>

		<template #body>
			<div class="aioseo-modal-body aioseo-ai-content-feature-modal-body">
				<div class="aioseo-ai-content-feature-modal-body-main">
					<ai-image-generator />
				</div>
			</div>
		</template>

		<template #footer>
			<div class="footer-left">
				<credit-counter />
			</div>

			<div class="footer-right">
				<base-button
					v-if="buttonStates.previousResults.show"
					size="small"
					type="gray"
					@click="aiImageGeneratorStore.switchScreen('results')"
					:disabled="buttonStates.previousResults.disabled"
				>
					{{ strings.showPreviousResults }}
				</base-button>

				<base-button
					v-if="buttonStates.submit.show"
					size="small"
					type="blue"
					@click="generate"
					:disabled="buttonStates.submit.disabled"
					:loading="buttonStates.submit.loading"
				>
					{{ buttonStates.submit.text }}
				</base-button>

				<base-button
					v-if="buttonStates.insertIntoContent.show"
					size="small"
					type="blue"
					@click="insertIntoContent"
					:disabled="buttonStates.insertIntoContent.disabled"
				>
					{{ strings.insertImage }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { computed, nextTick } from 'vue'

import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import { useAiContent } from '@/vue/composables/AiContent'
import { isBlockEditor, isClassicEditor } from '@/vue/utils/context'
import { __, sprintf } from '@/vue/plugins/translations'

import AiImageGenerator from '@/vue/standalone/ai-image-generator/App'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import SvgArrowBack from '@/vue/components/common/svg/ArrowBack'
import SvgImageGenerator from '@/vue/components/common/svg/ai/ImageGenerator'

const aiImageGeneratorStore = useAiImageGeneratorStore()
const td = import.meta.env.VITE_TEXTDOMAIN

const {
	hasEnoughCredits,
	strings : aiContentStrings
} = useAiContent()

const strings = {
	showPreviousResults : __('Show Previous Results', td),
	insertImage         : __('Insert Image', td)
}

const emit = defineEmits([ 'closeModal' ])

const initCloseModal = () => {
	emit('closeModal')

	aiImageGeneratorStore.resetInitiator()
	aiImageGeneratorStore.switchScreen('generate')
}

defineProps({
	feature : Object,
	show    : {
		type : Boolean,
		default () {
			return false
		}
	}
})

const buttonStates = computed(() => {
	const submitDisabled = !hasEnoughCredits(aiImageGeneratorStore.generationPrice) || !aiImageGeneratorStore.formPrompt || aiImageGeneratorStore.formPrompt.length < aiImageGeneratorStore.form.prompt.minlength

	return {
		submit : {
			show     : 'generate' === aiImageGeneratorStore.currentScreen && !aiImageGeneratorStore.images.selected.length,
			disabled : submitDisabled,
			loading  : aiImageGeneratorStore.form.isGenerating,
			text     : sprintf(
				// Translators: 1 - Number of credits.
				__('Generate Image (%1$s credits)', td), aiImageGeneratorStore.generationPrice.toLocaleString()
			)
		},
		previousResults : {
			show     : 'results' !== aiImageGeneratorStore.currentScreen && !!aiImageGeneratorStore.images.count,
			disabled : aiImageGeneratorStore.form.isGenerating
		},
		insertIntoContent : {
			show     : (isClassicEditor() || isBlockEditor()) && ('results' === aiImageGeneratorStore.currentScreen || aiImageGeneratorStore.images.selected.length),
			disabled : !aiImageGeneratorStore.images.selected.length || aiImageGeneratorStore.form.isGenerating
		}
	}
})

const generate = async () => {
	if (buttonStates.value.submit.disabled || buttonStates.value.submit.loading) {
		return
	}

	try {
		aiImageGeneratorStore.errors.api = null

		await aiImageGeneratorStore.generateImage()
			.then(result => {
				aiImageGeneratorStore.selectImage(result.data)
			})

		await aiImageGeneratorStore.fetchImages()
	} catch (error) {
		console.error(error)

		aiImageGeneratorStore.errors.api = aiContentStrings.somethingWrong
	}
}

const insertIntoContent = async () => {
	const { wp } = window

	if (isBlockEditor()) {
		const attributes = {
			alt      : aiImageGeneratorStore.selectedImage.alt,
			url      : aiImageGeneratorStore.selectedImage.url,
			id       : aiImageGeneratorStore.selectedImage.id,
			sizeSlug : 'full'
		}

		if (
			'image-block-placeholder' === aiImageGeneratorStore.initiator.slug ||
			'image-block-toolbar' === aiImageGeneratorStore.initiator.slug
		) {
			const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock()

			wp.data.dispatch('core/block-editor').updateBlockAttributes(selectedBlock.clientId, attributes)
		}

		if ('featured-image-btn' === aiImageGeneratorStore.initiator.slug) {
			wp.data.dispatch('core/editor').editPost({ featured_media: aiImageGeneratorStore.selectedImage.id })
		}

		if (!aiImageGeneratorStore.initiator.slug) {
			const insertionPoint = wp.data.select('core/block-editor').getBlockInsertionPoint() || {}
			const imageBlock     = wp.blocks.createBlock('core/image', attributes)

			wp.data.dispatch('core/block-editor').insertBlock(
				imageBlock,
				insertionPoint.index,
				insertionPoint.rootClientId
			)
		}
	}

	if (isClassicEditor()) {
		const imageHtml = wp.media.string.image(
			{},
			{
				alt    : aiImageGeneratorStore.selectedImage.alt,
				height : aiImageGeneratorStore.selectedImage.height,
				id     : aiImageGeneratorStore.selectedImage.id,
				type   : 'image',
				url    : aiImageGeneratorStore.selectedImage.url,
				width  : aiImageGeneratorStore.selectedImage.width
			}
		)

		wp.media.editor.insert(imageHtml)
	}

	await nextTick()

	initCloseModal()
}
</script>