import { isRTL } from '@wordpress/i18n'
import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initWatcher from './watcher'
import initToolbar from './toolbar'
import initLimitModifiedDate from './limit-modified-date'
import Sidebar from './components/Sidebar.vue'

const mountPostSettings = () => {
	// Router placeholder to prevent errors when using router-link.
	const router = createRouter({
		history : createWebHistory(),
		routes  : [
			{
				path      : '/',
				component : Sidebar
			}
		]
	})

	let app = createApp({
		name : 'Standalone/Avada',
		data () {
			return {
				tableContext  : window.aioseo.currentPost.context,
				screenContext : 'sidebar'
			}
		},
		render : () => h(Sidebar)
	})

	app = loadPlugins(app)
	app = loadComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app, router)

	app.config.globalProperties.$truSeo = new TruSeo()

	app.mount('#fusion-builder-aioseo-sidebar')
}

/**
 * Init the view for the front-end page builder.
 *
 * @returns {void}
 */
const initFrontEndView = () => {
	const { FusionEvents : fusionEvents } = window

	// FusionEvents is just for front-end builder, so early return if it's not available.
	if ('undefined' === typeof fusionEvents) {
		return
	}

	fusionEvents.on('fusion-app-setup', () => {
		// We need to set the dir attribute to match the right styles.
		document.documentElement.setAttribute('dir', isRTL() ? 'rtl' : 'ltr')

		initToolbar()
		mountPostSettings()
		initLimitModifiedDate()
	})
}

/**
 * Init the Avada integration.
 *
 * @returns {void}
 */
const init = () => {
	// Init the view for the front-end page builder.
	initFrontEndView()

	// Initialize the editor data watcher.
	initWatcher()
}

let preload        = false
const fusionEvents = window.FusionEvents || window.FusionPageBuilderEvents || {}

fusionEvents.on('fusion-builder-loaded', () => {
	setTimeout(init)
	preload = true
})

document.addEventListener('DOMContentLoaded', () => {
	if (!preload) {
		setTimeout(init)
	}
})