import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useRootStore = defineStore('RootStore', {
	state : () => ({
		pong     : true,
		loaded   : false,
		loading  : false,
		isPro    : 'pro' === import.meta.env.VITE_VERSION.toLowerCase(),
		aioseo   : {},
		navigate : {
			scroll    : null,
			highlight : null
		},
		modals : {
			active : null,
			all    : []
		}
	}),
	actions : {
		ping () {
			http.get(links.restUrl('ping'))
				.catch(() => {
					this.pong = false
				})
		},
		setActiveModal (modal) {
			this.modals.active = modal

			// Only add the modal to the list of all modals if it is not already in the list.
			if (!this.modals.all.includes(modal)) {
				this.modals.all.push(modal)
			}
		},
		unsetActiveModal (activeModal) {
			// If the active modal is not the modal we are trying to unset, do nothing.
			if (!this.modals.all.includes(activeModal)) {
				return
			}

			// Remove the active modal from the list of all modals.
			this.modals.all = this.modals.all.filter((modal) => {
				return modal !== activeModal
			})

			// Get the last modal in the list of all modals and set it as the active modal.
			this.modals.active = this.modals.all[this.modals.all.length - 1] || null
		}
	}
})