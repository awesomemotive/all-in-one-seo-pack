import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useDirtyOptionsStore,
	useIndexNowStore,
	useLinkAssistantStore,
	useNetworkStore,
	useNotificationsStore,
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

export const useOptionsStore = defineStore('OptionsStore', {
	state : () => ({
		dynamicOptions         : {},
		internalNetworkOptions : {},
		internalOptions        : {},
		networkOptions         : {},
		options                : {},
		htaccessError          : null,
		saveError              : null
	}),
	getters : {
		aiCreditPercentage () {
			return Math.round((this.internalOptions.internal.ai.credits.remaining / this.internalOptions.internal.ai.credits.total) * 100)
		}
	},
	actions : {
		saveChanges () {
			const dirtyOptionsStore   = useDirtyOptionsStore()
			const indexNowStore       = useIndexNowStore()
			const linkAssistantStore  = useLinkAssistantStore()
			const notificationsStore  = useNotificationsStore()
			const redirectsStore      = useRedirectsStore()
			const rootStore           = useRootStore()

			const options = {
				options        : this.options,
				dynamicOptions : this.dynamicOptions,
				network        : rootStore.aioseo.data.isNetworkAdmin,
				networkOptions : this.networkOptions
			}

			switch (rootStore.aioseo.page) {
				case 'redirects': {
					options.redirectOptions = redirectsStore.options
					break
				}
				case 'link-assistant': {
					options.linkAssistantOptions = linkAssistantStore.options
					break
				}
				case 'settings': {
					options.indexNowOptions = indexNowStore.options
					break
				}
				default:
					break
			}
			return http.post(links.restUrl('options'))
				.send(options)
				.then(response => {
					notificationsStore.updateNotifications(response.body.notifications)

					dirtyOptionsStore.updateOriginalOptions('options', this.options)
					dirtyOptionsStore.updateOriginalOptions('dynamicOptions', this.dynamicOptions)

					if (redirectsStore?.options) {
						dirtyOptionsStore.updateOriginalOptions('redirectOptions', redirectsStore.options)
					}

					if (indexNowStore.options.indexNow.apiKey) {
						dirtyOptionsStore.updateOriginalOptions('indexNowOptions', indexNowStore.options)
					}

					if (response.body.redirection) {
						if ('reload' === response.body.redirection) {
							window.location.reload()
						} else {
							window.location.href = response.body.redirection
						}
					}

					return response
				})
				.catch(error => {
					console.error('Failed to save the options.', error)
					this.saveError = error.response.body.message
				})
		},
		saveHtaccess () {
			const rootStore = useRootStore()
			return http.post(links.restUrl('htaccess'))
				.send({
					htaccess : rootStore.aioseo.data.htaccess
				})
				.then(() => {})
				.catch(error => {
					console.error('Failed to update .htaccess file.', error)
					this.htaccessError = error.response.body.message
				})
		},
		saveNetworkRobots () {
			const dirtyOptionsStore = useDirtyOptionsStore()
			const networkStore 		= useNetworkStore()
			const rootStore 		= useRootStore()

			const isNetwork = 'network' === networkStore.currentSite?.blog_id
			const getOptions = isNetwork ? this.networkOptions : this.options

			const options = {
				enabled          : isNetwork ? this.networkOptions.tools.robots.enable : this.options.tools.robots.enable,
				network          : rootStore.aioseo.data.isNetworkAdmin,
				rules            : networkStore.networkRobots.rules,
				searchAppearance : getOptions.searchAppearance
			}

			return http.post(links.restUrl(`network-robots/${networkStore.currentSite?.blog_id}`))
				.send(options)
				.then(() => {
					dirtyOptionsStore.updateOriginalOptions(isNetwork ? 'networkOptions' : 'options', getOptions)
				})
		},
		getObjects (payload) {
			return http.post(links.restUrl('objects'))
				.send(payload)
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					return response
				})
		},
		updateOption (store, { groups, key, value }) {
			let options = this[store]
			groups.forEach(group => {
				options = options[group]
			})

			if (undefined === options || undefined === options[key]) {
				return
			}

			options[key] = value
		},
		fetchOptions (siteId) {
			return http.get(links.restUrl('options'))
				.query({ siteId: siteId })
				.then(response => {
					this.options = response.body.options

					return response
				})
				.catch(error => {
					throw error
				})
		}
	}
})