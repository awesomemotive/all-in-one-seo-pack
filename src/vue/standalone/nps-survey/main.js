import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'
import { loadPiniaStores } from '@/vue/stores'

import App from './App'

let app = createApp({ ...App, name: 'Standalone/NpsSurvey' })
app     = loadPlugins(app)
app     = loadComponents(app)
app     = loadVersionedComponents(app)

// Use the pinia store.
loadPiniaStores(app)

app.mount('#aioseo-nps-survey')