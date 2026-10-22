import { iframeRef } from '@/vue/standalone/page-builders/helpers'

const positions = [ 'left', 'right', 'top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right' ]

const rootClassesToRemove = positions.flatMap(pos => [
	`aioseo-settings-bar-root-${pos}`,
	`aioseo-settings-bar-root-is-mobile-${pos}`,
	`aioseo-settings-bar-root-is-desktop-${pos}`
]).concat([ 'aioseo-settings-bar-root-is-mobile', 'aioseo-settings-bar-root-is-desktop' ])

const bodyPositionClasses = positions.map(pos => `aioseo-settings-bar-is-${pos}`)

const safeModalSelectors = [
	'.aioseo-pagebuilder-modal',
	'.aioseo-app.aioseo-post-settings-modal',
	'.el-popper',
	'.media-modal',
	// Keyword/language menus and tooltips teleport into this body-level portal, so
	// a click on a menu item lands outside the modal — treat the portal as inside,
	// otherwise picking an option (e.g. Edit) closes the whole panel.
	'#aioseo-popper-portal'
]

const mediaQuery = window.matchMedia('(min-width: 768px)')
let settingsBarRoot = null,
	observer = null

// Helper to get bar element (intentionally re-queried as Divi may recreate it).
const getBar = () => document.querySelector('.et-fb-page-settings-bar')

/**
 * Detach a node from the DOM.
 *
 * @param   {HTMLElement} node The node to detach.
 * @returns {HTMLElement}      The detached node.
 */
const detach = (node) => {
	node?.remove()
	return node
}

/**
 * Check if the settings bar is active.
 *
 * @returns {boolean} True if active.
 */
const isSettingsBarActive = () => getBar()?.classList.contains('et-fb-page-settings-bar--active') ?? false

/**
 * Check if the settings bar is being dragged.
 *
 * @returns {boolean} True if dragged.
 */
const isSettingsBarDragged = () => {
	const bar = getBar()
	return bar?.classList.contains('et-fb-page-settings-bar--dragged') && !isSettingsBarActive()
}

/**
 * Check if the settings bar is attached to the DOM.
 *
 * @returns {boolean} True if attached.
 */
const isSettingsBarAttached = () =>
	document.documentElement !== settingsBarRoot && document.documentElement.contains(settingsBarRoot)

/**
 * Get the current position of the settings bar.
 *
 * @returns {string} The position string.
 */
const getSettingsBarPosition = () => {
	const classes = getBar()?.classList
	if (!classes) {
		return ''
	}

	const has = (cls) => classes.contains(`et-fb-page-settings-bar--${cls}`)

	if (has('vertical--right')) {
		return 'right'
	}
	if (has('vertical--left')) {
		return 'left'
	}
	if (has('horizontal') && !has('top')) {
		return 'bottom'
	}
	if (has('top') && !has('corner')) {
		return 'top'
	}
	if (has('bottom-corner')) {
		return has('left-corner') ? 'bottom-left' : 'bottom-right'
	}
	if (has('top-corner')) {
		return has('left-corner') ? 'top-left' : 'top-right'
	}

	return ''
}

/**
 * Update the settings bar class names based on position and device.
 *
 * @param   {string} position The position.
 * @returns {void}
 */
const updateSettingsBarClasses = (position) => {
	const { classList } = settingsBarRoot

	// Single call to remove all position-related classes.
	classList.remove(...rootClassesToRemove)

	// Add new classes in one call.
	const device = mediaQuery.matches ? 'desktop' : 'mobile'
	classList.add(
		`aioseo-settings-bar-root-${position}`,
		`aioseo-settings-bar-root-is-${device}`,
		`aioseo-settings-bar-root-is-${device}-${position}`
	)
}

/**
 * Toggle body classes based on settings bar activity.
 *
 * @returns {void}
 */
const toggleBodyClasses = () => {
	const active = isSettingsBarActive()
	document.body.classList.toggle('aioseo-settings-bar-is-active', active)
	document.body.classList.toggle('aioseo-settings-bar-is-inactive', !active)
}

/**
 * Attach the settings bar to the DOM.
 *
 * @param   {string} position The position.
 * @returns {void}
 */
const attachSettingsBar = (position) => {
	if (isSettingsBarAttached()) {
		return
	}

	const bar = getBar()
	if (!bar) {
		return
	}

	updateSettingsBarClasses(position)

	if (isSettingsBarActive()) {
		const isDesktop = mediaQuery.matches
		const column = bar.querySelector(isDesktop
			? '.et-fb-page-settings-bar__column--main'
			: '.et-fb-page-settings-bar__column--left'
		)
		if (column) {
			if (isDesktop) {
				column.appendChild(settingsBarRoot)
			} else {
				column.insertBefore(settingsBarRoot, column.firstChild)
			}
		}
	} else {
		bar.querySelector('.et-fb-page-settings-bar__toggle-button')
			?.insertAdjacentElement('afterend', settingsBarRoot)
	}
}

/**
 * Handle settings bar position/class changes.
 *
 * @returns {void}
 */
const onSettingsBarChange = () => {
	const position = getSettingsBarPosition()

	// Single call to remove all position classes, then add current.
	document.body.classList.remove(...bodyPositionClasses)
	document.body.classList.add(`aioseo-settings-bar-is-${position}`)

	toggleBodyClasses()

	if (!isSettingsBarDragged()) {
		detach(settingsBarRoot)
		attachSettingsBar(position)
	}
}

/**
 * Check if the AIOSEO modal is open.
 *
 * @returns {boolean} True if open.
 */
const isModalOpen = () => {
	const modal = document.querySelector('.aioseo-pagebuilder-modal')
	return modal && !modal.classList.contains('aioseo-pagebuilder-modal-is-closed')
}

/**
 * Close the AIOSEO modal.
 *
 * @returns {void}
 */
const closeModal = () => {
	document.dispatchEvent(new Event('aioseo-pagebuilder-toggle-modal', { open: false }))
}

/**
 * Hide the modal when a highlight jump scrolls the canvas.
 * NOTE: The panel is drawn over the canvas, so the word a jump lands on would sit under it.
 *
 * @returns {void}
 */
const hideModalOnEditorJump = () => {
	if (isModalOpen()) {
		closeModal()
	}
}

/**
 * Hide the modal when clicking outside.
 *
 * @param   {Event} event The click event.
 * @returns {void}
 */
const hideModalOnOutsideClick = (event) => {
	if (!isModalOpen()) {
		return
	}

	const { target } = event

	// Removing a keyword unmounts its row, and the popper tears its teleported menu
	// out of #aioseo-popper-portal on the same click — so by the time this bubbled
	// handler runs the clicked node is already detached and closest() can no longer
	// see the portal. A detached target is always our own UI reacting to this very
	// click, never a genuine outside click, so treat it as inside.
	if (!target.isConnected) {
		return
	}

	const isSafe = safeModalSelectors.some(s => target.closest(s)) ||
		target === settingsBarRoot ||
		target.classList?.contains('aioseo')

	if (!isSafe) {
		closeModal()
	}
}

/**
 * Initialize the settings bar integration.
 *
 * @returns {void}
 */
export default () => {
	settingsBarRoot = document.querySelector('#aioseo-settings')
	if (!settingsBarRoot) {
		return
	}

	settingsBarRoot = detach(settingsBarRoot)
	toggleBodyClasses()

	const position = getSettingsBarPosition()
	document.body.classList.add(`aioseo-settings-bar-is-${position}`)
	attachSettingsBar(position)

	mediaQuery.addEventListener('change', () => {
		detach(settingsBarRoot)
		attachSettingsBar(getSettingsBarPosition())
	})

	const bar = getBar()
	if (bar) {
		observer = new MutationObserver(onSettingsBarChange)
		observer.observe(bar, { attributeFilter: [ 'class' ] })
	}

	document.addEventListener('click', hideModalOnOutsideClick)
	iframeRef('et-fb-app-frame')?.addEventListener('click', hideModalOnOutsideClick)
	document.addEventListener('aioseo-truseo-editor-jump', hideModalOnEditorJump)

	settingsBarRoot.addEventListener('click', () => {
		document.dispatchEvent(new Event('aioseo-pagebuilder-toggle-modal'))
	})
}