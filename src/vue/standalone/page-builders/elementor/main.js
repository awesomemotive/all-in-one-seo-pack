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
import { isAioseoDocument } from './helpers'

import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'

import App from '@/vue/standalone/post-settings/App'
import Button from './components/Button'
import HighlighterHost from './components/HighlighterHost'

let aioseoApp = null,
	preload   = false,
	highlighterHostApp = null,
	syncInFlight       = null,
	// The document the load-time analysis has already covered, so opening the panel does not
	// re-run it. Null until one has run, which is what keeps the panel's own call as a fallback.
	analyzedDocumentId = null

const scoreButtonId = 'aioseo-elementor-toolbar-button'

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
	if (!isAioseoDocument()) {
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

// Elementor's document id is the authority on what is being edited; `window.aioseo.currentPost`
// is the admin page's post, stale after a document switch. Awaited before scoring, so content is
// never measured against another post's data. Concurrent callers share one request.
const syncPostData = () => {
	syncInFlight = syncInFlight || fetchCanvasPostData().finally(() => {
		syncInFlight = null
	})

	return syncInFlight
}

const fetchCanvasPostData = async () => {
	const postEditorStore   = usePostEditorStore()
	const redirectsStore    = useRedirectsStore()
	const seoRevisionsStore = useSeoRevisionsStore()
	const elementorPostId   = window.elementor?.config?.document?.id

	if (!elementorPostId || Number(postEditorStore.currentPost.id) === Number(elementorPostId)) {
		return true
	}

	try {
		const response = await postEditorStore.fetchPostData({
			postId          : elementorPostId,
			integrationSlug : 'elementor'
		})

		postEditorStore.updateState(response.body.data.currentPost)
		redirectsStore.updateState(response.body.data?.redirects ?? null)
		seoRevisionsStore.updateState(response.body.data?.seoRevisions ?? null)

		return true
	} catch (error) {
		console.error(error)

		return false
	}
}

// Runs the analysis for the document now in the canvas, without waiting for the AIOSEO panel,
// whose later mount re-enters these calls harmlessly.
const analyzeCurrentDocument = async () => {
	if (!isAioseoDocument()) {
		return
	}

	const synced = await syncPostData()

	initHighlighterHost()

	// Left unrecorded, so the panel's own fallback analyses once the data has loaded.
	if (!synced) {
		return
	}

	analyzedDocumentId = window.elementor?.config?.document?.id ?? null
	updatePostData()
}

// The highlighter's own host, mounted at load into a detached node so painting does not wait for
// the AIOSEO panel ({@see components/HighlighterHost}). Detached because it renders nothing and has
// no business occupying a slot in Elementor's chrome.
const initHighlighterHost = () => {
	if (highlighterHostApp || !isAioseoDocument()) {
		return
	}

	const node = document.createElement('div')
	node.id = 'aioseo-elementor-highlighter-host'
	document.body.appendChild(node)

	let app = createApp({
		name   : 'Standalone/Elementor/HighlighterHost',
		render : () => h(HighlighterHost)
	})

	app = loadPlugins(app)
	app = loadComponents(app)
	app = loadVersionedComponents(app)

	loadPiniaStores(app, null)

	app.mount(node)

	highlighterHostApp = app
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
					id    : scoreButtonId,
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

	// The button is mounted once and survives document switches. `attach-preview` skips a document that is already
	// attached, so returning to the initial one would leave the button hidden.
	$e.commands.on('run:after', (_component, command) => {
		if ('editor/documents/open' !== command) {
			return
		}

		const $scoreButton = document.getElementById(scoreButtonId)
		if ($scoreButton) {
			$scoreButton.hidden = !isAioseoDocument()
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
		if (isAioseoDocument()) {
			registerAioseoTab($e)
		}
	})

	// This hook alone, not `preview:loaded` too, which would analyse every load twice. It covers
	// the first paint and a document switch.
	registerElementorUIHookAfter('editor/documents/attach-preview', 'aioseo-analyze-on-attach-preview', () => {
		analyzeCurrentDocument()
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
					if (!isAioseoDocument()) {
						return
					}

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
					syncPostData()
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

	// Only for a document the load-time analysis has not covered, e.g. one attach-preview never
	// reached. Joins the sync onBeforeMount started, so it never scores the previous post's data.
	if (analyzedDocumentId !== (window.elementor?.config?.document?.id ?? null)) {
		syncPostData().then(synced => {
			const documentId = window.elementor?.config?.document?.id ?? null
			if (synced && analyzedDocumentId !== documentId) {
				analyzedDocumentId = documentId
				updatePostData()
			}
		})
	}

	return app
}

/**
 * Initializes the Elementor integration.
 *
 * @returns {void}
 */
const init = () => {
	// Before the hooks below dispatch: the watcher seeds its snapshot from attach-preview, and
	// registering during that dispatch is too late. Idempotent.
	initWatcher()

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