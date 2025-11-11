import { defineStore } from 'pinia'

export const useTableOfContentsStore = defineStore('TableOfContentsStore', {
	state : () => ({
		clientIds : []
	}),
	getters : {
		// Get all headings from all registered TOC blocks
		headings : (state) => {
			if (!window.wp?.data?.select) {
				return []
			}

			const allHeadings = []
			state.clientIds.forEach(clientId => {
				const blockAttributes = window.wp.data.select('core/block-editor')?.getBlockAttributes(clientId)
				if (blockAttributes?.headings) {
					allHeadings.push(...blockAttributes.headings)
				}
			})

			return allHeadings
		}
	},
	actions : {
		addClientId (id) {
			// If the client ID already exists, don't add it again.
			if (!this.clientIds.includes(id)) {
				this.clientIds.push(id)
			}
		},
		removeClientId (id) {
			this.clientIds = this.clientIds.filter((clientId) => clientId !== id)
		}
	}
})