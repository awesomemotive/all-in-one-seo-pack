/* globals ETBuilderBackendDynamic, ET_Builder */
import { get } from 'lodash-es'
import { getText, getImages } from '@/vue/utils/html'

/**
 * Gets the content area.
 *
 * @returns {Object} DOM node.
 */
const getContentArea = () => {
	const frameElement = get(
		ET_Builder,
		'Frames.app.frameElement',
		document.querySelector('iframe#et-fb-app-frame')
	)

	if (!frameElement) {
		return document.createElement('div')
	}

	let contentArea = frameElement.contentWindow.document.querySelectorAll('#et-fb-app')

	if (1 < contentArea.length) {
		contentArea = [ ...contentArea ].filter(n => n.classList.contains('et-fb-root-ancestor'))
	}

	return contentArea[0] || document.createElement('div')
}

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
export const getContent = () => {
	const content = []
	const sections = getContentArea().querySelectorAll('.et_pb_section')
	const regex = new RegExp([
		/* eslint-disable no-useless-escape */
		'<style.*?<\/style>',
		'\\[object Object\\]'
		/* eslint-enable no-useless-escape */
	].join('|'), 'gi')

	for (const section of sections) {
		const html = section.innerHTML
			.replace(regex, '') // Remove unnecessary content for the analysis.
			.replaceAll(/<p.*>(<img.*>)<\/p>/g, '$1') // Remove the wrapper <p> if there's just an image inside it.

		// Skip if there's no text or images, just HTML markup.
		if ('' === getText(html) && 0 === getImages(html).length) {
			continue
		}

		content.push(html)
	}

	return content.join(' ')
}

/**
 * Gets the post permalink.
 *
 * @returns {string} The post permalink.
 */
export const getPermalink = () => {
	const permalink = new URL(get(ETBuilderBackendDynamic, 'currentPage.permalink', ''))

	// Delete the PageSpeed query arg from Divi builder.
	permalink.searchParams.delete('PageSpeed')

	return permalink.href
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	return {
		content       : getContent(),
		title         : get(ETBuilderBackendDynamic, 'postTitle', ''),
		excerpt       : get(ETBuilderBackendDynamic, 'postMeta.post_excerpt', ''),
		slug          : get(ETBuilderBackendDynamic, 'postMeta.post_name', ''),
		permalink     : getPermalink(),
		featuredImage : get(ETBuilderBackendDynamic, 'currentPage.thumbnailUrl', '')
	}
}