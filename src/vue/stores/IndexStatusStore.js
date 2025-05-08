import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useSearchStatisticsStore
} from '@/vue/stores'

export const useIndexStatusStore = defineStore('IndexStatusStore', {
	state : () => ({
		objects : {
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
				additionalFilters : {}
			},
			fetching : false
		},
		overview : null,
		options  : {}
	}),
	actions : {
		fetchIndexStatusObjects (payload = {}) {
			this.objects.fetching = true

			const searchStatisticsStore = useSearchStatisticsStore()

			this.objects.paginated = { ...this.objects.paginated, ...payload }

			return http.get(links.restUrl('search-statistics/index-status/objects'))
				.query({
					orderBy           : this.objects.paginated.orderBy,
					orderDir          : this.objects.paginated.orderDir,
					limit             : this.objects.paginated.limit,
					offset            : this.objects.paginated.offset,
					searchTerm        : this.objects.paginated.searchTerm,
					additionalFilters : this.objects.paginated.additionalFilters,
					endDate           : searchStatisticsStore.latestAvailableDate
				})
				.then(response => {
					this.objects.paginated.rows   = response.body.paginated.rows
					this.objects.paginated.totals = response.body.paginated.totals

					return response
				})
				.catch(error => {
					throw error
				})
				.finally(() => {
					this.objects.fetching = false
				})
		},
		fetchIndexStatusOverview () {
			return http.get(links.restUrl('search-statistics/index-status/overview'))
				.query()
				.then(response => {
					this.overview = response.body.data

					return response
				})
				.catch(error => {
					throw error
				})
		}
	}
})