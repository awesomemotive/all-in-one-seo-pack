import { createApp, h } from 'vue'
// import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import {
	loadPiniaStores,
	useWritingAssistantStore
} from '@/vue/stores'

import { elemLoaded } from '@/vue/utils/elemLoaded'

import App from './App.vue'
import Sidebar from './Sidebar.vue'
import './registerSidebar'
import '@/vue/plugins/writing-assistant'
import { merge } from 'lodash-es'

// Router placeholder to prevent errors when using router-link.
/*
const router = createRouter({
	history : createWebHistory(),
	routes  : [
		{
			path      : '/',
			component : App
		}
	]
})
*/

const localCreateApp = (app) => {
	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)
	/*
	app.use(router)

	router.app = app

	// Use the pinia store.
	*/
	// loadPiniaStores(app, router)
	loadPiniaStores(app)
	const writingAssistantStore = useWritingAssistantStore()

	// eslint-disable-next-line no-undef
	const writingAssistantData = aioseoWritingAssistant || null

	// Set initial data.
	if (writingAssistantData && !writingAssistantStore.loaded) {
		writingAssistantStore.$state = merge({ ...writingAssistantStore.$state }, { ...writingAssistantData || {} })
		// This will set in motion the current user + checks for user info and options.
		writingAssistantStore.setUserLoggedIn(writingAssistantData?.seoBoost?.isLoggedIn)
		writingAssistantStore.loaded = true
	}

	return app
}

// Create the metabox app.
localCreateApp(createApp({
	name   : 'Standalone/WritingAssistant/Metabox',
	render : () => h(App)
})).mount('#aioseo-writing-assistant-metabox-app')

// Create the sidebar app.
let sidebarApp
const loadSidebarApp = () => {
	if (sidebarApp) {
		sidebarApp.unmount()
	}

	sidebarApp = createApp({
		data () {
			return {
				tableContext  : 'post',
				screenContext : 'sidebar'
			}
		},
		render : () => h(Sidebar)
	})

	localCreateApp(sidebarApp).mount('#aioseo-writing-assistant-sidebar > div')
}

elemLoaded('#aioseo-writing-assistant-sidebar', 'aioseoWritingAssistantSidebarVisible')
document.addEventListener('animationstart', function (event) {
	if ('aioseoWritingAssistantSidebarVisible' === event.animationName) {
		loadSidebarApp()
	}
}, { passive: true })