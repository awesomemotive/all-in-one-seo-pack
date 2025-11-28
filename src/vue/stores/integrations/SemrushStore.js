import { defineStore } from 'pinia'
import { DateTime } from 'luxon'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { __ } from '@/vue/plugins/translations'

import {
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useSemrushStore = defineStore('SemrushStore', {
	state : () => ({
		results : [],
		error   : null
	}),
	getters : {
		expired : () => {
			const optionsStore = useOptionsStore()
			if (!optionsStore.internalOptions.integrations.semrush.expires) {
				return true
			}

			const now     = DateTime.now()
			const expires = DateTime.fromMillis(parseInt(optionsStore.internalOptions.integrations.semrush.expires * 1000, 10))

			return now >= expires
		},
		hasValidTokens : (store) => {
			const optionsStore = useOptionsStore()
			return !store.expired && !!optionsStore.internalOptions.integrations.semrush.accessToken && !!optionsStore.internalOptions.integrations.semrush.refreshToken
		}
	},
	actions : {
		getKeyphrases (database) {
			this.error = null
			const postEditorStore = usePostEditorStore()
			return http.post(links.restUrl('integration/semrush/keyphrases'))
				.send({
					keyphrase : postEditorStore.currentPost.keyphrases.focus.keyphrase,
					database
				})
				.then(response => {
					if (403 === response.body.keyphrases.status) {
						this.error = response.body.keyphrases.error
						return
					}
					this.results = response.body.keyphrases.data.rows
				})
				.catch(error => {
					this.results = []

					if (!error?.response?.body?.message && !error?.response?.message) {
						this.error = __('An unknown error occurred, please try again later.', td)
						return
					}

					this.error = error?.response?.body?.message || error?.response?.message
				})
		},
		authenticate (code) {
			this.error = null
			return http.post(links.restUrl('integration/semrush/authenticate'))
				.send({
					code
				})
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'integrations' ], key: 'semrush', value: response.body.semrush }, { root: true })
					optionsStore.internalOptions.integrations.semrush = response.body.semrush
				})
				.catch(error => {
					if (!error?.response?.body?.message) {
						this.error = __('An unknown error occurred, please try again later.', td)
						return
					}

					this.error = error.response.body.message
				})
		},
		refresh () {
			return http.post(links.restUrl('integration/semrush/refresh'))
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'integrations' ], key: 'semrush', value: response.body.semrush }, { root: true })
					optionsStore.internalOptions.integrations.semrush = response.body.semrush
				}).catch(_error => { // eslint-disable-line no-unused-vars
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'integrations' ], key: 'semrush', value: '' }, { root: true })
					optionsStore.internalOptions.integrations.semrush = ''
				})
		}
	}
})