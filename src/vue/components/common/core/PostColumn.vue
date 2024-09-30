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
					:result="inspectionResult?.indexStatusResult"
					:result-link="inspectionResult?.inspectionResultLink"
					:loading="inspectionResultLoading"
					:viewable="post.isPostVisible"
					tooltip-offset="-150px,0"
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
					v-if="allowed('aioseo_page_general_settings')"
					class="aioseo-details-column__tooltip"
					:disabled="showEditTitle"
				>
					<div class="edit-row edit-title">
						<strong>{{ strings.title }}</strong>

						<span v-if="!showEditTitle">
							<strong>:</strong>
							{{ truncate(titleParsed, 100) }}
						</span>

						<core-loader v-if="postLoading" dark />

						<svg-pencil
							v-if="!showEditTitle"
							class="pencil-icon"
							@click.prevent="editTitle"
						/>
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
					tags-context="postTitle"
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
					v-if="allowed('aioseo_page_general_settings')"
					class="aioseo-details-column__tooltip"
					:disabled="showEditDescription"
				>
					<div class="edit-row edit-description">
						<strong>{{ strings.description }}</strong>

						<span
							v-if="!showEditDescription"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							<strong>:</strong>
							{{ truncate(descriptionParsed) }}
						</span>

						<core-loader v-if="postLoading" dark />

						<svg-pencil
							v-if="!showEditDescription"
							class="pencil-icon"
							@click.prevent="editDescription"
						/>
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
					tags-context="postDescription"
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
						<strong>{{ strings.imageTitle }}</strong>

						<span
							v-if="!showEditImageTitle"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							<strong>:</strong>
							{{ imageTitle }}
						</span>

						<svg-pencil
							v-if="!showEditImageTitle"
							class="pencil-icon"
							@click.prevent="editImageTitle"
						/>
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
					tags-context="attachmentTitle"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[ 'image_title' ]"
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
					@click.prevent="saveImage"
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
						<strong>{{ strings.imageAltTag }}</strong>

						<span
							v-if="!showEditImageAltTag"
							:id="`aioseo-${columnName}-${postId}-value`"
						>
							<strong>:</strong>
							{{ imageAltTag }}
						</span>

						<svg-pencil
							v-if="!showEditImageAltTag"
							class="pencil-icon"
							@click.prevent="editImageAlt"
						/>
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
					tags-context="attachmentDescription"
					defaultMenuOrientation="bottom"
					tagsDescription=''
					:default-tags="[ 'alt_tag' ]"
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
					@click.prevent="saveImage"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>
		</div>
	</div>
</template>

<script>
import {
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
			composableStrings     : strings,
			optionsStore          : useOptionsStore(),
			rootStore             : useRootStore(),
			runAnalysis,
			searchStatisticsStore : useSearchStatisticsStore()
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
		post  : Object,
		posts : Array
	},
	data () {
		return {
			allowed,
			postId                  : null,
			columnName              : null,
			value                   : null,
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
			inspectionResult        : false,
			inspectionResultLoading : true,
			postLoading             : false,
			strings                 : merge(this.composableStrings, {
				title          : __('Title', td),
				description    : __('Description', td),
				imageTitle     : __('Image Title', td),
				imageAltTag    : __('Image Alt Tag', td),
				saveChanges    : __('Save Changes', td),
				discardChanges : __('Discard Changes', td),
				truSeoScore    : __('TruSEO Score', td),
				headlineScore  : __('Headline Score', td)
			}),
			license
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
			const isConnected = 'string' === typeof this.optionsStore.internalOptions.internal?.searchStatistics?.profile?.key
			const isAllowed   = this.allowed('aioseo_search_statistics_settings')

			return isVerified && isConnected && isAllowed
		},
		showHeadlineAnalyzer () {
			if (this.rootStore.aioseo.data.isClassicEditorActive) {
				return false
			}

			// We don't want to show the HA for products.
			if ('product' === this.post.postType) {
				return false
			}

			return this.optionsStore.options.advanced.headlineAnalyzer
		}
	},
	methods : {
		save () {
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
		saveImage () {
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
				.then(() => {})
				.catch(error => {
					console.error(`Unable to update attachment with ID ${this.post.id}: ${error}`)
				})
		},
		cancel () {
			this.value               = this.post.value
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
		updateInspectionResult (post) {
			const { inspectionResult, inspectionResultLoading } = post

			this.inspectionResult        = inspectionResult
			this.inspectionResultLoading = inspectionResultLoading
		}
	},
	mounted () {
		this.postId                  = this.post.id
		this.columnName              = this.post.columnName
		this.value                   = this.post.value
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
	},
	beforeUnmount () {
		window.aioseoBus.$off('updateInspectionResult' + this.postId, this.updateInspectionResult)
	},
	async created () {
		this.showTruSeo = shouldShowTruSeoScore()
	}
}
</script>

<style lang="scss">
.aioseo-details-column {
	float: left;
	display: block;
	opacity: 1;
	overflow: hidden;
	width: 100%;

	&.editing {
		max-height: initial;
		overflow: visible;
	}

	.dashicons {
		cursor: pointer;
	}

	.aioseo-quickedit {
		margin-right: 5px;
		color: #72777c;

		&:hover {
			color: #0073aa;
			outline: 0;
		}
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
			display: flex;

			span {
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}

			.aioseo-loading-spinner {
				position: relative;
				width: 18px;
				height: 18px;
				margin-left: 5px;
				vertical-align: middle;
			}

			.aioseo-pencil {
				margin-left: 5px;
				padding-left: 2px;
				flex: 0 0 16px;
				opacity: 0;
				display: inline-block;
				vertical-align: middle;
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