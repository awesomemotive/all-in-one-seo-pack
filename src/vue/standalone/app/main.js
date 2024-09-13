import { createApp } from 'vue'

import App from './App'

if (document.getElementById('aioseo-admin')) {
	const app = createApp({ ...App, name: 'Standalone/App' })

	app.mount('#aioseo-admin')
}