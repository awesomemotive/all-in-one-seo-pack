import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { getText } from '@/vue/utils/html'
import { getVideos, getImages } from '@/app/tru-seo/analyzer/analysis/contentHasAssets'

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