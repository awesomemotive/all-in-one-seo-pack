import Quill from 'quill'

const create = (attributes, node, value) => {
	attributes.forEach(attribute => {
		if (value[attribute]) {
			node.setAttribute(attribute, value[attribute])
		}

		if ('data-*' === attribute) {
			Object.keys(value).forEach(key => {
				if (0 === key.indexOf('data-')) {
					node.setAttribute(key, value[key])
				}
			})
		}
	})

	return node
}

const formats = (attributes, domNode) => {
	return attributes.reduce((formats, attribute) => {
		if (domNode.hasAttribute(attribute)) {
			formats[attribute] = domNode.getAttribute(attribute)
		}

		if ('data-*' === attribute) {
			Object.keys(domNode.dataset).forEach(key => {
				key          = 'data-' + key.replace(/[A-Z]/g, k => `-${k.toLowerCase()}`)
				formats[key] = domNode.getAttribute(key)
			})
		}

		return formats
	}, {})
}

// Link override.
const Link = Quill.import('formats/link')

const linkAttributes = [ 'href', 'rel', 'target', 'class', 'data-*', 'style' ]
class AioseoLink extends Link {
	static linkAttributesCache = {}
	static create (value) {
		if ('string' === typeof value) {
			value = this.linkAttributesCache
		}

		const node = create(linkAttributes, super.create(value), value)

		linkAttributes.forEach(attribute => {
			if ('href' === attribute) {
				value[attribute] = this.sanitize(value[attribute])
				node.setAttribute(attribute, value[attribute])
			}
		})

		this.linkAttributesCache = value

		return node
	}

	static formats (domNode) {
		return formats(linkAttributes, domNode)
	}
}

Quill.register('formats/link', AioseoLink, true)

const defaultAttributes = [ 'style', 'class', 'data-*' ]

// Add span tag support.
const Inline = Quill.import('blots/inline')

class AioseoInline extends Inline {
	static create (value) {
		return create(defaultAttributes, super.create(value), value)
	}

	static formats (domNode) {
		return formats(defaultAttributes, domNode)
	}
}
AioseoInline.blotName = 'aioseoInline'
AioseoInline.tagName = 'aioseo-inline'

Quill.register('formats/aioseoInline', AioseoInline, true)

// Italic override.
const Italic = Quill.import('formats/italic')
class AioseoItalic extends Italic {
	static create (value) {
		return create(defaultAttributes, super.create(value), value)
	}

	static formats (domNode) {
		return formats(defaultAttributes, domNode)
	}
}

Quill.register('formats/italic', AioseoItalic, true)