<template>
	<div class="aioseo-ai-content-style-form">
		<div class="form-group">
			<div class="field-group">
				<label>{{ aiContent.strings.tone }}</label>
				<base-select
					size="small"
					:options="aiContent.toneOptions"
					:modelValue="aiContent.toneOptions.find(o => o.value === options.tone)"
					@update:modelValue="value => options.tone = value.value"
				/>
			</div>

			<div class="field-group">
				<label>{{ aiContent.strings.audience }}</label>
				<base-select
					size="small"
					:options="aiContent.audienceOptions"
					:modelValue="aiContent.audienceOptions.find(o => o.value === options.audience)"
					@update:modelValue="value => options.audience = value.value"
				/>
			</div>

			<core-alert
				v-if="allowed('aioseo_general_settings')"
				type="blue"
				v-html="aiContent.strings.alertDescription"
			/>
		</div>
	</div>
</template>

<script>
import {
	useAiStore,
	useOptionsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import { useAiContent } from '@/vue/composables/AiContent'
import CoreAlert from '@/vue/components/common/core/alert/Index'

export default {
	setup () {
		return {
			aiContent    : useAiContent(),
			aiStore      : useAiStore(),
			optionsStore : useOptionsStore(),
			allowed
		}
	},
	components : {
		CoreAlert
	},
	props : {
		optionsKey : {
			type     : String,
			required : true
		}
	},
	computed : {
		options () {
			return this.aiStore[this.optionsKey]
		}
	},
	beforeMount () {
		this.options.tone     = this.optionsStore.options.aiContent.tone
		this.options.audience = this.optionsStore.options.aiContent.audience
	}
}

</script>

<style lang="scss">
.aioseo-ai-content-style-form {
	.field-group {
		margin-bottom: 32px;

		label {
			color: $black;
			display: block;
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 8px;
		}
	}

	.aioseo-alert a {
		text-decoration: underline;
	}
}
</style>