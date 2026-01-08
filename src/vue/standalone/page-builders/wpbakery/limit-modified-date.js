import { getSaveButtons } from './helpers'

import PageBuilderIntegration from '../PageBuilderIntegration'
import LimitModifiedDate from './components/LimitModifiedDate'

const getMoreOptionsMenu = () => {
	const $container = document.querySelector('#vc_more-options')

	return $container?.querySelector('ul')
}

/**
 * Initializes the Limit Modified Date integration.
 *
 * @param {Object} window The window object.
 * @returns {void}
 */
export default ({ jQuery : $ }) => {
	const ajaxSendHandler = (_event, _jqxhr, settings) => {
		const params = new URLSearchParams(settings.data)
		if ('vc_save' !== params.get('action')) {
			return
		}

		params.set('aioseo_limit_modified_date', true)

		settings.data = params.toString()

		$(document).off('ajaxSend', ajaxSendHandler)
	}

	const integration = new PageBuilderIntegration({
		limitModifiedDate : {
			component : LimitModifiedDate,
			appName   : 'Standalone/WPBakery/LimitModifiedDate',
			props     : {
				onClick : () => {
					$(document).on('ajaxSend', ajaxSendHandler)

					getSaveButtons(true)[0]?.click()
				}
			},
			node : {
				$wrapper   : getMoreOptionsMenu(),
				tag        : 'li',
				attributes : {
					id    : 'aioseo-limit-modified-date-wpbakery',
					class : 'vc_dropdown-list-item'
				}
			}
		}
	})

	integration.mount()
}