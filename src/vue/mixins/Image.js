import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useSettingsStore
} from '@/vue/stores'

import { getPostEditedAuthor } from '../plugins/tru-seo/components/postAuthor'
import { getPostEditedFeaturedImage } from '../plugins/tru-seo/components/postFeaturedImage'
import { getPostEditedContent } from '../plugins/tru-seo/components/postContent'
import { customFieldValue } from '../plugins/tru-seo/components/customFields'

export const ImageSourceOptions = {
	emits : [ 'updateSocialImagePreview' ],
	data () {
		return {
			excludedTermOptions        : [ 'featured', 'attach', 'content', 'author', 'auto' ],
			excludedAttachmentOptions  : [ 'featured', 'content', 'author' ],
			excludedPageBuilderOptions : [ 'auto' ]
		}
	},
	computed : {
		imageSourceOptions () {
			return [
				{ label: this.$t.__('Default Image (Set Below)', this.$td), value: 'default' },
				{ label: this.$t.__('Featured Image', this.$td), value: 'featured' },
				{ label: this.$t.__('Attached Image', this.$td), value: 'attach' },
				{ label: this.$t.__('First Image in Content', this.$td), value: 'content' },
				{ label: this.$t.__('Image from Custom Field', this.$td), value: 'custom' },
				{ label: this.$t.__('Post Author Image', this.$td), value: 'author' },
				{ label: this.$t.__('First Available Image', this.$td), value: 'auto' }
			]
		},
		imageSourceOptionsFiltered () {
			const postEditorStore = usePostEditorStore()
			const options = this.imageSourceOptions
				.map(option => {
					if ('default' === option.value) {
						option.label = this.$t.__('Default Image Source (Set in Social Networks)', this.$td)
					}
					return option
				}).concat({ label: this.$t.__('Custom Image', this.$td), value: 'custom_image' })

			if ('term' === postEditorStore.currentPost?.context) {
				return options.filter(option => !this.excludedTermOptions.includes(option.value))
			}
			if ('post' === postEditorStore.currentPost?.context && 'attachment' === postEditorStore.currentPost?.postType) {
				return options.filter(option => !this.excludedAttachmentOptions.includes(option.value))
			}

			const rootStore = useRootStore()
			if (rootStore.aioseo.integration) {
				if ('seedprod' === rootStore.aioseo.integration) {
					this.excludedPageBuilderOptions.push('featured', 'custom')
				}
				return options.filter(option => !this.excludedPageBuilderOptions.includes(option.value))
			}
			return options
		}
	},
	methods : {
		getTermImageSourceOptions () {
			return this.imageSourceOptions.filter(option => !this.excludedTermOptions.includes(option.value))
		},
		getImageSourceOption (option) {
			return this.imageSourceOptions.find(o => o.value === option)
		},
		getImageSourceOptionFiltered (option) {
			return this.imageSourceOptionsFiltered.find(o => o.value === option)
		}
	}
}

const getFirstImageInContent = () => {
	let image = null
	const images = /<img.*?src=['"](.*?)['"].*?>/i.exec(getPostEditedContent())
	if (images && images[1]) {
		image = images[1]
	}

	return image
}

const getFirstAvailableImage = async (currentPost, type, prefix) => {
	let image = customFieldValue(currentPost[`${prefix}image_custom_fields`])

	if (!image) {
		await getPostEditedFeaturedImage().then(url => {
			image = url
		})
	}

	if (!image) {
		const postEditorStore = usePostEditorStore()
		await postEditorStore.getFirstAttachedImage({ postId: currentPost.id })
			.then((url) => {
				image = url
			})
	}

	if (!image) {
		image = getFirstImageInContent()
	}

	if (!image) {
		const optionsStore = useOptionsStore()
		image = optionsStore.options.social[type].homePage.image
	}

	return image
}

const getUserImage = async () => {
	let image    = ''

	const userId          = getPostEditedAuthor()
	const postEditorStore = usePostEditorStore()
	await postEditorStore.getUserImage({ userId })
		.then((gravatar) => {
			image = gravatar
		})

	return image
}

export const ImagePreview = {
	data () {
		return {
			imageUrl : '',
			loading  : false
		}
	},
	async mounted () {
		await this.setImageUrl()
	},
	methods : {
		async setImageUrl (socialNetwork = '') {
			const optionsStore    = useOptionsStore()
			const postEditorStore = usePostEditorStore()
			const settingsStore   = useSettingsStore()
			const currentPost     = postEditorStore.currentPost
			const tab             = socialNetwork || settingsStore.metaBoxTabs?.social || 'facebook'
			const prefix          = 'facebook' === tab || ('twitter' === tab && currentPost.twitter_use_og) ? 'og_' : 'twitter_'

			let imageSource = currentPost[`${prefix}image_type`] || 'default'
			if ('default' === imageSource) {
				imageSource = optionsStore.options.social[tab].general.defaultImageSourcePosts
			}

			this.imageUrl = ''

			switch (imageSource) {
				case 'featured':
					this.loading = true
					await getPostEditedFeaturedImage()
						.then(url => {
							this.imageUrl = url
							this.loading  = false
						})
					break

				case 'attach':
					this.loading = true
					await postEditorStore.getFirstAttachedImage({ postId: currentPost.id })
						.then((url) => {
							this.imageUrl = url
							this.loading  = false
						})
					break

				case 'content':
					this.imageUrl = getFirstImageInContent()
					break

				case 'author':
					this.loading = true
					await getUserImage()
						.then((url) => {
							this.imageUrl = url
							this.loading  = false
						})
					break

				case 'auto':
					this.loading = true
					await getFirstAvailableImage(currentPost, tab, prefix)
						.then(url => {
							this.imageUrl = url
							this.loading  = false
						})
					break

				case 'custom':
					this.imageUrl = customFieldValue(currentPost[`${prefix}image_custom_fields`])
					break

				case 'custom_image':
					this.imageUrl = currentPost[`${prefix}image_custom_url`]
					break

				case 'default':
				default:
					this.imageUrl = optionsStore.options.social[tab].general.defaultImagePosts
					break
			}

			const rootStore = useRootStore()
			if (!this.imageUrl && rootStore.aioseo.urls.siteLogo) {
				this.imageUrl = rootStore.aioseo.urls.siteLogo
			}

			window.aioseoBus.$emit('updateSocialImagePreview', { social: tab, image: this.imageUrl })
		}
	}
}