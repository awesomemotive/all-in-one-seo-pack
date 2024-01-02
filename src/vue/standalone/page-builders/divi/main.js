import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initSettingsBar from './settings-bar'
import initWatcher from './watcher'
import initLimitModifiedDate from './limit-modified-date/main'
import App from './App.vue'

let aioseoApp = null

/**
 * Mount our Modal to render the SEO Settings.
 *
 * @returns {void}
 */
const mountPostSettings = () => {
	// Router placeholder to prevent errors when using router-link.
	const router = createRouter({
		history : createWebHistory(),
		routes  : [
			{
				path      : '/',
				component : App
			}
		]
	})

	let app = createApp({
		name : 'Standalone/Divi',
		data () {
			return {
				tableContext  : window.aioseo.currentPost.context,
				screenContext : 'sidebar'
			}
		},
		render : () => h(App)
	})

	app = loadPlugins(app)
	app = loadComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app, router)

	app.config.globalProperties.$truSeo = new TruSeo()

	app.mount('#aioseo-app-modal > div')

	return app
}

/**
 * Init the Divi integration.
 *
 * @returns {void}.
 */
const init = () => {
	// Init the settings bar.
	initSettingsBar()

	// Mount our Vue components in the Divi modal.
	aioseoApp?.unmount()
	aioseoApp = mountPostSettings()

	// Initialize the editor data watcher.
	initWatcher(window)

	// Initialize the Limit Modified Date integration.
	initLimitModifiedDate(window)
}

window.addEventListener('message', function (event) {
	if ('et_builder_api_ready' === event.data.eventType) {
		init()
	}
})