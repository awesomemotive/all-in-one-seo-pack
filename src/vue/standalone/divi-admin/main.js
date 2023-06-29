import '@/vue/utils/vue2.js'
import { createApp } from 'vue'

import loadPlugins from '@/vue/plugins'

import loadComponents from '@/vue/components/common'
import loadVersionedComponents from '@/vue/components/AIOSEO_VERSION'

import { loadPiniaStores } from '@/vue/stores'

import App from './App.vue'

/**
 * Mount the Alert component inside each tab of the SEO settings from Divi.
 *
 * @returns {void}
 */
const mountAlert = () => {
	const tabs = document.querySelectorAll('#wrap-seo .et-tab-content')
	for (let i = 0; i < tabs.length; i++) {
		// Create an element to render the App.
		const element = document.createElement('div')
		element.setAttribute('id', `aioseo-divi-seo-admin-notice-container-${i}`)

		// Insert the element before the first child of the tab content.
		tabs[i].insertBefore(element, tabs[i].firstChild)

		let app = createApp({ ...App, name: 'Standalone/DiviAdmin' })
		app     = loadPlugins(app)
		app     = loadComponents(app)
		app     = loadVersionedComponents(app)

		// Use the pinia store.
		loadPiniaStores(app)

		app.mount(`#${element.getAttribute('id')}`)
	}
}

/**
 * Disable all the Divi input fields from their interface.
 *
 * @returns {void}
 */
const disableDiviSeoInputs = () => {
	const textInputs = document.querySelectorAll('#wrap-seo input[type="text"], #wrap-seo textarea')
	for (let i = 0; i < textInputs.length; i++) {
		textInputs[i].style.pointerEvents = 'none'
		textInputs[i].setAttribute('readonly', true)
	}

	const selectInputs = document.querySelectorAll('#wrap-seo select')
	for (let i = 0; i < selectInputs.length; i++) {
		selectInputs[i].style.pointerEvents = 'none'
		selectInputs[i].setAttribute('disabled', true)
	}

	const toggleInputs = document.querySelectorAll('#wrap-seo .et-checkbox')
	for (let i = 0; i < toggleInputs.length; i++) {
		toggleInputs[i].setAttribute('disabled', true)
		toggleInputs[i].nextElementSibling.style.pointerEvents = 'none'
	}
}

/**
 * Changes the SEO tab link to redirect to our Search Appearance page.
 *
 * @returns {void}
 */
const changeTabLink = () => {
	const url = window.aioseo.urls.aio.searchAppearance
	const tabLink = document.querySelector('a[href="#wrap-seo"]')
	if (!url || !tabLink) {
		return
	}

	// Clone the element to remove all event listeners.
	const newTabLink = tabLink.cloneNode(true)
	newTabLink.setAttribute('href', url)
	tabLink.parentNode.replaceChild(newTabLink, tabLink)
}

// Trigger after the whole window loaded.
window.addEventListener('load', () => {
	// Mount our Alert component.
	mountAlert()

	// Disable the Divi SEO inputs.
	disableDiviSeoInputs()

	// Change the SEO tab link to redirect to our page.
	changeTabLink()
})