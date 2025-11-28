import { popup } from '@/vue/utils/popup'

/**
 * Opens AIOSEO Connect OAuth popup.
 *
 * @param {Object}   options               Options object.
 * @param {string}   options.url           AIOSEO Connect URL.
 * @param {Function} options.onAuthenticated Callback function when authentication is successful. Receives token.
 * @param {Function} options.onClosed      Callback function when popup is closed. Receives reload boolean.
 * @returns {void}
 */
export const openConnectOAuthPopup = ({ url, onAuthenticated, onClosed }) => {
	const completedCallback = (payload) => {
		return onAuthenticated(payload.token)
	}

	const closedCallback = (reload) => {
		if (onClosed) {
			onClosed(reload)
		}
	}

	popup(
		url,
		'Connect with AIOSEO',
		600,
		630,
		true,
		[ 'token' ],
		completedCallback,
		closedCallback
	)
}