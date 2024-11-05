<template>
	<div
		class="aioseo-writing-assistant__report-details-2"
		:class="{loading: writingAssistantStore.polling}"
		v-if="writingAssistantStore.hasReport || writingAssistantStore.polling"
	>
		<div class="aioseo-writing-assistant__report-details-2-title">
			{{ writingAssistantStore.polling ? strings.reportGenerating : strings.reportDetails }}
		</div>
		<div class="aioseo-writing-assistant__report-details-2-report">
			<div class="aioseo-writing-assistant__report-details-2-report-item">
				<span class="aioseo-writing-assistant__report-details-2-report-item-label">{{ strings.keyword }}:</span>
				{{ writingAssistantStore.keywordText }}
			</div>
			<div class="aioseo-writing-assistant__report-details-2-report-item">
				<span class="aioseo-writing-assistant__report-details-2-report-item-label">{{ strings.country }}:</span>
				{{ writingAssistantStoreSettings.getCountryLabel(writingAssistantStore.report.country) }}
			</div>
			<div class="aioseo-writing-assistant__report-details-2-report-item">
				<span class="aioseo-writing-assistant__report-details-2-report-item-label">{{
						strings.language
					}}:</span>
				{{ writingAssistantStoreSettings.getLanguageLabel(writingAssistantStore.report.language) }}
			</div>
		</div>
	</div>
</template>

<script setup>
import { useWritingAssistantStore, useWritingAssistantSettingsStore } from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const writingAssistantStoreSettings = useWritingAssistantSettingsStore()
const strings = {
	reportDetails    : __('Report Details', td),
	reportGenerating : __('Generating Report For', td),
	keyword          : __('Keyword', td),
	country          : __('Country', td),
	language         : __('Language', td)
}
</script>

<style lang="scss">
.aioseo-writing-assistant__report-details-2 {
	display: flex;
	gap: 12px;
	flex-direction: column;

	&-title {
		font-weight: 700;
		font-size: 16px;
		padding: 0 0 10px;
		color: $black;
	}

	&-report {
		position: relative;
		display: flex;
		padding: 12px;
		border: 1px solid $input-border;
		gap: 40px;

		&-item {
			display: flex;
			flex-direction: column;
			gap: 4px;
			flex: 1;
			border-right: 1px solid $input-border;

			&-label {
				font-weight: 700;
			}

			&:last-child {
				border-right: none;
			}
		}
	}
}
</style>