<template>
	<div class="ai-image-generator__form-waiting">
		<div
			v-if="aiImageGeneratorStore.isGenerationTakingTooLong"
			class="ai-image-generator__form-waiting__encouraging-message"
		>
			<lottie
				v-if="randomEncouragingMessage.lottie"
				:options="{animationData: randomEncouragingMessage.lottie}"
				:height="200"
				:width="200"
			/>

			<div>{{ randomEncouragingMessage.text }}</div>
		</div>

		<div
			v-else
			class="ai-image-generator__form-waiting__loader"
		>
			<core-loader dark />

			<span>{{ strings.generatingImage }}</span>
		</div>
	</div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'

import { useAiImageGeneratorStore } from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

import CoreLoader from '@/vue/components/common/core/Loader'
import Lottie from '@/vue/components/common/core/Lottie'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const strings = {
	generatingImage : __('Generating Image', td)
}

const animationImports = [
	() => import('@/vue/assets/lottie/cute-bear-dancing-animation.json'),
	() => import('@/vue/assets/lottie/enjoying-sloth-animation.json'),
	() => import('@/vue/assets/lottie/koala-eats-leaves.json'),
	() => import('@/vue/assets/lottie/panda-sleeping-animation.json'),
	() => import('@/vue/assets/lottie/cat-playing-animation.json')
]

const messageTexts = [
	__('Bear-ing down on your image... just a moment!', td),
	__('Your image is being generated... hang in there!', td),
	__('Koala-ty pixels in progress… give us a moment.', td),
	__('Dreaming up something special… stay tuned!', td),
	__('Pawsing for perfection… your image is coming!', td)
]

const loadedAnimation = ref(null)
const selectedIndex   = ref(0)

onMounted(async () => {
	selectedIndex.value = Math.floor(Math.random() * animationImports.length)
	const module = await animationImports[selectedIndex.value]()
	loadedAnimation.value = module.default || module
})

const randomEncouragingMessage = computed(() => {
	return {
		lottie : loadedAnimation.value,
		text   : messageTexts[selectedIndex.value]
	}
})
</script>

<style lang="scss" scoped>
.ai-image-generator {
	&__form-waiting {
		height: 100%;
		place-content: center;

		&__loader {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 10px;

			.aioseo-loading-spinner {
				position: relative;
			}
		}

		&__encouraging-message {
			display: flex;
			flex-direction: column;
			text-align: center;
			gap: 8px;
			align-items: center;
			font-size: 13px;
			line-height: 22px;
			font-style: italic;
		}
	}
}
</style>