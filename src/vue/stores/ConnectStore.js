import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useLicenseStore,
	useOptionsStore,
	useSetupWizardStore
} from '@/vue/stores'

export const useConnectStore = defineStore('ConnectStore', {
	state   : () => ({}),
	getters : {
		isConnected : () => {
			const licenseStore = useLicenseStore()
			const optionsStore = useOptionsStore()
			return (
				'pro' !== import.meta.env.VITE_VERSION.toLowerCase() &&
				optionsStore.internalOptions.internal.siteAnalysis?.connectToken
			) ||
			licenseStore.license.isActive
		}
	},
	actions : {
		getConnectUrl ({ key, wizard }) {
			// Disabled: Connect to Pro functionality - independent plugin doesn't need this
			return Promise.resolve({ body: { success: false, message: 'Connect functionality disabled - independent plugin' } })
		},
		processConnect (payload) {
			// Disabled: Connect to Pro functionality - independent plugin doesn't need this
			return Promise.resolve({ body: { success: false, message: 'Connect functionality disabled - independent plugin' } })
		},
		saveConnectToken (token) {
			// Disabled: Connect token functionality - independent plugin doesn't need this
			return Promise.resolve({ body: { success: true } })
		}
	}
})