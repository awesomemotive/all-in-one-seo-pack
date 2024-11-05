<template>
	<div class="aioseo-writing-assistant__your-content">
		<div class="aioseo-writing-assistant__your-content-title">{{ strings.yourContent }}:</div>
		<div>
			<GradeRound :grade="writingAssistantStore.getContentAnalysis.wordCount ? writingAssistantStore.getContentAnalysis.grade : '-'" />
		</div>
		<div class="aioseo-writing-assistant__your-content-stats">
			<word-count :summary="writingAssistantStore.getContentAnalysis"/>
			<readability :summary="writingAssistantStore.getContentAnalysis"/>
		</div>
	</div>
</template>

<script setup>
import Readability from '@/vue/standalone/writing-assistant/views/partials/summary/Readability'
import WordCount from '@/vue/standalone/writing-assistant/views/partials/summary/WordCount'
import { useWritingAssistantStore } from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'
import GradeRound from '@/vue/standalone/writing-assistant/views/partials/GradeRound'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()

const strings = {
	yourContent    : __('Your Content', td),
	missingContent : __('You must first add content before the score can be determined.', td)
}
</script>

<style lang="scss">
.aioseo-writing-assistant {
	&__your-content {
		padding: 12px;
		display: flex;
		gap: 12px;
		flex-direction: column;
		text-align: center;
		border: 1px solid $input-border;

		&-title {
			font-weight: 700;
			font-size: 15px;
		}

		&-stats {
			display: flex;
			justify-content: space-between;
			text-align: left
		}
	}
}
</style>