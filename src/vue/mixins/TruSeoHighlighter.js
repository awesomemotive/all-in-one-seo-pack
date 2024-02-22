import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { createApp, h } from 'vue'
import { mapStores } from 'pinia'
import { debounce, random } from 'lodash-es'
import { getOuterText } from '@/vue/utils/html'
import { escapeRegex } from '@/vue/utils/regex'
import { getMostReadableColor } from '@wordpress/block-editor/src/components/colors/utils'
import {
	isBlockEditor,
	isClassicEditor,
	isPageBuilderEditor
} from '@/vue/utils/context'
import {
	normalizeWhitespaces,
	reverseWindowSelection,
	getClosestNodeByPropertyValue,
	createHighlightPopoverNode
} from '@/vue/plugins/tru-seo/components/helpers'
import TruSeoHighlightPopover from '@/vue/components/common/tru-seo/HighlightPopover'

export const TruSeoHighlighter = {
	setup () {
		return {
			truSeoHighlighterStore : useTruSeoHighlighterStore()
		}
	},
	computed : {
		...mapStores(useTruSeoHighlighterStore),
		markBgColor () {
			const defaultBgColor = '#cce0ff'
			if ('function' !== typeof getMostReadableColor) {
				return defaultBgColor
			}

			const node = this.getEditorNode('first-block')?.parentElement || {}
			const color = Object.values(node).length ? document.defaultView.getComputedStyle(node)?.backgroundColor : ''
			if (color && !color.match(/(fffff|255,\s?255,\s?255|rgba)/gi)) {
				return getMostReadableColor([
					{ color: '#e6f0ff' }, // Lighter.
					{ color: defaultBgColor }, // Original.
					{ color: '#b3d1ff' }// Darker.
				], color)
			}

			return defaultBgColor
		}
	},
	data () {
		return {
			editorObserver    : null,
			editorWrapperRect : {},
			isBlockEditor     : isBlockEditor(),
			isClassicEditor   : isClassicEditor(),
			tinymceEditor     : null,
			selectBlockEditor : window?.wp?.data?.select('core/block-editor'),
			selectEditPost    : window?.wp?.data?.select('core/edit-post'),
			strings           : {
				highlightSections : this.$t.__('Highlight sections in the Editor', this.$td)
			}
		}
	},
	methods : {
		annotateBlock (highlightMark) {
			let identifier = 'content' // E.g. "Paragraph" block.
			if (highlightMark.block?.attributes?.caption) { // E.g. "Image" block.
				identifier = 'caption'
			} else if (highlightMark.block?.attributes?.value) { // E.g. "Pull-quote" block.
				identifier = 'value'
			} else if (highlightMark.block?.attributes?.citation) { // E.g. "Quote" block.
				identifier = 'citation'
			}

			this.truSeoHighlighterStore.dispatchAnnotations.__experimentalAddAnnotation({
				id                 : highlightMark.id,
				range              : highlightMark.range,
				source             : this.truSeoHighlighterStore.source,
				blockClientId      : highlightMark.block.clientId,
				richTextIdentifier : identifier
			})
		},
		annotateTinyMce (highlightMark, editor) {
			if (!editor) {
				return false
			}

			if (!editor.hasFocus() && this.isBlockEditor) {
				editor.focus()
			}

			let selection = editor.selection.win.getSelection()
			selection.selectAllChildren(highlightMark.parent)

			const selectionLength = selection.toString().length
			if (highlightMark.range.end < selectionLength) {
				for (let i = highlightMark.range.end; i < selectionLength; i++) {
					selection.modify('extend', 'backward', 'character')
				}
			}

			if (0 < highlightMark.range.start) {
				selection = reverseWindowSelection(selection)

				for (let i = highlightMark.range.start; 0 < i; i--) {
					selection.modify('extend', 'forward', 'character')
				}
			}

			editor.undoManager.ignore(() => {
				editor.annotator.annotate(this.truSeoHighlighterStore.source, {
					uid : highlightMark.id
				})
			})

			selection.empty()
		},
		appendHighlightPopover () {
			this.truSeoHighlighterStore.clearHighlightPopover()
			this.truSeoHighlighterStore.sanitizeHighlightMarks()
			if (!this.truSeoHighlighterStore.activeMark) {
				return false
			}

			const $this = this
			const app = createApp({
				name : 'TruSeoHighlightPopover',
				render () {
					return h(
						TruSeoHighlightPopover,
						{
							onNext () {
								$this.incrementActiveMark(1)
							},
							onPrevious () {
								$this.incrementActiveMark(-1)
							}
						}
					)
				}
			})
			const node = createHighlightPopoverNode()
			const observer = new ResizeObserver(debounce(this.repositionHighlightPopover, 750))
			const editorWrapper = this.getEditorNode('wrapper')
			if (!editorWrapper) {
				return false
			}

			editorWrapper.parentElement.appendChild(node)
			app.mount(node)
			observer.observe(this.getEditorNode('wrapper'), { box: 'border-box' })

			this.truSeoHighlighterStore.highlightPopover.app = app
			this.truSeoHighlighterStore.highlightPopover.node = node
			this.truSeoHighlighterStore.highlightPopover.observer = observer

			this.repositionHighlightPopover()
		},
		disallowHighlighting () {
			this.truSeoHighlighterStore.toggleHighlightAnalyzer(null)
			this.truSeoHighlighterStore.allowHighlighting = false
		},
		formatBlockContent ({ block, node }) {
			let content = ''
			if (node) {
				content = node.outerText.replace(/\n\n/g, '\n')
			} else if (block) {
				content = block?.attributes?.content || block?.attributes?.caption || block?.attributes?.value || block?.attributes?.citation || ''
				// Keep line breaks otherwise `getOuterText()` don't recognize them.
				content = content.replace(/<br[^>]*>/gi, '\n')
				content = getOuterText(content)
			}

			return normalizeWhitespaces(content)
		},
		getEditorNode (which) {
			if ('closest-relative' === which) {
				return getClosestNodeByPropertyValue({
					element  : this.getEditorNode('wrapper').parentElement,
					property : 'position',
					value    : 'relative'
				})
			}

			if ('navigable' === which) {
				return getClosestNodeByPropertyValue({
					element  : this.getEditorNode('wrapper').parentElement,
					property : 'overflow-y',
					value    : 'auto'
				})
			}

			if (this.isClassicEditor) {
				if ('wrapper' === which) {
					return document.getElementById('content_ifr')
				}

				if ('first-block' === which) {
					return this.tinymceEditor.getBody()?.firstElementChild || {}
				}
			}

			if (this.isBlockEditor) {
				if ('wrapper' === which) {
					return document.querySelector('.editor-styles-wrapper')
				}

				if ('first-block' === which) {
					const firstBlock = this.selectBlockEditor.getBlocks()[0]

					return document.getElementById(`block-${firstBlock?.clientId}`) || {}
				}
			}
		},
		highlightBlockEditor () {
			for (const block of (this.selectBlockEditor.getBlocks() || [])) {
				if ('core/freeform' === block.name) {
					const editor = window.tinymce.get(`editor-${block.clientId}`)
					const editorChildren = editor.getBody()?.children || []
					if (!editorChildren.length) {
						continue
					}

					this.registerTinyMceAnnotator(editor)

					for (const node of editorChildren) {
						this.setHighlightMarks({ block, node })
					}

					continue
				}

				this.setHighlightMarks({ block, node: null })
			}

			for (const [ index, hm ] of Object.entries(this.truSeoHighlighterStore.highlightMarks)) {
				this.observeMarkParent(hm.parent, parseInt(index) === this.truSeoHighlighterStore.highlightMarks.length - 1)

				// By adding the annotation texts the observer callback is triggered and the popover is appended.
				if ('core/freeform' === hm.block.name) {
					this.annotateTinyMce(hm, window.tinymce.get(`editor-${hm.block.clientId}`))
				} else {
					this.annotateBlock(hm)
				}
			}
		},
		highlightClassicEditor () {
			const editorChildren = this.tinymceEditor.getBody()?.children || []
			if (!editorChildren.length) {
				return false
			}

			this.registerTinyMceAnnotator(this.tinymceEditor)

			for (const node of editorChildren) {
				this.setHighlightMarks({ block: null, node })
			}

			for (const [ index, hm ] of Object.entries(this.truSeoHighlighterStore.highlightMarks)) {
				this.observeMarkParent(hm.parent, parseInt(index) === this.truSeoHighlighterStore.highlightMarks.length - 1)

				// By adding the annotation texts the observer callback is triggered and the popover is appended.
				this.annotateTinyMce(hm, this.tinymceEditor)
			}
		},
		incrementActiveMark (increment) {
			const currentIndex = this.truSeoHighlighterStore.highlightMarks.findIndex(hm => hm.active)
			if (!this.truSeoHighlighterStore.highlightMarks[currentIndex + increment]) {
				return false
			}

			this.truSeoHighlighterStore.allowScrolling = true
			this.truSeoHighlighterStore.highlightMarks[currentIndex].active = false
			this.truSeoHighlighterStore.highlightMarks[currentIndex + increment].active = true

			this.appendHighlightPopover()
		},
		listenWindowCopy (event) {
			const modifyData = () => {
				event.preventDefault()
				event.clipboardData.setData('text/html', window.getSelection().toString())
			}
			const selection = window.getSelection() || {}
			if (!selection?.rangeCount) {
				return false
			}

			const text = event.clipboardData?.getData('text/html') || ''
			if (text && -1 !== text.indexOf(this.truSeoHighlighterStore.source)) {
				modifyData()

				return false
			}

			const range = selection.getRangeAt(0) || {}
			for (const child of Object.values(range?.cloneContents()?.children || [])) {
				if (Object.values(child?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))) {
					modifyData()

					return false
				}
			}
		},
		listenWindowKeyup (event) {
			const modifySelection = ($selection, node) => {
				$selection.collapse(node.firstChild, node.firstChild.length)
				$selection.deleteFromDocument()
			}

			if (-1 === [ 'Delete', 'Backspace' ].indexOf(event.key)) {
				return false
			}

			const selection = window.getSelection() || null
			if (!selection?.toString()) {
				return false
			}

			const parent = selection?.anchorNode?.parentElement || null
			const sibling = selection?.anchorNode?.nextElementSibling || null
			if (Object.values(parent?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))) {
				modifySelection(selection, parent)
			} else if (Object.values(sibling?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))) {
				modifySelection(selection, sibling)
			}
		},
		observeEditor () {
			this.editorObserver = new MutationObserver((list) => {
				let reset = false

				for (const mutation of list) {
					for (const removedNode of (mutation?.removedNodes || [])) {
						// Cover scenarios where any block is removed or classic blocks are converted.
						// Cover scenarios where marks are removed.
						if (
							this.truSeoHighlighterStore.highlightMarks.some(hm => removedNode.isEqualNode(hm.parent)) ||
							Object.values(removedNode?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))
						) {
							reset = true
						}
					}
				}

				if (reset) {
					this.reset()
				}
			})

			const targetElement = this.getEditorNode('first-block')?.parentElement
			if ('object' !== typeof targetElement) {
				return false
			}

			this.editorObserver.observe(targetElement, {
				attributes : false,
				childList  : true,
				subtree    : true
			})
		},
		observeMarkParent (parent, lastMark = false) {
			const mutationCallback = (list, obs) => {
				obs.disconnect()

				for (const mutation of list) {
					// Cover scenarios where nodes are just updated.
					if (Object.values(mutation?.target?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))) {
						this.setHighlightMarkNode(mutation.target)

						continue
					}

					// Cover scenarios where the mark is first added.
					for (const addedNode of (mutation?.addedNodes || [])) {
						if (Object.values(addedNode?.classList || []).some(c => c.endsWith(this.truSeoHighlighterStore.source))) {
							this.setHighlightMarkNode(addedNode)

							break
						}
					}
				}

				if (lastMark) {
					this.$nextTick().then(() => debounce(this.appendHighlightPopover, 250)())
				}
			}

			if ('object' !== typeof parent?.parentElement) {
				return false
			}

			const observer = new MutationObserver(mutationCallback)

			observer.observe(parent, {
				attributes : true,
				childList  : true,
				subtree    : true
			})
		},
		observeWpBodyContent () {
			const mutationCallback = (list) => {
				for (const mutation of list) {
					if ('attributes' !== mutation?.type) {
						continue
					}

					const hiddenEditorParent = getClosestNodeByPropertyValue({
						element  : this.getEditorNode('wrapper')?.parentElement,
						property : 'display',
						value    : 'none'
					})
					if (!hiddenEditorParent.isEqualNode(document.documentElement)) {
						this.disallowHighlighting()

						return false
					}

					if (this.isBlockEditor) {
						// If the user switches to the "Code editor".
						// If the user starts editing the current selected block as HTML.
						if (
							'visual' !== this.selectEditPost.getEditorMode() ||
							'html' === this.selectBlockEditor.getBlockMode(this.selectBlockEditor.getSelectedBlock()?.clientId)
						) {
							this.disallowHighlighting()

							return false
						}
					}

					if (this.isClassicEditor) {
						this.setTinymceEditor()

						// The user switched to the "Text" tab.
						if (
							'TEXTAREA' === document.getElementById('content')?.nodeName &&
							'none' !== document.getElementById('content').style.display
						) {
							this.disallowHighlighting()

							return false
						}
					}

					this.truSeoHighlighterStore.allowHighlighting = true
				}
			}

			this.wpBodyContentObserver = new MutationObserver(debounce(mutationCallback, 250))
			this.wpBodyContentObserver.observe(document.getElementById('wpbody-content'), {
				attributes : true,
				childList  : false,
				subtree    : true
			})
		},
		registerTinyMceAnnotator (editor) {
			editor.annotator.register(this.truSeoHighlighterStore.source, {
				persistent : false,
				decorate   : () => ({ classes: [ 'annotation-text', `annotation-text-${this.truSeoHighlighterStore.source}` ] })
			})
		},
		repositionHighlightPopover (entries = []) {
			if (
				!this.truSeoHighlighterStore.activeMark ||
				!this.truSeoHighlighterStore.highlightPopover.node
			) {
				return false
			}

			for (const entry of entries) {
				if (JSON.stringify(entry.contentRect) === JSON.stringify(this.editorWrapperRect)) {
					return false
				}
			}

			if (entries.length) {
				this.editorWrapperRect = entries[0]?.contentRect || this.editorWrapperRect
				this.truSeoHighlighterStore.allowScrolling = false
			}

			const activeMarkNodePos = this.truSeoHighlighterStore.activeMark.node.getBoundingClientRect()
			const editorNodePos = this.getEditorNode('wrapper').getBoundingClientRect()
			const relativeEditorPos = this.getEditorNode('closest-relative').getBoundingClientRect()
			const firstBlockNodePos = this.getEditorNode('first-block').getBoundingClientRect()
			const [ navTooltipWidth, navTooltipHeight ] = [ 140, 32 ]
			let top = activeMarkNodePos.top,
				left = firstBlockNodePos.left - relativeEditorPos.left

			if (navTooltipWidth < left) {
				this.truSeoHighlighterStore.highlightPopover.node.style.top = top - relativeEditorPos.top + 'px'
				this.truSeoHighlighterStore.highlightPopover.node.style.transform = 'translate(-105%, 0)'
				this.truSeoHighlighterStore.highlightPopover.node.childNodes[0].dataset.arrowPlacement = 'right'
			} else {
				top = top - navTooltipHeight - relativeEditorPos.top
				top = this.isClassicEditor ? top + editorNodePos.top : top
				left = this.isClassicEditor ? left + editorNodePos.left : left

				this.truSeoHighlighterStore.highlightPopover.node.style.top = top + 'px'
				this.truSeoHighlighterStore.highlightPopover.node.style.transform = 'translate(0, 0)'
				this.truSeoHighlighterStore.highlightPopover.node.childNodes[0].dataset.arrowPlacement = 'bottomleft'
			}

			this.truSeoHighlighterStore.highlightPopover.node.style.left = left + 'px'

			this.scrollToHighlightMark()
		},
		reset () {
			window.removeEventListener('copy', this.listenWindowCopy)
			window.removeEventListener('keyup', this.listenWindowKeyup)

			this.editorObserver?.disconnect()
			this.truSeoHighlighterStore.clearAll()

			this.$nextTick().then(() => {
				/* The user might have fixed the errors while the highlighter was activated.
				   Or the highlighter was simply disabled. */
				if (!this.truSeoHighlighterStore.highlightSentences) {
					this.truSeoHighlighterStore.toggleHighlightAnalyzer(null)

					return false
				}

				if (this.isBlockEditor) {
					this.highlightBlockEditor()
				}

				if (this.isClassicEditor) {
					this.highlightClassicEditor()
				}

				this.observeEditor()

				window.addEventListener('copy', this.listenWindowCopy)
				window.addEventListener('keyup', this.listenWindowKeyup)
			})
		},
		scrollToHighlightMark () {
			this.$nextTick().then(() => {
				if (!this.truSeoHighlighterStore.allowScrolling) {
					return false
				}

				this.truSeoHighlighterStore.activeMark.node.scrollIntoView()

				let scrollable = this.getEditorNode('navigable'),
					offset = scrollable.scrollTop - 60

				if (this.isClassicEditor) {
					offset -= document.querySelector('#wp-content-editor-tools')?.getBoundingClientRect()?.height || 0
					offset -= document.querySelector('#wp-content-editor-container .mce-toolbar-grp')?.getBoundingClientRect()?.height || 0
				}

				if (scrollable.isEqualNode(document.documentElement)) {
					scrollable = window
				}

				scrollable.scrollTo({
					top : offset
				})

				this.truSeoHighlighterStore.allowScrolling = false
			})
		},
		setHighlightMarkNode (node) {
			const findIndex = this.truSeoHighlighterStore.highlightMarks.findIndex(hm => {
				return node.hasAttribute('data-mce-annotation-uid')
					? -1 !== node.dataset.mceAnnotationUid.indexOf(hm.id)
					: -1 !== node.id.indexOf(hm.id)
			})
			if (-1 !== findIndex) {
				this.truSeoHighlighterStore.highlightMarks[findIndex].node = node

				node.style.backgroundColor = this.markBgColor
			}
		},
		setHighlightMarks ({ block, node }) {
			const content = this.formatBlockContent({ block, node })
			if (content) {
				for (const [ index, sentence ] of Object.entries(this.truSeoHighlighterStore.highlightSentences || {})) {
					const regex = new RegExp(escapeRegex(sentence), 'g')
					let match
					while (null !== (match = regex.exec(content))) {
						if (this.truSeoHighlighterStore.highlightMarks.find(hm => hm.sentenceIndex === index)) {
							break
						}

						const range = {
							start : match.index,
							end   : (match.index + match[0].length) || 1
						}
						const highlightMarkExists = this.truSeoHighlighterStore.highlightMarks.find(hm => {
							return node
								? (hm.range.start === range.start || hm.range.end === range.end) && hm.parent.isEqualNode(node)
								: (hm.range.start === range.start || hm.range.end === range.end) && hm.block.clientId === block.clientId
						})
						if (highlightMarkExists) {
							continue
						}

						this.truSeoHighlighterStore.highlightMarks.push({
							id            : random(1, 999999999),
							range         : range,
							block         : block,
							parent        : node || document.getElementById(`block-${block.clientId}`),
							node          : null, // Set later by the block observer.
							active        : 0 === this.truSeoHighlighterStore.highlightMarks.length,
							sentenceIndex : index
						})
					}
				}
			}

			if (block) {
				for (const innerBlock of (block?.innerBlocks || [])) {
					this.setHighlightMarks({ block: innerBlock, node: null })
				}
			}
		},
		setTinymceEditor () {
			if (this.tinymceEditor) {
				return false
			}

			const interval = window.setInterval(() => {
				this.tinymceEditor = window?.tinymce?.get('content') || null
				if (!this.tinymceEditor) {
					return false
				}

				window.clearInterval(interval)

				this.tinymceEditor.dom.addStyle(`
					span.annotation-text.annotation-text-${this.truSeoHighlighterStore.source} {
						background-color: #CCE0FF;
						border-radius: 4px;
						color: inherit;
						display: inline;
						font-size: inherit;
						font-weight: inherit;
						letter-spacing: inherit;
						line-height: inherit;
						position: static;
					}
					`.trim()
				)
			}, 500)
		},
		watchHighlightSentences (value, oldValue) {
			const parsedValue = JSON.stringify(value)
			const parsedOldValue = JSON.stringify(oldValue)
			if (parsedValue !== parsedOldValue) {
				this.reset()
			}
		}
	},
	beforeMount () {
		if (
			isPageBuilderEditor() ||
			(
				!this.isBlockEditor &&
				!this.isClassicEditor
			)
		) {
			this.truSeoHighlighterStore.enabled = false
		}

		if (
			this.isBlockEditor &&
			(
				!this.truSeoHighlighterStore.dispatchAnnotations ||
				!this.selectBlockEditor ||
				!this.selectEditPost
			)
		) {
			this.truSeoHighlighterStore.enabled = false
		}

		if (!this.truSeoHighlighterStore.enabled) {
			return false
		}
	},
	mounted () {
		if (this.wpBodyContentObserver) {
			this.wpBodyContentObserver?.disconnect()
		}

		const interval = window.setInterval(() => {
			if ('object' === typeof this.getEditorNode('wrapper')) {
				window.clearInterval(interval)

				this.observeWpBodyContent()
			}
		}, 1000)
	}
}