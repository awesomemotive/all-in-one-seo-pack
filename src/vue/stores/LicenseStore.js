import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { __ } from '@wordpress/i18n'

import {
	useNotificationsStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

const td = import.meta.env.VITE_TEXTDOMAIN

const innerLicenseNoticeLink = () => {
	// Inner link.
	const rootStore  = useRootStore()
	const innerLink  = document.createElement('a')
	innerLink.href   = rootStore.aioseo.urls.aio.settings
	innerLink.classList.add('ab-item')

	// Inner span.
	const innerSpan     = document.createElement('span')
	innerSpan.innerText = __('Add License Key', td)
	innerSpan.classList.add('aioseo-menu-highlight')
	innerSpan.classList.add('green')

	// Append to DOM.
	innerLink.appendChild(innerSpan)

	return innerLink
}

export const useLicenseStore = defineStore('LicenseStore', {
	state : () => ({
		license : {
			expires    : 0,
			isActive   : false,
			isDisabled : false,
			isExpired  : false,
			isInvalid  : false,
			features   : {}
		}
	}),
	getters : {
		isUnlicensed : state => 'pro' !== import.meta.env.VITE_VERSION.toLowerCase() || !state.license.isActive,
		licenseKey   : () => {
			const rootStore = useRootStore()
			const optionsStore = useOptionsStore()
			return rootStore.aioseo.data.isNetworkAdmin
				? optionsStore.networkOptions.general.licenseKey
				: optionsStore.options.general.licenseKey
		}
	},
	actions : {
		activate (key) {
			const notificationsStore = useNotificationsStore()
			const rootStore          = useRootStore()

			return http.post(links.restUrl('activate'))
				.send({
					licenseKey : key.trim(),
					network    : rootStore.aioseo.data.isNetworkAdmin
				})
				.then(response => {
					const optionsStore = useOptionsStore()
					const store        = rootStore.aioseo.data.isNetworkAdmin ? 'networkOptions' : 'options'
					optionsStore.updateOption(store, { groups: [ 'general' ], key: 'licenseKey', value: key })

					notificationsStore.updateNotifications(response.body.notifications)

					if (response.body.licenseData) {
						Object.keys(response.body.licenseData).forEach(objectKey => {
							const internalStore = rootStore.aioseo.data.isNetworkAdmin ? 'internalNetworkOptions' : 'internalOptions'
							optionsStore.updateOption(internalStore, { groups: [ 'internal', 'license' ], key: objectKey, value: response.body.licenseData[objectKey] })
						})
						this.license = response.body.license

						rootStore.aioseo.data.isNetworkLicensed = rootStore.aioseo.data.isNetworkAdmin

						this.clearLicenseNotices()
					}

					return response
				})
		},
		multisite (sites) {
			const notificationsStore = useNotificationsStore()
			const rootStore          = useRootStore()

			return http.post(links.restUrl('multisite'))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin,
					sites
				})
				.then(response => {
					rootStore.aioseo.data = {
						...rootStore.aioseo.data,
						...{
							network : { ...rootStore.aioseo.data.network }
						}
					}

					notificationsStore.updateNotifications(response.body.notifications)
				})
		},
		deactivate () {
			const notificationsStore = useNotificationsStore()
			const rootStore          = useRootStore()

			return http.post(links.restUrl('deactivate'))
				.send({
					network : rootStore.aioseo.data.isNetworkAdmin
				})
				.then(response => {
					const optionsStore = useOptionsStore()
					const store        = rootStore.aioseo.data.isNetworkAdmin ? 'networkOptions' : 'options'
					optionsStore.updateOption(store, { groups: [ 'general' ], key: 'licenseKey', value: null })

					notificationsStore.updateNotifications(response.body.notifications)

					if (response.body.licenseData) {
						Object.keys(response.body.licenseData).forEach(key => {
							const internalStore = rootStore.aioseo.data.isNetworkAdmin ? 'internalNetworkOptions' : 'internalOptions'
							optionsStore.updateOption(internalStore, { groups: [ 'internal', 'license' ], key, value: response.body.licenseData[key] })
						})
						this.license = response.body.license

						rootStore.aioseo.isUnlicensed = true

						this.addLicenseNotices()
					}

					return response
				})
		},
		clearLicenseNotices () {
			const addLicenseKey1 = document.querySelector('.aioseo-submenu-highlight')
			if (addLicenseKey1) {
				addLicenseKey1.remove()
			}

			const addLicenseKey2 = document.querySelector('#wp-admin-bar-aioseo-pro-license')
			if (addLicenseKey2) {
				addLicenseKey2.remove()
			}
		},
		addLicenseNotices () {
			// Clear if it already exists.
			this.clearLicenseNotices()
			const wpSidebarMenu = document.querySelector('#toplevel_page_aioseo ul.wp-submenu-wrap')
			if (wpSidebarMenu) {
				const addLicenseKey1 = document.createElement('li')
				addLicenseKey1.classList.add('aioseo-submenu-highlight')

				const innerLink = innerLicenseNoticeLink()

				addLicenseKey1.appendChild(innerLink)
				wpSidebarMenu.appendChild(addLicenseKey1)
			}

			const wpAdminBarMenu = document.querySelector('#wp-admin-bar-aioseo-main-default')
			if (wpAdminBarMenu) {
				const addLicenseKey2 = document.createElement('li')
				addLicenseKey2.id = 'wp-admin-bar-aioseo-pro-license'

				const innerLink = innerLicenseNoticeLink()

				addLicenseKey2.appendChild(innerLink)
				wpAdminBarMenu.appendChild(addLicenseKey2)
			}
		}
	}

})