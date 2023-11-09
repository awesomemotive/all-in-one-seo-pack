import '@/vue/utils/vue2.js'
import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import { elemLoaded } from '@/vue/utils/elemLoaded'
import { shouldShowMetaBox } from '@/vue/utils/metabox'

import App from './App.vue'

import './blockEditor'

const loadLimitModifiedDate = () => {
	let app = createApp({ ...App, name: 'Standalone/LimitModifiedDate' })
	app     = loadPlugins(app)
	app     = loadComponents(app)
	app     = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount('#aioseo-limit-modified-date')
}

if (shouldShowMetaBox() && window.aioseo && window.aioseo.currentPost && 'post' === window.aioseo.currentPost.context) {
	const metaBox = document.getElementById('aioseo-limit-modified-date')

	if (!metaBox) {
		elemLoaded('#aioseo-limit-modified-date', 'aioseoLimitModifiedDate')
		document.addEventListener('animationstart', function (event) {
			if ('aioseoLimitModifiedDate' === event.animationName) {
				loadLimitModifiedDate()
			}
		}, { passive: true })
	} else {
		loadLimitModifiedDate()
	}
}