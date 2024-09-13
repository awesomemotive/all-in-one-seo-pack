import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import App from './App'
import startRouter from '@/vue/router'
import paths from '@/vue/pages/tools/router/paths'

let app = createApp({ ...App, name: 'Pages/Tools' })
app     = loadPlugins(app)
app     = loadComponents(app)
app     = loadVersionedComponents(app)

// If this is not an apache site, let's hide the .htaccess editor.
const router = startRouter(
	paths
		.filter(p => 'htaccess-editor' !== p.name || (window.aioseo.data.server.match(/apache|litespeed/) && !window.aioseo.data.isNetworkAdmin))
		.filter(p => 'bad-bot-blocker' !== p.name || (window.aioseo.internalOptions.internal.deprecatedOptions.includes('badBotBlocker') && !window.aioseo.data.isNetworkAdmin))
		.filter(p => 'debug' !== p.name || window.aioseo.data.isDev)
		.filter(p => 'system-status' !== p.name || !window.aioseo.data.isNetworkAdmin),
	app
)

// Give the router access to the app.
router.app = app

// Use the router.
app.use(router)

// Use the pinia store.
loadPiniaStores(app, router)

// // Set state from the window object.
app.mount('#aioseo-app')

export default app