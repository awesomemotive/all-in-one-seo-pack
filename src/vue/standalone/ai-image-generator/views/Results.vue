<template>
	<div class="ai-image-generator__results">
		<core-loader
			v-if="aiImageGeneratorStore.images.isFetching"
			dark
		/>

		<template v-else>
			<div class="ai-image-generator__group">
				<h3 class="ai-image-generator__title">
					{{ strings.title }}
				</h3>
			</div>

			<div
				v-if="0 === aiImageGeneratorStore.images.count"
				class="ai-image-generator__group"
			>
				{{ strings.noResults }}
			</div>

			<div
				v-if="0 === aiImageGeneratorStore.images.count"
				class="ai-image-generator__group"
			>
				<base-button
					size="small"
					type="blue"
					@click="aiImageGeneratorStore.switchScreen('generate')"
				>
					{{ strings.generateFirstImage }}
				</base-button>
			</div>

			<div
				v-else
				class="ai-image-generator__group"
			>
				<div class="ai-image-generator__results__masonry">
					<ai-image-generator-image
						v-for="(image, index) in aiImageGeneratorStore.images.all.rows"
						:key="`image-${index}`"
						:image="image"
					/>
				</div>
			</div>
		</template>
	</div>
</template>

<script setup>
import {
	useAiImageGeneratorStore
} from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

import CoreLoader from '@/vue/components/common/core/Loader'
import AiImageGeneratorImage from '@/vue/standalone/ai-image-generator/views/partials/Image'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const strings = {
	title              : __('Previous Results', td),
	noResults          : __('No results found.', td),
	generateFirstImage : __('Generate your first image', td)
}
</script>

<style lang="scss" scoped>
.ai-image-generator__results {
	position: relative;

	.aioseo-loading-spinner {
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
	}

	&__masonry {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
        gap: 16px;

		.ai-image-generator__image {
			&--portrait {
				grid-row: span 3;
			}

			&--square {
				grid-row: span 2;
			}

			&--landscape {
				grid-row: span 1;
			}

			:deep(img) {
				height: 100%;
			}
		}
	}
}
</style>