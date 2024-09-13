import { createApp } from 'vue'
import emitter from 'tiny-emitter/instance'
import { __ } from '@/vue/plugins/translations'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'
import App from './App'

const buttonSettings = {
	id    : 'aioseo-limit-modified-date-wpbakery',
	param : 'aioseo_limit_modified_date',
	event : 'save-limit-modified-date',
	title : __('Update (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN),
	flag  : false
}

/**
 * Gets the WP Bakery publish button.
 *
 * @since 4.5.2
 *
 * @returns {HTMLElement} The publish button.
 */
const getPublishButton = () => {
	return document.getElementById('vc_button-update')
}

/**
 * Initializes the Limit Modified Date view for WP Bakery.
 *
 * @since 4.5.2
 *
 * @returns {void}
 */
const initView = () => {
	if (document.getElementById(buttonSettings.id)) {
		return
	}

	const $wrapper = document.createElement('div')
	$wrapper.id = buttonSettings.id

	// Adds the wrapper after the publish button.
	getPublishButton().insertAdjacentElement('afterend', $wrapper)

	let app = createApp({ ...App, name: 'Standalone/WPBakery/LimitModifiedDate' }, {
		buttonTitle : buttonSettings.title,
		buttonEvent : buttonSettings.event
	})
	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount(`#${buttonSettings.id}`)
}

/**
 * Initializes the event listeners.
 *
 * @since 4.5.2
 *
 * @param {Object} $ The jQuery object.
 * @returns {void}
 */
const initEvents = ($) => {
	$(document).on('ajaxSend', function (_event, _jqxhr, settings) {
		const params = new URLSearchParams(settings.data)
		if ('vc_save' !== params.get('action') || !buttonSettings.flag) {
			return
		}

		params.set(buttonSettings.param, true)

		settings.data = params.toString()
	})

	emitter.on(buttonSettings.event, () => {
		// Set the flag to true so that the modified date is not updated.
		buttonSettings.flag = true

		getPublishButton().click()

		// Reset the flag after saving.
		setTimeout(() => { buttonSettings.flag = false })
	})
}

/**
 * Initializes the Limit Modified Date integration.
 *
 * @since 4.5.2
 *
 * @param {Object} window The window object.
 * @returns {void}
 */
export default ({ jQuery : $ }) => {
	// Ensure we have the publish button on the page.
	if (!getPublishButton()) {
		return
	}

	initView()

	initEvents($)
}