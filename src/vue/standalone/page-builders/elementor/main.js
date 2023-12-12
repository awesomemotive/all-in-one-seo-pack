import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initWatcher from './watcher'
import initIntroduction from './introduction'
import initLimitModifiedDate from './limit-modified-date'

import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import App from '@/vue/standalone/post-settings/App'

/**
 * Add to the body a class to identify the Elementor color schema.
 *
 * @returns {void}
 */
const addBodyClass = () => {
	let theme = window.elementor.getPreferences('ui_theme') || 'auto'
	if ('auto' === theme) {
		theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
	}

	// Remove the class if was already added.
	document.body.classList.forEach(className => {
		if (className.startsWith('aioseo-elementor-')) {
			document.body.classList.remove(className)
		}
	})

	document.body.classList.add('aioseo-elementor-' + theme)
}

/**
 * Mount our Component inside the Elementor tab.
 *
 * @returns {void}
 */
const initElementorHooks = ({ elementor, $e, Marionette }) => {
	// Check whether the route to our tab is active. If so, render our Vue component.
	$e.routes.on('run:after', function (_component, route) {
		addBodyClass()

		// Initialize our Vue component.
		if ('panel/page-settings/aioseo' === route) {
			initAioseoEditor('#elementor-panel-page-settings-controls')
		}
	})

	elementor.modules.layouts.panel.pages.menu.Menu.addItem({
		name     : 'aioseo',
		icon     : 'aioseo aioseo-element-menu-icon aioseo-element-menu-icon-' + elementor.getPreferences('ui_theme'),
		title    : import.meta.env.VITE_NAME,
		type     : 'page',
		callback : () => {
			try {
				$e.routes.run('panel/page-settings/aioseo')
			} catch (error) {
				// The AIOSEO tab is only available if the page settings have been visited.
				$e.routes.run('panel/page-settings/settings')
				$e.routes.run('panel/page-settings/aioseo')
			}
		}
	}, 'more')

	elementor.once('preview:loaded', function () {
		// Add our tab to the Elementor Page Settings.
		$e.components.get('panel/elements').addTab('aioseo', {
			title : 'AIOSEO'
		})
	})

	// Add AIOSEO region to the Elementor Region Views.
	elementor.hooks.addFilter('panel/elements/regionViews', (regionViews) => {
		regionViews.aioseo = {
			region : regionViews.global.region,
			view   : Marionette.ItemView.extend({
				template  : false,
				id        : 'elementor-panel-aioseo',
				className : 'aioseo-elementor aioseo-sidebar-panel',
				initialize () {
					document.getElementById('elementor-panel-elements-search-area').hidden = true
				},
				onShow () {
					initAioseoEditor('#elementor-panel-aioseo')
				},
				onDestroy () {
					document.getElementById('elementor-panel-elements-search-area').hidden = false
				}
			}),
			options : {}
		}
		return regionViews
	})
}

/**
 * Mount our Component inside the Elementor tab.
 *
 * @param {string} selector The selector of the element where we will mount our Vue component.
 * @returns {void}
 */
const initAioseoEditor = (selector) => {
	// Get the target element.
	const wrapper = document.querySelector(selector)

	// Add the class to the wrapper.
	wrapper.classList.add('edit-post-sidebar', 'aioseo-elementor-panel')

	// Add the div that will contain our Vue component.
	wrapper.appendChild(document.createElement('div'))

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
		name : 'Standalone/Elementor',
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
	app = loadVersionedComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app, router)

	// Initialize the editor data watcher.
	initWatcher()

	app.config.globalProperties.$truSeo = new TruSeo()

	app.mount(`${selector} > div`)

	// Update the post data and run the analysis when our panel loads.
	updatePostData()
}

/**
 * Init the Elementor integration.
 *
 * @returns {void}
 */
const init = () => {
	// Create all the needed Elementor hooks to add our panel.
	initElementorHooks(window)

	// Initialize the introduction.
	initIntroduction(window)

	// Initialize the Limit Modified Date integration.
	initLimitModifiedDate(window)
}

let preload = false
if (window.elementor) {
	setTimeout(init)
	preload = true
}

(function ($) {
	if (preload) {
		return
	}

	$(window).on('elementor:init', () => {
		window.elementor.on('panel:init', () => {
			setTimeout(init)
		})
	})
})(window.jQuery)