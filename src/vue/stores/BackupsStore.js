import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useLicenseStore,
	useOptionsStore
} from '@/vue/stores'

export const useBackupsStore = defineStore('BackupsStore', {
	state : () => ({
		backups        : [],
		networkBackups : {}
	}),
	actions : {
		createBackup (payload) {
			return http.post(links.restUrl('backup'))
				.send(payload)
				.then(response => {
					if (payload.siteId) {
						this.networkBackups[payload.siteId] = response.body.backups
						return
					}

					this.backups = response.body.backups
				})
		},
		restoreBackup (payload) {
			return http.post(links.restUrl('backup/restore'))
				.send(payload)
				.then(response => {
					if (response.body.license && !payload.siteId) {
						const licenseStore   = useLicenseStore()
						licenseStore.license = response.body.license

						licenseStore.clearLicenseNotices()
					}

					const optionsStore = useOptionsStore()

					optionsStore.options         = response.body.options
					optionsStore.internalOptions = response.body.internalOptions

					if (payload.siteId) {
						this.networkBackups[payload.siteId] = response.body.backups
						return
					}

					this.backups = response.body.backups
				})
				.catch(error => {
					this.syncBackups(payload, error)

					throw error
				})
		},
		deleteBackup (payload) {
			return http.delete(links.restUrl('backup'))
				.send(payload)
				.then(response => {
					if (payload.siteId) {
						this.networkBackups[payload.siteId] = response.body.backups
						return
					}

					this.backups = response.body.backups
				})
				.catch(error => {
					this.syncBackups(payload, error)

					throw error
				})
		},
		syncBackups (payload, error) {
			const backups = error?.response?.body?.backups
			if (!Array.isArray(backups)) {
				return
			}

			if (payload.siteId) {
				this.networkBackups[payload.siteId] = backups
				return
			}

			this.backups = backups
		}
	}
})