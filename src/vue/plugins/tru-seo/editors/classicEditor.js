import { isClassicEditor } from '@/vue/utils/context'

import {
	normalizeWhitespaces,
	getClosestNodeByPropertyValue
} from '@/vue/utils/postData/helpers'

import { buildTextStream } from '@/vue/plugins/tru-seo/highlighter/textStream'

import {
	replaceAllWordInClassicEditor,
	replaceBlocksInClassicEditor,
	replaceInClassicEditor
} from '@/vue/plugins/tru-seo/highlighter/spellingReplace'

import {
	suppressTinyMceEvents,
	restoreTinyMceEvents
} from '@/vue/plugins/tru-seo/highlighter/tinymce'

// `window.tinymce` can be present before `get` is, so the lookup itself throws
// rather than returning null.
const getTinymceEditor = () => {
	try {
		const editor = window?.tinymce?.get('content')

		return 'function' === typeof editor?.getBody ? editor : null
	} catch (_e) {
		return null
	}
}

// TinyMCE boots on DOM ready, after any adapter consumer can first ask. Polls
// on the same 500ms cadence as `initTinymceEditor` and hands the instance over
// once it lands. The returned canceller stops a poll that never resolved.
const whenTinymceReady = (onReady) => {
	const invoke = (editor) => {
		try {
			onReady(editor)
		} catch (_e) {
			// A consumer that fails to attach must not surface as an editor error.
		}
	}

	const editor = getTinymceEditor()
	if (editor) {
		invoke(editor)

		return () => {}
	}

	let interval = window.setInterval(() => {
		const instance = getTinymceEditor()
		if (!instance) {
			return
		}

		window.clearInterval(interval)
		interval = null

		invoke(instance)
	}, 500)

	return () => {
		if (interval) {
			window.clearInterval(interval)
			interval = null
		}
	}
}

export default {
	id : 'classic-editor',

	// Highlighting is expected to work normally here — not an analyze-only editor.
	analyzeOnly : false,

	// Ownership of the page, not readiness. TinyMCE boots on DOM ready, after
	// the post-settings app has mounted, so a readiness probe here resolves to
	// "no adapter" for every consumer that asks during boot — and resolveAdapter
	// memoises that answer for the page's lifetime. Each method below handles a
	// missing editor instance on its own.
	isActive () {
		try {
			return !!isClassicEditor()
		} catch (_e) {
			return false
		}
	},

	isReady () {
		try {
			return !!getTinymceEditor()?.getBody()
		} catch (_e) {
			return false
		}
	},

	getContentUnits () {
		try {
			const children = getTinymceEditor()?.getBody()?.children || []

			// ownerId is positional: inserting a paragraph renumbers everything
			// after it. Safe only because marks are rebuilt wholesale each pass
			// and never correlated by id. Do not persist these across an edit.
			return Array.from(children).map((node, index) => {
				const stream = buildTextStream(node)

				return {
					unitId   : `classic-${index}:content`,
					ownerId  : `classic-${index}`,
					attrName : 'content',
					// Must match formatBlockContent's projection exactly, which wraps
					// the stream in normalizeWhitespaces. The block adapter gets this
					// for free inside getNativeBlockText; here it is explicit.
					html     : normalizeWhitespaces(stream.text),
					skipped  : stream.skipped,
					getNode  : () => node
				}
			})
		} catch (_e) {
			return []
		}
	},

	// Classic marks are TinyMCE annotations applied by highlighter/classicEditor.js
	// during the highlight pass, not dispatched to a store.
	decorate () {},

	clearDecorations () {},

	onInvalidated (cb) {
		let subscribed = null

		const cancelPoll = whenTinymceReady(editor => {
			editor.on('change', cb)
			editor.on('SetContent', cb)
			subscribed = editor
		})

		return () => {
			cancelPoll()

			try {
				subscribed?.off('change', cb)
				subscribed?.off('SetContent', cb)
			} catch (_e) {
				// Editor already destroyed.
			}

			subscribed = null
		}
	},

	replaceMark (mark, replacement) {
		try {
			return Promise.resolve(replaceInClassicEditor(mark, replacement))
		} catch (_e) {
			return Promise.resolve(false)
		}
	},

	// TinyMCE's content is block html, so a paragraph-scope fix can write real markup instead of
	// a string whose newlines html would collapse. No `consumeWriteRefusal`, so refusals stay silent.
	replaceMarkBlocks (mark, parts) {
		try {
			return Promise.resolve(replaceBlocksInClassicEditor(mark, parts))
		} catch (_e) {
			return Promise.resolve(false)
		}
	},

	replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		if (!word || !replacement) {
			return Promise.resolve(0)
		}

		try {
			return Promise.resolve(replaceAllWordInClassicEditor(word, replacement, triggerReanalysis))
		} catch (_e) {
			return Promise.resolve(0)
		}
	},

	focus (unitId) {
		try {
			if (!unitId) {
				return
			}

			const [ ownerId ] = String(unitId).split(':')
			const index       = Number(ownerId.replace('classic-', ''))
			// Number('') is 0, not NaN — a malformed ownerId must not resolve to
			// the first paragraph either.
			if (!Number.isInteger(index) || 0 > index) {
				return
			}

			const node = getTinymceEditor()?.getBody()?.children?.[index]
			if (!node) {
				return
			}

			try {
				node.scrollIntoView({ block: 'center', inline: 'nearest' })
			} catch (_e) {
				node.scrollIntoView()
			}
		} catch (_e) {
			// Unit gone, or the editor cannot be scrolled.
		}
	},

	getRootNode () {
		try {
			return document.getElementById('content_ifr')
		} catch (_e) {
			return null
		}
	},

	getFirstUnitNode () {
		try {
			return getTinymceEditor()?.getBody()?.firstElementChild || {}
		} catch (_e) {
			return null
		}
	},

	// TinyMCE redraws its own edit area on caret and selection changes,
	// which would tear an in-area popover down; `.wp-editor-container` is
	// never touched by TinyMCE.
	getPopoverHost () {
		try {
			return document.getElementById('content_ifr')?.closest('.wp-editor-container') ||
				getClosestNodeByPropertyValue({
					element  : document.getElementById('content_ifr')?.parentElement,
					property : 'position',
					value    : 'relative'
				})
		} catch (_e) {
			return null
		}
	},

	getViewportBounds () {
		try {
			const viewportBottom = window.innerHeight

			// At minimum stay clear of the fixed WP admin bar at the top; this
			// adapter has no block-editor-style skeleton-content region to
			// clamp against.
			const adminBar = document.getElementById('wpadminbar')
			const adminBarBottom = adminBar ? adminBar.getBoundingClientRect().bottom : 0

			return {
				top    : Math.max(0, adminBarBottom),
				bottom : viewportBottom
			}
		} catch (_e) {
			return { top: 0, bottom: window.innerHeight || 0 }
		}
	},

	getMarkRectOffset () {
		try {
			// Classic marks are in TinyMCE iframe space; lift them into the
			// main viewport with the editor wrapper's offset.
			const wrapper = document.getElementById('content_ifr')

			// TinyMCE re-init or a builder swap can remove #content_ifr while
			// this instance is still very much alive, not just on teardown.
			if (!wrapper) {
				return { top: 0, left: 0 }
			}

			const editorNodePos = wrapper.getBoundingClientRect()

			return { top: editorNodePos.top, left: editorNodePos.left }
		} catch (_e) {
			return { top: 0, left: 0 }
		}
	},

	// #wp-content-wrap's class alone, which toggles `tmce-active`/`html-active` on a Text/Visual
	// switch; a broader observer would wake on the popover's own style mutations. Subscribes cb
	// undebounced.
	onSuspensionChange (cb) {
		try {
			const contentWrap = document.getElementById('wp-content-wrap')
			if (!contentWrap) {
				return () => {}
			}

			const observer = new MutationObserver(cb)
			observer.observe(contentWrap, {
				attributes      : true,
				attributeFilter : [ 'class' ],
				subtree         : false
			})

			return () => observer.disconnect()
		} catch (_e) {
			return () => {}
		}
	},

	// Suppresses TinyMCE's own change/undo events for the duration of a
	// paint -- annotating marks would otherwise dirty the editor and can
	// trigger WordPress's own re-init handlers.
	beginPaint (context) {
		try {
			// The caller's own handle wins over a live lookup, which after a re-init answers a
			// different instance than the one being painted.
			const editor = 'function' === typeof context?.getBody ? context : getTinymceEditor()
			if (!editor) {
				return null
			}

			return { editor, originalFire: suppressTinyMceEvents(editor) }
		} catch (_e) {
			return null
		}
	},

	endPaint (token) {
		try {
			if (token?.editor && token?.originalFire) {
				restoreTinyMceEvents(token.editor, token.originalFire)
			}
		} catch (_e) {
			// Editor already destroyed mid-paint.
		}
	},

	// Deliberately empty -- nothing to register for the classic editor; only
	// the block editor needs one-time RichText format registration.
	init () {},

	// Classic declares no isSupported(): the Code tab is a suspension, not an incapacity.
	isSuspended () {
		try {
			return !!document.getElementById('wp-content-wrap')?.classList?.contains('html-active')
		} catch (_e) {
			return false
		}
	},

	// Before TinyMCE boots, getDocument/getWindow answer with the main document
	// and window. That is a fallback, not the right answer: the editor's own DOM
	// does not exist yet. Anything that needs it must re-ask after boot rather
	// than caching what these return during setup.
	getDocument () {
		try {
			return getTinymceEditor()?.getDoc() || document
		} catch (_e) {
			return document
		}
	},

	getWindow () {
		try {
			return getTinymceEditor()?.getWin() || window
		} catch (_e) {
			return window
		}
	}
}