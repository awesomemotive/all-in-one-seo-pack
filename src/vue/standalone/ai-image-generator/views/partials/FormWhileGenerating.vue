<template>
	<div class="ai-image-generator__form-waiting">
		<div
			v-if="aiImageGeneratorStore.isGenerationTakingTooLong"
			class="ai-image-generator__form-waiting__encouraging-message"
		>
			<lottie
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
import { computed } from 'vue'

import { useAiImageGeneratorStore } from '@/vue/stores'

import { __ } from '@/vue/plugins/translations'

import CoreLoader from '@/vue/components/common/core/Loader'
import Lottie from '@/vue/components/common/core/Lottie'
import * as LottieCuteBear from '@/vue/assets/lottie/cute-bear-dancing-animation.json'
import * as LottieEnjoyingSloth from '@/vue/assets/lottie/enjoying-sloth-animation.json'
import * as LottieKoalaEats from '@/vue/assets/lottie/koala-eats-leaves.json'
import * as LottiePandaSleeping from '@/vue/assets/lottie/panda-sleeping-animation.json'
import * as LottieCatPlaying from '@/vue/assets/lottie/cat-playing-animation.json'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiImageGeneratorStore = useAiImageGeneratorStore()

const strings = {
	generatingImage : __('Generating Image', td)
}

const encouragingMessages = [
	{
		lottie : LottieCuteBear,
		text   : __('Bear-ing down on your image... just a moment!', td)
	},
	{
		lottie : LottieEnjoyingSloth,
		text   : __('Your image is being generated... hang in there!', td)
	},
	{
		lottie : LottieKoalaEats,
		text   : __('Koala-ty pixels in progress… give us a moment.', td)
	},
	{
		lottie : LottiePandaSleeping,
		text   : __('Dreaming up something special… stay tuned!', td)
	},
	{
		lottie : LottieCatPlaying,
		text   : __('Pawsing for perfection… your image is coming!', td)
	}
]

const randomEncouragingMessage = computed(() => {
	const randomIndex = Math.floor(Math.random() * encouragingMessages.length)

	return encouragingMessages[randomIndex]
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