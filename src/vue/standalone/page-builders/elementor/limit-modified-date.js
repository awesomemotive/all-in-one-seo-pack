import { __ } from '@wordpress/i18n'

const buttonSettings = {
	icon  : 'eicon-calendar',
	name  : 'aioseo-limit-modified-date',
	param : 'aioseo_limit_modified_date',
	title : __('Save (Don\'t Modify Date)', import.meta.env.VITE_TEXTDOMAIN)
}

/**
 * Sets the limit modified date button as active or disabled.
 *
 * @param {bool} hasChanges Whether the document has changes.
 *
 * @returns {void}
 */
const setActiveButton = (hasChanges) => {
	const $button = document.getElementById(`elementor-panel-footer-sub-menu-item-${buttonSettings.name}`)
	if (!$button) {
		return
	}

	$button.classList.remove('elementor-disabled')
	if (!hasChanges) {
		$button.classList.add('elementor-disabled')
	}
}

/**
 * Initializes the Limit Modified Date integration.
 *
 * @since 4.5.2
 *
 * @param   {Object} window The window object.
 * @returns {void}
 */
export default ({ elementor, elementorCommon, $e }) => {
	elementor.once('preview:loaded', function () {
		// Add the limit modified date option to the Elementor footer.
		elementor.getRegion('panel').currentView.footer.currentView.addSubMenuItem('saver-options', {
			icon     : buttonSettings.icon,
			name     : buttonSettings.name,
			title    : buttonSettings.title,
			callback : (event) => {
				if (event.currentTarget.classList.contains('elementor-disabled')) {
					return
				}

				// Before saving, set the request constant to true.
				elementorCommon.ajax.requestConstants[buttonSettings.param] = true
				$e.run('document/save/default')
			}
		})
	})

	elementor.on('document:loaded', (elementorDocument) => {
		setActiveButton('draft' === elementorDocument.container.settings.get('post_status'))
	})

	$e.commandsInternal.on('run:after', (_component, command, args) => {
		switch (command) {
			case 'document/save/set-is-modified':
				// Enable or disable the button when the document updates.
				setActiveButton(args.status)
				break
			case 'document/save/save':
			case 'document/save/default':
				// After saving, delete the request constant.
				delete elementorCommon.ajax.requestConstants[buttonSettings.param]
				break
		}
	})
}