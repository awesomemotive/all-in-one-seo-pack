import { createApp } from 'vue'
import emitter from 'tiny-emitter/instance'
import { __ } from '@/vue/plugins/translations'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'
import App from './App'

const buttonSettings = {
	id    : 'aioseo-limit-modified-date-divi',
	param : 'aioseo_limit_modified_date',
	event : 'save-limit-modified-date',
	title : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

/**
 * Gets the Divi publish button.
 *
 * @since 4.5.2
 *
 * @returns {HTMLElement} The publish button.
 */
const getPublishButton = () => {
	return document.querySelector('.et-fb-button--publish')
}

/**
 * Initializes the Limit Modified Date view for Divi.
 *
 * @since 4.5.2
 *
 * @returns {void}
 */
const initView = () => {
	const $wrapper = document.createElement('div')
	$wrapper.id    = buttonSettings.id

	// Adds the wrapper after the publish button.
	getPublishButton().insertAdjacentElement('afterend', $wrapper)

	let app = createApp({ ...App, name: 'Standalone/Divi/LimitModifiedDate' }, {
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
 * @returns {void}
 */
const initEvents = ({ ET_Builder : etBuilder }) => {
	emitter.on(buttonSettings.event, () => {
		const { conditionalTags } = etBuilder.Frames.app.frameElement.contentWindow.ETBuilderBackend

		// Before saving, set the conditional tag to true.
		conditionalTags[buttonSettings.param] = true
		getPublishButton().click()

		// After saving, delete the conditional tag.
		delete conditionalTags[buttonSettings.param]
	})
}

/**
 * Initializes the Limit Modified Date integration.
 *
 * @since 4.5.2
 *
 * @param   {Object} window The window object.
 * @returns {void}
 */
export default (window) => {
	// Ensure we have the publish button on the page.
	if (!getPublishButton()) {
		return
	}

	initView()

	initEvents(window)
}