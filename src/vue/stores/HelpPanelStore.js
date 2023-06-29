import { defineStore } from 'pinia'

export const useHelpPanelStore = defineStore('HelpPanelStore', {
	state : () => ({
		categories : {},
		docs       : {}
	})
})