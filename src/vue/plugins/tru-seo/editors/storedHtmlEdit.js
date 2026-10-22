import { BLOCK_TAGS, buildTextStream, toNodeOffset } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { IGNORED_TAGS } from '@/app/tru-seo/languageProcessing/helpers/html/ignoredElements'

import { foldNbsp } from './domDecorator'

const ANALYZER_SKIPPED_SELECTOR = IGNORED_TAGS.join(',')

// Parsed inert, as a `<body>` like Elementor's own parseHtmlChildren, so the round trip is an
// identity. Not a `<template>`: its insertion mode drops `<td>` tags and trailing `<col>` text.
// NOTE: the parse decides where an edit lands; the written value is a splice of the original
// string ({@see spliceStoredHtml}), because re-serializing normalizes shortcode arguments.
export const parseInert = (html) => {
	// HTML parsing has no failure mode, so `body` is always there — but a caller serializes
	// `.innerHTML` off this, so an absent one has to answer an empty element rather than throw
	// into Elementor's editor.
	return new DOMParser().parseFromString(`<body>${html || ''}</body>`, 'text/html')?.body ||
		document.implementation.createHTMLDocument('').body
}

// Resolves `[start, end)` ranges of a detached parse's own text stream to per-text-node rewrites.
// Descending, so an edit never shifts the offsets of one still to come. A range crossing an
// inline element puts the whole replacement in its first text node.
// NOTE: returned rather than performed, so they can be applied to the parse and mapped back to
// the source string against the same pristine offsets.
const planStreamWrites = (root, edits) => {
	const { segments } = buildTextStream(root)
	const writes       = []
	let applied        = 0

	for (const edit of [ ...edits ].sort((a, b) => b.start - a.start)) {
		const pieces = segments.filter(segment => segment.start < edit.end && segment.end > edit.start)

		// Element-backed segments hold no text to land in, and a guess around an emoji image
		// deletes it. Text the analyzer drops (`<code>`, `<pre>`…) was never flagged, so it is
		// never written either.
		if (!pieces.length || pieces.some(piece => piece.element || piece.node.parentElement?.closest(ANALYZER_SKIPPED_SELECTOR))) {
			continue
		}

		for (let index = pieces.length - 1; 0 <= index; index--) {
			const piece = pieces[index]

			writes.push({
				node : piece.node,
				from : toNodeOffset(piece, Math.max(edit.start, piece.start)),
				to   : toNodeOffset(piece, Math.min(edit.end, piece.end)),
				text : 0 === index ? edit.text : ''
			})
		}

		applied++
	}

	return { applied, writes }
}

// In the order planStreamWrites produced them, which is the order that keeps every write's
// offsets valid: a later write only ever changes text at or after its own `from`, so an earlier
// one still indexes the same characters it was planned against.
const applyPlannedWrites = (writes) => {
	for (const write of writes) {
		const text = write.node.textContent

		write.node.textContent = text.slice(0, write.from) + write.text + text.slice(write.to)
	}
}

// Where the HTML tokenizer's tag-open state actually opens markup: before an ASCII letter, `/`,
// `!` or `?`. Anything else after a `<` is prose, so `a < b` must not be read as a tag.
const MARKUP_OPEN_PATTERN = /[a-zA-Z!?/]/

// Content that is not parsed for tags, and the two kinds differ where it matters here: `script`
// and `style` hold RAW text, so no entity in them decodes and source maps to text one for one,
// while `textarea` and `title` are escapable raw text and decode exactly as prose does.
const RAW_TEXT_TAGS = new Set([ 'script', 'style' ])
const ESCAPABLE_RAW_TEXT_TAGS = new Set([ 'textarea', 'title' ])

const TAG_NAME_PATTERN = /^<\/?([a-zA-Z][^\s/>]*)/

// A character reference as the tokenizer's longest match: numeric, hex, or the longest run of
// alphanumerics, each with an optional `;`. The run is what makes the isolated decode below agree
// with an in-context one — nothing alphanumeric is left to extend the name.
const ENTITY_REF_PATTERN = /^&(?:#[0-9]+;?|#[xX][0-9a-fA-F]+;?|[a-zA-Z][a-zA-Z0-9]*;?)/

// End of one tag, respecting quoted attribute values so an `href` holding a `>` does not cut the
// tag short. Unterminated markup runs to the end of the string, which is what the tokenizer does.
const endOfTag = (html, at) => {
	let index = at + 1

	while (index < html.length) {
		const char = html[index]

		if ('"' === char || '\'' === char) {
			const close = html.indexOf(char, index + 1)
			if (-1 === close) {
				return html.length
			}

			index = close + 1

			continue
		}

		if ('>' === char) {
			return index + 1
		}

		index++
	}

	return html.length
}

// The raw string's own text regions, in source order. Spans only — whether they line up with the
// parse is decided by mapping them against it, never assumed here.
const scanTextRegions = (html) => {
	const regions = []
	let index     = 0,
		textStart = 0

	const flush = (end) => {
		if (end > textStart) {
			regions.push({ start: textStart, end, raw: false })
		}
	}

	while (index < html.length) {
		const at = html.indexOf('<', index)
		if (-1 === at) {
			break
		}

		if (!MARKUP_OPEN_PATTERN.test(html[at + 1] || '')) {
			index = at + 1

			continue
		}

		flush(at)

		let close

		if (html.startsWith('<!--', at)) {
			const end = html.indexOf('-->', at + 4)

			close = -1 === end ? html.length : end + 3
		} else if ('!' === html[at + 1] || '?' === html[at + 1]) {
			const end = html.indexOf('>', at)

			close = -1 === end ? html.length : end + 1
		} else {
			close = endOfTag(html, at)

			const name = (TAG_NAME_PATTERN.exec(html.slice(at, close)) || [])[1]?.toLowerCase() || ''
			const raw  = RAW_TEXT_TAGS.has(name)

			if ('/' !== html[at + 1] && (raw || ESCAPABLE_RAW_TEXT_TAGS.has(name))) {
				const closeTag = html.toLowerCase().indexOf(`</${name}`, close)
				const content  = -1 === closeTag ? html.length : closeTag

				if (content > close) {
					regions.push({ start: close, end: content, raw })
				}

				close = -1 === closeTag ? html.length : endOfTag(html, closeTag)
			}
		}

		index     = close
		textStart = close
	}

	flush(html.length)

	return regions
}

// The parser's own answer for one snippet, so the entity table is never restated here.
const decodeChunk = (chunk) => parseInert(chunk).textContent

// A source index for every character of `decoded`, plus an end sentinel. Null when the region does
// not spell exactly that text, which is the only evidence available that the alignment is real —
// the scan above is a guess about a string the parser owns, so it has to answer to it.
const mapRegion = (source, decoded, raw) => {
	const offsets = []
	let sourceAt  = 0,
		decodedAt = 0

	while (decodedAt < decoded.length) {
		if (sourceAt >= source.length) {
			return null
		}

		if (!raw && '&' === source[sourceAt]) {
			const match = ENTITY_REF_PATTERN.exec(source.slice(sourceAt))
			const value = match ? decodeChunk(match[0]) : ''

			// A reference decoding to itself is not one — `&foo;` stays literal — and its characters
			// have to map individually below, or an offset inside it would resolve to its start.
			if (match && value.length && value !== match[0] && decoded.startsWith(value, decodedAt)) {
				// One source span for however many characters it produced, so every one of them
				// resolves to the reference's own start.
				for (let index = 0; index < value.length; index++) {
					offsets.push(sourceAt)
				}

				sourceAt  += match[0].length
				decodedAt += value.length

				continue
			}
		}

		if (source[sourceAt] !== decoded[decodedAt]) {
			return null
		}

		offsets.push(sourceAt)
		sourceAt++
		decodedAt++
	}

	// A region holding text the node does not means the parse moved or dropped it — table foster
	// parenting is the reachable case — so the whole map is void rather than this one region.
	if (sourceAt !== source.length) {
		return null
	}

	offsets.push(sourceAt)

	return offsets
}

const textNodesOf = (root) => {
	const nodes  = []
	const walker = root.ownerDocument.createTreeWalker(root, NodeFilter.SHOW_TEXT)

	while (walker.nextNode()) {
		nodes.push(walker.currentNode)
	}

	return nodes
}

// Source spans for the parse's text nodes, matched to the scan's regions BY POSITION in document
// order — the order buildTextStream walks in too. Null when the two disagree anywhere.
const buildSourceMap = (html, root) => {
	const regions = scanTextRegions(html)
	const nodes   = textNodesOf(root)

	if (!regions.length || regions.length !== nodes.length) {
		return null
	}

	const map = new Map()

	for (let index = 0; index < nodes.length; index++) {
		const region  = regions[index]
		const offsets = mapRegion(html.slice(region.start, region.end), nodes[index].textContent, region.raw)

		if (!offsets) {
			return null
		}

		map.set(nodes[index], { base: region.start, offsets })
	}

	return map
}

// Caught, because the map is the only part of this write that reasons about the STRING instead of
// the parse, and editStoredHtml's callers have no try of their own — before the map existed it
// could not throw at all, so a throw here would surface as a popover click that does nothing.
const sourceMapOrNull = (html, root) => {
	try {
		return buildSourceMap(html, root)
	} catch (_e) {
		return null
	}
}

// Exactly the escaping innerHTML applies to a text node, taken from the serializer rather than
// restated, so a splice and a re-serialization spell the same replacement the same way.
const serializeAsText = (root, text) => {
	const holder = root.ownerDocument.createElement('span')

	holder.textContent = text

	return holder.innerHTML
}

// The written value as a splice of the original string, so everything outside the edited spans
// survives byte for byte. V4's atomic tier re-serializes afterwards anyway
// ({@see elementorV4.js:writeReplacements}). Null when the splice can't be proven; the caller
// then serializes the parse.
const spliceStoredHtml = (html, root, writes) => {
	const map = sourceMapOrNull(html, root)
	if (!map) {
		return null
	}

	const splices = []

	for (const write of writes) {
		const entry = map.get(write.node)
		if (!entry) {
			return null
		}

		const from = entry.offsets[write.from]
		const to   = entry.offsets[write.to]

		if (undefined === from || undefined === to || to < from) {
			return null
		}

		splices.push({ from: entry.base + from, to: entry.base + to, text: serializeAsText(root, write.text) })
	}

	// Right to left, so each splice's own indices still address the original string. `limit` is
	// what makes that true rather than assumed: two overlapping spans would each rewrite the
	// other's bytes, and there is no correct order for that.
	splices.sort((a, b) => b.from - a.from)

	let spliced = html,
		limit   = html.length

	for (const splice of splices) {
		if (splice.to > limit) {
			return null
		}

		spliced = spliced.slice(0, splice.from) + splice.text + spliced.slice(splice.to)
		limit   = splice.from
	}

	return spliced
}

// A parse's tree shape in document order, to check a splice landed: the same text can come from
// a different tree (`<p>a</p><p>b</p>` vs `<p>a<br>b</p>`). Attributes are sorted; text is raw.
export const structureOf = (root) => {
	const parts = []

	const walk = (node) => {
		for (const child of node.childNodes) {
			if (Node.TEXT_NODE === child.nodeType) {
				parts.push(`T:${child.nodeValue}`)

				continue
			}

			if (Node.ELEMENT_NODE !== child.nodeType) {
				parts.push(`${child.nodeType}:${child.nodeValue || ''}`)

				continue
			}

			const attributes = [ ...child.attributes ]
				.map(attribute => `${attribute.name}=${attribute.value}`)
				.sort()

			parts.push(`E:${child.tagName}[${attributes.join(',')}]`)
			walk(child)
			parts.push('/')
		}
	}

	walk(root)

	return parts.join('\u0001')
}

// Whether a candidate string carries the text AND the tree the edited parse does. The coordinate
// space marks are measured in is a projection of the WRITTEN value, so a splice that moved a
// character of it would move the marks with it — this is what stops that reaching a settings
// command. The structural half is what makes a mis-located span detectable at all
// ({@see structureOf}); before it, a splice was accepted on text agreement alone.
const matchesEditedParse = (html, root, expected) => {
	const candidate = parseInert(html)

	return foldNbsp(buildTextStream(candidate).text) === expected &&
		structureOf(candidate) === structureOf(root)
}

/**
 * Rewrites a stored HTML string: `plan` names ranges in its parse's text stream, and the edited
 * spans are spliced back into the ORIGINAL string.
 *
 * `plan` is handed the folded projection because that is the space a mark's `range` and a
 * `findSentenceRanges` search both index. Null when nothing was applied, so a miss can never
 * reach a settings command.
 *
 * NOTE: the splice is re-parsed and its text and tree shape compared ({@see structureOf}); on a
 * mismatch this falls back to serializing the parse.
 *
 * @param {string}   html The stored HTML string.
 * @param {function} plan Given the folded projection, returns `{start, end, text}` edits in it.
 * @returns {?{html: string, count: number}} The rewritten html and how many edits applied.
 */
export const editStoredHtml = (html, plan) => {
	const holder    = parseInert(html)
	const projected = foldNbsp(buildTextStream(holder).text)
	const edits     = plan(projected)

	if (!edits?.length) {
		return null
	}

	const { applied, writes } = planStreamWrites(holder, edits)
	if (!applied) {
		return null
	}

	// Before the parse is touched: both the map and the writes index the pristine node text.
	const spliced = spliceStoredHtml(html, holder, writes)

	applyPlannedWrites(writes)

	const expected = foldNbsp(buildTextStream(holder).text)

	return {
		html  : (null !== spliced && matchesEditedParse(spliced, holder, expected)) ? spliced : holder.innerHTML,
		count : applied
	}
}

// The innermost block element at or above a node, bounded by the parse. Asked of the SAME list
// buildTextStream emits its boundaries from, so the block a structural write replaces is a block
// the stream separated.
const blockAncestor = (root, node) => {
	let current = Node.TEXT_NODE === node?.nodeType ? node.parentElement : node

	while (current && current !== root) {
		if (BLOCK_TAGS.has(current.tagName)) {
			return current
		}

		current = current.parentElement
	}

	return null
}

// Blocks a run of `<p>`/`<h2>` may stand in for. An allowlist, because a wrong yes stores markup
// the browser repairs by moving nodes (`<ul><p>` and a whole `<table>` were both reachable).
// NOTE: `DIV` loses its wrapper classes; allowed because a bare layout div is the common case.

const REPLACEABLE_BLOCK_TAGS = new Set([ 'P', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6' ])

/**
 * Replaces the BLOCK element a stream range lies in with new markup.
 *
 * Two gates: the range must cover the whole block's text, and the block must be one a paragraph
 * can stand in for ({@see REPLACEABLE_BLOCK_TAGS}); `BLOCK_TAGS` is a boundary list, not a content
 * model.
 *
 * NOTE: writes the parse's serialization, not a splice of the original string.
 *
 * @param {string}   html The stored HTML string.
 * @param {function} plan Given the folded projection, returns `{start, end, markup}` or null.
 * @returns {?{html: string, count: number}|{refusal: string}} The rewritten html, the reason it was
 *          refused, or null when `plan` itself declined (the caller already named that one).
 */
export const editStoredBlock = (html, plan) => {
	const holder    = parseInert(html)
	const stream    = buildTextStream(holder)
	const projected = foldNbsp(stream.text)
	const request   = plan(projected)

	if (!request?.markup) {
		return null
	}

	const segment = stream.segments.find(entry => entry.start <= request.start && entry.end > request.start)
	const block   = segment ? blockAncestor(holder, segment.node) : null

	if (!block) {
		return { refusal: 'no-block' }
	}

	if (foldNbsp(buildTextStream(block).text).trim() !== projected.slice(request.start, request.end).trim()) {
		return { refusal: 'partial-block' }
	}

	if (!REPLACEABLE_BLOCK_TAGS.has(block.tagName)) {
		return { refusal: 'block-shape' }
	}

	// Spread before replaceWith: childNodes is live, and moving the first child would shorten the
	// very list being iterated.
	const nodes = [ ...parseInert(request.markup).childNodes ]
	if (!nodes.length) {
		return { refusal: 'no-markup' }
	}

	block.replaceWith(...nodes)

	return { html: holder.innerHTML, count: 1 }
}