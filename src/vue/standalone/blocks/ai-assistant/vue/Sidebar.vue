<template>
	<div class="aioseo-ai-assistant-sidebar">
		<credit-counter
			tooltip-placement="bottom"
			tooltip-offset="-80px,0"
		/>

		<div class="aioseo-ai-assistant-sidebar-cost">
			{{ strings.cost }}
		</div>
	</div>
</template>

<script setup>
import {
	useAiAssistantStore
} from '@/vue/stores'

import { sprintf, __ } from '@/vue/plugins/translations'

import CreditCounter from '@/vue/components/common/ai/CreditCounter'

const td = import.meta.env.VITE_TEXTDOMAIN

const aiAssistantStore = useAiAssistantStore()

const strings = {
	cost : sprintf(
		// Translators: 1 - Number of credits.
		__('Each AI Assistant request costs %1$s credits.', td), aiAssistantStore.generationPrice.toLocaleString()
	)
}
</script>

<style lang="scss" scoped>
	.aioseo-ai-assistant-sidebar {
		.aioseo-ai-assistant-sidebar-cost {
			margin-top: 20px;
			font-size: 13px;
			color: $black;
		}
	}
</style>