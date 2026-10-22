import { getAssessmentColor } from '@/vue/plugins/tru-seo/helpers/assessmentColors'
import { buildHighlightStateCss } from '@/vue/plugins/tru-seo/highlighter/tinymce'
import { buildTextStream, overlapsSkipped, toNodeOffset } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { findSentenceRanges } from '@/vue/utils/regex'
import { getFormatClassName } from '@/vue/plugins/tru-seo/highlighter/wpDataStore'

// The paint handle, kept separate from the per-analyzer class so a clear pass never has to
// know which analyzers were active when the marks were painted.
const MARK_ATTRIBUTE = 'data-aioseo-highlight'

// Same id shape the block editor's RichText format writes, because the composable resolves a
// mark's live node by `[id$="-${mark.id}"]` and re-anchors a clicked one by
// `id.endsWith('-' + m.id)` ({@see TruSeoHighlighter.js}). A different shape would paint
// marks the popover could never find again.
const MARK_ID_PREFIX = 'aioseo-highlight-'

const STYLE_ATTRIBUTE = 'data-aioseo-highlight-styles'

const NBSP_PATTERN = new RegExp(String.fromCharCode(160), 'g')

/**
 * Folds U+00A0 to a plain space, and nothing else.
 *
 * NOTE: not `normalizeWhitespaces`, whose `&nbsp;` 6→1 rewrite would shift live stream offsets.
 *
 * @param {string} text The text to fold.
 * @returns {string} The folded text, character-for-character the same length.
 */
export const foldNbsp = (text) => String(text ?? '').replace(NBSP_PATTERN, ' ')

const findOccurrences = (haystack, needle) => {
	const found = []
	let from    = haystack.indexOf(needle)

	while (-1 !== from) {
		found.push(from)
		from = haystack.indexOf(needle, from + 1)
	}

	return found
}

// A model-sourced unit's text and its paint node differ only by template whitespace, so the
// projection must appear as one contiguous run; the difference is one constant for the unit.
// Found by indexOf, not trimmed: `trimEnd()` would eat a trailing U+00A0, and can't absorb a
// leading indent.
const resolveConstantOffset = (rendered, projected) => {
	if (!projected) {
		return null
	}

	const index = rendered.indexOf(projected)
	if (0 > index) {
		return null
	}

	if ('' !== rendered.slice(0, index).trim() || '' !== rendered.slice(index + projected.length).trim()) {
		return null
	}

	return index
}

// The exact text a mark's range covered when built. The only check that can tell a mark from
// one whose element was edited under it, since the offsets shift together. Null without one.
const markText = (mark) => ('string' === typeof mark?.sentence && mark.sentence ? mark.sentence : null)

// Reached only when the constant offset does not hold. A guessed occurrence would highlight one
// phrase and fix another, so it is taken only when it is the only one or the whole unit agrees on
// one shift; anything else is dropped and counted.
const alignByOccurrence = ({ rendered, projected, marks, skipped }) => {
	const aligned  = []
	const dropped  = []
	const deferred = []
	const deltas   = new Set()

	for (const mark of marks) {
		// The mark's OWN text in preference to whatever now sits at its offsets. The two agree
		// while the element is unchanged; when it has changed under a stale mark, searching for
		// the sentence the issue is actually about can still place it, and searching for the
		// garbled slice would confidently place a phrase that no longer exists.
		const sentence = markText(mark)
		const needle   = sentence ?? projected.slice(mark.range.start, mark.range.end)
		if (!needle) {
			dropped.push({ mark, reason: 'empty-range' })

			continue
		}

		// Searched under the rule that found it: a word on word boundaries, so 'cat' never matches
		// inside 'concatenate'; an arbitrary slice keeps the substring search.
		// Never a copy the analyzer drops: one inside `<code>` would claim the mark, or make it ambiguous.
		const occurrences = (sentence
			? findSentenceRanges(rendered, sentence).map(found => found.start)
			: findOccurrences(rendered, needle)
		).filter(start => !overlapsSkipped({ start, end: start + needle.length }, skipped))

		if (!occurrences.length) {
			dropped.push({ mark, reason: 'no-match' })

			continue
		}

		if (1 === occurrences.length) {
			deltas.add(occurrences[0] - mark.range.start)
			aligned.push({ mark, start: occurrences[0], end: occurrences[0] + needle.length })

			continue
		}

		deferred.push({ mark, needle, occurrences })
	}

	const consensus = 1 === deltas.size ? [ ...deltas ][0] : null

	for (const entry of deferred) {
		const start = entry.mark.range.start + consensus

		if (null === consensus || !entry.occurrences.includes(start)) {
			dropped.push({ mark: entry.mark, reason: 'ambiguous' })

			continue
		}

		aligned.push({ mark: entry.mark, start, end: start + entry.needle.length })
	}

	return { aligned, dropped, offset: null }
}

/**
 * Maps a unit's marks from the projection they were measured in onto the rendered node's own
 * text stream.
 *
 * @param {Object} options           The options object.
 * @param {string} options.rendered  The paint node's text stream, nbsp-folded.
 * @param {string} options.projected The unit's text projection, in the space `mark.range` indexes.
 * @param {Array}  options.marks     Store marks belonging to this unit.
 * @param {Array}  [options.skipped] Ranges of `rendered` the analyzer drops ({@see textStream.js}).
 * @returns {{aligned: Array, dropped: Array, offset: ?number}} Placeable marks, refused marks with a reason, and the constant offset when one held.
 */
export const alignProjectedMarks = ({ rendered, projected, marks, skipped = [] }) => {
	if (!Array.isArray(marks) || !marks.length) {
		return { aligned: [], dropped: [], offset: null }
	}

	const offset = resolveConstantOffset(rendered, projected)
	if (null === offset) {
		return alignByOccurrence({ rendered, projected, marks, skipped })
	}

	const aligned = []
	const dropped = []

	for (const mark of marks) {
		const start = mark.range?.start
		const end   = mark.range?.end

		if (!(0 <= start) || !(start < end) || end > projected.length) {
			dropped.push({ mark, reason: 'out-of-range' })

			continue
		}

		// The offset holding proves the PROJECTION still maps onto the node — not that this
		// mark still describes what sits at its offsets. An element edited since analysis last
		// ran has a fresh projection that matches its fresh node perfectly, so the offset is
		// found and a stale range would paint straight over unrelated characters: 'quick' at
		// [4,9) of "The quick brown fox" becomes 'ick b' once the user shortens "The" to "A".
		const expected = markText(mark)
		if (null !== expected && projected.slice(start, end) !== expected) {
			// Recoverable when the sentence simply moved: hand it to the occurrence search,
			// which places it only if it is unambiguous and drops it otherwise.
			const relocated = alignByOccurrence({ rendered, projected, marks: [ mark ], skipped })

			aligned.push(...relocated.aligned)
			// Kept apart because they want different fixes, the same distinction `decorate`
			// draws between unknown-element and not-writable: gone means the analysis is behind
			// the content, ambiguous means the sentence is there but this mark can no longer say
			// which one it meant.
			dropped.push(...relocated.dropped.map(entry => ({
				mark   : entry.mark,
				reason : 'ambiguous' === entry.reason ? 'stale-text-ambiguous' : 'stale-text-missing'
			})))

			continue
		}

		aligned.push({ mark, start: start + offset, end: end + offset })
	}

	return { aligned, dropped, offset }
}

const findPaintedMark = (root, markId) => {
	try {
		return root.querySelector(`[id="${MARK_ID_PREFIX}${markId}"]`) || null
	} catch (_e) {
		return null
	}
}

// The text nodes a wrap creates by splitting a builder's own node. Only these are merged back on
// clear, into the node they were split from, so that node keeps its identity: a React canvas holds
// it and later writes its `nodeValue` in place.
const splitFragments = new WeakSet()

const collectPieces = (segments, entry) => segments.filter(
	segment => segment.start < entry.end && segment.end > entry.start
)

// A wrap earlier in this pass split the text node a cached segment points at, so the
// segment's offsets no longer address what it claims to. Cheap to test and cheap to be
// wrong about — a false positive only costs one extra tree walk.
const hasStaleSegment = (pieces, entry) => pieces.some(segment => {
	if (segment.element) {
		return !segment.node?.parentNode
	}

	return (segment.node?.length ?? 0) < toNodeOffset(segment, Math.min(entry.end, segment.end))
})

// One wrap per contributing text node, never one Range across them: `surroundContents` throws
// on a partial element and the extract-and-insert fallback tears it. One mark can therefore paint
// as several elements sharing an id, as Gutenberg's formats already do.
const wrapSegment = (doc, segment, start, end, mark) => {
	// A mark reaching across a block boundary collects that boundary's segment as one of its
	// pieces, and there is nothing there to wrap: the segment names the block the boundary
	// precedes, so the emoji arm below would put a `<mark>` around that entire paragraph. The
	// mark still paints, one fragment per block, which is the shape a multi-run mark already has.
	if (segment.boundary) {
		return null
	}

	const range = doc.createRange()

	if (segment.element) {
		// An emoji image contributes its `alt` characters atomically — the range covers the
		// whole element or none of it.
		range.setStartBefore(segment.node)
		range.setEndAfter(segment.node)
	} else {
		range.setStart(segment.node, toNodeOffset(segment, start))
		range.setEnd(segment.node, toNodeOffset(segment, end))
	}

	if (range.collapsed) {
		return null
	}

	const element = doc.createElement('mark')
	element.className = getFormatClassName(mark.analyzer)
	element.id        = `${MARK_ID_PREFIX}${mark.id}`
	element.setAttribute(MARK_ATTRIBUTE, '')

	try {
		range.surroundContents(element)
	} catch (_e) {
		return null
	}

	// The split keeps the original node as the prefix, and creates the wrapped copy and the suffix.
	if (!segment.element) {
		for (const fragment of [ ...element.childNodes, element.nextSibling ]) {
			if (Node.TEXT_NODE === fragment?.nodeType) {
				splitFragments.add(fragment)
			}
		}
	}

	return element.parentNode ? element : null
}

/**
 * Wraps each aligned range in the node with a `<mark>` carrying its analyzer's class.
 *
 * Already-painted marks are left alone rather than wrapped again, so a repaint driven by a
 * re-render that did not actually change the node costs nothing and cannot nest a mark
 * inside itself.
 *
 * @param {Object}      options       The options object.
 * @param {HTMLElement} options.node  The node to paint into.
 * @param {Array}       options.marks Aligned entries — `{mark, start, end}` in the node's own stream space.
 * @returns {{painted: Array, unpainted: Array}} One `{mark, node}` per painted or already-present mark, and the marks no wrap landed for.
 */
export const decorateNode = ({ node, marks }) => {
	const painted   = []
	const unpainted = []

	if (!node?.ownerDocument || !Array.isArray(marks) || !marks.length) {
		return { painted, unpainted: Array.isArray(marks) ? marks.map(entry => entry?.mark).filter(Boolean) : [] }
	}

	const doc = node.ownerDocument

	// Widest first, so a mark inside another (a misspelling inside a flagged sentence) nests
	// innermost, as the block editor's formats do. Same-offset underlines draw innermost on top,
	// so the reverse order hides the word's highlight under the sentence's.
	// NOTE: the order is not the correctness guard for the split nodes; the re-walk below is.
	const ordered = [ ...marks ].sort((a, b) => ((b.end - b.start) - (a.end - a.start)) || (b.start - a.start))
	let segments  = buildTextStream(node).segments

	for (const entry of ordered) {
		if (!entry?.mark || !(entry.start < entry.end)) {
			if (entry?.mark) {
				unpainted.push(entry.mark)
			}

			continue
		}

		const existing = findPaintedMark(node, entry.mark.id)
		if (existing) {
			painted.push({ mark: entry.mark, node: existing })

			continue
		}

		let pieces = collectPieces(segments, entry),
			first  = null

		if (!pieces.length || hasStaleSegment(pieces, entry)) {
			segments = buildTextStream(node).segments
			pieces   = collectPieces(segments, entry)
		}

		// Descending again, for the same reason one level down: wrapping this mark's own tail
		// piece first leaves its earlier pieces untouched.
		for (let index = pieces.length - 1; 0 <= index; index--) {
			const segment = pieces[index]
			const element = wrapSegment(
				doc,
				segment,
				Math.max(entry.start, segment.start),
				Math.min(entry.end, segment.end),
				entry.mark
			)

			if (element) {
				first = element
			}
		}

		if (first) {
			// The last wrap made is the earliest in document order, which is the fragment a
			// popover should anchor to.
			painted.push({ mark: entry.mark, node: first })

			continue
		}

		// Every wrap failed — a range that resolved against the projection but cannot be
		// anchored in the live DOM. Reported rather than silently absent, so a mark that cannot
		// be painted is always counted.
		unpainted.push(entry.mark)
	}

	return { painted, unpainted }
}

const mergeSplitFragments = (parent) => {
	for (const node of [ ...parent.childNodes ]) {
		const previous = node.previousSibling

		if (splitFragments.has(node) && Node.TEXT_NODE === previous?.nodeType) {
			previous.appendData(node.data)
			parent.removeChild(node)
		}

		splitFragments.delete(node)
	}
}

/**
 * Unwraps every mark this module painted under `root`.
 *
 * @param {HTMLElement} root The subtree to clear.
 * @returns {number} How many marks were removed.
 */
export const clearDecorations = (root) => {
	if ('function' !== typeof root?.querySelectorAll) {
		return 0
	}

	let removed = 0

	try {
		const parents = new Set()

		for (const element of root.querySelectorAll(`[${MARK_ATTRIBUTE}]`)) {
			const parent = element.parentNode
			if (!parent) {
				continue
			}

			while (element.firstChild) {
				parent.insertBefore(element.firstChild, element)
			}

			parent.removeChild(element)
			parents.add(parent)
			removed++
		}

		// Unwrapping leaves the freed text beside its siblings as separate nodes. Merging
		// them back keeps the next stream one segment per logical run, so a range spanning
		// the seam does not resolve across two nodes and lose `surroundContents`.
		// NOTE: not `normalize()`, which drops an emptied original or merges it into a
		// preceding text node, detaching the node a React canvas still updates.
		for (const parent of parents) {
			mergeSplitFragments(parent)
		}
	} catch (_e) {
		return removed
	}

	return removed
}

const isMarkElement = (node) => Node.ELEMENT_NODE === node?.nodeType && node.hasAttribute(MARK_ATTRIBUTE)

// The element a selection boundary is measured in: the nearest ancestor that is not one of our
// marks, so the measure survives that mark being unwrapped or painted again.
const boundaryHost = (node) => {
	let host = Node.ELEMENT_NODE === node?.nodeType ? node : node?.parentNode

	while (host && (Node.ELEMENT_NODE !== host.nodeType || isMarkElement(host))) {
		host = host.parentNode
	}

	return host || null
}

// `isContentEditable` is the browser's answer, inheritance included; the selector stands in for a
// DOM without it.
const isEditable = (element) => {
	if ('boolean' === typeof element.isContentEditable) {
		return element.isContentEditable
	}

	return !!element.closest?.('[contenteditable=""],[contenteditable="true"],[contenteditable="plaintext-only"]')
}

const captureBoundary = (doc, node, offset) => {
	const host = boundaryHost(node)
	if (!host || !isEditable(host)) {
		return null
	}

	const range = doc.createRange()
	range.setStart(host, 0)
	range.setEnd(node, offset)

	return { host, offset: range.toString().length }
}

const captureSelection = (doc) => {
	try {
		const selection = doc?.getSelection?.()
		if (!selection?.rangeCount || !selection.anchorNode) {
			return null
		}

		const anchor = captureBoundary(doc, selection.anchorNode, selection.anchorOffset)
		const focus  = captureBoundary(doc, selection.focusNode, selection.focusOffset)
		if (!anchor || !focus) {
			return null
		}

		return {
			anchor,
			focus,
			points : [ selection.anchorNode, selection.anchorOffset, selection.focusNode, selection.focusOffset ]
		}
	} catch (_e) {
		return null
	}
}

// Walks the host's text back to the saved offset. At a seam the side outside a mark wins, so a
// keystroke there extends the sentence rather than the highlight.
const resolveTextPosition = (doc, { host, offset }) => {
	const walker = doc.createTreeWalker(host, NodeFilter.SHOW_TEXT)
	let total = 0,
		last  = null

	while (walker.nextNode()) {
		const text = walker.currentNode
		if (!text.length) {
			continue
		}

		if (total + text.length > offset) {
			return [ text, offset - total ]
		}

		if (total + text.length === offset) {
			let next = walker.nextNode()
			while (next && !next.length) {
				next = walker.nextNode()
			}

			return next && isMarkElement(text.parentNode) && !isMarkElement(next.parentNode)
				? [ next, 0 ]
				: [ text, text.length ]
		}

		total += text.length
		last   = text
	}

	return last ? [ last, last.length ] : [ host, 0 ]
}

const restoreSelection = (doc, saved) => {
	try {
		const selection = doc.getSelection()
		const [ anchorNode, anchorOffset, focusNode, focusOffset ] = saved.points

		// Untouched nodes keep their selection; re-setting it would only fire selectionchange.
		if (
			anchorNode.isConnected && focusNode.isConnected &&
			selection.anchorNode === anchorNode && selection.anchorOffset === anchorOffset &&
			selection.focusNode === focusNode && selection.focusOffset === focusOffset
		) {
			return
		}

		selection.setBaseAndExtent(...resolveTextPosition(doc, saved.anchor), ...resolveTextPosition(doc, saved.focus))
	} catch (_e) {
		// The browser's own fallback position stays; the pass itself already succeeded.
	}
}

/**
 * Runs a DOM pass and puts an editable selection back where it was, measured as text offsets.
 *
 * Unwrapping or painting a mark replaces the text node a caret sits in, and a selection whose node
 * leaves the document parks on the parent element -- measured in Divi's inline editors: the next
 * keystroke then typed in front of the first mark instead of where the user was. A selection
 * outside any editable element is left to the browser.
 *
 * @param {Document} doc  The document whose selection to keep.
 * @param {Function} pass The DOM pass to run.
 * @returns {*} Whatever the pass returns.
 */
export const preserveSelection = (doc, pass) => {
	const saved = captureSelection(doc)

	try {
		return pass()
	} finally {
		if (saved) {
			restoreSelection(doc, saved)
		}
	}
}

/**
 * Injects the per-analyzer mark rules into a document, once per analyzer set.
 *
 * A builder paints into its own iframe document, which the composable's main-document
 * stylesheet cannot reach — without this the marks are in the DOM and invisible.
 *
 * @param {Document} doc       The document to inject into.
 * @param {Array}    analyzers Active analyzer identifiers.
 * @returns {void}
 */
export const ensureDecorationStyles = (doc, analyzers) => {
	if (!doc?.head || !Array.isArray(analyzers) || !analyzers.length) {
		return
	}

	try {
		// Sorted, because the caller collects analyzers into an insertion-ordered Set as it
		// paints: the same set arriving in a different paint order would otherwise miss the
		// cache and rewrite the stylesheet on every pass.
		const key      = [ ...analyzers ].sort().join(',')
		const existing = doc.head.querySelector(`style[${STYLE_ATTRIBUTE}]`)
		if (existing?.getAttribute(STYLE_ATTRIBUTE) === key) {
			return
		}

		existing?.remove()

		const style = doc.createElement('style')
		style.setAttribute(STYLE_ATTRIBUTE, key)
		// `mark.<class>` is the block editor's own selector shape ({@see
		// highlighter/blockEditor.js:buildBlockFormatCss}), rebuilt here rather than
		// imported so a builder adapter does not drag in the Gutenberg paint module.
		style.textContent = analyzers
			.map(analyzer => buildHighlightStateCss(`mark.${getFormatClassName(analyzer)}`, getAssessmentColor(analyzer)))
			.join('\n')

		doc.head.appendChild(style)
	} catch (_e) {
		// A document that cannot take a stylesheet still gets its marks; they are just unstyled.
	}
}

export { MARK_ATTRIBUTE, MARK_ID_PREFIX }