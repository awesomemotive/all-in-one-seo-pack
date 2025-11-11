<template>
	<div class="aioseo-ai-assistant-sidebar">
		<credit-counter
			tooltip-placement="bottom"
			tooltip-offset="-90px,0"
			parent-component-context="sidebar"
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