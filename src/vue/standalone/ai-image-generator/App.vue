<template>
	<div class="aioseo-ai-image-generator">
		<generate v-if="aiImageGeneratorStore.currentScreen === 'generate'" />

		<results v-if="aiImageGeneratorStore.currentScreen === 'results'" />

		<delete-images
			:modal-open="aiImageGeneratorStore.modalOpenDeleteImages"
			@update:modal-open="aiImageGeneratorStore.toggleModal({modal: 'modalOpenDeleteImages', open: $event})"
		/>
	</div>
</template>

<script setup>
import { onMounted } from 'vue'
import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import DeleteImages from './views/partials/DeleteImages'
import Generate from './views/Generate'
import Results from './views/Results'

const aiImageGeneratorStore = useAiImageGeneratorStore()

aiImageGeneratorStore.setFormDefaults()

onMounted(() => {
	if (null === aiImageGeneratorStore.images.count) {
		try {
			aiImageGeneratorStore.fetchImages()
		} catch (error) {
			console.error(error)
		}
	}

	if ('image-block-toolbar' === aiImageGeneratorStore.initiator.slug) {
		const selectedBlock = window.wp.data.select('core/block-editor').getSelectedBlock()
		if (!selectedBlock) {
			return
		}

		const attributes = selectedBlock?.attributes
		const imageId    = attributes?.id
		if (!imageId) {
			return
		}

		// Bail if the current image being edited doesn't exist anymore.
		if (!aiImageGeneratorStore.initiator.wpMedia) {
			aiImageGeneratorStore.resetInitiator()

			return
		}

		const selectedImage = aiImageGeneratorStore.images.all.rows.find(image => image.id === imageId) || {
			id     : imageId,
			url    : attributes.url,
			alt    : attributes.alt,
			width  : aiImageGeneratorStore.initiator.wpMedia?.media_details?.width,
			height : aiImageGeneratorStore.initiator.wpMedia?.media_details?.height
		}

		aiImageGeneratorStore.selectImage(selectedImage)
	}
})
</script>

<style lang="scss">
.aioseo-ai-image-generator {
	display: grid;
	min-height: min(60vh, 500px);

	.ai-image-generator {
		&__left {
			flex: 1 1 300px;
			max-width: 420px;
			min-width: 280px;
			position: relative;

			&:before {
				content: '';
				position: absolute;
				top: 0;
				right: calc(var(--container-gap) * -1 / 2);
				width: 1px;
				height: 100%;
				background: #D0D1D7;
			}
		}

		&__right {
			flex: 1 1 520px;
			display: grid;
			grid-template-rows: auto 1fr;
		}

		&__group {
			~ .ai-image-generator__group {
				margin-top: 16px;
			}
		}

		&__title {
			color: $black;
			margin: 0;
			font-size: 16px;
			font-weight: 600;
		}

		&__label {
			align-items: center;
			color: $font-color;
			display: flex;
			font-size: 14px;
			font-weight: 600;
			line-height: normal;
			margin-bottom: 8px;
		}

		&__shimmer {
			contain: layout style paint;
			cursor: wait;
			overflow: hidden;
			user-select: none;
			position: relative;

			&::after {
				animation: ai-image-shimmer 2s linear infinite;
				backface-visibility: hidden;
				background: linear-gradient(to right,  rgba(229,231,235,0) 0%,rgba(229,231,235,0.3) 25%,rgba(255,255,255,0.6) 50%,rgba(229,231,235,0.3) 75%,rgba(229,231,235,0) 100%);
				bottom: 0;
				content: '';
				height: 50rem;
				left: 0;
				margin: auto;
				pointer-events: none;
				position: absolute;
				right: 0;
				top: 0;
				transform-origin: center;
				transform: rotate(45deg) translate3d(-50%, 0, 0);
				width: 100%;
				will-change: transform;
				z-index: 1;
			}
		}

		@keyframes ai-image-shimmer {
			0% {
				transform: rotate(45deg) translate3d(-300%, 0, 0);
			}

			100% {
				transform: rotate(45deg) translate3d(300%, 0, 0);
			}
		}
	}
}
</style>