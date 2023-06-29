/* globals seedprod_app */

import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import initWatcher from './watcher'

import App from '@/vue/standalone/post-settings/App.vue'

import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'

/**
 * Mount our Component inside the SEO tab.
 *
 * @returns {void}
 */
const mountComponent = () => {
	// Check whether the route to SEO tab is active. If so, render our Vue component.
	seedprod_app.$watch('$route.name', (newVal) => {
		if ('setup_settings_seo' === newVal) {
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
				name : 'Standalone/SeedProd',
				data () {
					return {
						tableContext  : window.aioseo.currentPost.context,
						screenContext : 'metabox'
					}
				},
				mounted : () => {
					setTimeout(updatePostData)
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

			app.config.globalProperties.$truSeo = new TruSeo()

			app.mount('#seedprod-preview-wrapper .seedprod-settings-page-wrapper')
		}
	},
	{
		immediate : true
	})
}

/**
 * Replace the data-href attribute to load our styles.
 *
 * @returns {void}
 */
const fixStyleTag = () => {
	const style = document.getElementById('aioseo-seedprod-common-css')
	if (style) {
		style.setAttribute('href', style.getAttribute('data-href'))
	}
}

/**
 * Init the SeedProd integration.
 *
 * @returns {void}
 */
const init = () => {
	// Mount our Vue component in the SEO tab.
	mountComponent()

	// Fix the link tag to load our styles.
	fixStyleTag()

	// Initialize the editor data watcher.
	initWatcher()
}

document.addEventListener('DOMContentLoaded', () => {
	if (seedprod_app && seedprod_app._isMounted) {
		init()
	}
})