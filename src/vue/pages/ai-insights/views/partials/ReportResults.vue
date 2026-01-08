<template>
	<div class="report-results-partial">
		<core-card
			slug="ai-engine-results"
			:toggles="false"
			no-slide
		>
			<template #header>
				{{ strings.aiEngineResults }}
			</template>

			<template #tooltip>
				{{ strings.tooltip }}
			</template>

			<div class="providers-list">
				<div
					v-for="provider in providers"
					:key="provider.key"
					class="provider-entry"
					:class="{ 'is-expanded': expandedProvider === provider.key }"
					@click="toggleProvider(provider.key)"
				>
					<div class="provider-entry-main">
						<div class="provider-info">
							<div class="provider-icon">
								<svg-anthropic v-if="'anthropic' === provider.key" />
								<svg-deep-seek v-else-if="'deepseek' === provider.key" />
								<svg-perplexity v-else-if="'perplexity' === provider.key" />
								<svg-gemini v-else-if="'gemini' === provider.key" />
								<svg-open-a-i v-else-if="'openai' === provider.key" />
							</div>
							<span class="provider-name">{{ provider.name }}</span>
						</div>

						<div class="provider-metrics">
							<div
								v-if="provider.sourcesCount"
								class="metric-badge"
							>
								<svg-globe class="badge-icon" />
								<span>{{ sprintf(strings.sourcesCount, provider.sourcesCount) }}</span>
							</div>
							<div class="metric-badge">
								<span>{{ sprintf(strings.brandsCount, provider.brandsCount) }}</span>
							</div>
							<button class="expand-button">
								<svg-right-arrow :class="{ 'rotated': expandedProvider === provider.key }" />
							</button>
						</div>
					</div>

					<transition-slide
						:active="expandedProvider === provider.key"
					>
						<div
							v-if="expandedProvider === provider.key"
							class="provider-details"
							@click.stop
						>
							<div
								v-if="provider.data.brands && 0 < provider.data.brands.length"
								class="brands-list-section"
							>
								<div
									v-for="brand in provider.data.brands"
									:key="brand.name"
									class="brand-tag"
								>
									{{ brand.name }} ({{ sprintf('#%1$s', brand.position) }})
								</div>
							</div>

							<div
								v-if="provider.data.overview"
								class="provider-overview"
								v-html="provider.data.overview"
							/>

							<div
								v-if="provider.sourcesCount && provider.data.sources && 0 < provider.data.sources.length"
								class="sources-section"
							>
								<div class="sources-header">
									<h3 class="sources-title">
										<svg-globe class="sources-globe-icon" />
										{{ sprintf(strings.webSearchSources, provider.sourcesCount) }}
										<svg-caret
											class="sources-chevron"
											:class="{ 'rotated': expandedProvider === provider.key }"
										/>
									</h3>
								</div>

								<div class="sources-list">
									<a
										v-for="(source, index) in provider.data.sources"
										:key="index"
										:href="source.url"
										target="_blank"
										rel="noopener noreferrer"
										class="source-entry"
										@click.stop
									>
										<div class="source-content">
											<h4 class="source-title">{{ source.title || source.name || source.url }}</h4>
											<span class="source-url">
												{{ source.url }}
											</span>
										</div>
										<span class="source-link-button">
											<svg-link-external />
										</span>
									</a>
								</div>
							</div>
						</div>
					</transition-slide>
				</div>
			</div>
		</core-card>
	</div>
</template>

<script setup>
import { ref, computed } from 'vue'

import CoreCard from '@/vue/components/common/core/Card'
import TransitionSlide from '@/vue/components/common/transition/Slide'
import SvgRightArrow from '@/vue/components/common/svg/right-arrow/Simple'
import SvgGlobe from '@/vue/components/common/svg/Globe'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgLinkExternal from '@/vue/components/common/svg/link/External'
import SvgAnthropic from '@/vue/components/common/svg/ai/providers/Anthropic'
import SvgDeepSeek from '@/vue/components/common/svg/ai/providers/DeepSeek'
import SvgPerplexity from '@/vue/components/common/svg/ai/providers/Perplexity'
import SvgGemini from '@/vue/components/common/svg/ai/providers/Gemini'
import SvgOpenAI from '@/vue/components/common/svg/ai/providers/OpenAI'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const props = defineProps({
	report : {
		type     : Object,
		required : true
	}
})

const expandedProvider = ref(null)

const strings = {
	aiEngineResults : __('AI Engine Results', td),
	tooltip         : __('Results from different AI engines', td),
	sourcesCount    : sprintf(
		// Translators: 1 - Number of sources.
		__('%1$s sources', td),
		'%1$s'
	),
	brandsCount : sprintf(
		// Translators: 1 - Number of brands.
		__('%1$s brands', td),
		'%1$s'
	),
	webSearchSources : sprintf(
		// Translators: 1 - Number of web search sources.
		__('%1$s Web Search Sources', td),
		'%1$s'
	)
}

const providers = computed(() => {
	if (!props.report?.results || !Array.isArray(props.report.results)) {
		return []
	}

	const providerMap = {
		openai     : 'OpenAI',
		anthropic  : 'Claude',
		gemini     : 'Gemini',
		deepseek   : 'DeepSeek',
		perplexity : 'Perplexity'
	}

	return props.report.results.map((result) => {
		const brandsCount = result.brands?.length || 0
		const sourcesCount = result.sources?.length || 0

		return {
			key          : result.provider,
			name         : providerMap[result.provider] || result.provider,
			brandsCount  : brandsCount,
			sourcesCount : 0 < sourcesCount ? sourcesCount : null,
			data         : result
		}
	}).sort((a, b) => {
		// Sort by provider order: Open AI, Anthropic, Gemini, DeepSeek, Perplexity
		const order = [ 'openai', 'anthropic', 'gemini', 'deepseek', 'perplexity' ]
		return order.indexOf(a.key) - order.indexOf(b.key)
	})
})

const toggleProvider = (providerKey) => {
	if (expandedProvider.value === providerKey) {
		expandedProvider.value = null
	} else {
		expandedProvider.value = providerKey
	}
}
</script>

<style lang="scss" scoped>
@use '@/vue/assets/scss/app/variables.scss' as *;

.report-results-partial {
	margin-bottom: 24px;
}

.providers-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.provider-entry {
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	background: #fff;
	transition: all 0.2s ease;

	&:hover {
		border-color: #c0c0c0;
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
	}
}

.provider-entry-main {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px;
	cursor: pointer;
}

.provider-info {
	display: flex;
	align-items: center;
	gap: 12px;
	flex: 1;
}

.provider-icon {
	display: flex;
	align-items: center;
	justify-content: center;
}

.provider-name {
	font-size: 16px;
	font-weight: $font-bold;
	color: #333;
}

.provider-metrics {
	display: flex;
	align-items: center;
	gap: 8px;
}

.metric-badge {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 4px 8px;
	background-color: $inline-background;
	border-radius: 6px;
	font-size: 14px;
	font-weight: $font-bold;
	color: $black;

	.badge-icon {
		width: 16px;
		height: 16px;
		color: $blue;
	}
}

.expand-button {
	width: 24px;
	height: 24px;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: #F9FAFB;
	border: 1px solid $placeholder-color;
	border-radius: 4px;
	cursor: pointer;
	padding: 0;
	margin-left: 16px;
	transition: transform 0.2s ease;

	svg {
		width: 14px;
		height: 14px;
		color: $placeholder-color;
		transition: transform 0.2s ease;
	}

	&:hover {
		background-color: #e8e8e8;
	}

	.rotated {
		transform: rotate(90deg);
	}
}

.provider-details {
	padding: 0 48px 48px 48px;
	cursor: default;
}

.brands-list-section {
	// padding: 16px;
	margin-bottom: 16px;;
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}

.brand-tag {
	padding: 8px 12px;
	background-color: $border;
	border-radius: 4px;
	color: $black;
}

.provider-overview {
	border-bottom: 1px solid $border;
	padding-bottom: 16px;
	margin-bottom: 16px;

	:deep(p) {
		margin: 0 0 16px 0;
		font-size: 14px;
		line-height: 1.6;
		color: #333;

		&:last-child {
			margin-bottom: 0;
		}
	}

	:deep(ol),
	:deep(ul) {
		margin: 0 0 16px 0;
		padding-left: 24px;
		font-size: 14px;
		line-height: 1.6;
		color: #333;
	}

	:deep(li) {
		margin-bottom: 8px;

		&:last-child {
			margin-bottom: 0;
		}

		strong {
			font-weight: 600;
		}
	}

	:deep(h1),
	:deep(h2),
	:deep(h3),
	:deep(h4),
	:deep(h5),
	:deep(h6) {
		margin: 0 0 12px 0;
		font-size: 16px;
		font-weight: 600;
		color: #333;
	}
}

.sources-header {
	margin-bottom: 16px;
}

.sources-title {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
	color: #333;
	display: flex;
	align-items: center;
	gap: 8px;
}

.sources-globe-icon {
	width: 16px;
	height: 16px;
	color: $blue;
	flex-shrink: 0;
}

.sources-chevron {
	width: 20px;
	height: 20px;
	color: $blue;
	transform: rotate(-180deg);
	transition: transform 0.2s ease;

	&.rotated {
		transform: rotate(0deg);
	}
}

.sources-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.source-entry {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	padding: 12px;
	background-color: #F9FAFB;
	border: 1px solid $gray;
	border-radius: 6px;
	text-decoration: none;
	cursor: pointer;
	transition: border-color 0.2s ease;

	&:hover {
		border-color: #c0c0c0;
	}
}

.source-content {
	flex: 1;
	min-width: 0;
}

.source-title {
	margin: 0 0 6px 0;
	font-size: 14px;
	font-weight: 600;
	color: #333;
	line-height: 1.4;
	word-wrap: break-word;
}

.source-url {
	display: block;
	font-size: 13px;
	color: #666;
	word-break: break-all;
	line-height: 1.4;
}

.source-link-button {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-left: 12px;
	flex-shrink: 0;

	svg {
		width: 16px;
		height: 16px;
		color: $blue;
	}
}
</style>