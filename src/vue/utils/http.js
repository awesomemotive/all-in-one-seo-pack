import superagent from 'superagent'

export default superagent.agent()
	.set('X-WP-Nonce', window?.aioseo?.nonce)
	.use(req => {
		req.on('response', response => {
			if (401 === response.status || 403 === response.status) {
				console.error(response)
			}
		})
	})