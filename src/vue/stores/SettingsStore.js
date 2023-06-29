import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useSettingsStore = defineStore('SettingsStore', {
	state : () => ({
		settings    : {},
		metaBoxTabs : {
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
		}
	}
})