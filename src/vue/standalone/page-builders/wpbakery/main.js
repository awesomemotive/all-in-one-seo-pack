import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initWatcher from './watcher'
import initLimitModifiedDate from './limit-modified-date/main'
import App from './App.vue'

const { vc } = window

const mountPostSettings = () => {
	const { vc_mode : vcMode } = window

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
		name : 'Standalone/WPBakery',
		data () {
			return {
				tableContext  : window.aioseo.currentPost.context,
				screenContext : 'sidebar'
			}
		},
		render : () => h(App)
	}, {
		isFrontendEditor : 'admin_frontend_editor' === vcMode
	})

	app = loadPlugins(app)
	app = loadComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app, router)

	app.config.globalProperties.$truSeo = new TruSeo()

	app.mount('#aioseo-wpbakery')
}

/**
 * Init the WP Bakery integration.
 *
 * @returns {void}
 */
const init = () => {
	// Mount our Vue components in the WP Bakery bar.
	mountPostSettings()

	// Initialize the editor data watcher.
	initWatcher(window)

	// Initialize the Limit Modified Date integration.
	initLimitModifiedDate(window)
}

let preload = false
vc.events.on('app.render', () => {
	init()
	preload = true
})

window.addEventListener('load', () => {
	if (!preload) {
		init()
	}
})