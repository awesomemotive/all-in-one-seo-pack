<template>
	<core-modal
		:show="modalOpen"
		@close="$emit('update:modalOpen', false)"
		:classes="['aioseo-ai-image-generator-delete-images-modal']"
	>
		<template #headerTitle>
			{{ strings.headerTitle }}
		</template>

		<template #body>
			<div class="aioseo-ai-image-generator-delete-images-modal__body">
				<span>{{ strings.areYouSure }}</span>

				<template
					v-for="image in aiImageGeneratorStore.images.selected"
					:key="`delete-image-${image.id}`"
				>
					<img
						:src="image.url"
						alt=""
					/>
				</template>
			</div>
		</template>

		<template #footer>
			<div class="aioseo-ai-image-generator-delete-images-modal__footer">
				<base-button
					type="gray"
					size="medium"
					@click.exact="$emit('update:modalOpen', false)"
				>
					{{ GLOBAL_STRINGS.cancel }}
				</base-button>

				<base-button
					type="red"
					size="medium"
					:loading="btnDeleteLoading"
					@click.exact="initDelete()"
				>
					{{ GLOBAL_STRINGS.delete }}
				</base-button>
			</div>
		</template>
	</core-modal>
</template>

<script setup>
import { GLOBAL_STRINGS } from '@/vue/plugins/constants'

import { ref } from 'vue'

import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import CoreModal from '@/vue/components/common/core/modal/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const emit = defineEmits([ 'update:modalOpen' ])

defineProps({
	modalOpen : Boolean
})

const btnDeleteLoading = ref(false)

const strings = {
	headerTitle : __('Delete Image', td),
	areYouSure  : __('Are you sure you want to permanently delete the following image?', td)
}

const initDelete = async () => {
	btnDeleteLoading.value = true

	try {
		await aiImageGeneratorStore.deleteImages([ aiImageGeneratorStore.images.selected.map(image => image.id) ])
		await aiImageGeneratorStore.fetchImages()

		aiImageGeneratorStore.images.selected = []

		emit('update:modalOpen', false)
	} catch (error) {
		console.error(error)
	} finally {
		btnDeleteLoading.value = false
	}
}
</script>

<style lang="scss">
.aioseo-ai-image-generator-delete-images-modal {
	&__body {
		display: flex;
		flex-direction: column;
		gap: 12px;
		padding: 20px;
		align-items: center;

		img {
			border-radius: 4px;
			display: block;
			height: auto;
			object-fit: cover;
			object-position: center;
			width: 200px;
		}
	}

	&__footer {
		display: flex;
		gap: 12px;
		justify-content: center;
		padding: 12px 20px;
	}
}
</style>