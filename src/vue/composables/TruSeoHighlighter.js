import { ref, nextTick, createApp, h, onMounted, onBeforeMount } from 'vue'

import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { debounce, random } from 'lodash-es'
import { getOuterText } from '@/vue/utils/html'
import { escapeRegex } from '@/vue/utils/regex'

import {
	isBlockEditor as isBlockEditorFunc,
	isClassicEditor as isClassicEditorFunc,
	isPageBuilderEditor
} from '@/vue/utils/context'

import {
	normalizeWhitespaces,
	reverseWindowSelection,
	getClosestNodeByPropertyValue,
	createHighlightPopoverNode
} from '@/vue/plugins/tru-seo/components/helpers'

import TruSeoHighlightPopover from '@/vue/components/common/tru-seo/HighlightPopover'

export const useTruSeoHighlighter = () => {
	const editorObserver    = ref(null)
	const editorWrapperRect = ref({})
	const isBlockEditor     = isBlockEditorFunc()
	const isClassicEditor   = isClassicEditorFunc()
	const selectBlockEditor = window?.wp?.data?.select('core/block-editor')
	const selectEditPost    = window?.wp?.data?.select('core/edit-post')
	const tinymceEditor     = ref(null)

	const truSeoHighlighterStore = useTruSeoHighlighterStore()

	const annotateBlock = (highlightMark) => {
		let identifier = 'content' // E.g. "Paragraph" block.
		if (highlightMark.block?.attributes?.caption) { // E.g. "Image" block.
			identifier = 'caption'
		} else if (highlightMark.block?.attributes?.value) { // E.g. "Pull-quote" block.
			identifier = 'value'
		} else if (highlightMark.block?.attributes?.citation) { // E.g. "Quote" block.
			identifier = 'citation'
		}

		truSeoHighlighterStore.dispatchAnnotations.__experimentalAddAnnotation({
			id                 : highlightMark.id,
			range              : highlightMark.range,
			source             : truSeoHighlighterStore.source,
			blockClientId      : highlightMark.block.clientId,
			richTextIdentifier : identifier
		})
	}

	const annotateTinyMce = (highlightMark, editor) => {
		if (!editor) {
			return false
		}

		if (!editor.hasFocus() && isBlockEditor) {
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
			editor.annotator.annotate(truSeoHighlighterStore.source, {
				uid : highlightMark.id
			})
		})

		selection.empty()
	}

	const appendHighlightPopover = () => {
		truSeoHighlighterStore.clearHighlightPopover()
		truSeoHighlighterStore.sanitizeHighlightMarks()
		if (!truSeoHighlighterStore.activeMark) {
			return false
		}

		const app = createApp({
			name : 'TruSeoHighlightPopover',
			render () {
				return h(
					TruSeoHighlightPopover,
					{
						onNext () {
							incrementActiveMark(1)
						},
						onPrevious () {
							incrementActiveMark(-1)
						}
					}
				)
			}
		})
		const node = createHighlightPopoverNode()
		const observer = new ResizeObserver(debounce(repositionHighlightPopover, 750))
		const editorWrapper = getEditorNode('wrapper')
		if (!editorWrapper) {
			return false
		}

		editorWrapper.parentElement.appendChild(node)
		app.mount(node)
		observer.observe(getEditorNode('wrapper'), { box: 'border-box' })

		truSeoHighlighterStore.highlightPopover.app      = app
		truSeoHighlighterStore.highlightPopover.node     = node
		truSeoHighlighterStore.highlightPopover.observer = observer

		repositionHighlightPopover()
	}

	const disallowHighlighting = () => {
		truSeoHighlighterStore.toggleHighlightAnalyzer(null)
		truSeoHighlighterStore.allowHighlighting = false
	}

	const formatBlockContent = ({ block, node }) => {
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
	}

	const getEditorNode = (which) => {
		if ('closest-relative' === which) {
			return getClosestNodeByPropertyValue({
				element  : getEditorNode('wrapper').parentElement,
				property : 'position',
				value    : 'relative'
			})
		}

		if ('navigable' === which) {
			return getClosestNodeByPropertyValue({
				element  : getEditorNode('wrapper').parentElement,
				property : 'overflow-y',
				value    : 'auto'
			})
		}

		if (isClassicEditor) {
			if ('wrapper' === which) {
				return document.getElementById('content_ifr')
			}

			if ('first-block' === which) {
				return tinymceEditor.value.getBody()?.firstElementChild || {}
			}
		}

		if (isBlockEditor) {
			if ('wrapper' === which) {
				return document.querySelector('.editor-styles-wrapper')
			}

			if ('first-block' === which) {
				const firstBlock = selectBlockEditor.getBlocks()[0]

				return document.getElementById(`block-${firstBlock?.clientId}`) || {}
			}
		}
	}

	const highlightBlockEditor = () => {
		for (const block of (selectBlockEditor.getBlocks() || [])) {
			if ('core/freeform' === block.name) {
				const editor = window.tinymce.get(`editor-${block.clientId}`)
				const editorChildren = editor.getBody()?.children || []
				if (!editorChildren.length) {
					continue
				}

				registerTinyMceAnnotator(editor)

				for (const node of editorChildren) {
					setHighlightMarks({ block, node })
				}

				continue
			}

			setHighlightMarks({ block, node: null })
		}

		for (const [ index, hm ] of Object.entries(truSeoHighlighterStore.highlightMarks)) {
			observeMarkParent(hm.parent, parseInt(index) === truSeoHighlighterStore.highlightMarks.length - 1)

			// By adding the annotation texts the observer callback is triggered and the popover is appended.
			if ('core/freeform' === hm.block.name) {
				annotateTinyMce(hm, window.tinymce.get(`editor-${hm.block.clientId}`))
			} else {
				annotateBlock(hm)
			}
		}
	}

	const highlightClassicEditor = () => {
		const editorChildren = tinymceEditor.value.getBody()?.children || []
		if (!editorChildren.length) {
			return false
		}

		registerTinyMceAnnotator(tinymceEditor.value)

		for (const node of editorChildren) {
			setHighlightMarks({ block: null, node })
		}

		for (const [ index, hm ] of Object.entries(truSeoHighlighterStore.highlightMarks)) {
			observeMarkParent(hm.parent, parseInt(index) === truSeoHighlighterStore.highlightMarks.length - 1)

			// By adding the annotation texts the observer callback is triggered and the popover is appended.
			annotateTinyMce(hm, tinymceEditor.value)
		}
	}

	const incrementActiveMark = (increment) => {
		const currentIndex = truSeoHighlighterStore.highlightMarks.findIndex(hm => hm.active)
		if (!truSeoHighlighterStore.highlightMarks[currentIndex + increment]) {
			return false
		}

		truSeoHighlighterStore.allowScrolling = true
		truSeoHighlighterStore.highlightMarks[currentIndex].active = false
		truSeoHighlighterStore.highlightMarks[currentIndex + increment].active = true

		appendHighlightPopover()
	}

	const listenWindowCopy = (event) => {
		const modifyData = () => {
			event.preventDefault()
			event.clipboardData.setData('text/html', window.getSelection().toString())
		}
		const selection = window.getSelection() || {}
		if (!selection?.rangeCount) {
			return false
		}

		const text = event.clipboardData?.getData('text/html') || ''
		if (text && -1 !== text.indexOf(truSeoHighlighterStore.source)) {
			modifyData()

			return false
		}

		const range = selection.getRangeAt(0) || {}
		for (const child of Object.values(range?.cloneContents()?.children || [])) {
			if (Object.values(child?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))) {
				modifyData()

				return false
			}
		}
	}

	const listenWindowKeyup = (event) => {
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
		if (Object.values(parent?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))) {
			modifySelection(selection, parent)
		} else if (Object.values(sibling?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))) {
			modifySelection(selection, sibling)
		}
	}

	const observeEditor = () => {
		editorObserver.value = new MutationObserver((list) => {
			let shouldReset = false

			for (const mutation of list) {
				for (const removedNode of (mutation?.removedNodes || [])) {
					// Cover scenarios where any block is removed or classic blocks are converted.
					// Cover scenarios where marks are removed.
					if (
						truSeoHighlighterStore.highlightMarks.some(hm => removedNode.isEqualNode(hm.parent)) ||
						Object.values(removedNode?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))
					) {
						shouldReset = true
					}
				}
			}

			if (shouldReset) {
				reset()
			}
		})

		const targetElement = getEditorNode('first-block')?.parentElement
		if ('object' !== typeof targetElement) {
			return false
		}

		editorObserver.value.observe(targetElement, {
			attributes : false,
			childList  : true,
			subtree    : true
		})
	}

	const observeMarkParent = (parent, lastMark = false) => {
		const mutationCallback = (list, obs) => {
			obs.disconnect()

			for (const mutation of list) {
				// Cover scenarios where nodes are just updated.
				if (Object.values(mutation?.target?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))) {
					setHighlightMarkNode(mutation.target)

					continue
				}

				// Cover scenarios where the mark is first added.
				for (const addedNode of (mutation?.addedNodes || [])) {
					if (Object.values(addedNode?.classList || []).some(c => c.endsWith(truSeoHighlighterStore.source))) {
						setHighlightMarkNode(addedNode)

						break
					}
				}
			}

			if (lastMark) {
				nextTick().then(() => debounce(appendHighlightPopover, 250)())
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
	}

	const observeWpBodyContent = () => {
		const mutationCallback = (list) => {
			for (const mutation of list) {
				if ('attributes' !== mutation?.type) {
					continue
				}

				const hiddenEditorParent = getClosestNodeByPropertyValue({
					element  : getEditorNode('wrapper')?.parentElement,
					property : 'display',
					value    : 'none'
				})
				if (!hiddenEditorParent.isEqualNode(document.documentElement)) {
					disallowHighlighting()

					return false
				}

				if (isBlockEditor) {
					// If the user switches to the "Code editor".
					// If the user starts editing the current selected block as HTML.
					if (
						'visual' !== selectEditPost.getEditorMode() ||
						'html' === selectBlockEditor.getBlockMode(selectBlockEditor.getSelectedBlock()?.clientId)
					) {
						disallowHighlighting()

						return false
					}
				}

				if (isClassicEditor) {
					setTinymceEditor()

					// The user switched to the "Text" tab.
					if (
						'TEXTAREA' === document.getElementById('content')?.nodeName &&
						'none' !== document.getElementById('content').style.display
					) {
						disallowHighlighting()

						return false
					}
				}

				truSeoHighlighterStore.allowHighlighting = true
			}
		}

		truSeoHighlighterStore.wpBodyContentObserver = new MutationObserver(debounce(mutationCallback, 250))
		truSeoHighlighterStore.wpBodyContentObserver.observe(document.getElementById('wpbody-content'), {
			attributes : true,
			childList  : false,
			subtree    : true
		})
	}

	const registerTinyMceAnnotator = (editor) => {
		editor.annotator.register(truSeoHighlighterStore.source, {
			persistent : false,
			decorate   : () => ({ classes: [ 'annotation-text', `annotation-text-${truSeoHighlighterStore.source}` ] })
		})
	}

	const repositionHighlightPopover = (entries = []) => {
		if (
			!truSeoHighlighterStore.activeMark ||
			!truSeoHighlighterStore.highlightPopover.node
		) {
			return false
		}

		for (const entry of entries) {
			if (JSON.stringify(entry.contentRect) === JSON.stringify(editorWrapperRect)) {
				return false
			}
		}

		if (entries.length) {
			editorWrapperRect.value = entries[0]?.contentRect || editorWrapperRect
			truSeoHighlighterStore.allowScrolling = false
		}

		const activeMarkNodePos = truSeoHighlighterStore.activeMark.node.getBoundingClientRect()
		const editorNodePos = getEditorNode('wrapper').getBoundingClientRect()
		const relativeEditorPos = getEditorNode('closest-relative').getBoundingClientRect()
		const firstBlockNodePos = getEditorNode('first-block').getBoundingClientRect()
		const [ navTooltipWidth, navTooltipHeight ] = [ 140, 32 ]
		let top = activeMarkNodePos.top,
			left = firstBlockNodePos.left - relativeEditorPos.left

		if (navTooltipWidth < left) {
			truSeoHighlighterStore.highlightPopover.node.style.top = top - relativeEditorPos.top + 'px'
			truSeoHighlighterStore.highlightPopover.node.style.transform = 'translate(-105%, 0)'
			truSeoHighlighterStore.highlightPopover.node.childNodes[0].dataset.arrowPlacement = 'right'
		} else {
			top = top - navTooltipHeight - relativeEditorPos.top
			top = isClassicEditor ? top + editorNodePos.top : top
			left = isClassicEditor ? left + editorNodePos.left : left

			truSeoHighlighterStore.highlightPopover.node.style.top = top + 'px'
			truSeoHighlighterStore.highlightPopover.node.style.transform = 'translate(0, 0)'
			truSeoHighlighterStore.highlightPopover.node.childNodes[0].dataset.arrowPlacement = 'bottomleft'
		}

		truSeoHighlighterStore.highlightPopover.node.style.left = left + 'px'

		scrollToHighlightMark()
	}

	const reset = () => {
		window.removeEventListener('copy', listenWindowCopy)
		window.removeEventListener('keyup', listenWindowKeyup)

		editorObserver.value?.disconnect()
		truSeoHighlighterStore.clearAll()

		nextTick().then(() => {
			// The user might have fixed the errors while the highlighter was activated. Or the highlighter was simply disabled.
			if (!truSeoHighlighterStore.highlightSentences) {
				truSeoHighlighterStore.toggleHighlightAnalyzer(null)

				return false
			}

			if (isBlockEditor) {
				highlightBlockEditor()
			}

			if (isClassicEditor) {
				highlightClassicEditor()
			}

			observeEditor()

			window.addEventListener('copy', listenWindowCopy)
			window.addEventListener('keyup', listenWindowKeyup)
		})
	}

	const scrollToHighlightMark = () => {
		nextTick().then(() => {
			if (!truSeoHighlighterStore.allowScrolling) {
				return false
			}

			truSeoHighlighterStore.activeMark.node.scrollIntoView()

			let scrollable = getEditorNode('navigable'),
				offset = scrollable.scrollTop - 60

			if (isClassicEditor) {
				offset -= document.querySelector('#wp-content-editor-tools')?.getBoundingClientRect()?.height || 0
				offset -= document.querySelector('#wp-content-editor-container .mce-toolbar-grp')?.getBoundingClientRect()?.height || 0
			}

			if (scrollable.isEqualNode(document.documentElement)) {
				scrollable = window
			}

			scrollable.scrollTo({
				top : offset
			})

			truSeoHighlighterStore.allowScrolling = false
		})
	}

	const setHighlightMarkNode = (node) => {
		const findIndex = truSeoHighlighterStore.highlightMarks.findIndex(hm => {
			return node.hasAttribute('data-mce-annotation-uid')
				? -1 !== node.dataset.mceAnnotationUid.indexOf(hm.id)
				: -1 !== node.id.indexOf(hm.id)
		})
		if (-1 !== findIndex) {
			truSeoHighlighterStore.highlightMarks[findIndex].node = node

			node.style.backgroundColor = '#cce0ff'
		}
	}

	const setHighlightMarks = ({ block, node }) => {
		const content = formatBlockContent({ block, node })
		if (content) {
			for (const [ index, sentence ] of Object.entries(truSeoHighlighterStore.highlightSentences || {})) {
				const regex = new RegExp(escapeRegex(sentence), 'g')
				let match
				while (null !== (match = regex.exec(content))) {
					if (truSeoHighlighterStore.highlightMarks.find(hm => hm.sentenceIndex === index)) {
						break
					}

					const range = {
						start : match.index,
						end   : (match.index + match[0].length) || 1
					}
					const highlightMarkExists = truSeoHighlighterStore.highlightMarks.find(hm => {
						return node
							? (hm.range.start === range.start || hm.range.end === range.end) && hm.parent.isSameNode(node)
							: (hm.range.start === range.start || hm.range.end === range.end) && hm.block.clientId === block.clientId
					})
					if (highlightMarkExists) {
						continue
					}

					truSeoHighlighterStore.highlightMarks.push({
						id            : random(1, 999999999),
						range         : range,
						block         : block,
						parent        : node || document.getElementById(`block-${block.clientId}`),
						node          : null, // Set later by the block observer.
						active        : 0 === truSeoHighlighterStore.highlightMarks.length,
						sentenceIndex : index
					})
				}
			}
		}

		if (block) {
			for (const innerBlock of (block?.innerBlocks || [])) {
				setHighlightMarks({ block: innerBlock, node: null })
			}
		}
	}

	const setTinymceEditor = () => {
		if (tinymceEditor.value) {
			return false
		}

		const interval = window.setInterval(() => {
			tinymceEditor.value = window?.tinymce?.get('content') || null
			if (!tinymceEditor.value) {
				return false
			}

			window.clearInterval(interval)

			tinymceEditor.value.dom.addStyle(`
				span.annotation-text.annotation-text-${truSeoHighlighterStore.source} {
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
	}

	const watchHighlightSentences = (value, oldValue) => {
		const parsedValue = JSON.stringify(value)
		const parsedOldValue = JSON.stringify(oldValue)
		if (parsedValue !== parsedOldValue) {
			reset()
		}
	}

	onMounted(() => {
		if (truSeoHighlighterStore.wpBodyContentObserver) {
			truSeoHighlighterStore.wpBodyContentObserver?.disconnect()
		}

		const interval = window.setInterval(() => {
			if ('object' === typeof getEditorNode('wrapper')) {
				window.clearInterval(interval)

				observeWpBodyContent()
			}
		}, 1000)
	})

	onBeforeMount(() => {
		if (
			isPageBuilderEditor() ||
			(
				!isBlockEditor &&
				!isClassicEditor
			)
		) {
			truSeoHighlighterStore.enabled = false
		}

		if (
			isBlockEditor &&
			(
				!truSeoHighlighterStore.dispatchAnnotations ||
				!selectBlockEditor ||
				!selectEditPost
			)
		) {
			truSeoHighlighterStore.enabled = false
		}
	})

	return {
		watchHighlightSentences
	}
}