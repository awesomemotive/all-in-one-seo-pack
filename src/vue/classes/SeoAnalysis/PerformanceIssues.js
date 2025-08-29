/* eslint-disable no-unused-vars */
import { __, _n, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

class PerformanceIssues {
	get requests () {
		return {
			requests : {
				title : __('Assets loaded', td),
				text  : (result) => sprintf(
					// Translators: 1: The amount of requests.
					_n(
						'%1$d asset is loaded on the page.',
						'%1$d assets are loaded on the page.',
						result?.length,
						td
					),
					result?.length
				),
				tooltip : __('Assets are resources like images, stylesheets and scripts.', td)
			}
		}
	}

	get strings () {
		return {
			...this.requests
		}
	}
}

export default new PerformanceIssues()