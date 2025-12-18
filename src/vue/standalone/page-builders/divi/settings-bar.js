import { iframeRef } from '@/vue/standalone/page-builders/helpers'

/**
 * Detach the element from its parent.
 *
 * @param   {Object} node The DOM node.
 * @returns {Object}      The DOM node.
 */
const detach = (node) => {
	return node.parentElement.removeChild(node)
}

/**
 * Toggle the body classes depending whether settings bar is active or not.
 *
 * @returns {void}
 */
const toggleBodyClasses = () => {
	const active = isSettingsBarActive()
	document.body.classList.toggle('aioseo-settings-bar-is-active', active)
	document.body.classList.toggle('aioseo-settings-bar-is-inactive', !active)
}

/**
 * Attach the settings bar to the correct position.
 *
 * @returns {void}
 */
const initSettingsBar = () => {
	const position = getSettingsBarPosition()
	removePositionalClassNames(document.body, 'aioseo-settings-bar-is')
	document.body.classList.add(`aioseo-settings-bar-is-${position}`)
	attachSettingsBar(position)
}

/**
 * Add the event listeners.
 *
 * @returns {void}
 */
const addEventListeners = () => {
	// When the window change size, re-attach the settings button.
	settingsBarMediaQuery.addEventListener('change', () => {
		detachSettingsBar()
		attachSettingsBar(getSettingsBarPosition())
	})

	// When the settings bar change position, re-attach the settings button.
	settingsBarObserver.observe(
		document.querySelector('.et-fb-page-settings-bar'),
		{ attributeFilter: [ 'class' ] }
	)

	// When click outside our modal, close it.
	document.addEventListener('click', hideModalOnOutsideClick)
	iframeRef('et-fb-app-frame').addEventListener('click', hideModalOnOutsideClick)

	// When click on the settings bar, open the modal.
	settingsBarRoot.addEventListener('click', () => {
		const event = new Event('aioseo-pagebuilder-toggle-modal')
		document.dispatchEvent(event)
	})
}

/**
 * Re-attach the settings button when the settings bar change position.
 *
 * @returns {void}
 */
const onSettingsBarClassAttrChange = () => {
	const position = getSettingsBarPosition()
	removePositionalClassNames(document.body, 'aioseo-settings-bar-is')
	document.body.classList.add(`aioseo-settings-bar-is-${position}`)
	toggleBodyClasses()
	detachSettingsBar()
	if (!isSettingsBarDragged()) {
		attachSettingsBar(position)
	}
}

/**
 * Detach the settings bar.
 *
 * @returns {void}
 */
const detachSettingsBar = () => {
	if (!isSettingsBarAttached()) {
		return
	}
	settingsBarRoot = detach(settingsBarRoot)
}

/**
 * Attach the settings bar.
 *
 * @param   {string} position The position to attach.
 * @returns {void}
 */
const attachSettingsBar = (position) => {
	if (isSettingsBarAttached()) {
		return
	}

	const pageSettingsBar = document.querySelector('.et-fb-page-settings-bar')
	const pageSettingsBarToggleButton = pageSettingsBar.querySelector('.et-fb-page-settings-bar__toggle-button')
	const pageSettingsBarColumn = pageSettingsBar.querySelectorAll('.et-fb-page-settings-bar__column')

	toggleSettingsBarClassNames(position)

	if (isSettingsBarActive()) {
		if (settingsBarMediaQuery.matches) {
			const barColumn = [ ...pageSettingsBarColumn ].filter(el => el.classList.contains('et-fb-page-settings-bar__column--main'))
			if (barColumn.length) {
				barColumn[0].appendChild(settingsBarRoot)
			}
		} else {
			const barColumn = [ ...pageSettingsBarColumn ].filter(el => el.classList.contains('et-fb-page-settings-bar__column--left'))
			if (barColumn.length) {
				barColumn[0].insertBefore(settingsBarRoot)
			}
		}
	} else {
		pageSettingsBarToggleButton.insertAdjacentElement('afterend', settingsBarRoot)
	}
}

/**
 * Toggle the settings bar class names.
 *
 * @param   {string} position The settings bar position.
 * @returns {void}
 */
const toggleSettingsBarClassNames = (position) => {
	removePositionalClassNames(settingsBarRoot, 'aioseo-settings-bar-root')
	settingsBarRoot.classList.add(`aioseo-settings-bar-root-${position}`)

	removePositionalClassNames(settingsBarRoot, 'aioseo-settings-bar-root-is-mobile')
	const mobile = [
		'aioseo-settings-bar-root-is-mobile',
		`aioseo-settings-bar-root-is-mobile-${position}`
	]
	mobile.forEach(className => {
		settingsBarRoot.classList.toggle(
			className,
			!settingsBarMediaQuery.matches
		)
	})

	removePositionalClassNames(settingsBarRoot, 'aioseo-settings-bar-root-is-desktop')
	const desktop = [
		'aioseo-settings-bar-root-is-desktop',
		`aioseo-settings-bar-root-is-desktop-${position}`
	]
	desktop.forEach(className => {
		settingsBarRoot.classList.toggle(
			className,
			settingsBarMediaQuery.matches
		)
	})
}

/**
 * Remove the positional class names.
 *
 * @param   {Object} elem      The DOM element.
 * @param   {string} namespace The class namespace.
 * @returns {void}
 */
const removePositionalClassNames = (elem, namespace) => {
	const positionClassNameEndings = [
		`${namespace}-left`,
		`${namespace}-right`,
		`${namespace}-top`,
		`${namespace}-top-left`,
		`${namespace}-top-right`,
		`${namespace}-bottom`,
		`${namespace}-bottom-left`,
		`${namespace}-bottom-right`
	]

	elem.classList.remove(...positionClassNameEndings)
}

/**
 * Get the settings bar position.
 *
 * @returns {string} The settings bar position.
 */
const getSettingsBarPosition = () => {
	const barClasses = document.querySelector('.et-fb-page-settings-bar').classList

	if (barClasses.contains('et-fb-page-settings-bar--horizontal') && !barClasses.contains('et-fb-page-settings-bar--top')) {
		return 'bottom'
	}

	if (barClasses.contains('et-fb-page-settings-bar--top') && !barClasses.contains('et-fb-page-settings-bar--corner')) {
		return 'top'
	}

	if (barClasses.contains('et-fb-page-settings-bar--bottom-corner')) {
		return barClasses.contains('et-fb-page-settings-bar--left-corner')
			? 'bottom-left'
			: 'bottom-right'
	}

	if (barClasses.contains('et-fb-page-settings-bar--top-corner')) {
		return barClasses.contains('et-fb-page-settings-bar--left-corner')
			? 'top-left'
			: 'top-right'
	}

	if (barClasses.contains('et-fb-page-settings-bar--vertical--right')) {
		return 'right'
	}

	if (barClasses.contains('et-fb-page-settings-bar--vertical--left')) {
		return 'left'
	}

	return ''
}

/**
 * Hide our modal when user clicks outside it.
 *
 * @param   {Object} event The event object.
 * @returns {void}
 */
const hideModalOnOutsideClick = (event) => {
	if (!isModalOpen()) {
		return
	}

	const elem                 = event.target
	const seoModalSelector     = '.aioseo-pagebuilder-modal'
	const previewModalSelector = '.aioseo-app.aioseo-post-settings-modal'
	const elPopperSelector     = '.el-popper'
	const mediaModal           = '.media-modal'

	if (
		!elem.closest(seoModalSelector) &&
		!elem.closest(previewModalSelector) &&
		!elem.closest(elPopperSelector) &&
		!elem.closest(mediaModal) &&
		!(elem !== document.querySelector(seoModalSelector) && elem.contains(document.querySelector(seoModalSelector))) &&
		(elem.getAttribute('class') && !elem.getAttribute('class').includes('aioseo')) &&
		elem !== settingsBarRoot
	) {
		const toggleModalEvent = new Event('aioseo-pagebuilder-toggle-modal', { open: false })
		document.dispatchEvent(toggleModalEvent)
	}
}

/**
 * Check whether or not the SEO modal bar is open.
 *
 * @returns {bool} Whether the SEO modal is open or not.
 */
const isModalOpen = () => {
	return !document.querySelector('.aioseo-pagebuilder-modal').classList.contains('aioseo-pagebuilder-modal-is-closed')
}

/**
 * Check whether or not the settings bar is attached.
 *
 * @returns {bool} Whether the settings bar is attached or not.
 */
const isSettingsBarAttached = () => {
	return document.documentElement !== settingsBarRoot && document.documentElement.contains(settingsBarRoot)
}

/**
 * Check whether or not the settings bar is active.
 *
 * @returns {bool} Whether the settings bar is active or not.
 */
const isSettingsBarActive = () => {
	const pageSettingsBar = document.querySelector('.et-fb-page-settings-bar')
	return pageSettingsBar.classList.contains('et-fb-page-settings-bar--active')
}

/**
 * Check whether or not the settings bar is dragged.
 *
 * @returns {bool} Whether the settings bar is dragged or not.
 */
const isSettingsBarDragged = () => {
	const pageSettingsBar = document.querySelector('.et-fb-page-settings-bar')
	return pageSettingsBar.classList.contains('et-fb-page-settings-bar--dragged') && !isSettingsBarActive()
}

const settingsBarMediaQuery = window.matchMedia('(min-width: 768px)')
const settingsBarObserver = new MutationObserver(onSettingsBarClassAttrChange)
const settingsBarRootSelector = '#aioseo-settings'
let settingsBarRoot = document.querySelector(settingsBarRootSelector)
settingsBarRoot = detach(settingsBarRoot)

export default () => {
	toggleBodyClasses()

	initSettingsBar()

	addEventListeners()
}