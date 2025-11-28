<template>
	<div class="ai-providers">
		<div
			v-for="provider in aiProviders"
			:key="provider.id"
			class="ai-provider"
		>
			<div class="ai-provider-icon">
				<component :is="providerIcons[provider.id]" />
			</div>
			<span class="ai-provider-name">{{ provider.name }}</span>
		</div>
	</div>
</template>

<script setup>
import SvgGemini from '@/vue/components/common/svg/ai/providers/Gemini'
import SvgAnthropic from '@/vue/components/common/svg/ai/providers/Anthropic'
import SvgOpenAI from '@/vue/components/common/svg/ai/providers/OpenAI'
import SvgDeepSeek from '@/vue/components/common/svg/ai/providers/DeepSeek'
import SvgPerplexity from '@/vue/components/common/svg/ai/providers/Perplexity'

// AI Providers
const aiProviders = [
	{ id: 'gemini', name: 'Gemini' },
	{ id: 'anthropic', name: 'Claude' },
	{ id: 'openai', name: 'OpenAI' },
	{ id: 'deepseek', name: 'DeepSeek' },
	{ id: 'perplexity', name: 'Perplexity' }
]

// Map provider IDs to their icon components
const providerIcons = {
	gemini     : SvgGemini,
	anthropic  : SvgAnthropic,
	openai     : SvgOpenAI,
	deepseek   : SvgDeepSeek,
	perplexity : SvgPerplexity
}
</script>

<style lang="scss">
.ai-providers {
	display: flex;
	gap: 12px;
	justify-content: center;
	flex-wrap: wrap;
	margin-bottom: 32px;
}

.ai-provider {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 4px 12px;
	background: $border;
	border-radius: 30px;

	.ai-provider-icon {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 24px;
		height: 24px;

		svg {
			width: 20px;
			height: 20px;
		}
	}

	.ai-provider-name {
		font-size: $font-md;
		color: $black2;
		font-weight: 400;
	}
}
</style>