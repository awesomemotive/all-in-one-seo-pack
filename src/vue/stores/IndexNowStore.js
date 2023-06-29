import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useDirtyOptionsStore
} from '@/vue/stores'

export const useIndexNowStore = defineStore('IndexNowStore', {
	state : () => ({
		options : {
			indexNow : {
				apiKey : null
			}
		}
	}),
	actions : {
		generateApiKey () {
			return http.get(links.restUrl('index-now/generate-api-key'))
				.then(response => {
					this.options.indexNow.apiKey = response.body.key

					const dirtyOptionsStore = useDirtyOptionsStore()
					dirtyOptionsStore.updateOriginalOptions('indexNowOptions', this.options)

					return response.body.key
				})
		},
		getApiKey () {
			return http.get(links.restUrl('index-now/api-key'))
				.then(response => {
					this.options.indexNow.apiKey = response.body.key

					const dirtyOptionsStore = useDirtyOptionsStore()
					dirtyOptionsStore.updateOriginalOptions('indexNowOptions', this.options)

					return response.body.key
				})
		}
	}
})