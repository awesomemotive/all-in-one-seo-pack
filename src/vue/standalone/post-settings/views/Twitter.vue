<template>
	<div class="tab-twitter">
		<core-settings-row class="snippet-preview-row">
			<template #name>
				<span>{{ strings.twitterPreview }}</span>
			</template>

			<template #description>
				<i>{{ strings.twitterPreviewDescription }}</i>
			</template>

			<template #content>
				<core-alert
					class="twitter-disabled-warning"
					v-if="!optionsStore.options.social.twitter.general.enable"
					v-html="strings.twitterDisabled"
					type="red"
				/>

				<core-twitter-preview
					:card="postEditorStore.currentPost.twitter_card"
					:description="previewDescription"
					:image="previewImage"
					:loading="loading"
					:title="previewTitle"
				/>
			</template>
		</core-settings-row>

		<div id="aioseo-post-settings-twitter">
			<core-settings-row
				:name="strings.useFB"
				class="use-facebook"
			>
				<template #content>
					<base-toggle
						v-model="postEditorStore.currentPost.twitter_use_og"
						@update:modelValue="postEditorStore.isDirty = true"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!postEditorStore.currentPost.twitter_use_og"
				:name="strings.twitterTitle"
				class="twitter-title-settings"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="twitter-meta-input"
						v-model="postEditorStore.currentPost.twitter_title"
						:line-numbers="false"
						single
						@counter="count => titleCount = count.length"
						@update:modelValue="postEditorStore.isDirty = true"
						:tags-context="`${postEditorStore.currentPost.postType || postEditorStore.currentPost.termType}Title`"
						:default-tags="tags.getDefaultTags('term' === postEditorStore.currentPost.context ? 'taxonomies' : null, null, 'title')"
					>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(titleCount, 70)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!postEditorStore.currentPost.twitter_use_og"
				:name="strings.twitterDescription"
				class="twitter-description-settings"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="twitter-meta-input"
						v-model="postEditorStore.currentPost.twitter_description"
						:line-numbers="false"
						description
						@counter="count => descriptionCount = count.length"
						@update:modelValue="postEditorStore.isDirty = true"
						:tags-context="`${postEditorStore.currentPost.postType || postEditorStore.currentPost.termType}Description`"
						:default-tags="tags.getDefaultTags('term' === postEditorStore.currentPost.context ? 'taxonomies' : null, null, 'description')"
					>
						<template #tags-description>
							{{ strings.clickToAddHomePageDescription }}
						</template>
					</core-html-tags-editor>

					<div
						class="max-recommended-count"
						v-html="maxRecommendedCount(descriptionCount, 200)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!postEditorStore.currentPost.twitter_use_og"
				class="twitter-image-source"
				:name="strings.imageSource"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="imageSourceOptionsFiltered"
						:modelValue="getImageSourceOptionFiltered(postEditorStore.currentPost.twitter_image_type)"
						@update:modelValue="value => saveTwitterImageType(value.value)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!postEditorStore.currentPost.twitter_use_og && 'custom' === postEditorStore.currentPost.twitter_image_type"
				class="twitter-custom-field"
				:name="strings.customFieldsName"
				align
			>
				<template #content>
					<base-input
						type="text"
						size="medium"
						:placeholder="strings.placeholder"
						v-model="postEditorStore.currentPost.twitter_image_custom_fields"
						@update:modelValue="postEditorStore.isDirty = true"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!postEditorStore.currentPost.twitter_use_og && 'custom_image' === postEditorStore.currentPost.twitter_image_type"
				class="twitter-image"
				:name="strings.twitterImage"
			>
				<template #content>
					<core-image-uploader
						:description="twitterImageUploaderDescription"
						v-model="postEditorStore.currentPost.twitter_image_custom_url"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="twitter-card-type"
				:name="strings.twitterCardType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						open-direction="top"
						:options="twitterCardOptions"
						:modelValue="getCardOptions(postEditorStore.currentPost.twitter_card)"
						@update:modelValue="value => cardSelect(value.value)"
					/>
				</template>
			</core-settings-row>
		</div>
	</div>
</template>

<script>
import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import tags from '@/vue/utils/tags'

import { useImage } from '@/vue/composables/Image'
import { useMaxCounts } from '@/vue/composables/MaxCounts'
import { usePostSocial } from '@/vue/composables/PostSocial'
import { useTags } from '@/vue/composables/Tags'

import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTwitterPreview from '@/vue/components/common/core/TwitterPreview'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			getImageSourceOptionFiltered,
			imageSourceOptionsFiltered,
			imageUrl,
			loading,
			setImageUrl
		} = useImage()

		const {
			maxRecommendedCount
		} = useMaxCounts()

		const {
			twitterCardOptions
		} = usePostSocial()

		const {
			parseTags
		} = useTags({
			separator : undefined
		})

		return {
			getImageSourceOptionFiltered,
			imageSourceOptionsFiltered,
			imageUrl,
			loading,
			maxRecommendedCount,
			optionsStore    : useOptionsStore(),
			parseTags,
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore(),
			setImageUrl,
			twitterCardOptions
		}
	},
	components : {
		CoreAlert,
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow,
		CoreTwitterPreview
	},
	props : {
		isMobilePreview : {
			type : Boolean,
			default () {
				return false
			}
		}
	},
	data () {
		return {
			tags,
			titleCount       : 0,
			descriptionCount : 0,
			facebookImageUrl : '',
			strings          : {
				twitterPreview              : __('X (Twitter) Preview', td),
				twitterPreviewDescription   : __('X cards by default will use the data defined below. If no data is set, X will instead pick up the data set on the Facebook tab.', td),
				useFB                       : __('Use Data from Facebook Tab', td),
				imageSource                 : __('Image Source', td),
				customFieldsName            : __('Custom Field Name', td),
				twitterImage                : __('X Image', td),
				twitterTitle                : __('X Title', td),
				twitterDescription          : __('X Description', td),
				twitterCardType             : __('X Card Type', td),
				minimumSizeSummary          : __('Minimum size: 144px x 144px, ideal ratio 1:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', td),
				minimumSizeSummaryWithLarge : __('Minimum size: 300px x 157px, ideal ratio 2:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', td),
				twitterDisabled             : sprintf(
					// Translators: 1 - "Open Graph", 2 - "Go to Social Networks ->".
					__('No %1$s markup will be output for your post because it is currently disabled. You can enable %1$s markup in the Social Networks settings. %2$s', td),
					__('X (Twitter)', td),
					sprintf(
						'<a href="%1$s" target="_blank">%2$s<span class="link-right-arrow">&nbsp;&rarr;</span></a>',
						this.rootStore.aioseo.urls.aio.socialNetworks + '#twitter',
						__('Go to Social Networks', td)
					)
				)
			}
		}
	},
	watch : {
		'postEditorStore.currentPost.twitter_use_og' () {
			this.handleImageUpdate()
		},
		'postEditorStore.currentPost.twitter_image_type' () {
			this.handleImageUpdate()
		},
		'postEditorStore.currentPost.twitter_image_custom_url' () {
			this.handleImageUpdate()
		}
	},
	computed : {
		previewTitle () {
			const title = (this.postEditorStore.currentPost.twitter_use_og || !this.postEditorStore.currentPost.twitter_title)
				? this.postEditorStore.currentPost.og_title
				: this.postEditorStore.currentPost.twitter_title
			return this.parseTags(title || this.postEditorStore.currentPost.title || this.postEditorStore.currentPost.tags.title || '#post_title #separator_sa #site_title')
		},
		previewDescription () {
			const description = (this.postEditorStore.currentPost.twitter_use_og || !this.postEditorStore.currentPost.twitter_description)
				? this.postEditorStore.currentPost.og_description
				: this.postEditorStore.currentPost.twitter_description
			return this.parseTags(description || this.postEditorStore.currentPost.description || this.postEditorStore.currentPost.tags.description || '#post_content')
		},
		previewImage () {
			return (this.postEditorStore.currentPost.twitter_use_og || !this.imageUrl)
				? this.facebookImageUrl
				: this.imageUrl
		},
		twitterImageUploaderDescription () {
			if ('summary' === this.postEditorStore.currentPost.twitter_card || ('default' === this.postEditorStore.currentPost.twitter_card && 'summary' === this.optionsStore.options.social.twitter.general.defaultCardType)) {
				return this.strings.minimumSizeSummary
			}

			if ('summary_large_image' === this.postEditorStore.currentPost.twitter_card || ('default' === this.postEditorStore.currentPost.twitter_card && 'summary_large_image' === this.optionsStore.options.social.twitter.general.defaultCardType)) {
				return this.strings.minimumSizeSummaryWithLarge
			}

			return ''
		}
	},
	methods : {
		getCardOptions (option) {
			return this.twitterCardOptions.find(t => t.value === option)
		},
		cardSelect (option) {
			this.postEditorStore.currentPost.twitter_card = option
			this.postEditorStore.isDirty                  = true
		},
		scrollToElement () {
			const container = document.getElementsByClassName('component-wrapper')[0]
			setTimeout(() => {
				if (container) {
					container.firstChild.scrollTop = 0
				}
			}, 10)
		},
		saveTwitterImageType (value) {
			this.postEditorStore.currentPost.twitter_image_type = value
			this.postEditorStore.isDirty                        = true
		},
		updateImage (imageUrl) {
			this.postEditorStore.currentPost.twitter_image_custom_url = imageUrl
			this.postEditorStore.savePostState()
		},
		handleImageUpdate () {
			this.setImageUrl('twitter')
		},
		updateImagePreview (param) {
			if ('facebook' === param.social) {
				this.facebookImageUrl = param.image
			}
		}
	},
	mounted () {
		window.aioseoBus.$on('updateSocialImagePreview', this.updateImagePreview)
		window.aioseoBus.$on('updateFeaturedImage', this.handleImageUpdate)

		this.scrollToElement()

		this.setImageUrl('facebook').then(() => {
			this.setImageUrl('twitter')
		})
	},
	beforeUnmount () {
		window.aioseoBus.$off('updateFeaturedImage', this.handleImageUpdate)
		window.aioseoBus.$off('updateSocialImagePreview', this.updateImagePreview)
	}
}
</script>

<style lang="scss">
.tab-twitter {
	.twitter-image {
		img {
			margin-top: 20px;
			width: auto;
			max-width: 525px;
			max-height: 525px;
			height: auto;
		}

		&.vertical {
			img {
				max-width: 158px;
				max-height: 158px;
			}
		}
	}

	.aioseo-alert {
		margin-bottom: 20px;
	}
}
</style>