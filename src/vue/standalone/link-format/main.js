import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import { elemLoaded } from '@/vue/utils/elemLoaded'

import App from './App'

const loadLaDidYouKnow = () => {
	let app = createApp({ ...App, name: 'Standalone/LinkFormat' })
	app     = loadPlugins(app)
	app     = loadComponents(app)
	app     = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount('#aioseo-link-assistant-education-mount')
}

if (window.aioseo && window.aioseo.currentPost && 'post' === window.aioseo.currentPost.context) {
	const linkFormat = document.getElementById('aioseo-link-assistant-education')

	if (!linkFormat) {
		elemLoaded('#aioseo-link-assistant-education', 'aioseoLaDidYouKnow')
		document.addEventListener('animationstart', function (event) {
			if ('aioseoLaDidYouKnow' === event.animationName) {
				loadLaDidYouKnow()
			}
		}, { passive: true })
	} else {
		loadLaDidYouKnow()
	}
}