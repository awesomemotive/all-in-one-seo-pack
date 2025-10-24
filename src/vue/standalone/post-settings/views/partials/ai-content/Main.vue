<template>
	<div
		class="aioseo-ai-content-main"
		:class="{
			'aioseo-ai-content-main--sidebar': 'sidebar' === parentComponentContext
		}"
	>
		<div class="aioseo-ai-content-main-header">
			<div
				v-if="'sidebar' !== parentComponentContext"
				class="aioseo-ai-content-main-header-title"
			>
				{{ strings.aiContentGeneration }}
			</div>

			<credit-counter
				:parent-component-context="parentComponentContext"
				:tooltip-placement="'bottom'"
				:tooltip-offset="'-60px, 0'"
			/>
		</div>

		<div class="aioseo-ai-content-main-body">
			<core-alert
				class="aioseo-ai-content-no-content-warning"
				v-if="postContentLength < 200"
				type="red"
			>
				{{ strings.noContentWarning }}
			</core-alert>

			<core-alert
				v-if="optionsStore.internalOptions.internal.ai.accessToken && optionsStore.internalOptions.internal.ai.isTrialAccessToken"
				class="aioseo-ai-content-trial-warning"
				type="blue"
				v-html="strings.trialWarning"
			/>

			<div class="aioseo-ai-content-features">
				<feature-card
					v-for="(feature, index) in features"
					:key="index"
					:feature="feature"
					:buttonDisabled="isButtonDisabled(feature)"
					:parent-component-context="parentComponentContext"
				/>
			</div>
		</div>
	</div>
</template>

<script>
import { useOptionsStore } from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import FeatureCard from './FeatureCard'

import { getAiFeatures } from './utils'
import { isBlockEditor, isClassicEditor, isPageBuilderEditor } from '@/vue/utils/context'
import { getPostEditedContent } from '@/vue/plugins/tru-seo/components/postContent'

import { debounce } from 'lodash-es'
import links from '@/vue/utils/links'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

const getCleanedContent = () => {
	// Replace HTML tags with empty strings.
	return getPostEditedContent()
		.replace(/<\/?[a-z][^>]*?>/gi, '')
		.replace(/<!--[\s\S]*?-->/g, '')
		.trim()
}

export default {
	setup () {
		const optionsStore = useOptionsStore()

		return {
			optionsStore
		}
	},
	components : {
		CoreAlert,
		CreditCounter,
		FeatureCard
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			getPostEditedContent,
			getCleanedContent,
			features          : getAiFeatures(),
			postContentLength : 0,
			strings           : {
				aiContentGeneration : __('AI Content Generation', td),
				noContentWarning    : __('Your post is too short to generate AI content. Please add more content. For the best results, we recommend adding at least 200 characters.', td),
				trialWarning        : sprintf(
					// Translators: 1 - "upgrade to Pro" link, 2 - "purchase PAYG credits" link.
					__('You can try out our AI features for free, enjoy! To unlock additional AI credits, %1$s or %2$s.', td),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						links.getUpsellUrl('ai-content', 'trial-warning', 'pricing'),
						__('upgrade to Pro', td)
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						links.getUpsellUrl('ai-content', 'trial-warning', 'aiCredits'),
						__('purchase PAYG credits', td)
					)
				)
			}
		}
	},
	methods : {
		isButtonDisabled (feature) {
			if (
				'image-generator' === feature.slug ||
				'ai-assistant' === feature.slug
			) {
				return false
			}

			return 500 > this.postContentLength
		},
		updateContentLength (length) {
			this.postContentLength = length
		},
		watchBlockEditor () {
			window.wp.data.subscribe(() => {
				debounce(() => {
					this.updateContentLength(this.getCleanedContent().length)
				}, 500)()
			})
		},
		watchClassicEditor () {
			if (!window.tinyMCE) {
				return
			}

			if (document.querySelector('#wp-content-wrap.tmce-active')) {
				// tinyMCE Content Update.
				window.tinyMCE.get('content').on('keyup', () => {
					const contentLength = window.tinyMCE.get('content').getContent({ format: 'raw' }).length
					this.updateContentLength(contentLength)
				})
				window.tinyMCE.get('content').on('paste', () => {
					const contentLength = window.tinyMCE.get('content').getContent({ format: 'raw' }).length
					this.updateContentLength(contentLength)
				})
			} else {
				// No TinyMCE. Look for a proper #content textarea.
				const textEditor = document.querySelector('textarea#content')
				if (textEditor) {
					textEditor.addEventListener('keyup', () => {
						const contentLength = textEditor.value.length
						this.updateContentLength(contentLength)
					})
					textEditor.addEventListener('paste', () => {
						const contentLength = textEditor.value.length
						this.updateContentLength(contentLength)
					})
				}
			}
		},
		watchPageBuilderEditor () {
			window.aioseoBus.$on('aioseo-content-changed', () => {
				this.updateContentLength(this.getCleanedContent().length)
			})
		},
		initWatchers () {
			if (isPageBuilderEditor()) {
				this.watchPageBuilderEditor()
			} else if (isBlockEditor()) {
				this.watchBlockEditor()
			} else if (isClassicEditor()) {
				this.watchClassicEditor()
			}
		}
	},
	beforeMount () {
		this.updateContentLength(this.getCleanedContent().length)
		this.initWatchers()
	},
	beforeUnmount () {
		window.aioseoBus.$off('aioseo-content-changed')
	}
}
</script>

<style lang="scss">
.aioseo-ai-content-no-content-warning,
.aioseo-ai-content-trial-warning {
	margin-bottom: 12px;
}

.aioseo-ai-content-main {
	&--sidebar {
		--main-header-padding: 12px 16px;
	}

	.aioseo-ai-content-main-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		background-color: #F3F4F5;
		padding: var(--main-header-padding, 16px 20px);
		border-radius: 4px;
		margin-bottom: 12px;
		flex-wrap: wrap;
        gap: 12px;
	}

	.aioseo-ai-content-main-header-title {
		font-weight: 700;
		font-size: 18px;
	}
}
</style>