<template>
	<div
		class="aioseo-writing-assistant__report-details"
		:class="{loading: writingAssistantStore.isProcessing}"
		v-if="writingAssistantStore.hasReport || writingAssistantStore.isProcessing"
	>
		<base-collapse-box :open="props.open">
			<template #title>
				{{ writingAssistantStore.isProcessing ? strings.reportGenerating : strings.reportDetails }}
			</template>
			<template #body>
				<div class="aioseo-writing-assistant__report-details-report">
					<div class="aioseo-writing-assistant__report-details-report-item">
						<span class="aioseo-writing-assistant__report-details-report-item-label">{{ strings.keyword }}:</span>
						{{ writingAssistantStore.keywordText }}
					</div>
					<div class="aioseo-writing-assistant__report-details-report-item">
						<span class="aioseo-writing-assistant__report-details-report-item-label">{{ strings.country }}:</span>
						{{ writingAssistantSettingsStore.getCountryLabel(writingAssistantStore.report.country) }}
					</div>
					<div class="aioseo-writing-assistant__report-details-report-item">
						<span class="aioseo-writing-assistant__report-details-report-item-label">{{ strings.language }}:</span>
						{{ writingAssistantSettingsStore.getLanguageLabel(writingAssistantStore.report.language) }}
					</div>
				</div>
			</template>
		</base-collapse-box>
	</div>
</template>

<script setup>
import {
	useWritingAssistantStore,
	useWritingAssistantSettingsStore
} from '@/vue/stores'
import { __ } from '@/vue/plugins/translations'
import BaseCollapseBox from '@/vue/components/common/base/CollapseBox'

const td = import.meta.env.VITE_TEXTDOMAIN
const writingAssistantStore = useWritingAssistantStore()
const writingAssistantSettingsStore = useWritingAssistantSettingsStore()
const strings = {
	reportDetails    : __('Report Details', td),
	reportGenerating : __('Generating Report For', td),
	keyword          : __('Keyword', td),
	country          : __('Country', td),
	language         : __('Language', td)
}

const props = defineProps([ 'open' ])
</script>

<style lang="scss">
.aioseo-writing-assistant__report-details {
	display: flex;
	gap: 12px;
	flex-direction: column;

	&.loading {
		.aioseo-writing-assistant__report-details-report {
			opacity: 0.5;
			pointer-events: none;

			&-percent {
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
			}
		}
	}

	&-report {
		position: relative;
		display: flex;
		flex-direction: column;
		padding: 12px;
		border: 1px solid $input-border;
		background: $box-background;
		gap: 12px;

		&-item {
			display: flex;
			flex-direction: column;
			gap: 4px;

			&-label {
				font-weight: 700;
			}
		}
	}
}
</style>