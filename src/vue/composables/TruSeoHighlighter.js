import { ref, watch, nextTick, createApp, h, onMounted, onBeforeMount, onUnmounted } from 'vue'

import {
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { debounce, random } from 'lodash-es'
import { claimSentenceRanges, isMarkableUnit } from '@/vue/plugins/tru-seo/highlighter/sentences'
import { getAssessmentColor } from '@/vue/plugins/tru-seo/helpers/assessmentColors'

import { isPageBuilderEditor } from '@/vue/utils/context'

import { getAdapterGeneration, resolveAdapter } from '@/vue/plugins/tru-seo/editors'
import { HIGHLIGHT_CLASS_PREFIX } from '@/vue/plugins/tru-seo/highlighter/wpDataStore'

import {
	getEditorIframe,
	isIframedEditor
} from '@/vue/utils/editor'

import {
	normalizeWhitespaces,
	getClosestNodeByPropertyValue,
	createHighlightPopoverNode
} from '@/vue/utils/postData/helpers'

import TruSeoHighlightPopover from '@/vue/components/common/tru-seo/HighlightPopover'

import {
	buildTinyMceCss,
	HIGHLIGHT_ACTIVE_CLASS
} from '@/vue/plugins/tru-seo/highlighter/tinymce'

import { buildTextStream } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { getVisibleAnchor, revealInEditor } from '@/vue/plugins/tru-seo/highlighter/spellingJump'

import {
	initTinymceEditor,
	highlightClassicEditor as highlightClassic
} from '@/vue/plugins/tru-seo/highlighter/classicEditor'

import {
	buildBlockFormatCss,
	getFormatClassName,
	getNativeBlockText,
	highlightBlockEditor as highlightBlock
} from '@/vue/plugins/tru-seo/highlighter/blockEditor'
import { getNativeBlockSkipped } from '@/vue/plugins/tru-seo/highlighter/blockText'

const SUSPENSION_SLOT_RELEASED = 'aioseo-truseo-suspension-slot-released'

/**
 * Composable that orchestrates TruSeo readability/SEO highlight
 * functionality across Classic Editor (TinyMCE) and Block Editor (Gutenberg).
 *
 * Editor-specific logic is delegated to:
 * - `highlighter/classicEditor.js` for TinyMCE annotation and DOM manipulation.
 * - `highlighter/blockEditor.js` for Gutenberg custom RichText format types.
 *
 * This composable manages the shared lifecycle: style injection, mark
 * creation, hover popover positioning, editor observation, and the reset cycle.
 *
 * @returns {{ watchHighlightSentences: Function }} Watcher callback for highlight data changes.
 */
export const useTruSeoHighlighter = () => {
	const editorObserver    = ref(null)
	const isIframed         = ref(false)
	// Resolved on call, never captured. `select()` answers undefined for a store that is not
	// registered yet, and this composable can be constructed before the block editor's own
	// scripts have run -- so a handle taken at setup could be undefined for the page's whole
	// lifetime, and every paint would throw on it.
	const selectBlockEditor = () => window?.wp?.data?.select('core/block-editor')
	const tinymceEditor              = ref(null)
	const injectedStyleEl            = ref(null)
	const registeredTinyMceAnnotators = new Set()
	// Per-parent text-stream cache: `Map<parentNode, {text, segments}>`.
	// Populated during `formatBlockContent` for the node path and consumed
	// by `annotateTinyMce` to resolve regex offsets to DOM ranges without
	// re-walking text nodes. Never `outerText`, which is CSS-aware, forces a
	// layout reflow, and hands back characters with no segment behind them.
	const streamCache                = new Map()
	// `adapter` is a `let` for the whole of syncAdapter()'s sake: the answer captured here can
	// be WRONG rather than merely absent, and reassigning the binding is what reaches all of
	// this closure's readers without every one of them re-probing.
	let adapter                       = resolveAdapter(),
		adapterGeneration             = getAdapterGeneration(),
		resetInProgress               = false,
		popoverPinned                 = false,
		clickOutsideHandler           = null,
		escapeKeyHandler              = null,
		repositionRafId               = null,
		revealWaitRafId               = null,
		wrapperPollIntervalId         = null,
		invalidationUnsubscribe       = null,
		invalidationDebounced         = null,
		// The highlight sentences this instance last reset for ({@see watchHighlightSentences}).
		lastSentences                 = null,
		// onMounted runs ONCE. Everything it wires sits behind the `enabled` gate, so an instance
		// that mounts gated off wires nothing -- and since a rebind can now open that gate, these
		// two are what let the wiring happen late instead of never.
		isMounted                     = false,
		mountedChannelsWired          = false,
		// The debounced suspension callback, owned here so a rebind can re-subscribe with the
		// same one. Null until watchEditorMode() has run, which the wrapper poll can defer for
		// seconds -- a rebind before that has nothing to re-take and must not invent one.
		suspensionSync                = null,
		isDisposed                    = false

	const truSeoHighlighterStore = useTruSeoHighlighterStore()

	// This instance's identity, compared against `editorModeOwner` in the store. The claim is
	// recorded THERE rather than here for the reason that broke the per-instance version: the
	// instance taking the slot over cannot reach into the closure of the one it took it from, so
	// a local mirror goes stale from that moment on and both believe they own it.
	const instanceToken = Symbol('aioseo-truseo-highlighter')

	// Whether this editor paints through the contract rather than a built-in paint module.
	// NOTE: an explicit opt-in, never `decorate` being present — every adapter declares one (classic's
	// is a stub), and a contract painter runs no MutationObserver, relying on onInvalidated alone.
	const usesAdapterPaint = () => !!adapter?.paintsViaContract && 'function' === typeof adapter.decorate

	// The canvas document a contract painter's marks live in (Elementor's preview iframe), so the
	// delegated mousedown reaches them. Contract painters only: classic keeps the main document.
	const getAdapterDocument = () => {
		if (!usesAdapterPaint()) {
			return null
		}

		try {
			const doc = adapter.getDocument?.()

			// nodeType, never instanceof: a canvas document belongs to another realm, where
			// `instanceof Document` is false against this window's constructor.
			return 9 === doc?.nodeType ? doc : null
		} catch (_e) {
			return null
		}
	}

	const getAdapterWindow = () => {
		if (!usesAdapterPaint()) {
			return null
		}

		try {
			const win = adapter.getWindow?.()

			// A Window's self-reference, for the same cross-realm reason the document test
			// reads nodeType. NOT `typeof win.addEventListener` — Document carries that too,
			// so an adapter answering its document here would be accepted and mostly work
			// (`document.getSelection()` exists), failing only where a real Window is needed.
			return !!win && win.window === win ? win : null
		} catch (_e) {
			return null
		}
	}

	const getEditorDocument = () => {
		if (isIframed.value) {
			return getEditorIframe()?.contentDocument || document
		}

		return getAdapterDocument() || document
	}

	const getEditorWindow = () => {
		if (isIframed.value) {
			return getEditorIframe()?.contentWindow || window
		}

		return getAdapterWindow() || window
	}

	// Keeps `isIframed` matching whether the block editor iframe (WP 6.3+) exists, in both
	// directions, since Gutenberg can remove and recreate it without this instance unmounting.
	// Returns true only on a flip into iframed mode, so the caller can inject the stylesheet.
	const syncIframeState = () => {
		const iframed = isIframedEditor() && !!getEditorIframe()?.contentDocument?.head

		if (iframed === isIframed.value) {
			return false
		}

		isIframed.value = iframed

		return iframed
	}

	// Every event of one click gesture, so an editor's own selection never sees a click on a mark.
	const SHIELDED_POINTER_EVENTS = [ 'pointerdown', 'mousedown', 'pointerup', 'mouseup', 'click' ]

	// Mark click delegation: one mousedown listener per editor document
	// (`Map<Document, {listener, capture}>`), not one per mark node. Block-editor marks
	// are re-rendered — and their DOM nodes replaced — by Gutenberg on every
	// analyzer toggle or re-analysis, which stranded per-node listeners on
	// detached nodes and made re-rendered marks unclickable.
	const delegatedListenerMap = new Map()
	const popoverPlacement = ref('above')
	const scrollListenerDocs = new Set()

	const getSourceForAnalyzer = (analyzer) => {
		return `${truSeoHighlighterStore.source}-${analyzer}`
	}

	// The prefix is IMPORTED, not restated. The markup projection recognises our own decoration by
	// the same prefix ({@see highlighter/textStream.js:isOwnDecoration}), so a second literal drifting
	// from this one would put the highlighter's own marks back into the analysed string.
	const isHighlightClass = (className) => {
		if (className.includes(truSeoHighlighterStore.source)) {
			return true
		}

		return className.startsWith(HIGHLIGHT_CLASS_PREFIX)
	}

	const injectHighlightStyles = () => {
		if (injectedStyleEl.value) {
			injectedStyleEl.value.remove()
			injectedStyleEl.value = null
		}

		// The iframe stylesheet (WP 7.0+ block editor) lives in a separate
		// document and is tracked by id, not a ref. Remove it here so it is
		// rebuilt from the current analyzer set on every call — otherwise an
		// analyzer toggled on after the sheet was first created never gets its
		// color rule and its <mark> falls back to the browser default (yellow).
		const editorDoc     = getEditorDocument()
		const iframeStyleId = 'aioseo-tru-seo-highlight-styles'
		if (editorDoc && editorDoc !== document) {
			editorDoc.getElementById(iframeStyleId)?.remove()
		}

		const analyzers = truSeoHighlighterStore.highlightAnalyzers
		if (!analyzers.length) {
			return
		}

		const rules = analyzers.map(analyzer => {
			const color = getAssessmentColor(analyzer)
			const source = getSourceForAnalyzer(analyzer)
			const className = getFormatClassName(analyzer)

			return [
				buildTinyMceCss(source, color),
				buildBlockFormatCss(className, color)
			].join('\n')
		}).join('\n')

		const style = document.createElement('style')
		style.setAttribute('data-aioseo-highlighter', 'true')
		style.textContent = rules
		document.head.appendChild(style)
		injectedStyleEl.value = style

		// When the editor is inside an iframe (WP 7.0+), marks live in the iframe's
		// document so the main-document stylesheet cannot reach them. Inject the
		// block-format rules into the iframe's head as well.
		if (editorDoc && editorDoc !== document && editorDoc.head) {
			const iframeStyle = editorDoc.createElement('style')
			iframeStyle.id = iframeStyleId
			iframeStyle.textContent = rules
			editorDoc.head.appendChild(iframeStyle)
		}
	}

	// Walks up from `startEl` to the innermost enclosing highlight mark and
	// returns its store entry. Nested marks (e.g. sentenceLength inside
	// paragraphLength) resolve to the innermost — what the cursor is actually
	// over. As a side effect it re-anchors the matched mark to the live clicked
	// element, healing a `mark.node` left stale by a Gutenberg re-render.
	const findMarkFromElement = (startEl, fallbackMark) => {
		let current = startEl
		while (current && 1 === current.nodeType) {
			const uid = current.dataset?.mceAnnotationUid
			if (uid) {
				const match = truSeoHighlighterStore.highlightMarks.find(m => String(m.id) === uid)
				if (match) {
					// Re-anchor to the element actually clicked: a Gutenberg
					// re-render may have replaced the node cached at build time.
					match.node = current

					return match
				}
			}

			if (current.id && current.classList) {
				for (const cls of current.classList) {
					if (isHighlightClass(cls)) {
						const match = truSeoHighlighterStore.highlightMarks.find(m => current.id.endsWith(`-${m.id}`))
						if (match) {
							match.node = current

							return match
						}
						break
					}
				}
			}

			current = current.parentElement
		}

		return fallbackMark
	}

	// Whether the mark is what the pointer sees at a point, rather than something drawn over it, such
	// as a fixed site header. The adapter names its own transparent click overlay.
	const isMarkTopmostAt = (doc, x, y, node) => {
		const top = doc.elementFromPoint?.(x, y)
		if (!top) {
			return true
		}

		return top === node || node.contains(top) || top.contains(node) || !!adapter?.isPointerOverlay?.(top, node)
	}

	// Resolves the mark painted under a point, innermost first, for editors whose marks never
	// receive the pointer themselves ({@see editors/divi5.js} `marksIgnorePointer`).
	const findMarkAtPoint = (doc, x, y) => {
		let best     = null,
			bestArea = Infinity

		for (const node of doc.querySelectorAll('mark')) {
			for (const rect of node.getClientRects()) {
				if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
					continue
				}

				// An outer mark is painted one fragment per text node, so the fragment around a nested
				// word is exactly as large as the word's own mark; the tie goes to the descendant.
				const area = rect.width * rect.height
				if (area < bestArea || (area === bestArea && best?.contains(node))) {
					best     = node
					bestArea = area
				}
			}
		}

		return best && isMarkTopmostAt(doc, x, y, best) ? findMarkFromElement(best, null) : null
	}

	// Resolves a mark's current on-screen node, re-querying the live editor
	// documents when the cached reference has been detached. Gutenberg replaces
	// a native block's highlight `<mark>` elements on every re-render (analyzer
	// toggle, re-analysis), leaving `mark.node` pointing at a detached node whose
	// `getBoundingClientRect()` is all zeros — which anchored the popover at the
	// top-left instead of the mark.
	const getLiveMarkNode = (mark) => {
		if (!mark) {
			return null
		}

		if (mark.node?.isConnected) {
			return mark.node
		}

		for (const doc of getListenerDocuments()) {
			const live = doc.querySelector(`[data-mce-annotation-uid="${mark.id}"]`) ||
				doc.querySelector(`[id$="-${mark.id}"]`)
			if (live) {
				mark.node = live

				return live
			}
		}

		return mark.node || null
	}

	const attachMarkClickListeners = () => {
		// Reached from deferred/async work (reset()'s nextTick() continuation,
		// the real blockEditor.js module's onMarksReady callback, and
		// observeMarkParent()'s one-shot MutationObserver) that can still be
		// pending when the component unmounts. Guarding here -- not just at
		// each caller -- stops every one of them from re-attaching the listener
		// this same onUnmounted just removed.
		if (isDisposed) {
			return
		}

		for (const doc of getListenerDocuments()) {
			if (delegatedListenerMap.has(doc)) {
				continue
			}

			// Clicking a mark toggles its popover: open (or switch to) the clicked
			// mark, or close it when it's already the open one. The click still
			// propagates to the editor so caret placement keeps working. The popover
			// anchors to the mark and follows it as the content scrolls.
			//
			// A single delegated listener resolves the mark from the click target at
			// click time, so it keeps working no matter how often Gutenberg replaces
			// the mark's DOM node. `findMarkFromElement` walks up from the target to
			// the innermost enclosing mark, so overlapping (nested) analyzer marks
			// resolve to the one the user is actually pointing at.
			// A builder pen that replaces the editing area's innerHTML (Elementor's startEditing)
			// would destroy the mark the click just opened, so the first click inspects and the
			// second edits. Capture phase on the document so it runs before the pen's handler; one
			// shot plus a timer, since a drag never produces the click.
			const suppressNextEditingClick = (doc) => {
				if (!usesAdapterPaint()) {
					return
				}

				// An adapter can decline, and Divi 4 does. Swallowing the click is only right for
				// an editor whose inline surface DESTROYS the mark and whose writes go somewhere
				// else (Elementor's widget settings). Divi 4 has no store: a module's own inline
				// editor is the write path, and it only becomes writable through the very click
				// this would swallow -- so suppressing it leaves every fix refusing. Painting into
				// that open editor is safe there because a serializer guard strips our marks from
				// anything it writes ({@see editors/divi4.js:ensureSerializerGuards}).
				if (false === adapter?.suppressesEditingClick) {
					return
				}

				const swallow = (event) => {
					doc.removeEventListener('click', swallow, true)
					event.stopPropagation()

					// Stopping it also starves the builder's own link guard (Elementor cancels
					// navigation from a bubble listener on this document), so a mark inside a
					// link would follow it.
					event.preventDefault()
				}

				// The adapter's own capture listener runs first, so it has to be told not to arm, or
				// it repaints for a pen that never opens and every highlight flashes.
				adapter?.suppressNextPenArm?.()

				doc.addEventListener('click', swallow, true)
				setTimeout(() => doc.removeEventListener('click', swallow, true), 500)
			}

			// A builder that re-renders the clicked module from its OWN handler orphans the node the
			// mark was resolved from before this listener ever runs, so the click reads as "not on
			// a mark" and nothing opens. Measured on Divi 4: it happens exactly when the module's
			// inline editor does not exist yet, because activating one re-renders the module --
			// click a mark on a fresh page and the first click is lost, the second works.
			//
			// Re-resolved from the POINTER POSITION rather than the dead node: once the re-render
			// lands, a freshly painted mark stands at the same point. Only for adapters that let
			// the builder act on this click; nothing else can be in this state.
			// Spread PAST the repaint, not just past the re-render: the fresh mark only exists once
			// this composable has repainted, which its own invalidation debounce puts 250 ms after
			// the builder's DOM settles. Measured with a 220 ms ceiling, every attempt landed on
			// bare text. The last attempt is the bound on how late a popover may still appear.
			const MARK_CLICK_RETRY_DELAYS = [ 0, 120, 300, 550, 850 ]

			const retryMarkClickAfterRerender = (doc, event) => {
				if (false !== adapter?.suppressesEditingClick) {
					return
				}

				const { clientX, clientY } = event

				for (const delay of MARK_CLICK_RETRY_DELAYS) {
					setTimeout(() => {
						// Another attempt already opened one, or the user moved on.
						if (isDisposed || null !== truSeoHighlighterStore.hoveredMarkId) {
							return
						}

						const element = doc.elementFromPoint?.(clientX, clientY)
						const target  = element ? findMarkFromElement(element, null) : null

						if (!target || !truSeoHighlighterStore.highlightMarks.some(hm => hm.id === target.id && hm.node)) {
							return
						}

						truSeoHighlighterStore.hoveredMarkId = target.id
						popoverPinned = true
						showPopover()
					}, delay)
				}
			}

			const down = (e) => {
				// Two composable instances (the always-present metabox and the
				// on-demand sidebar) each attach this delegated listener to the same
				// editor document, so both fire for one click. Since hoveredMarkId is
				// shared but popoverPinned is per-instance, the first sets the clicked
				// mark and opens it, and the second then reads that now-matching id,
				// sees its own popoverPinned, and closes it as a re-click. Claim the
				// event so only the first handler to resolve a mark acts on it.
				if (e.aioseoTruseoMarkClickHandled) {
					return
				}

				const target = findMarkFromElement(e.target, null) ||
					(adapter?.marksIgnorePointer ? findMarkAtPoint(doc, e.clientX, e.clientY) : null)
				if (!target) {
					retryMarkClickAfterRerender(doc, e)

					return
				}

				e.aioseoTruseoMarkClickHandled = true

				// The second click on the same mark: close the popover and let the click through,
				// so the editor does what it always did.
				if (popoverPinned && truSeoHighlighterStore.hoveredMarkId === target.id) {
					hidePopover()
					return
				}

				suppressNextEditingClick(doc)

				truSeoHighlighterStore.hoveredMarkId = target.id
				popoverPinned = true
				showPopover()
			}

			// An editor whose marks never take the pointer selects the module under them from its own
			// pointer handlers, which run before a bubble listener here. So the gesture that opens a
			// mark is taken in CAPTURE and kept from the editor; the close-on-click-outside handler
			// already ignores a point over a mark, so it cannot undo this. The second click on the
			// open mark is left to the editor, as `down` does.
			let shieldedUntil = 0

			const shield = (e) => {
				if ('pointerdown' === e.type) {
					const target = findMarkAtPoint(doc, e.clientX, e.clientY)

					shieldedUntil = target && !(popoverPinned && truSeoHighlighterStore.hoveredMarkId === target.id) ? Date.now() + 1000 : 0
				}

				if (Date.now() > shieldedUntil) {
					return
				}

				e.stopPropagation()

				if ('mousedown' === e.type) {
					down(e)
				}

				if ('click' === e.type) {
					shieldedUntil = 0
				}
			}

			const listener = adapter?.marksIgnorePointer ? shield : down
			const capture  = listener === shield

			for (const type of capture ? SHIELDED_POINTER_EVENTS : [ 'mousedown' ]) {
				// BUBBLE otherwise, deliberately. The close-on-click-outside handler is registered in
				// CAPTURE on these same documents, so a capture-phase listener here opens the popover
				// and has that handler close it again in the same dispatch -- measured on Divi 4,
				// where it looked exactly like the click never registering.
				doc.addEventListener(type, listener, capture)
			}

			delegatedListenerMap.set(doc, { listener, capture })
		}

		attachScrollListener()
	}

	/**
	 * Removes the delegated mark click listeners from every editor document.
	 *
	 * @returns {void}
	 */
	const detachMarkClickListeners = () => {
		for (const [ doc, { listener, capture } ] of delegatedListenerMap) {
			for (const type of capture ? SHIELDED_POINTER_EVENTS : [ 'mousedown' ]) {
				doc.removeEventListener(type, listener, capture)
			}
		}
		delegatedListenerMap.clear()

		detachScrollListener()
	}

	/**
	 * Repositions at most once per animation frame while scrolling, so the
	 * open popover stays glued to its mark without thrashing layout.
	 *
	 * @returns {void}
	 */
	const scheduleReposition = () => {
		if (repositionRafId) {
			return
		}

		repositionRafId = requestAnimationFrame(() => {
			repositionRafId = null

			if (isDisposed) {
				return
			}

			repositionHoverPopover()
		})
	}

	/**
	 * Keeps the open popover aligned with its mark as the editor content
	 * (or the page) scrolls. No-op while the popover is closed.
	 *
	 * @returns {void}
	 */
	const onEditorScroll = () => {
		if (popoverPinned && truSeoHighlighterStore.highlightPopover?.node) {
			scheduleReposition()
		}
	}

	// Captured on whole documents rather than on a guessed scroller: a scroll event never bubbles,
	// but every one passes through its document's capture phase, and the viewport's own scroll
	// targets the document. The guess picked the nearest `overflow-y: auto` ancestor, and a theme's
	// `body { overflow-x: hidden }` alone computes that on a body that never scrolls -- so after a
	// smooth jump the popover stayed hidden where the scroll began.
	const SCROLL_LISTENER_OPTIONS = { capture: true, passive: true }

	/**
	 * Listens for scroll anywhere in every document a mark can live in, so the popover can follow
	 * its mark.
	 *
	 * NOTE: a document no longer listed (a builder preview reload) is dropped here.
	 *
	 * @returns {void}
	 */
	const attachScrollListener = () => {
		// The same documents as the mark clicks: a classic TinyMCE canvas scrolls inside its own
		// iframe when the full-height editor is off, and that scroll never reaches the main one.
		const docs = getListenerDocuments()

		for (const doc of scrollListenerDocs) {
			if (!docs.includes(doc)) {
				doc.removeEventListener('scroll', onEditorScroll, SCROLL_LISTENER_OPTIONS)
				scrollListenerDocs.delete(doc)
			}
		}

		for (const doc of docs) {
			if (!scrollListenerDocs.has(doc)) {
				doc.addEventListener('scroll', onEditorScroll, SCROLL_LISTENER_OPTIONS)
				scrollListenerDocs.add(doc)
			}
		}
	}

	/**
	 * Removes the scroll listeners and cancels any pending reposition frame.
	 *
	 * @returns {void}
	 */
	const detachScrollListener = () => {
		if (repositionRafId) {
			cancelAnimationFrame(repositionRafId)
			repositionRafId = null
		}

		for (const doc of scrollListenerDocs) {
			doc.removeEventListener('scroll', onEditorScroll, SCROLL_LISTENER_OPTIONS)
		}
		scrollListenerDocs.clear()
	}

	/**
	 * Shows the popover for the currently clicked mark. Mounts the Vue app on
	 * first use, otherwise positions the existing instance and reveals it.
	 *
	 * @returns {void}
	 */
	const showPopover = () => {
		setActiveMark(truSeoHighlighterStore.hoveredMarkId)

		if (truSeoHighlighterStore.highlightPopover?.node) {
			revealPopover()

			return
		}

		mountPopoverApp()
	}

	/**
	 * Positions the already-mounted popover for the active mark while invisible
	 * (avoiding a flash at the previous spot), reveals it, and re-arms the
	 * outside-click / Escape handlers. Used both to open the popover and to
	 * re-reveal it after an advance-to-next-word fix hid it.
	 *
	 * @returns {void}
	 */
	const revealPopover = () => {
		const node = truSeoHighlighterStore.highlightPopover?.node
		if (!node) {
			return
		}

		repositionHoverPopover()
		node.style.visibility = 'visible'
		node.style.pointerEvents = 'auto'

		attachClickOutsideHandler(node)
		attachEscapeKeyHandler()
	}

	/**
	 * Returns the set of documents that should receive global listeners
	 * (click-outside, Escape, and the delegated mark click). Covers the main
	 * document, whatever `getEditorDocument()` resolves — the iframed block
	 * editor's `editor-canvas` document (WP 6.3+), or a contract painter's own
	 * canvas — and every active TinyMCE editor's iframe document: Classic Editor
	 * (`content_ifr`) and Classic blocks inside the Block Editor (`editor-*_ifr`)
	 * both render inside their own iframes, so events there never reach the main
	 * document.
	 *
	 * Re-resolved on every call rather than cached, because a builder can replace
	 * its canvas document mid-session (Elementor's `reloadPreview()`); the
	 * delegated listener map is keyed by document, so the fresh one is picked up
	 * on the next paint.
	 *
	 * @returns {Document[]} Documents to attach listeners to.
	 */
	const getListenerDocuments = () => {
		const docs = [ document ]

		const editorDoc = getEditorDocument()
		if (editorDoc && editorDoc !== document) {
			docs.push(editorDoc)
		}

		const tinymceEditors = window?.tinymce?.editors
		if (tinymceEditors) {
			for (const editor of tinymceEditors) {
				try {
					const tinyDoc = editor.getDoc()
					if (tinyDoc && !docs.includes(tinyDoc)) {
						docs.push(tinyDoc)
					}
				} catch (_e) {
					// Editor not yet ready or already destroyed.
				}
			}
		}

		return docs
	}

	/**
	 * Toggles the active-mark class so the specific mark whose popover is open
	 * stands out from adjacent same-analyzer marks. Applies to every DOM node
	 * of the mark (marks can split across formatting boundaries) in each editor
	 * document. Pass null to clear.
	 *
	 * @param {(string|number|null)} markId The active mark's id, or null.
	 * @returns {void}
	 */
	const setActiveMark = (markId) => {
		for (const doc of getListenerDocuments()) {
			for (const node of doc.querySelectorAll(`.${HIGHLIGHT_ACTIVE_CLASS}`)) {
				node.classList.remove(HIGHLIGHT_ACTIVE_CLASS)
			}

			if (null === markId || undefined === markId) {
				continue
			}

			for (const node of doc.querySelectorAll('mark, span.annotation-text')) {
				if ((node.id && node.id.endsWith(`-${markId}`)) || node.dataset?.mceAnnotationUid === String(markId)) {
					node.classList.add(HIGHLIGHT_ACTIVE_CLASS)
				}
			}
		}
	}

	/**
	 * Hides the popover without destroying it and clears hovered mark state.
	 * Uses visibility instead of display so the element stays measurable
	 * for positioning before the next show.
	 *
	 * @returns {void}
	 */
	const hidePopover = () => {
		popoverPinned = false
		setActiveMark(null)
		truSeoHighlighterStore.hoveredMarkId = null

		const docs = getListenerDocuments()

		if (clickOutsideHandler) {
			for (const d of docs) {
				d.removeEventListener('mousedown', clickOutsideHandler, true)
			}
			clickOutsideHandler = null
		}

		if (escapeKeyHandler) {
			for (const d of docs) {
				d.removeEventListener('keydown', escapeKeyHandler, true)
			}
			escapeKeyHandler = null
		}

		if (truSeoHighlighterStore.highlightPopover?.node) {
			truSeoHighlighterStore.highlightPopover.node.style.visibility = 'hidden'
			truSeoHighlighterStore.highlightPopover.node.style.pointerEvents = 'none'
		}
	}

	/**
	 * Attaches mousedown listeners on the main document (and the editor
	 * document when iframed) that close the popover when the user clicks
	 * outside it. Clicks on a highlight mark are treated as "inside" —
	 * those pin the popover via the mark's own mousedown listener and
	 * must not trigger a hide here.
	 *
	 * @param {HTMLElement} node The popover root node.
	 * @returns {void}
	 */
	const attachClickOutsideHandler = (node) => {
		const docs = getListenerDocuments()

		if (clickOutsideHandler) {
			for (const d of docs) {
				d.removeEventListener('mousedown', clickOutsideHandler, true)
			}
		}

		clickOutsideHandler = (e) => {
			if (node.contains(e.target)) {
				return
			}

			// nodeType is a numeric value, so it works across realms — `instanceof
			// Element` would fail for click targets dispatched inside iframed
			// editor documents (different realm = different Element constructor).
			let el = (e.target && 1 === e.target.nodeType) ? e.target : null
			while (el) {
				const classList = el.classList
				if (classList) {
					for (const c of classList) {
						if (isHighlightClass(c)) {
							return
						}
					}
				}
				el = el.parentElement
			}

			// Such an editor never makes the mark the target, so the point decides whether it was clicked.
			const doc = e.target?.ownerDocument
			if (adapter?.marksIgnorePointer && doc && findMarkAtPoint(doc, e.clientX, e.clientY)) {
				return
			}

			// Explicit user dismissal — drop any queued advance-to-next-word so it
			// doesn't reopen after the rebuild finishes.
			truSeoHighlighterStore.cancelAdvance()
			hidePopover()
		}

		for (const d of docs) {
			d.addEventListener('mousedown', clickOutsideHandler, true)
		}
	}

	/**
	 * Attaches a keydown listener that closes the popover on Escape. Covers
	 * both the main document and the editor document (when iframed) so the
	 * keypress is observed regardless of focus location.
	 *
	 * @returns {void}
	 */
	const attachEscapeKeyHandler = () => {
		const docs = getListenerDocuments()

		if (escapeKeyHandler) {
			for (const d of docs) {
				d.removeEventListener('keydown', escapeKeyHandler, true)
			}
		}

		escapeKeyHandler = (e) => {
			if ('Escape' === e.key) {
				truSeoHighlighterStore.cancelAdvance()
				hidePopover()
			}
		}

		for (const d of docs) {
			d.addEventListener('keydown', escapeKeyHandler, true)
		}
	}

	/**
	 * Whether the wheel target sits inside a vertically scrollable region of the
	 * popover (e.g. the suggestions list), so native scroll should handle it.
	 *
	 * @param   {HTMLElement} popoverNode The popover container node.
	 * @param   {EventTarget} target      The wheel event target.
	 * @returns {boolean}                 True when a scrollable region is under the cursor.
	 */
	const isOverScrollableRegion = (popoverNode, target) => {
		let el = (target && 1 === target.nodeType) ? target : null
		while (el) {
			const overflowY = window.getComputedStyle(el).overflowY
			if (('auto' === overflowY || 'scroll' === overflowY) && el.scrollHeight > el.clientHeight) {
				return true
			}

			if (el === popoverNode) {
				break
			}
			el = el.parentElement
		}

		return false
	}

	/**
	 * Whether marks are measured in a different rect space than the popover's own —
	 * i.e. they live in an editor iframe (classic's TinyMCE body, the block editor's
	 * canvas). A non-zero offset is how an adapter says so.
	 *
	 * @returns {boolean} True when the popover is layered over an iframe.
	 */
	const hasMarkRectOffset = () => {
		const offset = adapter?.getMarkRectOffset?.() || { top: 0, left: 0 }

		return 0 !== offset.top || 0 !== offset.left
	}

	/**
	 * Creates the popover Vue app and mounts it to the DOM.
	 *
	 * @returns {boolean} False if editor wrapper is not found.
	 */
	const mountPopoverApp = () => {
		truSeoHighlighterStore.clearHighlightPopover()
		truSeoHighlighterStore.sanitizeHighlightMarks()

		const app = createApp({
			name : 'TruSeoHighlightPopover',
			render () {
				return h(
					TruSeoHighlightPopover,
					{
						placement : popoverPlacement.value
					}
				)
			}
		})

		const node = createHighlightPopoverNode()
		node.classList.add('aioseo-highlight-popover-root')

		const editorWrapper = getEditorNode('wrapper')
		if (!editorWrapper) {
			return false
		}

		adapter?.getPopoverHost?.()?.appendChild(node)

		// Claimed before mounting: mounting flushes the other instance's pending watchers, and one
		// that finds the slot still empty mounts a second popover nothing ever removes.
		truSeoHighlighterStore.highlightPopover.app  = app
		truSeoHighlighterStore.highlightPopover.node = node

		app.mount(node)

		attachClickOutsideHandler(node)
		attachEscapeKeyHandler()

		if (hasMarkRectOffset()) {
			// The popover is mounted in the main document, layered over the editor.
			// Trap wheel events so they scroll the popover's own scrollable region
			// (the suggestions list) rather than the editor/page behind it. When
			// nothing inside the popover can scroll, swallow the event entirely.
			node.addEventListener('wheel', (e) => {
				if (isOverScrollableRegion(node, e.target)) {
					return
				}
				e.preventDefault()
			}, { passive: false })
		}

		repositionHoverPopover()
	}

	const formatBlockContent = ({ block, node }) => {
		try {
			let content = ''
			if (node) {
				// Build a canonical text stream from the node's text nodes
				// in document order, plus one `\n` per block-level child
				// ({@see buildTextStream}). Every character is covered by a
				// segment, so a regex offset into `text` resolves to a DOM
				// range through the cached `segments` — which is what
				// `outerText` cannot offer for the `\n`s it inserts, on top
				// of being CSS-aware and forcing a reflow.
				const stream = buildTextStream(node)
				streamCache.set(node, stream)
				content = stream.text
			} else if (block) {
				// Shared with the re-anchoring pass so a rebuilt range lands in the
				// same coordinate space the original one was measured in.
				content = getNativeBlockText(block)
			}

			return normalizeWhitespaces(content)
		} catch (_e) {
			return ''
		}
	}

	const getEditorNode = (which) => {
		if ('wrapper' === which) {
			return adapter?.getRootNode?.()
		}

		if ('first-block' === which) {
			// Prefer this composable's own TinyMCE handle, the instance that was painted; an adapter
			// resolves TinyMCE live and can answer with a newer one.
			if (tinymceEditor.value) {
				return tinymceEditor.value.getBody()?.firstElementChild || {}
			}

			return adapter?.getFirstUnitNode?.()
		}
	}

	const highlightClassicEditor = () => {
		highlightClassic({
			editor               : tinymceEditor.value,
			store                : truSeoHighlighterStore,
			sourcePrefix         : truSeoHighlighterStore.source,
			registeredAnnotators : registeredTinyMceAnnotators,
			setHighlightMarks,
			streamCache
		})

		// `annotateTinyMce` inserts each span synchronously (`surroundContents`),
		// so every mark's node exists the moment the pass above returns — no
		// MutationObserver needed to discover them. Associate each mark with its
		// span and (re)attach the click listeners here. reset() runs this on
		// every re-scan after clearing the old listener map, so re-annotated
		// nodes always get fresh listeners.
		const body = tinymceEditor.value?.getBody()
		if (body) {
			for (const hm of truSeoHighlighterStore.highlightMarks) {
				const span = body.querySelector(`[data-mce-annotation-uid="${hm.id}"]`)
				if (span) {
					hm.node = span
				}
			}
		}

		attachMarkClickListeners()
	}

	// Paint for adapters that decorate their own canvas; block and classic keep their own paths.
	//
	// Marks come from the adapter's own content units, whose `html` is the coordinate space
	// its `decorate` aligns against — the same source `getContent()` analyses, so a sentence
	// the analyzer found is measured against the string it was found in.
	const highlightAdapterEditor = () => {
		for (const unit of (adapter?.getContentUnits?.() || [])) {
			// Sentence keys go to the first unit that matches, so a skipped unit must not claim one.
			if (!isMarkableUnit(unit)) {
				continue
			}

			setHighlightMarks({ block: null, node: null, unit })
		}

		// Once, with the whole set: `decorate` is document-scoped, and calling it per unit would
		// have each call clear the previous unit's paint.
		adapter.decorate(truSeoHighlighterStore.highlightMarks)

		// The adapter wraps every mark synchronously and assigns `mark.node`, so there is nothing to
		// poll for.
		attachMarkClickListeners()
	}

	// The invalidation arm: repaint what is already in the store, never rebuild it, because
	// `getContentUnits()` is quadratic in element count.
	// NOTE: marks whose text a re-render changed are dropped by `decorate` and return with the
	// next analysis.
	const repaintDecorations = () => {
		// A reset already clears and repaints, and it owns the store while it runs.
		if (isDisposed || resetInProgress || !usesAdapterPaint()) {
			return
		}

		try {
			adapter.clearDecorations?.()
			adapter.decorate(truSeoHighlighterStore.highlightMarks)
			attachMarkClickListeners()

			// The paint replaced every mark node, so an open popover is anchored to a box that
			// no longer exists. Unconditional because every close path nulls `hoveredMarkId`
			// (`hidePopover`, `clearAll`, `dismissAfterFix`) and this returns early without one
			// — so it cannot re-reveal a popover the user closed.
			repositionHoverPopover()
		} catch (error) {
			// Same reasoning as reset()'s catch: an editor exception must not poison the paint
			// path for the rest of the session.
			console.warn('[AIOSEO TruSEO highlighter] repaint failed:', error)
		}
	}

	// Content invalidation, for adapter-painted editors only: a builder wipes its marks on every
	// element re-render. Debounced here because an adapter can fire once per element rendered.
	// Self-releasing so a rebind can re-take it, and wrapped because reset() reaches it before its
	// own try.
	const subscribeInvalidation = () => {
		try {
			invalidationDebounced?.cancel?.()
			invalidationDebounced = null

			if (invalidationUnsubscribe) {
				invalidationUnsubscribe()
				invalidationUnsubscribe = null
			}

			if (usesAdapterPaint()) {
				invalidationDebounced   = debounce(repaintDecorations, 250)
				invalidationUnsubscribe = adapter.onInvalidated?.(invalidationDebounced) || null
			}
		} catch (_e) {
			// Best-effort: a repaint channel that cannot be taken must not cost us the paint.
		}
	}

	// The suspension channel's counterpart. The handle lives in the store's shared slot, so this
	// releases before assigning; wrapped because reset() reaches it before its own try.
	const releaseSuspensionSlot = () => {
		try {
			truSeoHighlighterStore.editorModeUnsubscribe?.()
		} catch (_e) {
			// A subscription whose editor is already gone must not cost us the paint.
		}

		truSeoHighlighterStore.editorModeUnsubscribe = null
		truSeoHighlighterStore.editorModeOwner       = null
	}

	const subscribeSuspension = () => {
		// Null until watchEditorMode() has run, which the wrapper poll can defer for seconds --
		// a rebind before that has nothing to re-take and must not invent a subscription.
		if (!suspensionSync) {
			return
		}

		// A disabled instance must never hold it. Belt to wireMountedChannels' braces, and the
		// one that matters for the wrapper poll, which can fire after the gate has closed.
		if (!truSeoHighlighterStore.enabled) {
			return
		}

		// Only the holder may re-take. A rebind reaches every live instance, and without this the
		// last one to run would win the slot regardless of who handed it over.
		const owner = truSeoHighlighterStore.editorModeOwner
		if (owner && owner !== instanceToken) {
			return
		}

		// `suspensionSync`'s own pending debounce is deliberately NOT cancelled, unlike the
		// invalidation one: it is the same debounce instance across re-subscribes, and
		// syncHighlightingAllowed() recomputes from live state, so a trailing call is a redundant
		// recompute rather than a stale one.
		releaseSuspensionSlot()

		try {
			truSeoHighlighterStore.editorModeUnsubscribe = adapter?.onSuspensionChange?.(suspensionSync) || null
			truSeoHighlighterStore.editorModeOwner       = truSeoHighlighterStore.editorModeUnsubscribe ? instanceToken : null
		} catch (_e) {
			// Same reason as subscribeInvalidation's: this runs ahead of reset()'s own try.
		}
	}

	// Whether a highlightable editor owns this page. A builder without its own adapter runs over a
	// block/classic editor the user isn't looking at, so only the builder's own adapter qualifies.
	// Coerced to a boolean because `enabled` is a v-if.
	const isHighlightableEditor = () => !!adapter &&
		(!isPageBuilderEditor() || !!(adapter.ownsPageBuilder && !adapter.analyzeOnly))

	// Points `enabled` at whether the user can highlight right now, gated on capability alone
	// (`isSupported`, default true). isHighlightableEditor() must run first: it short-circuits
	// before `adapter` is dereferenced.
	const syncHighlightingEnabled = () => {
		truSeoHighlighterStore.enabled = isHighlightableEditor() && (adapter.isSupported?.() ?? true)

		return truSeoHighlighterStore.enabled
	}

	// Re-reads the adapter when the memoised answer was invalidated after this instance captured it
	// ({@see editors/index.js:resetAdapter}): an early read can resolve the editor underneath a
	// builder whose globals haven't loaded yet.
	// NOTE: never short-circuit on an unchanged adapter; `enabled` and the subscriptions are
	// re-derived here too.
	const syncAdapter = () => {
		if (adapterGeneration === getAdapterGeneration()) {
			return false
		}

		adapterGeneration = getAdapterGeneration()
		adapter           = resolveAdapter()

		// Documented idempotent on every adapter, so the initial call in onBeforeMount and this
		// one cannot conflict.
		try {
			adapter?.init?.()
		} catch (_e) {
			// Registration is best-effort; a failure must not stop the rebind.
		}

		syncHighlightingEnabled()

		// Not "has onMounted run" -- that is what left a newly-enabled instance hollow. This asks
		// the question that actually decides it: is this instance mounted and enabled NOW.
		//
		// BOTH channels go through it, and nothing subscribes outside it. An earlier shape shed the
		// suspension slot inside it and re-took it on the next line, which made the disable
		// direction a no-op while the enable direction worked.
		wireMountedChannels()

		return true
	}

	const highlightBlockEditor = () => {
		highlightBlock({
			blocks               : selectBlockEditor()?.getBlocks() || [],
			store                : truSeoHighlighterStore,
			sourcePrefix         : truSeoHighlighterStore.source,
			registeredAnnotators : registeredTinyMceAnnotators,
			setHighlightMarks,
			observeMarkParent,
			onMarksReady         : attachMarkClickListeners,
			streamCache
		})
	}

	const listenWindowCopy = (event) => {
		const editorWin = getEditorWindow()
		const modifyData = () => {
			event.preventDefault()
			event.clipboardData.setData('text/html', editorWin.getSelection().toString())
		}
		const selection = editorWin.getSelection() || {}
		if (!selection?.rangeCount) {
			return false
		}

		const text = event.clipboardData?.getData('text/html') || ''
		if (text && (text.includes(truSeoHighlighterStore.source) || text.includes(HIGHLIGHT_CLASS_PREFIX))) {
			modifyData()

			return false
		}

		const range = selection.getRangeAt(0) || {}
		for (const child of Object.values(range?.cloneContents()?.children || [])) {
			if (Object.values(child?.classList || []).some(c => isHighlightClass(c))) {
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

		const selection = getEditorWindow().getSelection() || null
		if (!selection?.toString()) {
			return false
		}

		const parent = selection?.anchorNode?.parentElement || null
		const sibling = selection?.anchorNode?.nextElementSibling || null
		if (Object.values(parent?.classList || []).some(c => isHighlightClass(c))) {
			modifySelection(selection, parent)
		} else if (Object.values(sibling?.classList || []).some(c => isHighlightClass(c))) {
			modifySelection(selection, sibling)
		}
	}

	const observeEditor = () => {
		// Not for a contract painter: this callback is an undebounced full reset(), quadratic in
		// element count. The editor's own signals already cover it — `onInvalidated` for the
		// repaint (render and destroy events) and `document/save/set-is-modified` for the rebuild.
		if (usesAdapterPaint()) {
			return false
		}

		editorObserver.value = new MutationObserver((list) => {
			// `onUnmounted` disconnects this observer, but a mutation record
			// already queued by the browser before that call can still be
			// delivered afterward -- the guard is belt, the disconnect is braces.
			if (isDisposed || resetInProgress) {
				return
			}

			let shouldReset = false

			for (const mutation of list) {
				for (const removedNode of (mutation?.removedNodes || [])) {
					// Only reset when a content parent node is removed (user edit),
					// not when our own annotation spans are removed (TinyMCE normalization).
					if (
						truSeoHighlighterStore.highlightMarks.some(hm => removedNode.isEqualNode(hm.parent))
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

	/**
	 * Watches a highlight mark's parent element for DOM mutations.
	 * When annotation spans are added or modified, associates the
	 * resulting DOM node with its highlight mark via `setHighlightMarkNode`,
	 * which attaches the mark's click listener as its node arrives.
	 *
	 * @param {HTMLElement} parent The parent element to observe.
	 * @returns {void}
	 */
	const observeMarkParent = (parent) => {
		const mutationCallback = (list, obs) => {
			obs.disconnect()

			// A one-shot observer that can still be waiting on its first
			// mutation when the component unmounts; a later, unrelated mutation
			// on the same parent must not resume work for a torn-down instance.
			if (isDisposed) {
				return
			}

			for (const mutation of list) {
				if (mutation?.target?.classList.contains('is-hovered')) {
					break
				}

				if (Object.values(mutation?.target?.classList || []).some(c => isHighlightClass(c))) {
					setHighlightMarkNode(mutation.target)
				}

				// Also walk addedNodes — when a mark is wrapped INSIDE an
				// existing annotation span (nested marks like sentenceLength
				// within paragraphLength), the mutation's target is the outer
				// span and the new inner span is in addedNodes. Without this,
				// the inner mark never gets its `hm.node` set, no hover
				// listener is attached, and hovering it shows the outer mark.
				for (const addedNode of (mutation?.addedNodes || [])) {
					if (Object.values(addedNode?.classList || []).some(c => isHighlightClass(c))) {
						setHighlightMarkNode(addedNode)
					}
				}
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

	// Whether highlighting is currently allowed, from the editor's visibility
	// and mode. Pure read, evaluated on demand rather than per DOM mutation: the
	// computed-style walk below is too slow to run in a mutation callback.
	const evaluateHighlightingAllowed = () => {
		const editorWrapper = getEditorNode('wrapper')
		if (!editorWrapper) {
			return false
		}

		// An ancestor with `display: none` means the editor is hidden.
		const hiddenEditorParent = getClosestNodeByPropertyValue({
			element  : editorWrapper.parentElement,
			property : 'display',
			value    : 'none'
		})
		const editorDocElement = (editorWrapper.ownerDocument || document).documentElement
		if (!hiddenEditorParent?.isEqualNode(editorDocElement)) {
			return false
		}

		// Retained over the classic adapter's own isSuspended(), which reads
		// #wp-content-wrap's `html-active` class — a different signal than the raw
		// textarea's visibility. The branch also seeds the TinyMCE handle the
		// classic paint path consumes, and that has no capability channel.
		if ('classic-editor' === adapter?.id) {
			setTinymceEditor()

			// The user switched to the "Text" tab (the raw textarea is visible).
			if (
				'TEXTAREA' === document.getElementById('content')?.nodeName &&
				'none' !== document.getElementById('content').style.display
			) {
				return false
			}
		}

		// Whole-editor mode: the block editor's "Code editor", classic's "Text" tab.
		if (adapter?.isSuspended?.()) {
			return false
		}

		// Per-element suspension: a single block switched to "Edit as HTML" is as
		// unsafe to highlight as the whole-editor code view, and no adapter's
		// isSuspended() covers it yet ({@see EditorAdapter}). Reads as "no block in
		// HTML mode" wherever the block-editor store isn't live.
		const blockEditorStore = selectBlockEditor()
		if ('html' === blockEditorStore?.getBlockMode?.(blockEditorStore?.getSelectedBlock?.()?.clientId)) {
			return false
		}

		return true
	}

	// Applies the current allow/disallow state. Guarded so unrelated editor
	// activity (typing, selection) never re-toggles the analyzer store. Suspends
	// on switching away from a paintable editor (e.g. the Code Editor) and resumes
	// — restoring the prior selection — on switching back.
	const syncHighlightingAllowed = () => {
		const allowed = evaluateHighlightingAllowed()
		if (allowed === truSeoHighlighterStore.allowHighlighting) {
			return
		}

		if (allowed) {
			truSeoHighlighterStore.resumeHighlighting()
		} else {
			truSeoHighlighterStore.suspendHighlighting()
		}
	}

	// Enables/disables highlighting when the editor's mode or visibility
	// changes. Replaces a broad #wpbody-content attribute observer — which also
	// woke on the popover's own style mutations — with targeted signals: the
	// Block Editor's data store, and the Classic editor's single
	// `tmce-active`/`html-active` class toggle on #wp-content-wrap.
	const watchEditorMode = () => {
		// Debounced before it ever reaches the adapter: onSuspensionChange subscribes
		// with whatever it is given, and the block editor's signal is every wp.data
		// dispatch.
		suspensionSync = debounce(syncHighlightingAllowed, 250)

		subscribeSuspension()

		syncHighlightingAllowed()
	}

	/**
	 * Positions the popover anchored to its mark's current on-screen box, so it
	 * follows the mark as the content scrolls. Prefers below the mark; flips
	 * above when there isn't enough room below. Clamped to the editor's visible
	 * content region and hidden while the mark scrolls out of it.
	 *
	 * @returns {boolean} False if no hovered mark node or popover node.
	 */
	const repositionHoverPopover = () => {
		const markNode = getLiveMarkNode(truSeoHighlighterStore.hoveredMark)
		if (!markNode || !truSeoHighlighterStore.highlightPopover.node) {
			return false
		}

		const markRect = getVisibleAnchor(markNode).getBoundingClientRect()
		const popoverNode = truSeoHighlighterStore.highlightPopover.node
		const popoverHeight = popoverNode.offsetHeight || 80
		const popoverWidth = popoverNode.offsetWidth || 320
		const gap = 8

		// A mark measured inside an editor frame (the block editor's canvas, classic's
		// TinyMCE body) is only positionable while that frame is still in the main
		// document. It can be removed out from under a live, still-mounted instance —
		// device-preview switch, code-editor toggle, distraction-free mode, a TinyMCE
		// re-init — and `getMarkRectOffset()` answers {0, 0} for a vanished frame just
		// as it does for "no offset needed", so the missing frame has to be spotted
		// here. Hide rather than paint at a stale position.
		const markDocument = markNode.ownerDocument
		if (markDocument && markDocument !== document && !markDocument.defaultView?.frameElement?.isConnected) {
			popoverNode.style.visibility = 'hidden'

			return
		}

		// The popover is position:fixed in the main document, so anchor it to the
		// mark's main-viewport box: the offset lifts marks out of their frame's
		// coordinate space, and is {0, 0} for marks already in the main document.
		const markOffset = adapter?.getMarkRectOffset?.() || { top: 0, left: 0 }

		const anchorTop     = markOffset.top + markRect.top
		const anchorBottom  = markOffset.top + markRect.bottom
		const anchorCenterX = markOffset.left + markRect.left + (markRect.width / 2)
		let placement

		// Constrain the popover to the editor's visible content region so it can't
		// paint over the admin bar/header above or the meta boxes below.
		const bounds = adapter?.getViewportBounds?.() || { top: 0, bottom: window.innerHeight }

		// Hide (but keep mounted) while the anchored mark is scrolled out of the
		// safe region — a popover pinned to the edge with no mark in view reads as
		// broken. It reappears when the mark scrolls back in. A frame that scrolls on its
		// own (classic's TinyMCE body when not full-height) clips the mark while it is still
		// inside the safe region, so the frame's own viewport counts too.
		const frameHeight    = markDocument && markDocument !== document ? markDocument.defaultView?.innerHeight : null
		const clippedByFrame = !!frameHeight && (0 >= markRect.bottom || markRect.top >= frameHeight)
		if (clippedByFrame || anchorBottom <= bounds.top || anchorTop >= bounds.bottom) {
			popoverNode.style.visibility = 'hidden'

			return
		}
		popoverNode.style.visibility = ''

		// Prefer below the mark's last line so we don't cover the text; flip above
		// when there isn't enough room below, then fall back to the roomier side.
		const spaceBelow = bounds.bottom - anchorBottom - gap
		const spaceAbove = anchorTop - bounds.top - gap

		if (spaceBelow >= popoverHeight) {
			placement = 'below'
		} else if (spaceAbove >= popoverHeight) {
			placement = 'above'
		} else {
			placement = spaceBelow >= spaceAbove ? 'below' : 'above'
		}

		popoverPlacement.value = placement
		popoverNode.dataset.popoverPlacement = placement

		const rawTop = 'below' === placement
			? anchorBottom + gap
			: anchorTop - popoverHeight - gap

		// Keep the whole popover inside the safe region.
		const top = Math.max(bounds.top, Math.min(rawTop, bounds.bottom - popoverHeight))

		const left = Math.max(0, Math.min(anchorCenterX - (popoverWidth / 2), window.innerWidth - popoverWidth))

		popoverNode.style.top = top + 'px'
		popoverNode.style.left = left + 'px'
		popoverNode.style.transform = 'translate(0, 0)'
	}

	/**
	 * Main reset cycle: clears all highlights, re-injects styles, and
	 * re-applies highlights for the active editor. For TinyMCE editors,
	 * suppresses events during the entire clear+apply cycle to prevent
	 * WordPress handlers from triggering editor re-initialization.
	 *
	 * Uses `resetInProgress` guard + deferred unlock (nextTick + rAF)
	 * to prevent cascading resets from DOM normalization.
	 *
	 * @returns {void}
	 */
	const reset = () => {
		// A torn-down instance's own leftover subscriptions/observers (e.g. a
		// queued mutation record) can still call this after unmount.
		if (isDisposed || resetInProgress) {
			return
		}

		// AFTER the two guards above and before the id test below. Above them it would rebind
		// under a paint already in flight -- that pass captured its `beginPaint` token from the
		// previous adapter and would hand it back to the new one in its `finally` -- and it would
		// mutate a torn-down instance's state.
		syncAdapter()

		// TinyMCE boots asynchronously. If an upstream watcher (e.g. the
		// analyzer's `watchHighlightSentences`) fires before the editor
		// ref is populated, bail out *before* claiming `resetInProgress`.
		// The `tinymceEditor` watcher below re-invokes `reset()` once the
		// ref transitions to non-null, so no work is lost. Without this
		// guard, `highlightClassicEditor` would throw on `editor.getBody()`
		// and leave `resetInProgress` stuck — blocking every later reset.
		if ('classic-editor' === adapter?.id && !tinymceEditor.value) {
			return
		}

		// A second composable instance (the sidebar app mounts when the panel is
		// first opened) runs its first reset before the async detection interval in
		// `onMounted` has flipped `isIframed`. Resolve it synchronously here so the
		// paint targets the editor iframe — otherwise mark parents resolve against
		// the main document, come back null, and no click listeners attach, leaving
		// the highlights (and their AI-suggestion popover) dead to clicks until reload.
		syncIframeState()

		resetInProgress = true

		const editorWin = getEditorWindow()
		editorWin.removeEventListener('copy', listenWindowCopy)
		editorWin.removeEventListener('keyup', listenWindowKeyup)

		detachMarkClickListeners()
		editorObserver.value?.disconnect()

		nextTick().then(() => {
			// This continuation is a plain microtask, not bound to the component
			// instance -- unmount can land after the synchronous prefix above
			// (which already detached listeners) but before this resolves. Left
			// unguarded, it would re-attach the very listeners onUnmounted just
			// removed (via highlightClassicEditor()/highlightBlockEditor() ->
			// attachMarkClickListeners()).
			if (isDisposed) {
				return
			}

			// Hands over the handle this pass is about to paint with, for the same
			// reason getEditorNode('first-block') prefers it: an adapter that
			// resolves its target live can answer with a newer instance than the
			// one being painted, leaving the painted instance's events unsuppressed
			// and restoring a different instance's on the way out.
			const paintToken = adapter?.beginPaint?.(tinymceEditor.value)

			try {
				truSeoHighlighterStore.clearAll()

				// An adapter that paints its own DOM has to clear it itself. Gated so the block editor
				// isn't sent a second clearAll.
				if (usesAdapterPaint()) {
					adapter.clearDecorations?.()
				}

				// The cache references DOM nodes whose text content (and even
				// existence) may change between resets. Drop it here so the
				// upcoming `setHighlightMarks` pass rebuilds fresh streams.
				streamCache.clear()

				if (!truSeoHighlighterStore.highlightSentences) {
					// No sentences to render right now (e.g. all analyzers
					// unchecked). Bail out without disabling the master toggle —
					// the user controls that flag exclusively from the UI.
					return
				}

				injectHighlightStyles()

				// Mutually exclusive by construction — `adapter.id` is one string — but written
				// as a chain so the capability arm cannot also run for block or classic, whose
				// paints already own their DOM.
				if ('block-editor' === adapter?.id) {
					highlightBlockEditor()
				} else if ('classic-editor' === adapter?.id) {
					highlightClassicEditor()
				} else if (usesAdapterPaint()) {
					highlightAdapterEditor()
				}

				observeEditor()

				const editorWinInner = getEditorWindow()
				editorWinInner.addEventListener('copy', listenWindowCopy)
				editorWinInner.addEventListener('keyup', listenWindowKeyup)
			} catch (error) {
				// Never let an editor exception poison the global lock —
				// that is what produced the silent "highlighter dead for the
				// rest of the session" bug on slow Gutenberg boots. Surfacing
				// the warning gives us breadcrumbs without breaking the user.
				console.warn('[AIOSEO TruSEO highlighter] reset failed:', error)
			} finally {
				adapter?.endPaint?.(paintToken)

				// Wait for Vue flush + browser paint before allowing the observer to react,
				// preventing async TinyMCE DOM normalization from triggering a cascading reset.
				nextTick().then(() => {
					requestAnimationFrame(() => {
						if (isDisposed) {
							return
						}

						resetInProgress = false
					})
				})
			}
		})
	}

	/**
	 * Associates a DOM node with its corresponding highlight mark(s) in the store.
	 * Supports both TinyMCE annotation spans (`data-mce-annotation-uid`) and
	 * Block Editor format marks (CSS class + element id).
	 *
	 * @param {HTMLElement} node The DOM node to match against highlight marks.
	 * @returns {void}
	 */
	const setHighlightMarkNode = (node) => {
		const findIndexes = []
		truSeoHighlighterStore.highlightMarks.forEach((hm, i) => {
			const hmId = String(hm.id)

			// For the Classic editor (TinyMCE annotator).
			if (node.hasAttribute('data-mce-annotation-uid') && node.dataset.mceAnnotationUid === hmId) {
				findIndexes.push(i)

				return
			}

			// For the Block editor (custom format type marks). The format mark's
			// element id is suffixed with the mark id (e.g. `aioseo-highlight-12345`).
			const className = getFormatClassName(hm.analyzer)
			if (node.classList.contains(className) && node.id.endsWith(`-${hmId}`)) {
				findIndexes.push(i)
			}
		})

		if (findIndexes.length) {
			findIndexes.forEach(i => { truSeoHighlighterStore.highlightMarks[i].node = node })

			// Reached from the mutation observer (block-editor freeform blocks),
			// where a node can arrive after an earlier attach pass. (Re)attach
			// here as each node is associated so none is left without a listener.
			// (The Classic editor attaches directly in highlightClassicEditor.)
			attachMarkClickListeners()
		}
	}

	/**
	 * Builds highlight mark entries by matching assessment sentences against
	 * the text content of an editor node or block. Uses regex matching
	 * to find character offset ranges, then pushes mark objects to the store.
	 * Recursively processes inner blocks.
	 *
	 * @param {Object}      options       The options object.
	 * @param {Object|null} options.block The Gutenberg block (null for Classic Editor).
	 * @param {HTMLElement|null} options.node The DOM node (null for Block Editor).
	 * @param {Object|null} [options.unit] An adapter ContentUnit (null for block/classic).
	 * @returns {void}
	 */
	const setHighlightMarks = ({ block, node, unit }) => {
		// A unit's `html` is taken VERBATIM, never re-normalized: it is the coordinate space
		// the adapter's own `decorate` aligns against, so any transform here would shift every
		// range by the difference and paint the wrong words. A Classic block is marked only through
		// its TinyMCE's nodes ({@see highlighter/blockEditor.js:getFreeformEditor}).
		const isFreeformWithoutNode = 'core/freeform' === block?.name && !node
		const content               = unit ? unit.html : (isFreeformWithoutNode ? '' : formatBlockContent({ block, node }))
		if (content) {
			// What the analyzer drops in this content, so no mark claims a copy of a word in `<code>`.
			const skipped = unit ? unit.skipped : (node ? streamCache.get(node)?.skipped : getNativeBlockSkipped(block))

			// A unit is identified by its own id, never by its parent node: a builder unit's node can
			// be null (element not rendered yet), and two units of the same element resolve to the
			// SAME node.
			const isSameSource = (hm) => {
				if (unit) {
					return hm.unitId === unit.unitId
				}

				return node ? hm.parent?.isSameNode(node) : hm.block?.clientId === block.clientId
			}

			const existingSentenceKeys = new Set(
				truSeoHighlighterStore.highlightMarks.map(hm => hm.sentenceIndex)
			)

			const allSentences = truSeoHighlighterStore.allHighlightSentences
			for (const [ analyzer, sentences ] of Object.entries(allSentences)) {
				const claimed = new Set(
					sentences.map((_, index) => index).filter(index => existingSentenceKeys.has(`${analyzer}-${index}`))
				)
				const taken = truSeoHighlighterStore.highlightMarks
					.filter(hm => hm.analyzer === analyzer && isSameSource(hm))
					.map(hm => hm.range)

				// Through the shared rule, so "N found" counts exactly the marks built here
				// ({@see highlighter/sentences.js:countLocatableSentences}).
				for (const { index, range } of claimSentenceRanges(content, sentences, claimed, taken, skipped)) {
					truSeoHighlighterStore.highlightMarks.push({
						id            : random(1, 999999999),
						range         : range,
						block         : block,
						parent        : unit ? (unit.getNode?.() || null) : (node || getEditorDocument().getElementById(`block-${block.clientId}`)),
						unitId        : unit?.unitId || null,
						active        : 0 === truSeoHighlighterStore.highlightMarks.length,
						sentenceIndex : `${analyzer}-${index}`,
						sentence      : sentences[index],
						analyzer      : analyzer,
						node          : null
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
		// `reset()` may have already fired with `tinymceEditor.value` still null
		// (sentence data arriving before TinyMCE finishes loading), in which case
		// `highlightClassicEditor` bailed silently. Re-run reset once the editor
		// is ready so the pending highlights actually land.
		initTinymceEditor(tinymceEditor, () => {
			reset()
		})
	}

	// Compared with what this instance last saw, not the watcher's `oldValue`: callers debounce,
	// and a debounce keeps only the LAST call of a burst, whose `oldValue` already holds the new
	// sentences -- so a second, identical analysis landing inside the window lost the paint.
	const watchHighlightSentences = (value) => {
		const parsedValue = JSON.stringify(value)
		if (parsedValue !== lastSentences) {
			lastSentences = parsedValue
			reset()
		}
	}

	// Trigger a reset once TinyMCE finishes booting. `initTinymceEditor`
	// polls every 500ms and populates `tinymceEditor` when ready; if the
	// analyzer produced highlights before that, the first `reset()` bailed
	// early (see the early-return in `reset`) and this watcher fires the
	// retry. Vue tears the watcher down with the parent component.
	watch(tinymceEditor, (editor, prevEditor) => {
		if (editor && !prevEditor) {
			reset()
		}
	})

	// Follow the popover's prev/next pager and the advance-after-fix flow: when a
	// sibling/target mark is picked, highlight it, scroll it into view, and
	// (re)open the popover on it. Keyed off the nav counter — not hoveredMarkId —
	// so a plain click-to-open never scrolls.
	watch(() => truSeoHighlighterStore.markNavigationSeq, () => {
		const markNode = getLiveMarkNode(truSeoHighlighterStore.hoveredMark)
		if (!markNode) {
			return
		}

		setActiveMark(truSeoHighlighterStore.hoveredMarkId)

		// The fix's hidePopover() unpinned it, and an unpinned popover ignores scroll -- under a
		// smooth-scrolling canvas it stayed hidden where the scroll started.
		popoverPinned = true

		revealInEditor(markNode)

		const scrollTarget = getVisibleAnchor(markNode)

		try {
			scrollTarget.scrollIntoView({ block: 'center', inline: 'nearest' })
		} catch (_e) {
			scrollTarget.scrollIntoView()
		}

		// showPopover mounts the popover when advancing after a fix (the fix's
		// re-analysis tore the previous one down) and otherwise repositions +
		// re-reveals the existing one. Reposition again next frame to catch any
		// smooth-scroll settle or iframe reflow.
		showPopover()
		scheduleReposition()

		if (scrollTarget !== markNode) {
			reanchorOnceVisible(markNode, truSeoHighlighterStore.hoveredMarkId)
		}
	})

	// A reveal can open its container a few frames late (Elementor's atomic Tabs render through
	// Alpine), after the jump already settled on the closed container's box.
	const REVEAL_WAIT_FRAMES = 60

	const reanchorOnceVisible = (markNode, markId, framesLeft = REVEAL_WAIT_FRAMES) => {
		cancelAnimationFrame(revealWaitRafId)

		revealWaitRafId = requestAnimationFrame(() => {
			revealWaitRafId = null

			if (isDisposed || markId !== truSeoHighlighterStore.hoveredMarkId || !markNode.isConnected) {
				return
			}

			if (!markNode.getClientRects().length) {
				if (1 < framesLeft) {
					reanchorOnceVisible(markNode, markId, framesLeft - 1)
				}

				return
			}

			try {
				markNode.scrollIntoView({ block: 'center', inline: 'nearest' })
			} catch (_e) {
				markNode.scrollIntoView()
			}

			scheduleReposition()
		})
	}

	// Advance-after-fix: once the re-analysis rebuild repaints the target issue,
	// re-open the popover on it. If it never repaints (e.g. the issue is no longer
	// flagged), nothing happens and the popover simply stays closed.
	watch(() => truSeoHighlighterStore.pendingAdvanceMark, (markId) => {
		if (markId) {
			truSeoHighlighterStore.resolveAdvance(markId)
		}
	})

	// Hide the popover on demand (e.g. right after accepting a fix) so it closes
	// cleanly instead of flickering with emptied content while the rebuild runs.
	watch(() => truSeoHighlighterStore.popoverHideSeq, () => {
		hidePopover()
	})

	// Everything onMounted does behind the `enabled` gate, so a rebind that enables the instance
	// later can wire it too. Idempotent.
	const wireMountedChannels = () => {
		// `isDisposed` as well as `isMounted`: a torn-down instance is still "mounted" by the flag
		// alone, and reset()'s own guard is the only thing keeping this unreachable today.
		if (!isMounted || isDisposed) {
			return
		}

		// A rebind can also close the gate. Shed rather than leave a disabled instance holding
		// a live repaint channel -- repaintDecorations() does not consult `enabled`.
		if (!truSeoHighlighterStore.enabled) {
			invalidationDebounced?.cancel?.()
			invalidationDebounced = null

			try {
				invalidationUnsubscribe?.()
			} catch (_e) {
				// An adapter that throws on unsubscribe must not abort the reset this is inside.
			}
			invalidationUnsubscribe = null

			if (truSeoHighlighterStore.editorModeOwner === instanceToken) {
				releaseSuspensionSlot()
			}

			// Otherwise it keeps waking every second for an instance that will not paint -- and it
			// is the other route into a disabled instance taking the slot, since it is what reaches
			// watchEditorMode().
			if (wrapperPollIntervalId) {
				window.clearInterval(wrapperPollIntervalId)
				wrapperPollIntervalId = null
			}

			return
		}

		// The one-time half: a second `$on` would double the handler, and a second poll would
		// leave an interval nobody clears.
		if (!mountedChannelsWired) {
			mountedChannelsWired = true

			// Hide the popover the moment content starts changing so it
			// doesn't linger with stale analysis data during the debounce
			// window before the analysis re-runs.
			window.aioseoBus.$on('aioseo-content-changing', hidePopover)

			// An unmounting owner frees the slot; a live instance has to take it, or mode changes
			// reach nobody.
			window.aioseoBus.$on(SUSPENSION_SLOT_RELEASED, subscribeSuspension)

			// Hands the previous instance's mode subscription back before this one subscribes: the
			// classic editor's observer lives behind the same unsubscribe as the block editor's
			// wp.data subscription. Through the shared release so the CLAIM moves with the handle --
			// clearing one without the other is what let two instances both believe they held it.
			releaseSuspensionSlot()

			wrapperPollIntervalId = window.setInterval(() => {
				if (syncIframeState()) {
					injectHighlightStyles()
				}

				if (null !== getEditorNode('wrapper')) {
					window.clearInterval(wrapperPollIntervalId)
					wrapperPollIntervalId = null

					watchEditorMode()
				}
			}, 1000)
		}

		// Both self-releasing, so calling them on every rebind is correct rather than merely safe.
		subscribeInvalidation()
		subscribeSuspension()
	}

	onMounted(() => {
		isMounted = true

		// Ahead of the wiring: a rebind can change the answer the gate was given in
		// onBeforeMount, and syncAdapter() recomputes it -- and then wires through the very
		// function below, so the two paths cannot diverge.
		syncAdapter()

		wireMountedChannels()
	})

	onBeforeMount(() => {
		truSeoHighlighterStore.initTruSeoHighlighting()

		syncHighlightingEnabled()

		// Safety net for editor contexts not covered by the eager entry
		// registration; every adapter's init() is idempotent, so a double-call
		// here is a no-op.
		adapter?.init?.()
	})

	onUnmounted(() => {
		// Flipped first: every guard added for this composable's async
		// continuations and observer callbacks (reset() and its nextTick()
		// continuations, attachMarkClickListeners(), observeMarkParent(),
		// observeEditor(), scheduleReposition()'s rAF) reads this, so nothing
		// below can resurrect work for a torn-down instance.
		isDisposed = true
		isMounted  = false

		// `hidePopover` is a fresh closure per instance, so without this every
		// sidebar mount (it remounts on each visibility trigger and Headline
		// Analyzer toggle) adds a permanent subscriber that never goes away.
		window.aioseoBus.$off('aioseo-content-changing', hidePopover)
		window.aioseoBus.$off(SUSPENSION_SLOT_RELEASED, subscribeSuspension)

		// `detachMarkClickListeners` removes the delegated mark-click listeners
		// (a click on a still-present mark after teardown would otherwise reach
		// `showPopover()` -> `repositionHoverPopover()` synchronously, no rAF
		// involved) and, via its own call to `detachScrollListener`, cancels any
		// pending reposition frame and removes the scroll listeners that would
		// otherwise arm a fresh one -- `popoverPinned` and the store's popover
		// node both survive in this dead closure, so neither guard alone stops it.
		detachMarkClickListeners()

		// Belt and braces alongside the `isDisposed` guard in its callback: a
		// mutation record queued before this call can still be delivered after
		// it, but only disconnecting stops the observer from firing at all.
		editorObserver.value?.disconnect()
		editorObserver.value = null

		// `onMounted`'s wrapper-poll interval only clears itself once
		// `getEditorNode('wrapper')` resolves, so it runs for the page's
		// lifetime if the wrapper never appears (page builder, torn-down editor).
		if (wrapperPollIntervalId) {
			window.clearInterval(wrapperPollIntervalId)
			wrapperPollIntervalId = null
		}

		// The invalidation subscriber holds this dead closure's `reset`, and its source is the
		// editor's own render events — which keep firing long after this instance is gone. The
		// debounce is cancelled too: releasing the subscription does not un-schedule a repaint
		// the adapter already delivered.
		invalidationDebounced?.cancel?.()
		invalidationDebounced = null

		if (invalidationUnsubscribe) {
			invalidationUnsubscribe()
			invalidationUnsubscribe = null
		}

		// subscribeSuspension() lets only the owner re-take the slot, so an owner that leaves
		// without releasing it locks every other instance out.
		if (truSeoHighlighterStore.editorModeOwner === instanceToken) {
			suspensionSync?.cancel?.()
			releaseSuspensionSlot()
			window.aioseoBus.$emit(SUSPENSION_SLOT_RELEASED)
		}
	})

	return {
		watchHighlightSentences
	}
}