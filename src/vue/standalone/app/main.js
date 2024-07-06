import { createApp } from 'vue'

import App from './App.vue'

if (document.getElementById('aioseo-admin')) {
	const app = createApp({ ...App, name: 'Standalone/App' })

	app.mount('#aioseo-admin')
}