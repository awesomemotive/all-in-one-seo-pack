import { h, createApp } from 'vue'
// import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import { elemLoaded } from '@/vue/utils/elemLoaded'
import HeadLineAnalyzer from './registerHeadlineAnalyzer'
import { HeadlineCurrentScore } from './assets/js/HeadlineCurrentScore'

import App from './App'

// import scss
import './assets/scss/main.scss'

// Register Headline Analyzer Plugin
HeadLineAnalyzer()

const vueAppId        = '#aioseo-headline-analyzer-sidebar-vue'
const headlineSidebar = 'aioseo-headline-analyzer-sidebar/aioseo-headline-analyzer-sidebar'

let app, isPinBtnEventAttached = false
const localCreateApp = () => {
	if (app) {
		app.unmount()
	}

	// Load App
	app = createApp({
		name : 'Standalone/HeadlineAnalyzer/Sidebar',
		data () {
			return {
				tableContext  : 'post',
				screenContext : 'sidebar'
			}
		},
		render : () => h(App)
	})

	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	// unmount postSettingsSidebar if it exists
	if (window.aioseo.postSettingsSidebarApp) {
		window.aioseo.postSettingsSidebarApp.unmount()
	}

	HeadlineCurrentScore()

	app.mount(vueAppId)

	window.aioseo.headlineAnalyzerSidebarApp = app

	return app
}

if (window.aioseo.currentPost) {
	const currentContext = window.aioseo.currentPost.context

	if (!window.wp.blockEditor && window.wp.blocks && window.wp.oldEditor) {
		window.wp.blockEditor = window.wp.editor
	}

	if ('post' === currentContext) {
		const sidebar = document.querySelector(vueAppId)
		if (!sidebar) {
			elemLoaded(vueAppId, 'headlineAnalyzerSidebarLoaded')
			document.addEventListener('animationstart', function (event) {
				if ('headlineAnalyzerSidebarLoaded' === event.animationName) {
					localCreateApp()
				}
			}, { passive: true })
		} else {
			localCreateApp()
		}
	}
}

const { select } = window.wp.data

function attachEventListener (element) {
	if (!isPinBtnEventAttached) {
		element?.addEventListener('click', () => {
			HeadlineCurrentScore()
		})

		isPinBtnEventAttached = true
	}
}

window.wp.data.subscribe(() => {
	const activeSidebarName = select('core/edit-post').getActiveGeneralSidebarName()

	if (activeSidebarName) {
		const sidebarParent = document.querySelector('.interface-interface-skeleton__sidebar')

		if (headlineSidebar !== activeSidebarName) {
			// Unmount when opening other sidebars.
			if (window.aioseo.headlineAnalyzerSidebarApp) {
				window.aioseo.headlineAnalyzerSidebarApp.unmount()
			}
		}

		attachEventListener(sidebarParent)
	}
})