import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import { loadPiniaStores } from '@/vue/stores'

import App from './App'

let app = createApp(App)
app     = loadPlugins(app)

loadPiniaStores(app)

app.mount('#wp-admin-bar-aioseo-admin-bar-noindex-warning')

export default app