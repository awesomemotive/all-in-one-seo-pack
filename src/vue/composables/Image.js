import { ref, computed } from 'vue'

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

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

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

export const useImage = () => {
	const excludedAttachmentOptions  = ref([ 'featured', 'content', 'author' ])
	const excludedPageBuilderOptions = ref([ 'auto' ])
	const excludedTermOptions        = ref([ 'featured', 'attach', 'content', 'author', 'auto' ])
	const imageUrl                   = ref('')
	const loading                    = ref(false)

	const imageSourceOptions = computed(() => {
		return [
			{ label: __('Default Image (Set Below)', td), value: 'default' },
			{ label: __('Featured Image', td), value: 'featured' },
			{ label: __('Attached Image', td), value: 'attach' },
			{ label: __('First Image in Content', td), value: 'content' },
			{ label: __('Image from Custom Field', td), value: 'custom' },
			{ label: __('Post Author Image', td), value: 'author' },
			{ label: __('First Available Image', td), value: 'auto' }
		]
	})

	const imageSourceOptionsFiltered = computed(() => {
		const postEditorStore = usePostEditorStore()
		const options = imageSourceOptions.value
			.map(option => {
				if ('default' === option.value) {
					option.label = __('Default Image Source (Set in Social Networks)', td)
				}
				return option
			}).concat({ label: __('Custom Image', td), value: 'custom_image' })

		if ('term' === postEditorStore.currentPost?.context) {
			return options.filter(option => !excludedTermOptions.value.includes(option.value))
		}
		if ('post' === postEditorStore.currentPost?.context && 'attachment' === postEditorStore.currentPost?.postType) {
			return options.filter(option => !excludedAttachmentOptions.value.includes(option.value))
		}

		const rootStore = useRootStore()
		const optionsStore = useOptionsStore()
		if (rootStore.aioseo.integration) {
			// Exclude the "featured" option for page builders that do not support it.
			if (
				'seedprod' === rootStore.aioseo.integration ||
				('wpbakery' === rootStore.aioseo.integration && 'admin_frontend_editor' === window.vc_mode)
			) {
				excludedPageBuilderOptions.value.push('featured')
			}

			// Remove the "content" option for SiteOrigin Page Builder if run shortcodes is disabled, since it depends on shortcodes.
			if ('siteorigin' === rootStore.aioseo.integration && !optionsStore.options.searchAppearance.advanced.runShortcodes) {
				excludedPageBuilderOptions.value.push('content')
			}

			return options.filter(option => !excludedPageBuilderOptions.value.includes(option.value))
		}
		return options
	})

	const getTermImageSourceOptions = () => {
		return imageSourceOptions.value.filter(option => !excludedTermOptions.value.includes(option.value))
	}

	const getImageSourceOption = (option) => {
		return imageSourceOptions.value.find(o => o.value === option)
	}

	const getImageSourceOptionFiltered = (option) => {
		return imageSourceOptionsFiltered.value.find(o => o.value === option)
	}

	const setImageUrl = async (socialNetwork = '') => {
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

		imageUrl.value = ''

		switch (imageSource) {
			case 'featured':
				loading.value = true
				await getPostEditedFeaturedImage()
					.then(url => {
						imageUrl.value = url
						loading.value  = false
					})
				break

			case 'attach':
				loading.value = true
				await postEditorStore.getFirstAttachedImage({ postId: currentPost.id })
					.then((url) => {
						imageUrl.value = url
						loading.value  = false
					})
				break

			case 'content':
				imageUrl.value = getFirstImageInContent()
				break

			case 'author':
				loading.value = true
				await getUserImage()
					.then((url) => {
						imageUrl.value = url
						loading.value  = false
					})
				break

			case 'auto':
				loading.value = true
				await getFirstAvailableImage(currentPost, tab, prefix)
					.then(url => {
						imageUrl.value = url
						loading.value  = false
					})
				break

			case 'custom':
				imageUrl.value = customFieldValue(currentPost[`${prefix}image_custom_fields`])
				break

			case 'custom_image':
				imageUrl.value = currentPost[`${prefix}image_custom_url`]
				break

			case 'default':
			default:
				imageUrl.value = optionsStore.options.social[tab].general.defaultImagePosts
				break
		}

		if (!imageUrl.value && optionsStore.options.social[tab].general.defaultImagePosts) {
			imageUrl.value = optionsStore.options.social[tab].general.defaultImagePosts
		}

		const rootStore = useRootStore()
		if (!imageUrl.value && rootStore.aioseo.urls.siteLogo) {
			imageUrl.value = rootStore.aioseo.urls.siteLogo
		}

		window.aioseoBus.$emit('updateSocialImagePreview', { social: tab, image: imageUrl.value })
	}

	return {
		getImageSourceOption,
		getImageSourceOptionFiltered,
		getTermImageSourceOptions,
		imageSourceOptions,
		imageSourceOptionsFiltered,
		imageUrl,
		loading,
		setImageUrl
	}
}