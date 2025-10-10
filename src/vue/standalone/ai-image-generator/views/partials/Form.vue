<template>
	<div class="ai-image-generator__form">
		<template v-if="aiImageGeneratorStore.images.selected.length === 0">
			<ai-image-generator-form-while-generating
				v-if="aiImageGeneratorStore.form.isGenerating"
				class="ai-image-generator__group"
			/>

			<template v-else>
				<div class="ai-image-generator__group">
					<h3 class="ai-image-generator__title">
						{{ strings.imageOptions }}
					</h3>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.prompt.id"
						class="ai-image-generator__label"
					>
						{{ strings.prompt }}
					</label>

					<base-textarea
						v-model="aiImageGeneratorStore.form.prompt.value"
						:id="aiImageGeneratorStore.form.prompt.id"
						:rows="5"
						:max-height="200"
						:placeholder="strings.placeholder"
						:maxlength="aiImageGeneratorStore.form.prompt.maxlength"
					/>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.quality.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageQuality }}

						<core-tooltip placement="right">
							<svg-circle-question-mark
								width="17"
								height="17"
							/>

							<template #tooltip>
								{{ strings.qualityTooltip }}
							</template>
						</core-tooltip>
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.quality.value"
						:id="aiImageGeneratorStore.form.quality.id"
						:options="imageQualityOptions"
						size="medium"
					/>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.style.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageStyle }}
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.style.value"
						:id="aiImageGeneratorStore.form.style.id"
						:options="imageStyleOptions"
						size="medium"
					/>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.aspectRatio.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageAspectRatio }}
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.aspectRatio.value"
						:id="aiImageGeneratorStore.form.aspectRatio.id"
						:options="imageAspectRatioOptions"
						size="medium"
					/>
				</div>

				<core-alert
					class="ai-image-generator__group"
					type="blue"
					v-html="strings.manageDefaultSettings"
				/>
			</template>
		</template>

		<template v-else>
			<ai-image-generator-form-while-generating
				v-if="aiImageGeneratorStore.form.isGenerating"
				class="ai-image-generator__group"
			/>

			<template v-else>
				<div class="ai-image-generator__group">
					<h3 class="ai-image-generator__title">
						{{ strings.editImage }}
					</h3>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.prompt.id"
						class="ai-image-generator__label"
					>
						{{ strings.prompt }}
					</label>

					<base-textarea
						v-model="aiImageGeneratorStore.form.prompt.value"
						:id="aiImageGeneratorStore.form.prompt.id"
						:rows="5"
						:max-height="200"
						:placeholder="strings.describeChanges"
						:maxlength="aiImageGeneratorStore.form.prompt.maxlength"
					/>

					<div class="ai-image-generator__regenerate-description">
						{{ strings.regenerateDescription }}
					</div>
				</div>

				<div class="ai-image-generator__group">
					<label
						:for="aiImageGeneratorStore.form.quality.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageQuality }}

						<core-tooltip placement="right">
							<svg-circle-question-mark
								width="17"
								height="17"
							/>

							<template #tooltip>
								{{ strings.qualityTooltip }}
							</template>
						</core-tooltip>
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.quality.value"
						:id="aiImageGeneratorStore.form.quality.id"
						:options="imageQualityOptions"
						size="medium"
					/>
				</div>

				<div class="ai-image-generator__group">
					<base-button
						size="small"
						type="green"
						@click="regenerate"
						:disabled="buttonStates.submit.disabled"
						:loading="buttonStates.submit.loading"
					>
						{{ buttonStates.submit.text }}
					</base-button>
				</div>
			</template>
		</template>
	</div>
</template>

<script setup>
import { computed, onMounted } from 'vue'

import {
	useAiImageGeneratorStore,
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'
import { useAiContent } from '@/vue/composables/AiContent'

import AiImageGeneratorFormWhileGenerating from './FormWhileGenerating'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

const aiImageGeneratorStore = useAiImageGeneratorStore()
const rootStore = useRootStore()

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	imageQualityOptions,
	imageStyleOptions,
	imageAspectRatioOptions,
	strings : aiContentStrings
} = useAiContent()

const strings = {
	imageOptions          : __('Image Options', td),
	prompt                : __('Prompt', td),
	placeholder           : __('A cozy cottage in a snowy forest at sunset. A cute cat wearing a wizard hat, reading a spellbook… Unleash your imagination!', td),
	manageDefaultSettings : sprintf(
		// Translators: 1 - Link to the AI Content settings page.
		__('You can manage your default settings under <a href="%1$s" target="_blank" rel="noopener noreferrer">General Settings > AI Content</a>.', td),
		rootStore.aioseo.urls.aio.settings + '/#ai-content'
	),
	editImage             : __('Edit Image', td),
	regenerateDescription : __('Edit the selected image by describing the changes you want to make.', td),
	describeChanges       : __('Give the dog a hat, change the background to a sunset... You can make any changes you want!', td),
	qualityTooltip        : __('Please note that selecting a higher image quality will increase the credit cost and processing time.', td)
}

const buttonStates = computed(() => {
	const isImageSelected    = 0 < aiImageGeneratorStore.images.selected.length
	const isImagePromptEqual = aiImageGeneratorStore.selectedImage.prompt === aiImageGeneratorStore.formPrompt
	const submitDisabled     = !aiImageGeneratorStore.formPrompt || (isImageSelected && isImagePromptEqual) || aiImageGeneratorStore.formPrompt.length < aiImageGeneratorStore.form.prompt.minlength

	return {
		submit : {
			disabled : submitDisabled,
			loading  : aiImageGeneratorStore.form.isGenerating,
			text     : sprintf(
				// Translators: 1 - Number of credits.
				__('Regenerate (%1$s credits)', td), aiImageGeneratorStore.generationPrice.toLocaleString()
			)
		}
	}
})

const regenerate = async () => {
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

onMounted(() => {
	setTimeout(() => {
		const $promptInput = document.getElementById(aiImageGeneratorStore.form.prompt.id) || null
		if ($promptInput) {
			$promptInput.focus()
		}
	})
})
</script>

<style lang="scss" scoped>
.ai-image-generator {
	&__form {
		height: 100%;

		.aioseo-tooltip {
			margin-left: 8px;
		}
	}

	&__regenerate-description {
		font-size: 13px;
		line-height: 1.5;
		margin-top: 5px;
	}
}
</style>