<template>
	<div class="aioseo-ai-assistant">
		<div class="aioseo-ai-header">
			<svg-ai class="aioseo-ai-icon" />
			<h3>{{ strings.title }}</h3>
			<base-toggle
				v-model="isExpanded"
				size="small"
			/>
		</div>

		<transition name="aioseo-expand">
			<div v-if="isExpanded" class="aioseo-ai-content">
				<!-- Provider Selection -->
				<div class="aioseo-ai-provider">
					<label>{{ strings.provider }}</label>
					<base-select
						v-model="selectedProvider"
						:options="providerOptions"
						size="small"
					/>
				</div>

				<!-- AI Actions Tabs -->
				<div class="aioseo-ai-tabs">
					<button
						v-for="tab in tabs"
						:key="tab.id"
						:class="['aioseo-ai-tab', { active: activeTab === tab.id }]"
						@click="activeTab = tab.id"
					>
						{{ tab.label }}
					</button>
				</div>

				<!-- Content Generation Tab -->
				<div v-if="activeTab === 'generate'" class="aioseo-ai-panel">
					<base-textarea
						v-model="generatePrompt"
						:placeholder="strings.generatePromptPlaceholder"
						rows="3"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="generateContent"
						:loading="generating"
						:disabled="!generatePrompt"
					>
						{{ strings.generate }}
					</base-button>

					<div v-if="generatedContent" class="aioseo-ai-result">
						<div class="aioseo-result-actions">
							<base-button
								type="gray"
								size="small"
								@click="insertContent"
							>
								{{ strings.insert }}
							</base-button>
							<base-button
								type="gray"
								size="small"
								@click="copyContent"
							>
								{{ strings.copy }}
							</base-button>
							<base-button
								type="gray"
								size="small"
								@click="generatedContent = null"
							>
								{{ strings.clear }}
							</base-button>
						</div>
						<div class="aioseo-result-text" v-html="generatedContent"></div>
					</div>
				</div>

				<!-- SEO Analysis Tab -->
				<div v-if="activeTab === 'analyze'" class="aioseo-ai-panel">
					<p class="aioseo-helper-text">{{ strings.analyzeHelper }}</p>

					<base-input
						v-model="targetKeyword"
						:placeholder="strings.targetKeyword"
						size="medium"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="analyzeSeo"
						:loading="analyzing"
					>
						{{ strings.analyze }}
					</base-button>

					<div v-if="seoAnalysis" class="aioseo-ai-result">
						<div class="aioseo-seo-score">
							<div class="aioseo-score-circle" :class="getScoreClass(seoAnalysis.score)">
								<span class="aioseo-score-value">{{ seoAnalysis.score }}</span>
								<span class="aioseo-score-label">/100</span>
							</div>
							<div class="aioseo-score-info">
								<h4>{{ strings.seoScore }}</h4>
								<p>{{ getScoreLabel(seoAnalysis.score) }}</p>
							</div>
						</div>

						<div v-if="seoAnalysis.recommendations" class="aioseo-recommendations">
							<h4>{{ strings.recommendations }}</h4>
							<ul>
								<li v-for="(rec, index) in seoAnalysis.recommendations" :key="index">
									{{ rec }}
								</li>
							</ul>
						</div>

						<div v-if="seoAnalysis.meta_description" class="aioseo-suggested-meta">
							<h4>{{ strings.suggestedMetaDescription }}</h4>
							<p>{{ seoAnalysis.meta_description }}</p>
							<base-button
								type="gray"
								size="small"
								@click="applyMetaDescription(seoAnalysis.meta_description)"
							>
								{{ strings.apply }}
							</base-button>
						</div>

						<div v-if="seoAnalysis.title" class="aioseo-suggested-title">
							<h4>{{ strings.suggestedTitle }}</h4>
							<p>{{ seoAnalysis.title }}</p>
							<base-button
								type="gray"
								size="small"
								@click="applyTitle(seoAnalysis.title)"
							>
								{{ strings.apply }}
							</base-button>
						</div>
					</div>
				</div>

				<!-- Keyword Research Tab -->
				<div v-if="activeTab === 'keywords'" class="aioseo-ai-panel">
					<base-input
						v-model="researchTopic"
						:placeholder="strings.researchTopicPlaceholder"
						size="medium"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="researchKeywords"
						:loading="researching"
						:disabled="!researchTopic"
					>
						{{ strings.research }}
					</base-button>

					<div v-if="keywordData" class="aioseo-ai-result">
						<div v-if="keywordData.primary_keywords" class="aioseo-keyword-section">
							<h4>{{ strings.primaryKeywords }}</h4>
							<div class="aioseo-keyword-tags">
								<span
									v-for="(kw, index) in keywordData.primary_keywords"
									:key="index"
									class="aioseo-keyword-tag"
									@click="addKeyword(kw)"
								>
									{{ typeof kw === 'object' ? kw.keyword : kw }}
								</span>
							</div>
						</div>

						<div v-if="keywordData.long_tail" class="aioseo-keyword-section">
							<h4>{{ strings.longTailKeywords }}</h4>
							<div class="aioseo-keyword-tags">
								<span
									v-for="(kw, index) in keywordData.long_tail"
									:key="index"
									class="aioseo-keyword-tag"
									@click="addKeyword(kw)"
								>
									{{ kw }}
								</span>
							</div>
						</div>

						<div v-if="keywordData.questions" class="aioseo-keyword-section">
							<h4>{{ strings.questionKeywords }}</h4>
							<ul class="aioseo-question-list">
								<li v-for="(q, index) in keywordData.questions" :key="index">
									{{ q }}
								</li>
							</ul>
						</div>
					</div>
				</div>

				<!-- AEO Optimization Tab -->
				<div v-if="activeTab === 'aeo'" class="aioseo-ai-panel">
					<p class="aioseo-helper-text">{{ strings.aeoHelper }}</p>

					<base-textarea
						v-model="aeoQuestions"
						:placeholder="strings.aeoQuestionsPlaceholder"
						rows="4"
					/>

					<base-button
						type="blue"
						size="medium"
						@click="optimizeForAeo"
						:loading="optimizing"
					>
						{{ strings.optimize }}
					</base-button>

					<div v-if="aeoContent" class="aioseo-ai-result">
						<div class="aioseo-result-actions">
							<base-button
								type="gray"
								size="small"
								@click="insertContent(aeoContent)"
							>
								{{ strings.insert }}
							</base-button>
							<base-button
								type="gray"
								size="small"
								@click="copyContent(aeoContent)"
							>
								{{ strings.copy }}
							</base-button>
						</div>
						<div class="aioseo-result-text" v-html="aeoContent"></div>
					</div>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
import { defineComponent } from 'vue'
import BaseToggle from '@/vue/components/common/base/Toggle'
import BaseSelect from '@/vue/components/common/base/Select'
import BaseButton from '@/vue/components/common/base/Button'
import BaseInput from '@/vue/components/common/base/Input'
import BaseTextarea from '@/vue/components/common/base/Textarea'
import SvgAi from '@/vue/components/common/svg/Ai'

export default defineComponent({
	components: {
		BaseToggle,
		BaseSelect,
		BaseButton,
		BaseInput,
		BaseTextarea,
		SvgAi
	},
	data() {
		return {
			isExpanded: false,
			selectedProvider: 'openai',
			activeTab: 'generate',
			generatePrompt: '',
			targetKeyword: '',
			researchTopic: '',
			aeoQuestions: '',
			generating: false,
			analyzing: false,
			researching: false,
			optimizing: false,
			generatedContent: null,
			seoAnalysis: null,
			keywordData: null,
			aeoContent: null,
			strings: {
				title: this.$t.__('AI Assistant', this.$td),
				provider: this.$t.__('Provider', this.$td),
				generate: this.$t.__('Generate', this.$td),
				analyze: this.$t.__('Analyze', this.$td),
				research: this.$t.__('Research', this.$td),
				optimize: this.$t.__('Optimize', this.$td),
				insert: this.$t.__('Insert into Editor', this.$td),
				copy: this.$t.__('Copy', this.$td),
				clear: this.$t.__('Clear', this.$td),
				apply: this.$t.__('Apply', this.$td),
				generatePromptPlaceholder: this.$t.__('Describe what content you want to generate...', this.$td),
				analyzeHelper: this.$t.__('Analyze your current content for SEO improvements', this.$td),
				targetKeyword: this.$t.__('Target keyword (optional)', this.$td),
				seoScore: this.$t.__('SEO Score', this.$td),
				recommendations: this.$t.__('Recommendations', this.$td),
				suggestedMetaDescription: this.$t.__('Suggested Meta Description', this.$td),
				suggestedTitle: this.$t.__('Suggested Title', this.$td),
				researchTopicPlaceholder: this.$t.__('Enter topic for keyword research', this.$td),
				primaryKeywords: this.$t.__('Primary Keywords', this.$td),
				longTailKeywords: this.$t.__('Long-tail Keywords', this.$td),
				questionKeywords: this.$t.__('Question Keywords', this.$td),
				aeoHelper: this.$t.__('Optimize content for AI search engines and voice assistants', this.$td),
				aeoQuestionsPlaceholder: this.$t.__('Enter questions to address (one per line)', this.$td)
			},
			tabs: [
				{ id: 'generate', label: this.$t.__('Generate', this.$td) },
				{ id: 'analyze', label: this.$t.__('SEO Analysis', this.$td) },
				{ id: 'keywords', label: this.$t.__('Keywords', this.$td) },
				{ id: 'aeo', label: this.$t.__('AEO', this.$td) }
			],
			providerOptions: [
				{ label: 'OpenAI', value: 'openai' },
				{ label: 'Claude', value: 'claude' },
				{ label: 'Gemini', value: 'gemini' }
			]
		}
	},
	methods: {
		async generateContent() {
			this.generating = true
			this.generatedContent = null

			try {
				const response = await this.apiRequest('ai/generate-content', {
					prompt: this.generatePrompt,
					provider: this.selectedProvider,
					postId: this.getPostId()
				})

				if (response.success) {
					this.generatedContent = response.content
				} else {
					alert('Error: ' + (response.error || 'Generation failed'))
				}
			} catch (error) {
				alert('Error: ' + error.message)
			} finally {
				this.generating = false
			}
		},
		async analyzeSeo() {
			this.analyzing = true
			this.seoAnalysis = null

			try {
				const content = this.getPostContent()
				const response = await this.apiRequest('ai/analyze-seo', {
					content: content,
					keyword: this.targetKeyword,
					provider: this.selectedProvider,
					postId: this.getPostId()
				})

				if (response.success) {
					// Try to parse JSON if returned
					try {
						const analysis = typeof response.content === 'string' ? JSON.parse(response.content) : response.content
						this.seoAnalysis = analysis
					} catch (e) {
						// If not JSON, treat as plain text
						this.seoAnalysis = { raw: response.content }
					}
				} else {
					alert('Error: ' + (response.error || 'Analysis failed'))
				}
			} catch (error) {
				alert('Error: ' + error.message)
			} finally {
				this.analyzing = false
			}
		},
		async researchKeywords() {
			this.researching = true
			this.keywordData = null

			try {
				const response = await this.apiRequest('ai/research-keywords', {
					topic: this.researchTopic,
					provider: this.selectedProvider,
					postId: this.getPostId()
				})

				if (response.success) {
					try {
						const data = typeof response.content === 'string' ? JSON.parse(response.content) : response.content
						this.keywordData = data
					} catch (e) {
						alert('Error parsing keyword data')
					}
				} else {
					alert('Error: ' + (response.error || 'Research failed'))
				}
			} catch (error) {
				alert('Error: ' + error.message)
			} finally {
				this.researching = false
			}
		},
		async optimizeForAeo() {
			this.optimizing = true
			this.aeoContent = null

			try {
				const content = this.getPostContent()
				const questions = this.aeoQuestions.split('\n').filter(q => q.trim())

				const response = await this.apiRequest('ai/optimize-aeo', {
					content: content,
					questions: questions,
					provider: this.selectedProvider,
					postId: this.getPostId()
				})

				if (response.success) {
					this.aeoContent = response.content
				} else {
					alert('Error: ' + (response.error || 'Optimization failed'))
				}
			} catch (error) {
				alert('Error: ' + error.message)
			} finally {
				this.optimizing = false
			}
		},
		insertContent(content = null) {
			const contentToInsert = content || this.generatedContent

			if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
				// Gutenberg
				const { insertBlocks } = wp.data.dispatch('core/block-editor')
				const { createBlock } = wp.blocks
				insertBlocks(createBlock('core/paragraph', { content: contentToInsert }))
			} else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
				// Classic editor
				tinymce.activeEditor.insertContent(contentToInsert)
			}
		},
		copyContent(content = null) {
			const contentToCopy = content || this.generatedContent
			navigator.clipboard.writeText(contentToCopy).then(() => {
				alert('Content copied to clipboard!')
			})
		},
		applyMetaDescription(description) {
			// Apply to AIOSEO meta description field
			// This would need to integrate with the main post settings store
			console.log('Apply meta description:', description)
		},
		applyTitle(title) {
			// Apply to AIOSEO title field
			console.log('Apply title:', title)
		},
		addKeyword(keyword) {
			const kw = typeof keyword === 'object' ? keyword.keyword : keyword
			// Add to focus keyphrase
			console.log('Add keyword:', kw)
		},
		getPostContent() {
			if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
				const content = wp.data.select('core/editor').getEditedPostContent()
				return content
			} else if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
				return tinymce.activeEditor.getContent()
			}
			return ''
		},
		getPostId() {
			if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
				return wp.data.select('core/editor').getCurrentPostId()
			}
			return window.aioseo?.currentPost?.id || 0
		},
		async apiRequest(endpoint, data) {
			const response = await fetch(`${window.aioseo.urls.restUrl}aioseo/v1/${endpoint}`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.aioseo.nonce
				},
				body: JSON.stringify(data)
			})

			return await response.json()
		},
		getScoreClass(score) {
			if (score >= 80) return 'good'
			if (score >= 60) return 'okay'
			return 'poor'
		},
		getScoreLabel(score) {
			if (score >= 80) return this.$t.__('Excellent', this.$td)
			if (score >= 60) return this.$t.__('Good', this.$td)
			if (score >= 40) return this.$t.__('Needs Improvement', this.$td)
			return this.$t.__('Poor', this.$td)
		}
	}
})
</script>

<style lang="scss" scoped>
.aioseo-ai-assistant {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	margin-bottom: 20px;

	.aioseo-ai-header {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 16px;
		border-bottom: 1px solid #e0e0e0;
		cursor: pointer;

		.aioseo-ai-icon {
			width: 24px;
			height: 24px;
			fill: #0073aa;
		}

		h3 {
			flex: 1;
			margin: 0;
			font-size: 16px;
			font-weight: 600;
		}
	}

	.aioseo-ai-content {
		padding: 20px;
	}

	.aioseo-ai-provider {
		margin-bottom: 20px;

		label {
			display: block;
			font-size: 13px;
			font-weight: 600;
			margin-bottom: 8px;
		}
	}

	.aioseo-ai-tabs {
		display: flex;
		gap: 4px;
		margin-bottom: 20px;
		border-bottom: 2px solid #e0e0e0;
	}

	.aioseo-ai-tab {
		background: none;
		border: none;
		padding: 10px 16px;
		font-size: 14px;
		font-weight: 500;
		color: #666;
		cursor: pointer;
		border-bottom: 2px solid transparent;
		margin-bottom: -2px;
		transition: all 0.2s ease;

		&:hover {
			color: #0073aa;
		}

		&.active {
			color: #0073aa;
			border-bottom-color: #0073aa;
		}
	}

	.aioseo-ai-panel {
		display: flex;
		flex-direction: column;
		gap: 16px;
	}

	.aioseo-helper-text {
		font-size: 13px;
		color: #666;
		margin: 0;
	}

	.aioseo-ai-result {
		background: #f9f9f9;
		border: 1px solid #e0e0e0;
		border-radius: 6px;
		padding: 16px;
		margin-top: 16px;

		.aioseo-result-actions {
			display: flex;
			gap: 8px;
			margin-bottom: 12px;
		}

		.aioseo-result-text {
			background: #fff;
			padding: 16px;
			border-radius: 4px;
			line-height: 1.6;
			font-size: 14px;
		}
	}

	.aioseo-seo-score {
		display: flex;
		align-items: center;
		gap: 20px;
		margin-bottom: 20px;

		.aioseo-score-circle {
			width: 80px;
			height: 80px;
			border-radius: 50%;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			font-weight: 700;
			border: 4px solid;

			&.good {
				border-color: #46b450;
				color: #46b450;
			}

			&.okay {
				border-color: #ffb900;
				color: #ffb900;
			}

			&.poor {
				border-color: #dc3232;
				color: #dc3232;
			}

			.aioseo-score-value {
				font-size: 28px;
			}

			.aioseo-score-label {
				font-size: 12px;
			}
		}

		.aioseo-score-info {
			h4 {
				margin: 0 0 4px 0;
				font-size: 16px;
			}

			p {
				margin: 0;
				font-size: 13px;
				color: #666;
			}
		}
	}

	.aioseo-recommendations {
		margin-bottom: 16px;

		h4 {
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 8px;
		}

		ul {
			margin: 0;
			padding-left: 20px;

			li {
				margin-bottom: 6px;
				font-size: 13px;
				line-height: 1.5;
			}
		}
	}

	.aioseo-suggested-meta,
	.aioseo-suggested-title {
		background: #fff;
		padding: 12px;
		border-radius: 4px;
		margin-bottom: 12px;

		h4 {
			font-size: 13px;
			font-weight: 600;
			margin: 0 0 8px 0;
		}

		p {
			margin: 0 0 8px 0;
			font-size: 13px;
			line-height: 1.5;
		}
	}

	.aioseo-keyword-section {
		margin-bottom: 16px;

		h4 {
			font-size: 14px;
			font-weight: 600;
			margin-bottom: 8px;
		}
	}

	.aioseo-keyword-tags {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	.aioseo-keyword-tag {
		background: #0073aa;
		color: #fff;
		padding: 6px 12px;
		border-radius: 4px;
		font-size: 13px;
		cursor: pointer;
		transition: all 0.2s ease;

		&:hover {
			background: #005a87;
			transform: translateY(-2px);
		}
	}

	.aioseo-question-list {
		margin: 0;
		padding-left: 20px;

		li {
			margin-bottom: 8px;
			font-size: 13px;
			line-height: 1.5;
		}
	}
}

.aioseo-expand-enter-active,
.aioseo-expand-leave-active {
	transition: all 0.3s ease;
	max-height: 2000px;
	overflow: hidden;
}

.aioseo-expand-enter-from,
.aioseo-expand-leave-to {
	max-height: 0;
	opacity: 0;
}
</style>
