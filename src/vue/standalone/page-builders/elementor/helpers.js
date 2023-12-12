import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { getText, getImages } from '@/vue/utils/html'

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

	editorDocument.$element.find('.elementor-widget-container').each((_index, element) => {
		const html = element.innerHTML.trim()
			.replaceAll(/<p.*>(<img.*>)<\/p>/g, '$1') // Remove the wrapper <p> if there's just an image inside it.

		// Skip if there's no text or images, just HTML markup.
		if ('' === getText(html) && 0 === getImages(html).length) {
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