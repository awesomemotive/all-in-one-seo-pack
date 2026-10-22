import { nextTick } from 'vue'

import {
	STORE_NAME as HIGHLIGHT_STORE_NAME,
	getFormatClassName
} from './wpDataStore'

import {
	annotateTinyMce,
	registerTinyMceAnnotators,
	buildHighlightStateCss
} from './tinymce'

import { overlapsSkipped } from './textStream'
import { BLOCK_TEXT_ATTRIBUTES, ZERO_WIDTH, getNativeBlockSkipped, getNativeBlockText, getRawBlockText } from './blockText'

import { escapeRegex, wordBoundaryPattern } from '@/vue/utils/regex'

export { getNativeBlockSkipped, getNativeBlockText, isAnalyzerSkippedBlock } from './blockText'

/**
 * Builds a CSS rule for Block Editor custom format marks.
 *
 * @param {string} className The format class name.
 * @param {string} color     The highlight background color (hex).
 * @returns {string} CSS rule string.
 */
export const buildBlockFormatCss = (className, color) => {
	return buildHighlightStateCss(`mark.${className}`, color)
}

/**
 * Returns a Classic block's own TinyMCE, the only place its text can be marked.
 *
 * NOTE: it boots lazily, and an iframed canvas only shows a static preview.
 *
 * @param {Object} block The Gutenberg block object.
 * @returns {Object|null} The TinyMCE editor, or null while it isn't ready.
 */
export const getFreeformEditor = (block) => {
	try {
		const editor = window.tinymce?.get(`editor-${block?.clientId}`)

		return 'function' === typeof editor?.getBody ? editor : null
	} catch (_e) {
		return null
	}
}

/**
 * Determines which RichText attribute identifier a block uses for its
 * primary text content.
 *
 * @param {Object} block The Gutenberg block object.
 * @returns {string} The RichText identifier ('content', 'caption', 'value', 'citation', or a {@see BLOCK_TEXT_ATTRIBUTES} one).
 */
export const getRichTextIdentifier = (block) => {
	if (BLOCK_TEXT_ATTRIBUTES[block?.name]) {
		return BLOCK_TEXT_ATTRIBUTES[block.name]
	}

	if (block?.attributes?.caption) {
		return 'caption'
	}

	if (block?.attributes?.value) {
		return 'value'
	}

	if (block?.attributes?.citation) {
		return 'citation'
	}

	return 'content'
}

/**
 * Maps offsets in getNativeBlockText()'s space back to the block's rich text.
 *
 * NOTE: normalizeWhitespaces deletes U+200B/U+FEFF, so without this every range after one lands
 * a character early per deleted character.
 *
 * @param {Object} block The Gutenberg block object.
 * @returns {Function} Maps a `{ start, end }` range to rich-text offsets.
 */
const getRichTextRangeMapper = (block) => {
	const raw = getRawBlockText(block)
	if (!ZERO_WIDTH.test(raw)) {
		return (range) => range
	}

	// kept[i] is the raw index of the i-th character that survives normalization.
	const kept = []
	for (let i = 0; i < raw.length; i++) {
		if (!ZERO_WIDTH.test(raw[i])) {
			kept.push(i)
		}
	}

	const toRaw = (offset) => (offset < kept.length ? kept[offset] : raw.length)

	return ({ start, end }) => ({
		start : toRaw(start),
		end   : 0 < end ? toRaw(end - 1) + 1 : toRaw(end)
	})
}

/**
 * First-occurrence character range of a flagged sentence within block text.
 * Mirrors the matching used when marks are first built, so a re-anchored range
 * lands exactly where the original one did.
 *
 * NOTE: a local copy of {@see utils/regex.js}'s `sentencePattern`; keep the two in step.
 *
 * @param {string} content   The block's plain text.
 * @param {string} sentence  The flagged sentence (or single word).
 * @param {Array}  [skipped] Ranges of `content` the analyzer drops ({@see getNativeBlockSkipped}).
 * @returns {{start: number, end: number}|null} The range, or null when not found.
 */
export const findSentenceRange = (content, sentence, skipped = []) => {
	if (!content || !sentence) {
		return null
	}

	const isSingleWord = !sentence.includes(' ')
	const pattern = isSingleWord ? wordBoundaryPattern(sentence) : escapeRegex(sentence)
	const regex   = new RegExp(pattern, isSingleWord ? 'gu' : 'g')
	let match

	while (null !== (match = regex.exec(content))) {
		const range = {
			start : match.index,
			end   : (match.index + match[0].length) || 1
		}

		if (!overlapsSkipped(range, skipped)) {
			return range
		}
	}

	return null
}

/**
 * Re-anchors each native-block mark's range to the current block content.
 *
 * Highlight ranges are absolute character offsets into a block's rich text, so a
 * fix that rewrites text earlier in the same block leaves every later mark's
 * offsets stale — the RichText format then paints a partial, unclickable
 * highlight. Recomputing against the live block content before re-dispatch keeps
 * the surviving marks aligned without waiting on the debounced re-analysis.
 *
 * Freeform (Classic-in-block) marks are annotated via TinyMCE rather than this
 * store, so they are skipped. Mutates the passed marks in place.
 *
 * @param {Array} marks The highlight marks to re-anchor.
 * @returns {void}
 */
export const recomputeNativeBlockMarkRanges = (marks) => {
	const select = window?.wp?.data?.select?.('core/block-editor')
	if (!select) {
		return
	}

	for (const hm of (marks || [])) {
		if (!hm?.block || 'core/freeform' === hm.block.name) {
			continue
		}

		const block = select.getBlock(hm.block.clientId)
		if (!block) {
			continue
		}

		const range = findSentenceRange(getNativeBlockText(block), hm.sentence, getNativeBlockSkipped(block))
		if (range) {
			hm.range = range
		}
	}
}

/**
 * Dispatches highlight range data to the `aioseo/tru-seo-highlights`
 * WordPress data store. Groups highlight marks by block + richText
 * identifier + analyzer, then dispatches each group. The custom
 * RichText format types read from this store to apply non-persistent
 * formatting in the editor.
 *
 * @param {Array} marks The highlight marks array from the store.
 * @returns {void}
 */
export const dispatchBlockHighlights = (marks) => {
	if (!window?.wp?.data?.dispatch) {
		return
	}

	// Page builders can load wp.data without the block editor that registers this store.
	const dispatch = window.wp.data.dispatch(HIGHLIGHT_STORE_NAME)
	if (!dispatch) {
		return
	}

	dispatch.clearAll()

	const select     = window.wp.data.select?.('core/block-editor')
	const mappers    = new Map()
	const marksByKey = {}
	for (const hm of marks) {
		if (!hm.block || 'core/freeform' === hm.block.name) {
			continue
		}

		if (!mappers.has(hm.block.clientId)) {
			mappers.set(hm.block.clientId, getRichTextRangeMapper(select?.getBlock?.(hm.block.clientId) || hm.block))
		}

		const identifier = getRichTextIdentifier(hm.block)
		const key = `${hm.block.clientId}:${identifier}:${hm.analyzer}`

		if (!marksByKey[key]) {
			marksByKey[key] = {
				blockClientId      : hm.block.clientId,
				richTextIdentifier : identifier,
				analyzer           : hm.analyzer,
				ranges             : []
			}
		}

		marksByKey[key].ranges.push({
			id : hm.id,
			...mappers.get(hm.block.clientId)(hm.range)
		})
	}

	for (const entry of Object.values(marksByKey)) {
		dispatch.addHighlights(
			entry.blockClientId,
			entry.richTextIdentifier,
			entry.analyzer,
			entry.ranges
		)
	}
}

/**
 * Attempts to resolve the DOM node for a single highlight mark entry.
 *
 * @param {Object} hm A highlight mark object.
 * @returns {boolean} True if the node was found or lookup was skipped.
 */
const resolveHighlightNode = (hm) => {
	if (hm.node) {
		return true
	}

	const className = getFormatClassName(hm.analyzer)
	if (!hm.parent) {
		return true
	}

	const markNodes = hm.parent.querySelectorAll(`mark.${className}`)
	for (const markNode of markNodes) {
		if (hm.sentence === markNode.textContent || -1 !== markNode.id.indexOf(hm.id)) {
			hm.node = markNode
			break
		}
	}

	if (!hm.node && markNodes.length) {
		hm.node = markNodes[0]
	}

	return !!hm.node
}

/**
 * Polls the DOM for Block Editor highlight mark nodes (custom format
 * `<mark>` elements) until all are found or max attempts are reached.
 * Once all marks are located (or the limit is hit), triggers the
 * onMarksReady callback to attach hover listeners.
 *
 * @param {Array}    marks          The block highlight marks to find nodes for.
 * @param {Function} onMarksReady   Callback invoked when mark nodes are available.
 * @param {number}   [attempt=0]    Current polling attempt.
 * @returns {void}
 */
export const pollForBlockHighlightNodes = (marks, onMarksReady, attempt = 0) => {
	const maxAttempts = 10
	let allFound = true

	for (const hm of marks) {
		if (!resolveHighlightNode(hm)) {
			allFound = false
		}
	}

	if (!allFound && attempt < maxAttempts) {
		setTimeout(() => {
			pollForBlockHighlightNodes(marks, onMarksReady, attempt + 1)
		}, 100)

		return
	}

	onMarksReady()
}

/**
 * Orchestrates highlighting for the Gutenberg Block Editor.
 * Iterates all blocks, handles `core/freeform` blocks via TinyMCE
 * annotation, and dispatches highlight data to the wp.data store
 * for native blocks using custom RichText format types.
 *
 * @param {Object}   options                       The options object.
 * @param {Array}    options.blocks                Array of top-level blocks from `getBlocks()`.
 * @param {Object}   options.store                 The TruSeoHighlighter Pinia store.
 * @param {string}   options.sourcePrefix          The annotation source prefix.
 * @param {Set}      options.registeredAnnotators  Set tracking registered editor:source pairs.
 * @param {Function} options.setHighlightMarks     Callback to build highlight marks for a block/node.
 * @param {Function} options.observeMarkParent     Callback to observe a mark's parent for DOM changes.
 * @param {Function} options.onMarksReady          Callback invoked when mark nodes are available (attaches hover listeners).
 * @param {Map}      [options.streamCache]         Optional `Map<parentNode, {text, segments}>` for O(log n) range resolution on freeform marks.
 * @returns {void}
 */
export const highlightBlockEditor = ({ blocks, store, sourcePrefix, registeredAnnotators, setHighlightMarks, observeMarkParent, onMarksReady, streamCache }) => {
	for (const block of (blocks || [])) {
		if ('core/freeform' === block.name) {
			// A subsequent reset (triggered by the editor becoming available, or by the next
			// analyzer update) picks it up once it boots.
			const editor = getFreeformEditor(block)
			if (!editor) {
				continue
			}

			const editorChildren = editor.getBody()?.children || []
			if (!editorChildren.length) {
				continue
			}

			registerTinyMceAnnotators(editor, {
				analyzers     : store.highlightAnalyzers,
				sourcePrefix  : sourcePrefix,
				registeredSet : registeredAnnotators
			})

			for (const node of editorChildren) {
				setHighlightMarks({ block, node })
			}

			continue
		}

		setHighlightMarks({ block, node: null })
	}

	dispatchBlockHighlights(store.highlightMarks)

	const freeformMarks = store.highlightMarks.filter(hm => 'core/freeform' === hm.block?.name)

	// Observe freeform parents for the annotation spans TinyMCE adds;
	// setHighlightMarkNode wires up the click listener as each node lands.
	for (const hm of freeformMarks) {
		observeMarkParent(hm.parent)
	}

	// Annotate marks per-parent in descending offset order so each
	// `surroundContents` split only invalidates segments we no longer
	// need. See `classicEditor.js` for the full rationale.
	const marksByParent = new Map()
	for (const hm of freeformMarks) {
		if (!marksByParent.has(hm.parent)) {
			marksByParent.set(hm.parent, [])
		}
		marksByParent.get(hm.parent).push(hm)
	}

	for (const [ parent, marks ] of marksByParent) {
		marks.sort((a, b) => b.range.start - a.range.start)
		const segments = streamCache?.get(parent)?.segments || null
		const editor = window.tinymce.get(`editor-${marks[0].block.clientId}`)
		if (!editor) {
			continue
		}

		for (const hm of marks) {
			annotateTinyMce(hm, editor, sourcePrefix, segments)
		}
	}

	const blockMarks = store.highlightMarks.filter(hm => 'core/freeform' !== hm.block?.name)
	if (blockMarks.length) {
		nextTick().then(() => {
			pollForBlockHighlightNodes(blockMarks, onMarksReady)
		})
	}
}

export { getFormatClassName }