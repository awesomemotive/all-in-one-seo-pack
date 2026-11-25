<template>
	<div
		class="aioseo-ai-content-main"
		:class="{
			'aioseo-ai-content-main--sidebar': 'sidebar' === parentComponentContext
		}"
	>
		<div
			v-if="!heroFeature"
			class="aioseo-ai-content-main-header"
		>
			<credit-counter
				:parent-component-context="parentComponentContext"
				:tooltip-placement="'bottom'"
				:tooltip-offset="'-60px, 0'"
			/>
		</div>

		<div class="aioseo-ai-content-main-body">
			<feature-card
				v-if="heroFeature"
				:feature="heroFeature"
				variant="hero"
				:buttonDisabled="isButtonDisabled(heroFeature)"
				:parent-component-context="parentComponentContext"
				class="aioseo-ai-content-hero"
			/>

			<p
				v-if="isTrial"
				class="aioseo-ai-content-trial-note"
				v-safe-html="strings.trialNote"
			/>

			<div
				v-for="group in groups"
				:key="group.key"
				class="aioseo-ai-content-group"
			>
				<div class="aioseo-ai-content-group__label">{{ group.label }}</div>

				<div class="aioseo-ai-content-group__items">
					<feature-card
						v-for="(feature, index) in group.features"
						:key="index"
						:feature="feature"
						:buttonDisabled="isButtonDisabled(feature)"
						:parent-component-context="parentComponentContext"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { useAiContent } from '@/vue/composables/AiContent'

import {
	useOptionsStore,
	useSensitiveOptionsStore
} from '@/vue/stores'

import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import FeatureCard from './FeatureCard'

import { getAiFeatures } from './utils'
import { isBlockEditor, isClassicEditor, isPageBuilderEditor } from '@/vue/utils/context'

import { debounce } from 'lodash-es'
import links from '@/vue/utils/links'

import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const aiContent             = useAiContent()
		const optionsStore          = useOptionsStore()
		const sensitiveOptionsStore = useSensitiveOptionsStore()

		return {
			aiContent,
			optionsStore,
			sensitiveOptionsStore
		}
	},
	components : {
		CreditCounter,
		FeatureCard
	},
	props : {
		parentComponentContext : String
	},
	data () {
		return {
			features          : getAiFeatures(),
			postContentLength : 0,
			strings           : {
				groupContent : __('Generate Content', td),
				groupListing : __('Improve Search Listing', td),
				trialNote    : sprintf(
					// Translators: 1 - "upgrade to Pro" link, 2 - "buy a credit bundle" link.
					__('You\'re using trial credits — %1$s or %2$s for more.', td),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						links.getUpsellUrl('ai-content', 'trial-note', 'pricing'),
						__('upgrade to Pro', td)
					),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s</a>',
						links.getUpsellUrl('ai-content', 'trial-note', 'aiCredits'),
						__('buy a credit bundle', td)
					)
				)
			}
		}
	},
	computed : {
		isTrial () {
			return this.sensitiveOptionsStore.hasAiAccessToken &&
				this.optionsStore.internalOptions.internal.ai.isTrialAccessToken
		},
		heroFeature () {
			return this.features.find(feature => feature.hero)
		},
		groups () {
			return [
				{ key: 'content', label: this.strings.groupContent },
				{ key: 'listing', label: this.strings.groupListing }
			]
				.map(group => ({ ...group, features: this.features.filter(feature => group.key === feature.group) }))
				.filter(group => group.features.length)
		},
		minContentLength () {
			return this.aiContent.minContentLength
		},
		noContentWarning () {
			return this.aiContent.strings.noContentWarning
		}
	},
	methods : {
		isButtonDisabled (feature) {
			// Image Generator and AI Assistant create content from scratch, so they
			// don't need existing post content. Every other feature — Auto-Optimize
			// included — rewrites what's already there and needs the minimum length.
			if (
				'image-generator' === feature.slug ||
				'ai-assistant' === feature.slug
			) {
				return false
			}

			return this.minContentLength > this.postContentLength
		},
		updateContentLength (length) {
			this.postContentLength = length
		},
		watchBlockEditor () {
			window.wp.data.subscribe(() => {
				debounce(() => {
					this.updateContentLength(this.aiContent.getPostContentLength())
				}, 500)()
			})
		},
		watchClassicEditor () {
			if (!window.tinyMCE) {
				return
			}

			const updateLength = () => this.updateContentLength(this.aiContent.getPostContentLength())

			if (document.querySelector('#wp-content-wrap.tmce-active')) {
				window.tinyMCE.get('content').on('keyup', updateLength)
				window.tinyMCE.get('content').on('paste', updateLength)
			} else {
				const textEditor = document.querySelector('textarea#content')
				if (textEditor) {
					textEditor.addEventListener('keyup', updateLength)
					textEditor.addEventListener('paste', updateLength)
				}
			}
		},
		watchPageBuilderEditor () {
			window.aioseoBus.$on('aioseo-content-changed', () => {
				this.updateContentLength(this.aiContent.getPostContentLength())
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
		this.updateContentLength(this.aiContent.getPostContentLength())
		this.initWatchers()
	},
	beforeUnmount () {
		window.aioseoBus.$off('aioseo-content-changed')
	}
}
</script>

<style lang="scss">
// Deliberately not an alert: the hero sits right above it in nearly the same blue, and a
// trial balance is context rather than something blocking.
// NOTE: `.aioseo-app p` is 0-1-1, so a lone class loses on font-size.
.aioseo-ai-content-main .aioseo-ai-content-trial-note {
	margin: 10px 0 0;
	font-size: 12.5px;
	line-height: 1.45;
	color: $black2;

	a {
		font-weight: 600;
	}
}

.aioseo-ai-content-main {
	// The fallback for screens with no hero, so the balance still shows somewhere: a
	// hairline toolbar rather than a filled slab. The counter is the row's only child, so it
	// starts at the content edge — right-aligned it read as a number floating over a gutter.
	.aioseo-ai-content-main-header {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 12px;
		padding-bottom: 11px;
		margin-bottom: 14px;
		border-bottom: 1px solid $border;
	}

	.aioseo-ai-content-hero {
		margin-bottom: 4px;
	}

	.aioseo-ai-content-group {
		&__label {
			margin: 18px 0 8px;
			font-size: 11px;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: $placeholder-color;
		}

		&__items {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 9px;
		}
	}

	// The sidebar is roughly a third of the metabox's width, so two per line would leave
	// the hint with nowhere to go.
	&--sidebar .aioseo-ai-content-group__items {
		grid-template-columns: minmax(0, 1fr);
	}
}

@media screen and (max-width: 782px) {
	.aioseo-ai-content-main .aioseo-ai-content-group__items {
		grid-template-columns: minmax(0, 1fr);
	}
}
</style>