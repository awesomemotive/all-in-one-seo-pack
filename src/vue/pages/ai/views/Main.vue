<template>
	<div class="aioseo-ai-settings">
		<core-card
			slug="aioseoAiSettings"
			:header-text="strings.title"
		>
			<template #header-icon>
				<svg-ai />
			</template>

			<!-- AI Feature Toggle -->
			<core-settings-row
				:name="strings.enableAi"
				align
			>
				<template #content>
					<base-toggle
						v-model="options.ai.enabled"
						@update:modelValue="saveSettings"
					/>
				</template>
			</core-settings-row>

			<!-- Provider Selection -->
			<core-settings-row
				v-if="options.ai.enabled"
				:name="strings.defaultProvider"
				align
			>
				<template #content>
					<base-select
						size="medium"
						v-model="options.ai.defaultProvider"
						:options="providerOptions"
						@update:modelValue="saveSettings"
					/>
				</template>
			</core-settings-row>

			<!-- OpenAI Settings -->
			<div v-if="options.ai.enabled" class="aioseo-ai-provider-section">
				<h3>{{ strings.openaiSettings }}</h3>

				<core-settings-row
					:name="strings.apiKey"
					align
				>
					<template #content>
						<base-input
							type="password"
							size="medium"
							v-model="options.ai.openaiApiKey"
							:placeholder="strings.enterApiKey"
							@blur="saveSettings"
						/>
						<base-button
							type="blue"
							size="small"
							@click="testApiKey('openai')"
							:loading="testing.openai"
							class="aioseo-test-key-btn"
						>
							{{ strings.testKey }}
						</base-button>
						<div v-if="keyStatus.openai" :class="['aioseo-key-status', keyStatus.openai]">
							{{ keyStatusText.openai }}
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.model"
					align
				>
					<template #content>
						<base-select
							size="medium"
							v-model="options.ai.openaiModel"
							:options="openaiModelOptions"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>
			</div>

			<!-- Claude Settings -->
			<div v-if="options.ai.enabled" class="aioseo-ai-provider-section">
				<h3>{{ strings.claudeSettings }}</h3>

				<core-settings-row
					:name="strings.apiKey"
					align
				>
					<template #content>
						<base-input
							type="password"
							size="medium"
							v-model="options.ai.claudeApiKey"
							:placeholder="strings.enterApiKey"
							@blur="saveSettings"
						/>
						<base-button
							type="blue"
							size="small"
							@click="testApiKey('claude')"
							:loading="testing.claude"
							class="aioseo-test-key-btn"
						>
							{{ strings.testKey }}
						</base-button>
						<div v-if="keyStatus.claude" :class="['aioseo-key-status', keyStatus.claude]">
							{{ keyStatusText.claude }}
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.model"
					align
				>
					<template #content>
						<base-select
							size="medium"
							v-model="options.ai.claudeModel"
							:options="claudeModelOptions"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>
			</div>

			<!-- Gemini Settings -->
			<div v-if="options.ai.enabled" class="aioseo-ai-provider-section">
				<h3>{{ strings.geminiSettings }}</h3>

				<core-settings-row
					:name="strings.apiKey"
					align
				>
					<template #content>
						<base-input
							type="password"
							size="medium"
							v-model="options.ai.geminiApiKey"
							:placeholder="strings.enterApiKey"
							@blur="saveSettings"
						/>
						<base-button
							type="blue"
							size="small"
							@click="testApiKey('gemini')"
							:loading="testing.gemini"
							class="aioseo-test-key-btn"
						>
							{{ strings.testKey }}
						</base-button>
						<div v-if="keyStatus.gemini" :class="['aioseo-key-status', keyStatus.gemini]">
							{{ keyStatusText.gemini }}
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.model"
					align
				>
					<template #content>
						<base-select
							size="medium"
							v-model="options.ai.geminiModel"
							:options="geminiModelOptions"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>
			</div>

			<!-- Advanced Settings -->
			<div v-if="options.ai.enabled" class="aioseo-ai-advanced-section">
				<h3>{{ strings.advancedSettings }}</h3>

				<core-settings-row
					:name="strings.temperature"
					align
				>
					<template #content>
						<base-input
							type="number"
							size="medium"
							v-model.number="options.ai.temperature"
							:min="0"
							:max="1"
							:step="0.1"
							@blur="saveSettings"
						/>
						<p class="aioseo-description">{{ strings.temperatureDesc }}</p>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.maxTokens"
					align
				>
					<template #content>
						<base-input
							type="number"
							size="medium"
							v-model.number="options.ai.maxTokens"
							:min="100"
							:max="4000"
							:step="100"
							@blur="saveSettings"
						/>
						<p class="aioseo-description">{{ strings.maxTokensDesc }}</p>
					</template>
				</core-settings-row>
			</div>

			<!-- Feature Toggles -->
			<div v-if="options.ai.enabled" class="aioseo-ai-features-section">
				<h3>{{ strings.features }}</h3>

				<core-settings-row
					:name="strings.contentGeneration"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.features.contentGeneration"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.seoAnalysis"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.features.seoAnalysis"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.keywordResearch"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.features.keywordResearch"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.aeoOptimization"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.features.aeoOptimization"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.elementorIntegration"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.features.elementorIntegration"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>
			</div>

			<!-- Elementor Settings -->
			<div v-if="options.ai.enabled && options.ai.features.elementorIntegration" class="aioseo-ai-elementor-section">
				<h3>{{ strings.elementorSettings }}</h3>

				<core-settings-row
					:name="strings.enableAiWidgets"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.elementor.enableAiWidgets"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.autoSuggestions"
					align
				>
					<template #content>
						<base-toggle
							v-model="options.ai.elementor.autoSuggestions"
							@update:modelValue="saveSettings"
						/>
					</template>
				</core-settings-row>
			</div>

			<!-- AI Usage Stats -->
			<div v-if="options.ai.enabled" class="aioseo-ai-stats-section">
				<h3>{{ strings.usageStats }}</h3>
				<ai-usage-stats />
			</div>
		</core-card>

		<!-- Quick Test Panel -->
		<core-card
			v-if="options.ai.enabled"
			slug="aioseoAiQuickTest"
			:header-text="strings.quickTest"
		>
			<ai-quick-test />
		</core-card>
	</div>
</template>

<script>
import { defineComponent } from 'vue'
import { useOptionsStore } from '@/vue/stores'
import CoreCard from '@/vue/components/common/core/Card'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import BaseToggle from '@/vue/components/common/base/Toggle'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseInput from '@/vue/components/common/base/Input'
import BaseButton from '@/vue/components/common/base/Button'
import SvgAi from '@/vue/components/common/svg/Ai'
import AiUsageStats from './partials/UsageStats'
import AiQuickTest from './partials/QuickTest'

export default defineComponent({
	components: {
		CoreCard,
		CoreSettingsRow,
		BaseToggle,
		BaseSelect,
		BaseInput,
		BaseButton,
		SvgAi,
		AiUsageStats,
		AiQuickTest
	},
	setup() {
		const optionsStore = useOptionsStore()

		return {
			options: optionsStore.options,
			optionsStore
		}
	},
	data() {
		return {
			testing: {
				openai: false,
				claude: false,
				gemini: false
			},
			keyStatus: {
				openai: null,
				claude: null,
				gemini: null
			},
			keyStatusText: {
				openai: '',
				claude: '',
				gemini: ''
			},
			strings: {
				title: this.$t.__('AI Settings', this.$td),
				enableAi: this.$t.__('Enable AI Features', this.$td),
				defaultProvider: this.$t.__('Default AI Provider', this.$td),
				openaiSettings: this.$t.__('OpenAI Settings', this.$td),
				claudeSettings: this.$t.__('Claude Settings', this.$td),
				geminiSettings: this.$t.__('Google Gemini Settings', this.$td),
				apiKey: this.$t.__('API Key', this.$td),
				enterApiKey: this.$t.__('Enter your API key', this.$td),
				testKey: this.$t.__('Test Key', this.$td),
				model: this.$t.__('Model', this.$td),
				advancedSettings: this.$t.__('Advanced Settings', this.$td),
				temperature: this.$t.__('Temperature', this.$td),
				temperatureDesc: this.$t.__('Controls randomness: 0 is focused and deterministic, 1 is more creative (0-1)', this.$td),
				maxTokens: this.$t.__('Max Tokens', this.$td),
				maxTokensDesc: this.$t.__('Maximum length of generated content (100-4000)', this.$td),
				features: this.$t.__('Feature Toggles', this.$td),
				contentGeneration: this.$t.__('Content Generation', this.$td),
				seoAnalysis: this.$t.__('SEO Analysis', this.$td),
				keywordResearch: this.$t.__('Keyword Research', this.$td),
				aeoOptimization: this.$t.__('AEO Optimization', this.$td),
				elementorIntegration: this.$t.__('Elementor Integration', this.$td),
				elementorSettings: this.$t.__('Elementor Settings', this.$td),
				enableAiWidgets: this.$t.__('Enable AI Widgets', this.$td),
				autoSuggestions: this.$t.__('Auto Suggestions', this.$td),
				usageStats: this.$t.__('Usage Statistics', this.$td),
				quickTest: this.$t.__('Quick Test', this.$td),
				keyValid: this.$t.__('API key is valid!', this.$td),
				keyInvalid: this.$t.__('API key is invalid', this.$td),
				testFailed: this.$t.__('Test failed. Please check your key.', this.$td)
			},
			providerOptions: [
				{ label: 'OpenAI', value: 'openai' },
				{ label: 'Claude', value: 'claude' },
				{ label: 'Google Gemini', value: 'gemini' }
			],
			openaiModelOptions: [
				{ label: 'GPT-4o', value: 'gpt-4o' },
				{ label: 'GPT-4o Mini', value: 'gpt-4o-mini' },
				{ label: 'GPT-4 Turbo', value: 'gpt-4-turbo' }
			],
			claudeModelOptions: [
				{ label: 'Claude 3.5 Sonnet', value: 'claude-3-5-sonnet-20241022' },
				{ label: 'Claude 3 Opus', value: 'claude-3-opus-20240229' },
				{ label: 'Claude 3 Sonnet', value: 'claude-3-sonnet-20240229' },
				{ label: 'Claude 3 Haiku', value: 'claude-3-haiku-20240307' }
			],
			geminiModelOptions: [
				{ label: 'Gemini 1.5 Pro', value: 'gemini-1.5-pro' },
				{ label: 'Gemini 1.5 Flash', value: 'gemini-1.5-flash' },
				{ label: 'Gemini 1.0 Pro', value: 'gemini-1.0-pro' }
			]
		}
	},
	methods: {
		saveSettings() {
			this.optionsStore.saveOptions()
		},
		async testApiKey(provider) {
			this.testing[provider] = true
			this.keyStatus[provider] = null

			try {
				const apiKey = this.options.ai[`${provider}ApiKey`]

				if (!apiKey) {
					this.keyStatus[provider] = 'error'
					this.keyStatusText[provider] = this.$t.__('Please enter an API key first', this.$td)
					return
				}

				const response = await fetch(`${window.aioseo.urls.restUrl}aioseo/v1/ai/validate-api-key`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': window.aioseo.nonce
					},
					body: JSON.stringify({
						provider: provider,
						apiKey: apiKey
					})
				})

				const data = await response.json()

				if (data.valid) {
					this.keyStatus[provider] = 'success'
					this.keyStatusText[provider] = this.strings.keyValid
				} else {
					this.keyStatus[provider] = 'error'
					this.keyStatusText[provider] = data.error || this.strings.keyInvalid
				}
			} catch (error) {
				console.error('API key test failed:', error)
				this.keyStatus[provider] = 'error'
				this.keyStatusText[provider] = this.strings.testFailed
			} finally {
				this.testing[provider] = false
			}
		}
	}
})
</script>

<style lang="scss" scoped>
.aioseo-ai-settings {
	.aioseo-ai-provider-section,
	.aioseo-ai-advanced-section,
	.aioseo-ai-features-section,
	.aioseo-ai-elementor-section,
	.aioseo-ai-stats-section {
		margin-top: 30px;
		padding-top: 30px;
		border-top: 1px solid #e0e0e0;

		h3 {
			font-size: 16px;
			font-weight: 600;
			margin-bottom: 20px;
			color: #23282d;
		}
	}

	.aioseo-test-key-btn {
		margin-left: 12px;
	}

	.aioseo-key-status {
		margin-top: 8px;
		padding: 8px 12px;
		border-radius: 4px;
		font-size: 13px;

		&.success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}

		&.error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
	}

	.aioseo-description {
		font-size: 13px;
		color: #666;
		margin-top: 6px;
		font-style: italic;
	}
}
</style>
