<template>
	<div
		class="aioseo-details-column"
		:class="{
			editing: showEditTitle || showEditDescription || showEditImageTitle || showEditImageAltTag
		}"
	>
		<div>
			<div
				v-if="'edit' === $root.$data.screen.base && !isSpecialPage"
				class="edit-row scores"
			>
				<index-status
					v-if="showIndexStatus"
					:result="inspectionResult"
					:loading="inspectionResultLoading"
					:viewable="post.isPostVisible"
					tooltip-offset="-150px,0"
					refreshable
					@refresh="refreshInspectionResult"
				/>

				<core-tooltip
					type="action"
					v-if="showHeadlineAnalyzer"
				>
					<core-score-button
						:score="post.headlineScore"
						:postId="postId"
					>
						<template #icon>
							<svg-headline-analyzer />
						</template>
					</core-score-button>

					<template #tooltip>
						{{ strings.headlineScore }}
					</template>
				</core-tooltip>

				<core-tooltip
					type="action"
					v-if="showTruSeo && allowed('aioseo_page_analysis')"
				>
					<core-score-button
						:score="post.value"
						:postId="postId"
					>
						<template #icon>
							<svg-aioseo-logo-gear />
						</template>
					</core-score-button>

					<template #tooltip>
						{{ strings.truSeoScore }}
					</template>
				</core-tooltip>
			</div>

			<div>
				<core-tooltip
					v-if="allowed('aioseo_page_general_settings') && post.showTitle"
					class="aioseo-details-column__tooltip"
					:disabled="showEditTitle"
				>
					<div class="edit-row edit-title">
						<strong>{{ strings.title }}:</strong>

						<core-loader v-if="postLoading" dark />

						<svg-pencil
							v-if="!showEditTitle"
							@click.prevent="editTitle"
						/>

						<span v-if="!showEditTitle">
							{{ truncate(titleParsed, 100) }}
						</span>
					</div>

					<template #tooltip>
						<strong>{{ strings.title }}:</strong>
						{{ titleParsed }}
					</template>
				</core-tooltip>
			</div>

			<div
				v-if="showEditTitle"
				class="edit-row"
			>
				<core-html-tags-editor
					v-model="title"
					:line-numbers="false"
					single
					:tags-context="getTagText('post', post?.postType, 'Title')"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[ 'post_title' ]"
				/>

				<base-button
					type="gray"
					size="small"
					@click.prevent="cancel"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="save"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>

			<div>
				<core-tooltip
					v-if="allowed('aioseo_page_general_settings') && post.showDescription"
					class="aioseo-details-column__tooltip"
					:disabled="showEditDescription"
				>
					<div class="edit-row edit-description">
						<strong>{{ strings.description }}:</strong>

						<core-loader v-if="postLoading" dark />

						<svg-pencil
							v-if="!showEditDescription"
							@click.prevent="editDescription"
						/>

						<span
							v-if="!showEditDescription"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							{{ truncate(descriptionParsed) }}
						</span>
					</div>

					<template #tooltip>
						<strong>{{ strings.description }}:</strong>
						{{ truncate(descriptionParsed) }}
					</template>
				</core-tooltip>
			</div>

			<div
				v-if="showEditDescription"
				class="edit-row"
			>
				<core-html-tags-editor
					v-model="postDescription"
					:line-numbers="false"
					:tags-context="getTagText('post', post?.postType, 'Description')"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[ 'post_excerpt' ]"
				/>

				<base-button
					type="gray"
					size="small"
					@click.prevent="cancel"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="save"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>

			<slot />

			<div>
				<core-tooltip
					v-if="'upload' === $root.$data.screen.base && post.showMedia"
					class="aioseo-details-column__tooltip"
					:disabled="showEditImageTitle"
				>
					<div class="edit-row edit-image-title">
						<strong>{{ strings.imageTitle }}:</strong>

						<svg-pencil
							v-if="!showEditImageTitle"
							@click.prevent="editImageTitle"
						/>

						<span
							v-if="!showEditImageTitle"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							{{ imageTitle }}
						</span>
					</div>

					<template #tooltip>
						<strong>{{ strings.imageTitle }}:</strong>
						{{ imageTitle }}
					</template>
				</core-tooltip>
			</div>

			<div
				v-if="showEditImageTitle"
				class="edit-row"
			>
				<core-html-tags-editor
					v-model="imageTitle"
					:line-numbers="false"
					single
					tags-context="imageSeoTitleColumn"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[]"
				/>

				<base-button
					type="gray"
					size="small"
					@click.prevent="cancel"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="saveColumn"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>

			<div>
				<core-tooltip
					v-if="'upload' === $root.$data.screen.base && post.showMedia"
					class="aioseo-details-column__tooltip"
					:disabled="showEditImageAltTag"
				>
					<div class="edit-row edit-image-alt">
						<strong>{{ strings.imageAltTag }}:</strong>

						<core-loader v-if="generatingAlt" dark />

						<svg-pencil
							v-if="!showEditImageAltTag && !generatingAlt"
							@click.prevent="editImageAlt"
						/>

						<span
							v-if="!showEditImageAltTag && !generatingAlt"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							{{ imageAltTag }}
						</span>
					</div>

					<template #tooltip>
						<strong>{{ strings.imageAltTag }}:</strong>
						{{ imageAltTag }}
					</template>
				</core-tooltip>
			</div>

			<div
				v-if="showEditImageAltTag"
				class="edit-row"
			>
				<core-html-tags-editor
					v-model="imageAltTag"
					:line-numbers="false"
					single
					tags-context="imageSeoAltColumn"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[]"
				/>

				<base-button
					type="gray"
					size="small"
					@click.prevent="cancel"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="saveColumn"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>
		</div>
	</div>
</template>

<script>
import {
	useAiStore,
	useOptionsStore,
	useRootStore,
	useSearchStatisticsStore
} from '@/vue/stores'
import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import http from '@/vue/utils/http'
import { merge } from 'lodash-es'

import { useTruSeoScore } from '@/vue/composables/TruSeoScore'

import { truncate } from '@/vue/utils/html'
import license from '@/vue/utils/license'
import links from '@/vue/utils/links'
import tags from '@/vue/utils/tags'

import { shouldShowTruSeoScore } from '@/vue/plugins/tru-seo/components/helpers'
import BaseButton from '@/vue/components/common/base/Button'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import IndexStatus from '@/vue/components/AIOSEO_VERSION/search-statistics/IndexStatus'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgHeadlineAnalyzer from '@/vue/components/common/svg/HeadlineAnalyzer'
import SvgPencil from '@/vue/components/common/svg/Pencil'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export default {
	setup () {
		const {
			runAnalysis,
			strings
		} = useTruSeoScore()

		return {
			aiStore               : useAiStore(),
			composableStrings     : strings,
			optionsStore          : useOptionsStore(),
			rootStore             : useRootStore(),
			runAnalysis,
			searchStatisticsStore : useSearchStatisticsStore(),
			getTagText            : tags.getTagText
		}
	},
	components : {
		BaseButton,
		CoreHtmlTagsEditor,
		CoreLoader,
		CoreScoreButton,
		CoreTooltip,
		IndexStatus,
		SvgAioseoLogoGear,
		SvgHeadlineAnalyzer,
		SvgPencil
	},
	props : {
		post : Object
	},
	data () {
		return {
			allowed,
			postId                  : null,
			columnName              : null,
			title                   : null,
			titleParsed             : null,
			postDescription         : null,
			descriptionParsed       : null,
			imageTitle              : null,
			imageAltTag             : null,
			showEditTitle           : false,
			showEditDescription     : false,
			showEditImageTitle      : false,
			showEditImageAltTag     : false,
			showTruSeo              : false,
			isSpecialPage           : false,
			inspectionResult        : {},
			inspectionResultLoading : true,
			postLoading             : false,
			generatingAlt           : false,
			strings                 : merge(this.composableStrings, {
				title          : __('Title', td),
				description    : __('Description', td),
				imageTitle     : __('Image Title', td),
				imageAltTag    : __('Image Alt Tag', td),
				saveChanges    : __('Save Changes', td),
				discardChanges : __('Discard Changes', td),
				truSeoScore    : __('TruSEO Score', td),
				headlineScore  : __('Headline Score', td)
			})
		}
	},
	computed : {
		showIndexStatus () {
			if (!this.rootStore.isPro) {
				return false
			}

			if (!license.hasCoreFeature('search-statistics', 'index-status')) {
				return false
			}

			const isVerified  = !this.searchStatisticsStore.unverifiedSite
			const isConnected = !!this.searchStatisticsStore.isConnected
			const isAllowed   = this.allowed('aioseo_search_statistics_settings')

			return isVerified && isConnected && isAllowed
		},
		showHeadlineAnalyzer () {
			if (this.rootStore.aioseo.data.isClassicEditorActive) {
				return false
			}

			// We don't want to show the HA for products or AMP web stories.
			if ('product' === this.post.postType || 'web-story' === this.post.postType) {
				return false
			}

			return this.optionsStore.options.advanced.headlineAnalyzer
		}
	},
	methods : {
		save () {
			if (!allowed('aioseo_page_general_settings')) {
				return
			}

			this.showEditTitle       = false
			this.showEditDescription = false
			this.post.title          = this.title
			this.post.description    = this.postDescription
			this.postLoading         = true
			http.post(links.restUrl('posts-list/update-details-column'))
				.send({
					postId      : this.post.id,
					title       : this.post.title,
					description : this.post.description
				})
				.then((response) => {
					this.titleParsed       = response.body.title
					this.descriptionParsed = response.body.description

					this.post.titleParsed       = response.body.title
					this.post.descriptionParsed = response.body.description

					if ('upload' !== this.$root.$data.screen.base) {
						this.runAnalysis(this.post.id)
					}
				})
				.catch(error => {
					console.error(`Unable to update post with ID ${this.post.id}: ${error}`)
				})
				.finally(() => {
					this.postLoading = false
				})
		},
		saveColumn () {
			if (!allowed('aioseo_page_general_settings')) {
				return
			}

			this.showEditImageTitle  = false
			this.showEditImageAltTag = false
			this.post.title          = this.title
			this.post.description    = this.postDescription
			this.post.imageTitle     = this.imageTitle
			this.post.imageAltTag    = this.imageAltTag

			http.post(links.restUrl('posts-list/update-details-column'))
				.send({
					postId      : this.post.id,
					isMedia     : true,
					title       : this.post.title,
					description : this.post.description,
					imageTitle  : this.post.imageTitle,
					imageAltTag : this.post.imageAltTag
				})
				.then(() => {
					this.updatePostTitle(this.post.id, this.post.imageTitle)
				})
				.catch(error => {
					console.error(`Unable to update attachment with ID ${this.post.id}: ${error}`)
				})
		},
		cancel () {
			this.showEditTitle       = false
			this.showEditDescription = false
			this.showEditImageTitle  = false
			this.showEditImageAltTag = false
		},
		editTitle () {
			this.showEditTitle = true
		},
		editDescription () {
			this.showEditDescription  = true
		},
		editImageTitle () {
			this.showEditImageTitle = true
		},
		editImageAlt () {
			this.showEditImageAltTag = true
		},
		truncate,
		updatePostTitle (postId, value) {
			const post = document.getElementById(`post-${postId}`)
			if (!post) {
				return
			}
			const title = post.getElementsByClassName('title')[0].getElementsByTagName('a')[0]
			if (!title) {
				return
			}
			const image = title.getElementsByTagName('span')[0]
			title.innerText = value
			title.prepend(image)
		},
		getRowActionLink () {
			const row = document.getElementById(`post-${this.postId}`)

			return row ? row.querySelector('.aioseo_generate_alt a') : null
		},
		setRowActionGenerating (generating) {
			const link = this.getRowActionLink()
			if (!link) {
				return
			}

			if (generating) {
				link.dataset.originalText = link.textContent
				link.textContent = link.dataset.originalText + '…'
				link.style.cursor = 'wait'
				link.style.opacity = '0.5'
			} else {
				link.textContent = link.dataset.originalText || link.textContent
				link.style.cursor = ''
				link.style.opacity = ''
			}
		},
		async generateAltInline () {
			if (this.generatingAlt || this.showEditImageAltTag) {
				return
			}

			this.generatingAlt = true
			this.setRowActionGenerating(true)

			try {
				const response = await this.aiStore.generateImageAlt({ attachmentId: this.postId })
				const altText  = response.body.altTexts[0]

				const request = () => http.post(links.restUrl('posts-list/update-details-column'))
					.send({
						postId      : this.postId,
						isMedia     : true,
						imageAltTag : altText,
						imageTitle  : this.imageTitle || ''
					})

				// Retry once if the request fails due to a temporary error (network issues, etc.).
				await request().catch(() => request())

				this.imageAltTag      = altText
				this.post.imageAltTag = altText
			} catch (error) {
				console.error('Failed to generate alt text:', error)
			} finally {
				this.generatingAlt = false
				this.setRowActionGenerating(false)
			}
		},
		updateInspectionResult (post) {
			const { inspectionResult, inspectionResultLoading } = post

			this.inspectionResult        = inspectionResult
			this.inspectionResultLoading = inspectionResultLoading
		},
		async refreshInspectionResult () {
			this.inspectionResultLoading = true

			try {
				const response = await this.searchStatisticsStore.getInspectionResult({
					paths : this.post.page,
					force : true
				})

				this.inspectionResult = response?.[this.post.page]
			} catch (error) {
				console.error(error)
			} finally {
				this.inspectionResultLoading = false
			}
		}
	},
	mounted () {
		this.postId                  = this.post.id
		this.columnName              = this.post.columnName
		this.imageTitle              = this.post.imageTitle
		this.imageAltTag             = this.post.imageAltTag
		this.isSpecialPage           = this.post.isSpecialPage
		this.title                   = this.post.title || this.post.defaultTitle
		this.titleParsed             = this.post.titleParsed
		this.postDescription         = this.post.description || this.post.defaultDescription
		this.descriptionParsed       = this.post.descriptionParsed
		this.inspectionResult        = this.post.inspectionResult
		this.inspectionResultLoading = this.post.inspectionResultLoading

		// If the post data changed, we need to parse the title and description again.
		// This can happen after using the quick-edit feature.
		if (this.post.reload) {
			this.save()
		}

		window.aioseoBus.$on('updateInspectionResult' + this.postId, this.updateInspectionResult)
		window.aioseoBus.$on('generateAltInline' + this.postId, this.generateAltInline)
	},
	beforeUnmount () {
		window.aioseoBus.$off('updateInspectionResult' + this.postId, this.updateInspectionResult)
		window.aioseoBus.$off('generateAltInline' + this.postId, this.generateAltInline)
	},
	created () {
		this.showTruSeo = shouldShowTruSeoScore()
	}
}
</script>

<style lang="scss">
.aioseo-details-column {
	display: block;
	overflow: hidden;
	width: 100%;

	&.editing {
		max-height: initial;
		overflow: visible;
	}

	&__tooltip {
		display: inline-block;
		margin-left: 0;
		max-width: 100%;
		width: auto;
	}

	.edit-row {
		margin-bottom: 10px;

		&.edit-title,
		&.edit-description,
		&.edit-image-title,
		&.edit-image-alt {
			display: grid;
			grid-template-columns: repeat(3, auto);
			align-items: center;
			column-gap: 4px;

			> strong {
				white-space: nowrap;
			}

			> span {
				grid-column: 1 / -1;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}

			.aioseo-loading-spinner {
				position: relative;
				width: 18px;
				height: 18px;
			}

			.aioseo-pencil {
				opacity: 0;
				cursor: pointer;
				color: $black;
				width: 16px;
				height: 16px;
			}

			&:hover {
				.aioseo-pencil {
					opacity: 1;
				}
			}
		}

		.aioseo-html-tags-editor {
			margin-bottom: 4px;

			.ql-editor,
			.aioseo-add-template-tag {
				background: $white;
			}

			@media (max-width: 1300px) {
				.add-tags {
					flex-direction: column;
					align-items: start;
				}
			}

			.aioseo-emoji-picker em-emoji-picker {
				right: 0;
				left: auto;
			}
		}

		.aioseo-button {
			margin-right: 2px;
			margin-bottom: 2px;

			@media screen and (max-width: 1366px) {
				width: 100%;
				margin-right: 0;
			}
		}

		&.scores {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 8px;

			.aioseo-tooltip {
				margin-left: 0;

				> div + div { // Fix for tooltip alignment.
					line-height: 0;
				}
			}
		}
	}
}

td.seotitle.column-seotitle,
td.seodesc.column-seodesc,
td.seokeywords.column-seokeywords {
	overflow: visible;
}

@media screen and (max-width: 782px) {
	body.wp-admin {
		th.column-seotitle,
		th.column-seodesc,
		th.column-seokeywords,
		td.seotitle.column-seotitle,
		td.seodesc.column-seodesc,
		td.seokeywords.column-seokeywords {
			display: none;
		}
	}
}
</style>