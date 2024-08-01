import Quill from 'quill'
import {
	attachDataValues,
	getMentionCharIndex,
	hasValidChars,
	hasValidMentionCharIndex
} from './quill-mention-utils'
import './quill-mention-blot'

const Keys = {
	TAB    : 9,
	ENTER  : 13,
	ESCAPE : 27,
	UP     : 38,
	DOWN   : 40
}

class Mention {
	constructor (quill, options) {
		this.isOpen = false
		this.itemIndex = 0
		this.mentionCharPos = null
		this.cursorPos = null
		this.values = []
		this.suspendMouseEnter = false
		this.activeElement = null
		this.currentBlot   = null
		this.currentNode   = null

		this.quill = quill

		this.silentInsert = false

		this.insertingCustomItem   = false

		this.options = {
			source : null,
			renderItem (item) {
				return `${item.value}`
			},
			onSelect (item, insertItem) {
				insertItem(item)
			},
			mentionDenotationChars : [ '@' ],
			showDenotationChar     : true,
			allowedChars           : /^[a-zA-Z0-9_]*$/,
			minChars               : 0,
			maxChars               : 1000,
			offsetTop              : 2,
			offsetLeft             : 0,
			isolateCharacter       : false,
			fixMentionsToQuill     : false,
			defaultMenuOrientation : 'bottom',
			dataAttributes         : [ 'id', 'value', 'denotationChar', 'link', 'target' ],
			linkTarget             : '_blank',
			onOpen () {
				return true
			},
			onClose () {
				return true
			},
			// Style options
			listItemClass         : 'ql-mention-list-item',
			mentionContainerClass : 'ql-mention-list-container',
			mentionListClass      : 'ql-mention-list',
			spaceAfterInsert      : true
		}

		Object.assign(this.options, options, {
			dataAttributes : Array.isArray(options.dataAttributes)
				? this.options.dataAttributes.concat(options.dataAttributes)
				: this.options.dataAttributes
		})

		this.mentionContainer = document.createElement('div')
		this.mentionContainer.className = this.options.mentionContainerClass
			? this.options.mentionContainerClass
			: ''
		this.mentionContainer.style.cssText = 'display: none; position: absolute;'
		this.mentionContainer.onmousemove = this.onContainerMouseMove.bind(this)

		if (this.options.fixMentionsToQuill) {
			this.mentionContainer.style.width = 'auto'
		}

		this.customFieldInput = document.createElement('div')
		this.customFieldInput.className = this.options.mentionPrependClassCustom
			? this.options.mentionPrependClassCustom
			: ''
		this.customFieldInput.innerHTML = this.options.customFieldInput || ''
		this.mentionContainer.appendChild(this.customFieldInput)

		this.mentionSearch = document.createElement('div')
		this.mentionSearch.className = this.options.mentionPrependClass
			? this.options.mentionPrependClass
			: ''
		this.mentionSearch.innerHTML = this.options.prependMentionList || ''
		this.mentionContainer.appendChild(this.mentionSearch)

		const removeSmartTag = this.mentionSearch.querySelector('.aioseo-trash')
		if (removeSmartTag) {
			removeSmartTag.addEventListener('click', () => {
				this.currentBlot.remove()

				this.hideMentionList()
				this.removeOrphanedMentionChar()
			})
		}

		this.customFieldInput.addEventListener('click', event => {
			this.activeElement = event.target
			const input = event.target.querySelector('input')
			if (input) {
				setTimeout(() => {
					input.focus()
				}, 0)
			}
		})
		const customInput = this.customFieldInput.querySelector('input')
		if (customInput) {
			customInput.addEventListener('input', event => {
				if (this.currentNode) {
					let customValue = this.currentNode.querySelector('.tag-custom')
					if (!customValue) {
						customValue = document.createElement('span')
						customValue.classList.add('tag-custom')
						const name = this.currentNode.querySelector('.tag-name')
						name.parentNode.insertBefore(customValue, name.nextSibling)
					}

					customValue.innerText = event.target.value ? ' - ' + event.target.value : ''
					// this.currentNode.dataset.customValue = event.target.value

					this.currentBlot = Quill.find(this.currentNode)
					this.currentBlot.format('customValue', event.target.value)
				}
			})
		}

		this.customFieldInput.addEventListener('keydown', event => {
			if (13 === event.keyCode) {
				this.hideMentionList()
				this.removeOrphanedMentionChar()
			}

			if (27 === event.keyCode) {
				this.hideMentionList()
				this.removeOrphanedMentionChar()
			}

			if (32 === event.keyCode) {
				event.preventDefault()
			}
		})

		this.mentionSearch.addEventListener('click', event => {
			this.activeElement = event.target
		})
		this.mentionSearch.addEventListener('keydown', event => {
			if (40 === event.keyCode) {
				this.nextItem()
			}

			if (38 === event.keyCode) {
				this.prevItem()
			}

			if (13 === event.keyCode) {
				this.selectItem()
			}

			if (27 === event.keyCode) {
				this.hideMentionList()
			}
		})
		const searchInput = this.mentionSearch.querySelector('input')
		if (searchInput) {
			searchInput.addEventListener('input', event => {
				const textBeforeCursor = this.getTextBeforeCursor()
				const { mentionChar } = getMentionCharIndex(
					textBeforeCursor,
					this.options.mentionDenotationChars
				)

				if (!mentionChar && !this.currentBlot) {
					return
				}

				const currentMentionChar = mentionChar || this.currentBlot.domNode.dataset.denotationChar
				this.options.source(
					event.target.value,
					this.renderList.bind(this, currentMentionChar),
					currentMentionChar
				)
			})
		}

		this.mentionList = document.createElement('ul')
		this.mentionList.className = this.options.mentionListClass
			? this.options.mentionListClass
			: ''
		this.mentionContainer.appendChild(this.mentionList)

		// Documentation link.
		const documentationDiv     = document.createElement('div')
		documentationDiv.className = 'aioseo-documentation-link'
		documentationDiv.innerHTML = this.options.documentationDiv || ''
		documentationDiv.addEventListener('click', event => {
			this.activeElement = event.target
		})

		this.mentionContainer.appendChild(documentationDiv)

		this.quill.container.appendChild(this.mentionContainer)

		quill.on('text-change', this.onTextChange.bind(this))
		quill.on('selection-change', this.onSelectionChange.bind(this))

		quill.keyboard.addBinding(
			{
				key : Keys.TAB
			},
			this.selectHandler.bind(this)
		)
		quill.keyboard.bindings[Keys.TAB].unshift(
			quill.keyboard.bindings[Keys.TAB].pop()
		)

		quill.keyboard.addBinding(
			{
				key : Keys.ENTER
			},
			this.selectHandler.bind(this)
		)
		quill.keyboard.bindings[Keys.ENTER].unshift(
			quill.keyboard.bindings[Keys.ENTER].pop()
		)

		quill.keyboard.addBinding(
			{
				key : Keys.ESCAPE
			},
			this.escapeHandler.bind(this)
		)

		quill.keyboard.addBinding(
			{
				key : Keys.UP
			},
			this.upHandler.bind(this)
		)

		quill.keyboard.addBinding(
			{
				key : Keys.DOWN
			},
			this.downHandler.bind(this)
		)

		quill.clipboard.addMatcher(Node.TEXT_NODE, this.clipboardHandler.bind(this))
	}

	selectHandler () {
		if (this.isOpen) {
			this.selectItem()
			return false
		}
		return true
	}

	escapeHandler () {
		if (this.isOpen) {
			this.hideMentionList()

			this.removeOrphanedMentionChar()

			return false
		}
		return true
	}

	removeOrphanedMentionChar (skipFocus = false) {
		let shouldBlur = false
		if (!skipFocus && !this.quill.hasFocus()) {
			this.quill.focus()
			shouldBlur = true
		}
		const textBefore = this.getTextBeforeCursor()
		if ('#' === textBefore.charAt(textBefore.length - 1)) {
			this.quill.deleteText(this.quill.getSelection().index - 1, 1, Quill.sources.USER)
		}

		if (shouldBlur) {
			this.quill.blur()
		}
	}

	upHandler () {
		if (this.isOpen) {
			this.prevItem()
			return false
		}
		return true
	}

	downHandler () {
		if (this.isOpen) {
			this.nextItem()
			return false
		}
		return true
	}

	clipboardHandler (node, delta) {
		if ('string' !== typeof (node.data)) {
			return
		}

		const regex = new RegExp(`${this.options.mentionDenotationChars.join('|')}[A-Za-z_-]+`, 'g')
		const matches = node.data.match(regex)

		if (matches && 0 < matches.length) {
			const ops = []
			let str   = node.data
			this.mentionCharPos = 0
			matches.forEach(match => {
				const mentionChar = match.charAt(0)
				// Split on dashes so that we can grab the tag name.
				const exploded    = match.substr(1).split('-')

				// The first value is the tag name; any subsequent values are part of a custom field/taxonomy name.
				// Therefore, we delete the first value after cloning it and then put the remaining values back together so that we can use it as a suffix.
				const tagName = exploded[0]
				delete exploded[0]
				const suffix = exploded.filter(value => value).join('-')

				const item = this.options.source(
					tagName,
					this.renderList.bind(this, mentionChar),
					mentionChar,
					true,
					suffix || null
				)

				if (!item) {
					return
				}

				const mention = {
					denotationChar : !this.options.showDenotationChar ? '' : mentionChar,
					...item
				}

				if (undefined !== suffix) {
					mention.customValue = suffix
				}

				const split     = str.split(match)
				const beforeTag = split.shift()
				ops.push({ insert: beforeTag })
				ops.push({
					insert : {
						mention
					}
				})
				str = split.join(match)
			})
			ops.push({ insert: str })
			delta.ops = ops
		}

		return delta
	}

	showMentionList () {
		this.mentionContainer.style.visibility = 'hidden'
		this.mentionContainer.style.display = ''
		this.setMentionContainerPosition()

		this.customFieldInput.style.display = 'none'
		if (this.currentNode && this.currentNode.dataset.custom) {
			this.customFieldInput.style.display = 'block'
			const customInput = this.customFieldInput && this.customFieldInput.querySelector('input')
			const customValue = this.currentNode.querySelector('.tag-custom')
			if (customInput && customValue) {
				customInput.value = customValue.innerText.replace(' - ', '')
			}
		}

		const removeSmartTag = this.mentionSearch.querySelector('.aioseo-trash')
		if (removeSmartTag) {
			removeSmartTag.style.display = 'block'
			if (!this.currentBlot) {
				removeSmartTag.style.display = 'none'
			}
		}

		this.setIsOpen(true)
	}

	hideMentionList () {
		const toggles = document.querySelectorAll('.aioseo-tag .tag-toggle svg')
		toggles.forEach(node => {
			node.classList.remove('rotated')
		})

		const searchInput = this.mentionSearch && this.mentionSearch.querySelector('input')
		if (searchInput) {
			searchInput.value = ''
		}

		const customInput = this.customFieldInput && this.customFieldInput.querySelector('input')
		if (customInput) {
			customInput.value = ''
		}

		this.currentBlot = null
		this.currentNode = null
		this.mentionContainer.style.display = 'none'
		this.setIsOpen(false)
	}

	highlightItem (scrollItemInView = true) {
		if (!this.mentionList.childNodes[this.itemIndex]) {
			return
		}

		for (let i = 0; i < this.mentionList.childNodes.length; i += 1) {
			this.mentionList.childNodes[i].classList.remove('selected')
		}
		this.mentionList.childNodes[this.itemIndex].classList.add('selected')

		if (scrollItemInView) {
			const itemHeight = this.mentionList.childNodes[this.itemIndex]
				.offsetHeight
			const itemPos = this.itemIndex * itemHeight
			const containerTop = this.mentionContainer.scrollTop
			const containerBottom = containerTop + this.mentionContainer.offsetHeight

			if (itemPos < containerTop) {
				// Scroll up if the item is above the top of the container
				this.mentionContainer.scrollTop = itemPos
			} else if (itemPos > containerBottom - itemHeight) {
				// scroll down if any part of the element is below the bottom of the container
				this.mentionContainer.scrollTop += itemPos - containerBottom + itemHeight
			}
		}
	}

	getItemData () {
		const { link } = this.mentionList.childNodes[this.itemIndex].dataset
		const hasLinkValue = 'undefined' !== typeof link
		const itemTarget = this.mentionList.childNodes[this.itemIndex].dataset
			.target
		if (hasLinkValue) {
			this.mentionList.childNodes[
				this.itemIndex
			].dataset.value = `<a href="${link}" target=${itemTarget || this.options.linkTarget}>${
				this.mentionList.childNodes[this.itemIndex].dataset.value
			}`
		}
		return this.mentionList.childNodes[this.itemIndex].dataset
	}

	onContainerMouseMove () {
		this.suspendMouseEnter = false
	}

	selectItem () {
		const data = this.getItemData()
		if (!data.id) {
			return this.hideMentionList()
		}

		this.options.onSelect(data, asyncData => {
			this.insertItem(asyncData)
		})

		if (!data.custom) {
			this.hideMentionList()
		}
	}

	insertItem (data) {
		const render = data
		if (null === render) {
			return
		}
		if (!this.options.showDenotationChar) {
			render.denotationChar = ''
		}

		if (data.custom) {
			this.insertingCustomItem = true
			setTimeout(() => {
				this.insertingCustomItem = false
			}, 50)
			this.customFieldInput.style.display = 'block'
			this.customFieldInput.click()
		}

		const prevMentionCharPos = this.mentionCharPos

		this.quill.deleteText(
			this.mentionCharPos,
			this.cursorPos - this.mentionCharPos,
			Quill.sources.USER
		)
		this.quill.insertEmbed(
			prevMentionCharPos,
			'mention',
			render,
			Quill.sources.USER
		)
		if (this.options.spaceAfterInsert) {
			this.quill.insertText(prevMentionCharPos + 1, ' ', Quill.sources.USER)
			// setSelection here sets cursor position
			this.quill.setSelection(prevMentionCharPos + 2, Quill.sources.USER)
		} else {
			this.quill.setSelection(prevMentionCharPos + 1, Quill.sources.USER)
		}

		if (!data.custom) {
			this.hideMentionList()
		}
	}

	onItemMouseEnter (e) {
		if (this.suspendMouseEnter) {
			return
		}

		const index = Number(e.target.dataset.index)

		if (!Number.isNaN(index) && index !== this.itemIndex) {
			this.itemIndex = index
			this.highlightItem(false)
		}
	}

	onItemClick (e) {
		e.preventDefault()
		e.stopImmediatePropagation()
		this.itemIndex = e.currentTarget.dataset.index
		this.highlightItem()
		this.selectItem()
	}

	renderList (mentionChar, data, searchTerm, returnItem = false, insertExact = false) {
		this.mentionList.innerHTML = ''
		if (data && 0 < data.length) {
			this.values = data
			if (1 === this.values.length && (this.values[0].name === searchTerm || this.values[0].id === searchTerm)) {
				if (returnItem) {
					return this.values[0]
				}
				this.insertItem(this.values[0])
				return
			} else if (returnItem) {
				const item = this.values.find(i => i.name === searchTerm || i.id === searchTerm)
				if (item) {
					return item
				}
			} else if (insertExact) {
				const item = this.values.find(i => i.name === searchTerm || i.id === searchTerm)
				if (item) {
					this.insertItem(item)
				}
			}

			for (let i = 0; i < data.length; i += 1) {
				const li = document.createElement('li')
				li.className = this.options.listItemClass
					? this.options.listItemClass
					: ''
				li.dataset.index = i
				li.innerHTML = this.options.renderItem(data[i], searchTerm)
				li.onmouseenter = this.onItemMouseEnter.bind(this)
				li.dataset.denotationChar = mentionChar
				li.onclick = this.onItemClick.bind(this)
				this.mentionList.appendChild(
					attachDataValues(li, data[i], this.options.dataAttributes)
				)
			}

			this.itemIndex = 0
			this.highlightItem()
		} else {
			const li = document.createElement('li')
			li.className = this.options.listItemClassNoMatch
				? this.options.listItemClassNoMatch
				: ''
			li.innerHTML = this.options.renderItemNoMatch()
			this.mentionList.appendChild(li)
		}

		this.showMentionList()
	}

	nextItem () {
		this.itemIndex = (this.itemIndex + 1) % this.values.length
		this.suspendMouseEnter = true
		this.highlightItem()
	}

	prevItem () {
		this.itemIndex = (this.itemIndex + this.values.length - 1) % this.values.length
		this.suspendMouseEnter = true
		this.highlightItem()
	}

	containerBottomIsNotVisible () {
		// const mentionContainerBottom = topPos + this.mentionContainer.offsetHeight + containerPos.top
		const rect = this.mentionContainer.getBoundingClientRect()
		return rect.bottom > window.innerHeight
	}

	containerRightIsNotVisible (leftPos, containerPos) {
		if (this.options.fixMentionsToQuill) {
			return false
		}

		return containerPos.width < leftPos + this.mentionContainer.offsetWidth

		// This was the original code, replaced by the above when the mention container goes past the right side.
		// const rightPos     = leftPos + this.mentionContainer.offsetWidth + containerPos.left
		// const browserWidth = window.pageXOffset + document.documentElement.clientWidth
		// return rightPos > browserWidth
	}

	setIsOpen (isOpen) {
		if (this.isOpen !== isOpen) {
			if (isOpen) {
				this.options.onOpen()
			} else {
				this.options.onClose()
			}
			this.isOpen = isOpen
		}
	}

	setMentionContainerPosition () {
		const containerPos = this.quill.container.getBoundingClientRect()
		const mentionCharPos = this.quill.getBounds(this.mentionCharPos)
		const containerHeight = this.mentionContainer.offsetHeight

		let topPos = this.options.offsetTop,
			leftPos = this.options.offsetLeft

		// handle horizontal positioning
		if (this.options.fixMentionsToQuill) {
			const rightPos = 0
			this.mentionContainer.style.right = `${rightPos}px`
		} else {
			leftPos += mentionCharPos.left
		}

		if (this.containerRightIsNotVisible(leftPos, containerPos)) {
			const containerWidth = this.mentionContainer.offsetWidth + this.options.offsetLeft
			const quillWidth     = containerPos.width
			leftPos              = quillWidth - containerWidth
		}

		// handle vertical positioning
		if ('top' === this.options.defaultMenuOrientation) {
			// Attempt to align the mention container with the top of the quill editor
			if (this.options.fixMentionsToQuill) {
				topPos = -1 * (containerHeight + this.options.offsetTop)
			} else {
				topPos = mentionCharPos.top - (containerHeight + this.options.offsetTop)
			}

			// default to bottom if the top is not visible
			if (0 >= topPos + containerPos.top) {
				let overMentionCharPos = this.options.offsetTop

				if (this.options.fixMentionsToQuill) {
					overMentionCharPos += containerPos.height
				} else {
					overMentionCharPos += mentionCharPos.bottom
				}

				topPos = overMentionCharPos
			}
		} else {
			// Attempt to align the mention container with the bottom of the quill editor
			if (this.options.fixMentionsToQuill) {
				topPos += containerPos.height
			} else {
				topPos += mentionCharPos.bottom
			}

			// default to the top if the bottom is not visible
			if (this.containerBottomIsNotVisible(topPos, containerPos)) {
				let overMentionCharPos = this.options.offsetTop * -1

				if (!this.options.fixMentionsToQuill) {
					overMentionCharPos += mentionCharPos.top
				}

				topPos = overMentionCharPos - containerHeight
			}
		}

		if (0 <= topPos) {
			this.options.mentionContainerClass.split(' ').forEach(className => {
				this.mentionContainer.classList.add(`${className}-bottom`)
				this.mentionContainer.classList.remove(`${className}-top`)
			})
		} else {
			this.options.mentionContainerClass.split(' ').forEach(className => {
				this.mentionContainer.classList.add(`${className}-top`)
				this.mentionContainer.classList.remove(`${className}-bottom`)
			})
		}

		this.mentionContainer.style.top = `${topPos}px`
		this.mentionContainer.style.left = `${leftPos}px`

		this.mentionContainer.style.visibility = 'visible'
	}

	getTextBeforeCursor () {
		const startPos = Math.max(0, this.cursorPos - this.options.maxChars)
		const textBeforeCursorPos = this.quill.getText(
			startPos,
			this.cursorPos - startPos
		)
		return textBeforeCursorPos
	}

	onSomethingChange () {
		const range = this.quill.getSelection()
		if (null === range || this.insertingCustomItem) {
			return
		}

		this.cursorPos = range.index
		const textBeforeCursor = this.getTextBeforeCursor()
		const { mentionChar, mentionCharIndex } = getMentionCharIndex(
			textBeforeCursor,
			this.options.mentionDenotationChars
		)

		if (
			hasValidMentionCharIndex(
				mentionCharIndex,
				textBeforeCursor,
				this.options.isolateCharacter
			)
		) {
			const mentionCharPos = this.cursorPos - (textBeforeCursor.length - mentionCharIndex)
			this.mentionCharPos  = mentionCharPos
			const textAfter      = textBeforeCursor.substring(
				mentionCharIndex + mentionChar.length
			)
			if (
				textAfter.length >= this.options.minChars &&
				hasValidChars(textAfter, this.options.allowedChars)
			) {
				this.options.source(
					textAfter,
					this.renderList.bind(this, mentionChar),
					mentionChar
				)
			} else {
				this.hideMentionList()
			}
		} else {
			this.hideMentionList()
		}
	}

	onTextChange (delta, oldDelta, source) {
		if ('user' === source) {
			this.onSomethingChange()
		}
	}

	onSelectionChange (range) {
		if (range && 0 === range.length) {
			this.onSomethingChange()
		} else {
			setTimeout(() => {
				if (!this.activeElement) {
					this.hideMentionList()
				} else {
					if (!this.activeElement.classList.contains('aioseo-tag-custom')) {
						this.activeElement = null
					}
				}
			}, 0)
		}
	}
}

Quill.register('modules/mention', Mention)

export default Mention