// Opens the collapsed Elementor containers (accordion items, toggles, tabs) hiding a node, so a
// jump can scroll to it. Each one is opened through Elementor's own state, never a click: a click
// selects the element, and selecting swaps the editor panel the Spelling list lives in.
//
// Each check reads the widget's own "open" marker rather than measuring a box: an outer container
// may still be animating open, and re-opening an item that is already open closes a Toggle again.

// Legacy Accordion, Toggle and Tabs (one `baseTabs` handler), and Nested Tabs. Their handlers
// re-activate on `editSettings.activeItemIndex`, the setting the panel's own repeater writes.
const REPEATER_ITEMS = [
	{
		// `data-tab` is the 1-based index those handlers match titles and contents by.
		isClosed : (node) => node.matches('.elementor-tab-content[data-tab]:not(.elementor-active)'),
		index    : (panel) => parseInt(panel.getAttribute('data-tab'), 10)
	},
	{
		isClosed : (node) => node.matches('.e-con:not(.e-active)') && !!node.parentElement?.matches('.e-n-tabs-content'),
		index    : (panel) => [ ...panel.parentElement.children ].filter(child => child.matches('.e-con')).indexOf(panel) + 1
	}
]

const isClosedDetails = (element, node) => 'DETAILS' === element.nodeName &&
	!element.open &&
	!element.querySelector(':scope > summary')?.contains(node)

const isClosedAtomicTab = (element) => element.matches('[data-element_type="e-tab-content"]:not(.e--selected)')

const isClosed = (element, node) => isClosedDetails(element, node) ||
	isClosedAtomicTab(element) ||
	REPEATER_ITEMS.some(item => item.isClosed(element))

const getEditSettings = (widget) => {
	const id = widget?.getAttribute('data-id')

	return id ? window.elementor?.getContainer?.(id)?.model?.get?.('editSettings') || null : null
}

const openRepeaterItem = (panel) => {
	const item = REPEATER_ITEMS.find(candidate => candidate.isClosed(panel))
	if (!item) {
		return
	}

	const editSettings = getEditSettings(panel.parentElement?.closest('.elementor-widget'))
	const index        = item.index(panel)
	if (!editSettings || !(0 < index)) {
		return
	}

	// Still the active index means the author closed the item by hand since, and an unchanged
	// value fires no change event, so it is cleared silently first.
	if (index === editSettings.get('activeItemIndex')) {
		editSettings.set('activeItemIndex', null, { silent: true })
	}

	editSettings.set('activeItemIndex', index)
}

// Atomic (V4) Tabs keep the active tab in an Alpine store, so a class written here would be undone
// on its next render. Their preview handler listens for the Navigator's item click instead.
const openAtomicTab = (panel) => {
	const id = panel.getAttribute('data-id')
	if (!id) {
		return
	}

	const editorWindow = panel.ownerDocument?.defaultView?.parent || window
	editorWindow.dispatchEvent(new CustomEvent('elementor/navigator/item/click', { detail: { id, type: 'e-tab-content' } }))
}

const open = (container) => {
	if ('DETAILS' === container.nodeName) {
		// Nested and atomic Accordion items. In the editor nothing else opens an atomic one.
		container.open = true

		return
	}

	if (isClosedAtomicTab(container)) {
		openAtomicTab(container)

		return
	}

	openRepeaterItem(container)
}

/**
 * Opens every collapsed container around `node`, outermost first.
 *
 * NOTE: a container this does not recognise stays closed; the jump then falls back to its nearest
 * visible ancestor ({@see highlighter/spellingJump.js:getVisibleAnchor}).
 *
 * @param {HTMLElement} node The jump target.
 * @returns {void}
 */
export const revealElementorNode = (node) => {
	const closed = []

	for (let current = node; 1 === current?.nodeType; current = current.parentElement) {
		if (isClosed(current, node)) {
			closed.unshift(current)
		}
	}

	for (const container of closed) {
		try {
			open(container)
		} catch (_e) {
			// One container failing must not keep the others closed.
		}
	}
}