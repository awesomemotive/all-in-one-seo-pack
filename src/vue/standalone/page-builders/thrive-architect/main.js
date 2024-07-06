import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initWatcher from './watcher'
import initPanel from './settings-panel'
import initLimitModifiedDate from './limit-modified-date'
import Sidebar from './components/Sidebar.vue'
import Button from './components/Button.vue'

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
		name : 'Standalone/ThriveArchitect',
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

	app.mount('#aioseo-panel')
}

const mountScoreButton = () => {
	const targetList = [
		'aioseo-score-btn-settings',
		'aioseo-score-btn-header'
	]

	targetList.forEach((wrapperId) => {
		const name = wrapperId.replace('aioseo-score-btn-', '').replace(/^\w/, (c) => c.toUpperCase())

		let app = createApp({
			name   : `Standalone/ThriveArchitect/Score${name}`,
			render : () => h(Button)
		}, {
			buttonContext : name
		})

		app = loadPlugins(app)
		app = loadComponents(app)

		// Use the pinia store.
		loadPiniaStores(app)

		app.mount(`#${wrapperId}`)
	})
}

/**
 * Init the ThriveArchitect integration.
 *
 * @returns {void}
 */
const init = () => {
	// Initialize the panel inside the page builder.
	initPanel()

	// Mount the AIOSEO post settings.
	mountPostSettings()

	// Mount the Score button in the settings and header.
	mountScoreButton()

	// Initialize the limit modified date.
	initLimitModifiedDate()

	// Initialize the watcher.
	initWatcher()
}

init()