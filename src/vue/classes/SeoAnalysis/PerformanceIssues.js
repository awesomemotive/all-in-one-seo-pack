/* eslint-disable no-unused-vars */
import { __, sprintf } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

class PerformanceIssues {
	get requests () {
		return {
			requests : {
				title : __('Requests were found in the page.', td),
				text  : (result) => sprintf(
					__('At least %1$d requests were found in the page.', td),
					result?.length
				),
				tooltip : __('Requests are used to load resources like images, stylesheets, and scripts.', td)
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