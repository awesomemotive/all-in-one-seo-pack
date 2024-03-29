import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useAddonsStore,
	useRootStore
} from '@/vue/stores'

export const usePluginsStore = defineStore('PluginsStore', {
	state : () => ({
		plugins : {}
	}),
	actions : {
		installPlugins (payload) {
			const rootStore = useRootStore()
			return http.post(links.restUrl('plugins/install'))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin,
					plugins : payload
				})
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					const addonsStore = useAddonsStore()

					Object.keys(response.body.completed).forEach(sku => {
						const basename = response.body.completed[sku]
						const addon    = addonsStore.addons.find(item => sku === item.sku)
						if (addon) {
							addon.basename          = basename
							addon.installed         = true
							addon.hasMinimumVersion = true
							addon.installedVersion  = addon.version
							addonsStore.updateAddon(addon)
						}
					})

					return response
				})
		},
		upgradePlugins (payload) {
			const rootStore = useRootStore()
			return http.post(links.restUrl('plugins/upgrade'))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin,
					plugins : payload
				})
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					const addonsStore = useAddonsStore()

					Object.keys(response.body.completed).forEach(sku => {
						addonsStore.updateAddon(response.body.completed[sku])
					})

					return response
				})
		},
		deactivatePlugins (payload) {
			const rootStore = useRootStore()
			return http.post(links.restUrl('plugins/deactivate'))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin,
					plugins : payload
				})
				.then(response => {
					return response
				})
		}
	}
})