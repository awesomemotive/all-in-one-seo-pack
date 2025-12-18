import PageBuilderIntegration from '../PageBuilderIntegration'
import initWatcher from './watcher'

import Button from './components/Button'
import LimitModifiedDate from './components/LimitModifiedDate'
import Modal from './components/Modal'

const slug     = 'oxygen'
const buttonId = `aioseo-button-${slug}`

const mountIntegration = () => {
	const $toolbar = window.parent.document.querySelector('.topbar-section')
	if (!$toolbar) {
		return
	}

	const integration = new PageBuilderIntegration({
		scoreBtn : {
			component : Button,
			appName   : `Standalone/${slug}/Button`,
			node      : {
				$wrapper   : $toolbar,
				tag        : 'div',
				attributes : {
					id    : buttonId,
					style : 'align-self: center; margin-left: 10px; cursor: pointer; width: auto',
					role  : 'button'
				}
			}
		},
		limitModifiedDate : {
			component : LimitModifiedDate,
			appName   : `Standalone/${slug}/LimitModifiedDate`,
			node      : {
				tag        : 'span',
				attributes : {
					id       : `aioseo-limit-modified-date-wrapper-${slug}`,
					role     : 'menuitem',
					tabindex : 0
				},
				mountLater (initMount) {
					const $parent = window.parent.document.querySelector('.breakdance-top-bar-wrapper')
					if (!$parent) {
						console.warn('Settings toolbar not found.')
						return
					}

					$parent._aioseoAbortController?.abort()
					$parent._aioseoAbortController = new AbortController()

					$parent.addEventListener('click', (e) => {
						if (e.target?.closest('.open-settings-toolbar-button')) {
							requestAnimationFrame(() => {
								setTimeout(() => {
									const $activeMenu = window.parent.document.querySelector('.menuable__content__active')

									initMount($activeMenu?.querySelector('.dropdown-content [role="menuitem"]')?.parentElement)
								})
							})
						}
					}, { capture: true, signal: $parent._aioseoAbortController.signal })
				}
			}
		},
		metabox : {
			component : Modal,
			appName   : `Standalone/${slug}/Modal`
		},
		injectStyles : true
	})

	integration.mount()
}

const remountOnResize = () => {
	window.addEventListener('resize', () => {
		if (!window.parent.document.querySelector(`#${buttonId}`)) {
			mountIntegration()
		}
	})
}

const init = () => {
	mountIntegration()
	remountOnResize()
	initWatcher()
}

document.addEventListener('DOMContentLoaded', () => {
	// The init function will run in a fresh call stack.
	setTimeout(init)
})