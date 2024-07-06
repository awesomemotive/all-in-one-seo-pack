import Quill from 'quill'

class LineNumbers {
	constructor (quill, options) {
		this.quill     = quill
		this.options   = {
			defaultLength : 1,
			...options
		}
		this.container = options.container
		quill.on('text-change', this.update.bind(this))
		this.update() // Account for initial contents
	}

	update () {
		// Clear old nodes
		while (this.container.firstChild) {
			this.container.removeChild(this.container.firstChild)
		}

		const lines = this.quill.getLines()

		if (this.options.defaultLength > lines.length) {
			for (let i = lines.length; this.options.defaultLength > i; i++) {
				lines.push({})
			}
		}

		// Add new nodes
		let minHeight = 0
		for (let i = 0; i < lines.length; i++) {
			const height = lines[i].domNode ? lines[i].domNode.offsetHeight : 25

			const node = document.createElement('div')

			node.style.minHeight = `${height}px`
			node.innerHTML       = i + 1
			this.container.appendChild(node)

			minHeight += height
		}

		const editor = this.quill.container.querySelector('.ql-editor')
		if (editor) {
			const styles           = window.getComputedStyle(editor)
			editor.style.minHeight = `${minHeight + parseInt(styles.getPropertyValue('padding-top')) + parseInt(styles.getPropertyValue('padding-bottom'))}px`
		}
	}
}

Quill.register('modules/lineNumbers', LineNumbers, true)

export default LineNumbers