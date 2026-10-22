import { isBlockEditor } from '@/vue/utils/context'
import { getEditorDocument, getEditorIframe, getEditorWindow, isIframedEditor } from '@/vue/utils/editor'

import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'

import {
	dispatchBlockHighlights,
	getFreeformEditor,
	getNativeBlockText,
	getRichTextIdentifier
} from '@/vue/plugins/tru-seo/highlighter/blockEditor'
import { getNativeBlockSkipped } from '@/vue/plugins/tru-seo/highlighter/blockText'

import { buildTextStream } from '@/vue/plugins/tru-seo/highlighter/textStream'

import {
	replaceAllInBlockEditor,
	replaceInBlockEditor
} from '@/vue/plugins/tru-seo/highlighter/spellingReplace'

import { registerHighlightFormats } from '@/vue/plugins/tru-seo/highlighter/blockFormats'

export default {
	id : 'block-editor',

	// Highlighting is expected to work normally here — not an analyze-only editor.
	analyzeOnly : false,

	// Ownership of the page, not readiness: resolveAdapter() memoises this, so a capability probe
	// here would cache "no adapter" for good. Capability is asked in isSupported().
	isActive () {
		try {
			return !!isBlockEditor()
		} catch (_e) {
			return false
		}
	},

	// Whether this page can ever highlight. isReady() is the same: AIOSEO's scripts are deferred
	// modules that run after every editor script, so these globals exist by then or never will.
	isSupported () {
		try {
			const wp = window?.wp

			return !!(
				wp?.richText?.registerFormatType &&
				wp?.data?.select('core/block-editor') &&
				wp?.data?.select('core/edit-post')
			)
		} catch (_e) {
			return false
		}
	},

	isReady () {
		return this.isSupported()
	},

	getContentUnits () {
		try {
			const blocks = window.wp.data.select('core/block-editor').getBlocks() || []
			const units = []

			// getBlocks() only returns root-level blocks; Group/Columns/Cover etc.
			// nest their content in innerBlocks, so this must recurse to see them.
			const walk = (list, isRoot) => {
				for (const block of list) {
					// The same nodes and text the highlighter marks a Classic block from; a nested one
					// is never marked.
					if ('core/freeform' === block.name) {
						const children = isRoot ? getFreeformEditor(block)?.getBody()?.children : null
						Array.from(children || []).forEach((node, index) => {
							const stream = buildTextStream(node)

							units.push({
								unitId   : `${block.clientId}:content-${index}`,
								ownerId  : block.clientId,
								attrName : 'content',
								html     : normalizeWhitespaces(stream.text),
								skipped  : stream.skipped,
								getNode  : () => node
							})
						})

						continue
					}

					// html must stay getNativeBlockText() to match the highlighter's projection, even
					// though attrName can name a different attribute (nothing consumes it yet).
					const attrName = getRichTextIdentifier(block)

					units.push({
						unitId  : `${block.clientId}:${attrName}`,
						ownerId : block.clientId,
						attrName,
						html    : getNativeBlockText(block),
						skipped : getNativeBlockSkipped(block),
						getNode : () => getEditorDocument().getElementById(`block-${block.clientId}`)
					})

					if (block.innerBlocks?.length) {
						walk(block.innerBlocks, false)
					}
				}
			}
			walk(blocks, true)

			return units
		} catch (_e) {
			return []
		}
	},

	// Document-scoped: dispatchBlockHighlights clears the whole store before
	// re-adding, so it must see every mark in one call.
	decorate (marks) {
		dispatchBlockHighlights(marks || [])
	},

	clearDecorations () {
		dispatchBlockHighlights([])
	},

	onInvalidated (cb) {
		try {
			const subscribe = window.wp.data.subscribe

			// The store-scoped 2nd arg needs a newer @wordpress/data than this
			// plugin's WP floor (readme.txt: Requires at least 5.7) ships. Older
			// cores accept the extra arg but ignore it, firing on every store's
			// dispatch — detect support via arity rather than assume it.
			return 2 <= subscribe.length
				? subscribe(cb, 'core/block-editor')
				: subscribe(cb)
		} catch (_e) {
			return () => {}
		}
	},

	replaceMark (mark, replacement) {
		try {
			return Promise.resolve(replaceInBlockEditor(mark, replacement))
		} catch (_e) {
			return Promise.resolve(false)
		}
	},

	// Mirrors replaceWordInContent's block branch: no re-analysis step, so
	// triggerReanalysis is a classic-only option.
	replaceAllOccurrences (word, replacement) {
		if (!word || !replacement) {
			return Promise.resolve(0)
		}

		try {
			const blocks = window.wp?.data?.select?.('core/block-editor')?.getBlocks?.() || []
			if (!blocks.length) {
				return Promise.resolve(0)
			}

			let totalCount = 0

			const walk = (list) => {
				for (const block of list) {
					totalCount += replaceAllInBlockEditor(block.clientId, word, replacement)

					if (block.innerBlocks?.length) {
						walk(block.innerBlocks)
					}
				}
			}
			walk(blocks)

			return Promise.resolve(totalCount)
		} catch (_e) {
			return Promise.resolve(0)
		}
	},

	focus (unitId) {
		try {
			const [ clientId ] = String(unitId || '').split(':')

			window.wp.data.dispatch('core/block-editor').selectBlock(clientId)

			const node = getEditorDocument().getElementById(`block-${clientId}`)
			if (node) {
				try {
					node.scrollIntoView({ block: 'center', inline: 'nearest' })
				} catch (_e) {
					node.scrollIntoView()
				}
			}
		} catch (_e) {
			// Block already gone, or the editor store is not available.
		}
	},

	getRootNode () {
		try {
			return getEditorDocument().querySelector('.editor-styles-wrapper .is-root-container')
		} catch (_e) {
			return null
		}
	},

	getFirstUnitNode () {
		try {
			const firstBlock = window.wp.data.select('core/block-editor').getBlocks()[0]

			return getEditorDocument().getElementById(`block-${firstBlock?.clientId}`) || {}
		} catch (_e) {
			return null
		}
	},

	// Appends to <body> so the fixed popover escapes the editor canvas's
	// containing block/overflow clip. It stays in the main document, so its
	// scoped styles still apply; repositioning keeps it clear of the
	// surrounding chrome (admin bar, header, meta boxes).
	getPopoverHost () {
		try {
			return document.body
		} catch (_e) {
			return null
		}
	},

	getViewportBounds () {
		try {
			const viewportBottom = window.innerHeight

			// The block editor's scroll region already sits below the admin bar and
			// header; its top is the highest the popover may go.
			const content = document.querySelector('.interface-interface-skeleton__content')
			if (content) {
				const contentRect = content.getBoundingClientRect()
				let bottom = contentRect.bottom

				// Meta boxes render below the canvas inside that same scroll region —
				// clamp to their top so the popover can't spill onto them.
				const metaboxes = document.querySelector('.edit-post-layout__metaboxes')
				if (metaboxes) {
					const mbRect = metaboxes.getBoundingClientRect()
					if (0 < mbRect.height && mbRect.top < bottom) {
						bottom = mbRect.top
					}
				}

				return {
					top    : Math.max(0, contentRect.top),
					bottom : Math.min(viewportBottom, bottom)
				}
			}

			// No skeleton content region found — at minimum stay clear of the
			// fixed WP admin bar at the top.
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
			// Mirrors syncIframeState()'s own predicate (TruSeoHighlighter.js): the
			// iframe element can exist before its contentDocument (and .head) is
			// usable, so isIframedEditor() alone is not enough.
			if (!isIframedEditor() || !getEditorIframe()?.contentDocument?.head) {
				return { top: 0, left: 0 }
			}

			// The canvas iframe can be removed under a live instance (device preview, code
			// editor), not just on teardown.
			const editorIframe = getEditorIframe()
			if (!editorIframe) {
				return { top: 0, left: 0 }
			}

			const iframeRect = editorIframe.getBoundingClientRect()

			return { top: iframeRect.top, left: iframeRect.left }
		} catch (_e) {
			return { top: 0, left: 0 }
		}
	},

	// The composable currently wraps cb in a 250ms debounce before ever handing
	// it here — this method subscribes with whatever it is given, undebounced.
	// A caller passing the raw callback gets it invoked on every wp.data dispatch.
	onSuspensionChange (cb) {
		try {
			if (!window.wp?.data?.subscribe) {
				return () => {}
			}

			return window.wp.data.subscribe(cb)
		} catch (_e) {
			return () => {}
		}
	},

	// Deliberately empty — only the classic adapter suppresses TinyMCE's own
	// change/undo events around a paint; the block editor has nothing to
	// suppress.
	beginPaint () {
		return null
	},

	endPaint () {},

	init () {
		try {
			// Safety net for editor contexts not covered by the eager entry
			// registration; idempotent, so a double-call here is a no-op.
			registerHighlightFormats()
		} catch (_e) {
			// Format registration is best-effort; a failure here must not block
			// the editor adapter from otherwise working.
		}
	},

	isSuspended () {
		try {
			return 'text' === window.wp.data.select('core/edit-post')?.getEditorMode()
		} catch (_e) {
			return false
		}
	},

	getDocument : getEditorDocument,
	getWindow   : getEditorWindow
}