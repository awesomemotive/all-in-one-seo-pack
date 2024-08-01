import Quill from 'quill'

// Toolbar button for auto linking.
const Toolbar = Quill.import('modules/toolbar')
class CustomToolbar extends Toolbar {}
CustomToolbar.DEFAULTS.handlers.autoLink = function () {
	const range    = this.quill.getSelection()
	const autoLink = this.quill.getModule('autoLink')
	const delta    = this.quill.getContents()

	delta.ops.forEach(op => {
		if (op.attributes && op.attributes.link) {
			delete op.attributes.link
		}
	})

	this.quill.setContents(delta, Quill.sources.SILENT)
	this.quill.setSelection(range, Quill.sources.SILENT)
	this.quill.format('link', autoLink.options.url, Quill.sources.USER)
}

Quill.register('modules/toolbar', CustomToolbar, true)

// Module for auto linking.
class AutoLink {
	constructor (quill, options) {
		this.quill     = quill
		this.options   = options
		this.container = document.querySelector(options.container)
	}
}

Quill.register('modules/autoLink', AutoLink, true)