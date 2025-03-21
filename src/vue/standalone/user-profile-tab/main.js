import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import App from './App'

import { elemLoaded } from '@/vue/utils/elemLoaded'
import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

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

	// Unload WordPress Core's event listener that warns users about unsaved changes.
	window.jQuery(window).off('beforeunload')

	form.prepend(hiddenInputElement)
	form.prepend(targetElement)

	// Once our elements are inserted, redefine the event listener.
	let isDirty = false
	window.jQuery(window).off('beforeunload').on('beforeunload', function () {
		if (isDirty) {
			return __('Your profile has unsaved changes. If you leave this page, you will lose your changes.', td)
		}
	})

	// Track changes to form fields.
	window.jQuery('#your-profile').on('change input', ':input', function () {
		isDirty = true
	})

	// Reset when form is submitted.
	window.jQuery('#your-profile').on('submit', function () {
		isDirty = false
	})
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