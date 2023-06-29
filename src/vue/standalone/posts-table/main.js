import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { elemLoaded } from '@/vue/utils/elemLoaded'

import App from './App.vue'
import TermApp from './TermApp.vue'

const localCreateApp = (app) => {
	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.config.globalProperties.$truSeo = new TruSeo()

	return app
}

const loadPostsTable = (post, index) => {
	const app = localCreateApp(createApp({
		name : 'Standalone/PostsTable/' + post.id,
		data () {
			return {
				screen : window.aioseo.screen
			}
		},
		render : () => h(App)
	}, {
		post,
		index
	}))

	app.mount(`#${post.columnName}-${post.id}`)
}

if (window.aioseo.posts) {
	window.aioseo.posts.forEach((post, index) => {
		elemLoaded(`#${post.columnName}-${post.id}`, `aioseoPostsTable-${post.id}`)
		document.addEventListener('animationstart', function (event) {
			if (`aioseoPostsTable-${post.id}` === event.animationName) {
				loadPostsTable(post, index)
			}
		}, { passive: true })
	})
}

const loadTermsTable = (term, index) => {
	const app = localCreateApp(createApp({
		name : 'Standalone/TermsTable/' + term.id,
		data () {
			return {
				screen : window.aioseo.screen
			}
		},
		render : () => h(TermApp)
	}, {
		term,
		index
	}))

	app.mount(`#${term.columnName}-${term.id}`)
}

if (window.aioseo.terms && 0 === window.aioseo.posts.length) {
	window.aioseo.terms.forEach((term, index) => {
		elemLoaded(`#${term.columnName}-${term.id}`, `aioseoTermsTable-${term.id}`)
		document.addEventListener('animationstart', function (event) {
			if (`aioseoTermsTable-${term.id}` === event.animationName) {
				loadTermsTable(term, index)
			}
		}, { passive: true })
	})
}