import superagent from 'superagent'

import {
	useRootStore
} from '@/vue/stores'

export const http = (nonce) => superagent
	.agent()
	.set('X-WP-Nonce', nonce)
	.use(req => {
		req.on('response', response => {
			if (401 === response.status) {
				console.error(response)
			} else if (403 === response.status) {
				console.error(response)
			}
		})
	})

const unForwardSlashIt = str => {
	return str.replace(/^\//, '')
}

const unTrailingSlashIt = str => {
	return str.replace(/\/$/, '')
}

const trailingSlashIt = str => {
	return unTrailingSlashIt(str) + '/'
}

export const restUrl = (path) => {
	const rootStore = useRootStore()

	path = rootStore.aioseo.data.hasUrlTrailingSlash ? trailingSlashIt(path) : unTrailingSlashIt(path)
	return trailingSlashIt(rootStore.aioseo.urls.restUrl) + trailingSlashIt('aioseo/v1') + unForwardSlashIt(path)
}