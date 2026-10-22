import { isBlockEditor } from '@/vue/utils/context'

const drain = response => {
	if ('function' === typeof response?.clone) {
		response.clone().arrayBuffer().catch(() => {})
	}
}

/**
 * Reads the reply of the block editor's meta box save, which core requests with `parse: false` and never reads.
 *
 * NOTE: That reply is the whole edit screen (several MB). Left unread, it keeps its connection busy, so after about
 * six saves the browser has no connections left for the site and every request from the tab hangs.
 *
 * @returns {void}
 */
export default () => {
	if (!isBlockEditor() || 'function' !== typeof window.wp?.apiFetch?.use) {
		return
	}

	window.wp.apiFetch.use((options, next) => {
		const result = next(options)
		if (false !== options.parse || !options.url?.includes('meta-box-loader=')) {
			return result
		}

		return result.then(
			response => {
				drain(response)

				return response
			},
			error => {
				drain(error)

				throw error
			}
		)
	})
}