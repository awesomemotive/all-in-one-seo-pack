import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'

import App from './App.vue'
import TermApp from './TermApp.vue'

import './AIOSEO_VERSION/urlInspection.js'

const localCreateApp = (app) => {
	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.config.globalProperties.$truSeo = new TruSeo()

	return app
}

const localUnmountApp = (id) => {
	const $el = document.getElementById(id)

	if ($el?.__vue_app__) {
		$el.__vue_app__.unmount()
	}
}

const loadPostsTable = (post) => {
	localUnmountApp(`${post.columnName}-${post.id}`)

	const app = localCreateApp(createApp({
		name : 'Standalone/PostsTable/' + post.id,
		data () {
			return {
				screen : window.aioseo.screen
			}
		},
		render : () => h(App)
	}, { post }))

	app.mount(`#${post.columnName}-${post.id}`)
}

if (window.aioseo.posts) {
	window.aioseo.posts.forEach((post) => {
		loadPostsTable(post)
	})
}

const loadTermsTable = (term) => {
	localUnmountApp(`${term.columnName}-${term.id}`)

	const app = localCreateApp(createApp({
		name : 'Standalone/TermsTable/' + term.id,
		data () {
			return {
				screen : window.aioseo.screen
			}
		},
		render : () => h(TermApp)
	}, { term }))

	app.mount(`#${term.columnName}-${term.id}`)
}

if (window.aioseo.terms && 0 === window.aioseo.posts.length) {
	window.aioseo.terms.forEach((term) => {
		loadTermsTable(term)
	})
}

// Re-renders the component when the quick-edit finishes.
;(function ($) {
	$(document).on('ajaxComplete', (_event, _jqXHR, ajaxOptions) => {
		const data   = new URLSearchParams(ajaxOptions.data)
		const action = data?.get('action')
		if (!data || !action) {
			return
		}

		// Quick-edit on posts table.
		if ('inline-save' === action) {
			const { post_ID : postId } = Object.fromEntries(data.entries())
			const post = window.aioseo.posts.find((p) => p.id === parseInt(postId))

			loadPostsTable({
				...post,
				reload : true
			})
		}

		// Quick-edit on terms table.
		if ('inline-save-tax' === action) {
			const { tax_ID : termId } = Object.fromEntries(data.entries())
			const term = window.aioseo.terms.find((t) => t.id === parseInt(termId))

			loadTermsTable({
				...term,
				reload : true
			})
		}
	})
})(window.jQuery)