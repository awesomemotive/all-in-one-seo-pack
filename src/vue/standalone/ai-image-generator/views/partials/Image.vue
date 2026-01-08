<template>
	<label
		:for="`ai-image-generator__image-${image.id}`"
		:tabindex="aiImageGeneratorStore.form.isGenerating ? -1 : 0"
		:class="{
			'ai-image-generator__image' : true,
			'ai-image-generator__shimmer' : aiImageGeneratorStore.form.isGenerating && isSelected,
			'ai-image-generator__image--landscape' : 'landscape' === parsedAspectRatio,
			'ai-image-generator__image--portrait' : 'portrait' === parsedAspectRatio,
			'ai-image-generator__image--square' : 'square' === parsedAspectRatio
		}"
		@keydown.enter="$refs.inputTag.click()"
	>
		<input
			ref="inputTag"
			type="checkbox"
			:id="`ai-image-generator__image-${image.id}`"
			:checked="isSelected"
			:disabled="aiImageGeneratorStore.form.isGenerating || isSelected"
			@input="onSelect"
		/>

		<img
			:src="image.url"
			alt=""
			decoding="async"
		/>

		<div class="ai-image-generator__image__actions">
			<base-button
				v-if="buttonStates.removeImage.show"
				size="small"
				type="gray"
				@click.stop="aiImageGeneratorStore.toggleModal({modal: 'modalOpenDeleteImages', open: true, images: [ image ]})"
				:title="GLOBAL_STRINGS.delete"
			>
				<svg-trash trim />
			</base-button>

			<base-button
				v-if="buttonStates.editImage.show"
				size="small"
				type="gray"
				@click.stop="editImage"
				:title="GLOBAL_STRINGS.edit"
			>
				<svg-pencil />
			</base-button>
		</div>
	</label>
</template>

<script setup>
import { computed, ref } from 'vue'
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'
import { useAiContent } from '@/vue/composables/AiContent'

import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import SvgPencil from '@/vue/components/common/svg/Pencil'
import SvgTrash from '@/vue/components/common/svg/Trash'

const aiImageGeneratorStore = useAiImageGeneratorStore()

const {
	getAspectRatioFromDimensions
} = useAiContent()

const inputTag = ref(null)

const props = defineProps({
	image : Object
})

const parsedAspectRatio = computed(() => getAspectRatioFromDimensions(props.image.width, props.image.height))

const buttonStates = computed(() => {
	return {
		editImage : {
			show : 'results' === aiImageGeneratorStore.currentScreen
		},
		removeImage : {
			show : 'results' === aiImageGeneratorStore.currentScreen
		}
	}
})

const isSelected = computed(() => {
	return aiImageGeneratorStore.images.selected.find(image => image.id === props.image.id)
})

const onSelect = (event) => {
	if (event.target.checked) {
		aiImageGeneratorStore.selectImage(props.image)
	}
}

const editImage = () => {
	aiImageGeneratorStore.switchScreen('generate')

	aiImageGeneratorStore.selectImage(props.image)
}
</script>

<style lang="scss" scoped>
.ai-image-generator__image {
	border-radius: 4px;
	cursor: pointer;
	margin: 0;
	padding: 0;
	position: relative;
	outline: none;
	overflow: hidden;

	&:focus:not(:hover) {
		outline: 1px solid $blue;
		outline-offset: 2px;

		&:has(input:checked) {
			outline: none;
		}
	}

	img {
		border: 2px solid transparent;
		border-radius: 4px;
		display: block;
		margin: auto;
		outline: none;
		user-select: none;
		height: auto;
		object-fit: cover;
		object-position: center;
		opacity: 1;
		width: 100%;
	}

	&:hover,
	&:focus {
		img {
			opacity: 0.8;
		}
	}

	input {
		display: block;
		height: 0;
		margin: 0;
		padding: 0;
		border: none;
		visibility: hidden;

		&:checked {
			& + img {
				border: 2px solid $blue;
				opacity: 1;
			}
		}
	}

	&__actions {
		display: flex;
		gap: 8px;
		position: absolute;
		bottom: 10px;
		right: 10px;
		transform: translateY(50px);
		will-change: transform;

		.aioseo-button {
			padding: 0;
			width: 32px;
			height: 32px;
		}

		svg {
			width: 16px;
			height: 16px;
		}
	}

	&:hover &__actions,
	&:focus-within &__actions {
		transform: translateY(0);
	}
}
</style>