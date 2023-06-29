import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useRootStore = defineStore('RootStore', {
	state : () => ({
		pong    : true,
		loaded  : false,
		loading : false,
		aioseo  : {}
	}),
	actions : {
		ping () {
			http.get(links.restUrl('ping'))
				.catch(() => {
					this.pong = false
				})
		}
	}
})