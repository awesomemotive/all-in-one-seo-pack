import { IGNORED_CLASSES, IGNORED_TAGS } from '@/app/tru-seo/languageProcessing/helpers/html/ignoredElements'

import { getEditorDocument } from '@/vue/utils/editor'
import { createElement, getOuterText } from '@/vue/utils/html'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'

// Blocks whose only rich text lives in an attribute of their own name.
export const BLOCK_TEXT_ATTRIBUTES = {
	'core/button'  : 'text',
	'core/details' : 'summary'
}

export const ZERO_WIDTH = /[\u200B\uFEFF]/

/**
 * Plain-text projection of a native block's rich text, in the same coordinate
 * space the highlight ranges are measured against (mirrors the block branch of
 * the highlighter composable's `formatBlockContent`).
 *
 * @param {Object} block The Gutenberg block object.
 * @returns {string} The normalized plain text, or '' when unavailable.
 */
export const getNativeBlockText = (block) => normalizeWhitespaces(getRawBlockText(block))

// The block's rich text as the HTML its plain text is read from.
const getRawBlockHtml = (block) => {
	let content = block?.attributes?.content || block?.attributes?.caption || block?.attributes?.value || block?.attributes?.citation || ''
	if ('core/table' === block?.name) {
		content = block?.originalContent || ''
	}

	if (BLOCK_TEXT_ATTRIBUTES[block?.name]) {
		content = block?.attributes?.[BLOCK_TEXT_ATTRIBUTES[block.name]] || ''
	}

	// Newer WP stores rich text as a RichTextData object rather than a string.
	if (content && 'object' === typeof content && 'function' === typeof content.toHTMLString) {
		content = content.toHTMLString()
	}

	// Keep line breaks otherwise `getOuterText()` doesn't recognize them.
	return String(content).replace(/<br[^>]*>/gi, '\n')
}

// The block's text before normalizeWhitespaces, which is what wp.richText counts offsets in.
export const getRawBlockText = (block) => {
	try {
		// Don't trim: these offsets are applied in wp.richText's coordinate space,
		// which counts a leading `<br>` as one "\n". Dropping it shifts every range
		// in the block left, silently voiding `replaceInBlockEditor`'s exact match.
		return getOuterText(getRawBlockHtml(block), false)
	} catch (_e) {
		return ''
	}
}

const toSelector = (tags) => [ ...tags, ...IGNORED_CLASSES.map(className => `.${className}`) ].join(',')

// Every element whose text the analyzer drops ({@see htmlParser.js}).
const ANALYZER_SKIPPED_SELECTOR = toSelector(IGNORED_TAGS)

// Not a Quote's `<blockquote>`: the block editor's analysed content carries its inner blocks' text
// ({@see components/postContent.js:getAllProcessedBlockContent}), so they are listed and marked.
const NATIVE_SKIPPED_SELECTOR = toSelector(IGNORED_TAGS.filter(tag => 'blockquote' !== tag))

/**
 * Whether the block's rich text renders inside an element the analyzer drops: a Code or
 * Preformatted block is a `<pre>`, a Quote's inner blocks sit in its `<blockquote>`.
 *
 * NOTE: asks the canvas DOM; a block with no rendered node (Code editor mode) is not skipped.
 *
 * @param {string} clientId   The block's client id.
 * @param {string} [selector] The elements that count as dropped.
 * @returns {boolean} Whether the analyzer drops the block's text.
 */
export const isAnalyzerSkippedBlock = (clientId, selector = ANALYZER_SKIPPED_SELECTOR) => {
	const node = getEditorDocument()?.getElementById(`block-${clientId}`)
	if (!node) {
		return false
	}

	const editable = node.matches('[contenteditable="true"]') ? node : (node.querySelector('[contenteditable="true"]') || node)

	return !!editable.closest(selector)
}

/**
 * Ranges of getNativeBlockText() the analyzer drops: all of it in a skipped block, otherwise the
 * text of an inline element such as `<code>`.
 *
 * @param {Object} block The Gutenberg block object.
 * @returns {Array<{start: number, end: number}>} The ranges, in document order.
 */
export const getNativeBlockSkipped = (block) => {
	try {
		const text = getNativeBlockText(block)
		if (!text) {
			return []
		}

		if (isAnalyzerSkippedBlock(block?.clientId, NATIVE_SKIPPED_SELECTOR)) {
			return [ { start: 0, end: text.length } ]
		}

		const html = getRawBlockHtml(block)
		if (!/<[a-z]/i.test(html)) {
			return []
		}

		// Parsed the way getRawBlockText() reads it, so the text nodes concatenate to that string.
		const holder = createElement(html, 'div', false)
		const raw    = getRawBlockText(block)
		if (holder.textContent !== raw) {
			return []
		}

		// Only zero-width characters change length under normalizeWhitespaces here; anything else
		// would leave these offsets unmappable, so nothing is skipped rather than the wrong text.
		const removedBefore = []
		let removed = 0,
			offset  = 0

		for (let index = 0; index <= raw.length; index++) {
			removedBefore.push(removed)
			if (index < raw.length && ZERO_WIDTH.test(raw[index])) {
				removed++
			}
		}

		if (raw.length - removed !== text.length) {
			return []
		}

		const ranges = []
		const walker = holder.ownerDocument.createTreeWalker(holder, NodeFilter.SHOW_TEXT)

		while (walker.nextNode()) {
			const length = walker.currentNode.textContent.length

			if (walker.currentNode.parentElement?.closest(NATIVE_SKIPPED_SELECTOR)) {
				const start = offset - removedBefore[offset]
				const end   = offset + length - removedBefore[offset + length]
				const last  = ranges[ranges.length - 1]

				if (last && last.end >= start) {
					last.end = Math.max(last.end, end)
				} else if (start < end) {
					ranges.push({ start, end })
				}
			}

			offset += length
		}

		return ranges
	} catch (_e) {
		return []
	}
}