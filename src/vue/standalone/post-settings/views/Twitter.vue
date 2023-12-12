<template>
	<div class="tab-twitter">
		<core-settings-row
			noBorder
			noVerticalMargin
		>
			<template #content>
				<core-alert
					class="twitter-disabled-warning"
					v-if="!optionsStore.options.social.twitter.general.enable"
					v-html="strings.twitterDisabled"
					type="red"
				/>
			</template>
		</core-settings-row>

		<core-settings-row
			:name="strings.twitterPreview"
		>
			<template #content>
				<core-twitter-preview
					:card="postEditorStore.currentPost.twitter_card"
					:class="{ ismobilecard: postEditorStore.currentPost.socialMobilePreview }"
					:description="previewDescription"
					:image="imageUrl"
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
						@counter="count => updateCount(count, 'titleCount')"
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
						@counter="count => updateCount(count, 'descriptionCount')"
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
import { ImageSourceOptions, ImagePreview } from '@/vue/mixins/Image'
import { MaxCounts } from '@/vue/mixins/MaxCounts'
import { Tags } from '@/vue/mixins/Tags'
import { twitterCard } from '@/vue/mixins/PostSocial'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTwitterPreview from '@/vue/components/common/core/TwitterPreview'

export default {
	setup () {
		return {
			optionsStore    : useOptionsStore(),
			postEditorStore : usePostEditorStore(),
			rootStore       : useRootStore()
		}
	},
	components : {
		CoreAlert,
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow,
		CoreTwitterPreview
	},
	mixins : [ ImageSourceOptions, ImagePreview, MaxCounts, Tags, twitterCard ],
	props  : {
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
			separator        : undefined,
			titleCount       : 0,
			descriptionCount : 0,
			strings          : {
				twitterPreview              : this.$t.__('Twitter Preview', this.$td),
				useFB                       : this.$t.__('Use Data from Facebook Tab', this.$td),
				imageSource                 : this.$t.__('Image Source', this.$td),
				customFieldsName            : this.$t.__('Custom Field Name', this.$td),
				twitterImage                : this.$t.__('Twitter Image', this.$td),
				twitterTitle                : this.$t.__('Twitter Title', this.$td),
				twitterDescription          : this.$t.__('Twitter Description', this.$td),
				twitterCardType             : this.$t.__('Twitter Card Type', this.$td),
				minimumSizeSummary          : this.$t.__('Minimum size: 144px x 144px, ideal ratio 1:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', this.$td),
				minimumSizeSummaryWithLarge : this.$t.__('Minimum size: 300px x 157px, ideal ratio 2:1, 5MB max. JPG, PNG, WEBP and GIF formats only.', this.$td),
				twitterDisabled             : this.$t.sprintf(
					// Translators: 1 - "Open Graph", 2 - "Go to Social Networks ->".
					this.$t.__('No %1$s markup will be output for your post because it is currently disabled. You can enable %1$s markup in the Social Networks settings. %2$s', this.$td),
					this.$t.__('Twitter', this.$td),
					this.$t.sprintf(
						'<a href="%1$s" target="_blank">%2$s<span class="link-right-arrow">&nbsp;&rarr;</span></a>',
						this.rootStore.aioseo.urls.aio.socialNetworks + '#twitter',
						this.$t.__('Go to Social Networks', this.$td)
					)
				)
			}
		}
	},
	computed : {
		previewTitle () {
			const title = this.postEditorStore.currentPost.twitter_use_og ? this.postEditorStore.currentPost.og_title : this.postEditorStore.currentPost.twitter_title
			return this.parseTags(title || this.postEditorStore.currentPost.title || this.postEditorStore.currentPost.tags.title || '#post_title #separator_sa #site_title')
		},
		previewDescription () {
			const description = this.postEditorStore.currentPost.twitter_use_og ? this.postEditorStore.currentPost.og_description : this.postEditorStore.currentPost.twitter_description
			return this.parseTags(description || this.postEditorStore.currentPost.description || this.postEditorStore.currentPost.tags.description || '#post_content')
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
	mounted () {
		this.scrollToElement()
		this.setImageUrl('twitter')

		window.aioseoBus.$on('updateFeaturedImage', this.handleImageUpdate)
	},
	beforeUnmount () {
		window.aioseoBus.$off('updateFeaturedImage', this.handleImageUpdate)
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
}
</style>