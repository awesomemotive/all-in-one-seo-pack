import { defineStore } from 'pinia'

export const useTableOfContentsStore = defineStore('TableOfContentsStore', {
	state : () => ({
		blockClientId : null,
		headings      : [],
		listStyle     : 'ul',
		reOrdered     : false
	}),
	actions : {
		setHeadings (headings) {
			this.headings = headings
		}
	}
})