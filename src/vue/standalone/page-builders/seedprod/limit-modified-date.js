import { __ } from '@wordpress/i18n'
import { elemLoaded } from '@/vue/utils/elemLoaded'

const buttonSettings = {
	id    : 'aioseo-limit-modified-date-seedprod',
	param : 'aioseo_limit_modified_date',
	event : 'save-limit-modified-date',
	title : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN),
	svg   : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="14" class="sp-w-4 sp-fill-current sp-inline sp-mr-2"><path fill="currentColor" d="M10.7 2H10V.7H8.7V2H3.3V.7H2V2h-.7C.6 2 0 2.6 0 3.3v9.4c0 .7.6 1.3 1.3 1.3h9.4c.7 0 1.3-.6 1.3-1.3V3.3c0-.7-.6-1.3-1.3-1.3Zm0 10.7H1.3V6h9.4v6.7Zm-9.4-8V3.3h9.4v1.4H1.3Zm2.2 6.2.7.7L5.8 10l1.6 1.6.8-.7-1.7-1.6 1.7-1.6-.8-.7-1.6 1.6L4.2 7l-.7.7L5 9.3 3.5 11Z"/></svg>'
}

/**
 * Initializes the Limit Modified Date integration.
 *
 * @since 4.5.2
 *
 * @param {Object} seedprodApp The SeedProd Vue instance.
 * @returns {void}
 */
export default (seedprodApp) => {
	// First we need to remove the request constant from the settings object.
	// This is because SeedProd might have stored it in the database, which
	// will cause the Limit Modified Date integration to be always enabled.
	delete seedprodApp.$children[0].$data.shared.settings[buttonSettings.param]

	const $button       = document.createElement('button')
	$button.id          = buttonSettings.id
	$button.textContent = buttonSettings.title
	$button.className   = 'sp-btn sp-font-semibold'
	$button.type        = 'button'
	$button.innerHTML   = `<span>${buttonSettings.svg}${buttonSettings.title}</span>`

	$button.addEventListener('click', () => {
		// Before saving, set the request constant to true.
		seedprodApp.$children[0].$data.shared.settings[buttonSettings.param] = true
		document.getElementById('seedprod-builder-save').click()

		// After saving, delete the request constant.
		setTimeout(() => delete seedprodApp.$children[0].$data.shared.settings[buttonSettings.param])
	})

	elemLoaded('#seedprod-builder-save-dropdown-menu', 'seedprodBuilderSaveDropdownMenu')
	document.addEventListener('animationstart', event => {
		if ('seedprodBuilderSaveDropdownMenu' === event.animationName) {
			event.target.insertAdjacentElement('afterbegin', $button)
		}
	}, { passive: true })
}