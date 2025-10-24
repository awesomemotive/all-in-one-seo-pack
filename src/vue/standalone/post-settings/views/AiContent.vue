<template>
	<div class="aioseo-ai-content-standalone aioseo-tab-content">
		<core-blur v-if="aiStore.isFreeAndOutOfCredits">
			<ai-content-main :parent-component-context="props.parentComponentContext" />
		</core-blur>

		<ai-content-main
			v-else
			:parent-component-context="props.parentComponentContext"
		/>

		<ai-content-cta v-if="aiStore.isFreeAndOutOfCredits" />
	</div>
</template>

<script setup>
import { useAiStore } from '@/vue/stores/AiStore'

import AiContentMain from './partials/ai-content/Main'
import AiContentCta from './partials/ai-content/Cta'
import CoreBlur from '@/vue/components/common/core/Blur'

const props = defineProps({
	parentComponentContext : String
})

const aiStore = useAiStore()
</script>

<style lang="scss">
.aioseo-app.aioseo-post-settings .aioseo-ai-content-standalone {
	min-height: 600px;

	.settings-name .name {
		flex-direction: column;
		align-items: flex-start;
		margin-bottom: 10px;
	}

	.aioseo-ai-content-features {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
		gap: 16px;
	}
}
</style>