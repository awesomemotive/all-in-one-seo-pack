import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

export const truSeoShouldAnalyze = () => {
	const postEditorStore = usePostEditorStore()
	if (!postEditorStore?.currentPost?.id) {
		return false
	}

	return !!postEditorStore.currentPost?.isTruSeoEligible || false
}

// Whether the post's type supports the Optimization tab's analysis features
// (TruSEO and the Headline Analyzer), regardless of the TruSEO master toggle.
export const supportsPageAnalysis = () => {
	const postEditorStore = usePostEditorStore()

	return !!postEditorStore?.currentPost?.supportsPageAnalysis
}

// Resolved server-side and localized with the list payload: the site options this used to read
// are stripped for users who cannot manage AIOSEO, so they read as "TruSEO is off".
// {@see \AIOSEO\Plugin\Common\Standalone\DetailsColumn::isTruSeoScoreEnabled()}
export const shouldShowTruSeoScore = () => {
	const rootStore = useRootStore()

	return !!rootStore.aioseo.truSeoScoreEnabled
}

/**
 * Reverses a Selection object in order to modify it from left to right.
 *
 * @since 4.4.6
 *
 * @param   {Object} selection The Selection object.
 * @returns {Object}           The reversed Selection object.
 */
export const reverseWindowSelection = (selection) => {
	const selectionRange = selection.getRangeAt(0)
	const cloneRange = selectionRange.cloneRange()

	cloneRange.collapse(false)
	selection.removeAllRanges()
	selection.addRange(cloneRange)
	selection.extend(selectionRange.startContainer, selectionRange.startOffset)

	return selection
}

/**
 * Normalizes whitespace characters.
 *
 * @since 4.4.6
 *
 * @param   {string} string The string.
 * @returns {string}        The normalized string.
 */
// U+200B and U+FEFF are formatting an editor leaves inside words (Elementor's inline pen keeps a
// U+200B), and a word carrying one fails the spell check. Only these two: U+200C and U+200D are
// part of words in Persian, Arabic and Indic scripts, and of emoji sequences.
const ZERO_WIDTH_FORMATTING = /[\u200B\uFEFF]/g

export const normalizeWhitespaces = (string) => {
	const NBSP = new RegExp(String.fromCharCode(160), 'g')

	return string
		.replace(/&nbsp;/g, ' ')
		.replace(NBSP, ' ')
		.replace(ZERO_WIDTH_FORMATTING, '')
}

/**
 * Finds the closest parent node of a given element that has a specific property value.
 *
 * @since 4.4.6
 *
 * @param   {Object}  options          The options for finding the node.
 * @param   {Element} options.element  The element from which to start searching.
 * @param   {string}  options.property The CSS property to compare against.
 * @param   {string}  options.value    The value to match against the property value.
 * @returns {Element}                  The closest element with the specified property value, or the html root element if not found.
 */
export const getClosestNodeByPropertyValue = ({ element, property, value }) => {
	const ownerDoc = element?.ownerDocument || document

	if (!element) {
		return ownerDoc.documentElement
	}

	let parent = element.parentElement
	while (parent) {
		if (
			parent.isEqualNode(ownerDoc.documentElement) ||
			value === ownerDoc.defaultView?.getComputedStyle(parent).getPropertyValue(property)
		) {
			return parent
		}

		parent = parent.parentElement
	}

	return ownerDoc.documentElement
}

/**
 * Creates a highlight popover container node.
 *
 * @since 4.4.6
 *
 * @returns {HTMLElement} The created popover node element wrapper.
 */
export const createHighlightPopoverNode = () => {
	const el = document.createElement('div')

	el.classList.add('aioseo-app')
	// Fixed + appended to <body> so the popover escapes the editor's
	// `overflow: hidden` clip; positioning keeps it inside the visible content.
	el.style.position = 'fixed'
	el.style.zIndex = '100000'
	el.style.width = '100%'
	el.style.maxWidth = '400px'
	el.setAttribute('tabindex', -1)

	return el
}

/**
 * Whether the additional-keyword limit has been reached.
 *
 * NOTE: A limit of 0 means unlimited — the keyword list paginates, so there is no UI reason to cap
 * it. Kept configurable via the `aioseo_post_additional_keywords_limit` filter.
 *
 * @param {number} limit The configured limit.
 * @param {number} count The number of additional keywords already added.
 * @returns {boolean}    Whether no more keywords may be added.
 */
export const additionalKeywordLimitReached = (limit, count) => {
	const max = parseInt(limit, 10)

	return !isNaN(max) && 0 < max && max <= count
}