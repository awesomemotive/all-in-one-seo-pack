import { defineStore } from 'pinia'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useAiStore,
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

import { useAiContent } from '@/vue/composables/AiContent'

export const useAiImageGeneratorStore = defineStore('AiImageGeneratorStore', {
	state : () => ({
		currentScreen : 'generate',
		extend        : {
			imageBlockToolbar     : true,
			imageBlockPlaceholder : true,
			featuredImageButton   : true
		},
		form : {
			prompt : {
				value     : '',
				id        : 'aioseo-ai-image-generator-input-prompt',
				maxlength : 1000,
				minlength : 5
			},
			quality : {
				value : null,
				id    : 'aioseo-ai-image-generator-input-quality'
			},
			aspectRatio : {
				value : null,
				id    : 'aioseo-ai-image-generator-input-aspect-ratio'
			},
			model : {
				value : null,
				id    : 'aioseo-ai-image-generator-input-model'
			},
			isGenerating          : false,
			generationElapsedTime : null
		},
		errors : {
			api : null
		},
		images : {
			isFetching : false,
			selected   : [],
			all        : {
				rows : []
			},
			count : null // The null as an initial value is used to indicate the count was not yet fetched.
		},
		modalOpenDeleteImages : false,
		initiator             : {
			slug    : null,
			wpMedia : null
		}
	}),
	getters : {
		formPrompt : state => {
			return (state.form.prompt.value || '').trim()
		},
		error : state => {
			if (state.errors.api) {
				return {
					message : state.errors.api,
					type    : 'red'
				}
			}

			return null
		},
		selectedImage : state => {
			return state.images.selected?.length ? state.images.selected[0] : null
		},
		generationPrice : state => {
			const optionsStore        = useOptionsStore()
			const costPerFeature      = optionsStore.internalOptions?.internal?.ai?.costPerFeature || {}
			const imageGeneratorCosts = costPerFeature.imageGenerator || {}
			const quality             = state.form.quality.value?.value

			// Backend per-model cost wins — keyed by the vendor slug we send on the wire.
			// Two shapes are accepted: a flat number (flat-cost models like Nano Banana) or
			// an object of `{ quality: credits }` for models with quality tiers.
			const modelSlug = state.form.model.value?.value
			const modelCost = modelSlug ? imageGeneratorCosts[modelSlug] : undefined
			if ('number' === typeof modelCost || 'string' === typeof modelCost) {
				return Number(modelCost)
			}
			if (modelCost && 'object' === typeof modelCost && quality && modelCost[quality] !== undefined) {
				return Number(modelCost[quality])
			}

			// Plugin's flat default for models that don't accept a quality.
			const flatCost = state.form.model.value?.flatCost
			if (flatCost) {
				return Number(flatCost)
			}

			if (quality && imageGeneratorCosts[quality]) {
				// Backend can serialize costs as strings — coerce so callers get a consistent Number for .toLocaleString().
				return Number(imageGeneratorCosts[quality])
			}

			// Fallback to hardcoded values.
			switch (quality) {
				case 'low':
					return 250
				case 'medium':
					return 1000
			}

			return 0
		},
		// Single gate for both the generate and regenerate submit buttons: enough credits,
		// a long-enough prompt, and — when editing a selected image — a prompt that differs
		// from that image's (no point re-running an identical edit).
		canGenerate (state) {
			const optionsStore = useOptionsStore()

			const hasEnoughCredits  = optionsStore.internalOptions.internal.ai.credits.remaining >= this.generationPrice
			const isPromptValid     = this.formPrompt.length >= state.form.prompt.minlength
			const isPromptUnchanged = !!this.selectedImage && this.selectedImage.prompt === this.formPrompt

			return hasEnoughCredits && isPromptValid && !isPromptUnchanged
		},
		isGenerationTakingTooLong : state => {
			if (!state.form.isGenerating || !state.form.generationElapsedTime) {
				return false
			}

			return 20 < state.form.generationElapsedTime // 20 seconds is the threshold.
		}
	},
	actions : {
		selectImage (image) {
			const {
				getAspectRatioFromDimensions,
				imageQualityOptions,
				imageAspectRatioOptions,
				imageModelOptions
			} = useAiContent()

			const optionsStore = useOptionsStore()

			this.images.selected = [ image ]

			this.form.prompt.value  = image?.prompt || ''
			this.form.quality.value = imageQualityOptions.find(o => o.value === image?.quality) || imageQualityOptions.find(o => o.value === optionsStore.options.aiContent.imageQuality) || imageQualityOptions[0]
			this.form.model.value   = imageModelOptions.find(o => o.value === image?.model) || imageModelOptions.find(o => o.value === optionsStore.options.aiContent.imageModel) || imageModelOptions[0]

			const aspectRatioValue = image?.aspectRatio || getAspectRatioFromDimensions(image?.width, image?.height)

			this.form.aspectRatio.value = imageAspectRatioOptions.find(o => o.value === aspectRatioValue) || imageAspectRatioOptions.find(o => o.value === optionsStore.options.aiContent.imageAspectRatio)
		},
		generateImage () {
			const aiStore = useAiStore()

			this.form.isGenerating          = true
			this.form.generationElapsedTime = 0

			const interval = setInterval(() => {
				this.form.generationElapsedTime += 1 // Add 1 second to the elapsed time.
			}, 1000)

			const postEditorStore = usePostEditorStore()

			return http.post(links.restUrl('ai/image-generator'))
				.send({
					prompt          : this.formPrompt,
					quality         : this.form.quality.value.value,
					aspectRatio     : this.form.aspectRatio.value.value,
					model           : this.form.model.value?.value,
					selectedImageId : this.selectedImage?.id || null,
					postId          : postEditorStore.currentPost.id || null
				})
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					return response.body
				})
				.catch(error => {
					throw error
				})
				.finally(() => {
					this.form.isGenerating          = false
					this.form.generationElapsedTime = null

					clearInterval(interval)

					aiStore.getCredits(true)
				})
		},
		fetchImages () {
			this.images.isFetching = true

			const postEditorStore = usePostEditorStore()

			return http.get(links.restUrl('ai/image-generator'))
				.query({
					postId : postEditorStore.currentPost?.id || null
				})
				.then(response => {
					this.images.all.rows = response.body.all.rows
					this.images.count    = response.body.count

					return response
				})
				.catch(error => {
					throw error
				})
				.finally(() => {
					this.images.isFetching = false
				})
		},
		deleteImages (ids) {
			return http.delete(links.restUrl('ai/image-generator'))
				.send({ ids })
				.then(response => response)
				.catch(error => {
					throw error
				})
		},
		switchScreen (screen) {
			this.images.selected   = []
			this.form.prompt.value = ''
			this.errors.api        = null

			this.currentScreen = screen
		},
		toggleModal (args) {
			if ('modalOpenDeleteImages' === args.modal && args.open) {
				this.images.selected = args?.images ?? []
			}

			this[args.modal] = args.open
		},
		resetInitiator () {
			this.initiator = {
				slug    : null,
				wpMedia : null
			}
		},
		setFormDefaults () {
			const optionsStore = useOptionsStore()

			const {
				imageQualityOptions,
				imageAspectRatioOptions,
				imageModelOptions
			} = useAiContent()

			this.form.quality.value     = imageQualityOptions.find(o => o.value === optionsStore.options.aiContent.imageQuality) || imageQualityOptions[0]
			this.form.aspectRatio.value = imageAspectRatioOptions.find(o => o.value === optionsStore.options.aiContent.imageAspectRatio)
			this.form.model.value       = imageModelOptions.find(o => o.value === optionsStore.options.aiContent.imageModel) || imageModelOptions[0]
		}
	}
})