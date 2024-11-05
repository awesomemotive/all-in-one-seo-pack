<template>
	<div class="aioseo-writing-assistant__new-keyword">
		<div>
			<div class="aioseo-writing-assistant__new-keyword-label">{{ strings.keyword }}</div>
			<div class="aioseo-writing-assistant__new-keyword-input">
				<base-input
					type="text"
					size="medium"
					v-model="newKeyword"
					:disabled="writingAssistantStore.isProcessing || disabled"
				/>
			</div>
			<div class="aioseo-description">
				{{ strings.insert }}
			</div>
		</div>
		<div>
			<div class="aioseo-writing-assistant__new-keyword-label">{{ strings.country }}</div>
			<base-select
				size="medium"
				:options="writingAssistantSettingsStore.getCountriesOptions"
				:modelValue="writingAssistantSettingsStore.userCountryOption"
				@update:modelValue="option => writingAssistantSettingsStore.seoBoost.userOptions.country = option.value"
				:disabled="writingAssistantStore.isProcessing"
			/>
		</div>
		<div>
			<div class="aioseo-writing-assistant__new-keyword-label">{{ strings.language }}</div>
			<base-select
				size="medium"
				:options="writingAssistantSettingsStore.getLanguagesOptions"
				:modelValue="writingAssistantSettingsStore.userLanguageOption"
				@update:modelValue="option => writingAssistantSettingsStore.seoBoost.userOptions.language = option.value"
				:disabled="writingAssistantStore.isProcessing"
			/>
		</div>
		<div>
			<base-button
				v-if="showButton"
				size="medium"
				type="blue"
				@click="writingAssistantStore.processKeyword({
					keyword  : newKeyword,
					country  : writingAssistantSettingsStore.seoBoost.userOptions.country,
					language : writingAssistantSettingsStore.seoBoost.userOptions.language
				})"
				:loading="writingAssistantStore.processing"
				:disabled="0 === newKeyword.length"
			>
				{{ writingAssistantStore.polling ? strings.generatingReport : strings.generateReport }}
			</base-button>
		</div>

		<core-alert
			v-if="writingAssistantStore.error"
			type="red"
		>
			{{ writingAssistantStore.error }}
		</core-alert>
	</div>
</template>

<script>
import { useWritingAssistantStore, useWritingAssistantSettingsStore } from '@/vue/stores'
import BaseInput from '@/vue/components/common/base/Input'
import BaseButton from '@/vue/components/common/base/Button'
import BaseSelect from '@/vue/components/common/base/Select'
import CoreAlert from '@/vue/components/common/core/alert/Index'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	emits : [ 'report-generating' ],
	setup () {
		return {
			writingAssistantStore         : useWritingAssistantStore(),
			writingAssistantSettingsStore : useWritingAssistantSettingsStore()
		}
	},
	components : {
		BaseInput,
		BaseButton,
		BaseSelect,
		CoreAlert
	},
	props : {
		descriptionPosition : String,
		showButton          : {
			type : Boolean,
			default () {
				return true
			}
		},
		disabled : Boolean
	},
	data () {
		return {
			strings : {
				keyword           : __('Keyword', td),
				country           : __('Region', td),
				language          : __('Language', td),
				generateAReport   : __('Generate a Report', td),
				generateReport    : __('Generate Report', td),
				generatingReport  : __('Generating Report...', td),
				insert            : __('Insert keyword that you want to rank for.', td),
				processingKeyword : __('The keyword is being processed. This can take a couple of minutes.', td)
			},
			newKeyword : ''
		}
	},
	mounted () {
		this.newKeyword = this.writingAssistantStore.keywordText
	}
}
</script>

<style lang="scss" scoped>
.aioseo-writing-assistant__new-keyword {
	display: flex;
	gap: 12px;
	flex-direction: column;
	max-width: 450px;

	&-label {
		font-weight: 700;
		padding-bottom: 8px;
		color: $black;
	}

	&-input {
		display: flex;
		flex-wrap: wrap;

		.aioseo-input-container {
			position: relative;
			margin: 0;

			.aioseo-loading-spinner {
				position: absolute;
				right: 10px;
				top: 3px;
			}

			.grade-value {
				position: absolute;
				right: 10px;
				top: 4px;
			}
		}

		.aioseo-button {
			width: auto;
			margin-left: 12px;
		}

		input {
			padding: 12px 50px 12px 12px !important;
		}
	}

	.aioseo-description {
		font-size: 14px;
		line-height: 18px;
	}

	.aioseo-alert {
		margin-top: 8px;
	}

	.button-percentage {
		display: flex;
		align-items: center;
		flex: 1;
	}

	.aioseo-percent-circle {
		margin-left: 12px;
	}
}
</style>