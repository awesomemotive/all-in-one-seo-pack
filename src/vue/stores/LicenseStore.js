import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { __ } from '@/vue/plugins/translations'

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
			isActive   : true, // Always active - no license required
			isDisabled : false,
			isExpired  : false,
			isInvalid  : false,
			features   : {}
		}
	}),
	getters : {
		isUnlicensed : state => false, // Always licensed - independent plugin
		licenseKey   : () => {
			return '' // No license key needed
		}
	},
	actions : {
		activate (key) {
			// No-op: No license activation needed - independent plugin
			this.clearLicenseNotices()
			return Promise.resolve({ body: { success: true } })
		},
		multisite (sites) {
			// No-op: No license needed - independent plugin
			return Promise.resolve({ body: { success: true } })
		},
		deactivate () {
			// No-op: No license deactivation needed - independent plugin
			return Promise.resolve({ body: { success: true } })
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