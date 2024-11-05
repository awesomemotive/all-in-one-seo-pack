<template>
	<div class="aioseo-writing-assistant-report-keyword">
		<div
			class="aioseo-writing-assistant-report-keyword__title"
			v-if="props.showTitle"
		>
			{{ strings.reportDetails }}
		</div>

		<div>
			<div class="aioseo-writing-assistant-report-keyword__keyword">
				"{{ writingAssistantStore.keywordText }}"
			</div>
		</div>
	</div>
</template>

<script setup>
import { useWritingAssistantStore } from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const strings = {
	reportDetails : __('Report Keyword', td)
}

const props = defineProps({
	showTitle : {
		type    : Boolean,
		default : true
	}
})
</script>

<style lang="scss">
.aioseo-writing-assistant-report-keyword {
	display: flex;
	gap: 12px;
	flex-direction: column;

	&__title {
		font-weight: 700;
		font-size: 16px;
		padding: 0 0 10px;
		color: $black;
	}

	&__keyword {
		font-size: 16px;
		line-height: 22px;
		font-weight: 700;
		padding: 7px 12px;
		background: $background;
		font-style: italic;
		color: $black;
		display: inline-block;
	}

}
</style>