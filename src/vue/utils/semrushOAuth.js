import { popup } from '@/vue/utils/popup'
import { getParams } from '@/vue/utils/params'

const SEMRUSH_OAUTH_URL = 'https://oauth.semrush.com/auth/login?client_id=aioseo&redirect_uri=https%3A%2F%2Foauth.semrush.com%2Foauth2%2Faioseo%2Fsuccess&ref=2190331110&response_type=code&scope=user.id'
const SEMRUSH_ORIGIN = 'https://oauth.semrush.com'

/**
 * Opens Semrush OAuth popup and handles the authentication flow.
 *
 * @param {Object}   options                       Options object.
 * @param {Function} options.onAuthenticated       Callback function when authentication is successful. Receives code.
 * @param {Function} options.onClosed              Callback function when popup is closed. Receives reload boolean.
 * @param {Function} options.onAuthenticationError Optional callback for authentication errors.
 * @returns {void}
 */
export const openSemrushOAuthPopup = ({ onAuthenticated, onClosed, onAuthenticationError }) => {
	const completedCallback = async (payload) => {
		try {
			await onAuthenticated(payload.code)
		} catch (error) {
			if (onAuthenticationError) {
				onAuthenticationError(error)
			}
			throw error
		}
	}

	const closedCallback = (reload) => {
		if (onClosed) {
			onClosed(reload)
		}
	}

	const postMessageCallback = async (event, popupWindow, triggerPostMessageCallback) => {
		const { data, source, origin } = event

		// Check that the message comes from the expected origin.
		if (SEMRUSH_ORIGIN !== origin || popupWindow !== source) {
			return
		}

		if ('semrush:oauth:success' === data.type) {
			// Stop listening to messages, since the popup is closed.
			window.removeEventListener('message', triggerPostMessageCallback, false)

			let params = {}
			try {
				const url = new URL(data.url)
				params    = getParams(url.search)
			} catch (e) {
				// console.error('Failed to parse Semrush OAuth URL:', e)
			}

			try {
				await completedCallback(params)
				popupWindow.close()
				popupWindow = null
				closedCallback(true)
			} catch (error) {
				// console.error('Semrush authentication failed:', error)
				popupWindow.close()
				popupWindow = null
				closedCallback(false)
			}
		}

		if ('semrush:oauth:denied' === data.type) {
			popupWindow.close()
			// Stop listening to messages, since the popup is closed.
			window.removeEventListener('message', triggerPostMessageCallback, false)
			popupWindow = null
			closedCallback(false)
		}
	}

	popup(
		SEMRUSH_OAUTH_URL,
		'Semrush Oauth',
		450,
		570,
		true,
		[ 'code' ],
		completedCallback,
		closedCallback,
		postMessageCallback
	)
}

/**
 * Checks if Semrush OAuth is required and returns the appropriate action.
 *
 * @param {Object}  options                   Options object.
 * @param {boolean} options.isConnected       Whether AIOSEO Connect is connected.
 * @param {boolean} options.hasValidTokens    Whether Semrush has valid tokens.
 * @param {string}  options.connectUrl        AIOSEO Connect URL.
 * @returns {Object|null}                     Object with action type and payload, or null if no action needed.
 */
export const getSemrushAuthRequirement = ({ isConnected, hasValidTokens, connectUrl }) => {
	if (!isConnected) {
		return {
			type : 'connect',
			url  : connectUrl + '&semrush=true'
		}
	}

	if (!hasValidTokens) {
		return {
			type : 'semrush_oauth',
			url  : SEMRUSH_OAUTH_URL
		}
	}

	return null
}