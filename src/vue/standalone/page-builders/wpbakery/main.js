import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import { resetAdapter } from '@/vue/plugins/tru-seo/editors'

import initWatcher from './watcher'
import initLimitModifiedDate from './limit-modified-date'
import App from './App'

const { vc } = window

const mountPostSettings = () => {
	const { vc_mode : vcMode } = window

	// Router placeholder to prevent errors when using router-link.
	const router = createRouter({
		history : createWebHistory(),
		routes  : [
			{
				path      : '/:pathMatch(.*)*', // Catch-all keeps Vue Router from warning about the current WP admin URL.
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

	app.mount('#aioseo-wpbakery')
}

/**
 * Init the WP Bakery integration.
 *
 * @returns {void}
 */
const init = () => {
	// BEFORE anything reads the adapter. Adapter resolution is memoised for the page's lifetime,
	// and on a back-end builder screen the first read happens while `window.vc` is still undefined
	// -- so WPBakery's own page resolves to the CLASSIC editor underneath it and keeps that answer,
	// leaving the builder analysed as raw shortcodes and unpainted. This runs from WPBakery's
	// `app.render`, by which point `vc` exists, which is exactly the post-boot staleness
	// resetAdapter() documents itself for.
	resetAdapter()

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