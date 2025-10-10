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
	state : () => {
		const optionsStore = useOptionsStore()

		const {
			imageQualityOptions,
			imageStyleOptions,
			imageAspectRatioOptions
		} = useAiContent()

		const defaultQuality     = imageQualityOptions.find(o => o.value === optionsStore.options.aiContent.imageQuality)
		const defaultStyle       = imageStyleOptions.find(o => o.value === optionsStore.options.aiContent.imageStyle)
		const defaultAspectRatio = imageAspectRatioOptions.find(o => o.value === optionsStore.options.aiContent.imageAspectRatio)

		return {
			currentScreen : 'generate',
			form          : {
				prompt : {
					value     : '',
					id        : 'aioseo-ai-image-generator-input-prompt',
					maxlength : 1000,
					minlength : 5
				},
				quality : {
					defaultValue : defaultQuality,
					value        : defaultQuality,
					id           : 'aioseo-ai-image-generator-input-quality'
				},
				style : {
					defaultValue : defaultStyle,
					value        : defaultStyle,
					id           : 'aioseo-ai-image-generator-input-style'
				},
				aspectRatio : {
					defaultValue : defaultAspectRatio,
					value        : defaultAspectRatio,
					id           : 'aioseo-ai-image-generator-input-aspect-ratio'
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
		}
	},
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
			switch (state.form.quality.value.value) {
				case 'low':
					return 250
				case 'medium':
					return 1000
				case 'high':
					return 5000
			}

			return 0
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
				imageQualityOptions,
				imageStyleOptions,
				imageAspectRatioOptions
			} = useAiContent()

			this.images.selected        = [ image ]
			this.form.prompt.value      = image?.prompt || ''
			this.form.quality.value     = imageQualityOptions.find(o => o.value === image?.quality) || this.form.quality.defaultValue
			this.form.style.value       = imageStyleOptions.find(o => o.value === image?.style) || this.form.style.defaultValue
			this.form.aspectRatio.value = imageAspectRatioOptions.find(o => o.value === image?.aspectRatio) || this.form.aspectRatio.defaultValue
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
					style           : this.form.style.value.label,
					aspectRatio     : this.form.aspectRatio.value.value,
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
		}
	}
})