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
						:for="aiImageGeneratorStore.form.model.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageModel }}
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.model.value"
						:id="aiImageGeneratorStore.form.model.id"
						:options="imageModelOptions"
						:searchable="false"
						float-dropdown
						size="medium"
						class="ai-image-generator__model-select"
					>
						<template #singleLabel="{ option }">
							<div class="ai-image-generator__model-row">
								<component
									:is="modelIcons[option.value]"
									:height="20"
									:width="20"
									class="ai-image-generator__model-icon"
								/>

								<div class="ai-image-generator__model-title">
									{{ option.label }}

									<base-badge
										v-if="option.badge"
										:text="option.badge.text"
										:color="option.badge.color"
									/>
								</div>
							</div>
						</template>

						<template #option="{ option }">
							<div class="ai-image-generator__model-row">
								<component
									:is="modelIcons[option.value]"
									:height="20"
									:width="20"
									class="ai-image-generator__model-icon"
								/>

								<div class="ai-image-generator__model-content">
									<div class="ai-image-generator__model-title">
										{{ option.label }}

										<base-badge
											v-if="option.badge"
											:text="option.badge.text"
											:color="option.badge.color"
										/>
									</div>

									<div class="ai-image-generator__model-description">
										{{ option.description }}
									</div>
								</div>
							</div>
						</template>
					</base-select>
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

				<div
					v-if="aiImageGeneratorStore.form.model.value?.supportsQuality !== false"
					class="ai-image-generator__group"
				>
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
						:searchable="false"
						float-dropdown
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
						:searchable="false"
						float-dropdown
						size="medium"
					/>
				</div>

				<core-alert
					class="ai-image-generator__group"
					type="blue"
					v-html="aiContentStrings.alertDescription"
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
						:for="aiImageGeneratorStore.form.model.id"
						class="ai-image-generator__label"
					>
						{{ aiContentStrings.imageModel }}
					</label>

					<base-select
						v-model="aiImageGeneratorStore.form.model.value"
						:id="aiImageGeneratorStore.form.model.id"
						:options="imageModelOptions"
						:searchable="false"
						float-dropdown
						size="medium"
						class="ai-image-generator__model-select"
					>
						<template #singleLabel="{ option }">
							<div class="ai-image-generator__model-row">
								<component
									:is="modelIcons[option.value]"
									:height="20"
									:width="20"
									class="ai-image-generator__model-icon"
								/>

								<div class="ai-image-generator__model-title">
									{{ option.label }}

									<base-badge
										v-if="option.badge"
										:text="option.badge.text"
										:color="option.badge.color"
									/>
								</div>
							</div>
						</template>

						<template #option="{ option }">
							<div class="ai-image-generator__model-row">
								<component
									:is="modelIcons[option.value]"
									:height="20"
									:width="20"
									class="ai-image-generator__model-icon"
								/>

								<div class="ai-image-generator__model-content">
									<div class="ai-image-generator__model-title">
										{{ option.label }}

										<base-badge
											v-if="option.badge"
											:text="option.badge.text"
											:color="option.badge.color"
										/>
									</div>

									<div class="ai-image-generator__model-description">
										{{ option.description }}
									</div>
								</div>
							</div>
						</template>
					</base-select>
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

				<div
					v-if="aiImageGeneratorStore.form.model.value?.supportsQuality !== false"
					class="ai-image-generator__group"
				>
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
						:searchable="false"
						float-dropdown
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

import { useAiImageGeneratorStore } from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'
import { useAiContent } from '@/vue/composables/AiContent'

import AiImageGeneratorFormWhileGenerating from './FormWhileGenerating'
import BaseBadge from '@/vue/components/common/base/Badge'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'
import SvgGemini from '@/vue/components/common/svg/ai/providers/Gemini'
import SvgOpenAI from '@/vue/components/common/svg/ai/providers/OpenAI'

const modelIcons = {
	'gemini-3.1-flash-image' : SvgGemini,
	'gpt-image-2'            : SvgOpenAI
}

const aiImageGeneratorStore = useAiImageGeneratorStore()

const td = import.meta.env.VITE_TEXTDOMAIN

const {
	imageQualityOptions,
	imageAspectRatioOptions,
	imageModelOptions,
	strings : aiContentStrings
} = useAiContent()

const strings = {
	imageOptions          : __('Image Options', td),
	prompt                : __('Prompt', td),
	placeholder           : __('A cozy cottage in a snowy forest at sunset. A cute cat wearing a wizard hat, reading a spellbook… Unleash your imagination!', td),
	editImage             : __('Edit Image', td),
	regenerateDescription : __('Edit the selected image by describing the changes you want to make.', td),
	describeChanges       : __('Give the dog a hat, change the background to a sunset... You can make any changes you want!', td),
	qualityTooltip        : __('Please note that selecting a higher image quality will increase the credit cost and processing time.', td)
}

const buttonStates = computed(() => {
	return {
		submit : {
			disabled : !aiImageGeneratorStore.canGenerate,
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

	&__model-select {
		// vue-multiselect paints a solid blue rectangle in the option's top-right corner
		// (intended as a "press enter" hint). It collides with our New badge — hide it here.
		:deep(.multiselect__option::after) {
			content: none;
		}
	}

	&__model-row {
		display: flex;
		align-items: flex-start;
		gap: 12px;
		min-width: 0;
		width: 100%;
	}

	&__model-icon {
		flex-shrink: 0;
	}

	&__model-content {
		flex: 1 1 auto;
		min-width: 0;
		display: flex;
		flex-direction: column;
		gap: 3px;
	}

	&__model-title {
		display: flex;
		align-items: center;
		gap: 8px;
		font-weight: 600;

		// BaseBadge ships with no padding/typography of its own — apply locally so the
		// global component stays untouched. Scoped to this picker via :deep().
		:deep(.aioseo-badge) {
			display: inline-flex;
			align-items: center;
			padding: 1px 8px;
			font-size: 11px;
			line-height: 1.5;
			letter-spacing: 0.02em;
		}
	}

	&__model-description {
		font-size: 12px;
		line-height: 1.4;
		color: $black2;
		white-space: normal;
		word-break: break-word;
	}
}
</style>