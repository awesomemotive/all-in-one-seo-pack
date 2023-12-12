/* globals seedprod_data */
import { get } from 'lodash-es'
import { getText, getImages } from '@/vue/utils/html'

/**
 * Get the HTML for a given block object.
 *
 * @param   {Object} block The block object.
 * @returns {Object}       The DOM object.
 */
const getBlockHTML = block => {
	let html

	switch (block.type) {
		// Headline block.
		case 'header':
			html = document.createElement(block.settings.tag)
			html.innerHTML = block.settings.headerTxt.trim()
			break

		// Image block.
		case 'image':
			// eslint-disable-next-line no-case-declarations
			const image = document.createElement('img')
			block.settings.altTxt && image.setAttribute('alt', block.settings.altTxt)
			block.settings.src && image.setAttribute('src', block.settings.src)
			block.settings.noFollow && image.setAttribute('rel', 'nofollow')

			html = image
			if (block.settings.link) {
				html = document.createElement('a')
				html.setAttribute('href', block.settings.link)
				html.innerHTML = image.outerHTML
				block.settings.noFollow && html.setAttribute('rel', 'nofollow')
				block.settings.openNewWindow && html.setAttribute('target', '_blank')
			}
			break

		// Video block.
		case 'video':
			if ('youtube' === block.settings.type) {
				html = document.createElement('iframe')
				html.setAttribute('src', block.settings.youtubeUrl)
			} else {
				html = document.createElement('div')
				html.innerHTML = block.settings.code
			}
			break

		// Text block.
		case 'text':
			html = document.createElement('div')
			html.innerHTML = block.settings.txt.trim()
			break

		// Button block.
		case 'button':
			html = document.createElement('a')
			html.appendChild(document.createTextNode(block.settings.btnTxt + ' ' + block.settings.btnSubTxt))
			block.settings.link && html.setAttribute('href', block.settings.link)
			block.settings.altTxt && html.setAttribute('alt', block.settings.altTxt)
			block.settings.src && html.setAttribute('src', block.settings.src)
			block.settings.noFollow && html.setAttribute('rel', 'nofollow')
			block.settings.openNewWindow && html.setAttribute('target', '_blank')
			break

		// List block.
		case 'bullet-list':
			html = document.createElement('ul')
			block.settings.items.forEach((item) => {
				const li = document.createElement('li')
				li.innerHTML = item.txt
				html.appendChild(li)
			})
			break

		// Custom HTML block.
		case 'custom-html':
			html = document.createElement('div')
			html.innerHTML = block.settings.code
			break
	}

	return html
}

/**
 * Gets the post content.
 *
 * @returns {string} The post content.
 */
export const getContent = () => {
	const content = []
	const sections = JSON.parse(JSON.stringify(get(seedprod_data, 'settings.document.sections', [])))

	sections.forEach(section => {
		section.rows.forEach(row => {
			row.cols.forEach(col => {
				col.blocks.forEach(block => {
					const html = getBlockHTML(block)

					if (!html) {
						return
					}

					// Skip if there's no text or images, just HTML markup.
					if ('' === getText(html) && 0 === getImages(html).length) {
						return
					}

					content.push(html.outerHTML)
				})
			})
		})
	})

	return content.join(' ')
}

/**
 * Gets the post permalink.
 *
 * @returns {string} The post permalink.
 */
export const getPermalink = () => {
	const homeUrl = get(seedprod_data, 'home_url', '')
	const postName = get(seedprod_data, 'settings.post_name', '')

	return `${homeUrl}/${postName}/`
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	return {
		content   : getContent(),
		title     : get(seedprod_data, 'settings.post_title', ''),
		excerpt   : '',
		slug      : get(seedprod_data, 'settings.post_name', ''),
		permalink : getPermalink()
	}
}