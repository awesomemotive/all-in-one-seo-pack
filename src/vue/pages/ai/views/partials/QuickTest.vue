<template>
	<div class="aioseo-ai-quick-test">
		<p class="aioseo-description">
			{{ strings.description }}
		</p>

		<div class="aioseo-test-form">
			<base-select
				v-model="selectedProvider"
				:options="providerOptions"
				size="medium"
				:placeholder="strings.selectProvider"
			/>

			<base-textarea
				v-model="testPrompt"
				:placeholder="strings.enterPrompt"
				rows="4"
			/>

			<base-button
				type="blue"
				size="medium"
				@click="runTest"
				:loading="testing"
				:disabled="!selectedProvider || !testPrompt"
			>
				<svg-external width="14" height="14" />
				{{ strings.runTest }}
			</base-button>
		</div>

		<div v-if="testResult" class="aioseo-test-result">
			<div class="aioseo-result-header">
				<h4>{{ strings.result }}</h4>
				<span :class="['aioseo-result-status', testResult.success ? 'success' : 'error']">
					{{ testResult.success ? strings.success : strings.failed }}
				</span>
			</div>

			<div v-if="testResult.success" class="aioseo-result-content">
				<div class="aioseo-result-meta">
					<span><strong>{{ strings.provider }}:</strong> {{ capitalizeFirst(testResult.provider) }}</span>
					<span v-if="testResult.tokensUsed"><strong>{{ strings.tokens }}:</strong> {{ testResult.tokensUsed }}</span>
					<span v-if="testResult.duration"><strong>{{ strings.duration }}:</strong> {{ testResult.duration }}ms</span>
				</div>

				<div class="aioseo-result-text">
					{{ testResult.content }}
				</div>
			</div>

			<div v-else class="aioseo-result-error">
				<svg-close class="aioseo-error-icon" />
				<p>{{ testResult.error }}</p>
			</div>
		</div>
	</div>
</template>

<script>
import { defineComponent } from 'vue'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import BaseButton from '@/vue/components/common/base/Button'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgClose from '@/vue/components/common/svg/Close'

export default defineComponent({
	components: {
		BaseSelect,
		BaseTextarea,
		BaseButton,
		SvgExternal,
		SvgClose
	},
	data() {
		return {
			selectedProvider: '',
			testPrompt: 'Write a short paragraph about the benefits of SEO.',
			testing: false,
			testResult: null,
			strings: {
				description: this.$t.__('Test your AI providers by generating sample content. This helps verify your API keys are working correctly.', this.$td),
				selectProvider: this.$t.__('Select Provider', this.$td),
				enterPrompt: this.$t.__('Enter a test prompt...', this.$td),
				runTest: this.$t.__('Run Test', this.$td),
				result: this.$t.__('Test Result', this.$td),
				success: this.$t.__('Success', this.$td),
				failed: this.$t.__('Failed', this.$td),
				provider: this.$t.__('Provider', this.$td),
				tokens: this.$t.__('Tokens Used', this.$td),
				duration: this.$t.__('Duration', this.$td)
			},
			providerOptions: [
				{ label: 'OpenAI', value: 'openai' },
				{ label: 'Claude', value: 'claude' },
				{ label: 'Google Gemini', value: 'gemini' }
			]
		}
	},
	methods: {
		async runTest() {
			this.testing = true
			this.testResult = null

			const startTime = Date.now()

			try {
				const response = await fetch(`${window.aioseo.urls.restUrl}aioseo/v1/ai/generate-content`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': window.aioseo.nonce
					},
					body: JSON.stringify({
						prompt: this.testPrompt,
						provider: this.selectedProvider
					})
				})

				const data = await response.json()
				const duration = Date.now() - startTime

				if (data.success) {
					this.testResult = {
						success: true,
						provider: this.selectedProvider,
						content: data.content,
						tokensUsed: data.usage?.total_tokens || null,
						duration: duration
					}
				} else {
					this.testResult = {
						success: false,
						error: data.error || 'Unknown error occurred'
					}
				}
			} catch (error) {
				console.error('Test failed:', error)
				this.testResult = {
					success: false,
					error: error.message || 'Network error occurred'
				}
			} finally {
				this.testing = false
			}
		},
		capitalizeFirst(str) {
			return str.charAt(0).toUpperCase() + str.slice(1)
		}
	}
})
</script>

<style lang="scss" scoped>
.aioseo-ai-quick-test {
	.aioseo-description {
		font-size: 14px;
		color: #666;
		margin-bottom: 20px;
		line-height: 1.6;
	}

	.aioseo-test-form {
		display: flex;
		flex-direction: column;
		gap: 16px;
		margin-bottom: 24px;
	}

	.aioseo-test-result {
		background: #f9f9f9;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		padding: 20px;

		.aioseo-result-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 16px;
			padding-bottom: 12px;
			border-bottom: 1px solid #e0e0e0;

			h4 {
				font-size: 16px;
				font-weight: 600;
				margin: 0;
			}

			.aioseo-result-status {
				padding: 4px 12px;
				border-radius: 4px;
				font-size: 12px;
				font-weight: 600;
				text-transform: uppercase;

				&.success {
					background: #d4edda;
					color: #155724;
				}

				&.error {
					background: #f8d7da;
					color: #721c24;
				}
			}
		}

		.aioseo-result-meta {
			display: flex;
			flex-wrap: wrap;
			gap: 16px;
			margin-bottom: 16px;
			font-size: 13px;
			color: #666;

			strong {
				color: #23282d;
			}
		}

		.aioseo-result-text {
			background: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 4px;
			padding: 16px;
			font-size: 14px;
			line-height: 1.6;
			color: #23282d;
			white-space: pre-wrap;
			word-wrap: break-word;
		}

		.aioseo-result-error {
			display: flex;
			align-items: flex-start;
			gap: 12px;
			color: #721c24;

			.aioseo-error-icon {
				width: 20px;
				height: 20px;
				flex-shrink: 0;
				margin-top: 2px;
				fill: currentColor;
			}

			p {
				margin: 0;
				font-size: 14px;
				line-height: 1.6;
			}
		}
	}
}
</style>
