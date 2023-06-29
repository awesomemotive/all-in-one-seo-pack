import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	usePostEditorStore
} from '@/vue/stores'

export const useLinkAssistantStore = defineStore('LinkAssistantStore', {
	state : () => ({
		options : {
			main : {
				affiliatePrefix : '',
				wordsToIgnore   : '',
				skipSentences   : 3,
				postTypes       : {
					all      : true,
					included : null
				},
				postStatuses : {
					all      : true,
					included : null
				},
				excludePosts : []
			}
		},
		internalOptions : {
			internal : {
				minimumLinkScanDate       : null,
				minimumSuggestionScanDate : null,
				dismissedAlerts           : {
					suggestions : false
				}
			}
		},
		overview : {
			totals : {
				crawledPosts   : 0,
				externalLinks  : 0,
				internalLinks  : 0,
				affiliateLinks : 0,
				orphanedPosts  : 0
			},
			linkingOpportunities : [],
			mostLinkedDomains    : []
		},
		domainsReport : {
			rows   : [],
			totals : {
				page  : 1,
				pages : 1,
				total : 1
			},
			innerPagination : {}
		},
		linksReport : {
			rows   : [],
			totals : {
				page  : 1,
				pages : 1,
				total : 1
			},
			prioritizedPosts : [],
			counts           : [
				{
					inboundInternal     : 0,
					outboundInternal    : 0,
					affiliate           : 0,
					external            : 0,
					suggestionsInbound  : 0,
					suggestionsOutbound : 0
				}
			]
		},
		postReport : {
			inboundInternal     : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
			outboundInternal    : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
			affiliate           : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
			external            : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
			suggestionsInbound  : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
			suggestionsOutbound : { rows: [], totals: { page: 1, pages: 1, total: 0 } }
		},
		suggestionsScan : {
			percent             : 0,
			showProcessingPopup : false
		},
		skipNextPostSettingsUpdate : false
	}),
	actions : {
		linkDelete ({ postId, linkId, linksReport, postReport }) {
			const slug = linksReport ? 'links-report-inner' : postReport ? 'post-report' : 'post-settings'
			return http.post(links.restUrl(`link-assistant/${slug}/links/delete`))
				.send({
					postId : postId,
					linkId : linkId
				})
				.then(response => {
					if (!linksReport && !postReport) {
						if (response.body) {
							const postEditorStore           = usePostEditorStore()
							const currentPost               = postEditorStore.currentPost
							currentPost.linkAssistant.links = response.body.links

							postEditorStore.updateState(currentPost)
						}
						return
					}

					this.getOverviewData()

					if (postReport) {
						return
					}

					if (linksReport) {
						this.setLinksReportCounts()
					}
				})
		},
		linksBulk ({ postId, action, linkType, linkIds, linksReport, postReport }) {
			const slug = linksReport ? 'links-report-inner' : postReport ? 'post-report' : 'post-settings'
			return http.post(links.restUrl(`link-assistant/${slug}/links/bulk`))
				.send({
					postId   : postId,
					action   : action,
					linkType : linkType,
					linkIds  : linkIds
				})
				.then(response => {
					if (!linksReport && !postReport) {
						if (response.body) {
							const postEditorStore           = usePostEditorStore()
							const currentPost               = postEditorStore.currentPost
							currentPost.linkAssistant.links = response.body.links

							postEditorStore.updateState(currentPost)
						}
						return
					}

					this.getOverviewData()

					if (postReport) {
						return
					}

					if (linksReport) {
						this.setLinksReportCounts()
					}
				})
		},
		linksRefresh ({ postId, linksReport, postReport }) {
			const slug = linksReport ? 'links-report-inner' : postReport ? 'post-report' : 'post-settings'
			return http.post(links.restUrl(`link-assistant/${slug}/refresh`))
				.send({
					postId : postId
				}).then(() => {
					this.linksReport.prioritizedPosts.push(postId)

					if (linksReport) {
						this.setLinksReportCounts()
					}

					this.pollSuggestionsScan()
					// Don't need to get the overview data here since the poll does it for us.
				}).catch((error) => {
					console.error('Couldn\'t prioritize the post for the next scan.', error)
				})
		},
		suggestionDismiss ({ postIndex, postId, suggestionId, linksReport, postReport }) {
			const postEditorStore = usePostEditorStore()
			const postSlug        = postReport ? 'post-report' : 'post-settings'
			const slug            = linksReport ? 'links-report-inner' : postSlug
			return http.post(links.restUrl(`link-assistant/${slug}/suggestions/dismiss`))
				.send({
					postId       : postId || postEditorStore.currentPost.id,
					suggestionId : suggestionId
				})
				.then(response => {
					if (!linksReport && !postReport) {
						if (response.body) {
							const currentPost               = postEditorStore.currentPost
							currentPost.linkAssistant.links = response.body.links

							postEditorStore.updateState(currentPost)
						}
						return
					}

					if (postReport) {
						return
					}

					if (linksReport) {
						if (postIndex) {
							this.linksReport.rows[postIndex].links = response.body.links
						}

						this.getOverviewData()
						this.setLinksReportCounts()
					}
				})
		},
		suggestionsBulk ({ postId, action, suggestionType, suggestionRows, linksReport, postReport }) {
			const slug = linksReport ? 'links-report-inner' : postReport ? 'post-report' : 'post-settings'
			return http.post(links.restUrl(`link-assistant/${slug}/suggestions/bulk`))
				.send({
					postId         : postId,
					action         : action,
					suggestionType : suggestionType,
					suggestionRows : suggestionRows
				})
				.then(response => {
					if (!linksReport && !postReport) {
						if (response.body) {
							const postEditorStore           = usePostEditorStore()
							const currentPost               = postEditorStore.currentPost
							currentPost.linkAssistant.links = response.body.links

							postEditorStore.updateState(currentPost)
						}
						return
					}

					this.getOverviewData()

					if (postReport) {
						return
					}

					if (linksReport) {
						this.setLinksReportCounts()
					}
				})
		},
		fetchLinksReport ({ orderBy, orderDir, limit, offset, searchTerm, filter, additionalFilters }) {
			return http.post(links.restUrl(`link-assistant/links-report/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm,
					additionalFilters
				})
				.then(response => {
					this.linksReport = response.body.linksReport

					this.getOverviewData()
					this.setLinksReportCounts()
				})
		},
		fetchLinksReportInner ({ filter, additionalFilters }) {
			return http.post(links.restUrl(`link-assistant/links-report-inner/${filter}`))
				.send({
					additionalFilters
				})
				.then(response => {
					if (additionalFilters.postIndex) {
						this.linksReport.rows[additionalFilters.postIndex].links = response.body.links
					}

					this.getOverviewData()
				})
		},
		fetchDomainsReport ({ orderBy, orderDir, limit, offset, searchTerm, filter, additionalFilters }) {
			return http.post(links.restUrl(`link-assistant/domains-report/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm,
					additionalFilters
				})
				.then(response => {
					this.domainsReport = response.body.domainsReport

					this.getOverviewData()
				})
		},
		fetchDomainsReportInner ({ orderBy, orderDir, offset, searchTerm, filter, additionalFilters }) {
			return http.post(links.restUrl(`link-assistant/domains-report-inner/${filter}`))
				.send({
					orderBy,
					orderDir,
					offset,
					searchTerm,
					additionalFilters
				})
				.then(response => {
					this.domainsReport.rows[additionalFilters.domainIndex][additionalFilters.domain] = response.body.posts

					this.getOverviewData()
				})
		},
		fetchPostReport ({ orderBy, orderDir, limit, offset, searchTerm, filter, additionalFilters = {} }) {
			return http.post(links.restUrl(`link-assistant/post-report/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm,
					additionalFilters
				})
				.then(response => {
					if (additionalFilters.type) {
						this.postReport[additionalFilters.type] = response.body.links
					}

					return response
				})
		},
		linksReportDeleteAll ({ postId }) {
			return http.delete(links.restUrl(`link-assistant/links-report/post/${postId}`))
		},
		domainsReportBulk ({ action, rowIndexes }) {
			const hostnames = []

			if (Array.isArray(rowIndexes)) {
				rowIndexes.forEach((index) => {
					hostnames.push(Object.keys(this.domainsReport.rows[index])[0])
				})
			} else {
				hostnames.push(Object.keys(this.domainsReport.rows[rowIndexes])[0])
			}

			return http.post(links.restUrl(`link-assistant/domains-report/bulk/${action}`))
				.send({
					hostnames
				}).then(() => {
					this.getOverviewData()
				})
		},
		domainsReportInnerBulk ({ offset, searchTerm, action, domainIndex, linkIndexes }) {
			const links = []
			if (Array.isArray(linkIndexes)) {
				linkIndexes.forEach((index) => {
					const domainKey  = Object.keys(this.domainsReport.rows[domainIndex])[0]
					const postObject = this.domainsReport.rows[domainIndex][domainKey][index]
					postObject.links.forEach((link) => {
						links.push(link)
					})
				})
			} else {
				const domainKey  = Object.keys(this.domainsReport.rows[domainIndex])[0]
				const postObject = this.domainsReport.rows[domainIndex][domainKey][linkIndexes]
				postObject.links.forEach((link) => {
					links.push(link)
				})
			}

			return http.post(links.restUrl(`link-assistant/domains-report-inner/bulk/${action}`))
				.send({
					searchTerm,
					links,
					offset
				}).then(() => {
					this.getOverviewData()
				})
		},
		domainsReportInnerLinkUpdate ({ domain, link }) {
			return http.put(links.restUrl('link-assistant/domains-report-inner/link'))
				.send({
					hostname : domain,
					link     : link
				}).then(() => {
					this.getOverviewData()
				})
		},
		domainsReportInnerLinkDelete ({ searchTerm, rows, postIndex, linkIndex, offset }) {
			const linkObject = rows[postIndex].links[linkIndex]
			return http.delete(links.restUrl('link-assistant/domains-report-inner/link'))
				.send({
					searchTerm,
					offset,
					link : linkObject
				}).then(() => {
					this.getOverviewData()
				})
		},
		postSettingsUpdate ({ postContent, skipNextRun }) {
			if (this.skipNextPostSettingsUpdate) {
				this.skipNextPostSettingsUpdate = false
				return
			}

			const postEditorStore = usePostEditorStore()

			window.aioseoBus.$emit('updatingLinks', true)
			return http.post(links.restUrl('link-assistant/post-settings/update'))
				.send({
					postId      : postEditorStore.currentPost.id,
					postContent : postContent
				})
				.then(response => {
					if (response.body) {
						const currentPost               = postEditorStore.currentPost
						currentPost.linkAssistant.links = response.body.links

						postEditorStore.updateState(currentPost)
					}

					if (skipNextRun) {
						this.skipNextPostSettingsUpdate = true
					}
				})
				.catch((error) => {
					console.error('Couldn\'t get updated Link Assistant data:', error)
				}).finally(() => {
					window.aioseoBus.$emit('updatingLinks', false)
				})
		},
		getMenuData () {
			return http.get(links.restUrl('link-assistant/data/menu'))
				.then(response => {
					if (response.body.data) {
						this.resetState(response.body.data)
					}
				})
		},
		getOverviewData () {
			return http.get(links.restUrl('link-assistant/data/overview'))
				.then(response => {
					if (response.body.data) {
						this.overview = response.body.data
					}
				})
		},
		getPostData () {
			const postEditorStore = usePostEditorStore()
			const postId          = postEditorStore.currentPost.id
			return http.get(links.restUrl(`link-assistant/data/post/${postId}`))
				.then(response => {
					if (response.body.data) {
						const currentPost               = postEditorStore.currentPost
						currentPost.linkAssistant = response.body.data.currentPost.linkAssistant

						postEditorStore.updateState(currentPost)

						this.resetState(response.body.data)
					}
				})
		},
		triggerScan () {
			return http.get(links.restUrl('link-assistant/data/trigger-scan'))
				.then(response => {
					if (response.body.data) {
						this.resetState(response.body.data)
					}
				})
		},
		pollSuggestionsScan () {
			return http.get(links.restUrl('link-assistant/data/suggestions-scan-percent'))
				.then(response => {
					if (response.body && 'percent' in response.body) {
						if (this.suggestionsScan.percent !== response.body.percent) {
							// If the percentage changed, refresh the overview data.
							this.getOverviewData()
						}

						this.suggestionsScan.percent = response.body.percent

						if (100 !== response.body.percent) {
							setTimeout(() => {
								this.pollSuggestionsScan()
							}, 10000)
						}
					}
				})
		},
		setLinksReportCounts () {
			const counts = []
			this.linksReport.rows.forEach((row, index) => {
				counts[index] = {
					inboundInternal     : row.links.inboundInternal.totals.total,
					outboundInternal    : row.links.outboundInternal.totals.total,
					affiliate           : row.links.affiliate.totals.total,
					external            : row.links.external.totals.total,
					suggestionsInbound  : row.links.suggestionsInbound.totals.total,
					suggestionsOutbound : row.links.suggestionsOutbound.totals.total
				}
			})

			this.linksReport.counts = counts
		},
		resetState (data) {
			if (data.options) {
				this.options = data.options
			}

			if (data.internalOptions) {
				this.internalOptions = data.internalOptions
			}

			if (data.overview) {
				this.overview = data.overview
			}

			if (data.domainsReport) {
				this.domainsReport = data.domainsReport
			}

			if (data.linksReport) {
				this.linksReport = data.linksReport
			}
		},
		toggleProcessingPopup () {
			this.suggestionsScan.showProcessingPopup = !this.suggestionsScan.showProcessingPopup
		},
		setDomainsReportInnerPaginatedPage ({ domain, page }) {
			const innerPagination   = this.domainsReport.innerPagination || {}
			innerPagination[domain] = page

			this.domainsReport.innerPagination = innerPagination
		},
		resetPostReportState () {
			this.postReport = {
				inboundInternal     : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
				outboundInternal    : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
				affiliate           : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
				external            : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
				suggestionsInbound  : { rows: [], totals: { page: 1, pages: 1, total: 0 } },
				suggestionsOutbound : { rows: [], totals: { page: 1, pages: 1, total: 0 } }
			}
		}
	}
})