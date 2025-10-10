<template>
	<core-settings-row
		align
	>
		<template #name>
			<div class="name align">
				{{ strings.aiContentGeneration }}
			</div>

			<credit-counter :parentComponentContext="parentComponentContext" />
		</template>

		<template #content>
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
				/>
			</div>
		</template>
	</core-settings-row>
</template>

<script>
import { useOptionsStore } from '@/vue/stores'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
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
		CoreSettingsRow,
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
			if ('image-generator' === feature.slug) {
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
	max-width: 880px;
}
</style>