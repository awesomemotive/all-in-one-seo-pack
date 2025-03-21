import { defineStore } from 'pinia'
import dateFormat from '@/vue/utils/dateFormat'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import { useKeywordRankTrackerStore, useOptionsStore } from '@/vue/stores'

export const useSearchStatisticsStore = defineStore('SearchStatisticsStore', {
	state : () => ({
		isConnected         : false,
		hasInitialized      : false,
		latestAvailableDate : null,
		unverifiedSite      : false,
		authedSite          : null,
		quotaExceeded       : {},
		rolling             : null,
		sitemapsWithErrors  : [],
		range               : {
			start        : null,
			end          : null,
			compareStart : null,
			compareEnd   : null
		},
		loading : {
			seoStatistics           : false,
			keywords                : false,
			contentRankings         : false,
			postDetailSeoStatistics : false,
			postDetailKeywords      : false
		},
		data : {
			seoStatistics : {
				statistics : {
					clicks      : 0,
					impressions : 0,
					ctr         : 0,
					position    : 0,
					keywords    : 0,
					difference  : {
						clicks      : 0,
						impressions : 0,
						ctr         : 0,
						position    : 0
					}
				},
				intervals : [],
				pages     : {
					topPages : {
						rows : []
					},
					topWinning : {
						rows : []
					},
					topLosing : {
						rows : []
					},
					paginated : {
						rows   : [],
						totals : {
							page  : 0,
							pages : 0,
							total : 0
						}
					}
				},
				timelineMarkers : {}
			},
			keywords : {
				distribution          : [],
				distributionIntervals : [],
				topWinning            : [],
				topLosing             : [],
				topKeywords           : [],
				paginated             : {
					rows   : [],
					totals : {
						page  : 0,
						pages : 0,
						total : 0
					}
				}
			},
			contentRankings : {
				paginated : {
					rows   : [],
					totals : {
						page  : 0,
						pages : 0,
						total : 0
					}
				}
			},
			postDetail : {
				postId        : 0,
				seoStatistics : {
					intervals  : [],
					statistics : {
						clicks      : 0,
						impressions : 0,
						ctr         : 0,
						position    : 0,
						keywords    : 0,
						difference  : {
							clicks      : 0,
							impressions : 0,
							ctr         : 0,
							position    : 0
						}
					},
					timelineMarkers : {}
				},
				keywords : {
					paginated : {
						rows   : [],
						totals : {
							page  : 0,
							pages : 0,
							total : 0
						}
					}
				}
			}
		},
		shouldShowSampleReports : false
	}),
	actions : {
		getAuthUrl ({ returnTo }) {
			return http.get(links.restUrl('search-statistics/url/auth'))
				.query({ returnTo })
				.then(response => {
					return response.body.url
				})
		},
		getReauthUrl ({ returnTo }) {
			return http.get(links.restUrl('search-statistics/url/reauth'))
				.query({ returnTo })
				.then(response => {
					return response.body.url
				})
		},
		deleteAuth () {
			return http.delete(links.restUrl('search-statistics/auth'))
				.send()
				.then(response => {
					if (true === response.body.success) {
						this.isConnected = false
					}

					return response.body
				})
		},
		async setDateRange (payload, refetch = true) {
			const keywordRankTrackerStore = useKeywordRankTrackerStore()

			this.range   = {
				start : dateFormat(payload.dateRange[0], 'Y-m-d'),
				end   : dateFormat(payload.dateRange[1], 'Y-m-d')
			}
			this.rolling = payload.rolling

			if (refetch) {
				const promises = [
					() => this.updateSeoStatistics({ filter: 'all', searchTerm: '' }),
					() => this.updateKeywords({ filter: 'all', searchTerm: '' }),
					() => this.updateContentRankings({ searchTerm: '' }),
					() => keywordRankTrackerStore.maybeUpdateKeywords(),
					() => keywordRankTrackerStore.maybeUpdateGroups()
				]

				await Promise.all(promises.map(task => task()))
			}
		},
		loadInitialData () {
			if (this.hasInitialized) {
				return
			}

			this.hasInitialized = true

			if (!this.data.seoStatistics?.statistics?.impressions) {
				this.updateSeoStatistics({
					filter     : 'all',
					searchTerm : ''
				})
			}

			if (!this.data.keywords?.paginated?.rows?.length) {
				this.updateKeywords({
					filter     : 'all',
					searchTerm : ''
				})
			}

			if (!this.data.contentRankings?.paginated?.totals?.total) {
				this.updateContentRankings({
					searchTerm : ''
				})
			}
		},
		updateSeoStatistics (options) {
			this.loading.seoStatistics = true

			return http.get(links.restUrl('search-statistics/stats/seo-statistics'))
				.query({
					startDate : this.range.start,
					endDate   : this.range.end,
					rolling   : this.rolling,
					...options
				})
				.then(response => {
					if (response.body.success) {
						this.range              = response.body.range
						this.data.seoStatistics = response.body.data
					}
				})
				.finally(() => {
					this.loading.seoStatistics = false
				})
		},
		updateKeywords (options) {
			this.loading.keywords = true

			return http.get(links.restUrl('search-statistics/stats/keywords'))
				.query({
					startDate : this.range.start,
					endDate   : this.range.end,
					rolling   : this.rolling,
					...options
				})
				.then(response => {
					if (response.body.success) {
						this.range         = response.body.range
						this.data.keywords = response.body.data
					}
				})
				.finally(() => {
					this.loading.keywords = false
				})
		},
		updateContentRankings (options) {
			this.loading.contentRankings = true

			return http.get(links.restUrl('search-statistics/stats/content-rankings'))
				.query({
					endDate : this.latestAvailableDate,
					...options
				})
				.then(response => {
					if (response.body.success) {
						this.data.contentRankings = response.body.data
					}
				})
				.finally(() => {
					this.loading.contentRankings = false
				})
		},
		getInspectionResult (paths) {
			return http.get(links.restUrl('search-statistics/inspection-result'))
				.query({
					'paths[]' : paths
				})
				.then(response => {
					this.quotaExceeded.urlInspection = response.body.quotaExceeded

					return response.body.data
				})
		},
		getPagesByKeywords (payload = {}) {
			const keywords = payload.keywords

			return http.post(links.restUrl('search-statistics/stats/keywords/posts'))
				.send({
					limit     : payload.limit,
					offset    : payload.offset,
					startDate : this.range.start,
					endDate   : this.range.end,
					keywords  : keywords
				})
				.then(response => {
					return response.body?.data?.[keywords[0]].paginated || {}
				})
				.catch(error => {
					throw error
				})
		},
		getPostDetail (postId) {
			return http.get(links.restUrl('search-statistics/post-detail'))
				.query({
					startDate : this.range.start,
					endDate   : this.range.end,
					postId    : postId
				})
				.then(response => response)
		},
		getPostDetailFocusKeywordTrend ({ focusKeyword }) {
			return http.get(links.restUrl('search-statistics/post-detail/focus-keyword'))
				.query({
					startDate    : this.range.start,
					endDate      : this.range.end,
					postId       : this.data.postDetail.postId,
					focusKeyword : focusKeyword
				})
				.then(response => response)
		},
		updatePostDetailSeoStatistics (options) {
			this.loading.postDetailSeoStatistics = true

			return http.get(links.restUrl('search-statistics/post-detail/seo-statistics'))
				.query({
					startDate : this.range.start,
					endDate   : this.range.end,
					rolling   : this.rolling,
					postId    : this.data.postDetail.postId,
					...options
				})
				.then(response => {
					if (response.body.success) {
						this.data.postDetail.seoStatistics = response.body.data
					}
				})
				.finally(() => {
					this.loading.postDetailSeoStatistics = false
				})
		},
		updatePostDetailKeywords (options) {
			this.loading.postDetailKeywords = true

			return http.get(links.restUrl('search-statistics/post-detail/keywords'))
				.query({
					startDate : this.range.start,
					endDate   : this.range.end,
					rolling   : this.rolling,
					postId    : this.data.postDetail.postId,
					...options
				})
				.then(response => {
					if (response.body.success) {
						this.data.postDetail.keywords = response.body.data
					}
				})
				.finally(() => {
					this.loading.postDetailKeywords = false
				})
		},
		getPageSpeed (payload) {
			return http.get(links.restUrl('search-statistics/pagespeed'))
				.query(payload)
				.then(response => response)
		},
		deleteSitemap ({ sitemap }) {
			return http.post(links.restUrl('search-statistics/sitemap/delete'))
				.send({ sitemap })
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'internal', 'searchStatistics' ], key: 'sitemap', value: response.body.data.internalOptions })
					this.sitemapsWithErrors = response.body.data.sitemapsWithErrors
				})
		},
		ignoreSitemap ({ sitemap }) {
			return http.post(links.restUrl('search-statistics/sitemap/ignore'))
				.send({ sitemap })
				.then(response => {
					const optionsStore = useOptionsStore()
					optionsStore.updateOption('internalOptions', { groups: [ 'internal', 'searchStatistics' ], key: 'sitemap', value: response.body.data.internalOptions })
					this.sitemapsWithErrors = response.body.data.sitemapsWithErrors
				})
		},
		updateSeoRevision (revision) {
			const markers = this.data.postDetail.seoStatistics.timelineMarkers

			Object.keys(markers).forEach(date => {
				markers[date].forEach((rev, revIndex) => {
					if ('aioseoRevision' === rev.type) {
						markers[date][revIndex].revisions = markers[date][revIndex].revisions.map(item => {
							if (item.id === revision[0].id) {
								return revision[0]
							}

							return item
						})
					}
				})
			})

			this.data.postDetail.seoStatistics.timelineMarkers = markers
		},
		deleteSeoRevision (revision) {
			const markers = this.data.postDetail.seoStatistics.timelineMarkers

			Object.keys(markers).forEach(date => {
				markers[date].forEach((rev, revIndex) => {
					if ('aioseoRevision' === rev.type) {
						markers[date][revIndex].revisions = markers[date][revIndex].revisions.filter(item => {
							return item.id !== revision.id
						})
					}
				})
			})

			this.data.postDetail.seoStatistics.timelineMarkers = markers
		},
		showSampleReports () {
			this.shouldShowSampleReports = true
		}
	}
})