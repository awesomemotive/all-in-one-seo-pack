import { blockElements } from '@/app/tru-seo/languageProcessing/helpers/html/html'
import { IGNORED_CLASSES, IGNORED_TAGS } from '@/app/tru-seo/languageProcessing/helpers/html/ignoredElements'
import { HIGHLIGHT_CLASS_PREFIX } from '@/vue/plugins/tru-seo/highlighter/wpDataStore'

// The analyzer's own block list ({@see languageProcessing/helpers/html/html.js:getBlocks}), never a
// copy: a boundary the analyzer doesn't split at would leave its sentences unfindable here.
export const BLOCK_TAGS = new Set(blockElements.map(tag => tag.toUpperCase()))

// A newline, not a space: `getSentences` splits blocks on `\n`, so it separates paragraphs even
// when the first doesn't end in punctuation.
const BLOCK_SEPARATOR = '\n'

// The two characters `normalizeWhitespaces` deletes. Skipped here too, so the stream stays the
// same length as the analyzed string its mark offsets index.
const ZERO_WIDTH = /[\u200B\uFEFF]/g

// Every element whose text the analyzer drops ({@see htmlParser.js}). Its text stays in the stream,
// so offsets are unchanged, but a mark built there would claim a sentence found in the visible text.
export const ANALYZER_SKIPPED_SELECTOR = [ ...IGNORED_TAGS, ...IGNORED_CLASSES.map(className => `.${className}`) ].join(',')

const isAnalyzerSkipped = (element) => !!element?.closest?.(ANALYZER_SKIPPED_SELECTOR)

// Merged into the previous range when the two touch, so a skipped element split across text nodes
// stays one range.
const addRange = (ranges, start, end) => {
	if (end <= start) {
		return
	}

	const last = ranges[ranges.length - 1]
	if (last && last.end >= start) {
		last.end = Math.max(last.end, end)

		return
	}

	ranges.push({ start, end })
}

/**
 * Whether a range overlaps text the analyzer drops.
 *
 * @param {{start: number, end: number}} range   The range.
 * @param {Array}                        skipped The `skipped` ranges of the same text.
 * @returns {boolean} Whether any character of the range is skipped.
 */
export const overlapsSkipped = (range, skipped) => (skipped || []).some(other => other.start < range.end && other.end > range.start)

/**
 * Resolves a stream offset to an offset inside the segment's own node.
 *
 * @param {Object} segment      A `buildTextStream` segment.
 * @param {number} streamOffset An offset into the stream, within the segment.
 * @returns {number} The offset inside `segment.node`.
 */
export const toNodeOffset = (segment, streamOffset) => (segment.nodeOffset || 0) + streamOffset - segment.start

/**
 * Whether a node is a WordPress emoji image (`<img class="emoji">`), which
 * `wp-emoji`/Twemoji substitutes for an emoji or special glyph in the editor
 * DOM. Its `alt` holds the original character(s).
 *
 * @param {Node} node The node to test.
 * @returns {boolean} True for an emoji image element.
 */
const isEmojiImage = (node) => {
	return 'IMG' === node.tagName && node.classList?.contains('emoji')
}

// Tag names only, never computed style: resolving block-ness through CSS would force the layout
// reflow this module exists to avoid, and an editor canvas restyles freely.
const startsBlock = (node) => BLOCK_TAGS.has(node.tagName)

/**
 * Builds a canonical character stream from a root element's text nodes.
 *
 * `text` is the concatenation of every descendant Text node in document
 * order — the order a `SHOW_TEXT` TreeWalker visits. Emoji images
 * (`<img class="emoji">`, injected by `wp-emoji`/Twemoji in place of an
 * emoji or special glyph) contribute their `alt` character(s) too, so the
 * stream stays aligned with the analyzer's sentence text (which keeps the
 * original character). The start of a block-level child contributes one
 * `\n`, so prose on either side of it reads as two blocks rather than
 * one run-on — the same boundary the analyzer's own `getBlocks` splits an
 * HTML paper at. That `\n` is emitted only where the stream does not
 * already end in one, so nested block starts collapse to a single
 * separator and a newline the DOM itself supplied is never doubled.
 * `segments` records each contributor's `[start, end)` range within the
 * stream, so a stream offset can be resolved to a DOM position via
 * `rangeFromStream` without re-walking the DOM.
 *
 * Unlike `outerText`, this stream is neither CSS-aware nor does it force a
 * layout reflow: block-ness is decided by tag name against the analyzer's
 * own list, and every character — the synthetic ones included — is covered
 * by a segment, so offsets matched in `text` resolve to a DOM Range
 * without drift.
 *
 * NOTE: a range edge on a block boundary comes back collapsed, which callers read differently;
 * no analyzer sentence can carry one, since `getSentences` splits blocks on it.
 *
 * @param {Node} root The element to extract text from.
 * @returns {{ text: string, segments: Array<{node: Node, start: number, end: number, element?: boolean, boundary?: boolean}>, skipped: Array<{start: number, end: number}> }} The text stream, its segment map, and the ranges the analyzer drops.
 */
export const buildTextStream = (root) => {
	const segments = []
	const skipped  = []
	let text       = ''

	if (!root || !root.ownerDocument) {
		return { text, segments, skipped }
	}

	const walker = root.ownerDocument.createTreeWalker(root, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT)
	while (walker.nextNode()) {
		const node = walker.currentNode

		if (Node.TEXT_NODE === node.nodeType) {
			// One segment per run between zero-width characters, each recording where it starts
			// inside the node.
			const data      = node.textContent
			const isSkipped = isAnalyzerSkipped(node.parentElement)
			let runStart    = 0

			for (const { index } of [ ...data.matchAll(ZERO_WIDTH), { index: data.length } ]) {
				if (index > runStart) {
					segments.push({ node, start: text.length, end: text.length + index - runStart, nodeOffset: runStart })
					if (isSkipped) {
						addRange(skipped, text.length, text.length + index - runStart)
					}

					text += data.slice(runStart, index)
				}

				runStart = index + 1
			}

			continue
		}

		if (isEmojiImage(node)) {
			const alt = node.getAttribute('alt') || ''
			if (alt.length) {
				segments.push({ node, start: text.length, end: text.length + alt.length, element: true })
				if (isAnalyzerSkipped(node)) {
					addRange(skipped, text.length, text.length + alt.length)
				}

				text += alt
			}

			continue
		}

		// Deduped against a newline only: a trailing space is not a sentence break to `getSentences`.
		if (!startsBlock(node) || !text.length || text.endsWith(BLOCK_SEPARATOR)) {
			continue
		}

		// `boundary` tells this apart from an emoji image: the node is the block it precedes, so
		// treating it as an emoji would wrap or rewrite that whole block.
		segments.push({
			node,
			start    : text.length,
			end      : text.length + BLOCK_SEPARATOR.length,
			element  : true,
			boundary : true
		})
		text += BLOCK_SEPARATOR
	}

	return { text, segments, skipped }
}

// The two attributes the link researches read: `getLinkType` classifies by `href` and
// `checkNofollow` parses `rel`. Every other attribute except `class` is left out, so a builder's
// own marker attribute cannot reach the analyzed string.
const ANCHOR_ATTRIBUTES = [ 'href', 'rel' ]

// Media the caller collects separately. Their tags never come from here, so a projection cannot
// count an image, a video or an embed that the collector already emitted
// ({@see standalone/page-builders/elementor/helpers.js:getMediaMarkup}).
const MEDIA_TAGS = new Set([ 'IMG', 'IFRAME', 'VIDEO' ])

// No closing tag, and no children to walk. `</br>` is not a tag `getBlocks` has a rule for, so
// emitting one leaves the rest of the block tokenizing as an unmatched-tag run.
const VOID_TAGS = new Set([
	'AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'LINK', 'META', 'PARAM', 'SOURCE', 'TRACK', 'WBR'
])

const escapeAttribute = (value) => value
	.replace(/&/g, '&amp;')
	.replace(/"/g, '&quot;')
	// `>` too, or an href holding one truncates the open tag and `getAnchorsFromText`'s `[^>]+`
	// bounds the anchor in the wrong place.
	.replace(/>/g, '&gt;')

// Text serialized the way `innerHTML` serializes a text node; raw, a `<` would open a tag and
// swallow the rest of the sentence.
export const escapeText = (value) => value
	.replace(/&/g, '&amp;')
	.replace(/</g, '&lt;')
	.replace(/>/g, '&gt;')

// AIOSEO's own highlight decoration ({@see editors/domDecorator.js:decorateNode}), recognized by
// class rather than the `mark` tag, because a `<mark>` the author wrote is content.
const isOwnDecoration = (node) => {
	if (!node.classList?.length) {
		return false
	}

	for (const name of node.classList) {
		if (name.startsWith(HIGHLIGHT_CLASS_PREFIX)) {
			return true
		}
	}

	return false
}

const attribute = (node, name) => {
	const value = node.getAttribute(name)

	return value ? ` ${name}="${escapeAttribute(value)}"` : ''
}

// `class` on every element, unconditionally: the analyzer's filters decide by it, so filtering
// here would drift from lists this file doesn't own. `omitClass` drops individual names.
// NOTE: never a prefix matcher like `elementor-`; the analyzer skips many `elementor-` content
// classes, and dropping them would un-hide those subtrees.
const classAttribute = (node, omitClass) => {
	if (!omitClass) {
		return attribute(node, 'class')
	}

	const kept = [ ...node.classList ].filter(name => !omitClass(name))
	if (kept.length === node.classList.length) {
		return attribute(node, 'class')
	}

	return kept.length ? ` class="${escapeAttribute(kept.join(' '))}"` : ''
}

const openTag = (node, omitClass) => {
	const name       = node.tagName.toLowerCase()
	const attributes = ('a' === name ? ANCHOR_ATTRIBUTES.map(entry => attribute(node, entry)).join('') : '') +
		classAttribute(node, omitClass)

	return `<${name}${attributes}>`
}

/**
 * Builds the same stream `buildTextStream` does, with the element structure kept.
 *
 * NOTE: `text` equals `normalizeText(buildTextStream(root).text)` unless `substitute` is used, so a
 * caller can check that a sentence found in the markup is findable in the plain projection too.
 *
 * @param {Node}      root                 The element to project.
 * @param {Function}  normalizeText        Applied to each text contribution.
 * @param {Object}    [options]            Projection hooks.
 * @param {?Function} [options.substitute] Given an element, returns `{markup, text}` to emit in
 *                                         place of that whole element, or null to walk it. A
 *                                         substituted element is emitted as its OWN block, so the
 *                                         caller's text cannot fuse with the text around it.
 * @returns {{markup: string, text: string, skipped?: Array<{start: number, end: number}>}} The markup projection, the text it carries, and the ranges of that text the analyzer drops (absent for a node with no document).
 */
export const buildMarkupProjection = (root, normalizeText = (text) => text, { substitute = null, omitClass = null } = {}) => {
	const skipped = []
	let markup    = '',
		text      = ''

	if (!root || !root.ownerDocument) {
		return { markup, text }
	}

	const append = (chunk) => {
		markup += chunk
		text   += chunk
	}

	// Only the markup is escaped; `text` stays the coordinate space marks are measured in.
	const appendText = (chunk, isSkipped) => {
		if (isSkipped) {
			addRange(skipped, text.length, text.length + chunk.length)
		}

		markup += escapeText(chunk)
		text   += chunk
	}

	// Deduped exactly as `buildTextStream` does it.
	const separate = () => {
		if (text.length && !text.endsWith(BLOCK_SEPARATOR)) {
			append(BLOCK_SEPARATOR)
		}
	}

	const visit = (node, isRoot, inSkipped) => {
		if (Node.TEXT_NODE === node.nodeType) {
			appendText(normalizeText(node.textContent.replace(ZERO_WIDTH, '')), inSkipped)

			return
		}

		if (Node.ELEMENT_NODE !== node.nodeType) {
			return
		}

		// The root by its ancestors too, as `buildTextStream` decides it.
		const isSkipped = inSkipped || (isRoot ? isAnalyzerSkipped(node) : node.matches(ANALYZER_SKIPPED_SELECTOR))

		if (isEmojiImage(node)) {
			appendText(normalizeText(node.getAttribute('alt') || ''), isSkipped)

			return
		}

		// Our own mark is emitted as its children only, before the substitution hook, so it never
		// reaches the analyzed markup or the AI endpoints.
		if (isOwnDecoration(node)) {
			for (const child of node.childNodes) {
				visit(child, false, isSkipped)
			}

			return
		}

		const replacement = substitute?.(node)
		if (replacement) {
			if (!replacement.markup && !replacement.text) {
				return
			}

			separate()
			if (isSkipped) {
				addRange(skipped, text.length, text.length + String(replacement.text || '').length)
			}

			markup += replacement.markup
			text   += replacement.text
			append(BLOCK_SEPARATOR)

			return
		}

		// The root is the stream's own frame, and `buildTextStream`'s TreeWalker never visits it,
		// so it can neither open a block nor be deduped against one.
		if (!isRoot && startsBlock(node)) {
			separate()
		}

		const media = MEDIA_TAGS.has(node.tagName)
		if (!media) {
			markup += openTag(node, omitClass)
		}

		if (VOID_TAGS.has(node.tagName)) {
			return
		}

		for (const child of node.childNodes) {
			visit(child, false, isSkipped)
		}

		if (!media) {
			markup += `</${node.tagName.toLowerCase()}>`
		}
	}

	visit(root, true, false)

	return { markup, text, skipped }
}

/**
 * Binary-searches the segments array for the segment containing `offset`.
 * Returns -1 if the offset falls outside the stream.
 *
 * @param {Array}  segments Segments from `buildTextStream`.
 * @param {number} offset   Stream offset to locate.
 * @returns {number} The segment index, or -1 if not found.
 */
const findSegmentIndex = (segments, offset) => {
	let lo = 0,
		hi = segments.length - 1

	while (lo <= hi) {
		const mid = (lo + hi) >> 1
		const seg = segments[mid]
		if (offset < seg.start) {
			hi = mid - 1
		} else if (offset >= seg.end) {
			lo = mid + 1
		} else {
			return mid
		}
	}

	return -1
}

/**
 * Anchors one edge of a range at a stream offset within a segment.
 *
 * Emoji-image segments (`element: true`) are atomic — the offset falls
 * before or after the whole image — so the edge is set with
 * `setStart/EndBefore/After`. A block-boundary segment (`boundary: true`)
 * has no extent at all and resolves to the one position it marks. Text
 * segments use the character offset.
 *
 * False signals a stale segment, so the caller can bail to a collapsed
 * range: a text node shorter than the offset it is asked for, or an
 * element-backed node no longer attached to a parent.
 *
 * @param {Range}   range   The range to anchor.
 * @param {Object}  segment The target segment from `buildTextStream`.
 * @param {number}  offset  The stream offset to anchor at.
 * @param {boolean} isStart Whether this is the range's start edge.
 * @returns {boolean} True on success, false if the segment is stale.
 */
const anchorEdge = (range, segment, offset, isStart) => {
	if (segment.element) {
		// `setStart/EndBefore/After` throws on a node with no parent, so a detached one bails here.
		if (!segment.node?.parentNode) {
			return false
		}

		// A boundary is a position, not a span: both edges sit just before the block it precedes.
		if (segment.boundary) {
			isStart ? range.setStartBefore(segment.node) : range.setEndBefore(segment.node)

			return true
		}

		const after = isStart ? offset > segment.start : offset >= segment.end
		if (isStart) {
			after ? range.setStartAfter(segment.node) : range.setStartBefore(segment.node)
		} else {
			after ? range.setEndAfter(segment.node) : range.setEndBefore(segment.node)
		}

		return true
	}

	const localOffset = toNodeOffset(segment, offset)
	if ((segment.node?.length ?? 0) < localOffset) {
		return false
	}

	isStart ? range.setStart(segment.node, localOffset) : range.setEnd(segment.node, localOffset)

	return true
}

/**
 * Builds a DOM Range from stream offsets using a `buildTextStream` map.
 *
 * `endOffset` is exclusive (matches `match.index + match[0].length`).
 * Returns an empty (collapsed) range if offsets fall outside the stream.
 *
 * Segment node references stay valid only until that segment's text node
 * is split (e.g. by `Range.surroundContents`). Callers that annotate
 * multiple ranges per parent should iterate marks in descending offset
 * order — splitting a later segment leaves earlier segments untouched,
 * so unprocessed segments remain resolvable.
 *
 * @param {Document} doc         Owning document for the range.
 * @param {Array}    segments    Segments from `buildTextStream`.
 * @param {number}   startOffset Stream offset for the range start (inclusive).
 * @param {number}   endOffset   Stream offset for the range end (exclusive).
 * @returns {Range} A DOM Range spanning the stream offsets.
 */
export const rangeFromStream = (doc, segments, startOffset, endOffset) => {
	const range = doc.createRange()
	if (!segments.length) {
		return range
	}

	const startIdx = findSegmentIndex(segments, startOffset)
	const endIdx   = findSegmentIndex(segments, Math.max(endOffset - 1, startOffset))
	if (0 > startIdx || 0 > endIdx) {
		return range
	}

	// Bail if a cached node has been mutated (split, emptied, or detached)
	// since `buildTextStream` ran — using stale offsets would throw
	// `IndexSizeError`. A collapsed range lets the caller fall back to a
	// fresh live-DOM walk.
	if (
		!anchorEdge(range, segments[startIdx], startOffset, true) ||
		!anchorEdge(range, segments[endIdx], endOffset, false)
	) {
		return doc.createRange()
	}

	return range
}