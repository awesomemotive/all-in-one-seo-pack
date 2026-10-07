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
					v-if="showTruSeo && allowed('aioseo_page_analysis')"
				>
					<div class="aioseo-details-column__truseo">
						<div
							v-if="truSeoScanning"
							class="aioseo-details-column__loading">
							<core-loader dark />
						</div>

						<core-score-button
							v-else
							:score="post.value"
							:postId="postId"
						>
							<template #icon>
								<svg-aioseo-logo-gear />
							</template>
						</core-score-button>
					</div>

					<template #tooltip>
						{{ strings.seoScoreTooltip }}
					</template>
				</core-tooltip>

				<core-tooltip
					v-if="post.isNoindexed"
					class="aioseo-details-column__robots-tooltip"
				>
					<span class="aioseo-details-column__robots">{{ strings.noindex }}</span>

					<template #tooltip>
						{{ strings.noindexTooltip }}
					</template>
				</core-tooltip>
			</div>

			<details-field
				v-if="allowed('aioseo_page_general_settings') && post.showTitle"
				v-model="title"
				:parsed="titleParsed"
				:label="strings.title"
				:is-custom="isCustomTitle"
				:editing="showEditTitle"
				:loading="isFieldLoading('title')"
				:labelled="isMediaScreen"
				single
				:length="showLengthBadges ? titleLength : null"
				:tags-context="getTagText('post', post?.postType, 'Title')"
				:default-tags="[ 'post_title' ]"
				:custom-tooltip="strings.customTitle"
				:default-tooltip="strings.defaultTitle"
				@edit="editTitle"
				@save="save('title')"
				@cancel="cancel('title')"
			/>

			<details-field
				v-if="allowed('aioseo_page_general_settings') && post.showDescription"
				v-model="postDescription"
				:parsed="descriptionParsed"
				:label="strings.description"
				:is-custom="isCustomDescription"
				:editing="showEditDescription"
				:loading="isFieldLoading('description')"
				:labelled="isMediaScreen"
				:length="showLengthBadges ? descriptionLength : null"
				:tags-context="getTagText('post', post?.postType, 'Description')"
				:default-tags="[ 'post_excerpt' ]"
				:custom-tooltip="strings.customDescription"
				:default-tooltip="strings.defaultDescription"
				@edit="editDescription"
				@save="save('description')"
				@cancel="cancel('description')"
			/>
			<slot />

			<div
				v-if="isMediaScreen && post.showMedia && !showEditImageTitle"
				class="aioseo-details-column__field aioseo-details-column__field--labelled"
			>
				<span class="aioseo-details-column__flabel">{{ strings.imageTitle }}</span>

				<core-tooltip
					v-if="hasText(imageTitle)"
					class="aioseo-details-column__tooltip"
				>
					<span
						:id="`aioseo-${columnName}-${postId}-value`"
						class="aioseo-details-column__value aioseo-details-column__value--single"
					>
						{{ imageTitle }}
					</span>

					<template #tooltip>
						<strong>{{ strings.imageTitle }}:</strong>
						{{ imageTitle }}
					</template>
				</core-tooltip>

				<span
					v-else
					class="aioseo-details-column__value aioseo-details-column__value--single"
				>{{ emptyValue }}</span>

				<svg-pencil @click.prevent="editImageTitle" />
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
					@click.prevent="cancel('imageTitle')"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="saveColumn('imageTitle')"
				>
					{{ strings.saveChanges }}
				</base-button>
			</div>

			<div
				v-if="isMediaScreen && post.showMedia && !showEditImageAltTag && !generatingAlt"
				class="aioseo-details-column__field aioseo-details-column__field--labelled"
			>
				<span class="aioseo-details-column__flabel">{{ strings.imageAltTag }}</span>

				<core-tooltip
					v-if="hasText(imageAltTag)"
					class="aioseo-details-column__tooltip"
				>
					<span
						:id="`aioseo-${columnName}-${postId}-value`"
						class="aioseo-details-column__value aioseo-details-column__value--single"
					>
						{{ imageAltTag }}
					</span>

					<template #tooltip>
						<strong>{{ strings.imageAltTag }}:</strong>
						{{ imageAltTag }}
					</template>
				</core-tooltip>

				<span
					v-else
					class="aioseo-details-column__value aioseo-details-column__value--single"
				>{{ emptyValue }}</span>

				<core-loader v-if="generatingAlt" dark />

				<svg-pencil @click.prevent="editImageAlt" />
			</div>

			<div
				v-if="isMediaScreen && post.showMedia && altError && !showEditImageAltTag && !generatingAlt"
				class="aioseo-details-column__error"
			>
				{{ altError }}
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
					@click.prevent="cancel('imageAltTag')"
				>
					{{ strings.discardChanges }}
				</base-button>

				<base-button
					type="blue"
					size="small"
					@click.prevent="saveColumn('imageAltTag')"
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

import license from '@/vue/utils/license'
import { MIN_WIDTH_FOR_BADGES, useDetailsFieldLength } from '@/vue/composables/DetailsFieldLength'
import links from '@/vue/utils/links'
import tags from '@/vue/utils/tags'

import { shouldShowTruSeoScore } from '@/vue/utils/postData/helpers'
import BaseButton from '@/vue/components/common/base/Button'
import CoreHtmlTagsEditor from '@/vue/components/common/core/HtmlTagsEditor'
import CoreLoader from '@/vue/components/common/core/Loader'
import DetailsField from '@/vue/components/common/core/DetailsField'
import CoreScoreButton from '@/vue/components/common/core/ScoreButton'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import IndexStatus from '@/vue/components/AIOSEO_VERSION/search-statistics/IndexStatus'
import SvgAioseoLogoGear from '@/vue/components/common/svg/aioseo/LogoGear'
import SvgPencil from '@/vue/components/common/svg/Pencil'

import { __, _x } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// Below this the length badge takes more room than the value it describes, so the
// column drops it. Screen Options and narrow viewports both get us there.

// Matches how WordPress fills an empty list table cell.
const EMPTY_VALUE = '\u2014'

export default {
	setup () {
		const {
			runAnalysis,
			strings
		} = useTruSeoScore()
		const { getTitleLength, getDescriptionLength } = useDetailsFieldLength()

		return {
			aiStore               : useAiStore(),
			getTitleLength,
			getDescriptionLength,
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
		DetailsField,
		IndexStatus,
		SvgAioseoLogoGear,
		SvgPencil
	},
	props : {
		post : Object
	},
	data () {
		return {
			allowed,
			columnWidth             : 0,
			resizeObserver          : null,
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
			loadingField            : null,
			truSeoScanning          : false,
			generatingAlt           : false,
			altError                : '',
			strings                 : merge(this.composableStrings, {
				title              : __('SEO Title', td),
				description        : __('Meta Description', td),
				imageTitle         : __('Image Title', td),
				imageAltTag        : __('Image Alt Tag', td),
				noCredits          : __('Insufficient AI credits.', td),
				altNotGenerated    : __('The alt text could not be generated. Please try again.', td),
				saveChanges        : __('Save', td),
				discardChanges     : __('Cancel', td),
				customTitle        : __('Custom SEO title written for this post', td),
				defaultTitle       : __('Default SEO title generated from your template', td),
				customDescription  : __('Custom meta description written for this post', td),
				defaultDescription : __('Default meta description generated from your template', td),
				noDescription      : __('No meta description set', td),
				// Translators: 1 - Character count, 2 - Recommended minimum, 3 - Recommended maximum.
				// Translators: 1 - Width in pixels, 2 - Recommended minimum, 3 - Recommended maximum.
				noindex            : _x('Noindex', 'Robots meta directive shown as a badge.', td),
				noindexTooltip     : __('This post is set to noindex, so search engines are told to keep it out of search results.', td)
			})
		}
	},
	computed : {
		emptyValue () {
			return EMPTY_VALUE
		},
		// The badges are the first thing to go when the column is squeezed: at that point
		// they cost more of the value than the signal is worth.
		minWidthForBadges () {
			return MIN_WIDTH_FOR_BADGES
		},
		showLengthBadges () {
			if (this.isMediaScreen) {
				return false
			}

			return !this.columnWidth || this.columnWidth >= this.minWidthForBadges
		},
		isMediaScreen () {
			return 'upload' === this.$root.$data.screen.base
		},
		// A missing meta description is a real defect on a post, but not on an attachment,
		// where almost nothing has one — flagging every row red was pure noise.
		emptyDescriptionText () {
			return this.isMediaScreen ? EMPTY_VALUE : this.strings.noDescription
		},
		isCustomTitle () {
			return !!this.post.title && this.post.title !== this.post.defaultTitle
		},
		isCustomDescription () {
			return !!this.post.description && this.post.description !== this.post.defaultDescription
		},
		titleLength () {
			return this.getTitleLength(this.titleParsed)
		},
		descriptionLength () {
			return this.getDescriptionLength(this.descriptionParsed)
		},
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
		}
	},
	methods : {
		// An editor left at the template means "keep using the default", which is stored empty.
		storedValue (value, template) {
			return value === template ? '' : value
		},
		// A tooltip that only carries a field label and an empty value is noise, so the
		// value tooltips are disabled when there is nothing to show.
		hasText (value) {
			return !!(value && String(value).trim())
		},
		isFieldLoading (field) {
			return field === this.loadingField || 'both' === this.loadingField
		},
		observeColumnWidth () {
			if (!window.ResizeObserver) {
				return
			}

			this.resizeObserver = new window.ResizeObserver(entries => {
				const width = entries[0]?.contentRect?.width
				if (width) {
					this.columnWidth = Math.round(width)
				}
			})

			this.resizeObserver.observe(this.$el)
		},
		refreshParsedValues () {
			this.loadingField = 'both'

			http.post(links.restUrl('posts-list/load-details-column'))
				.send({ ids: [ this.post.id ] })
				.then(response => {
					const fresh = response.body?.posts?.find(p => p.id === this.post.id)
					if (!fresh) {
						return
					}

					this.titleParsed            = fresh.titleParsed
					this.descriptionParsed      = fresh.descriptionParsed
					this.post.titleParsed       = fresh.titleParsed
					this.post.descriptionParsed = fresh.descriptionParsed
				})
				.catch(error => {
					console.error(`Unable to refresh post ${this.post.id}: ${error}`)
				})
				.finally(() => {
					this.loadingField = null
				})
		},
		save (field) {
			if (!allowed('aioseo_page_general_settings')) {
				return
			}

			// Commit only the field being saved so an open editor for the other field keeps its
			// saved baseline (used by cancel() to revert) and isn't persisted with unsaved changes.
			// An editor left at the template is stored empty so the field keeps following the
			// template if the default changes.
			if ('title' === field) {
				this.post.title    = this.storedValue(this.title, this.post.defaultTitle)
				this.showEditTitle = false
			}

			if ('description' === field) {
				this.post.description    = this.storedValue(this.postDescription, this.post.defaultDescription)
				this.showEditDescription = false
			}

			this.loadingField = field
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
						this.runAnalysis({ postId: this.post.id })
					}
				})
				.catch(error => {
					console.error(`Unable to update post with ID ${this.post.id}: ${error}`)
				})
				.finally(() => {
					this.loadingField = null
				})
		},
		saveColumn (field) {
			if (!allowed('aioseo_page_general_settings')) {
				return
			}

			// Commit only the field being saved so the other open editors keep their saved baseline.
			if ('imageTitle' === field) {
				this.post.imageTitle    = this.imageTitle
				this.showEditImageTitle = false
			}

			if ('imageAltTag' === field) {
				this.post.imageAltTag    = this.imageAltTag
				this.showEditImageAltTag = false

				// An alt text typed by hand replaces the one AI couldn't generate, so its error no longer applies.
				// Saving an empty one leaves the image without alt text, so the error stays.
				if (String(this.imageAltTag || '').trim()) {
					this.altError = ''
				}
			}

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
		cancel (field) {
			// Revert the field's unsaved value to its saved baseline and close only that editor.
			if ('title' === field) {
				this.title         = this.post.title || this.post.defaultTitle
				this.showEditTitle = false
			}

			if ('description' === field) {
				this.postDescription     = this.post.description || this.post.defaultDescription
				this.showEditDescription = false
			}

			if ('imageTitle' === field) {
				this.imageTitle         = this.post.imageTitle
				this.showEditImageTitle = false
			}

			if ('imageAltTag' === field) {
				this.imageAltTag         = this.post.imageAltTag
				this.showEditImageAltTag = false
			}
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
			this.altError      = ''
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

				this.altError = 'insufficient_credits' === error.response?.body?.code
					? this.strings.noCredits
					: this.strings.altNotGenerated
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
		updateScanningState (isScanning) {
			this.truSeoScanning = isScanning
		},
		updateScore (score) {
			this.post.value = score
			this.value = score
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

		// A quick edit can change the WP title or slug, which the parsed values are built
		// from, so they're re-read here. Re-reading, not re-saving: save() would post the
		// editor's values back, and those fall back to the default template — which would
		// store the template as an explicit override and spawn a second SEO revision.
		if (this.post.reload) {
			this.refreshParsedValues()
		}

		this.observeColumnWidth()

		window.aioseoBus.$on('updateInspectionResult' + this.postId, this.updateInspectionResult)
		window.aioseoBus.$on('batchScanLoading' + this.postId, this.updateScanningState)
		window.aioseoBus.$on('batchScanScoreUpdate' + this.postId, this.updateScore)
		window.aioseoBus.$on('generateAltInline' + this.postId, this.generateAltInline)
	},
	beforeUnmount () {
		if (this.resizeObserver) {
			this.resizeObserver.disconnect()
			this.resizeObserver = null
		}

		window.aioseoBus.$off('updateInspectionResult' + this.postId, this.updateInspectionResult)
		window.aioseoBus.$off('generateAltInline' + this.postId, this.generateAltInline)
		window.aioseoBus.$off('batchScanLoading' + this.postId, this.updateScanningState)
		window.aioseoBus.$off('batchScanScoreUpdate' + this.postId, this.updateScore)
	},
	created () {
		// PHP resolves eligibility per post (master toggle, the analysed post-type list, the
		// never-analysable types, special pages), so prefer its answer — excluding a post type used to
		// hide only the Optimization tab and leave a stale score in this column. The screen-level helper
		// remains the fallback for any caller that renders the column without the per-post payload.
		this.showTruSeo = undefined !== this.post?.showScore
			? !!this.post.showScore
			: shouldShowTruSeoScore()
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

	&__loading {
		position: relative;
		width: 35px;
		height: 35px;
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

	&__field {
		display: flex;
		align-items: flex-start;
		gap: 7px;

		// Tighter than the 10px the generic .edit-row uses: these three rows read as
		// one block, so they need less air between them than unrelated rows do.
		margin-bottom: 6px;

		.aioseo-details-column__dot-tooltip,
		.aioseo-details-column__len-tooltip {
			flex-shrink: 0;
			margin-left: 0;
		}

		.aioseo-details-column__dot-tooltip {
			line-height: 0;
		}

		// The tooltip wrapper is the flex child that holds the value, so it has to be
		// allowed to shrink below its content width for the clamp to kick in.
		.aioseo-details-column__tooltip {
			flex: 1 1 auto;
			min-width: 0;
		}

		.aioseo-loading-spinner {
			position: relative;
			width: 18px;
			height: 18px;
		}

		.aioseo-pencil {
			flex-shrink: 0;
			margin-top: 1px;
			cursor: pointer;
			color: $black;
			width: 16px;
			height: 16px;
			opacity: 0.38;
			transition: opacity 0.15s ease;
		}

		&:hover .aioseo-pencil,
		.aioseo-pencil:hover,
		.aioseo-pencil:focus {
			opacity: 1;
		}

		@media (prefers-reduced-motion: reduce) {
			.aioseo-pencil {
				transition: none;
			}
		}
	}

	&__robots-tooltip {
		margin-left: 0;
	}

	// A plain label rather than a chip: the row already carries a bordered score and an
	// icon, so a third boxed element made it read as an alert.
	&__robots {
		color: $red;
		font-size: 12px;
		font-weight: 600;
		line-height: 1.3;
		white-space: nowrap;
		cursor: default;
	}

	// Media shows four fields where posts show two, so they carry a micro-label instead
	// of relying on the T/D badges to tell them apart. The label sits above the value
	// rather than beside it: this column is only ~235px wide here, so a label column
	// left the value about 12 characters of room.
	&__field--labelled {
		display: grid;
		grid-template-columns: 1fr auto;
		gap: 0 8px;
		margin-bottom: 8px;

		.aioseo-details-column__flabel {
			grid-area: 1 / 1;
			padding-top: 0;
		}

		.aioseo-details-column__tooltip {
			grid-area: 2 / 1;
		}

		.aioseo-pencil {
			grid-area: 1 / 2 / span 2 / auto;
			align-self: start;
			margin-top: 2px;
		}
	}

	&__error {
		font-size: 12px;
		color: $red;
	}

	&__flabel {
		padding-top: 3px;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: 0.07em;
		text-transform: uppercase;
		color: $placeholder-color;
		white-space: nowrap;
	}

	&__dot {
		flex-shrink: 0;
		width: 7px;
		height: 7px;
		margin-top: 6px;
		border-radius: 50%;

		&--custom {
			background: $blue;
		}

		&--default {
			background: transparent;
			border: 1.5px solid $placeholder-color;
		}
	}

	&__value {
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
		font-size: 13px;
		line-height: 1.45;
		overflow-wrap: anywhere;

		&--single {
			-webkit-line-clamp: 1;
		}

		&--missing {
			color: $red;
			font-style: italic;
		}
	}

	&__len {
		flex-shrink: 0;
		display: inline-flex;
		align-items: baseline;
		gap: 4px;
		margin-top: 1px;
		padding: 2px 6px;
		border-radius: 3px;
		font-size: 11.5px;
		line-height: 1.25;
		font-variant-numeric: tabular-nums;
		white-space: nowrap;
		cursor: default;

		&--ok {
			background: rgba($green, 0.12);
			color: $green3;
		}

		&--warn {
			background: rgba($orange, 0.14);
			color: $orange3;
		}

		&--bad {
			background: rgba($red, 0.1);
			color: $red2;
		}
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
				opacity: 0.38;
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
			margin-right: 8px;
			margin-bottom: 2px;

			&:last-child {
				margin-right: 0;
			}

			// Stacked full-width at this breakpoint, so the horizontal gap becomes a
			// vertical one instead.
			@media screen and (max-width: 1366px) {
				width: 100%;
				margin-right: 0;
				margin-bottom: 6px;
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

// Quick Edit / Bulk Edit robots fieldset. Plain admin markup rather than a Vue
// component, so it is styled here alongside the column's other wp-admin overrides.
//
// NOTE: #wpbody-content is required, not decorative. Core sets
// `#wpbody-content .inline-edit-row fieldset { margin: 0; padding: 0 12px 0 0 }`,
// which outranks a class-only selector and silently drops our margin and padding.
#wpbody-content .inline-edit-row .aioseo-inline-edit-robots {
	float: none;
	clear: both;
	width: 100%;
	margin: 12px 0 0;
	padding: 16px 0;
	border-top: 1px solid $gray;

	&__title {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
	}

	&__controls {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px 24px;
	}

	&__options {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 8px 20px;

		&.is-disabled {
			opacity: 0.5;
		}
	}

	label {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		float: none;
		margin: 0;
		line-height: 1.4;
	}

	input[type='checkbox'] {
		margin: 0;
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