import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import RedirectsSlugMonitor from '@/vue/plugins/redirects'
import TruSeo from '@/vue/plugins/tru-seo'

import App from './App.vue'
import registerScoreToggler from './registerScoreToggler'
import initWatcher from './watcher/main.js'
import { elemLoaded } from '@/vue/utils/elemLoaded'
import { shouldShowMetaBox } from '@/vue/plugins/tru-seo/components/helpers'
import loadTruSeo from '@/vue/standalone/post-settings/loadTruSeo'
import LinkAssistantWatcher from './link-assistant/AIOSEO_VERSION'

// Local Business.
import AppLocalBusiness from '../local-business-seo/App.vue'

initWatcher()

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

const localCreateApp = (app) => {
	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app, router)

	// The code below requires Pinia to be loaded.
	new RedirectsSlugMonitor()
	new LinkAssistantWatcher()
	registerScoreToggler()
	app.config.globalProperties.$truSeo = new TruSeo()

	window.addEventListener('load', () => loadTruSeo(app))

	return app
}

const loadSidebarApp = () => {
	localCreateApp(createApp({
		name : 'Standalone/PostSettings/Sidebar',
		data () {
			return {
				tableContext  : 'post',
				screenContext : 'sidebar'
			}
		},
		render : () => h(App)
	})).mount('#aioseo-post-settings-sidebar-vue')
}

if (window.aioseo.currentPost) {
	const currentContext = window.aioseo.currentPost.context
	const node = document.querySelector(`#aioseo-${currentContext}-settings-metabox`)
	if (node && shouldShowMetaBox()) {
		if (!window.wp.blockEditor && window.wp.blocks && window.wp.oldEditor) {
			window.wp.blockEditor = window.wp.editor
		}

		localCreateApp(createApp({
			name : 'Standalone/PostSettings/Metabox',
			data () {
				return {
					tableContext  : currentContext,
					screenContext : 'metabox'
				}
			},
			render : () => h(App)
		})).mount(`#aioseo-${currentContext}-settings-metabox`)

		if ('post' === currentContext) {
			const sidebar = document.getElementById('aioseo-post-settings-sidebar-vue')
			if (!sidebar) {
				elemLoaded('#aioseo-post-settings-sidebar-vue', 'aioseoSidebarVisible')
				document.addEventListener('animationstart', function (event) {
					if ('aioseoSidebarVisible' === event.animationName) {
						loadSidebarApp()
					}
				}, { passive: true })
			} else {
				loadSidebarApp()
			}
		}
	}
}

if (window.aioseo.currentPost && window.aioseo.localBusiness && document.getElementById('aioseo-location-settings-metabox')) {
	localCreateApp(createApp({
		name : 'Standalone/LocalSeo/Metabox',
		data () {
			return {
				screenContext : 'metabox'
			}
		},
		render : () => h(AppLocalBusiness)
	})).mount('#aioseo-location-settings-metabox')
}