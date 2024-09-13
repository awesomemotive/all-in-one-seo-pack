import { h, createApp } from 'vue'
import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import { loadPiniaStores } from '@/vue/stores'

import Button from './components/Button'
import LimitModifiedDate from './components/LimitModifiedDate'

import { buttonSettings as lmdButtonSettings } from './limit-modified-date'

let btnApp   = null,
	lmdApp   = null,
	observer = null

const btnWrapperId = '#fusion-builder-aioseo-button'
const lmdWrapperId = '#fusion-builder-aioseo-lmd'

/**
 * Mount our Modal to render the SEO Settings.
 *
 * @returns {void}
 */
const mountComponent = ({ id, component, name, data }) => {
	let app = createApp({
		name   : `Standalone/Avada/${name}`,
		render : () => h(component)
	}, data || {})

	app = loadPlugins(app)
	app = loadComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount(id)

	return app
}

/**
 * Create a wrapper element for our toolbar items.
 *
 * @param {string} id The ID of the wrapper.
 * @returns {HTMLElement} The wrapper element.
 */
const createWrapper = (id) => {
	const $wrapper     = document.createElement('div')
	$wrapper.id        = id.replace('#', '')
	$wrapper.className = 'aioseo-toolbar-item'

	return $wrapper
}

/**
 * Maybe remount the AIOSEO toolbar items.
 * This is necessary because Fusion Builder recreates the DOM when the builder content is updated.
 *
 * @returns {void}
 */
const mountToolbar = () => {
	const $toolbar      = document.querySelector('.fusion-builder-live-toolbar')
	const resetObserver = null === $toolbar.querySelector('.aioseo-toolbar-item')

	// If we don't have the button in the DOM, mount it.
	if (!$toolbar.querySelector(btnWrapperId)) {
		$toolbar.insertAdjacentElement('beforeend', createWrapper(btnWrapperId))

		btnApp?.unmount()
		btnApp = mountComponent({
			id        : btnWrapperId,
			component : Button,
			name      : 'Button'
		})
	}

	// If we don't have the limit modified date app in the DOM, mount it.
	if (!$toolbar.querySelector(lmdWrapperId)) {
		const $saveButton = $toolbar.querySelector('.fusion-builder-save-page')
		$saveButton.insertAdjacentElement('afterend', createWrapper(lmdWrapperId))

		lmdApp?.unmount()
		lmdApp = mountComponent({
			id        : lmdWrapperId,
			component : LimitModifiedDate,
			name      : 'LimitModifiedDate',
			data      : {
				buttonTitle    : lmdButtonSettings.title,
				buttonEvent    : lmdButtonSettings.event,
				buttonDisabled : 'true' === $saveButton.getAttribute('data-disabled')
			}
		})
	}

	// If we didn't find any of our items in the toolbar, means that  Fusion Builder recreated the DOM.
	// In this case, we will restart the observer.
	if (resetObserver) {
		observer?.disconnect()
		observer = observeToolbar()
	}
}

/**
 * Observe the toolbar to mount the button.
 *
 * @returns {void}
 */
const observeToolbar = () => {
	const observer = new MutationObserver(() => {
		mountToolbar()
	})

	observer.observe(document.querySelector('.fusion-builder-live-toolbar'), {
		childList : true,
		subtree   : true
	})

	return observer
}

export default () => {
	mountToolbar()
}