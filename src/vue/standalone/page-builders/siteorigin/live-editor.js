import { h, createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { debounce } from '@/vue/utils/debounce'
import loadPlugins from '@/vue/plugins'
import loadComponents from '@/vue/components/common'
import { loadPiniaStores } from '@/vue/stores'

import Button from './components/Button'
import Sidebar from './components/Sidebar'
import LimitModifiedDate from './components/LimitModifiedDate'

let btnApp = null,
	sidebarApp = null,
	lmdApp = null

const btnWrapperId     = '#so-live-editor-aioseo-button'
const sidebarWrapperId = '#so-live-editor-aioseo-sidebar'
const lmdWrapperId     = '#so-live-editor-aioseo-lmd'

/**
 * Mount our Modal to render the SEO Settings.
 *
 * @returns {void}
 */
const mountComponent = ({ id, component, name, rootProps, data }) => {
	// Router placeholder to prevent errors when using router-link.
	const router = createRouter({
		history : createWebHistory(),
		routes  : [
			{
				path      : '/:pathMatch(.*)*', // Catch-all keeps Vue Router from warning about the current WP admin URL.
				component : component
			}
		]
	})

	let app = createApp({
		name   : `Standalone/SiteOrigin/${name}`,
		render : () => h(component),
		data   : () => data || {}
	}, rootProps || {})

	app = loadPlugins(app)
	app = loadComponents(app)

	app.use(router)

	router.app = app

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount(id)

	return app
}

/**
 * Create a wrapper element for our toolbar items.
 *
 * @param {string} id The ID of the wrapper.
 * @returns {HTMLElement} The wrapper element.
 */
const createWrapper = (id) => {
	const $wrapper     = document.createElement('div')
	$wrapper.id        = id.replace('#', '')
	$wrapper.className = 'aioseo-live-editor-item'

	return $wrapper
}

/**
 * Mount the toolbar and sidebar.
 *
 * @param {HTMLElement} $builder The builder element.
 * @returns {void}
 */
const mountToolbar = ($builder) => {
	// If we don't have the button in the DOM, mount it.
	if (!$builder.querySelector(btnWrapperId)) {
		const $toolbar = $builder.querySelector('.so-builder-toolbar')
		$toolbar.insertAdjacentElement('beforeend', createWrapper(btnWrapperId))

		btnApp?.unmount()
		btnApp = mountComponent({
			id        : btnWrapperId,
			component : Button,
			name      : 'Button'
		})
	}

	// If we don't have the sidebar in the DOM, mount it.
	if (!$builder.querySelector(sidebarWrapperId)) {
		const $container = $builder.querySelector('.so-rows-container')
		$container.insertAdjacentElement('beforeend', createWrapper(sidebarWrapperId))

		sidebarApp?.unmount()
		sidebarApp = mountComponent({
			id        : sidebarWrapperId,
			component : Sidebar,
			name      : 'Sidebar',
			data      : {
				tableContext  : window.aioseo.currentPost.context,
				screenContext : 'sidebar'
			}
		})
	}
}

/**
 * Mount the Limit Modified Date component.
 *
 * @returns {void}
 */
const maybeMountLimitModifiedDate = () => {
	const $toolbar = document.querySelector('.so-sidebar-tools')
	if (!$toolbar || $toolbar.querySelector(lmdWrapperId)) {
		return
	}

	$toolbar.insertAdjacentElement('afterbegin', createWrapper(lmdWrapperId))

	lmdApp?.unmount()
	lmdApp = mountComponent({
		id        : lmdWrapperId,
		component : LimitModifiedDate,
		name      : 'LimitModifiedDate',
		data      : {
			tableContext  : window.aioseo.currentPost.context,
			screenContext : 'limitModifiedDate'
		}
	})
}

export default (builderView) => {
	const $builder = document.querySelector('.siteorigin-panels-builder')

	// If builder is not present, or we already have mounted, do nothing.
	if (!$builder || $builder.querySelector('.aioseo-live-editor-item')) {
		return
	}

	mountToolbar($builder)

	builderView.on('builder_resize', () => {
		debounce(maybeMountLimitModifiedDate, 500)
	})
}