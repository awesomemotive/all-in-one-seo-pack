import { defineStore } from 'pinia'

export const useAddonsStore = defineStore('AddonsStore', {
	state : () => ({
		addons : []
	}),
	actions : {
		updateAddon (addon) {
			const addonIndex = this.addons.findIndex(a => a.sku === addon.sku)

			if (-1 !== addonIndex) {
				this.addons[addonIndex] = addon
			}
		}
	}
})