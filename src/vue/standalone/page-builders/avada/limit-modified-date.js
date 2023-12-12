import emitter from 'tiny-emitter/instance'
import { __ } from '@wordpress/i18n'

export const buttonSettings = {
	id    : 'aioseo-limit-modified-date-avada',
	param : 'aioseo_limit_modified_date',
	event : 'save-limit-modified-date',
	title : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

/**
 * Initializes the event listeners.
 *
 * @since 4.5.2
 *
 * @returns {void}
 */
const initEvents = () => {
	const { FusionApp } = window

	emitter.on(buttonSettings.event, () => {
		// Set the flag to true so that the modified date is not updated.
		FusionApp.data.query[buttonSettings.param] = true

		// Trigger the save method.
		FusionApp.savePostContent()

		// After saving, delete the conditional tag.
		delete FusionApp.data.query[buttonSettings.param]
	})
}

export default () => {
	initEvents()
}