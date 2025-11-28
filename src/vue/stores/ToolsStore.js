import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

export const useToolsStore = defineStore('ToolsStore', {
	actions : {
		emailDebugInfo (emailAddress) {
			return http.post(links.restUrl('email-debug-info'))
				.send({
					email : emailAddress
				})
		},
		doTask ({ action, data, siteId }) {
			const rootStore = useRootStore()
			return http.post(links.restUrl('settings/do-task')).send({
				action,
				data,
				siteId,
				network : rootStore.aioseo.data.isNetworkAdmin
			})
				.then((response) => {
					if (!response?.statusCode || 400 === response.statusCode) {
						return Promise.reject(new Error(`Task ${action} could not be completed.`))
					}
				})
		},
		uploadFile ({ file, filename, siteId }) {
			return http.post(links.restUrl(`settings/import/${siteId || ''}`))
				.attach('file', file, filename)
				.then(response => {
					if (response.body.license && !siteId) {
						const licenseStore   = useLicenseStore()
						licenseStore.license = response.body.license

						licenseStore.clearLicenseNotices()
					}

					const optionsStore   = useOptionsStore()
					optionsStore.options = response.body.options

					return response
				})
		},
		exportSettings (payload) {
			return http.post(links.restUrl('settings/export'))
				.send(payload)
		},
		exportContent (payload) {
			return http.post(links.restUrl('settings/export-content'))
				.send(payload)
		},
		resetSettings ({ payload, siteId }) {
			return http.post(links.restUrl('reset-settings'))
				.send({
					settings : payload,
					siteId
				})
		},
		importPlugins (payload) {
			return http.post(links.restUrl('settings/import-plugins'))
				.send(payload)
		}
	}
})