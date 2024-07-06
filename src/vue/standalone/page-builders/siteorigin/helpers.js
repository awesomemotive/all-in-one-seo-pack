import { createElement } from '@/vue/utils/html'

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
const getContent = () => {
	let data = ''

	const whitelist = [
		'p', 'a', 'img', 'caption', 'br', 'video', 'iframe',
		'blockquote', 'cite',
		'em', 'strong', 'i', 'b',
		'q',
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		'ul', 'ol', 'li',
		'table', 'tr', 'th', 'td'
	]

	const extractContent = function (data) {
		const $data = createElement(data)

		// Skip this for empty pages.
		if (0 === $data.querySelectorAll('.so-panel').length) {
			return data
		}

		// Remove elements that have no content analysis value.
		for (const el of $data.querySelectorAll('iframe, script, style, link')) {
			el.remove()
		}

		$data.querySelectorAll('*').forEach((element) => {
			if (!whitelist.includes(element.tagName.toLowerCase())) {
				const content = Array.from(element.childNodes)
				element.replaceWith(...content)
			}
		})

		return $data.innerHTML?.replace(/[\t|\n]/gm, ' ') || ''
	}

	const { soPanelsBuilderView } = window

	if (!soPanelsBuilderView) {
		return data
	}

	if (!Array.isArray(soPanelsBuilderView)) {
		data = extractContent(soPanelsBuilderView.contentPreview)
	} else {
		soPanelsBuilderView.forEach(function (panel) {
			data += extractContent(panel.contentPreview)
		})
	}

	return data
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	return {
		content       : getContent(),
		title         : '',
		excerpt       : '',
		slug          : '',
		permalink     : '',
		featuredImage : ''
	}
}