import { createApp } from 'vue'

import loadDirectives from '@/vue/directives'

import { loadPiniaStores } from '@/vue/stores'

import App from './App'

const newNotifications = document.querySelector('#aioseo-menu-new-notifications')
if (newNotifications) {
	const app = createApp({ ...App, name: 'Standalone/Notifications' })

	// Use the pinia store.
	loadPiniaStores(app)

	loadDirectives(app)

	app.mount('#aioseo-menu-new-notifications')
}