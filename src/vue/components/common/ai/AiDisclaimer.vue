<template>
	<div class="aioseo-ai-disclaimer">
		<span class="aioseo-ai-disclaimer__icon-wrapper">
			<svg-sparkles />
		</span>

		<span v-safe-html="disclaimerMessage" />
	</div>
</template>

<script setup>
import { computed } from 'vue'

import { useAiContent } from '@/vue/composables/AiContent'

import SvgSparkles from '@/vue/components/common/svg/ai/Sparkles'

const props = defineProps({
	message : {
		type    : String,
		default : ''
	}
})

const aiContent = useAiContent()

const disclaimerMessage = computed(() => props.message || aiContent.strings.disclaimer)
</script>

<style lang="scss">
.aioseo-ai-disclaimer {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 10px 14px 10px 16px;
	border-left: 3px solid $blue;
	border-radius: 4px;
	background: linear-gradient(90deg, rgba($blue, 0.06), rgba($blue, 0.02) 35%, $background);
	font-size: $font-sm;
	line-height: 18px;
	color: $black2;

	&__icon-wrapper {
		flex-shrink: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 22px;
		height: 22px;
		border-radius: 5px;
		background-color: rgba($blue, 0.1);

		svg {
			width: 12px;
			height: 12px;
			color: $blue;
		}
	}

	a {
		color: $blue;
		font-weight: 500;
	}
}
</style>