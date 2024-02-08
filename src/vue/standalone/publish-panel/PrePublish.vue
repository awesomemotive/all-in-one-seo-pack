<template>
	<div
		class="seo-overview"
		v-if="postEditorStore.currentPost.id"
	>
		<ul class="pre-publish-checklist">
			<li
				v-for="(tip, index) in tips"
				:key="index"
			>
				<span class="icon">
					<component
						:is="tip.icon"
						:class="tip.type"
					/>
				</span>

				<span>{{ tip.label }}: <span class="result" :class="tip.value.endsWith('/100') ? tip.type : null">{{ tip.value }}</span></span>

				<span
					v-if="optionsStore.dynamicOptions.searchAppearance.postTypes[postEditorStore.currentPost.postType] && optionsStore.dynamicOptions.searchAppearance.postTypes[postEditorStore.currentPost.postType].advanced.showMetaBox"
					class="edit"
					@click="openSidebar(tip.name)"
				>
					<svg-pencil />
				</span>
			</li>
		</ul>

		<div
			class="snippet-preview"
			v-if="allowed('aioseo_page_analysis')"
		>
			<p class="title">{{ strings.serpPreview }}:</p>
			<core-google-search-preview
				:url="tagsStore.liveTags.permalink"
				:title="parseTags(postEditorStore.currentPost.title || postEditorStore.currentPost.tags.title || '#post_title #separator_sa #site_title')"
				:description="parseTags(postEditorStore.currentPost.description || postEditorStore.currentPost.tags.description || '#post_content')"
			/>
		</div>

		<div
			class="canonical-url"
			v-if="allowed('aioseo_page_analysis') && postEditorStore.currentPost.canonicalUrl"
		>
			<p class="title">
				{{ strings.canonicalUrl }}:
				<span class="edit" @click="openSidebar('canonical')">
					<svg-pencil />
				</span>
			</p>
			<a :href="postEditorStore.currentPost.canonicalUrl" target="_blank" rel="noopener noreferrer">
				<span>{{ postEditorStore.currentPost.canonicalUrl }}</span>
				<svg-external />
			</a>
		</div>
	</div>
</template>

<script>
import {
	useOptionsStore,
	usePostEditorStore,
	useSettingsStore,
	useTagsStore
} from '@/vue/stores'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'

import { ImagePreview } from '@/vue/mixins/Image'
import { Tags } from '@/vue/mixins/Tags'
import CoreGoogleSearchPreview from '@/vue/components/common/core/GoogleSearchPreview'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgPencil from '@/vue/components/common/svg/Pencil'
export default {
	setup () {
		return {
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			settingsStore   : useSettingsStore(),
			tagsStore       : useTagsStore()
		}
	},
	mixins     : [ Tags, ImagePreview ],
	components : {
		CoreGoogleSearchPreview,
		SvgCircleCheck,
		SvgCircleClose,
		SvgCircleExclamation,
		SvgExternal,
		SvgPencil
	},
	data () {
		return {
			allowed,
			separator      : undefined,
			socialImage    : null,
			socialImageKey : 0,
			strings        : {
				serpPreview  : this.$t.__('SERP Preview', this.$td),
				canonicalUrl : this.$t.__('Canonical URL', this.$td)
			}
		}
	},
	computed : {
		tips () {
			let tips = [
				{
					label  : this.$t.__('Visibility', this.$td),
					name   : 'visibility',
					access : 'aioseo_page_advanced_settings'
				},
				{
					label  : this.$t.__('SEO Analysis', this.$td),
					name   : 'seoAnalysis',
					access : 'aioseo_page_analysis'
				},
				{
					label  : this.$t.__('Readability', this.$td),
					name   : 'readabilityAnalysis',
					access : 'aioseo_page_analysis'
				},
				{
					label  : this.$t.__('Focus Keyphrase', this.$td),
					name   : 'focusKeyphrase',
					access : 'aioseo_page_analysis'
				},
				{
					label  : this.$t.__('Social', this.$td),
					name   : 'social',
					access : 'aioseo_page_social_settings'
				}
			].filter((tip) => {
				return this.allowed(tip.access) && (
					// Exclude items which require TruSEO to be enabled.
					'aioseo_page_analysis' !== tip.access ||
					this.optionsStore.options.advanced.truSeo
				)
			})

			// Remove Social tip if both Twitter and Facebook markup are disabled.
			if (!this.optionsStore.options.social.facebook.general.enable && !this.optionsStore.options.social.twitter.general.enable) {
				tips = tips.filter(tip => 'social' !== tip.name)
			}

			tips.forEach((tip, index) => {
				tips[index] = { ...tip, ...this.getData(tip.name) }
			})

			return tips
		},
		canImprove () {
			return this.tips.some(tip => 'error' === tip.type)
		}
	},
	methods : {
		getIcon (type) {
			switch (type) {
				case 'error':
					return 'svg-circle-close'
				case 'warning':
					return 'svg-circle-exclamation'
				case 'success':
				default:
					return 'svg-circle-check'
			}
		},
		getData (tipName) {
			const result = {}

			if ('visibility' === tipName) {
				result.value = this.$t.__('Good!', this.$td)
				result.type  = 'success'

				const value = this.postEditorStore.currentPost.default
					? (
						this.optionsStore.dynamicOptions.searchAppearance.postTypes[this.postEditorStore.currentPost.postType] &&
						!this.optionsStore.dynamicOptions.searchAppearance.postTypes[this.postEditorStore.currentPost.postType].advanced.robotsMeta.default &&
						this.optionsStore.dynamicOptions.searchAppearance.postTypes[this.postEditorStore.currentPost.postType].advanced.robotsMeta.noindex
					)
					: this.postEditorStore.currentPost.noindex
				if (value) {
					result.value = this.$t.__('Blocked!', this.$td)
					result.type  = 'error'
				}
			}

			if ('seoAnalysis' === tipName) {
				result.value = this.$t.__('N/A', this.$td)
				result.type  = 'error'

				const value = this.postEditorStore.currentPost.seo_score
				if (Number.isInteger(value)) {
					result.value = value + '/100'
					result.type  = 80 < value ? 'success' : 50 < value ? 'warning' : 'error'
				}
			}

			if ('readabilityAnalysis' === tipName) {
				result.value = this.$t.__('Good!', this.$td)
				result.type  = 'success'

				const value = this.postEditorStore.currentPost.page_analysis.analysis.readability.errors
				if (value && 0 < value) {
					result.value = this.$t.sprintf(
						// Translators: 1 - How many errors were found.
						this.$t._n('%1$s error found!', '%1$s errors found!', value, this.$td),
						value
					)
					result.type  = 'error'
				}
			}

			if ('focusKeyphrase' === tipName) {
				result.value = this.$t.__('No focus keyphrase!', this.$td)
				result.type  = 'error'

				const value = this.postEditorStore.currentPost.keyphrases.focus
				if (value && value.keyphrase) {
					result.value = value.score + '/100'
					result.type  = 80 < value.score ? 'success' : 50 < value.score ? 'warning' : 'error'
				}
			}

			if ('social' === tipName) {
				result.value = this.$t.__('Good!', this.$td)
				result.type  = 'success'

				// We're just putting the social image key here to force the computed property to recompute when the key is updated.
				// eslint-disable-next-line no-unused-expressions
				this.socialImageKey

				const socialTitle       = this.parseTags(this.postEditorStore.currentPost.og_title || this.postEditorStore.currentPost.title || this.postEditorStore.currentPost.tags.title).trim()
				const socialDescription = this.parseTags(this.postEditorStore.currentPost.og_description || this.postEditorStore.currentPost.description || this.postEditorStore.currentPost.tags.description).trim()
				const socialImage       = this.socialImage

				if (!socialTitle || !socialDescription || !socialImage) {
					result.value = this.$t.__('Missing social markup!', this.$td)
					result.type  = 'error'
				}
			}

			return { ...result, icon: this.getIcon(result.type) }
		},
		openSidebar (tipName) {
			const { closePublishSidebar, openGeneralSidebar } = window.wp.data.dispatch('core/edit-post')

			closePublishSidebar()
			openGeneralSidebar('aioseo-post-settings-sidebar/aioseo-post-settings-sidebar')

			const sidebarSettings = {}

			switch (tipName) {
				case 'canonical':
				case 'visibility':
					sidebarSettings.tab = 'advanced'
					break
				case 'seoAnalysis':
					sidebarSettings.tab  = 'general'
					sidebarSettings.card = 'basicseo'
					break
				case 'readabilityAnalysis':
					sidebarSettings.tab  = 'general'
					sidebarSettings.card = 'readability'
					break
				case 'focusKeyphrase':
					sidebarSettings.tab  = 'general'
					sidebarSettings.card = 'focus'
					break
				case 'social':
					sidebarSettings.tab = 'social'
					break
			}

			this.settingsStore.changeTabSettings({ setting: 'mainSidebar', value: sidebarSettings })
		}
	},
	async mounted () {
		await this.setImageUrl().then(() => {
			this.socialImage = this.imageUrl
		})

		window.aioseoBus.$on('updateSocialImagePreview', (param) => {
			this.socialImage = param.image
			this.socialImageKey++
		})

		this.$nextTick(() => {
			const menuItem = document.querySelector('.aioseo-pre-publish .editor-post-publish-panel__link')
			if (menuItem) {
				menuItem.innerHTML = this.canImprove
					? this.$t.__('Your post needs improvement!', this.$td)
					: this.$t.__('You\'re good to go!', this.$td)
			}
		})
	}
}
</script>

<style lang="scss">
.aioseo-seo-overview {
	&.components-panel__body {
		.components-button {
			display: inline-block;
			line-height: 140%;
		}
		&.is-opened {
			.components-panel__body-title,
			.components-panel__body-title:hover {
				border-bottom: 1px solid #e0e0e0;
			}
		}
	}
}
.seo-overview {

	ul {
		margin: 16px 0;
		padding-bottom: 6px;
		border-bottom: 1px solid #E8E8EB;

		li {
			display: flex;
			align-items: center;
			margin-bottom: 12px;
		}

		.icon {
			margin-right: 10px;
			line-height: 0;

			svg {
				width: 22px;
				height: 22px;
			}
		}

		.result {
			font-weight: 700;
		}

		.warning {
			color: $orange;
		}

		.info {
			color: $blue;
		}

		.success {
			color: $green;
		}

		.error {
			border: none;
			color: $red;
		}
	}

	.title {
		font-size: 13px;
		line-height: 130%;
		color: $placeholder-color;
		margin-bottom: 8px;
		display: flex;
	}

	.edit {
		cursor: pointer;
		margin-left: auto;
		margin-right: 5px;

		svg {
			width: 14px;
			height: 14px;
			color: $black2;
		}

		&:hover {
			svg {
				color: $black2-hover;
			}
		}
	}

	.snippet-preview {
		&:not(:last-child) {
			margin-bottom: 16px;
		}

		.aioseo-google-search-preview {
			border: 1px solid $border;
			padding: 10px;

			a {
				color: $black3;
				text-decoration: none;
			}
		}
	}
	.canonical-url {
		a {
			font-size: 12px;
			color: $blue;
			svg {
				margin-left: 5px;
				width: 16px;
				height: 16px;
				vertical-align: middle;
			}
		}
		span {
			vertical-align: middle;
			overflow-wrap: break-word;
		}
	}
}
</style>