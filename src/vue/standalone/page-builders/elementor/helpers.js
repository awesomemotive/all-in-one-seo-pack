import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { getText } from '@/vue/utils/html'
import { getImages, getVideos } from '@/vue/standalone/page-builders/helpers/index'
import { shouldShowMetaBox } from '@/vue/utils/metabox'

/**
 * Gets the post type of an Elementor document.
 * NOTE: Elementor only exposes it through the "all posts" admin URL, which omits the query arg for posts.
 *
 * @param   {Object} documentConfig The Elementor document config.
 * @returns {string}                The post type.
 */
const getPostType = (documentConfig) => {
	if (!documentConfig?.urls?.all_post_type) {
		return ''
	}

	return new URL(documentConfig.urls.all_post_type, window.location.origin).searchParams.get('post_type') || 'post'
}

/**
 * Checks whether the active Elementor document is the one AIOSEO is scoped to.
 * NOTE: Elementor swaps the active document for Site Settings and theme templates, and moves the initial one
 * when users navigate to another post from the document title dropdown.
 *
 * @returns {boolean} Whether AIOSEO should act on the active document.
 */
export const isAioseoDocument = () => {
	const documentConfig = window.elementor?.documents?.getCurrent()?.config
	if (Number(documentConfig?.id) !== Number(window.elementor?.config?.initial_document?.id)) {
		return false
	}

	// The post we booted for was already vetted server-side, so its post type needs no second look.
	if (Number(documentConfig?.id) === Number(window.aioseo?.currentPost?.id)) {
		return true
	}

	// An unreadable post type must not fall through to `shouldShowMetaBox()`, which then answers for another post.
	const postType = getPostType(documentConfig)

	return postType ? shouldShowMetaBox(postType) : false
}

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
export const getContent = () => {
	const editorDocument = window.elementor.documents.getCurrent()
	const content        = []

	if (!editorDocument.$element) {
		return ''
	}

	editorDocument.$element.find('.elementor-widget').each((_index, element) => {
		const html = element.innerHTML.trim()
			// Remove the wrapper div with class .elementor-widget-container. This is needed to support old Elementor versions where the Optimized DOM option is disabled or not supported.
			.replaceAll(/<div class="elementor-widget-container">(.*)<\/div>/g, '$1')
			.replaceAll(/<p.*>(<img.*>)<\/p>/g, '$1') // Remove the wrapper <p> if there's just an image inside it.

		// Skip if there's no text, images or videos, just HTML markup
		if ('' === getText(html) && 0 === getImages(html) && 0 === getVideos(html)) {
			return
		}

		content.push(html)
	})

	return content.join(' ')
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	const { settings, config } = window.elementor

	return {
		content       : getContent(),
		title         : settings.page.model.get('post_title'),
		excerpt       : settings.page.model.get('post_excerpt') || '',
		slug          : cleanForSlug(settings.page.model.get('post_title')),
		permalink     : config.document.urls.permalink || '',
		featuredImage : settings.page.model.get('post_featured_image')?.url || ''
	}
}