/* globals TVE */
import { isRTL } from '@/vue/plugins/translations'

let isAioseoSettingsEnabled = false

/**
 * Register the events.
 *
 * @returns {void}
 */
const registerEvents = () => {
	TVE.$('#sidebar-top, #aioseo-score-btn-settings').on('click', '.aioseo-score-button', () => {
		const aioseoElement = document.querySelector('.tcb-sidebar-icon-aioseo')
		const clickEvent    = new MouseEvent('click', {
			bubbles    : true,
			cancelable : true,
			view       : window
		})

		aioseoElement?.dispatchEvent(clickEvent)
	})

	TVE.$('#settings-action-btn').on('click', () => {
		isAioseoSettingsEnabled = !isAioseoSettingsEnabled

		TVE.$('.tcb-aioseo #settings-action-btn').toggleClass('active', isAioseoSettingsEnabled)
		TVE.$body.toggleClass('aioseo-settings-enabled', isAioseoSettingsEnabled)

		TVE.ajax('update_option', 'post', {
			option_name  : 'is_aioseo_settings_enabled',
			option_value : isAioseoSettingsEnabled
		}).done(() => {})
	})
}

/**
 * Register the hooks.
 *
 * @returns {void}
 */
const registerHooks = () => {
	TVE.add_action('tcb.drawer.toggle', (drawer) => {
		if ('settings' !== drawer) {
			return
		}

		setTimeout(() => {
			const $aioseoPanel = TVE.$('#aioseo-panel')
			const isActive     = TVE.$('.tcb-sidebar-icon-aioseo').hasClass('active')

			isActive ? $aioseoPanel.show() : $aioseoPanel.hide()

			toggleClasses(isActive)
		}, 100)
	})

	TVE.add_action('tcb.drawer.hide', () => {
		setTimeout(() => {
			const isActive = TVE.$('.tcb-sidebar-icon-aioseo').hasClass('active')

			toggleClasses(isActive)
		}, 110)
	})
}

/**
 * Toggles the classes.
 *
 * @param {boolean} isActive Whether the panel is active or not.
 * @returns {void}
 */
const toggleClasses = (isActive) => {
	TVE.$('#aioseo-score-btn-header .aioseo-score-button').toggleClass('aioseo-score-button--active', isActive)
	TVE.$body.toggleClass('aioseo-settings-active', isActive)
}

/**
 * Initialize the settings panel.
 *
 * @returns {void}
 **/
const initSettingsPanel = () => {
	if (TVE.$('#aioseo-panel').length) {
		return
	}

	const $settingsPanel = TVE.$('.settings')
	const $aioseoPanel   = TVE.$('<div>', { id: 'aioseo-panel' }).hide()
	$settingsPanel.append($aioseoPanel)
}

/**
 * Initializes the score button.
 *
 * @returns {void}
 */
const initScoreButton = () => {
	if (TVE.$('#aioseo-score-btn-header').length) {
		return
	}

	const $sidebarTopRight = TVE.$('#tcb-sidebar-top-right .button-group')
	const $scoreButton     = TVE.$('<div>', { id: 'aioseo-score-btn-header' })
	$sidebarTopRight.append($scoreButton)
}

export default () => {
	isAioseoSettingsEnabled = 'true' === TVE.CONST.is_aioseo_settings_enabled || '1' === TVE.CONST.is_aioseo_settings_enabled

	if (isAioseoSettingsEnabled) {
		TVE.$('.tcb-aioseo #settings-action-btn').addClass('active')
		TVE.$body.addClass('aioseo-settings-enabled')
	}

	TVE.$('html').attr('dir', isRTL() ? 'rtl' : 'ltr')
	TVE.$('body').addClass('wp-core-ui')
	TVE.$('html').removeClass('no-js').addClass('js')

	initSettingsPanel()
	initScoreButton()

	registerHooks()
	registerEvents()
}