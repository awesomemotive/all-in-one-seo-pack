/**
 * Reveal helpers shared by both Divi adapters, which render the same container markup.
 */

// The theme's top bars and a Theme Builder header, which can each be fixed over the canvas.
const STICKY_HEADERS = '#top-header, #main-header, .et-l--header, .et-l--header .et_pb_sticky'

export const CAROUSEL_CLONE = '.et_pb_group_carousel_clone'

// Divi's own clicks switch these by writing inline `display`, not by moving the "open" classes, so
// a container counts as closed by what is rendered.
const isHidden = (element) => 'none' === element.ownerDocument.defaultView.getComputedStyle(element).display

const getToggleContent = (toggle) => Array.from(toggle.children).find(child => child.matches('.et_pb_toggle_content')) || null

// A word in the title is on screen either way.
const isClosedToggle = (element, node) => {
	const content = element.matches('.et_pb_toggle') ? getToggleContent(element) : null

	return !!content?.contains(node) && (element.classList.contains('et_pb_toggle_close') || isHidden(content))
}

const isClosedTab = (element) => element.matches('.et_pb_tab') &&
	!!element.parentElement?.matches('.et_pb_all_tabs') &&
	(isHidden(element) || false === getTabNavItem(element)?.classList.contains('et_pb_tab_active'))

const isClosedSlide = (element) => element.matches('.et_pb_slide') && !element.classList.contains('et-pb-active-slide')

const isClosedCarouselGroup = (element) => element.matches('.et_pb_group_carousel_slide') &&
	!getCarouselOriginal(element)?.classList.contains('et_pb_group_carousel_slide_active')

const getTabPanels = (panel) => Array.from(panel.parentElement.children).filter(child => child.matches('.et_pb_tab'))

// By position, as Divi's own tab click matches them: a WooCommerce Product Tabs panel has no `data-id`.
const getTabNavItem = (panel) => {
	const tabs = panel.parentElement.parentElement
	const nav  = Array.from(tabs.children).find(child => child.matches('.et_pb_tabs_controls'))

	return nav?.children[getTabPanels(panel).indexOf(panel)] || null
}

const getCarouselOriginals = (carousel) => Array.from(carousel.querySelectorAll(`.et_pb_group_carousel_slide:not(${CAROUSEL_CLONE})`))
	.filter(slide => slide.closest('.et_pb_group_carousel') === carousel)

const getCarouselOriginal = (slide) => {
	const carousel = slide.closest('.et_pb_group_carousel')

	return carousel ? getCarouselOriginals(carousel).find(original => original.dataset.slideId === slide.dataset.slideId) || null : null
}

// The state Divi's own toggle click ends in, minus the 700ms slide: an Accordion keeps one item open.
const openToggle = (toggle) => {
	if (toggle.matches('.et_pb_accordion_item')) {
		for (const sibling of Array.from(toggle.parentElement.children).filter(child => child !== toggle && child.matches('.et_pb_accordion_item.et_pb_toggle_open'))) {
			sibling.classList.replace('et_pb_toggle_open', 'et_pb_toggle_close')

			const content = getToggleContent(sibling)
			if (content) {
				content.style.display = 'none'
			}
		}
	}

	toggle.classList.replace('et_pb_toggle_close', 'et_pb_toggle_open')
	getToggleContent(toggle)?.style.removeProperty('display')
}

// The state the builder's own tab click ends in, minus the fade. That click keeps no state outside
// these classes and inline styles.
const openTab = (panel) => {
	const nav = getTabNavItem(panel)

	for (const sibling of getTabPanels(panel)) {
		const active = sibling === panel

		sibling.classList.toggle('et_pb_active_content', active)
		sibling.classList.toggle('et-pb-active-slide', active)
		sibling.style.display = active ? 'block' : 'none'
		sibling.style.opacity = active ? '1' : '0'
	}

	for (const item of Array.from(nav?.parentElement?.children || [])) {
		item.classList.toggle('et_pb_tab_active', item === nav)
	}
}

// Through the slider's own instance, which keeps the active index a class write would desync.
const openSlide = (slide) => {
	const slider   = slide.closest('.et_pb_slider')
	const instance = slider && slide.ownerDocument.defaultView.jQuery?.data(slider, 'et_pb_simple_slider')
	const index    = slider ? Array.from(slider.querySelectorAll('.et_pb_slide')).filter(item => item.closest('.et_pb_slider') === slider).indexOf(slide) : -1

	if (instance?.et_slider_move_to && 0 <= index) {
		instance.et_slider_move_to(index)
	}
}

const openCarouselGroup = (slide) => {
	const carousel = slide.closest('.et_pb_group_carousel')
	const index    = carousel ? getCarouselOriginals(carousel).indexOf(getCarouselOriginal(slide)) : -1

	if (0 <= index) {
		carousel.__groupCarouselInstance?.goToSlide?.(index)
	}
}

const CONTAINERS = [
	{ isClosed: isClosedToggle, open: openToggle },
	{ isClosed: isClosedTab, open: openTab },
	{ isClosed: isClosedSlide, open: openSlide },
	{ isClosed: isClosedCarouselGroup, open: openCarouselGroup }
]

/**
 * Height of the theme chrome fixed over the top of the canvas.
 *
 * @param   {?Document} doc The canvas document.
 * @returns {number}        The offset in pixels, 0 when nothing is pinned there.
 */
export const getStickyTopOffset = (doc) => {
	try {
		const view   = doc?.defaultView
		let   bottom = 0

		for (const element of doc?.querySelectorAll(STICKY_HEADERS) || []) {
			const rect = element.getBoundingClientRect()

			if ([ 'fixed', 'sticky' ].includes(view.getComputedStyle(element).position) && 1 >= rect.top && rect.height) {
				bottom = Math.max(bottom, rect.bottom)
			}
		}

		return Math.round(bottom)
	} catch (_e) {
		return 0
	}
}

/**
 * Opens every closed Toggle, Accordion item, Tab, Slide and carousel group around `node`, outermost first.
 *
 * @param   {?Node} node The node to reveal.
 * @returns {void}
 */
export const revealNode = (node) => {
	const closed = []

	try {
		for (let current = node; 1 === current?.nodeType; current = current.parentElement) {
			const container = CONTAINERS.find(candidate => candidate.isClosed(current, node))
			if (container) {
				closed.unshift({ element: current, open: container.open })
			}
		}
	} catch (_e) {
		// A failed reveal still leaves the plain scroll.
	}

	for (const { element, open } of closed) {
		try {
			open(element)
		} catch (_e) {
			// One container failing must not keep the others closed.
		}
	}
}