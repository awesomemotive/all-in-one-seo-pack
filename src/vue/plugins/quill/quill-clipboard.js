import Quill from 'quill'

const Clipboard = Quill.import('modules/clipboard')
const CodeBlock = Quill.import('formats/code')
const Delta     = Quill.import('delta')
class CustomClipboard extends Clipboard {
	onPaste (e) {
		const self      = this
		const options   = this.options
		const range     = this.quill.getSelection()
		const scrollTop = this.quill.scrollingContainer.scrollTop
		let delta       = new Delta().retain(range.index)

		if (e.defaultPrevented || !this.quill.isEnabled()) {
			return
		}

		this.container.focus()
		this.quill.selection.update(Quill.sources.SILENT)
		setTimeout(function () {
			delta = delta.concat(self.convert()).delete(range.length)

			if (false === options.newLines) {
				delta.ops.map(function (op) {
					if ('string' === typeof op.insert) {
						op.insert = op.insert.replace(/(\r\n|\n|\r)/gm, ' ')
					}
					return op
				})
			}

			self.quill.updateContents(delta, Quill.sources.USER)
			// range.length contributes to delta.length()
			self.quill.setSelection(delta.length() - range.length, Quill.sources.SILENT)
			self.quill.scrollingContainer.scrollTop = scrollTop
			self.quill.focus()
		}, 1)
	}

	convert (html) {
		if ('string' === typeof html) {
			// Remove spaces between tags
			// this.container.innerHTML = html = html.replace(/>\s+<(?!\s*(?:b|big|i|small|tt|abbr|acronym|cite|code|dfn|em|kbd|strong|samp|var|a|bd|br|img|map|object|q|script|span|sub|sub|sup|button|input|label|select|textarea))/g, '><')
			this.container.innerHTML = html.replace(/>\r?\n +</g, '><')
			return this.convert()
		}
		const formats = this.quill.getFormat(this.quill.selection.savedRange.index)
		if (formats[CodeBlock.blotName]) {
			const text = this.container.innerText
			this.container.innerHTML = ''
			return new Delta().insert(text, { [CodeBlock.blotName]: formats[CodeBlock.blotName] })
		}
		const [ elementMatchers, textMatchers ] = this.prepareMatching()
		textMatchers[0] = matchText
		let delta = traverse(this.container, elementMatchers, textMatchers)
		// Remove trailing newline
		if (deltaEndsWith(delta, '\n') && null === delta.ops[delta.ops.length - 1].attributes) {
			delta = delta.compose(new Delta().retain(delta.length() - 1).delete(1))
		}

		this.container.innerHTML = ''
		return delta
	}
}

const DOM_KEY = '__ql-matcher'
function traverse (node, elementMatchers, textMatchers) { // Post-order
	if (node.nodeType === node.TEXT_NODE) {
		const newNode = textMatchers.reduce(function (delta, matcher) {
			return matcher(node, delta)
		}, new Delta())

		return newNode
	} else if (node.nodeType === node.ELEMENT_NODE) {
		return [].reduce.call(node.childNodes || [], (delta, childNode) => {
			let childrenDelta = traverse(childNode, elementMatchers, textMatchers)
			if (childNode.nodeType === node.ELEMENT_NODE) {
				childrenDelta = elementMatchers.reduce(function (childrenDelta, matcher) {
					return matcher(childNode, childrenDelta)
				}, childrenDelta)
				childrenDelta = (childNode[DOM_KEY] || []).reduce(function (childrenDelta, matcher) {
					return matcher(childNode, childrenDelta)
				}, childrenDelta)
			}
			return delta.concat(childrenDelta)
		}, new Delta())
	} else {
		return new Delta()
	}
}

function deltaEndsWith (delta, text) {
	let endText = ''
	for (let i = delta.ops.length - 1; 0 <= i && endText.length < text.length; --i) {
		const op = delta.ops[i]
		if ('string' !== typeof op.insert) break
		endText = op.insert + endText
	}
	return endText.slice(-1 * text.length) === text
}

function matchText (node, delta) {
	let text = node.data
	// Word represents empty line with <o:p>&nbsp;</o:p>
	if ('O:P' === node.parentNode.tagName) {
		return delta.insert(text.trim())
	}
	if (!computeStyle(node.parentNode).whiteSpace.startsWith('pre')) {
		// eslint-disable-next-line func-style
		const replacer = function (collapse, match) {
			match = match.replace(/[^\u00a0]/g, '') // \u00a0 is nbsp;
			return 1 > match.length && collapse ? ' ' : match
		}
		text = text.replace(/\r\n/g, ' ').replace(/\n/g, ' ')
		text = text.replace(/\s\s+/g, replacer.bind(replacer, true)) // collapse whitespace
		if ((null === node.previousSibling && isLine(node.parentNode)) ||
			(null !== node.previousSibling && isLine(node.previousSibling))) {
			text = text.replace(/^\s+/, replacer.bind(replacer, false))
		}
		if ((null === node.nextSibling && isLine(node.parentNode)) ||
			(null !== node.nextSibling && isLine(node.nextSibling))) {
			text = text.replace(/\s+$/, replacer.bind(replacer, false))
		}
	}
	return delta.insert(text)
}

function computeStyle (node) {
	if (node.nodeType !== Node.ELEMENT_NODE) return {}
	const DOM_KEY = '__ql-computed-style'
	return node[DOM_KEY] || (node[DOM_KEY] = window.getComputedStyle(node))
}

function isLine (node) {
	if (0 === node.childNodes.length) return false   // Exclude embed blocks
	const style = computeStyle(node)
	return -1 < [ 'block', 'list-item' ].indexOf(style.display)
}

Quill.register('modules/clipboard', CustomClipboard, true)