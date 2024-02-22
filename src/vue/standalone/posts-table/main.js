import '@/vue/utils/vue2.js'
import { h, createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import {
	loadPiniaStores,
	useOptionsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import App from './App.vue'
import TermApp from './TermApp.vue'

import './AIOSEO_VERSION/urlInspection.js'

// We need to load the Pinia here since we will use the store outside an App.
// links.restUrl method that we use below uses rootStore to get the rest URL.
loadPiniaStores()

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

const addHiddenField = (wrapper) => {
	if (!wrapper) {
		return
	}

	const input = document.createElement('input')
	input.setAttribute('type', 'hidden')
	input.setAttribute('name', 'aioseo-has-details-column')
	input.setAttribute('value', true)

	wrapper.append(input)
}

if (window.aioseo.posts && 0 < window.aioseo.posts.length) {
	http.post(links.restUrl('posts-list/load-details-column'))
		.send({
			ids : window.aioseo.posts.map((p) => p.id)
		})
		.then(response => {
			window.aioseo.posts = window.aioseo.posts.map(item => {
				return {
					...item,
					...response.body.posts.find(p => p.id === item.id)
				}
			})

			window.aioseo.posts.forEach((post) => {
				loadPostsTable(post)
			})
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

if (window.aioseo.terms && 0 < window.aioseo.terms.length && 0 === window.aioseo.posts.length) {
	http.post(links.restUrl('terms-list/load-details-column'))
		.send({
			ids : window.aioseo.terms.map((t) => t.id)
		})
		.then(response => {
			window.aioseo.terms = window.aioseo.terms.map(item => {
				return {
					...item,
					...response.body.terms.find(t => t.id === item.id)
				}
			})

			window.aioseo.terms.forEach((term) => {
				loadTermsTable(term)
			})
		})
}

// Adds a flag to the quick-edit form to re-render the component when it finishes.
addHiddenField(document.querySelector('#inline-edit div'))

// Adds a flag to the add-tag form to render the component when it finishes.
addHiddenField(document.getElementById('addtag'))

// Re-renders the component when the quick-edit finishes.
;(function ($) {
	$(document).on('ajaxComplete', (_event, jqXHR, ajaxOptions) => {
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

		// Add new tag.
		if ('add-tag' === action) {
			const termId       = $(jqXHR.responseXML).find('term_id').text()
			const taxonomy     = $(jqXHR.responseXML).find('term taxonomy').text()
			const optionsStore = useOptionsStore()

			loadTermsTable({
				id          : parseInt(termId),
				columnName  : 'aioseo-details',
				title       : optionsStore.dynamicOptions.searchAppearance.taxonomies[taxonomy]?.title,
				description : optionsStore.dynamicOptions.searchAppearance.taxonomies[taxonomy]?.metaDescription,
				reload      : true
			})
		}
	})
})(window.jQuery)