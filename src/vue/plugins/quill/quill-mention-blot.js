import Quill from 'quill'

const Embed = Quill.import('blots/embed')

class MentionBlot extends Embed {
	constructor (domNode) {
		super(domNode)

		// Bind our click handler to the class.
		this.clickHandler = this.clickHandler.bind(this)
		domNode.addEventListener('click', this.clickHandler)
	}

	static getQuillInstance (domNode) {
		let scrollBlot = Quill.find(domNode)
		if (!scrollBlot) {
			return
		}

		// Navigate up to Scroll blot
		while (scrollBlot.parent) {
			scrollBlot = scrollBlot.parent
		}

		return scrollBlot.domNode.parentNode ? Quill.find(scrollBlot.domNode.parentNode) : null
	}

	clickHandler () {
		const quill         = MentionBlot.getQuillInstance(this.domNode)
		const mention       = quill.getModule('mention')
		mention.currentBlot = this
		mention.currentNode = this.domNode

		if (mention.isOpen) {
			this.domNode.querySelector('.aioseo-tag .tag-toggle svg').classList.remove('rotated')
			return mention.hideMentionList()
		}

		this.domNode.querySelector('.aioseo-tag .tag-toggle svg').classList.add('rotated')
		mention.mentionCharPos = this.offset(quill.scroll)
		mention.options.source(
			'',
			mention.renderList.bind(mention, this.domNode.dataset.denotationChar),
			this.domNode.dataset.denotationChar
		)
	}

	static create (data) {
		const node = super.create()
		const denotationChar = document.createElement('span')
		denotationChar.className = 'ql-mention-denotation-char'
		denotationChar.innerHTML = data.denotationChar
		node.appendChild(denotationChar)
		node.innerHTML += data.value

		if (data.custom && data.customValue) {
			// Since this is custom to this specific blot, we need to append the custom value node here.
			let customValue = node.querySelector('.tag-custom')
			if (!customValue) {
				customValue = document.createElement('span')
				customValue.classList.add('tag-custom')
				const name = node.querySelector('.tag-name')
				name.parentNode.insertBefore(customValue, name.nextSibling)
			}

			customValue.innerText = ' - ' + (data.customValue || '')
		}

		setTimeout(() => {
			const quill = MentionBlot.getQuillInstance(node)
			if (quill) {
				const mention       = quill.getModule('mention')
				mention.currentNode = node
			}
		}, 0)

		return MentionBlot.setDataValues(node, data)
	}

	static setDataValues (element, data) {
		const domNode = element
		Object.keys(data).forEach(key => {
			domNode.dataset[key] = data[key]
		})
		return domNode
	}

	static value (domNode) {
		return domNode.dataset
	}

	format (name, value) {
		if ('customValue' === name && value) {
			this.domNode.dataset.customValue = value
		} else {
			super.format(name, value)
		}
	}
}

MentionBlot.blotName  = 'mention'
MentionBlot.tagName   = 'span'
MentionBlot.className = 'mention'

Quill.register(MentionBlot)