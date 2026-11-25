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

				<core-alert
					v-if="deleteError"
					type="red"
				>
					{{ deleteError }}
				</core-alert>
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

import { ref, watch } from 'vue'

import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreModal from '@/vue/components/common/core/modal/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const emit = defineEmits([ 'update:modalOpen' ])

const props = defineProps({
	modalOpen : Boolean
})

const btnDeleteLoading = ref(false)
const deleteError      = ref(null)

const strings = {
	headerTitle     : __('Delete Image', td),
	areYouSure      : __('Are you sure you want to permanently delete the following image?', td),
	deleteForbidden : __('You don\'t have permission to delete this image.', td),
	deleteFailed    : __('The image could not be deleted. Please try again.', td)
}

watch(() => props.modalOpen, open => {
	if (open) {
		deleteError.value = null
	}
})

const initDelete = async () => {
	btnDeleteLoading.value = true
	deleteError.value      = null

	const ids = aiImageGeneratorStore.images.selected.map(image => image.id)

	try {
		await aiImageGeneratorStore.deleteImages(ids)
	} catch (error) {
		console.error(error)

		deleteError.value      = 403 === error?.response?.status ? strings.deleteForbidden : strings.deleteFailed
		btnDeleteLoading.value = false

		return
	}

	try {
		await aiImageGeneratorStore.fetchImages()
	} catch (error) {
		console.error(error)

		// The images are gone even though the list couldn't be reloaded, so don't leave them clickable.
		aiImageGeneratorStore.images.all.rows = aiImageGeneratorStore.images.all.rows.filter(image => !ids.includes(image.id))
		aiImageGeneratorStore.images.count    = aiImageGeneratorStore.images.all.rows.length
	}

	aiImageGeneratorStore.images.selected = []
	btnDeleteLoading.value                = false

	emit('update:modalOpen', false)
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