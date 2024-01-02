import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import {
	useOptionsStore
} from '@/vue/stores'
import { merge } from 'lodash-es'

export const useLocalSeoStore = defineStore('LocalSeoStore', {
	state : () => ({
		importers : []
	}),
	actions : {
		importPlugins (payload) {
			return http.post(links.restUrl('local-business/import-plugins'))
				.send(payload)
				.then(response => {
					if (response.body?.localBusinessOptions) {
						const optionsStore = useOptionsStore()
						optionsStore.options.localBusiness = merge({ ...optionsStore.options.localBusiness }, { ...response.body?.localBusinessOptions || {} })
					}
				})
		}
	}
})