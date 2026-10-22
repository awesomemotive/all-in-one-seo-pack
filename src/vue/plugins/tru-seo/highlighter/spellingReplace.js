import { matchCase, wordBoundaryPattern } from '@/vue/utils/regex'
import { getRichTextIdentifier } from './blockEditor'
import { isAnalyzerSkippedBlock } from './blockText'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'
import { buildStructuredMarkup, parseStructuredReplacement } from './structuredMarkup'
import { IGNORED_TAGS } from '@/app/tru-seo/languageProcessing/helpers/html/ignoredElements'

// Elements whose text the analyzer drops ({@see htmlParser.js}), so a fix must never write there.
const ANALYZER_SKIPPED_SELECTOR = IGNORED_TAGS.join(',')

// Why the last write refused, when the adapter that refused knew why. Block and classic never
// set it: a no-op fix is common there (button labels, table cells) and alerting would cry wolf.
let lastWriteRefusal = null

/**
 * Reads and clears why the last write refused.
 *
 * NOTE: valid only immediately after a write; every entry point below clears it on the way in.
 *
 * @returns {?string} The refusal reason, or null when the write landed or the editor gave none.
 */
export const consumeWriteRefusal = () => {
	const reason = lastWriteRefusal

	lastWriteRefusal = null

	return reason
}

/**
 * Finds the character offset of the mark's node within its closest
 * contenteditable container, in the same coordinate space wp.richText uses.
 *
 * @since 5.0.0
 *
 * @param {HTMLElement} container The contenteditable container element.
 * @param {Node}        markNode  The highlighted mark DOM node.
 * @returns {number} The character offset, or -1 if not found.
 */
function getCharacterOffset (container, markNode) {
	// In the iframed block editor (WP 6.3+), `container` lives in the iframe's
	// document. Use its owner document so the walker traverses the iframe tree
	// — `document.createTreeWalker(foreignNode)` is not portable across realms.
	const doc = container.ownerDocument || document
	// Visit elements as well as text: wp.richText models each `<br>` as one "\n"
	// in the text the mark range is measured against, so a text-only walk skips
	// every `<br>` and drifts the offset by one per line break — silently
	// misaligning the replacement for any sentence after a `<br>`.
	const walker = doc.createTreeWalker(container, NodeFilter.SHOW_TEXT | NodeFilter.SHOW_ELEMENT)
	let offset = 0,
	 node

	while (null !== (node = walker.nextNode())) {
		if (markNode === node || markNode.contains(node)) {
			return offset
		}

		if (Node.TEXT_NODE === node.nodeType) {
			offset += node.textContent.length
		} else if ('BR' === node.tagName) {
			offset += 1
		}
	}

	return -1
}

/**
 * Builds the rich-text value to insert, carrying the formats every replaced character shares.
 *
 * NOTE: a bare `create({ text })` has no formats, so inserting it cut links and inline code in two.
 *
 * @param {Object} value       The rich-text value being edited.
 * @param {number} start       The start offset of the replaced range.
 * @param {number} end         The end offset of the replaced range.
 * @param {string} replacement The replacement text.
 * @returns {Object}           The rich-text value to insert.
 */
function createReplacementValue (value, start, end, replacement) {
	const replacementValue = window.wp.richText.create({ text: replacement })
	const sharedFormats    = (value.formats[start] || []).filter((format) => {
		for (let i = start + 1; i < end; i++) {
			if (!(value.formats[i] || []).includes(format)) {
				return false
			}
		}

		return true
	})

	if (sharedFormats.length) {
		replacementValue.formats = Array.from(replacementValue.text, () => sharedFormats)
	}

	return replacementValue
}

/**
 * Replaces a misspelled word with a suggestion in the Block Editor.
 *
 * Uses the wp.richText API to perform a position-aware replacement,
 * ensuring only the specific word the user hovered is replaced even
 * when the same misspelled word appears multiple times in a block.
 *
 * @since 5.0.0
 *
 * @param {Object} mark        The highlight mark object from the store.
 * @param {string} replacement The replacement word.
 * @returns {boolean}          Whether the replacement was successful.
 */
export function replaceInBlockEditor (mark, replacement) {
	if (!mark?.block?.clientId || !window?.wp?.data || !window?.wp?.richText) {
		return false
	}

	const { select, dispatch } = window.wp.data
	const block = select('core/block-editor').getBlock(mark.block.clientId)
	if (!block) {
		return false
	}

	const attributeId = getRichTextIdentifier(block)
	const content = block.attributes[attributeId]
	if (!content) {
		return false
	}

	const isRichTextData = 'object' === typeof content && 'function' === typeof content.toHTMLString
	const html = isRichTextData ? content.toHTMLString() : String(content)

	const word = mark.sentence
	const markNode = mark.node
	if (!markNode) {
		return false
	}

	const container = (markNode.nodeType === Node.TEXT_NODE ? markNode.parentElement : markNode)
		?.closest('[contenteditable="true"]')
	if (!container) {
		return false
	}

	const charOffset = getCharacterOffset(container, markNode)
	if (-1 === charOffset) {
		return false
	}

	const { create, insert, toHTMLString } = window.wp.richText
	const richTextValue = create({ html })

	// `word` (mark.sentence) went through normalizeWhitespaces, which folds `&nbsp;`
	// / U+00A0 down to a plain space; the raw rich-text projection keeps the U+00A0.
	// Both are one code unit so the offsets still line up — compare normalized so a
	// sentence containing a non-breaking space isn't rejected here (which silently
	// dropped the replacement for any flagged sentence with special-character spacing).
	const candidate = richTextValue.text.substring(charOffset, charOffset + word.length)
	if (normalizeWhitespaces(candidate) !== normalizeWhitespaces(word)) {
		return false
	}

	const newValue = insert(
		richTextValue,
		createReplacementValue(richTextValue, charOffset, charOffset + word.length, replacement),
		charOffset,
		charOffset + word.length
	)
	const newHtml = toHTMLString({ value: newValue })

	const RichTextData = window.wp?.richText?.RichTextData
	const newContent = (isRichTextData && RichTextData)
		? RichTextData.fromHTMLString(newHtml)
		: newHtml

	dispatch('core/block-editor').updateBlockAttributes(mark.block.clientId, {
		[attributeId] : newContent
	})

	return true
}

/**
 * Replaces a misspelled word with a suggestion in the Classic Editor (TinyMCE).
 *
 * Swaps the annotation span for a fresh text node, merges adjacent text
 * nodes via `normalize()` so the editor doesn't end up with fragmented
 * siblings (which can confuse TinyMCE's serializer on form submit), marks
 * the editor dirty, and registers a single undo step.
 *
 * @since 5.0.0
 *
 * @param {Object} mark        The highlight mark object from the store.
 * @param {string} replacement The replacement word.
 * @returns {boolean}          Whether the replacement was successful.
 */
export function replaceInClassicEditor (mark, replacement) {
	const editor = window.tinymce?.get('content')
	if (!editor || !mark?.node?.parentNode) {
		return false
	}

	const markNode = mark.node
	const parent   = markNode.parentNode
	const doc      = markNode.ownerDocument || editor.getDoc()
	const textNode = doc.createTextNode(replacement)

	parent.replaceChild(textNode, markNode)
	parent.normalize()

	editor.undoManager.add()
	editor.setDirty(true)

	// Programmatic DOM edits don't fire the keyup/paste events that drive the
	// Classic editor's re-analysis (see `watchClassicEditor`), so trigger it
	// explicitly. Without this, the highlights cleared right after applying a
	// suggestion never come back even though the content still has issues.
	// Routed through the worker-free bridge (registered by `components/helpers`)
	// so this module doesn't statically pull the TruSEO worker into addon bundles.
	requestPostUpdate(500)

	return true
}

/**
 * Replaces the mark with real block markup in the Classic Editor (TinyMCE).
 *
 * NOTE: uses `insertContent`, which splits the surrounding block itself; the mark is selected
 * first so the insert replaces it.
 *
 * @param {Object} mark  The highlight mark object.
 * @param {Array}  parts Parts from `parseStructuredReplacement`.
 * @returns {boolean} Whether the replacement was successful.
 */
export function replaceBlocksInClassicEditor (mark, parts) {
	const editor = window.tinymce?.get('content')
	if (!editor || !mark?.node?.parentNode || !parts?.length) {
		return false
	}

	if ('function' !== typeof editor.insertContent || 'function' !== typeof editor.selection?.select) {
		return false
	}

	editor.selection.select(mark.node)
	editor.insertContent(buildStructuredMarkup(parts))

	editor.undoManager.add()
	editor.setDirty(true)

	// Programmatic DOM edits don't fire the keyup/paste events that drive the Classic editor's
	// re-analysis, the same reason `replaceInClassicEditor` asks for one.
	requestPostUpdate(500)

	return true
}

/**
 * Detects if the current editor is the Block Editor.
 *
 * @since 5.0.0
 *
 * @returns {boolean} True if the Block Editor is active.
 */
export function isBlockEditor () {
	return !!document.querySelector('.block-editor')
}

/**
 * Replaces a misspelled word with a suggestion in the active editor.
 *
 * NOTE: the owning adapter writes; the block/classic branches are the fallback for a page no
 * adapter claims.
 *
 * @since 5.0.0
 *
 * @param {Object} mark        The highlight mark object.
 * @param {string} replacement The replacement word.
 * @returns {Promise<boolean>} Whether the replacement was successful.
 */
export async function replaceWord (mark, replacement) {
	lastWriteRefusal = null

	const adapter = resolveAdapter()
	if ('function' === typeof adapter?.replaceMark) {
		const written = !!await adapter.replaceMark(mark, replacement)
		if (!written) {
			// Asked of the adapter rather than inferred here: only the editor that refused knows
			// why, and one that cannot say supplies nothing.
			lastWriteRefusal = adapter.consumeWriteRefusal?.() || null
		}

		return written
	}

	if (isBlockEditor()) {
		return replaceInBlockEditor(mark, replacement)
	}

	return replaceInClassicEditor(mark, replacement)
}

/**
 * Replaces the flagged text (sentence/anchor) with new text in the active editor.
 *
 * Works for any scope where the replacement stays inside the same block (single
 * sentence, new anchor text). Uses the same position-aware rich-text replacement
 * as `replaceWord`, just with a longer source range.
 *
 * @since 5.0.0
 *
 * @param {Object} mark    The highlight mark object.
 * @param {string} newText The replacement text.
 * @returns {Promise<boolean>} Whether the replacement was successful.
 */
export function replaceText (mark, newText) {
	return replaceWord(mark, newText)
}

/**
 * Replaces the block containing the mark with multiple paragraph/heading blocks
 * built from a structured AI replacement. Used by paragraph and section scope.
 *
 * NOTE: the writer depends on what the target can hold — block editor blocks, an adapter's
 * `replaceMarkBlocks` markup, or the plain-text write for a single part. Never flatten parts into
 * one string: html collapses the newlines and the paragraph breaks vanish.
 *
 * @since 5.0.0
 *
 * @param {Object} mark    The highlight mark object.
 * @param {string} newText The replacement text (may contain `\n\n` and `## `/`### ` markers).
 * @returns {Promise<boolean>} Whether the replacement was successful.
 */
export async function replaceBlockText (mark, newText) {
	// Its block arm returns without ever reaching an adapter, so without this the channel would
	// still hold whatever the previous write left — and the popover reads it after this call.
	lastWriteRefusal = null

	const parts = parseStructuredReplacement(newText)

	if (isBlockEditor()) {
		if (!mark?.block?.clientId || !window?.wp?.data || !window?.wp?.blocks) {
			return false
		}

		if (!parts.length) {
			return false
		}

		const { createBlock } = window.wp.blocks
		const newBlocks = parts.map(part => {
			if ('heading' === part.type) {
				return createBlock('core/heading', { level: part.level, content: part.content })
			}

			return createBlock('core/paragraph', { content: part.content })
		})

		window.wp.data.dispatch('core/block-editor').replaceBlocks(mark.block.clientId, newBlocks)

		return true
	}

	if (1 < parts.length) {
		const adapter = resolveAdapter()

		if ('function' === typeof adapter?.replaceMarkBlocks) {
			const written = !!await adapter.replaceMarkBlocks(mark, parts)
			if (!written) {
				lastWriteRefusal = adapter.consumeWriteRefusal?.() || null
			}

			return written
		}

		// Fallback for a page no adapter claims. Without TinyMCE this answers false rather than
		// handing a plain-text writer a string with newlines in it.
		return replaceBlocksInClassicEditor(mark, parts)
	}

	// Whitespace-only suggestion. Refused, because `'\n\n'` is truthy and would reach a writer.
	if (!parts.length) {
		return false
	}

	// ONE part, so there is no structure for a block write to carry: the plain-text write is the
	// whole answer, and every one-part suggestion keeps exactly the behaviour it shipped with.
	return replaceWord(mark, parts[0].content)
}

/**
 * Replaces every whole-word occurrence of `word` with `replacement`
 * inside a single Gutenberg block.
 *
 * Reads the block content fresh, finds matches via the rich-text plain
 * text representation, then applies replacements from the end so earlier
 * offsets stay valid for the same update.
 *
 * @since   5.0.0
 * @version 5.0.1 Preserves each occurrence's capitalisation.
 *
 * @param {string} clientId    The Gutenberg block client ID.
 * @param {string} word        The misspelled word to replace.
 * @param {string} replacement The replacement word.
 * @returns {number}           The count of occurrences replaced in this block (0 means none).
 */
export function replaceAllInBlockEditor (clientId, word, replacement) {
	if (!clientId || !window?.wp?.data || !window?.wp?.richText) {
		return 0
	}

	const { select, dispatch } = window.wp.data
	const block = select('core/block-editor').getBlock(clientId)
	if (!block) {
		return 0
	}

	const attributeId = getRichTextIdentifier(block)
	const content = block.attributes[attributeId]
	if (!content) {
		return 0
	}

	if (isAnalyzerSkippedBlock(clientId)) {
		return 0
	}

	const isRichTextData = 'object' === typeof content && 'function' === typeof content.toHTMLString
	const html = isRichTextData ? content.toHTMLString() : String(content)

	const { create, insert, toHTMLString } = window.wp.richText
	const wordRegex = new RegExp(wordBoundaryPattern(word), 'gu')
	const positions = []
	let richTextValue = create({ html }),
	 match

	while (null !== (match = wordRegex.exec(richTextValue.text))) {
		const formats = richTextValue.formats.slice(match.index, match.index + match[0].length)

		// Inline `<code>` is a format here, not an element, and the analyzer drops its text.
		if (formats.some(charFormats => charFormats?.some(format => 'core/code' === format?.type))) {
			continue
		}

		positions.push({ start: match.index, end: match.index + match[0].length, matched: match[0] })
	}

	if (!positions.length) {
		return 0
	}

	for (let i = positions.length - 1; 0 <= i; i--) {
		richTextValue = insert(
			richTextValue,
			createReplacementValue(
				richTextValue,
				positions[i].start,
				positions[i].end,
				matchCase(positions[i].matched, replacement)
			),
			positions[i].start,
			positions[i].end
		)
	}

	const newHtml = toHTMLString({ value: richTextValue })

	const RichTextData = window.wp?.richText?.RichTextData
	const newContent = (isRichTextData && RichTextData)
		? RichTextData.fromHTMLString(newHtml)
		: newHtml

	dispatch('core/block-editor').updateBlockAttributes(clientId, {
		[attributeId] : newContent
	})

	return positions.length
}

/**
 * Replaces every whole-word occurrence of a word in a term's description textarea.
 *
 * @since 5.0.1
 *
 * @param {HTMLTextAreaElement} textarea          The term description textarea.
 * @param {string}              word              The word to replace.
 * @param {string}              replacement       The replacement word.
 * @param {boolean}             triggerReanalysis Re-run analysis after editing.
 * @returns {boolean}                             Whether any occurrence was replaced.
 */
export function replaceAllWordInTermDescription (textarea, word, replacement, triggerReanalysis = true) {
	const wordRegex = new RegExp(wordBoundaryPattern(word), 'gu')
	const original  = textarea.value || ''

	wordRegex.lastIndex = 0
	if (!wordRegex.test(original)) {
		return false
	}

	// Preserve the caret so applying a fix doesn't jump the cursor to the end for anyone
	// mid-sentence in the description.
	const selectionStart = textarea.selectionStart
	const selectionEnd   = textarea.selectionEnd

	wordRegex.lastIndex = 0
	// Function replacer so `$` sequences in the replacement aren't treated as special patterns.
	textarea.value = original.replace(wordRegex, match => matchCase(match, replacement))

	const maxCaret = textarea.value.length
	textarea.setSelectionRange(Math.min(selectionStart, maxCaret), Math.min(selectionEnd, maxCaret))

	// The term description is a plain textarea, so nothing observes programmatic writes —
	// dispatch input so WordPress and our own listeners see the change.
	textarea.dispatchEvent(new Event('input', { bubbles: true }))

	if (triggerReanalysis) {
		requestPostUpdate(500)
	}

	return true
}

/**
 * Replaces every whole-word occurrence of a misspelled word in the Classic editor.
 *
 * @since 5.0.0
 *
 * @param {string}  word              The misspelled word to replace.
 * @param {string}  replacement       The replacement word.
 * @param {boolean} triggerReanalysis Re-run analysis after editing. Pass false when the caller re-analyzes itself.
 * @returns {number}                  The count of occurrences replaced (0 means none).
 */
export function replaceAllWordInClassicEditor (word, replacement, triggerReanalysis = true) {
	const editor = window.tinymce?.get('content')
	const body   = editor?.getBody?.()
	if (!editor || !body) {
		return 0
	}

	const doc       = editor.getDoc?.() || body.ownerDocument || document
	const wordRegex = new RegExp(wordBoundaryPattern(word), 'gu')
	const walker    = doc.createTreeWalker(body, NodeFilter.SHOW_TEXT)
	const targets   = []
	let node,
		count = 0

	while (null !== (node = walker.nextNode())) {
		// The analyzer drops this text, so it was never flagged.
		if (node.parentElement?.closest(ANALYZER_SKIPPED_SELECTOR)) {
			continue
		}

		wordRegex.lastIndex = 0
		if (wordRegex.test(node.nodeValue || '')) {
			targets.push(node)
		}
	}

	if (!targets.length) {
		return 0
	}

	for (const textNode of targets) {
		wordRegex.lastIndex = 0
		// Function replacer so `$` sequences in the replacement (e.g. "$&") aren't
		// treated as special patterns by String.prototype.replace, and so each match
		// can be tallied into the returned count while keeping its own capitalisation.
		textNode.nodeValue = textNode.nodeValue.replace(wordRegex, match => {
			count++

			return matchCase(match, replacement)
		})
	}

	// Merge annotation-split siblings so TinyMCE's serializer stays clean, then
	// re-run analysis — programmatic edits don't fire the keyup/paste that drives it.
	body.normalize()
	editor.undoManager.add()
	editor.setDirty(true)
	if (triggerReanalysis) {
		requestPostUpdate(500)
	}

	return count
}

/**
 * Replaces every whole-word occurrence of a misspelled word across the whole
 * document, without needing any painted highlight marks.
 *
 * The Spelling tab's word list is built from the assessment, not the editor's
 * highlight marks, so its fix action must work even when spelling highlighting
 * is toggled off (no marks exist).
 *
 * @since 5.0.0
 *
 * @param {string}  word              The misspelled word to replace.
 * @param {string}  replacement       The replacement word.
 * @param {boolean} triggerReanalysis Re-run analysis after editing. Pass false when the caller re-analyzes itself.
 * @returns {Promise<number>}         The count of occurrences replaced across the document (0 means none).
 */
export async function replaceWordInContent (word, replacement, triggerReanalysis = true) {
	lastWriteRefusal = null

	if (!word || !replacement) {
		return 0
	}

	const adapter = resolveAdapter()
	if ('function' === typeof adapter?.replaceAllOccurrences) {
		const count = await adapter.replaceAllOccurrences(word, replacement, { triggerReanalysis })
		if (!count) {
			lastWriteRefusal = adapter.consumeWriteRefusal?.() || null
		}

		return count
	}

	// A term has no editor — its description textarea is the analysed content.
	const termDescription = document.querySelector('#edittag textarea#description')
	if (termDescription) {
		return replaceAllWordInTermDescription(termDescription, word, replacement, triggerReanalysis)
	}

	if (!isBlockEditor()) {
		return replaceAllWordInClassicEditor(word, replacement, triggerReanalysis)
	}

	const blocks = window.wp?.data?.select?.('core/block-editor')?.getBlocks?.() || []
	if (!blocks.length) {
		return 0
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

	return totalCount
}