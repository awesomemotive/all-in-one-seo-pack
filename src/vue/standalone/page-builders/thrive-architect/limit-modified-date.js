/* globals TVE */
import { __ } from '@/vue/plugins/translations'

const buttonSettings = {
	id    : 'aioseo-limit-modified-date-thrive',
	param : 'aioseo_limit_modified_date',
	title : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN),
	flag  : false
}

/**
 * Initializes the button.
 *
 * @returns {void}
 */
const initButton = () => {
	TVE.$('#tve-save-dash-return').after(`
		<a
			href="javascript:void(0)"
			id="${buttonSettings.id}"
			title="${buttonSettings.title}"
		>
			${buttonSettings.title}
		</a>
	`)
}

/**
 * Initializes the event listeners.
 *
 * @returns {void}
 */
const initEvents = () => {
	TVE.add_filter('tcb_save_post_data_before', data => {
		data[buttonSettings.param] = buttonSettings.flag

		return data
	})

	TVE.add_action('tve.save_post.success', () => {
		buttonSettings.flag = false
	})

	TVE.$body.on('click', `#${buttonSettings.id}`, () => {
		buttonSettings.flag = true
		TVE.main.editor_settings.save()
	})
}

export default () => {
	// Initialize the button.
	initButton()

	// Initialize the event listeners.
	initEvents()
}