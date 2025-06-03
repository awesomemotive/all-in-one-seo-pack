import { cleanForSlug } from '@/vue/utils/cleanForSlug'
/* global TVE */

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
const getContent = () => {
	return TVE?.Editor_Page?.content_manager?.get_clean_content() || ''
}

/**
 * Gets the post title.
 *
 * @returns {string} The post title.
 */
const getTitle = () => {
	return TVE?.CONST?.post?.post_title || ''
}

/**
 * Gets the post slug.
 *
 * @returns {string} The post slug.
 */
const getSlug = () => {
	return TVE?.CONST?.post?.post_name || ''
}

/**
 * Gets the post permalink.
 *
 * @returns {string} The post permalink.
 */
const getPermalink = () => {
	return TVE?.CONST?.post_url || ''
}

/**
 * Gets the post featured image.
 *
 * @returns {string} The post featured image.
 */
const getFeaturedImage = () => {
	const featuredUrl = TVE?.CONST?.theme?.post_featured_image?.src || TVE?.CONST?.post_image?.featured || ''

	// If the featured image is the default one, return an empty string.
	if (featuredUrl.includes('inc/assets/images/featured_image.png')) {
		return ''
	}

	return featuredUrl
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	return {
		content       : getContent(),
		title         : getTitle(),
		excerpt       : '',
		slug          : getSlug() || cleanForSlug(getTitle()),
		permalink     : getPermalink(),
		featuredImage : getFeaturedImage()
	}
}