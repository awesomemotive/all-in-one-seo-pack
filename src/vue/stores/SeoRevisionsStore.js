import { defineStore } from 'pinia'

import { allowed } from '@/vue/utils/AIOSEO_VERSION'
import { arrayColumn } from '@/vue/utils/helpers'

import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	usePostEditorStore
} from '@/vue/stores'

export const useSeoRevisionsStore = defineStore('SeoRevisionsStore', {
	state : () => ({
		items       : [],
		currentUser : {
			avatar       : null,
			display_name : null
		},
		itemFrom              : {},
		itemTo                : {},
		itemContext           : '',
		noteMaxLength         : 0,
		itemsLimit            : 0,
		itemsTotalCount       : 0,
		seoRevisionsDiff      : [],
		seoRevisionsDiffCache : {},
		modalOpenSidebar      : false,
		error                 : null,
		capability            : 'aioseo_page_seo_revisions_settings'
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
			return http.delete(links.restUrl(`seo-revisions/${id}`))
				.then((response) => {
					this.itemsTotalCount = response.body.itemsTotalCount

					return response
				})
		},
		fetch (payload = {}, append = false) {
			const postEditorStore = usePostEditorStore()
			return http.get(links.restUrl('seo-revisions/' + postEditorStore.currentPost.context + '/' + postEditorStore.currentPost.id))
				.query(payload)
				.then(response => {
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
			return http.post(links.restUrl(`seo-revisions/${id}/`))
				.send(payload)
				.then((response) => {
					return response
				})
		},
		restore ({ id }) {
			return http.post(links.restUrl(`seo-revisions/restore/${id}/`))
				.then((response) => {
					return response
				})
		},
		fetchDiff (payload) {
			const cacheKey = payload.fromId + '_' + payload.toId

			return new Promise((resolve) => {
				if (this.seoRevisionsDiffCache[cacheKey]) {
					this.seoRevisionsDiff = this.seoRevisionsDiffCache[cacheKey]

					resolve({})
				} else {
					http.get(links.restUrl('seo-revisions/diff'))
						.query(payload)
						.then((response) => {
							this.seoRevisionsDiff = response.body.diff
							this.seoRevisionsDiffCache[cacheKey] = response.body.diff

							resolve(response)
						})
				}
			})
		}
	}
})