import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'

import { loadPiniaStores } from '@/vue/stores'

import SeoSetup from './SeoSetup'
import Overview from './Overview'

import { elemLoaded } from '@/vue/utils/elemLoaded'
import camelCase from 'lodash/camelCase'

const dashboardWidgetsMap = [
	{ id: 'aioseo-seo-setup-app', component: SeoSetup },
	{ id: 'aioseo-overview-app', component: Overview }
]

const loadDashboardWidget = (widget) => {
	let app = createApp({ ...widget.component, name: 'Standalone/DashboardWidgets' })
	app     = loadPlugins(app)
	app     = loadComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount('#' + widget.id)
}

dashboardWidgetsMap.forEach(widget => {
	if (!document.getElementById(widget.id)) {
		elemLoaded('#' + widget.id, camelCase(widget.id))
		document.addEventListener('animationstart', function (event) {
			if (camelCase(widget.id) === event.animationName) {
				loadDashboardWidget(widget)
			}
		}, { passive: true })
	} else {
		loadDashboardWidget(widget)
	}
})