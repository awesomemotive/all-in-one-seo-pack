<template>
	<div class="ai-image-generator__generate">
		<div class="ai-image-generator__left">
			<ai-image-generator-form />
		</div>

		<div class="ai-image-generator__right">
			<div class="ai-image-generator__group">
				<h3 class="ai-image-generator__title">
					{{ strings.imagePreview }}
				</h3>
			</div>

			<div class="ai-image-generator__group">
				<div class="ai-image-generator__generate__preview-outer">
					<div class="ai-image-generator__generate__preview-inner">
						<div
							v-if="displayImages.length > 0"
							class="ai-image-generator__generate__preview-images"
							:class="{
								'ai-image-generator__generate__preview-images--one' : displayImages.length === 1
							}"
						>
							<ai-image-generator-image :image="displayImages[0]" />

							<svg-right-arrow-simple
								v-if="1 < displayImages.length"
								width="20"
								height="20"
								color="#8C8F9A"
							/>

							<ai-image-generator-image
								v-if="displayImages[1]"
								:image="displayImages[1]"
							/>
						</div>

						<div
							v-else
							class="ai-image-generator__generate__preview-placeholder"
							:class="{
								'ai-image-generator__generate__preview-placeholder--landscape' : aiImageGeneratorStore.form.aspectRatio.value.value === 'landscape',
								'ai-image-generator__generate__preview-placeholder--portrait' : aiImageGeneratorStore.form.aspectRatio.value.value === 'portrait',
								'ai-image-generator__generate__preview-placeholder--square' : aiImageGeneratorStore.form.aspectRatio.value.value === 'square',
								'ai-image-generator__shimmer' : aiImageGeneratorStore.form.isGenerating
							}"
						>
							<svg-ai-content />
						</div>
					</div>
				</div>
			</div>

			<div
				class="ai-image-generator__group"
				v-if="aiImageGeneratorStore.error?.message"
			>
				<core-alert
					v-html="aiImageGeneratorStore.error.message"
					:type="aiImageGeneratorStore.error.type"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

import AiImageGeneratorForm from './partials/Form'
import AiImageGeneratorImage from './partials/Image'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import SvgAiContent from '@/vue/components/common/svg/ai/AiContent'
import SvgRightArrowSimple from '@/vue/components/common/svg/right-arrow/Simple'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const strings = {
	imagePreview : __('Preview', td)
}

const parentImage = computed(() => {
	if (!aiImageGeneratorStore.selectedImage) {
		return null
	}

	if (aiImageGeneratorStore.selectedImage.parentImageId) {
		return aiImageGeneratorStore.images.all.rows.find(img => img.id === aiImageGeneratorStore.selectedImage.parentImageId)
	}

	return null
})

const childImage = computed(() => {
	if (!aiImageGeneratorStore.selectedImage) {
		return null
	}

	if (parentImage.value) {
		return null
	}

	return aiImageGeneratorStore.images.all.rows.find(img => img.parentImageId === aiImageGeneratorStore.selectedImage.id)
})

const displayImages = computed(() => {
	if (!aiImageGeneratorStore.selectedImage) {
		return []
	}

	// Case 1: Selected image has a parent - show parent first, then selected.
	if (parentImage.value) {
		return [ parentImage.value, aiImageGeneratorStore.selectedImage ]
	}

	// Case 2: Selected image has a child - show selected first, then child.
	if (childImage.value) {
		return [ aiImageGeneratorStore.selectedImage, childImage.value ]
	}

	// Case 3: No relationship - show only selected.
	return [ aiImageGeneratorStore.selectedImage ]
})
</script>

<style lang="scss">
.ai-image-generator__generate {
	--container-gap: 35px;

	display: flex;
	gap: var(--container-gap);

	&__preview-outer {
		align-content: center;
		background-color: #F3F4F5;
		border-radius: 4px;
		height: 100%;
	}

	&__preview-inner {
		margin: 20px;
	}

	&__preview-images {
		align-items: center;
		display: grid;
		grid-template-columns: 1fr auto 1fr;
		gap: 20px;

		&--one {
			grid-template-columns: minmax(0, 345px);
			justify-content: center;
		}
	}

	&__preview-placeholder {
		background: #e5e7eb;
		border-radius: 4px;
		margin: auto;
		max-width: 345px;
		position: relative;
		overflow: hidden;

		&::before {
			content: '';
			display: block;
		}

		&--landscape {
			&::before {
				padding-top: 66.66%;
			}
		}

		&--portrait {
			&::before {
				padding-top: 150%;
			}
		}

		&--square {
			&::before {
				padding-top: 100%;
			}
		}

		svg {
			color: #8c8f9a;
			display: block;
			height: 28px;
			left: 50%;
			margin: 0 auto;
			position: absolute;
			top: 50%;
			transform: translate(-50%, -50%);
			width: 28px;
		}
	}
}
</style>