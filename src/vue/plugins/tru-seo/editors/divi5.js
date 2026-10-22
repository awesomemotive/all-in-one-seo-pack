import { buildMarkupProjection, buildTextStream, escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import {
	alignProjectedMarks,
	clearDecorations as clearDomDecorations,
	decorateNode,
	ensureDecorationStyles,
	foldNbsp,
	preserveSelection
} from './domDecorator'
import { ensureSerializerGuards } from './diviSerializerGuard'
import { isDivi5Editor, isExtraTheme } from '@/vue/utils/context'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'

import { buildStructuredMarkup } from '@/vue/plugins/tru-seo/highlighter/structuredMarkup'
import { findSentenceRanges, matchCase, wordBoundaryPattern } from '@/vue/utils/regex'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'

import { editStoredBlock, editStoredHtml, parseInert } from './storedHtmlEdit'
import { attrWritePath, findRenderedHeadingTag, looksLikeHtml, readAttrText, readHeadingLevel, textAttrNames } from './divi5Units'
import {
	alignAcrossGaps,
	editMaskedPlain,
	fillSentinels,
	gapFor,
	maskHtml,
	maskPlain,
	maskPlainWithGaps,
	sentinelOffsets,
	stripGaps,
	unmaskHtml
} from './divi5Masks'
import { CAROUSEL_CLONE, getStickyTopOffset, revealNode } from './diviReveal'

/**
 * Divi 5 adapter.
 *
 * The canvas is an iframe (`#et-vb-app-frame`, note Divi 4 uses `et-fb-`) and the model is a
 * wp.data-style registry INSIDE it: `contentWindow.divi.data`, whose `divi/edit-post` store
 * enumerates modules, reads their attributes and writes them back.
 *
 * THE PROJECTION COMES FROM THE ATTRIBUTE, NOT THE RENDERED NODE, which is the opposite of the
 * other two adapters and deliberate. Divi 5's attributes are the source of truth and readable in
 * every view including `wireframe`, where no module is rendered at all -- so analysis stays correct
 * there without the view-flipping the standalone integration resorts to
 * ({@see standalone/page-builders/divi/divi5-helpers.js:refreshDivi5Content}). Paint still needs a
 * node, so wireframe simply paints nothing.
 *
 * Only the DESKTOP breakpoint is read or written ({@see divi5Units.js}).
 */

const CANVAS_FRAME = 'iframe#et-vb-app-frame'

const getCanvasFrame = () => document.querySelector(CANVAS_FRAME)

// Same-origin by construction -- the builder loads the site's own page -- but a frame mid-navigation
// answers null and must not be mistaken for "use the admin document".
const getCanvasDocument = () => getCanvasFrame()?.contentDocument || null

const getCanvasWindow = () => getCanvasFrame()?.contentWindow || null

const getRegistry = () => getCanvasWindow()?.divi?.data || null

const getEditPost = () => {
	try {
		return getRegistry()?.select?.('divi/edit-post') || null
	} catch (_e) {
		return null
	}
}

// `wireframe` renders no modules, so there is nothing to paint into. Analysis is unaffected: the
// projection reads attributes rather than the DOM.
const isWireframeView = () => {
	try {
		return 'wireframe' === getRegistry()?.select?.('divi/app-ui')?.getView?.()
	} catch (_e) {
		return false
	}
}

const squashSpace = (text) => String(text ?? '').replace(/\s+/g, '')

// The element that renders one attribute, not the module's first `[data-id]` node. A module can
// render under several -- a Tab's title sits in the parent's nav `<li>`, its content in a panel --
// and one node renders every attribute side by side with no boundary between them, so a word at
// the edge of a title (`Bar liason` beside `50%`) can never be placed on word boundaries.
// Falls back to the first node holding the text, which is how a value carrying resolved dynamic
// content still paints.
// A Group Carousel clones its edge groups, `data-id`s included, to loop; a clone sits off screen
// ahead of the first group, so it is never the module's node.
const findUnitNode = (ownerId, projected) => {
	const nodes  = [ ...(getCanvasDocument()?.querySelectorAll(`[data-id="${ownerId}"]`) || []) ]
		.filter(node => !node.closest(CAROUSEL_CLONE))
	const wanted = squashSpace(projected)

	if (!wanted) {
		return nodes[0] || null
	}

	for (const node of nodes) {
		const exact = [ node, ...node.querySelectorAll('*') ].find(element => squashSpace(element.textContent) === wanted)
		if (exact) {
			return exact
		}
	}

	return nodes.find(node => squashSpace(node.textContent).includes(wanted)) || nodes[0] || null
}

const getModuleName = (ownerId) => {
	try {
		return getEditPost()?.getModuleName?.(ownerId) || ''
	} catch (_e) {
		return ''
	}
}

// Only a Pricing Table's feature list gives a line-leading `+`/`-` a meaning.
const getMaskOptions = (moduleName, attrName) => ({ lineMarkers: 'divi/pricing-table' === moduleName && 'content' === attrName })

const getLockedIds = () => {
	try {
		return new Set(getEditPost()?.getLockedModuleIds?.() || [])
	} catch (_e) {
		return new Set()
	}
}

// Moves ranges measured before stripGaps() into its text: every gap before an offset is one
// character fewer. `gaps[index] + index` is where that gap stood before the strip.
const shiftPastGaps = (ranges, gaps) => {
	if (!gaps.length) {
		return ranges
	}

	const shift = (offset) => offset - gaps.filter((at, index) => at + index < offset).length

	return ranges
		.map(range => ({ start: shift(range.start), end: shift(range.end) }))
		.filter(range => range.start < range.end)
}

// An attribute's text is HTML for prose and plain for a title or a button label. Both are projected
// through the SAME stream a rendered node goes through, so marks measured against a unit and marks
// aligned into its node share one coordinate space.
// Masked first ({@see divi5Masks.js}), so a dynamic token or a Pricing marker never reaches the
// analyser, and the write paths below mask the same way to stay in this coordinate space.
// `gaps` are where each dynamic token stood in `text`, for painting over its resolved value, and
// `skipped` the ranges of `text` the analyzer drops.
const projectAttrText = (text, maskOptions = {}) => {
	const gap = gapFor(text)

	if (!looksLikeHtml(text)) {
		const visible = stripGaps(normalizeWhitespaces(maskPlainWithGaps(text, maskOptions, gap)), gap)

		return { markup: escapeText(visible.text), text: visible.text, gaps: visible.gaps, skipped: [] }
	}

	// Wrapped in a div so the emitted root tag is a div rather than the parse's `<body>`, which is
	// the shape a rendered element wrapper gives the other adapters.
	const masked = maskHtml(text, maskOptions)
	const holder = parseInert(`<div>${fillSentinels(masked.html, masked.pieces, gap)}</div>`).firstElementChild
	if (!holder) {
		return { markup: '', text: '', gaps: [], skipped: [] }
	}

	const projected = buildMarkupProjection(holder, normalizeWhitespaces)
	const visible   = stripGaps(projected.text, gap)

	return {
		markup  : stripGaps(projected.markup, gap).text,
		text    : visible.text,
		gaps    : visible.gaps,
		skipped : shiftPastGaps(projected.skipped || [], visible.gaps)
	}
}

const buildUnits = () => {
	const editPost = getEditPost()
	if (!editPost) {
		return []
	}

	const locked = getLockedIds()
	const units  = []

	for (const ownerId of (editPost.getAllModuleIds?.() || [])) {
		const attrs      = editPost.getModuleAttrs?.(ownerId)
		const moduleName = getModuleName(ownerId)

		// A container answers none, so sections, rows and columns drop out here rather than through
		// a list of module names this file would have to maintain.
		for (const attrName of textAttrNames(attrs, moduleName)) {
			const read        = readAttrText(attrs[attrName])
			const maskOptions = getMaskOptions(moduleName, attrName)
			const { text: html, gaps, skipped } = projectAttrText(read.text, maskOptions)

			units.push({
				unitId   : `${ownerId}:${attrName}`,
				ownerId,
				attrName,
				moduleName,
				maskOptions,
				subKey   : read.subKey,
				html,
				gaps,
				skipped,
				writable : !locked.has(ownerId),
				getNode  : () => findUnitNode(ownerId, html)
			})
		}
	}

	return units
}

let writeRefusal = null

const recordRefusal = (reason) => {
	writeRefusal = reason || null
}

// A sentence is located by its TEXT in the stored attribute and taken only when it occurs once. The
// mark's offsets address the PROJECTION, and Divi wraps each module in chrome the attribute does
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

	// Divi's own lock, so this refusal is the builder's rule rather than a guess of ours.
	if (false === unit.writable) {
		recordRefusal('locked-module')

		return null
	}

	const attrs = getEditPost()?.getModuleAttrs?.(unit.ownerId)
	const read  = readAttrText(attrs?.[unit.attrName])

	if (!read) {
		recordRefusal('not-structural')

		return null
	}

	return { unit, stored: read.text, subKey: read.subKey, maskOptions: unit.maskOptions || {} }
}

// The refusal a masked piece answers when an edit would cross or drop it: the dynamic one names
// where the text really comes from, a marker has no reason of its own.
const maskRefusal = (pieces) => (pieces.some(piece => 'dynamic' === piece.kind) ? 'dynamic' : 'not-found')

// `editStoredHtml`/`editStoredBlock` against the MASKED value, so `plan` sees the same projection a
// unit's `html` holds. A plan edit with a masked piece strictly inside it is dropped, and a result
// that lost a piece is refused rather than written.
const editMaskedHtml = (stored, maskOptions, plan, { block = false } = {}) => {
	const masked = maskHtml(stored, maskOptions)

	if (!masked.pieces.length) {
		return block ? editStoredBlock(stored, plan) : editStoredHtml(stored, plan)
	}

	const offsets  = [ ...sentinelOffsets(masked.html).values() ]
	const crosses  = (edit) => offsets.some(offset => offset > edit.start && offset < edit.end)
	let crossed    = false

	const guarded = (projected) => {
		const request = plan(projected)

		if (block) {
			crossed = !!request && crosses(request)

			return crossed ? null : request
		}

		const edits = (request || []).filter(edit => !crosses(edit))
		crossed     = edits.length < (request || []).length

		return edits
	}

	const edited = block ? editStoredBlock(masked.html, guarded) : editStoredHtml(masked.html, guarded)
	if (!edited?.html) {
		return crossed ? { refusal: maskRefusal(masked.pieces) } : edited
	}

	const html = unmaskHtml(edited.html, masked.pieces)

	return null === html ? { refusal: maskRefusal(masked.pieces) } : { ...edited, html }
}

const writeAttr = async (unit, subKey, value) => {
	const registry = getRegistry()
	if (!registry) {
		return false
	}

	// Divi's React writes a plain-text label into the text node it rendered. With a mark still
	// splitting that node, the write lands in the prefix fragment and the old word stays beside it.
	// The re-analysis the write triggers repaints the module.
	for (const node of getCanvasDocument()?.querySelectorAll(`[data-id="${unit.ownerId}"]`) || []) {
		clearDomDecorations(node)
	}

	// `caller: 'user'` is what Divi's own UI passes, so history and the dirty flag behave as they do
	// for a hand edit ({@see edit-post.js} call sites).
	await registry.dispatch('divi/edit-post').editModuleAttribute({
		id       : unit.ownerId,
		attrName : attrWritePath(unit.attrName, subKey),
		value,
		caller   : 'user',
		subName  : false
	})

	return true
}

const replaceAllInText = (text, word, replacement, maskOptions = {}) => {
	const pattern = wordBoundaryPattern(word)
	if (!pattern) {
		return null
	}

	const edits = []

	for (const match of maskPlain(text, maskOptions).text.matchAll(new RegExp(pattern, 'gu'))) {
		// The matched text, not `word`: the needle was flagged against the projection, so the casing
		// to carry onto a lowercase suggestion is the stored value's own.
		edits.push({ start: match.index, end: match.index + match[0].length, text: matchCase(match[0], replacement) })
	}

	if (!edits.length) {
		return null
	}

	const edited = editMaskedPlain(text, maskOptions, edits)

	return edited.refusal ? null : { value: edited.value, count: edited.count }
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

/**
 * The heading tag a plain-text unit renders in, or null when it is not a heading.
 * NOTE: A title is stored plain, while the analyzer only counts `<h1>`-`<h6>` as subheadings. The
 * canvas has the level Divi actually rendered, defaults included; wireframe falls back to the
 * attribute's own setting.
 *
 * @param {Object} unit      A unit from `getContentUnits()`.
 * @param {*}      attrValue The unit's attribute.
 * @returns {?string} The tag name, or null.
 */
const getHeadingTag = (unit, attrValue) => findRenderedHeadingTag(unit.getNode?.(), unit.html) ||
	readHeadingLevel(attrValue, unit.moduleName, unit.attrName)

/**
 * One unit's contribution to the ANALYZED string, which carries markup where `html` carries text.
 *
 * The markup is what lets the analyzer see structure plain text cannot show it: `getSubheadings`
 * regexes for `<h1>`-`<h6>`, `getAnchors` for `<a>`, and `getBlocks` splits a paper at the block
 * tags. A Divi text module's `content` holds real markup, so dropping it would hide every
 * subheading on the page.
 *
 * Both sides come from the SAME attribute, so the identity the other adapters have to check for
 * holds by construction here.
 *
 * @param {Object} unit A unit from `getContentUnits()`.
 * @returns {string} The contribution, or an empty string when there is nothing to analyze.
 */
export const projectUnitContribution = (unit) => {
	try {
		if (!unit?.html) {
			return ''
		}

		const attrs = getEditPost()?.getModuleAttrs?.(unit.ownerId)
		const read  = readAttrText(attrs?.[unit.attrName])

		if (!read) {
			return escapeText(unit.html)
		}

		const projected = projectAttrText(read.text, unit.maskOptions)
		if (unit.html !== projected.text) {
			return escapeText(unit.html)
		}

		const tag = looksLikeHtml(read.text) ? null : getHeadingTag(unit, attrs[unit.attrName])

		return tag ? `<${tag}>${projected.markup}</${tag}>` : projected.markup
	} catch (_e) {
		return ''
	}
}

export default {
	id : 'divi-5',

	analyzeOnly : false,

	// This adapter owns the builder; the classic/block adapter underneath owns only the editor
	// beneath it, never the canvas painted over it.
	ownsPageBuilder : true,

	paintsViaContract : true,

	// Divi lays a mousetrap over every module and sets `pointer-events: none` on all it contains,
	// so a click's target is never the mark; the composable hit-tests the painted marks instead.
	marksIgnorePointer : true,

	// That mousetrap, so a point over a mark is not mistaken for something drawn on top of it.
	isPointerOverlay (element, markNode) {
		return !!element?.classList?.contains('et-vb-module-mousetrap') && !!element.parentElement?.contains(markNode)
	},

	// The canvas renders the theme's own header, fixed by default ("Fixed Navigation Bar"), so a
	// jump landing at the canvas top lands under it.
	getStickyTopOffset () {
		return getStickyTopOffset(getCanvasDocument())
	},

	// Opens every closed Toggle, Accordion item, Tab, Slide and carousel group around `node`,
	// outermost first, ending in the state Divi's own click leaves.
	// NOTE: DOM state only, never a module attribute, so revealing text never dirties the page; the
	// next render of the module restores what the model says.
	revealNode (node) {
		revealNode(node)
	},

	// Ownership and capability, never readiness ({@see utils/context.js:isDivi5Editor}).
	isActive () {
		try {
			return !isExtraTheme() && !!isDivi5Editor()
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

	// NO CANVAS GATE AGAINST OUR OWN MARKS. The stored value is the model's and the canvas is a
	// render of it, so a painted `<mark>` lives only in the DOM -- with one reader: an open inline
	// editor serializes its LIVE body into the attribute when the edit ends (measured on 5.15: the
	// stored content came back with our marks in it). The serializer guard covers that reader, so
	// Invariant 3 holds without the pen gate Elementor needs.
	decorate (marks) {
		try {
			const root = getCanvasDocument()?.body || null

			ensureSerializerGuards(getCanvasWindow())

			// The caret the user types with sits in a text node this pass replaces, so it is
			// carried across as an offset ({@see preserveSelection}).
			preserveSelection(root?.ownerDocument, () => {
				clearDomDecorations(root)

				// Nothing is rendered in wireframe, so there is nothing to paint into. Analysis is
				// unaffected: units read attributes, not the DOM.
				if (!root || isWireframeView() || !Array.isArray(marks) || !marks.length) {
					return
				}

				const byUnit = groupMarksByUnit(marks)
				if (!byUnit.size) {
					return
				}

				const units     = new Map((this.getContentUnits?.() || []).map(unit => [ unit.unitId, unit ]))
				const analyzers = new Set()

				for (const [ unitId, unitMarks ] of byUnit) {
					try {
						const unit = units.get(unitId)

						// A locked module cannot be written, so a mark on it could only ever offer a fix
						// that refuses (Invariant 4).
						if (!unit || false === unit.writable) {
							continue
						}

						const node = unit.getNode?.()
						if (!node) {
							continue
						}

						// The projection came from the ATTRIBUTE while this paints into the RENDERED
						// node, so unlike the other adapters the two are genuinely different strings --
						// Divi wraps a module's markup in its own chrome. The aligner is what maps one
						// onto the other, and it drops a mark it cannot place rather than guessing.
						const stream   = buildTextStream(node)
						const rendered = foldNbsp(stream.text)
						const aligned  = alignProjectedMarks({ rendered, projected: unit.html, marks: unitMarks, skipped: stream.skipped })
						const bridged  = unit.gaps?.length && aligned.dropped.length
							? alignAcrossGaps({ rendered, projected: unit.html, gaps: unit.gaps, marks: aligned.dropped.map(entry => entry.mark) })
							: []

						const decorated = decorateNode({ node, marks: [ ...aligned.aligned, ...bridged ] })

						for (const painted of decorated.painted) {
							// The hand-off the store depends on: it resolves a mark's live node from
							// here and drops any mark without one, and the popover's jump scrolls it.
							// Adopted once the cached node is detached, as on Divi 4: a re-render leaves
							// `mark.node` truthy but dead, and sibling order compares those nodes.
							painted.mark.node = painted.mark.node?.isConnected ? painted.mark.node : painted.node
							analyzers.add(painted.mark.analyzer)
						}
					} catch (_e) {
						// One module's failure must not cost the modules after it.
					}
				}

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

			preserveSelection(doc, () => clearDomDecorations(doc?.body || null))
		} catch (_e) {
			// Nothing to undo.
		}
	},

	// The popover mounts in the ADMIN document while a mark's rect is measured inside the canvas
	// iframe; these two are the bridge between those realms.
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

	// One mark's sentence, replaced in the attribute that stores it.
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

			const { unit, stored, subKey, maskOptions } = target
			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			let next    = null,
				refusal = 'not-found'

			if (looksLikeHtml(stored)) {
				const edited = editMaskedHtml(stored, maskOptions, (projected) => {
					const range = locateSentence(projected, sentence)

					return range ? [ { start: range.start, end: range.end, text: replacement } ] : []
				})

				next    = edited?.html ?? null
				refusal = edited?.refusal || refusal
			} else {
				const range  = locateSentence(maskPlain(stored, maskOptions).text, sentence)
				const edited = range ? editMaskedPlain(stored, maskOptions, [ { ...range, text: replacement } ]) : null

				next    = edited?.value ?? null
				refusal = edited?.refusal || refusal
			}

			if (null === next) {
				recordRefusal(refusal)

				return Promise.resolve(false)
			}

			return writeAttr(unit, subKey, next).then(written => {
				if (written) {
					requestPostUpdate(500)
				}

				return written
			}).catch(() => {
				recordRefusal('write-error')

				return false
			})
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	},

	// Document-scoped and marks-independent: the Spelling tab calls this straight off the resolved
	// adapter, so the per-unit gates inside are the only ones it gets.
	async replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		recordRefusal(null)

		try {
			// wordBoundaryPattern('') matches at every boundary with zero length and a `g` exec loop
			// never advances past it, so an unguarded call hangs the editor rather than failing.
			if (!word || !replacement) {
				return 0
			}

			let total = 0

			for (const unit of buildUnits()) {
				const target = resolveWriteTarget(unit.unitId, unit)
				if (!target) {
					continue
				}

				const { stored, subKey, maskOptions } = target

				if (looksLikeHtml(stored)) {
					const edited = editMaskedHtml(stored, maskOptions, (projected) => {
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

					if (edited?.html) {
						await writeAttr(unit, subKey, edited.html)
						total += edited.count
					} else if (edited?.refusal) {
						recordRefusal(edited.refusal)
					}

					continue
				}

				const replaced = replaceAllInText(stored, word, replacement, maskOptions)

				if (replaced) {
					await writeAttr(unit, subKey, replaced.value)
					total += replaced.count
				}
			}

			// Only a refusal that cost the caller everything is worth reporting: one module refusing
			// while others were written is a partial write, and the Spelling tab reports the count.
			if (!total && !writeRefusal) {
				recordRefusal('not-found')
			}

			if (total && triggerReanalysis) {
				requestPostUpdate(500)
			}

			return total
		} catch (_e) {
			recordRefusal('write-error')

			return 0
		}
	},

	// FAILS OPEN, the inverse of the write gate: this is asked BEFORE the AI request is billed, so a
	// wrong "no" removes the only fix a genuinely failing check has, while a wrong "yes" costs one
	// request. False only where the attribute POSITIVELY cannot hold a block -- a title and a button
	// label are plain text, and a `<p>` written into either would be stored as literal characters.
	canReplaceMarkBlocks (mark) {
		try {
			const target = mark?.unitId ? buildUnits().find(entry => entry.unitId === mark.unitId) : null
			if (!target) {
				return true
			}

			const attrs = getEditPost()?.getModuleAttrs?.(target.ownerId)
			const read  = readAttrText(attrs?.[target.attrName])

			return !read || looksLikeHtml(read.text)
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

			const { unit, stored, subKey, maskOptions } = target

			// The write gate's polarity, not canReplaceMarkBlocks': storing `<p>` in a plain
			// attribute would persist the tags as text the reader sees.
			if (!looksLikeHtml(stored)) {
				recordRefusal('not-structural')

				return Promise.resolve(false)
			}

			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			const edited = editMaskedHtml(stored, maskOptions, (projected) => {
				const range = locateSentence(projected, sentence)

				return range ? { start: range.start, end: range.end, markup: buildStructuredMarkup(parts) } : null
			}, { block: true })

			if (!edited?.html) {
				recordRefusal(edited?.refusal || 'not-found')

				return Promise.resolve(false)
			}

			return writeAttr(unit, subKey, edited.html).then(written => {
				if (written) {
					requestPostUpdate(500)
				}

				return written
			}).catch(() => {
				recordRefusal('write-error')

				return false
			})
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	},

	// Divi's registry is a wp.data store, so its own subscribe is the content-change channel --
	// the same one main.js already uses to keep the score button mounted. Delivered raw; the
	// composable debounces at 250 ms.
	onInvalidated (cb) {
		const noop = () => {}

		try {
			if ('function' !== typeof cb) {
				return noop
			}

			const unsubscribe = getRegistry()?.subscribe?.(() => {
				try {
					cb()
				} catch (_e) {
					// A subscriber's failure must not tear down Divi's own store.
				}
			})

			return 'function' === typeof unsubscribe ? unsubscribe : noop
		} catch (_e) {
			return noop
		}
	}
}

export { isWireframeView }