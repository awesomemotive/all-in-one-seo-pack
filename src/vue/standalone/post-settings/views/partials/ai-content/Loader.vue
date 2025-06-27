<template>
	<div class="aioseo-ai-content-loader">
		<div class="aioseo-ai-content-loader-image">
			<img
				:src="getLoaderImage(currentItem.slug)"
				:alt="currentItem.label"
			/>
		</div>

		<div class="aioseo-ai-content-loader-text">
			<div class="loading-container">
				<core-loader :dark="true" />
				<div class="loading-text">{{ aiContent.loadingText(currentItem.name) }}</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { getAssetUrl } from '@/vue/utils/helpers'
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAiContent } from '@/vue/composables/AiContent'

import CoreLoader from '@/vue/components/common/core/Loader'

// Images
import EmailImage from '@/vue/assets/images/ai/loader/email.png'
import FacebookImage from '@/vue/assets/images/ai/loader/facebook.png'
import FaqImage from '@/vue/assets/images/ai/loader/faq.png'
import InstagramImage from '@/vue/assets/images/ai/loader/instagram.png'
import KeyPointsImage from '@/vue/assets/images/ai/loader/keypoints.png'
import LinkedInImage from '@/vue/assets/images/ai/loader/linkedin.png'
import MetaTitleImage from '@/vue/assets/images/ai/loader/meta-title.png'
import MetaDescriptionImage from '@/vue/assets/images/ai/loader/meta-description.png'
import TwitterImage from '@/vue/assets/images/ai/loader/twitter.png'

// Props definition
const props = defineProps({
	loaders : {
		type      : Array,
		required  : true,
		validator : (value) => {
			return value.every(item => 'string' === typeof item.name && 'string' === typeof item.icon)
		}
	}
})

const aiContent = useAiContent()
const currentIndex = ref(0)

const changeItem = () => {
	currentIndex.value = (currentIndex.value + 1) % props.loaders.length
}

let interval = null

onMounted(() => {
	interval = setInterval(changeItem, 2000)
})

onUnmounted(() => {
	clearInterval(interval)
})

const currentItem = computed(() => props.loaders[currentIndex.value])

const getLoaderImage = (slug) => {
	switch (slug) {
		case 'email':
			return getAssetUrl(EmailImage)
		case 'faq':
			return getAssetUrl(FaqImage)
		case 'facebook':
			return getAssetUrl(FacebookImage)
		case 'instagram':
			return getAssetUrl(InstagramImage)
		case 'linkedin':
			return getAssetUrl(LinkedInImage)
		case 'key-points':
			return getAssetUrl(KeyPointsImage)
		case 'twitter':
			return getAssetUrl(TwitterImage)
		case 'meta-title':
			return getAssetUrl(MetaTitleImage)
		case 'meta-description':
			return getAssetUrl(MetaDescriptionImage)
		default:
			return null
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-loader {
	display: flex;
	margin-bottom: -20px;

	.aioseo-ai-content-loader-image {
		display: flex;
		align-items: center;
		justify-content: center;

		img {
				max-width: 400px;
				max-height: 400px;
				padding: 50px;
				height: auto;
		}
	}

	>div {
		flex: 1 1 50%;

		&:first-of-type {
			margin: -20px 0 0 -20px;
			background-color: $blue2;
		}

		&:last-of-type {
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.loading-container {
			display: flex;
			align-items: center;

			.aioseo-loading-spinner {
				position: relative;
			}
		}

		.loading-text {
			margin-left: 10px;
			font-size: 16px;
		}
	}
}
</style>