import superagent from 'superagent'
import links from '@/vue/utils/links'

export default superagent.agent()
	.set('X-WP-Nonce', window?.aioseo?.nonce)
	.use(req => {
		if ('/' === req.url[0]) {
			req.url = links.unTrailingSlashIt(links.restUrl(req.url))
		}

		req.on('response', response => {
			if (401 === response.status || 403 === response.status) {
				console.error(response)
			}
		})
	})