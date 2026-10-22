import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useSettingsStore = defineStore('SettingsStore', {
	state : () => ({
		settings    : {},
		metaBoxTabs : {
			// A one-shot deep-link intent, not the open tab — its consumers clear it.
			mainSidebar   : {},
			main          : 'general',
			modal         : 'general',
			social        : 'facebook',
			socialModal   : 'facebook',
			linkAssistant : 'inbound-internal'
		},
		modals : {
			aiGenerator : false
		},
		userProfile : {}
	}),
	actions : {
		closeCard (slug) {
			this.settings.toggledCards[slug] = false
		},
		// Persists, unlike closeCard: the toggle endpoint flips whatever it has stored, so leaving
		// the server on the old value would make the user's next click invert the card instead.
		openCard (slug) {
			if (this.settings.toggledCards[slug]) {
				return
			}

			this.toggleCard({ slug, shouldSave: true })
		},
		changeTabSettings ({ setting, value }) {
			this.metaBoxTabs[setting] = value
		},
		setModalState ({ modalName, value }) {
			this.modals[modalName] = value
		},
		toggleCard ({ slug, shouldSave }) {
			this.settings.toggledCards[slug] = !this.settings.toggledCards[slug]

			if (shouldSave) {
				http.post(links.restUrl('settings/toggle-card'))
					.send({
						card : slug
					})
					.then(() => {})
			}
		},
		toggleRadio ({ slug, value }) {
			this.settings.toggledRadio[slug] = value

			http.post(links.restUrl('settings/toggle-radio'))
				.send({
					radio : slug,
					value : value
				})
				.then(() => {})
		},
		dismissAlert (alert) {
			this.settings.dismissedAlerts[alert] = true

			http.post(links.restUrl('settings/dismiss-alert'))
				.send({
					alert
				})
				.then(() => {})
		},
		dismissReviewCta (delay) {
			return http.post(links.restUrl('settings/dismiss-review-cta'))
				.send({
					delay
				})
				.then(() => {})
				.catch(error => console.error('Failed to dismiss the review CTA.', error))
		},
		changeItemsPerPage ({ slug, value }) {
			this.settings.tablePagination[slug] = value

			return http.post(links.restUrl('settings/items-per-page'))
				.send({
					table : slug,
					value : value
				})
				.then(() => {})
		},
		changeTab ({ slug, value }) {
			this.settings.internalTabs[slug] = value
		},
		hideUpgradeBar () {
			this.settings.showUpgradeBar = false

			return http.post(links.restUrl('settings/hide-upgrade-bar'))
				.send({})
				.then(() => {})
		},
		hideSetupWizard () {
			this.settings.showSetupWizard = false

			return http.post(links.restUrl('settings/hide-setup-wizard'))
				.send({})
				.then(() => {})
		},
		changeSemrushCountry ({ value }) {
			this.settings.semrushCountry = value

			return http.post(links.restUrl('settings/semrush-country'))
				.send({
					value
				})
				.then(() => {})
		}
	}
})