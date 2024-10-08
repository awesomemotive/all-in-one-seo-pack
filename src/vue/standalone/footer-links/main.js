import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import App from './App'

const footerLinks = document.querySelector('#aioseo-footer-links')
if (footerLinks) {
	let app = createApp({ ...App, name: 'Standalone/FooterLinks' })
	app     = loadPlugins(app)

	app.mount('#aioseo-footer-links')
}