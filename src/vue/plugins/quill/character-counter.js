import Quill from 'quill'
import { decode } from 'he'
import { getText } from '@/vue/utils/html'

class Counter {
	constructor (quill, options) {
		this.quill = quill
		this.options = options
		this.container = document.querySelector(options.container)
		quill.on('text-change', this.update.bind(this))
		this.update()  // Account for initial contents
	}

	calculate () {
		let html      = this.quill.getText() ? this.quill.root.innerHTML : ''
		const frag    = document.createRange().createContextualFragment(html)
		const fragNew = document.createRange().createContextualFragment('')
		frag.childNodes.forEach(node => {
			// quill wraps everything in <p /> tags so we are going to loop through those.
			if ('P' !== node.tagName) {
				return
			}

			node.childNodes.forEach(n => {
				if (3 === n.nodeType) {
					fragNew.appendChild(n.cloneNode(true))
					return
				}

				if ('SPAN' === n.tagName) {
					const aioTag  = n.querySelector('.aioseo-tag .tag-name')
					if (aioTag) {
						const tag     = this.options.tags.find(t => t.name === aioTag.textContent)
						let value     = 'string' === typeof tag.valueText ? decode(tag.valueText) : tag.valueText
						// Strip HTML tags from the value to get accurate character count.
						value         = getText(value, false)
						const newNode = document.createTextNode(value)
						fragNew.appendChild(newNode)
					}
				}
			})

			const br = document.createElement('br')
			fragNew.appendChild(br)
		})

		fragNew.normalize()

		const div = document.createElement('div')
		div.appendChild(fragNew.cloneNode(true))

		html = div.innerHTML.replace(/&nbsp;/gi, ' ').replace(/<br\s*[/]?>/gi, this.single ? '' : '\n').trim()
		return { length: decode(html).length, text: html }
	}

	update () {
		const { length } = this.calculate()
		let label        = this.options.unit
		if (1 !== length) {
			label += 's'
		}

		if (this.container) {
			this.container.innerText = length + ' ' + label
		}
	}
}

Quill.register('modules/counter', Counter)