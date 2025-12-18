import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import {
	loadPiniaStores,
	usePostEditorStore,
	useRedirectsStore,
	useSeoRevisionsStore
} from '@/vue/stores'

import PageBuilderIntegration from '../PageBuilderIntegration'
import initWatcher from './watcher'
import initIntroduction from './introduction'
import initLimitModifiedDate from './limit-modified-date'
import { registerElementorUIHookAfter } from './hooks'

import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'

import App from '@/vue/standalone/post-settings/App'
import Button from './components/Button'

let aioseoApp = null,
	preload   = false

/**
 * Adds body class for Elementor color scheme (dark/light).
 *
 * @returns {void}
 */
const addBodyClass = () => {
	let theme = window.elementor.getPreferences('ui_theme') || 'auto'
	if ('auto' === theme) {
		theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
	}

	document.body.classList.forEach(className => {
		if (className.startsWith('aioseo-elementor-')) {
			document.body.classList.remove(className)
		}
	})

	document.body.classList.add('aioseo-elementor-' + theme)
}

/**
 * Checks if the current document is a post or page (not Site Settings, etc.).
 *
 * @returns {boolean} True if editing a post or page.
 */
const isPostOrPage = () => {
	const currentDocument = window.elementor?.documents?.getCurrent()
	return [ 'wp-post', 'wp-page' ].includes(currentDocument?.config?.type)
}

/**
 * Registers the AIOSEO tab in Elementor's panel if not already registered.
 *
 * @param {Object} $e Elementor's $e object.
 * @returns {void}
 */
const registerAioseoTab = ($e) => {
	const elementsComponent = $e.components.get('panel/elements')
	if (elementsComponent && !elementsComponent.tabs?.aioseo) {
		elementsComponent.addTab('aioseo', {
			title : 'AIOSEO'
		})
	}
}

/**
 * Opens the AIOSEO panel in Elementor's page settings.
 *
 * @param {Object}        root0    The window object.
 * @param {Object}        root0.$e Elementor's $e object.
 * @returns {void}
 */
const runAioseoRoute = ({ $e }) => {
	if (!isPostOrPage()) {
		return
	}

	registerAioseoTab($e)

	try {
		$e.routes.run('panel/page-settings/aioseo')
	} catch (_error) {
		// Page settings must be visited first before we can open the AIOSEO tab.
		$e.routes.run('panel/page-settings/settings')
		$e.routes.run('panel/page-settings/aioseo')
	}
}

const initAioseoButton = async () => {
	const integration = new PageBuilderIntegration({
		scoreBtn : {
			component : Button,
			appName   : 'Standalone/Elementor/Button',
			node      : {
				$wrapper   : document.querySelector('#elementor-editor-wrapper-v2 .MuiGrid-container:first-child .MuiStack-root:last-child'),
				tag        : 'span',
				attributes : {
					id    : 'aioseo-elementor-toolbar-button',
					style : 'cursor: pointer;',
					role  : 'button'
				}
			}
		}
	})

	await integration.mount()

	document.addEventListener('aioseo-pagebuilder-toggle-modal', () => {
		runAioseoRoute(window)
	})
}

/**
 * Sets up Elementor hooks, routes and panel regions for AIOSEO.
 *
 * @returns {void}
 */
const initElementorHooks = ({ elementor, $e, Marionette }) => {
	$e.routes.on('run:after', function (_component, route) {
		addBodyClass()

		if ('panel/page-settings/aioseo' === route) {
			aioseoApp?.unmount()
			aioseoApp = initAioseoEditor('#elementor-panel-page-settings-controls')
		}
	})

	elementor.modules.layouts.panel.pages.menu.Menu.addItem({
		name     : 'aioseo',
		icon     : 'aioseo aioseo-element-menu-icon aioseo-element-menu-icon-' + elementor.getPreferences('ui_theme'),
		title    : import.meta.env.VITE_NAME,
		type     : 'page',
		callback : () => {
			runAioseoRoute(window)
		}
	}, 'more')

	elementor.once('preview:loaded', function () {
		registerAioseoTab($e)
	})

	// Re-register the tab after returning from Site Settings.
	registerElementorUIHookAfter('editor/documents/attach-preview', 'aioseo-register-tab-on-document-switch', () => {
		if (isPostOrPage()) {
			registerAioseoTab($e)
		}
	})

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
					aioseoApp?.unmount()
					aioseoApp = initAioseoEditor('#elementor-panel-aioseo')
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
 * Mounts the AIOSEO Vue app inside the Elementor panel.
 *
 * @param {string} selector Container selector.
 * @returns {Object} The Vue app instance.
 */
const initAioseoEditor = (selector) => {
	const wrapper = document.querySelector(selector)
	wrapper.classList.add('edit-post-sidebar', 'editor-sidebar', 'aioseo-elementor-panel')
	wrapper.appendChild(document.createElement('div'))

	const router = createRouter({
		history : createWebHistory(),
		routes  : [
			{
				path      : '/:pathMatch(.*)*',
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
		render : () => h(
			App,
			{
				onBeforeMount () {
					const postEditorStore   = usePostEditorStore()
					const redirectsStore = useRedirectsStore()
					const seoRevisionsStore = useSeoRevisionsStore()
					const elementorPostId   = window.elementor?.config?.document?.id
					if (elementorPostId && Number(postEditorStore.currentPost.id) !== Number(elementorPostId)) {
						try {
							postEditorStore.fetchPostData({
								postId          : elementorPostId,
								integrationSlug : 'elementor'
							})
								.then((response) => {
									postEditorStore.updateState(response.body.data.currentPost)
									redirectsStore.updateState(response.body.data?.redirects ?? null)
									seoRevisionsStore.updateState(response.body.data?.seoRevisions ?? null)
								})
						} catch (error) {
							console.error(error)
						}
					}
				}
			}
		)
	})

	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	app.use(router)

	router.app = app

	loadPiniaStores(app, router)

	initWatcher()

	app.mount(`${selector} > div`)

	updatePostData()

	return app
}

/**
 * Initializes the Elementor integration.
 *
 * @returns {void}
 */
const init = () => {
	initAioseoButton()
	initElementorHooks(window)
	initIntroduction(window)
	initLimitModifiedDate(window)
}

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