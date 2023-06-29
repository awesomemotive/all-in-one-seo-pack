import '@/vue/utils/vue2.js'
import { createApp } from 'vue'

import { loadPiniaStores } from '@/vue/stores'

import App from './App.vue'

import translate from '@/vue/plugins/translations'

const newNotifications = document.querySelector('#aioseo-menu-new-notifications')
if (newNotifications) {
	const app = createApp({ ...App, name: 'Standalone/Notifications' })

	// Use the pinia store.
	loadPiniaStores(app)

	app.config.globalProperties.$t     = translate
	app.config.globalProperties.$td    = import.meta.env.VITE_TEXTDOMAIN
	app.config.globalProperties.$tdPro = import.meta.env.VITE_TEXTDOMAIN_PRO

	app.mount('#aioseo-menu-new-notifications')
}