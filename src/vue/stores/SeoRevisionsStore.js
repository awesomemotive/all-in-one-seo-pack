import { defineStore } from 'pinia'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { arrayColumn } from '@/vue/utils/helpers'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	usePostEditorStore
} from '@/vue/stores'

// Identifies the newest list refresh, so responses that have been superseded can be discarded.
let refreshToken   = 0,
	pendingRefresh = Promise.resolve()

const waitForPendingRefresh = () => {
	const current = pendingRefresh

	return current.then(() => (current === pendingRefresh ? undefined : waitForPendingRefresh()))
}

export const useSeoRevisionsStore = defineStore('SeoRevisionsStore', {
	state : () => ({
		items       : [],
		currentUser : {
			avatar       : null,
			display_name : null
		},
		itemFrom                  : {},
		itemTo                    : {},
		itemContext               : '',
		noteMaxLength             : 0,
		itemsLimit                : 0,
		itemsTotalCount           : 0,
		seoRevisionsDiff          : [],
		seoRevisionsDiffCache     : {},
		// Smart tag ID => the value it resolves to for the compared object.
		seoRevisionsDiffTagValues : {},
		modalOpenSidebar          : false,
		error                     : null,
		capability                : 'aioseo_page_seo_revisions_settings'
	}),
	getters : {
		hasPermission : (state) => allowed(state.capability),
		hasDiff () {
			if (0 < this.seoRevisionsDiff.length) {
				return 0 < arrayColumn(this.seoRevisionsDiff, 'diff').filter(v => v).length
			}

			return true
		}
	},
	actions : {
		updateState (value = null) {
			for (const key in (value || {})) {
				this[key] = value[key]
			}
		},
		delete (id) {
			if (!this.hasPermission) {
				return Promise.resolve()
			}

			return http.delete(links.restUrl(`seo-revisions/${id}`))
				.then((response) => {
					this.itemsTotalCount = response.body.itemsTotalCount

					return response
				})
		},
		fetch (payload = {}, append = false) {
			if (!this.hasPermission) {
				return Promise.resolve()
			}

			// A refresh still in flight is about to replace the list, so an offset measured now would
			// skip rows. Appends wait for it and measure the offset against the list that replaced it.
			if (append) {
				return waitForPendingRefresh().then(() => this.request({ ...payload, offset: this.items.length }, true))
			}

			const request = this.request(payload, false)
			pendingRefresh = request.catch(() => {})

			return request
		},
		request (payload, append) {
			// Saving twice in a row leaves two refreshes in flight, and they can resolve out of order —
			// the earlier one landing last would overwrite the newer list. Each refresh takes a token
			// and drops its response if a later one has started since; an append is dropped likewise.
			const token = append ? refreshToken : ++refreshToken

			const postEditorStore = usePostEditorStore()
			return http.get(links.restUrl('seo-revisions/' + postEditorStore.currentPost.context + '/' + postEditorStore.currentPost.id))
				.query(payload)
				.then(response => {
					if (token !== refreshToken) {
						return response
					}

					this.itemsTotalCount = response.body.itemsTotalCount
					if (append) {
						for (const item of response.body.items) {
							this.items.push(item)
						}
					} else {
						this.items = response.body.items
					}

					return response
				})
		},
		update ({ id, payload }) {
			if (!this.hasPermission) {
				return Promise.resolve()
			}

			return http.post(links.restUrl(`seo-revisions/${id}/`))
				.send(payload)
				.then((response) => {
					return response
				})
		},
		restore ({ id }) {
			if (!this.hasPermission) {
				return Promise.resolve()
			}

			return http.post(links.restUrl(`seo-revisions/restore/${id}/`))
				.then((response) => {
					return response
				})
		},
		fetchDiff (payload) {
			if (!this.hasPermission) {
				return Promise.resolve()
			}

			const cacheKey = payload.fromId + '_' + payload.toId

			return new Promise((resolve) => {
				if (this.seoRevisionsDiffCache[cacheKey]) {
					this.seoRevisionsDiff          = this.seoRevisionsDiffCache[cacheKey].diff
					this.seoRevisionsDiffTagValues = this.seoRevisionsDiffCache[cacheKey].tagValues

					resolve({})
				} else {
					http.get(links.restUrl('seo-revisions/diff'))
						.query(payload)
						.then((response) => {
							this.seoRevisionsDiff          = response.body.diff
							this.seoRevisionsDiffTagValues = response.body.tagValues || {}

							this.seoRevisionsDiffCache[cacheKey] = {
								diff      : this.seoRevisionsDiff,
								tagValues : this.seoRevisionsDiffTagValues
							}

							resolve(response)
						})
				}
			})
		}
	}
})