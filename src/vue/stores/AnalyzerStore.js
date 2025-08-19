import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'
import { __ } from '@/vue/plugins/translations'

import {
	useOptionsStore
} from '@/vue/stores'

import SeoAnalysis from '@/vue/classes/SeoAnalysis/SeoAnalysis'
import SiteAnalysis from '@/vue/classes/SiteAnalysis'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useAnalyzerStore = defineStore('AnalyzerStore', {
	state : () => ({
		activeTab     : 'all-urls',
		homepageAudit : {
			activeTab : 'error'
		},
		siteAudit : {
			activeTab : 'error'
		},
		analyzer     : null,
		analyzing    : false,
		analyzeError : null,
		homeResults  : {
			results : [],
			score   : 0
		},
		competitors   : {},
		issuesResults : {
			auditItems       : [],
			auditItemResults : {},
			isLoading        : true,
			counts           : {
				passed  : 0,
				warning : 0,
				error   : 0
			}
		},
		allUrlsResults : {
			rows      : [],
			isLoading : true,
			search    : '',
			totals    : {
				page  : 1,
				pages : 1,
				total : 1
			},
			auditItemResults : {}
		},
		allUrlsAdditionalFilters : {
			content_type : 'all'
		},
		objectsScan : {
			percent             : null,
			showProcessingPopup : false
		}
	}),
	getters : {
		issueResultsTotalCounts    : state => state.issuesResults.counts.passed + state.issuesResults.counts.warning + state.issuesResults.counts.error,
		getHeadlineAnalysisResults : () => {
			const optionsStore = useOptionsStore()
			return optionsStore.internalOptions.internal.headlineAnalysis.headlines || {}
		},
		getAnalyzer : state => (type, site = null) => {
			const result = {
				factory : null,
				results : null
			}

			switch (type) {
				case 'homepage':
					result.factory = SiteAnalysis
					result.results = state.homeResults.results
					break
				case 'site':
					result.factory = SeoAnalysis
					result.results = state.issuesResults.auditItems
					break
				case 'competitor':
					result.factory = SiteAnalysis
					result.results = state.competitors[site]?.results || {}
					break
			}

			return result
		},
		allItemsCount    : state => type => state.recommendedCount(type) + state.criticalCount(type) + state.goodCount(type),
		recommendedCount : state => (type, site) => {
			const { factory, results } = state.getAnalyzer(type, site)

			return factory?.getResultsCount(results, 'warning') || 0
		},
		criticalCount : state => (type, site) => {
			const { factory, results } = state.getAnalyzer(type, site)

			return factory?.getResultsCount(results, 'error') || 0
		},
		goodCount : state => (type, site) => {
			const { factory, results } = state.getAnalyzer(type, site)

			return factory?.getResultsCount(results, 'passed') || 0
		}
	},
	actions : {
		getCompetitorSiteAnalysisResults () {
			if (this.competitors?.length) {
				return this.competitors
			}

			return http.get(links.restUrl('seo-analysis/competitors'))
				.then(response => {
					this.competitors = response.body.result

					return this.competitors
				})
		},
		runSiteAnalyzer (payload = {}) {
			if (this.analyzing) {
				return
			}

			this.analyzing = true
			this.analyzer  = 'competitor-site'

			return http.post(links.restUrl('analyze'))
				.send({
					url     : payload.url,
					refresh : payload.refresh
				})
				.then(response => {
					if (payload.url) {
						this.analyzing = false
						return response
					}

					this.homeResults = response.body
					this.analyzing = false
				})
				.catch(error => {
					this.analyzing = false
					let message = __('We couldn\'t connect to the site, please try again later.', td)
					if (error.response?.body?.response?.error) {
						message = error.response.body.response.error
					}

					this.analyzeError = message
				})
		},
		runHeadlineAnalyzer (payload = {}) {
			this.analyzer = 'headline'
			return http.post(links.restUrl('analyze-headline'))
				.send({
					headline            : payload.headline,
					shouldStoreHeadline : payload.shouldStoreHeadline
				})
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'internal', 'headlineAnalysis' ], key: 'headlines', value: response.body })
					this.analyzing = false
				})
				.catch(error => {
					this.analyzing = false
					let message = __('We couldn\'t analyze your title, please try again later.', td)
					if (error.response.body?.message) {
						message = error.response.body.message
					}

					this.analyzeError = message
				})
		},
		deleteCompetitorSite (url) {
			return http.post(links.restUrl('analyze/delete-site'))
				.send({
					url
				})
				.then(() => {
					delete this.competitors[url]
					this.analyzing = false
				})
		},
		deleteHeadline (headline) {
			return http.post(links.restUrl('analyze-headline/delete'))
				.send({
					headline
				})
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'internal', 'siteAnalysis' ], key: 'headlines', value: response.body })
					this.analyzing = false
				})
		},
		fetchPostsByIssue ({ filter, limit, offset, searchTerm }) {
			const page = 0 === offset ? 1 : (offset / limit) + 1

			// Get posts by issue code
			return http.get(links.restUrl(`seo-analysis/objects/${filter}`))
				.query({
					search : searchTerm,
					limit,
					offset
				})
				.then(response => {
					this.issuesResults.auditItemResults[filter] = { ...response.body.result, search: searchTerm, page }

					return response
				})
		},
		// filter = row item
		fetchIssuesByObject ({ filter, limit, offset, searchTerm }) {
			const page = 0 === offset ? 1 : (offset / limit) + 1
			const { id, type } = filter
			const key = `${id}-${type.toLowerCase()}`

			if (
				this.allUrlsResults.auditItemResults[key] &&
				this.allUrlsResults.auditItemResults[key].search === searchTerm &&
				this.allUrlsResults.auditItemResults[key].page === page
			) {
				return Promise.resolve()
			}

			// Get issues by object id and object tpye
			return http.get(links.restUrl(`seo-analysis/issues/${type.toLowerCase()}/${id}`))
				.query({
					search : searchTerm,
					limit,
					offset
				})
				.then(response => {
					this.allUrlsResults.auditItemResults[key] = { ...response.body.result, search: searchTerm, page }

					return response
				})
		},
		fetchAllUrls ({ limit, offset, searchTerm = '', additionalFilters = [] }) {
			this.allUrlsResults.isLoading = true
			return http.get(links.restUrl('seo-analysis/objects'))
				.query({
					search : searchTerm,
					limit,
					offset,
					additionalFilters
				})
				.then(response => {
					this.allUrlsResults = { ...response.body.result, search: searchTerm, auditItemResults: {}, isLoading: false }

					return response
				})
		},
		async fetchSitePagesAnalysisResults () {
			this.issuesResults.isLoading = true
			 return http.get(links.restUrl('seo-analysis/issues'))
				.then(response => {
					this.issuesResults.auditItems = response.body.result?.results
					this.issuesResults.counts = response.body.result?.counts
					this.issuesResults.isLoading = false
				})
		},
		doIgnoreIssue (row, resultsGroup) {
			const { issueId } = row

			return http.post(links.restUrl(`seo-analysis/issues/${issueId}/ignore`))
				.then(() => {
					resultsGroup.rows = resultsGroup.rows.filter(row => row.issueId !== issueId)
					resultsGroup.totals.total--
				})
		},
		changeHomepageAuditTab (value) {
			this.homepageAudit.activeTab = value
		},
		changeSiteAuditTab (value) {
			this.siteAudit.activeTab = value
		},
		changeSeoAnalyzerTab (value) {
			this.activeTab = value
		},
		doAddFocusKeyword (id, value) {
			return http.post(links.restUrl(`seo-analysis/objects/${id}/keywords`))
				.send({
					keyphrase : value
				})
		},
		toggleProcessingPopup () {
			this.objectsScan.showProcessingPopup = !this.objectsScan.showProcessingPopup
		},
		async fetchObjectsScanPercent () {
			return http.get(links.restUrl('seo-analysis/objects-scan-percent'))
				.then(async response => {
					if (response.body && 'percent' in response.body) {
						this.objectsScan.percent = response.body.percent

						if (100 !== response.body.percent) {
							setTimeout(() => {
								this.fetchObjectsScanPercent()
							}, 10000)
						}
					}
				})
		}
	}
})