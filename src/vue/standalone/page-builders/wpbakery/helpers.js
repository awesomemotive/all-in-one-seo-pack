import { usePostEditorStore } from '@/vue/stores'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'

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

		return vc.builder.getTitle() || document.querySelector('#vc_page-title-field')?.value
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

		return pathParts[pathParts.length - 1] || document.querySelector('#vc_post_name')?.value
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
	const slug = getSlug(vcMode) || cleanForSlug(getTitle(vcMode))

	return {
		content       : getContent(),
		title         : getTitle(vcMode),
		excerpt       : '',
		slug          : slug,
		permalink     : getPermalink(vcMode),
		featuredImage : ''
	}
}

export const getSaveButtons = (onlyVisible = false) => {
	const buttons = Array.from(document.querySelectorAll('#vc_button-update, #vc_button-save-draft, #vc_button-save-as-pending'))

	if (onlyVisible) {
		return buttons.filter($button => $button.offsetParent && $button.offsetWidth && $button.offsetHeight)
	}

	return buttons
}