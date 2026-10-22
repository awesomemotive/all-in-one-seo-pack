import { buildMarkupProjection, buildTextStream, escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import {
	MARK_ATTRIBUTE,
	alignProjectedMarks,
	clearDecorations as clearDomDecorations,
	decorateNode,
	ensureDecorationStyles,
	foldNbsp,
	preserveSelection
} from './domDecorator'
import { ensureSerializerGuards } from './diviSerializerGuard'
import { isDivi4Editor, isExtraTheme } from '@/vue/utils/context'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'

import { buildStructuredMarkup } from '@/vue/plugins/tru-seo/highlighter/structuredMarkup'
import { findSentenceRanges, matchCase, wordBoundaryPattern } from '@/vue/utils/regex'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'

import { editStoredBlock, editStoredHtml, parseInert } from './storedHtmlEdit'
import { findEditorChangeHandler, findInlineEditor, isTextModuleProps, moduleUuid, readModuleProps, readTextAttrs } from './divi4Units'
import { findRenderedHeadingTag, looksLikeHtml } from './divi5Units'
import { getStickyTopOffset, revealNode } from './diviReveal'

/**
 * Divi 4 adapter.
 *
 * The canvas is an iframe (`#et-fb-app-frame`, note Divi 5 uses `et-vb-`) and there the
 * resemblance to Divi 5 ends: Divi 4 exposes no store. `ET_Builder.API` on the canvas window
 * carries module-registration helpers only, and the internal Flux store that owns
 * `updateModuleProps` is reachable through webpack internals alone -- module ids that move with
 * every Divi release, so it is not used here.
 *
 * READ comes from the module's React props ({@see divi4Units.js:readModuleProps}), which hold the
 * STORED content rather than the rendered DOM.
 *
 * WRITE goes through the module's own inline TinyMCE: its content is set and then Divi's own change
 * handler is called, after which Divi's normal save persists it. Measured end to end, in the
 * database.
 * The editor exists only once the user has clicked that module's text -- Divi opens it on a
 * TRUSTED click, and neither dispatched MouseEvents nor calling Divi's own `onClick` through the
 * fiber will do it -- so a write with no editor refuses rather than pretending. An editor SURVIVES
 * its blur, so a module clicked once stays writable for the rest of the session.
 *
 * PAINTING IS NOT GATED ON AN OPEN EDITOR, unlike Elementor V3's pen: a `PreProcess` handler on
 * each editor strips our marks from anything it serializes ({@see ensureSerializerGuards}), so a
 * decoration cannot reach stored content even from inside the editor the user is typing in.
 */

const CANVAS_FRAME = 'iframe#et-fb-app-frame'

const getCanvasFrame = () => document.querySelector(CANVAS_FRAME)

// Same-origin by construction -- the builder loads the site's own page -- but a frame
// mid-navigation answers null and must not be mistaken for "use the admin document".
const getCanvasDocument = () => getCanvasFrame()?.contentDocument || null

const getCanvasWindow = () => getCanvasFrame()?.contentWindow || null

const MODULE_SELECTOR = '[class*="et-module-"]'

const squashSpace = (text) => String(text ?? '').replace(/\s+/g, '')

// By position, as Divi's own tab click matches them.
const getTabPanel = (navItem) => {
	const nav    = navItem.parentElement
	const panels = Array.from(nav.parentElement?.children || []).find(child => child.matches('.et_pb_all_tabs'))?.children || []

	return Array.from(panels).filter(child => child.matches('.et_pb_tab'))[Array.from(nav.children).indexOf(navItem)] || null
}

// Divi renders a Tab child twice -- its title in the parent's nav `<li>`, its body in a panel under
// `.et_pb_all_tabs` -- and the nav comes first in the DOM, so the first class match holds the title
// alone. The panel is resolved from its nav item whether or not it carries the class too, and nav
// items go last: a nav item is only ever the fallback, because its fiber may climb to the parent
// Tabs module's component rather than the tab's own.
const orderModuleNodes = (nodes) => {
	const navItems = nodes.filter(node => node.matches('.et_pb_tabs_controls > li'))
	const panels   = navItems.map(getTabPanel).filter(Boolean)

	return [ ...new Set([ ...nodes.filter(node => !navItems.includes(node)), ...panels, ...navItems ]) ]
}

const getModuleNodes = (uuid) => orderModuleNodes([ ...(getCanvasDocument()?.querySelectorAll(`.et-module-${uuid}`) || []) ])

// The module node that renders `text`, so a value is painted into the node that shows it. An exact
// match first: a tab title repeated inside its body must still land on the nav item.
const getModuleNode = (uuid, text = '') => {
	try {
		const nodes  = getModuleNodes(uuid)
		const wanted = squashSpace(text)

		if (!wanted) {
			return nodes[0] || null
		}

		return nodes.find(node => squashSpace(node.textContent) === wanted) ||
			nodes.find(node => squashSpace(node.textContent).includes(wanted)) ||
			nodes[0] ||
			null
	} catch (_e) {
		return null
	}
}

// Divi 4's wireframe renders no module content, so there is nothing to paint into. Read from the
// module's own props rather than a body class, so it is the builder's answer and not a guess.
const isWireframeView = () => {
	try {
		return true === readModuleProps(getCanvasDocument()?.querySelector(MODULE_SELECTOR))?.wireframeMode
	} catch (_e) {
		return false
	}
}

// A module's stored `content` is HTML for prose. Projected through the SAME stream a rendered node
// goes through, so marks measured against a unit and marks aligned into its node share one
// coordinate space.
const projectContent = (text) => {
	if (!looksLikeHtml(text)) {
		return { markup: escapeText(normalizeWhitespaces(text)), text: normalizeWhitespaces(text), skipped: [] }
	}

	// Wrapped in a div so the emitted root tag is a div rather than the parse's `<body>`, which is
	// the shape a rendered element wrapper gives the other adapters.
	const holder = parseInert(`<div>${text}</div>`).firstElementChild

	return holder ? buildMarkupProjection(holder, normalizeWhitespaces) : { markup: '', text: '', skipped: [] }
}

/**
 * Removes our own decorations from a stored-content string.
 *
 * A decoration should never reach here -- decorate() refuses a unit with an open editor -- but the
 * write base is `ed.getContent()`, which serializes a live DOM we do not own. Unwrapping is the
 * safe failure: refusing would turn a stray mark into a fix the user cannot make, and writing it
 * through would persist a `<mark>` into their content.
 *
 * @param {string} html The stored content.
 * @returns {string} The content without our marks.
 */
const stripDecorations = (html) => {
	try {
		if (!String(html ?? '').includes(MARK_ATTRIBUTE)) {
			return html
		}

		const holder = parseInert(`<div>${html}</div>`).firstElementChild
		if (!holder) {
			return html
		}

		for (const mark of holder.querySelectorAll(`mark[${MARK_ATTRIBUTE}]`)) {
			mark.replaceWith(...mark.childNodes)
		}

		return holder.innerHTML
	} catch (_e) {
		return html
	}
}

const buildUnits = () => {
	const doc = getCanvasDocument()
	if (!doc) {
		return []
	}

	const units       = []
	const nodesByUuid = new Map()

	// Some modules carry their class on two nodes (a button's wrapper and its link, a tab's nav
	// item and its panel), so the module is the unit and its nodes are ordered as getModuleNodes().
	for (const node of doc.querySelectorAll(MODULE_SELECTOR)) {
		const uuid = moduleUuid(node)

		if (uuid) {
			nodesByUuid.set(uuid, [ ...(nodesByUuid.get(uuid) || []), node ])
		}
	}

	for (const [ uuid, nodes ] of nodesByUuid) {
		const props = readModuleProps(orderModuleNodes(nodes)[0])

		if (!props) {
			continue
		}

		const { leading, trailing } = readTextAttrs(props)

		// Read-only: the module's inline TinyMCE edits `content` alone, so a fix could only refuse.
		const attrUnit = ({ name, text }) => {
			const projected = projectContent(text)

			return {
				unitId   : `${uuid}:${name}`,
				ownerId  : uuid,
				attrName : name,
				html     : projected.text,
				skipped  : projected.skipped,
				writable : false,
				getNode  : () => getModuleNode(uuid, projected.text)
			}
		}

		units.push(...leading.map(attrUnit))

		// A container stores its CHILDREN in `content` as an object, so sections, rows and columns
		// drop out here rather than through a list of module names this file would have to keep.
		if (isTextModuleProps(props)) {
			const projected = projectContent(props.content)

			units.push({
				unitId   : `${uuid}:content`,
				ownerId  : uuid,
				attrName : 'content',
				html     : projected.text,
				skipped  : projected.skipped,

				// Divi's own lock, so the refusal is the builder's rule and not a guess of ours. NOT
				// "does an editor exist": that is decided at write time, because the user's click on a
				// mark is what opens one.
				writable : 'on' !== props.locked,
				getNode  : () => getModuleNode(uuid, projected.text)
			})
		}

		units.push(...trailing.map(attrUnit))
	}

	return units
}

// `mutationSuppressionDepth` is declared here only to keep one module-scope declaration; what it
// is for is explained where it is used ({@see suppressCanvasMutations}).
let writeRefusal = null,
	mutationSuppressionDepth = 0

const recordRefusal = (reason) => {
	writeRefusal = reason || null
}

// A sentence is located by its TEXT in the stored content and taken only when it occurs once. The
// mark's offsets address the PROJECTION, and Divi wraps each module in chrome the stored value does
// not carry, so reusing an offset here would rewrite whatever happens to sit at that position.
const locateSentence = (haystack, sentence) => {
	if (!sentence) {
		return null
	}

	const found = findSentenceRanges(haystack, sentence)

	return 1 === found.length ? found[0] : null
}

// Everything a write needs about one unit, or null with the refusal already recorded.
// `knownUnit` is for a caller that is ALREADY iterating the unit list. Everything volatile
// here is re-read below regardless, so the only thing the rebuild supplied was the unit's own
// identity -- and rebuilding costs a full enumeration of the page. `replaceAllOccurrences`
// resolving each of its own units through here enumerated it once per unit.
const resolveWriteTarget = (unitId, knownUnit = null) => {
	const unit = knownUnit || (unitId ? buildUnits().find(entry => entry.unitId === unitId) : null)

	if (!unit) {
		recordRefusal('unit-gone')

		return null
	}

	if (false === unit.writable) {
		recordRefusal('content' === unit.attrName ? 'locked-module' : 'read-only-attribute')

		return null
	}

	ensureSerializerGuards(getCanvasWindow())

	const node   = unit.getNode()
	const editor = findInlineEditor(getCanvasWindow(), node)

	// No editor means the user has never clicked this module's text, and we cannot open one for
	// them. The popover answers this reason with "click the highlighted word again", which is
	// exactly the step that unblocks it, unlike `dynamic` or `shortcode-output` where the text is
	// not in the content at all.
	if (!editor) {
		recordRefusal('needs-inline-editor')

		return null
	}

	// AND it has to be ACTIVE, which is the difference between a fix and a lie. An inactive editor
	// still takes `setContent` far enough to change what the canvas SHOWS, but Divi commits
	// nothing and its React component -- where the commit handler lives -- is gone, so the edit
	// exists only in the DOM: the canvas reads as fixed, the analyser still sees the old text
	// because it reads the module's props, and the change is lost on the next render or reload.
	// Refusing here keeps "we could not fix this" honest.
	if ('true' !== editor.getBody?.()?.getAttribute?.('contenteditable')) {
		recordRefusal('needs-inline-editor')

		return null
	}

	return { unit, editor, stored: stripDecorations(editor.getContent()) }
}

// Bounds the wait for Divi to apply a commit; it announces each with a message, sometimes two.
const COMMIT_SETTLE_WAIT_MS = 2000
const COMMIT_QUIET_MS       = 150

/**
 * Starts listening for Divi's "content changed" messages, so a write can wait until Divi applied it.
 * NOTE: start it BEFORE the commit -- the message can land before the caller gets to listen.
 *
 * @returns {{ settled: function(): Promise<void>, stop: Function }} The waiter and its teardown.
 */
const watchCommits = () => {
	let done   = false,
		notify = null,
		quiet  = null

	const handler = (event) => {
		if (event.origin !== window.location.origin || 'et_fb_section_content_change' !== event.data?.eventType) {
			return
		}

		clearTimeout(quiet)
		quiet = setTimeout(() => {
			done = true
			notify?.()
		}, COMMIT_QUIET_MS)
	}

	const stop = () => {
		window.removeEventListener('message', handler)
		clearTimeout(quiet)
	}

	window.addEventListener('message', handler)

	return {
		settled : () => new Promise(resolve => {
			const finish = () => {
				clearTimeout(timer)
				stop()
				resolve()
			}
			const timer = setTimeout(finish, COMMIT_SETTLE_WAIT_MS)

			done ? finish() : (notify = finish)
		}),
		stop
	}
}

/**
 * Commits a new value for one module through its inline editor.
 *
 * `setContent` only changes the editor; the commit is Divi's own change handler
 * ({@see divi4Units.js:findEditorChangeHandler}, which carries the measurements). The `blur`
 * fallback is kept for the case the handler cannot be reached, where it still commits as long as
 * the editor is active.
 *
 * @param {Object} editor The module's inline TinyMCE editor.
 * @param {string} value  The new content.
 * @returns {boolean} True when the write was issued.
 */
const commitContent = (editor, value) => {
	try {
		editor.setContent(value)

		const onChange = findEditorChangeHandler(editor)

		if (onChange) {
			onChange({ type: 'change', target: editor.getBody?.() || null }, editor)

			return true
		}

		editor.fire('blur')

		return true
	} catch (_e) {
		return false
	}
}

const replaceAllInText = (text, word, replacement) => {
	const pattern = wordBoundaryPattern(word)
	if (!pattern) {
		return null
	}

	let count = 0

	const next = String(text).replace(new RegExp(pattern, 'gu'), (match) => {
		count++

		// The matched text, not `word`: the needle was flagged against the projection, so the
		// casing to carry onto a lowercase suggestion is the stored value's own.
		return matchCase(match, replacement)
	})

	return count ? { value: next, count } : null
}

// decorate() is document-scoped and receives the whole store mark set, so the grouping is the
// adapter's job.
const isAddressableMark = (mark) => !!mark?.unitId && !!mark.range

const groupMarksByUnit = (marks) => {
	const grouped = new Map()

	for (const mark of marks.filter(isAddressableMark)) {
		if (!grouped.has(mark.unitId)) {
			grouped.set(mark.unitId, [])
		}

		grouped.get(mark.unitId).push(mark)
	}

	return grouped
}

// Our own paints must not read back as a content change.
//
// Divi 4 has no store to subscribe to, so invalidation is a MutationObserver on the canvas
// ({@see onInvalidated}) -- and the composable's callback is a repaint, which mutates the very
// tree being observed. Left unguarded that is an endless repaint loop.
//
// Released on a MACROTASK because MutationObserver delivers its records as a microtask: by the
// time this timeout runs, our own records have already been seen and dropped. A change Divi makes
// inside the same window is lost, which the next mutation or the composable's own content watch
// picks up.
const suppressCanvasMutations = (fn) => {
	mutationSuppressionDepth++

	try {
		return fn()
	} finally {
		setTimeout(() => {
			mutationSuppressionDepth = Math.max(0, mutationSuppressionDepth - 1)
		}, 0)
	}
}

/**
 * One unit's contribution to the ANALYZED string, which carries markup where `html` carries text.
 *
 * The markup is what lets the analyzer see structure plain text cannot show it: `getSubheadings`
 * regexes for `<h1>`-`<h6>`, `getAnchors` for `<a>`, and `getBlocks` splits a paper at the block
 * tags. A Divi text module's `content` holds real markup, so dropping it would hide every
 * subheading on the page.
 *
 * Both sides come from the SAME stored value, so the identity the rendered-node adapters have to
 * check for holds by construction here.
 *
 * @param {Object} unit A unit from `getContentUnits()`.
 * @returns {string} The contribution, or an empty string when there is nothing to analyze.
 */
export const projectUnitContribution = (unit) => {
	try {
		if (!unit?.html) {
			return ''
		}

		const node = getModuleNode(unit.ownerId, unit.html)

		// A title is stored plain; the heading Divi renders it in is what makes it a subheading.
		if ('content' !== unit.attrName) {
			const tag = findRenderedHeadingTag(node, unit.html)

			return tag ? `<${tag}>${escapeText(unit.html)}</${tag}>` : escapeText(unit.html)
		}

		const props = readModuleProps(node)
		if (!isTextModuleProps(props)) {
			return escapeText(unit.html)
		}

		const projected = projectContent(props.content)

		return unit.html === projected.text ? projected.markup : escapeText(unit.html)
	} catch (_e) {
		return ''
	}
}

export default {
	id : 'divi-4',

	analyzeOnly : false,

	// This adapter owns the builder; the classic/block adapter underneath owns only the editor
	// beneath it, never the canvas painted over it.
	ownsPageBuilder : true,

	paintsViaContract : true,

	// Divi must be allowed to act on a mark's click. Its inline editor is the only write path
	// there is, and only a trusted click activates it -- an inactive instance takes `setContent`
	// and commits nothing (measured: `contenteditable="false"`, `focus()` does not take, the
	// stored value never moves). The mark surviving that activation is what the serializer guard
	// buys ({@see ensureSerializerGuards}).
	suppressesEditingClick : false,

	// A fix can only be applied by clicking the MARK. Divi's inline editor is the only write path
	// and only a trusted click on a module's text opens it -- and opening the AIOSEO panel is
	// itself a click in the admin document, which closes it again. So a suggestion clicked in the
	// Spelling tab answers with a notice pointing at the mark instead of a write that could only
	// refuse, and the popover closes after a fix instead of advancing to the next mark, which Divi
	// would see no click on. Clicking the mark activates the module it belongs to, which is why
	// that path works.
	fixesRequireMarkClick : true,

	// A fix has to be written during the mousedown that requests it. Divi closes its inline editor
	// on a mousedown anywhere in the admin document -- where our popover lives -- and a closed
	// editor is read-only ({@see components/common/tru-seo/HighlightPopover.vue} for the
	// measurements).
	fixesOnPointerDown : true,

	// The canvas renders the theme's own header, fixed by default ("Fixed Navigation Bar"), so a
	// jump landing at the canvas top lands under it.
	getStickyTopOffset () {
		return getStickyTopOffset(getCanvasDocument())
	},

	// Opens every closed Toggle, Accordion item, Tab and Slide around `node`, ending in the state
	// Divi's own click leaves.
	// NOTE: DOM state only, never the module's stored content, so revealing text never dirties the
	// page; the next render of the module restores what it stores.
	revealNode (node) {
		revealNode(node)
	},

	// Ownership and capability, never readiness ({@see utils/context.js:isDivi4Editor}).
	isActive () {
		try {
			return !isExtraTheme() && !!isDivi4Editor()
		} catch (_e) {
			return false
		}
	},

	getRootNode () {
		try {
			return getCanvasDocument()?.body || null
		} catch (_e) {
			return null
		}
	},

	getDocument () {
		try {
			return getCanvasDocument() || document
		} catch (_e) {
			return document
		}
	},

	getWindow () {
		try {
			return getCanvasWindow() || window
		} catch (_e) {
			return window
		}
	},

	getContentUnits () {
		try {
			return buildUnits()
		} catch (_e) {
			return []
		}
	},

	getContentUnit (unitId) {
		try {
			return buildUnits().find(unit => unit.unitId === unitId) || null
		} catch (_e) {
			return null
		}
	},

	getUnitNode (unitId) {
		try {
			return buildUnits().find(unit => unit.unitId === unitId)?.getNode() || null
		} catch (_e) {
			return null
		}
	},

	decorate (marks) {
		try {
			const root = getCanvasDocument()?.body || null

			// Before any paint: a mark may land inside an open editor, and this is what keeps it
			// out of that editor's serialization.
			ensureSerializerGuards(getCanvasWindow())

			// The caret the user types with sits in a text node this pass replaces, so it is
			// carried across as an offset ({@see preserveSelection}).
			preserveSelection(root?.ownerDocument, () => {
				suppressCanvasMutations(() => clearDomDecorations(root))

				// Nothing is rendered in wireframe, so there is nothing to paint into. Analysis is
				// unaffected: units read stored content, not the DOM.
				if (!root || isWireframeView() || !Array.isArray(marks) || !marks.length) {
					return
				}

				const byUnit = groupMarksByUnit(marks)
				if (!byUnit.size) {
					return
				}

				const units     = new Map((this.getContentUnits?.() || []).map(unit => [ unit.unitId, unit ]))
				const analyzers = new Set()

				suppressCanvasMutations(() => {
					for (const [ unitId, unitMarks ] of byUnit) {
						try {
							const unit = units.get(unitId)

							// A locked module cannot be written, so a mark on it could only ever offer
							// a fix that refuses (Invariant 4).
							if (!unit || false === unit.writable) {
								continue
							}

							const node = unit.getNode?.()
							if (!node) {
								continue
							}

							// The projection came from the STORED value while this paints into the
							// RENDERED node, so the two are genuinely different strings -- Divi renders
							// `content` through the_content-style filters and wraps it in module chrome.
							// The aligner maps one onto the other and drops a mark it cannot place
							// rather than guessing.
							const stream  = buildTextStream(node)
							const aligned = alignProjectedMarks({
								rendered  : foldNbsp(stream.text),
								projected : unit.html,
								marks     : unitMarks,
								skipped   : stream.skipped
							})

							const decorated = decorateNode({ node, marks: aligned.aligned })

							for (const painted of decorated.painted) {
								// The hand-off the store depends on: it resolves a mark's live node
								// from here and drops any mark without one, and the popover's jump
								// scrolls it.
								//
								// ADOPTED whenever the cached node is no longer in the document, not
								// just when it is missing. Divi re-renders a module the moment its
								// inline editor opens, which detaches every mark inside it while
								// leaving `mark.node` truthy -- measured: 7 store marks all pointing
								// at detached nodes while 9 fresh ones stood in the DOM. Keeping the
								// stale one leaves the mark unclickable and its popover anchored to a
								// zero-sized rect.
								painted.mark.node = painted.mark.node?.isConnected ? painted.mark.node : painted.node
								analyzers.add(painted.mark.analyzer)
							}
						} catch (_e) {
							// One module's failure must not cost the modules after it.
						}
					}
				})

				// The canvas is an iframe, so the composable's main-document stylesheet cannot reach
				// these marks -- without this they are in the DOM and invisible.
				ensureDecorationStyles(root.ownerDocument, [ ...analyzers ])
			})
		} catch (_e) {
			// Never throws: a failed paint degrades to no highlighting.
		}
	},

	clearDecorations () {
		try {
			const doc = getCanvasDocument()

			preserveSelection(doc, () => suppressCanvasMutations(() => clearDomDecorations(doc?.body || null)))
		} catch (_e) {
			// Nothing to undo.
		}
	},

	// The popover mounts in the ADMIN document while a mark's rect is measured inside the canvas
	// iframe; these three are the bridge between those realms.
	getPopoverHost () {
		try {
			return document.body
		} catch (_e) {
			return null
		}
	},

	getViewportBounds () {
		const viewportBottom = window.innerHeight || 0

		try {
			const frame = getCanvasFrame()
			if (!frame) {
				return { top: 0, bottom: viewportBottom }
			}

			const rect = frame.getBoundingClientRect()

			return {
				top    : Math.max(0, rect.top),
				bottom : Math.min(viewportBottom, rect.bottom)
			}
		} catch (_e) {
			return { top: 0, bottom: viewportBottom }
		}
	},

	getMarkRectOffset () {
		try {
			const frame = getCanvasFrame()
			if (!frame) {
				return { top: 0, left: 0 }
			}

			const rect = frame.getBoundingClientRect()

			return { top: rect.top, left: rect.left }
		} catch (_e) {
			return { top: 0, left: 0 }
		}
	},

	consumeWriteRefusal () {
		const reason = writeRefusal

		writeRefusal = null

		return reason
	},

	// One mark's sentence, replaced in the content its module stores.
	replaceMark (mark, replacement) {
		recordRefusal(null)

		try {
			if (!replacement || 'string' !== typeof replacement) {
				recordRefusal('no-replacement')

				return Promise.resolve(false)
			}

			const target = resolveWriteTarget(mark?.unitId)
			if (!target) {
				return Promise.resolve(false)
			}

			const { editor, stored } = target
			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			let next = null

			if (looksLikeHtml(stored)) {
				const edited = editStoredHtml(stored, (projected) => {
					const range = locateSentence(projected, sentence)

					return range ? [ { start: range.start, end: range.end, text: replacement } ] : []
				})

				next = edited?.html ?? null
			} else {
				const range = locateSentence(stored, sentence)

				next = range ? stored.slice(0, range.start) + replacement + stored.slice(range.end) : null
			}

			if (null === next) {
				recordRefusal('not-found')

				return Promise.resolve(false)
			}

			if (!commitContent(editor, next)) {
				recordRefusal('write-error')

				return Promise.resolve(false)
			}

			requestPostUpdate(500)

			return Promise.resolve(true)
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	},

	// Document-scoped and marks-independent: the Spelling tab calls this straight off the resolved
	// adapter, so the per-unit gates inside are the only ones it gets.
	//
	// Resolves only once Divi has applied the commits: the units read Divi's stored props, so a caller
	// re-analyzing on resolve would otherwise read the pre-fix text.
	async replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		recordRefusal(null)

		let commits = null

		try {
			// wordBoundaryPattern('') matches at every boundary with zero length and a `g` exec loop
			// never advances past it, so an unguarded call hangs the editor rather than failing.
			if (!word || !replacement) {
				return 0
			}

			commits = watchCommits()

			let total = 0

			for (const unit of buildUnits()) {
				const target = resolveWriteTarget(unit.unitId, unit)
				if (!target) {
					continue
				}

				const { editor, stored } = target

				if (looksLikeHtml(stored)) {
					const edited = editStoredHtml(stored, (projected) => {
						const pattern = wordBoundaryPattern(word)
						if (!pattern) {
							return []
						}

						const edits = []
						const regex = new RegExp(pattern, 'gu')
						let match

						while (null !== (match = regex.exec(projected))) {
							edits.push({
								start : match.index,
								end   : match.index + match[0].length,
								text  : matchCase(match[0], replacement)
							})
						}

						return edits
					})

					if (edited?.html && commitContent(editor, edited.html)) {
						total += edited.count
					}

					continue
				}

				const replaced = replaceAllInText(stored, word, replacement)

				if (replaced && commitContent(editor, replaced.value)) {
					total += replaced.count
				}
			}

			// Only a refusal that cost the caller everything is worth reporting: one module
			// refusing while others were written is a partial write, and the Spelling tab reports
			// the count.
			if (!total && !writeRefusal) {
				recordRefusal('not-found')
			}

			if (total && triggerReanalysis) {
				requestPostUpdate(500)
			}

			if (!total) {
				commits.stop()

				return 0
			}

			await commits.settled()

			return total
		} catch (_e) {
			commits?.stop()
			recordRefusal('write-error')

			return 0
		}
	},

	// FAILS OPEN, the inverse of the write gate: this is asked BEFORE the AI request is billed, so
	// a wrong "no" removes the only fix a genuinely failing check has, while a wrong "yes" costs
	// one request. False only where the stored value POSITIVELY cannot hold a block.
	canReplaceMarkBlocks (mark) {
		try {
			const target = mark?.unitId ? buildUnits().find(entry => entry.unitId === mark.unitId) : null
			if (!target) {
				return true
			}

			const props = readModuleProps(getModuleNode(target.ownerId, target.html))

			return !isTextModuleProps(props) || looksLikeHtml(props.content)
		} catch (_e) {
			return true
		}
	},

	replaceMarkBlocks (mark, parts) {
		recordRefusal(null)

		try {
			const target = resolveWriteTarget(mark?.unitId)
			if (!target) {
				return Promise.resolve(false)
			}

			const { editor, stored } = target

			// The write gate's polarity, not canReplaceMarkBlocks': storing `<p>` in a plain value
			// would persist the tags as text the reader sees.
			if (!looksLikeHtml(stored)) {
				recordRefusal('not-structural')

				return Promise.resolve(false)
			}

			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			const edited = editStoredBlock(stored, (projected) => {
				const range = locateSentence(projected, sentence)

				return range ? { start: range.start, end: range.end, markup: buildStructuredMarkup(parts) } : null
			})

			if (!edited?.html) {
				recordRefusal(edited?.refusal || 'not-found')

				return Promise.resolve(false)
			}

			if (!commitContent(editor, edited.html)) {
				recordRefusal('write-error')

				return Promise.resolve(false)
			}

			requestPostUpdate(500)

			return Promise.resolve(true)
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	},

	/**
	 * Divi 4 has no store to subscribe to, so the canvas DOM is the channel.
	 *
	 * It covers the two changes that matter and one the other adapters need a separate mechanism
	 * for: a module's content changing, the tree changing under it, and an inline editor CLOSING --
	 * TinyMCE re-renders the element on close, which is what lets the unit decorate() refused get
	 * painted. Elementor V3 needs a dedicated pen-close poll for exactly that
	 * ({@see elementor.js} `ensurePenOpenWatch`).
	 *
	 * Delivered raw; the composable debounces at 250 ms, which also settles the burst a re-render
	 * arrives in.
	 *
	 * @param {Function} cb Called when the canvas changed.
	 * @returns {Function} The unsubscribe.
	 */
	onInvalidated (cb) {
		const noop = () => {}

		try {
			if ('function' !== typeof cb) {
				return noop
			}

			const win  = getCanvasWindow()
			const root = getCanvasDocument()?.body

			if (!win?.MutationObserver || !root) {
				return noop
			}

			const observer = new win.MutationObserver(() => {
				if (mutationSuppressionDepth) {
					return
				}

				try {
					cb()
				} catch (_e) {
					// A subscriber's failure must not tear down the observer.
				}
			})

			observer.observe(root, { childList: true, subtree: true, characterData: true })

			return () => {
				try {
					observer.disconnect()
				} catch (_e) {
					// Already gone with the frame.
				}
			}
		} catch (_e) {
			return noop
		}
	}
}

export { isWireframeView }