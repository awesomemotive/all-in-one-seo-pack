import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useWpCodeStore = defineStore('WpCodeStore', {
	state : () => ({
		snippets          : [],
		pluginInstalled   : false,
		pluginActive      : false,
		pluginNeedsUpdate : false,
		ctaUrl            : ''
	}),
	getters : {
		getSnippets : state => state.snippets
	},
	actions : {
		loadSnippets () {
			return http.post(links.restUrl('integration/wpcode/snippets'))
				.send()
				.then(response => {
					if (response.body.snippets) {
						this.snippets = response.body.snippets
					}

					this.pluginInstalled   = response.body.pluginInstalled
					this.pluginActive      = response.body.pluginActive
					this.pluginNeedsUpdate = response.body.pluginNeedsUpdate
				})
		}
	}
})