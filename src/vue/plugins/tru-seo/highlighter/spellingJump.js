import { wordBoundaryPattern } from '@/vue/utils/regex'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'

/**
 * The editor's content root(s), for locating a word without any painted marks.
 *
 * NOTE: scoped to the editor — never the panel — so a text search here can't match the
 * same word shown in the Spelling tab's own list.
 *
 * @returns {Array} The content root elements, in search order.
 */
export const getEditorContentRoots = () => {
	const roots = []

	const canvas = document.querySelector('iframe[name="editor-canvas"]')
	if (canvas?.contentDocument?.body) {
		roots.push(canvas.contentDocument.body)
	} else {
		const blockRoot = document.querySelector('.editor-styles-wrapper .is-root-container')
		if (blockRoot) {
			roots.push(blockRoot)
		}
	}

	const classicFrame = document.getElementById('content_ifr')
	if (classicFrame?.contentDocument?.body) {
		roots.push(classicFrame.contentDocument.body)
	}

	// A builder with its own adapter keeps its content in a canvas the roots above can't reach.
	// Only for `ownsPageBuilder`: an adapterless builder resolves to block/classic, covered above.
	const adapter = resolveAdapter()
	if (adapter?.ownsPageBuilder) {
		const adapterRoot = adapter.getRootNode?.()
		if (adapterRoot) {
			roots.push(adapterRoot)
		}
	}

	return roots
}

/**
 * Locates the first whole-word occurrence of `word` in the editor content.
 *
 * NOTE: marks-independent, so a jump works with spelling highlighting off.
 *
 * @param   {string}       word The word to locate.
 * @returns {?HTMLElement}      The element to scroll to, or null.
 */
export const findWordInEditorContent = (word) => {
	const regex = new RegExp(wordBoundaryPattern(word), 'u')

	for (const root of getEditorContentRoots()) {
		const doc = root.ownerDocument || document
		const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT)
		let node

		while (null !== (node = walker.nextNode())) {
			if (regex.test(node.nodeValue || '')) {
				return node.parentElement
			}
		}
	}

	return null
}

/**
 * Opens whatever collapsed editor container hides `node`, before a jump scrolls to it.
 *
 * @param   {?HTMLElement} node The jump target.
 * @returns {void}
 */
export const revealInEditor = (node) => {
	if (!node) {
		return
	}

	try {
		resolveAdapter()?.revealNode?.(node)
	} catch (_e) {
		// A failed reveal still leaves the plain scroll.
	}
}

/**
 * Tells editor chrome that a jump from the panel has started scrolling the content to `target`.
 *
 * NOTE: a panel drawn over the canvas (Divi's modal) listens to get out of the way. A target that
 * fell back to the document root shows nothing, so the panel stays.
 *
 * @param   {HTMLElement} target The element the jump scrolls to.
 * @returns {void}
 */
export const announceEditorJump = (target) => {
	const doc = target?.ownerDocument
	if (!doc || target === doc.body || target === doc.documentElement) {
		return
	}

	document.dispatchEvent(new CustomEvent('aioseo-truseo-editor-jump'))
}

/**
 * The height of chrome the editor keeps fixed over the top of its own content, for a jump's margin.
 *
 * @returns {number} Pixels, or 0.
 */
export const getEditorStickyOffset = () => {
	try {
		return Math.max(0, Math.round(resolveAdapter()?.getStickyTopOffset?.() || 0))
	} catch (_e) {
		return 0
	}
}

/**
 * Gets the element a jump scrolls to and the popover anchors on: `node`, or while it renders no box,
 * its nearest ancestor that does.
 * NOTE: a word inside a container the adapter could not open then points at that container
 * instead of a 0x0 box at the viewport origin, which reads as "scrolled away" and hides the popover.
 *
 * @param   {?HTMLElement} node The jump target.
 * @returns {?HTMLElement}      The element to scroll to.
 */
export const getVisibleAnchor = (node) => {
	try {
		for (let current = node; current; current = current.parentElement) {
			if (current.getClientRects().length) {
				return current
			}
		}
	} catch (_e) {
		// Measured as the node itself below.
	}

	return node
}

/**
 * Re-resolves a page builder content unit's node from its id.
 *
 * NOTE: not `mark.parent`, which can be a detached node from before the widget re-rendered.
 *
 * @param   {Array}        unitIds Unit ids to try, in order.
 * @returns {?HTMLElement}         The first unit that resolves to a live node, or null.
 */
export const findPageBuilderUnitNode = (unitIds) => {
	// Same guard as getEditorContentRoots(): the generic adapters address no builder units.
	const adapter = resolveAdapter()
	if (!adapter?.ownsPageBuilder || !unitIds?.length) {
		return null
	}

	// Per unit, never through getContentUnits(), which is quadratic in element count and would
	// block the main thread on exactly the large pages that reach this arm.
	for (const unitId of unitIds) {
		const node = adapter.getUnitNode?.(unitId)
		if (node) {
			return node
		}
	}

	return null
}