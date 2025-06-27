<template>
	<div class="aioseo-ai-content-settings">
		<core-card slug="aiContentSettings" :header-text="strings.aiContentSettings">
			<core-blur v-if="aiStore.isFreeAndOutOfCredits">
				<ai-content-main />
			</core-blur>

			<ai-content-main v-else />

			<ai-content-cta v-if="aiStore.isFreeAndOutOfCredits" />
		</core-card>
	</div>
</template>

<script setup>
import { useAiStore } from '@/vue/stores/AiStore'

import AiContentMain from './partials/AiContent/Main'
import AiContentCta from '@/vue/standalone/post-settings/views/partials/ai-content/Cta'
import CoreBlur from '@/vue/components/common/core/Blur'
import CoreCard from '@/vue/components/common/core/Card'

import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const strings = {
	aiContentSettings : __('AI Content Settings', td)
}

const aiStore = useAiStore()
</script>

<style lang="scss">
.aioseo-ai-content-settings {
	$blueBackground: #B2D1FF;

	.aioseo-blur {
		min-height: 650px;
	}
}
</style>