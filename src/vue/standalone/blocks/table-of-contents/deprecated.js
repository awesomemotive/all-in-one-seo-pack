/**
 * External dependencies.
 */
import { encode } from 'he'
import { html } from '@/vue/standalone/blocks/utils'
const { RawHTML } = window.wp.element

export default [
	{
		attributes : {
			listStyle : {
				type    : 'string',
				default : 'ul'
			},
			headings : {
				type  : 'array',
				items : {
					type : 'object'
				},
				default : []
			},
			reOrdered : {
				type    : 'boolean',
				default : false
			}
		},
		supports : {
			multiple : false,
			html     : false,
			align    : [
				'wide',
				'full'
			]
		},
		save ({ attributes: { headings, listStyle } }) {
			if (!headings) {
				return ''
			}

			const htmlString = getPreviousBlockNestedHeadings(headings, listStyle)

			return html`<${RawHTML}>${htmlString}</${RawHTML}>`
		}
	}
]

const getPreviousBlockNestedHeadings = (headings, listStyle) => {
	let htmlString = `<${listStyle}>`

	headings.forEach((heading) => {
		if (heading.hidden) {
			return
		}

		let listItem  = '<li>'

		const content = heading.editedContent || heading.content
		listItem += `<a href="#${heading.anchor}">${encode(content)}</a>`

		if (heading.headings?.length) {
			listItem += getPreviousBlockNestedHeadings(heading.headings, listStyle)
		}

		listItem += '</li>'

		htmlString += listItem
	})

	htmlString += `</${listStyle}>`

	return htmlString
}