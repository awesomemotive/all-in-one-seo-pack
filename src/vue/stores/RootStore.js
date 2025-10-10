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
			active   : null,
			all      : new Set(),
			rendered : new Set()
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

			this.modals.all.add(modal)
		},
		unsetActiveModal (activeModal) {
			// Remove the active modal from the list of all modals.
			this.modals.all.delete(activeModal)

			// Get the last modal in the list of all modals and set it as the active modal.
			this.modals.active = [ ...this.modals.all ].pop() || null
		}
	}
})