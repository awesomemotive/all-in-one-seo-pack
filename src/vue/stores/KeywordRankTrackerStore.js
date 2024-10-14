import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useSearchStatisticsStore
} from '@/vue/stores'

export const useKeywordRankTrackerStore = defineStore('KeywordRankTrackerStore', {
	state : () => ({
		groups : {
			selected : [],
			all      : {
				rows : []
			},
			paginated : {
				rows   : [],
				totals : {
					page  : 1,
					pages : 0,
					total : 0
				},
				orderBy           : '',
				orderDir          : '',
				limit             : '',
				offset            : '',
				searchTerm        : '',
				filter            : 'all',
				additionalFilters : {}
			},
			count         : 0,
			statistics    : null,
			tableKeywords : {
				paginated : {
					rows   : [],
					totals : {
						page  : 1,
						pages : 0,
						total : 0
					},
					orderBy           : '',
					orderDir          : '',
					limit             : '',
					offset            : '',
					searchTerm        : '',
					filter            : 'all',
					additionalFilters : {}
				}
			}
		},
		keywords : {
			selected : [],
			all      : {
				rows : []
			},
			paginated : {
				rows   : [],
				totals : {
					page  : 1,
					pages : 0,
					total : 0
				},
				orderBy           : '',
				orderDir          : '',
				limit             : '',
				offset            : '',
				searchTerm        : '',
				filter            : 'all',
				additionalFilters : {}
			},
			count      : 0,
			statistics : null
		},
		siteFocusKeywords       : [],
		options                 : {},
		modalOpenAddKeywords    : false,
		modalOpenCreateGroup    : false,
		modalOpenAssignGroups   : false,
		modalOpenDeleteKeywords : false,
		modalOpenUpdateGroup    : false,
		modalOpenDeleteGroups   : false,
		modalOpenPostEdit       : false,
		isFetchingStatistics    : false,
		fetchKeywordsCallback   : null,
		keywordsLimit           : 0,
		errors                  : {
			crud : null
		},
		favoriteGroup : []
	}),
	getters : {
		range () {
			const searchStatisticsStore = useSearchStatisticsStore()
			return searchStatisticsStore.range
		}
	},
	actions : {
		toggleModal (args) {
			this.fetchKeywordsCallback = args?.fetchKeywordsCallback || null

			if ('modalOpenAddKeywords' === args.modal && args.open) {
				this.keywords.selected = []
			}

			if ('modalOpenAssignGroups' === args.modal && args.open) {
				this.keywords.selected = args?.keywords ?? []
			}

			if ('modalOpenDeleteKeywords' === args.modal && args.open) {
				this.keywords.selected = args?.keywords ?? []
			}

			if ('modalOpenCreateGroup' === args.modal) {
				this.errors.crud = null
			}

			if ('modalOpenUpdateGroup' === args.modal && args.open) {
				this.errors.crud = null
			}

			if ('modalOpenPostEdit' === args.modal && !args.open) {
				const searchStatisticsStore = useSearchStatisticsStore()

				searchStatisticsStore.shouldShowSampleReports = false
			}

			this[args.modal] = args.open
		},
		insertKeywords ({ keywords, groups }) {
			this.errors.crud = null

			return http.post(links.restUrl('search-statistics/keyword-rank-tracker/keywords'))
				.send({ keywords, groups })
				.then(response => response)
				.catch(error => {
					this.errors.crud = error?.response?.body?.error || null

					throw error
				})
		},
		abstractFetchKeywords (payload = {}) {
			return !this.fetchKeywordsCallback ? this.fetchKeywords(payload) : this.fetchKeywordsCallback(payload)
		},
		fetchKeywords (payload = {}) {
			this.keywords.paginated = { ...this.keywords.paginated, ...payload }

			return http.get(links.restUrl('search-statistics/keyword-rank-tracker/keywords'))
				.query({
					orderBy           : this.keywords.paginated.orderBy,
					orderDir          : this.keywords.paginated.orderDir,
					limit             : this.keywords.paginated.limit,
					offset            : this.keywords.paginated.offset,
					searchTerm        : this.keywords.paginated.searchTerm,
					filter            : this.keywords.paginated.filter,
					additionalFilters : this.keywords.paginated.additionalFilters,
					startDate         : this.range.start,
					endDate           : this.range.end,
					'names[]'         : payload?.names || [],
					'ids[]'           : payload?.ids || []
				})
				.then(response => {
					this.keywords.paginated.rows = response.body.paginated.rows
					this.keywords.paginated.totals = response.body.paginated.totals
					this.keywords.paginated.orderBy = response.body.paginated.orderBy
					this.keywords.paginated.orderDir = response.body.paginated.orderDir
					this.keywords.all.rows = response.body.all.rows
					this.keywords.count = response.body.count

					return response
				})
				.catch(error => {
					throw error
				})
		},
		updateKeyword ({ id, payload }) {
			return http.put(links.restUrl(`search-statistics/keyword-rank-tracker/keywords/${id}/`))
				.send(payload)
				.then(response => response)
				.catch(error => {
					throw error
				})
		},
		deleteKeywords (ids) {
			return http.delete(links.restUrl('search-statistics/keyword-rank-tracker/keywords'))
				.send({ ids })
				.then(response => {
					const update = [ this.keywords.paginated, this.groups.tableKeywords.paginated ]

					update.forEach((paginated) => {
						const newOffset = Math.max(0, paginated.offset - response.body.rowsAffected)

						// Round up or down the offset to the next multiple of `limit`.
						paginated.offset = paginated.totals.total - response.body.rowsAffected <= paginated.offset
							? Math.floor(newOffset / paginated.limit) * paginated.limit
							: Math.ceil(newOffset / paginated.limit) * paginated.limit
					})

					return response
				})
				.catch(error => {
					throw error
				})
		},
		insertGroups ({ groups, keywords }) {
			this.errors.crud = null

			return http.post(links.restUrl('search-statistics/keyword-rank-tracker/groups'))
				.send({ groups, keywords })
				.then(response => response)
				.catch(error => {
					this.errors.crud = error?.response?.body?.error || null

					throw error
				})
		},
		fetchGroups (payload = {}) {
			this.groups.paginated = { ...this.groups.paginated, ...payload }

			return http.get(links.restUrl('search-statistics/keyword-rank-tracker/groups'))
				.query({
					orderBy           : this.groups.paginated.orderBy,
					orderDir          : this.groups.paginated.orderDir,
					limit             : this.groups.paginated.limit,
					offset            : this.groups.paginated.offset,
					searchTerm        : this.groups.paginated.searchTerm,
					filter            : this.groups.paginated.filter,
					additionalFilters : this.groups.paginated.additionalFilters,
					startDate         : this.range.start,
					endDate           : this.range.end
				})
				.then(response => {
					this.groups.paginated.rows = response.body.paginated.rows
					this.groups.paginated.totals = response.body.paginated.totals
					this.groups.all.rows = response.body.all.rows
					this.groups.count = response.body.count

					return response
				})
				.catch(error => {
					throw error
				})
		},
		fetchGroupsTableKeywords (payload = {}) {
			this.groups.tableKeywords.paginated = { ...this.groups.tableKeywords.paginated, ...payload }

			return http.get(links.restUrl('search-statistics/keyword-rank-tracker/keywords'))
				.query({
					orderBy           : this.groups.tableKeywords.paginated.orderBy,
					orderDir          : this.groups.tableKeywords.paginated.orderDir,
					limit             : this.groups.tableKeywords.paginated.limit,
					offset            : this.groups.tableKeywords.paginated.offset,
					searchTerm        : this.groups.tableKeywords.paginated.searchTerm,
					filter            : this.groups.tableKeywords.paginated.filter,
					additionalFilters : this.groups.tableKeywords.paginated.additionalFilters,
					startDate         : this.range.start,
					endDate           : this.range.end,
					'ids[]'           : payload?.ids ? (payload.ids.length ? payload.ids : [ 0 ]) : []
				})
				.then(async response => {
					this.groups.tableKeywords.paginated.rows   = response.body.paginated.rows
					this.groups.tableKeywords.paginated.totals = response.body.paginated.totals

					if (payload?.updateKeywords) {
						await this.fetchKeywords()

						this.maybeFetchStatistics({ context: 'keywords' })
					}

					return response
				})
				.catch(error => {
					throw error
				})
		},
		updateGroup ({ id, payload }) {
			this.errors.crud = null

			return http.put(links.restUrl(`search-statistics/keyword-rank-tracker/groups/${id}/`))
				.send(payload)
				.then(response => response)
				.catch(error => {
					this.errors.crud = error?.response?.body?.error || null

					throw error
				})
		},
		deleteGroups (ids) {
			return http.delete(links.restUrl('search-statistics/keyword-rank-tracker/groups'))
				.send({ ids })
				.then(response => {
					const newOffset = Math.max(0, this.groups.paginated.offset - response.body.rowsAffected)

					// Round up or down the offset to the next multiple of `limit`.
					this.groups.paginated.offset = this.groups.paginated.totals.total - response.body.rowsAffected <= this.groups.paginated.offset
						? Math.floor(newOffset / this.groups.paginated.limit) * this.groups.paginated.limit
						: Math.ceil(newOffset / this.groups.paginated.limit) * this.groups.paginated.limit

					return response
				})
				.catch(error => {
					throw error
				})
		},
		updateRelationships ({ keywords, groups }) {
			return http.post(links.restUrl('search-statistics/keyword-rank-tracker/relationships'))
				.send({ keywords, groups })
				.then(response => response)
				.catch(error => {
					throw error
				})
		},
		maybeFetchStatistics ({ context }) {
			if (!this[context].paginated.rows.length && !this[context].all.rows.length) {
				return
			}

			this.isFetchingStatistics = true

			return http.post(links.restUrl(`search-statistics/keyword-rank-tracker/statistics/${context}`))
				.send({
					startDate : this.range.start,
					endDate   : this.range.end,
					paginated : this[context].paginated,
					all       : this[context].all
				})
				.then(response => {
					this[context].paginated.rows = response.body.paginated.rows
					this[context].all.rows = response.body.all.rows
					this[context].statistics = response.body.statistics
				})
				.catch(error => {
					throw error
				})
				.finally(() => {
					this.isFetchingStatistics = false
				})
		},
		maybeUpdateKeywords (payload = {}) {
			if (this.keywords.count) {
				return this.fetchKeywords(payload)
					.then(() => this.maybeFetchStatistics({ context: 'keywords' }))
			}
		},
		maybeUpdateGroups () {
			if (this.groups.count) {
				return this.fetchGroups()
					.then(() => this.maybeFetchStatistics({ context: 'groups' }))
			}
		},
		resetGroupsTableKeywords () {
			this.groups.tableKeywords = {
				paginated : {
					rows   : [],
					totals : {
						page  : 1,
						pages : 0,
						total : 0
					},
					orderBy           : '',
					orderDir          : '',
					limit             : '',
					offset            : '',
					searchTerm        : '',
					filter            : 'all',
					additionalFilters : {}
				}
			}
		}
	}
})