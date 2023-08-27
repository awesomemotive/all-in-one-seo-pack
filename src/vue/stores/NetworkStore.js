import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

export const useNetworkStore = defineStore('NetworkStore', {
	state : () => ({
		activeSites   : [],
		networkData   : {},
		networkRobots : {
			siteId : 'network',
			rules  : []
		}
	}),
	getters : {
		getNetworkRobots : () => {
			const optionsStore = useOptionsStore()
			return optionsStore.networkOptions.tools ? optionsStore.networkOptions.tools.robots : []
		}
	},
	actions : {
		fetchNetworkSites ({ orderBy, orderDir, limit, offset, searchTerm, filter }) {
			return http.post(links.restUrl(`network-sites/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm
				})
				.then(response => {
					const rootStore = useRootStore()
					rootStore.aioseo.data = {
						...rootStore.aioseo.data,
						...{
							network : {
								...rootStore.aioseo.data.network,
								sites : response.body.sites
							}
						}
					}

					this.networkData.sites = response.body.sites
				})
		},
		fetchSiteRobots (blogId) {
			return http.get(links.restUrl(`network-robots/${blogId}`))
				.then(response => {
					this.networkRobots.rules  = response.body.rules
					this.networkRobots.siteId = blogId
				})
		},
		getActiveSites (domains) {
			return http.post(links.restUrl('activated'))
				.send({ domains })
				.then(response => {
					this.activeSites = response.body.activeSites
				})
		},
		importRobotsTxt ({ url, text, source, networkLevel, blogId }) {
			return http.post(links.restUrl('tools/import-robots-txt'))
				.send({
					url,
					text,
					source,
					networkLevel,
					blogId
				})
				.then(response => {
					return response
				})
		}
	}
})