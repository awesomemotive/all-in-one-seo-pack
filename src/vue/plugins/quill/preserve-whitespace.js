import Quill from 'quill'

class PreserveWhiteSpace {
	constructor (quill, options) {
		this.quill = quill
		this.options = options
		this.preserveWhitespace = options.preserveWhitespace || false

		quill.clipboard.addMatcher(Node.TEXT_NODE, this.clipboardHandler.bind(this))
		quill.container.style.whiteSpace = 'pre-line'
	}

	clipboardHandler (node, delta) {
		if (node.data.match(/^\t/) && this.preserveWhitespace) {
			const Delta = Quill.import('delta')
			return new Delta().insert(node.data)
		}
		return delta
	}
}

Quill.register('modules/preserveWhiteSpace', PreserveWhiteSpace)