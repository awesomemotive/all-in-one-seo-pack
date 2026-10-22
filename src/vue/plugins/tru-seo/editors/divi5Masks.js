import { buildTextStream } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { escapeRegex } from '@/vue/utils/regex'

import { parseInert } from './storedHtmlEdit'

// Characters a Divi 5 attribute stores but a reader never sees as written, masked out of both the
// projection and every write so neither can treat them as prose. Kept free of `divi.data`, like
// `divi5Units.js`, so the rules are testable without a builder.
//
//   dynamic -- `$variable({...})$`, which Divi renders as the RESOLVED value (a post title, a date).
//              Its JSON reads as words, and rewriting one character of it leaves a token Divi no
//              longer recognises, so the canvas prints the raw JSON.
//   marker  -- the `+`/`-` a Pricing Table content line starts with, which Divi strips to choose the
//              "included"/"excluded" style. Left in, `+Feature` reaches the spelling check as one
//              word and a fix drops the marker.

const DYNAMIC_TOKEN = /\$variable\(.*?\)\$/gs

// Divi splits Pricing Table content into lines on these before it reads each line's first
// character ({@see module-library.js} `et_pb_pricing`); `<li>` is how a list the user typed
// arrives there.
const LINE_BREAK_TAG = /^<\/?(?:p|br|li|ul|ol)(?:[\s/>])/i

const SENTINEL_ATTRIBUTE = 'data-aioseo-divi-mask'

const sentinel = (index) => `<span ${SENTINEL_ATTRIBUTE}="${index}"></span>`

// Holds a dynamic token's place through the projection's whitespace pass, so the offset it sat at
// is known when its sentence has to be found in a canvas that renders the resolved value. A
// private-use character, stripped before anything is analysed.
const GAP = '\uE000'

// The `[start, end)` ranges of every masked piece in a run of text, in order.
const findPieces = (text, { lineMarkers = false, atLineStart = true } = {}) => {
	const pieces = []

	for (const match of String(text).matchAll(DYNAMIC_TOKEN)) {
		pieces.push({ start: match.index, end: match.index + match[0].length, kind: 'dynamic' })
	}

	if (lineMarkers) {
		const pattern = /(^|\n)([ \t]*)([+-])/g
		let match

		while (null !== (match = pattern.exec(text))) {
			// The first line of a run only counts when the run itself starts a line.
			if (!match.index && !match[1] && !atLineStart) {
				continue
			}

			const at = match.index + match[1].length + match[2].length
			if (!pieces.some(piece => at >= piece.start && at < piece.end)) {
				pieces.push({ start: at, end: at + 1, kind: 'marker' })
			}
		}
	}

	return pieces.sort((a, b) => a.start - b.start)
}

/**
 * Whether a value carries anything this module masks.
 *
 * @param {string}  text                The stored value.
 * @param {Object}  options             Mask options.
 * @param {boolean} options.lineMarkers Whether line-leading `+`/`-` are markers.
 * @returns {boolean} True when masking would change it.
 */
export const hasMaskedPieces = (text, { lineMarkers = false } = {}) => !!findPieces(text, { lineMarkers }).length

/**
 * Masks a plain-text value, returning the text a reader sees and how to map back to the stored one.
 *
 * @param {string}  text                The stored value.
 * @param {Object}  options             Mask options.
 * @param {boolean} options.lineMarkers Whether line-leading `+`/`-` are markers.
 * @returns {{text: string, pieces: Array}} The visible text and the masked pieces, each with its
 *          projected offset `at`.
 */
export const maskPlain = (text, options = {}) => {
	const stored = String(text ?? '')
	const pieces = []
	let visible  = '',
		cursor   = 0

	for (const piece of findPieces(stored, options)) {
		visible += stored.slice(cursor, piece.start)
		pieces.push({ ...piece, at: visible.length })
		cursor = piece.end
	}

	return { text: visible + stored.slice(cursor), pieces }
}

/**
 * Applies `{start, end, text}` edits addressed to `maskPlain()`'s text back onto the stored value.
 * NOTE: an edit with a masked piece strictly inside it is refused, never approximated.
 *
 * @param {string}  stored  The stored value.
 * @param {Object}  options Mask options, as given to `maskPlain()`.
 * @param {Array}   edits   Edits against the masked text.
 * @returns {{value: ?string, count: number, refusal: ?string}} The rewritten value, or a refusal.
 */
export const editMaskedPlain = (stored, options, edits) => {
	const { pieces } = maskPlain(stored, options)
	const crossed    = edits.map(edit => pieces.find(piece => piece.at > edit.start && piece.at < edit.end)).find(Boolean)

	if (crossed) {
		return { value: null, count: 0, refusal: 'dynamic' === crossed.kind ? 'dynamic' : 'not-found' }
	}

	// An edge sitting exactly on a masked piece stays outside it: a start lands after the piece,
	// an end before it, so the piece is never inside the replaced range.
	const toStored = (offset, side) => {
		let shift = 0

		for (const piece of pieces) {
			if (piece.at < offset || ('start' === side && piece.at === offset)) {
				shift += piece.end - piece.start
			}
		}

		return offset + shift
	}

	let value = String(stored)

	for (const edit of [ ...edits ].sort((a, b) => b.start - a.start)) {
		const start = toStored(edit.start, 'start')
		const end   = Math.max(start, toStored(edit.end, 'end'))

		value = value.slice(0, start) + edit.text + value.slice(end)
	}

	return { value, count: edits.length, refusal: null }
}

/**
 * Masks an HTML value by swapping each masked piece in its TEXT for an empty sentinel element.
 * NOTE: tag interiors are never touched, so a token in an attribute value survives as written.
 *
 * @param {string}  html                The stored value.
 * @param {Object}  options             Mask options.
 * @param {boolean} options.lineMarkers Whether line-leading `+`/`-` are markers.
 * @returns {{html: string, pieces: Array}} The masked html and the original pieces, by sentinel index.
 */
export const maskHtml = (html, { lineMarkers = false } = {}) => {
	const stored = String(html ?? '')
	const pieces = []
	let masked   = '',
		lineOpen = true

	for (const chunk of stored.split(/(<[^>]*>)/)) {
		if (!chunk) {
			continue
		}

		if (chunk.startsWith('<')) {
			masked += chunk
			lineOpen = LINE_BREAK_TAG.test(chunk) ? true : (lineOpen && /^<\/?(?:span|strong|b|em|i)\b/i.test(chunk))

			continue
		}

		let cursor = 0

		for (const piece of findPieces(chunk, { lineMarkers, atLineStart: lineOpen })) {
			masked += chunk.slice(cursor, piece.start) + sentinel(pieces.length)
			pieces.push({ text: chunk.slice(piece.start, piece.end), kind: piece.kind })
			cursor = piece.end
		}

		masked  += chunk.slice(cursor)
		lineOpen = /\n[ \t]*$/.test(chunk) || (lineOpen && !chunk.trim())
	}

	return { html: masked, pieces }
}

/**
 * Restores `maskHtml()`'s pieces, or null when a sentinel was lost or duplicated by the edit.
 *
 * @param {string} html   The edited, masked html.
 * @param {Array}  pieces The pieces `maskHtml()` returned.
 * @returns {?string} The html with every piece back in place.
 */
export const unmaskHtml = (html, pieces) => {
	let restored = String(html ?? '')

	for (const [ index, piece ] of pieces.entries()) {
		const tag = sentinel(index)

		if (1 !== restored.split(tag).length - 1) {
			return null
		}

		restored = restored.replace(tag, () => piece.text)
	}

	return restored
}

/**
 * The projected offset of each sentinel in a masked parse, by sentinel index.
 *
 * @param {string} html Masked html.
 * @returns {Map<number, number>} Sentinel index to the stream offset it sits at.
 */
export const sentinelOffsets = (html) => {
	const holder             = parseInert(html)
	const { text, segments } = buildTextStream(holder)
	const offsets            = new Map()

	for (const node of holder.querySelectorAll(`[${SENTINEL_ATTRIBUTE}]`)) {
		const next = segments.find(segment => !segment.boundary && (node.compareDocumentPosition(segment.node) & Node.DOCUMENT_POSITION_FOLLOWING))

		offsets.set(Number(node.getAttribute(SENTINEL_ATTRIBUTE)), next ? next.start : text.length)
	}

	return offsets
}

/**
 * The character a projection of this value may mark its dynamic tokens' places with, or an empty
 * string when the value already holds it and stripping it would cost the user's own text.
 *
 * @param {string} stored The stored value.
 * @returns {string} The gap character, or an empty string.
 */
export const gapFor = (stored) => (String(stored ?? '').includes(GAP) ? '' : GAP)

/**
 * Swaps `maskHtml()`'s sentinels for `gap` where a dynamic piece stood, and for nothing elsewhere.
 *
 * @param {string} html   Masked html.
 * @param {Array}  pieces The pieces `maskHtml()` returned.
 * @param {string} gap    What `gapFor()` returned.
 * @returns {string} The html, free of sentinels.
 */
export const fillSentinels = (html, pieces, gap) => pieces.reduce(
	(filled, piece, index) => filled.replace(sentinel(index), () => ('dynamic' === piece.kind ? gap : '')),
	String(html ?? '')
)

/**
 * `maskPlain()`'s text with `gap` where each dynamic piece stood.
 *
 * @param {string} text    The stored value.
 * @param {Object} options Mask options, as given to `maskPlain()`.
 * @param {string} gap     What `gapFor()` returned.
 * @returns {string} The visible text, gaps marked.
 */
export const maskPlainWithGaps = (text, options, gap) => {
	const masked = maskPlain(text, options)

	return masked.pieces
		.filter(piece => 'dynamic' === piece.kind)
		.reduceRight((visible, piece) => visible.slice(0, piece.at) + gap + visible.slice(piece.at), masked.text)
}

/**
 * Removes the gap characters from a projected text.
 *
 * @param {string} text The projected text.
 * @param {string} gap  What `gapFor()` returned.
 * @returns {{text: string, gaps: number[]}} The text, and the offsets in it each gap stood at.
 */
export const stripGaps = (text, gap) => {
	const parts = gap ? String(text).split(gap) : [ String(text) ]
	const gaps  = []
	let offset  = 0

	for (const part of parts.slice(0, -1)) {
		offset += part.length
		gaps.push(offset)
	}

	return { text: parts.join(''), gaps }
}

/**
 * Places marks the aligner dropped because their sentence spans a dynamic token, which the canvas
 * renders as its resolved value: the text either side of each gap must occur once with anything
 * between.
 *
 * @param {Object}   options           The options object.
 * @param {string}   options.rendered  The paint node's text stream, nbsp-folded.
 * @param {string}   options.projected The unit's projection, in the space `mark.range` indexes.
 * @param {number[]} options.gaps      The projected offsets of the unit's dynamic tokens.
 * @param {Array}    options.marks     The dropped marks.
 * @returns {Array} Aligned `{mark, start, end}` entries for the marks it could place.
 */
export const alignAcrossGaps = ({ rendered, projected, gaps, marks }) => {
	const aligned = []

	for (const mark of marks) {
		const { start, end } = mark.range || {}
		const inside         = gaps.filter(gap => gap > start && gap < end)
		const sentence       = projected.slice(start, end)

		// A stale mark describes text that is no longer at its offsets, so its gaps would be too.
		if (!inside.length || (mark.sentence && mark.sentence !== sentence)) {
			continue
		}

		const parts   = [ ...inside, end ].map((gap, index) => projected.slice(index ? inside[index - 1] : start, gap))
		const pattern = new RegExp(parts.map(escapeRegex).join('[^]*?'), 'g')
		const found   = [ ...rendered.matchAll(pattern) ]

		if (1 === found.length) {
			aligned.push({ mark, start: found[0].index, end: found[0].index + found[0][0].length })
		}
	}

	return aligned
}