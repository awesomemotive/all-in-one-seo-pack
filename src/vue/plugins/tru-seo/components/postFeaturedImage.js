import {
	usePostEditorStore
} from '@/vue/stores'

import {
	isBlockEditor,
	isClassicEditor,
	isClassicNoEditor,
	isElementorEditor,
	isDiviEditor,
	isAvadaEditor,
	isThriveArchitectEditor,
	isBricksEditor,
	isOxygenEditor
} from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/page-builders/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/page-builders/divi/helpers'
import { getEditorData as getAvadaData } from '@/vue/standalone/page-builders/avada/helpers'
import { getEditorData as getThriveArchitectData } from '@/vue/standalone/page-builders/thrive-architect/helpers'
import { getEditorData as getBricksData } from '@/vue/standalone/page-builders/bricks/helpers'
import { getEditorData as getOxygenData } from '@/vue/standalone/page-builders/oxygen/helpers'

/**
 * Retrieves the featured image from the active page builder editor.
 *
 * @returns {string} The featured image from the active page builder editor.
 */
const getEditorFeaturedImage = () => {
	const editorMap = [
		{ isEditor: isElementorEditor, getData: getElementorData },
		{ isEditor: isDiviEditor, getData: getDiviData },
		{ isEditor: isAvadaEditor, getData: getAvadaData },
		{ isEditor: isThriveArchitectEditor, getData: getThriveArchitectData },
		{ isEditor: isBricksEditor, getData: getBricksData },
		{ isEditor: isOxygenEditor, getData: getOxygenData }
	]

	for (const editor of editorMap) {
		if (editor.isEditor()) {
			return editor.getData()?.featuredImage ?? ''
		}
	}

	return ''
}

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

	return getEditorFeaturedImage()
}