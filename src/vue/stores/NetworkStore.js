import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { normalizeRules } from '@/vue/utils/robots'

import {
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

// The site's load in progress, kept out of the state: a save waits on it rather than sending an unloaded site's rules.
let siteRobotsRequest = null

export const useNetworkStore = defineStore('NetworkStore', {
	state : () => ({
		activeSites   : [],
		networkData   : {},
		networkRobots : {
			rules        : [],
			defaultRules : null,
			sitemapUrls  : null,
			isMainSite   : false,
			loading      : false,
			loadFailed   : false,
			saveBlocked  : false
		},
		currentSite : {}
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
			// The previous site's data must not stay on screen, or be saved, under this site's name.
			this.networkRobots.rules        = []
			this.networkRobots.defaultRules = null
			this.networkRobots.sitemapUrls  = null
			this.networkRobots.isMainSite   = false
			this.networkRobots.loading      = true
			this.networkRobots.loadFailed   = false
			this.networkRobots.saveBlocked  = false

			siteRobotsRequest = http.get(links.restUrl(`network-robots/${blogId}`))
				.then(response => {
					// Normalize at the store's entry point — the table's row indices have to line up with the store.
					this.networkRobots.rules        = normalizeRules(response.body?.rules)
					this.networkRobots.defaultRules = response.body?.defaultRules ?? null
					this.networkRobots.sitemapUrls  = response.body?.sitemapUrls ?? null
					this.networkRobots.isMainSite   = !!response.body?.isMainSite
				})
				.catch(error => {
					this.networkRobots.loadFailed = true

					throw error
				})
				.finally(() => {
					this.networkRobots.loading = false
				})

			return siteRobotsRequest
		},
		waitForSiteRobots () {
			// Settles either way; the caller reads loadFailed afterwards.
			return siteRobotsRequest ? siteRobotsRequest.catch(() => {}) : Promise.resolve()
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