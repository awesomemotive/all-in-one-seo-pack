import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useWritingAssistantSettingsStore = defineStore('WritingAssistantSettings', {
	state : () => ({
		seoBoost : {
			isLoggedIn  : false,
			loginUrl    : '',
			userOptions : {}
		},
		updating : {
			userOptions : false
		}
	}),
	getters : {
		loading : (state) => {
			return state.updating.userOptions
		},
		getCountriesOptions : (state) => {
			const countries = []
			if (!state.seoBoost.userOptions?.country_list) {
				return countries
			}

			Object.keys(state.seoBoost.userOptions?.country_list).forEach(function (key) {
				countries.push({
					label : state.seoBoost.userOptions?.country_list[key] + ' (' + state.seoBoost.userOptions?.search_engine_list[key] + ')',
					value : key
				})
			})
			return countries
		},
		userCountryOption : (state) => {
			return state.getCountriesOptions.find(option => option.value === state.seoBoost.userOptions.country) || []
		},
		getLanguagesOptions : (state) => {
			const languages = []
			if (!state.seoBoost.userOptions?.language_list) {
				return languages
			}

			Object.keys(state.seoBoost.userOptions?.language_list).forEach(function (key) {
				languages.push({
					label : state.seoBoost.userOptions?.language_list[key],
					value : key
				})
			})
			return languages
		},
		userLanguageOption : (state) => {
			return state.getLanguagesOptions.find(option => option.value === state.seoBoost.userOptions.language) || []
		}
	},
	actions : {
		setUserLoggedIn (isLoggedIn) {
			this.seoBoost.isLoggedIn = isLoggedIn
			this.refreshUserOptions()
		},
		disconnect () {
			http.post(links.restUrl('writing-assistant/disconnect'))
			    .send()
			    .then(response => {
				    if (response.body.success) {
					    this.seoBoost.isLoggedIn = false
				    }
			    })
		},
		hookSaveUserOptions () {
			window.aioseoBus.$on('saving-changes', () => {
				this.saveUserOptions()
			})
		},
		saveUserOptions () {
			if (!this.seoBoost.isLoggedIn) {
				return
			}

			http.post(links.restUrl('writing-assistant/user-options'))
			    .send({
				    country  : this.seoBoost.userOptions.country,
				    language : this.seoBoost.userOptions.language
			    }).then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}
				})
		},
		getCountryLabel (country) {
			return this.getCountriesOptions.find(option => option.value.toUpperCase() === country.toUpperCase())?.label || ''
		},
		getLanguageLabel (language) {
			return this.getLanguagesOptions.find(option => option.value === language)?.label || ''
		},
		refreshUserOptions () {
			if (this.updating.userOptions) {
				return
			}

			this.updating.userOptions = true
			http.get('/writing-assistant/user-options')
			    .then(result => {
				    if (!result.body?.error) {
					    this.seoBoost.userOptions = result.body || {}
				    }
				    this.updating.userOptions = false
			    })
		}
	}
})