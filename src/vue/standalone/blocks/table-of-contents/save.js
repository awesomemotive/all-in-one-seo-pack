import { encode } from 'he'
import { html } from '@/vue/standalone/blocks/utils'

const { RawHTML } = window.wp.element

/**
 * The save function defines the final markup.
 *
 * @returns {WPElement} Element to render.
 */
export default function save ({ attributes: { headings, listStyle } }) {
	if (!headings) {
		return ''
	}

	const htmlString = getNestedHeadings(headings, listStyle)

	return html`<${RawHTML}>${htmlString}</${RawHTML}>`
}

const getNestedHeadings = (headings, listStyle) => {
	let htmlString = `<${listStyle}>`

	headings.forEach((heading) => {
		if (heading.hidden) {
			return
		}

		let listItem  = '<li>'

		const content = heading.editedContent || heading.content
		listItem += `<a href="#${heading.anchor}">${encode(content)}</a>`

		if (heading.headings?.length) {
			listItem += getNestedHeadings(heading.headings, listStyle)
		}

		listItem += '</li>'

		htmlString += listItem
	})

	htmlString += `</${listStyle}>`

	return htmlString
}