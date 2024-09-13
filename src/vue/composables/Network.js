import {
	useNetworkStore,
	useRootStore
} from '@/vue/stores'

import { computed } from 'vue'

export const useNetwork = () => {
	const networkStore = useNetworkStore()
	const rootStore = useRootStore()

	const getSites = computed(() => {
		return JSON.parse(JSON.stringify(networkStore.networkData.sites.sites))
	})

	const activeSitesIds = computed(() => {
		const ids = []
		getSites.value.forEach(s => {
			if (networkStore.activeSites.some(as => as.domain === s.domain && as.path === s.path)) {
				ids.push(getUniqueSiteId(s))
			}
		})
		return ids
	})

	const getUniqueSiteId = (site) => {
		return `${site.blog_id}_${site.domain}_${site.path}`
	}

	const isMainSite = (domain, path) => {
		return (rootStore.aioseo.urls.mainSiteUrl + '/').includes(`${(rootStore.aioseo.data.isSsl ? 'https' : 'http')}://${domain}${path}`)
	}

	const getSiteByUniqueId = (uniqueSiteId) => {
		return getSites.value.find(s => getUniqueSiteId(s) === uniqueSiteId)
	}

	const isSiteActive = (site) => {
		return activeSitesIds.value.includes(getUniqueSiteId(site))
	}

	const parseSiteValue = (sites) => {
		const parsedSites = []
		sites.forEach(uniqueSiteId => {
			parsedSites.push(getSiteByUniqueId(uniqueSiteId))
		})
		return parsedSites
	}

	return {
		getSites,
		activeSitesIds,
		getUniqueSiteId,
		isMainSite,
		isSiteActive,
		parseSiteValue
	}
}