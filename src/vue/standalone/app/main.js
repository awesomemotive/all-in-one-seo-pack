import { createApp } from 'vue'

import loadDirectives from '@/vue/directives'

import App from './App'

if (document.getElementById('aioseo-admin')) {
	const app = createApp({ ...App, name: 'Standalone/App' })

	loadDirectives(app)

	app.mount('#aioseo-admin')
}