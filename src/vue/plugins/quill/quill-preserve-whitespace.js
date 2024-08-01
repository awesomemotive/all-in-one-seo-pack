import Quill from 'quill'

class PreserveWhiteSpace {
	constructor (quill) {
		quill.container.style.whiteSpace = 'pre-line'
	}
}

Quill.register('modules/preserveWhiteSpace', PreserveWhiteSpace)