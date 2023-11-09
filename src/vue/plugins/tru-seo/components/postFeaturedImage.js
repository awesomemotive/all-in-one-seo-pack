import {
	usePostEditorStore
} from '@/vue/stores'

import { isBlockEditor, isClassicEditor, isClassicNoEditor, isElementorEditor, isDiviEditor } from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/divi/helpers'

/**
 * Get the Classic Editor featured image.
 *
 * @returns {string} The featured image
 */
const getClassicEditorFeaturedImage = () => {
	const image = document.querySelector('#set-post-thumbnail img')
	if (image) {
		return image.getAttribute('src')
	}

	return ''
}

/**
 * Get the Block Editor featured image ID.
 *
 * @param   {boolean} edited If should retrieve the ID for edited or saved post.
 * @returns {int}            The media ID.
 */
const getBlockEditorFeaturedMediaId = async (edited = false) => {
	const coreEditor = window.wp.data.select('core/editor')
	const mediaId    = edited && coreEditor ? coreEditor?.getEditedPostAttribute('featured_media') : coreEditor?.getCurrentPost()?.featured_media

	// Try again if the Block Editor API is not fully loaded yet.
	if ('undefined' === typeof mediaId) {
		return new Promise(resolve => setTimeout(() => resolve(getBlockEditorFeaturedMediaId(edited)), 1000))
	}

	return mediaId
}

/**
 * Returns the featured image URL.
 *
 * @returns {string} The featured image
 */
export const getPostFeaturedImage = async () => {
	if (isClassicEditor() || isClassicNoEditor()) {
		return getClassicEditorFeaturedImage()
	}

	if (isBlockEditor()) {
		const mediaId = await getBlockEditorFeaturedMediaId()
			.then(id => id)

		if (isNaN(mediaId) || 0 === mediaId) {
			return ''
		}

		const postEditorStore = usePostEditorStore()
		return postEditorStore.getMediaData({ mediaId })
			.then(media => {
				return media.source_url
			})
	}

	if (isElementorEditor()) {
		return getElementorData().featuredImage
	}

	if (isDiviEditor()) {
		return getDiviData().featuredImage
	}

	return ''
}

/**
 * Returns the edited featured image URL.
 *
 * @returns {string} The featured image
 */
export const getPostEditedFeaturedImage = async () => {
	if (isClassicEditor() || isClassicNoEditor()) {
		return getClassicEditorFeaturedImage()
	}

	if (isBlockEditor()) {
		const mediaId = await getBlockEditorFeaturedMediaId(true)
			.then(id => id)

		if (isNaN(mediaId) || 0 === mediaId) {
			return ''
		}

		const postEditorStore = usePostEditorStore()
		return postEditorStore.getMediaData({ mediaId })
			.then(media => {
				return media.source_url
			})
	}

	if (isElementorEditor()) {
		return getElementorData().featuredImage
	}

	if (isDiviEditor()) {
		return getDiviData().featuredImage
	}

	return ''
}