<template>
	<div class="aioseo-seoboost-processing">
		<div class="aioseo-seoboost-processing__title">
			{{ strings.processing }}
		</div>
		<div class="aioseo-seoboost-processing__keyword">
			"{{ writingAssistantStore.keywordText }}"
		</div>
		<loading-bar
			:percent="percent"
			:show-number="false"
		>
			{{ writingAssistantStore.progress.text }}
		</loading-bar>
	</div>
</template>

<script setup>
import {
	onBeforeMount,
	ref,
	watch
} from 'vue'
import { useWritingAssistantStore } from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'
import LoadingBar from '@/vue/components/common/core/LoadingBar'

const td = import.meta.env.VITE_TEXTDOMAIN

const writingAssistantStore = useWritingAssistantStore()

const strings = {
	processing : __('Generating Report For', td)
}

const percent = ref(0)

watch(() => writingAssistantStore.progress.percent, (value) => {
	percent.value = value
})

onBeforeMount(() => {
	setTimeout(() => {
		percent.value = writingAssistantStore.progress.percent
	}, 200)
})
</script>

<style lang="scss">
.aioseo-seoboost-processing {
	display: flex;
	flex-direction: column;
	align-items: center;
	text-align: center;
	justify-content: center;
	line-height: 22px;

	.aioseo-loading-bar {
		max-width: 400px;
	}

	&__title {
		font-size: 12px;
		text-transform: uppercase;
		text-align: center;
		padding-bottom: 4px;
	}

	&__keyword {
		font-size: 16px;
		line-height: 22px;
		font-weight: 700;
		padding: 7px 12px;
		margin-bottom: 20px;
		background: $background;
		font-style: italic;
	}

}
</style>