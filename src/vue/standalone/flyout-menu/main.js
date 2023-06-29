import '@/vue/utils/vue2.js'
import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import App from './App.vue'

let app = createApp({ ...App, name: 'Standalone/FlyoutMenu' })
app     = loadPlugins(app)
app     = loadComponents(app)
app     = loadVersionedComponents(app)

// Use the pinia store.
loadPiniaStores(app)

app.mount('#aioseo-flyout-menu')