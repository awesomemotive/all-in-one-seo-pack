import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import App from './App.vue'

const elemDiv = document.createElement('div')
elemDiv.id    = 'aioseo-redirects-add-redirect-standalone'
document.body.appendChild(elemDiv)

let app = createApp({ ...App, name: 'Standalone/Redirects/AddRedirect' })
app     = loadPlugins(app)
app     = loadComponents(app)

// Use the pinia store.
loadPiniaStores(app)

app.mount('#aioseo-redirects-add-redirect-standalone')