import { usePostEditorStore } from '@/vue/stores'

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
const getContent = () => {
	const postEditorStore = usePostEditorStore()
	return postEditorStore.currentPost.processedContent
}

/**
 * Gets the post title.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post title.
 */
const getTitle = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc } = window

		return vc.builder.getTitle()
	}

	return document.getElementById('#title')?.value || ''
}

/**
 * Gets the post slug.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post slug.
 */
const getSlug = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc_iframe_src : src } = window
		const pathParts = new URL(src).pathname.split('/').filter(n => n)

		return pathParts[pathParts.length - 1]
	}

	return document.querySelector('#post_name')?.value || ''
}

/**
 * Gets the post permalink.
 *
 * @param {string} mode The editor mode.
 * @returns {string} The post permalink.
 */
const getPermalink = (mode) => {
	if ('admin_frontend_editor' === mode) {
		const { vc_iframe_src : src } = window

		return src.replace(new URL(src).search, '')
	}

	return document.querySelector('#sample-permalink a')?.href || ''
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	const { vc_mode : vcMode } = window

	return {
		content       : getContent(),
		title         : getTitle(vcMode),
		excerpt       : '',
		slug          : getSlug(vcMode),
		permalink     : getPermalink(vcMode),
		featuredImage : ''
	}
}