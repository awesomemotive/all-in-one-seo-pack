import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import App from './App.vue'

import { elemLoaded } from '@/vue/utils/elemLoaded'

const insertRequiredElements = () => {
	const form = document.getElementById('your-profile')
	if (!form) {
		return
	}
	// Add our tab bar and hidden form field.
	const targetElement = document.createElement('div')
	targetElement.id    = 'aioseo-user-profile-tab-wrapper'

	const hiddenInputElement = document.createElement('input')
	hiddenInputElement.id    = 'aioseo-user-social-profiles'
	hiddenInputElement.name  = 'aioseo-user-social-profiles'
	hiddenInputElement.type  = 'hidden'

	form.prepend(hiddenInputElement)
	form.prepend(targetElement)
}

const mountApp = () => {
	let app = createApp({ ...App, name: 'Standalone/UserProfileTab' })
	app     = loadPlugins(app)
	app     = loadComponents(app)
	app     = loadVersionedComponents(app)

	// Use the pinia store.
	loadPiniaStores(app)

	app.mount('#aioseo-user-profile-tab-wrapper')
}

elemLoaded('#your-profile', 'profilePageLoaded')
document.addEventListener('animationstart', function (event) {
	if ('profilePageLoaded' !== event.animationName) {
		return
	}

	insertRequiredElements()
	mountApp()
})