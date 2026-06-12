<template>
	<div class="aioseo-ai-bulk-generate">
		<core-main
			:page-name="strings.pageName"
			:show-tabs="false"
			:show-save-button="false"
		>
			<div>
				<div v-html="backLink"></div>

				<div
					v-if="!postIds.length"
					class="aioseo-ai-bulk-generate__empty"
				>
					<p>{{ strings.noPostsSelected }}</p>
				</div>

				<div
					v-else
					class="aioseo-ai-bulk-generate__container"
				>
					<div class="aioseo-ai-bulk-generate__header">
						<span
							class="aioseo-ai-bulk-generate__description"
							v-html="strings.description"
						/>

						<credit-counter
							tooltip-placement="left"
							show-purchase-only-when-low
						/>
					</div>

					<div
						v-if="bulkItems.length"
						class="aioseo-ai-bulk-generate__table"
					>
						<table class="wp-list-table widefat fixed striped">
							<thead>
							<tr>
								<th class="aioseo-ai-bulk-generate__col-status"></th>

								<th class="aioseo-ai-bulk-generate__col-title">
									{{ strings.postColumn }}
								</th>

								<th class="aioseo-ai-bulk-generate__col-generated">{{ strings.resultColumn }}</th>
							</tr>
							</thead>

							<tbody>
							<tr
								v-for="item in bulkItems"
								:key="item.id"
								:class="getRowClass(item.status)"
							>
								<td class="aioseo-ai-bulk-generate__col-status">
									<div
										v-if="'pending' === item.status"
										class="aioseo-ai-bulk-generate__status-icon"
									/>

									<core-loader
										v-else-if="'processing' === item.status"
										dark
										class="aioseo-ai-bulk-generate__status-icon"
									/>

									<svg-circle-check
										v-else-if="'success' === item.status"
										class="aioseo-ai-bulk-generate__status-icon"
									/>

									<svg-circle-close
										v-else-if="'error' === item.status"
										class="aioseo-ai-bulk-generate__status-icon"
									/>
								</td>

								<td class="aioseo-ai-bulk-generate__col-title">
									<div class="aioseo-ai-bulk-generate__item-preview">
										<div
											v-if="isAlt"
											class="aioseo-ai-bulk-generate__thumbnail"
										>
											<img
												v-if="item.object?.thumbnailUrl"
												:src="item.object.thumbnailUrl"
												:alt="item.object?.title"
											/>
										</div>

										<div>
											<span>{{ item.object?.title ?? `#${item.id}` }}</span>

											<div class="row-actions">
												<span>
													<a
														:href="getEditLink(item.id)"
														target="_blank"
													>{{ editPost(postTypeSingularLabel) }}</a> |
												</span>

												<span>
													<a
														:href="getViewLink(item.id)"
														target="_blank"
													>{{ viewPost(postTypeSingularLabel) }}</a>
												</span>
											</div>
										</div>
									</div>
								</td>

								<td class="aioseo-ai-bulk-generate__col-generated">
									<div
										v-if="'success' === item.status"
										class="aioseo-ai-bulk-generate__result"
									>
										<div class="aioseo-ai-bulk-generate__selected-value">
											<span>{{ item.generatedValue }}</span>
										</div>

										<div
											v-if="1 < item.allSuggestions.length"
											class="aioseo-ai-bulk-generate__show-more"
										>
											<a
												href="#"
												@click.prevent="item.showSuggestions = !item.showSuggestions"
											>
												<span>{{ moreSuggestionsLabel(item) }}</span>

												<svg-caret :class="{ rotated: item.showSuggestions }" />
											</a>
										</div>

										<transition-slide
											tag="div"
											:active="item.showSuggestions"
											class="aioseo-ai-bulk-generate__suggestions-list"
										>
											<div
												v-for="(suggestion, suggestionIndex) in item.allSuggestions"
												:key="suggestionIndex"
												class="aioseo-ai-bulk-generate__suggestion"
											>
												<input
													type="radio"
													:checked="suggestionIndex === item.selectedIndex"
													@click.stop="selectSuggestion(item, suggestionIndex)"
												/>

												<span>{{ suggestion }}</span>
											</div>
										</transition-slide>
									</div>

									<div
										v-if="item.error"
										class="aioseo-ai-bulk-generate__inline-error"
									>
										{{ item.error }}
									</div>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</core-main>
	</div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'

import {
	useAiStore,
	useRootStore
} from '@/vue/stores'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import { useAiContent } from '@/vue/composables/AiContent'
import { usePostTypes } from '@/vue/composables/PostTypes'

import { __, _n, sprintf } from '@/vue/plugins/translations'

import CoreLoader from '@/vue/components/common/core/Loader'
import CoreMain from '@/vue/components/common/core/main/Index'
import CreditCounter from '@/vue/components/common/ai/CreditCounter'
import SvgCaret from '@/vue/components/common/svg/Caret'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import TransitionSlide from '@/vue/components/common/transition/Slide'

const rootStore = useRootStore()
const aiStore   = useAiStore()

const { getFeatureCost, hasEnoughCredits } = useAiContent()

const { editPost, viewPost } = usePostTypes()

const td = import.meta.env.VITE_TEXTDOMAIN

const bulkItems      = ref([])
const postIds        = computed(() => rootStore.aioseo?.aiBulkGenerate?.ids || [])
const postsData      = computed(() => rootStore.aioseo?.aiBulkGenerate?.posts || {})
const generationType = computed(() => rootStore.aioseo?.aiBulkGenerate?.type || 'title')
const isAlt          = computed(() => 'alt' === generationType.value)
const isComplete     = computed(() => 0 < bulkItems.value.length && bulkItems.value.every(item => 'success' === item.status || 'error' === item.status))

const typeConfigs = {
	title : {
		featureCostName  : 'titles',
		generate         : (postId) => aiStore.generateMetaTitles({ postId, postContent: '' }),
		extractAllValues : (body) => body.titles,
		savePayload      : (item) => ({ title: item.generatedValue }),
		strings          : {
			typeName        : __('SEO titles', td),
			resultColumn    : __('SEO Title', td),
			errorGeneration : __('Failed to generate title.', td),
			errorSaving     : __('Failed to save title.', td)
		}
	},
	description : {
		featureCostName  : 'descriptions',
		generate         : (postId) => aiStore.generateMetaDescriptions({ postId, postContent: '' }),
		extractAllValues : (body) => body.descriptions,
		savePayload      : (item) => ({ description: item.generatedValue }),
		strings          : {
			typeName        : __('meta descriptions', td),
			resultColumn    : __('Meta Description', td),
			errorGeneration : __('Failed to generate description.', td),
			errorSaving     : __('Failed to save description.', td)
		}
	},
	alt : {
		featureCostName  : 'imageAltText',
		generate         : (postId) => aiStore.generateImageAlt({ attachmentId: postId }),
		extractAllValues : (body) => body.altTexts,
		savePayload      : (item) => ({
			isMedia     : true,
			imageAltTag : item.generatedValue,
			imageTitle  : item.object?.title || ''
		}),
		strings : {
			typeName        : __('alt texts', td),
			resultColumn    : __('Alt Text', td),
			errorGeneration : __('Failed to generate ALT tag.', td),
			errorSaving     : __('Failed to save ALT tag.', td)
		}
	}
}

const activeTypeConfig = computed(() => typeConfigs[generationType.value] || typeConfigs.title)

const postType = computed(() => rootStore.aioseo?.aiBulkGenerate?.postType || '')

const postTypeObject = computed(() => {
	const postTypes = rootStore.aioseo?.postData?.postTypes || []

	return postTypes.find(pt => pt.name === postType.value) || null
})

const postTypeSingularLabel = computed(() => {
	return postTypeObject.value?.singular || __('Post', td)
})

const postTypePluralLabel = computed(() => {
	return postTypeObject.value?.label || __('Posts', td)
})

const postsListUrl = computed(() => {
	const baseUrl = rootStore.aioseo.urls.editScreen

	// For attachments, link back to the Media Library.
	if ('attachment' === postType.value) {
		return baseUrl.replace('edit.php', 'upload.php')
	}

	// For "post" type, WordPress uses edit.php without query string.
	// For other post types, we need to append post_type parameter.
	if (!postType.value || 'post' === postType.value) {
		return baseUrl
	}

	return `${baseUrl}?post_type=${postType.value}`
})

const backLink = computed(() => links.getPlainLink(strings.value.goToPostsList, postsListUrl.value, 'back', false))

const getEditLink = (postId) => {
	return rootStore.aioseo.urls.editScreen.replace('edit.php', `post.php?post=${postId}&action=edit`)
}

const getViewLink = (postId) => {
	return `${rootStore.aioseo.urls.home}?p=${postId}`
}

const strings = computed(() => {
	const config = activeTypeConfig.value

	return {
		pageName      : __('Bulk Generator', td),
		goToPostsList : isAlt.value
			? __('Back to Media Library', td)
			: sprintf(
				// Translators: 1 - The post type plural label (e.g. "Posts", "Pages").
				__('Back to %1$s', td),
				postTypePluralLabel.value
			),
		noPostsSelected : isAlt.value
			? __('No images were selected for bulk generation.', td)
			: __('No posts were selected for bulk generation.', td),
		description : isComplete.value
			? sprintf(
				// Translators: 1 - The type being generated (e.g. "SEO titles", "meta descriptions", "alt texts").
				__('All done! Your %1$s have been generated.', td),
				config.strings.typeName
			)
			: sprintf(
				// Translators: 1 - The type being generated (e.g. "SEO titles", "meta descriptions", "alt texts").
				__('Your %1$s are now being generated. Please wait a moment.', td),
				config.strings.typeName
			),
		postColumn : isAlt.value
			? __('Image Title', td)
			: sprintf(
				// Translators: 1 - The singular post type label (e.g. "Post", "Page").
				__('%1$s Title', td),
				postTypeSingularLabel.value
			),
		resultColumn    : config.strings.resultColumn,
		noCredits       : __('Insufficient AI credits.', td),
		errorGeneration : config.strings.errorGeneration,
		errorSaving     : config.strings.errorSaving,
		errorByCode     : {
			no_content        : __('This post has no content. Add content and try again.', td),
			content_too_short : __('This post is too short. Add more content and try again.', td),
			post_not_found    : __('This post could not be found. It may have been deleted.', td),
			unauthorized      : __('You don\'t have permission to edit this post.', td),
			not_an_image      : __('The attachment is not an image.', td)
		}
	}
})

const getRowClass = (status) => {
	return `aioseo-ai-bulk-generate__row--${status}`
}

const saveGeneratedValue = async (item) => {
	item.error = null

	const payload = {
		postId : item.id,
		...activeTypeConfig.value.savePayload(item)
	}

	try {
		const request = () => http.post(links.restUrl('posts-list/update-details-column')).send(payload)

		// Retry once if the request fails due to a temporary error (network issues, etc.).
		await request().catch(() => request())

		return true
	} catch (error) {
		console.error('Failed to save:', error)

		item.error = strings.value.errorSaving

		return false
	}
}

const generateAiContent = async (postId) => {
	const config   = activeTypeConfig.value
	const response = await config.generate(postId)

	return config.extractAllValues(response.body)
}

const processAllItems = async () => {
	const featureCost = getFeatureCost(activeTypeConfig.value.featureCostName)

	for (const item of bulkItems.value) {
		if (!hasEnoughCredits(featureCost)) {
			item.status = 'error'
			item.error  = strings.value.noCredits

			continue
		}

		item.status = 'processing'

		try {
			const allValues     = await generateAiContent(item.id)
			item.allSuggestions = allValues
			item.generatedValue = allValues[0]
		} catch (error) {
			console.error('Failed to generate:', error)

			const errorCode = error.response?.body?.code

			item.status = 'error'
			item.error  = strings.value.errorByCode[errorCode] || strings.value.errorGeneration

			continue
		}

		const saved = await saveGeneratedValue(item)

		item.status = saved ? 'success' : 'error'
	}
}

const selectSuggestion = async (item, index) => {
	if (item.selectedIndex === index) {
		return
	}

	const previousIndex = item.selectedIndex

	item.selectedIndex  = index
	item.generatedValue = item.allSuggestions[index]

	const saved = await saveGeneratedValue(item)
	if (!saved) {
		item.selectedIndex  = previousIndex
		item.generatedValue = item.allSuggestions[previousIndex]
	}
}

const moreSuggestionsLabel = (item) => {
	const count = item.allSuggestions.length - 1

	return sprintf(
		// Translators: 1 - The number of alternative suggestions.
		_n('%1$d more suggestion', '%1$d more suggestions', count, td),
		count
	)
}

onMounted(() => {
	if (!postIds.value.length) {
		return
	}

	bulkItems.value = postIds.value.map(id => ({
		id,
		status          : 'pending',
		generatedValue  : null,
		allSuggestions  : [],
		selectedIndex   : 0,
		showSuggestions : false,
		error           : null,
		object          : postsData.value[id] || null
	}))

	processAllItems()
})
</script>

<style lang="scss" scoped>
.aioseo-ai-bulk-generate {
	color: $black;

	&__container {
		margin-top: 25px;
		position: relative;
	}

	&__empty {
		margin-top: 25px;
		font-size: 14px;

		p {
			margin: 10px 0;
		}
	}

	&__header {
		display: grid;
		grid-template-columns: 1fr auto;
		align-items: center;
		gap: 10px;
		font-size: 14px;
		margin-bottom: 10px;
	}

	&__description {
		line-height: 1.5;
	}

	&__col-status {
		text-align: center;
		width: 40px;
	}

	&__col-title {
		width: 40%;
	}

	&__item-preview {
		display: flex;
		gap: 10px;
	}

	&__thumbnail {
		width: 40px;
		height: 40px;
		flex-shrink: 0;
		border-radius: 4px;
		background: $border;

		img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			border-radius: inherit;
		}
	}

	&__status-icon {
		width: 20px;
		height: 20px;
		position: relative;
		display: inline-block;
	}

	&__row--pending &__status-icon {
		background: $placeholder-color;
		border-radius: 50%;
		width: 10px;
		height: 10px;
	}

	&__row--success &__status-icon {
		color: $green;
	}

	&__row--error &__status-icon,
	&__row--error &__col-generated {
		color: $red;
	}

	&__inline-error {
		color: $red;
		font-size: 13px;
	}

	&__show-more {
		padding: 8px 0 4px;

		a {
			display: inline-flex;
			align-items: center;
		}

		svg.aioseo-caret {
			width: 18px;
			height: 18px;
			transform: rotate(-90deg);
			transition: transform 0.3s;

			&.rotated {
				transform: rotate(-180deg);
			}
		}
	}

	&__suggestion {
		align-items: center;
		display: grid;
		gap: 8px;
		grid-template-columns: auto 1fr;
		padding: 10px 0;

		input[type=radio] {
			margin: 0;
		}

		& + & {
			border-top: 1px solid $border;
		}
	}
}
</style>