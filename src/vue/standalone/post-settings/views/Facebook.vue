<template>
	<div class="tab-facebook">
		<core-settings-row class="snippet-preview-row">
			<template #name>
				<span>{{ strings.tabName }}</span>
			</template>

			<template #content>
				<core-alert
					class="facebook-disabled-warning"
					v-if="!optionsStore.options.social.facebook.general.enable"
					v-html="strings.facebookDisabled"
					type="red"
				/>

				<core-facebook-preview
					:description="previewDescription"
					:image="imageUrl"
					:loading="loading"
					:title="previewTitle"
				/>
			</template>
		</core-settings-row>

		<div id="aioseo-post-settings-facebook">
			<core-settings-row
				:name="strings.facebookTitle"
				class="facebook-title-settings"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="facebook-meta-input"
						v-model="postEditorStore.currentPost.og_title"
						:line-numbers="false"
						single
						@counter="count => updateCount(count, 'titleCount')"
						@update:modelValue="postEditorStore.isDirty = true"
						:tags-context="`${postEditorStore.currentPost.postType || postEditorStore.currentPost.termType}Title`"
						:default-tags="tags.getDefaultTags('term' === postEditorStore.currentPost.context ? 'taxonomies' : null, null, 'title')"
					>
						<template #tags-description>
							{{ strings.clickToAddSiteName }}
						</template>
					</core-html-tags-editor>

					<div class="max-recommended-count"
						v-html="maxRecommendedCount(titleCount, 95)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.facebookDescription"
				class="facebook-description-settings"
				align
			>
				<template #content>
					<core-html-tags-editor
						class="facebook-meta-input"
						v-model="postEditorStore.currentPost.og_description"
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

					<div class="max-recommended-count"
						v-html="maxRecommendedCount(descriptionCount, 200)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="facebook-image-source"
				:name="strings.imageSource"
				align
			>
				<template #content>
					<base-select
						size="medium"
						:options="imageSourceOptionsFiltered"
						:modelValue="getImageSourceOptionFiltered(postEditorStore.currentPost.og_image_type)"
						@update:modelValue="value => saveImageType(value.value)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'custom' === postEditorStore.currentPost.og_image_type"
				:name="strings.customFieldsName"
				align
			>
				<template #content>
					<base-input
						type="text"
						size="medium"
						:placeholder="strings.placeholder"
						v-model="postEditorStore.currentPost.og_image_custom_fields"
						@update:modelValue="postEditorStore.isDirty = true"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="'custom_image' === postEditorStore.currentPost.og_image_type"
				class="facebook-image"
				:name="strings.facebookImage"
			>
				<template #content>
					<core-image-uploader
						:description="strings.minimumSize"
						v-model="postEditorStore.currentPost.og_image_custom_url"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="facebook-video"
				:name="strings.video"
				align
			>
				<template #content>
					<base-input
						type="text"
						size="medium"
						v-model="postEditorStore.currentPost.og_video"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				class="facebook-object-type"
				:name="strings.facebookObjectType"
				align
			>
				<template #content>
					<base-select
						size="medium"
						open-direction="top"
						:options="objectTypeOptions"
						group-label="groupLabel"
						group-values="options"
						:modelValue="getObjectTypeOptions(postEditorStore.currentPost.og_object_type)"
						@update:modelValue="value => setObjectType(value.value)"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="shouldShowArticleSection"
				:name="strings.articleSection"
				align
			>
				<template #content>
					<base-input
						type="text"
						size="medium"
						v-model="postEditorStore.currentPost.og_article_section"
					/>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="shouldShowArticleSection"
				:name="strings.articleTags"
				align
			>
				<template #content>
					<base-select
						multiple
						taggable
						:options="[]"
						:modelValue="postEditorStore.currentPost.og_article_tags || []"
						@update:modelValue="values => postEditorStore.currentPost.og_article_tags = values"
						:tag-placeholder="strings.tagPlaceholder"
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

import { ImageSourceOptions, ImagePreview } from '@/vue/mixins/Image'
import { MaxCounts } from '@/vue/mixins/MaxCounts'
import { Tags } from '@/vue/mixins/Tags'
import { objectType } from '@/vue/mixins/PostSocial'

import tags from '@/vue/utils/tags'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreFacebookPreview from '@/vue/components/common/core/FacebookPreview'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreImageUploader from '@/vue/components/common/core/ImageUploader'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'

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
		CoreFacebookPreview,
		CoreHtmlTagsEditor,
		CoreImageUploader,
		CoreSettingsRow
	},
	mixins : [ ImageSourceOptions, ImagePreview, MaxCounts, Tags, objectType ],
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
				tabName                       : this.$t.__('Facebook Preview', this.$td),
				imageSource                   : this.$t.__('Image Source', this.$td),
				customFieldsName              : this.$t.__('Custom Field Name', this.$td),
				video                         : this.$t.__('Video URL', this.$td),
				width                         : this.$t.__('Width', this.$td),
				height                        : this.$t.__('Height', this.$td),
				facebookObjectType            : this.$t.__('Object Type', this.$td),
				facebookImage                 : this.$t.__('Facebook Image', this.$td),
				facebookTitle                 : this.$t.__('Facebook Title', this.$td),
				facebookDescription           : this.$t.__('Facebook Description', this.$td),
				minimumSize                   : this.$t.__('Minimum size: 200px x 200px, ideal ratio 1.91:1, 5MB max. (eg: 1640px x 856px or 3280px x 1712px for Retina screens). JPG, PNG, WEBP and GIF formats only.', this.$td),
				clickToAddSiteName            : this.$t.__('Click on the tags below to insert variables into your site name.', this.$td),
				clickToAddHomePageDescription : this.$t.__('Click on the tags below to insert variables into your meta description.', this.$td),
				articleSection                : this.$t.__('Article Section', this.$td),
				articleTags                   : this.$t.__('Article Tags', this.$td),
				tagPlaceholder                : this.$t.__('Press enter to create an article tag', this.$td),
				facebookDisabled              : this.$t.sprintf(
					// Translators: 1 - "Open Graph", 2 - "Go to Social Networks ->".
					this.$t.__('No %1$s markup will be output for your post because it is currently disabled. You can enable %1$s markup in the Social Networks settings. %2$s', this.$td),
					this.$t.__('Open Graph', this.$td),
					this.$t.sprintf(
						'<a href="%1$s" target="_blank">%2$s<span class="link-right-arrow">&nbsp;&rarr;</span></a>',
						this.rootStore.aioseo.urls.aio.socialNetworks + '#facebook',
						this.$t.__('Go to Social Networks', this.$td)
					)
				)
			}
		}
	},
	computed : {
		previewTitle () {
			return this.parseTags(this.postEditorStore.currentPost.og_title || this.postEditorStore.currentPost.title || this.postEditorStore.currentPost.tags.title || '#post_title #separator_sa #site_title')
		},
		previewDescription () {
			return this.parseTags(this.postEditorStore.currentPost.og_description || this.postEditorStore.currentPost.description || this.postEditorStore.currentPost.tags.description || '#post_content')
		},
		shouldShowArticleSection () {
			const context = 'term' === this.postEditorStore.currentPost.context ? 'taxonomies' : 'postTypes'
			return 'article' === this.postEditorStore.currentPost.og_object_type ||
				(
					'default' === this.postEditorStore.currentPost.og_object_type &&
					'article' === this.optionsStore.dynamicOptions.social.facebook.general[context][this.postEditorStore.currentPost.postType || this.postEditorStore.currentPost.termType].objectType
				)
		}
	},
	methods : {
		scrollToElement () {
			const container = document.getElementsByClassName('component-wrapper')[0]
			setTimeout(() => {
				if (container) {
					container.firstChild.scrollTop = 0
				}
			}, 10)
		},
		saveImageType (value) {
			this.postEditorStore.currentPost.og_image_type = value
			this.postEditorStore.isDirty                   = true
		},
		getObjectTypeOptions (savedOption) {
			let option = null
			this.objectTypeOptions.forEach(group => {
				const localOption = group.options.find(o => o.value === savedOption)
				if (localOption) {
					option = localOption
				}
			})

			return option
		},
		setObjectType (option) {
			this.postEditorStore.currentPost.og_object_type = option
			this.postEditorStore.isDirty                    = true
		},
		updateImage (imageUrl) {
			this.postEditorStore.currentPost.og_image_custom_url = imageUrl
			this.postEditorStore.savePostState()
		},
		handleImageUpdate () {
			this.setImageUrl('facebook')
		}
	},
	watch : {
		'postEditorStore.currentPost.og_image_type' () {
			this.handleImageUpdate()
		},
		'postEditorStore.currentPost.og_image_custom_url' () {
			this.handleImageUpdate()
		}
	},
	mounted () {
		this.scrollToElement()
		this.setImageUrl('facebook')

		window.aioseoBus.$on('updateFeaturedImage', this.handleImageUpdate)
	},
	beforeUnmount () {
		window.aioseoBus.$off('updateFeaturedImage', this.handleImageUpdate)
	}
}
</script>
<style lang="scss">
.tab-facebook {
	.facebook-image {
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