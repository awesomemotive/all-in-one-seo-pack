import { usePostEditorStore } from '@/vue/stores'

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
const getContent = () => {
	const postEditorStore = usePostEditorStore()
	return postEditorStore.currentPost?.processedContent || ''
}

/**
 * Gets the post title.
 *
 * @returns {string} The post title.
 */
const getTitle = () => {
	const { FusionApp } = window

	return FusionApp?.getPost('post_title') || document.getElementById('#title')?.value || ''
}

/**
 * Gets the post slug.
 *
 * @returns {string} The post slug.
 */
const getSlug = () => {
	const { FusionApp } = window

	return FusionApp?.getPost('post_name') || document.querySelector('#post_name')?.value || ''
}

/**
 * Gets the post permalink.
 *
 * @returns {string} The post permalink.
 */
const getPermalink = () => {
	const { FusionApp } = window

	return FusionApp?.getPost('post_permalink').replace('?fb-edit=1', '') || document.querySelector('#sample-permalink a')?.href || ''
}

/**
 * Gets the featured image.
 *
 * @returns {string} The featured image.
 */
const getFeaturedImage = async () => {
	const { wp, FusionApp } = window

	if (!wp || !FusionApp || !FusionApp.getDynamicPost('post_meta')._thumbnail_id) {
		return ''
	}

	const attachment  = wp.media.attachment(FusionApp.getDynamicPost('post_meta')._thumbnail_id)
	let featuredImage = ''

	await attachment.fetch().then(() => {
		featuredImage = attachment.get('url')
	})

	return featuredImage
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
		slug          : getSlug(),
		permalink     : getPermalink(),
		featuredImage : getFeaturedImage()
	}
}