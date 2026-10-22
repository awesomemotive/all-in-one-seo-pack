import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'
import { loadPiniaStores } from '@/vue/stores'
import { isDivi4Editor, isDivi5Editor, isExtraTheme } from '@/vue/utils/context'
import { resetAdapter } from '@/vue/plugins/tru-seo/editors'

import HighlighterHost from './components/HighlighterHost'
import PageBuilderIntegration from '../PageBuilderIntegration'
import initWatcher from './watcher'
import { isDivi5Ready, isDivi5 } from './helpers'
import { getDivi5AppWindow } from './divi5-helpers'

// Modern components (Divi 5+).
import Button from './components/Button'
import LimitModifiedDate from './components/LimitModifiedDate'
import Modal from './components/Modal'

// Legacy imports (Divi 4.x).
import initSettingsBar from './legacy-settings-bar'
import initLimitModifiedDate from './limit-modified-date/main'
import App from './App'

let highlighterHostApp = null

/**
 * Mounts the highlighter's own host, so highlights do not wait on the AIOSEO modal.
 *
 * Divi's panel is a `<core-modal :show="modalOpen">`, so `<post-settings />` -- and the `Main.vue`
 * that owns the highlighter bootstrap -- mounts only when the user opens it. Measured on a fresh
 * builder load: no panel node, no analyzers seeded, no marks, while the analysis those marks
 * describe had already found five passive-voice sentences.
 *
 * Detached, because it renders nothing and has no business occupying a slot in Divi's chrome.
 *
 * Both builders, and for the same reason: Divi 4's panel is a modal too, so its highlights waited
 * on the user opening it exactly as Divi 5's did.
 *
 * @returns {void}
 */
const initHighlighterHost = () => {
	if (highlighterHostApp || isExtraTheme() || !(isDivi5Editor() || isDivi4Editor())) {
		return
	}

	const node = document.createElement('div')
	node.id = 'aioseo-divi-highlighter-host'
	document.body.appendChild(node)

	let app = createApp({
		name   : 'Standalone/Divi/HighlighterHost',
		render : () => h(HighlighterHost)
	})

	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	loadPiniaStores(app, null)

	app.mount(node)

	highlighterHostApp = app
}

/**
 * Init the Divi integration using PageBuilderIntegration (Divi 5+).
 *
 * @returns {void}
 */
const initModern = () => {
	// Resolution is memoised for the page's lifetime and the answer can be WRONG rather than
	// absent: `isDiviEditor()` asks for `window.ET_Builder` and Divi's body classes, so a read
	// landing before the builder's bundle has run resolves whatever is underneath -- or nothing
	// -- and keeps that for good.
	//
	// This one is NOT the readiness reset. `initModern` is registered on DOMContentLoaded, a
	// document signal rather than a Divi one -- the 60 x 1s `isDivi5Ready()` poll below exists
	// precisely because Divi 5 is not up yet at this point. The reset that matters is in that
	// poll's callback, once Divi has actually announced itself.
	//
	// Kept as INSURANCE, not because a known earlier reader exists: on a Divi page today nothing
	// can resolve before this runs -- every `resolveAdapter()` call site sits inside a function
	// or a composable factory, and `Base::enqueue()` ships this entry alone. It costs nothing
	// (no composable exists yet, so the generation bump lands with no subscribers) and keeps this
	// integration the same shape as the others should a second bundle ever be co-enqueued here.
	resetAdapter()

	const slug                  = 'divi'
	const toolbarSelector       = '.et-vb-builder-bar-wrap'
	const buttonId              = `aioseo-button-${slug}`
	const getToolbarWrapper     = () => document.querySelector(toolbarSelector)
	const getToolbarButtonAfter = ($wrapper) => [ ...($wrapper?.querySelectorAll(':scope > button.et-vb-builder-bar-button') || []) ].pop()
	const getToolbarButtonStyle = ($wrapper) => {
		const $icon = $wrapper?.querySelector('.et-vb-icon')
		if (!$icon) {
			return ''
		}

		const { width, height, minWidth } = getComputedStyle($icon)
		return `--icon-width: ${width}; --icon-height: ${height}; --icon-min-width: ${minWidth}`
	}

	let attempts      = 0
	const maxAttempts = 60
	const interval    = setInterval(() => {
		const $wrapper = getToolbarWrapper()
		if (!$wrapper || !isDivi5Ready()) {
			if (++attempts >= maxAttempts) {
				clearInterval(interval)
			}
			return
		}

		clearInterval(interval)

		// THE reset for Divi 5, and the reason the one at the top of this function is not enough:
		// `isDivi5Ready()` has only just become true, so this is the first moment we KNOW Divi has
		// booted. Not the same question `isDivi5Editor()` asks -- readiness reads the registry
		// inside the canvas iframe, ownership reads `ET_Builder` and the body classes in this
		// window -- but it is the signal that the globals ownership depends on are now published.
		// Anything that resolved during the poll window -- up to 60s -- holds a stale answer until
		// this runs.
		//
		// It has to stay AHEAD of every mount below. `PointerDownFix.js` and `SpellingAnalysis.vue`
		// read the adapter through a `computed()` over a non-reactive source, so they cache their
		// first answer permanently and no later reset can reach them.
		resetAdapter()

		const scoreBtnNode = {
			$wrapper   : $wrapper,
			$after     : getToolbarButtonAfter($wrapper),
			tag        : 'button',
			attributes : {
				id    : buttonId,
				class : 'aioseo-app et-vb-builder-bar-button',
				style : getToolbarButtonStyle($wrapper)
			}
		}

		const integration = new PageBuilderIntegration({
			scoreBtn : {
				component : Button,
				appName   : `Standalone/${slug}/Button`,
				node      : scoreBtnNode
			},
			limitModifiedDate : {
				component : LimitModifiedDate,
				appName   : `Standalone/${slug}/LimitModifiedDate`,
				node      : {
					tag        : 'a',
					attributes : {
						id    : `aioseo-limit-modified-date-wrapper-${slug}`,
						href  : '#',
						class : ''
					},
					mountLater (initMount) {
						const appId           = `aioseo-limit-modified-date-wrapper-${slug}`
						const diviData        = getDivi5AppWindow()?.divi?.data
						const rightClickStore = diviData?.select('divi/right-click-options')
						if (!rightClickStore) {
							return
						}

						let wasActive = false
						diviData.subscribe(() => {
							const isActive = rightClickStore.isActive()

							if (isActive && !wasActive) {
								requestAnimationFrame(() => {
									if (document.getElementById(appId)) {
										return
									}

									const optionType = rightClickStore?.getState()?.optionType
									if ('pageBarSaveMenu' !== optionType) {
										return
									}

									const wrappers = document.getElementsByClassName('et-vb-right-click-options')
									if (wrappers.length) {
										initMount(wrappers[0])
									}
								})
							}

							wasActive = isActive
						})
					}
				}
			},
			metabox : {
				component : Modal,
				appName   : `Standalone/${slug}/Modal`
			}
		})

		const remountScoreBtnIfNeeded = () => {
			const $currentWrapper = getToolbarWrapper()
			if (!$currentWrapper) {
				return
			}

			scoreBtnNode.$wrapper         = $currentWrapper
			scoreBtnNode.$after           = getToolbarButtonAfter($currentWrapper)
			scoreBtnNode.attributes.style = getToolbarButtonStyle($currentWrapper)

			if ($currentWrapper.querySelector(`#${buttonId}`)) {
				return
			}

			integration.mountScoreBtn()
		}

		integration.mount()

		const diviData = getDivi5AppWindow()?.divi?.data
		if (diviData) {
			let isRemountQueued = false
			diviData.subscribe(() => {
				if (isRemountQueued) {
					return
				}

				isRemountQueued = true
				requestAnimationFrame(() => {
					isRemountQueued = false
					remountScoreBtnIfNeeded()
				})
			})
		}

		initHighlighterHost()

		initWatcher(window)
	}, 1000)
}

/**
 * Init the Divi integration using legacy approach (Divi 4.x).
 *
 * @returns {void}.
 */
const initLegacy = () => {
	// BEFORE anything reads the adapter. Resolution is memoised for the page's lifetime and the
	// answer can be WRONG rather than absent: `isDiviEditor()` asks for `window.ET_Builder` and
	// Divi's body classes, so a read landing before the builder's bundle has run resolves
	// whatever is underneath -- or nothing -- and keeps that for good.
	//
	// Correct at this point for Divi 4, unlike `initModern`'s: this runs off Divi's own
	// `et_builder_api_ready` message, so the builder has announced itself by the time we get
	// here, and it precedes every mount in this function.
	resetAdapter()

	// Init the settings bar.
	initSettingsBar()

	// Mount our Vue components in the Divi modal.
	new PageBuilderIntegration({
		metabox : {
			component     : App,
			appName       : 'Standalone/Divi',
			rootContainer : '#aioseo-app-modal > div',
			screenContext : 'sidebar'
		}
	}).mount()

	// Before the watcher, matching initModern(): the host owns the highlighter, and seeding it
	// ahead of the first content read means the marks describe the analysis that read triggers.
	initHighlighterHost()

	// Initialize the editor data watcher.
	initWatcher(window)

	// Initialize the Limit Modified Date integration.
	initLimitModifiedDate(window)
}

if (isDivi5) {
	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', initModern, { once: true })
	} else {
		initModern()
	}
} else {
	// Divi 4.x fires the et_builder_api_ready message when the builder is ready.
	window.addEventListener('message', (event) => {
		if (event.origin !== window.location.origin) {
			return
		}

		if ('et_builder_api_ready' === event.data.eventType) {
			initLegacy()
		}
	})
}