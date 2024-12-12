import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

import {
	useDirtyOptionsStore,
	useNotificationsStore,
	usePostEditorStore
} from '@/vue/stores'

export const useRedirectsStore = defineStore('RedirectsStore', {
	state : () => ({
		options         : {},
		importers       : [],
		redirects       : [],
		rows            : [],
		logs            : [],
		logs404         : [],
		filters         : [],
		selectedFilters : {},
		manualUrls      : [],
		sort            : {
			logs    : 'last_accessed',
			logs404 : 'last_accessed'
		},
		sortDir : {
			logs    : 'desc',
			logs404 : 'desc'
		},
		totals : {
			main : {
				total : 0,
				pages : 0,
				page  : 1
			},
			total404 : {
				total : 0,
				pages : 0,
				page  : 1
			},
			logs : {
				total : 0,
				pages : 0,
				page  : 1
			}
		},
		server : {
			redirectTest : {
				testing : false,
				failed  : false
			},
			filePath : ''
		},
		lateRefresh : {
			redirects : false,
			logs      : false,
			logs404   : false
		},
		protectedPaths : [],
		path           : '',
		addNewRedirect : {
			sourceUrls           : [],
			targetUrl            : null,
			redirectType         : null,
			queryParam           : null,
			customRules          : [],
			showAdvancedSettings : false
		}
	}),
	actions : {
		bulk ({ action, rowIds }) {
			// First, let's remove any rows that are already set to this action.
			rowIds = rowIds.filter(rowId => {
				const row = this.rows.find(r => r.id === parseInt(rowId))
				if ('enable' === action && row.enabled) {
					return false
				}

				if ('disable' === action && !row.enabled) {
					return false
				}

				if ('reset-hits' === action && !row.hits) {
					return false
				}

				return true
			})

			if (!rowIds.length) {
				return Promise.resolve()
			}

			const httpAction = 'delete' === action ? 'delete' : 'post'
			const url        = 'delete' === action ? '' : `${action}/`
			return http[httpAction](links.restUrl(`redirects/bulk/${url}`))
				.send({
					rowIds : rowIds
				})
		},
		fetchRedirects ({ orderBy, orderDir, limit, offset, searchTerm, filter, additionalFilters }) {
			// Post|term metabox.
			const postEditorStore = usePostEditorStore()
			if ('post' === postEditorStore.currentPost?.context || 'term' === postEditorStore.currentPost?.context) {
				return this.getPostRedirects()
			}

			return http.post(links.restUrl(`redirects/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm,
					additionalFilters
				})
				.then(response => {
					this.filters     = response.body.filters
					this.rows        = response.body.rows
					this.totals.main = response.body.totals
				})
		},
		fetchLogs ({ slug, orderBy, orderDir, limit, offset, searchTerm, filter }) {
			return http.post(links.restUrl(`redirects/${slug}/${filter}`))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm
				})
				.then(response => {
					this['404' === slug ? 'logs404' : 'logs'] = response.body.rows
					this.totals['404' === slug ? 'total404' : 'logs'] = response.body.totals
				})
		},
		create (payload) {
			if ('404' === payload?.group) {
				this.setLateRefresh(true, 'redirects')
			}

			return http.post(links.restUrl('redirects'))
				.send(payload)
		},
		update ({ id, payload }) {
			return http.post(links.restUrl(`redirects/${id}/`))
				.send(payload)
				.then(response => {
					if (response.body.redirect?.id) {
						const redirectIndex = this.rows.findIndex(r => r.id === response.body.redirect.id)
						if (-1 !== redirectIndex) {
							this.rows[redirectIndex] = response.body.redirect
						}
					}
				})
		},
		delete (id) {
			return http.delete(links.restUrl(`redirects/${id}`))
		},
		test ({ id, payload }) {
			return http.post(links.restUrl(`redirects/${id}/test/`)).send(payload)
		},
		deleteLog ({ slug, ids }) {
			return http.delete(links.restUrl(`redirects/logs/${slug}`))
				.send({
					ids
				})
		},
		exportServerRedirects (server) {
			return http.get(links.restUrl(`redirects/export/${server}/`))
		},
		exportRedirects ({ groups, type }) {
			return http.post(links.restUrl(`redirects/export/${type}/`))
				.send({
					groups
				})
		},
		exportLogs (type) {
			return http.get(links.restUrl(`redirects/export-logs/${type}/`))
		},
		uploadFile ({ file, filename }) {
			return http.post(links.restUrl('redirects/import'))
				.attach('file', file, filename)
				.then(response => {
					this.filters     = response.body.filters
					this.rows        = response.body.rows
					this.totals.main = response.body.totals
				})
		},
		importPlugins (plugins) {
			return http.post(links.restUrl('redirects/import-plugins'))
				.send({
					plugins
				})
				.then(() => this.setLateRefresh({ value: true, type: 'redirects' }))
		},
		importCsvRedirects (redirects) {
			return http.post(links.restUrl('redirects/import-csv'))
				.send(redirects)
		},
		getPosts (payload) {
			return http.post(links.restUrl('redirects/posts'))
				.send(payload)
				.then(response => {
					if (!response.body.success) {
						throw new Error(response.body.message)
					}

					return response
				})
		},
		getRedirectOptions () {
			return http.get(links.restUrl('redirects/options'))
				.then(response => {
					if (response.body.options) {
						this.options   = response.body.options
						this.importers = response.body.importers

						const dirtyOptionsStore = useDirtyOptionsStore()
						dirtyOptionsStore.updateOriginalOptions('redirectOptions', this.options)
					}
				})
		},
		testServerRedirects () {
			const notificationsStore = useNotificationsStore()

			if (this.server.redirectTest.testing) {
				return
			}

			this.server.redirectTest.testing = true
			return http.get(links.restUrl('redirects/server/test')).then(response => {
				this.server.redirectTest.testing = false
				this.server.redirectTest.failed  = !response.body.success

				notificationsStore.updateNotifications(response.body.notifications)
			}).catch(() => {
				this.server.redirectTest.testing = false
				this.server.redirectTest.failed  = true
			})
		},
		getPostRedirects () {
			const postEditorStore = usePostEditorStore()
			return http.get(links.restUrl('redirects/' + postEditorStore.currentPost.context + '/' + postEditorStore.currentPost.id))
				.then(response => {
					this.rows                                 = response.body.rows
					postEditorStore.currentPost.permalinkPath = response.body.permalinkPath
					postEditorStore.currentPost.postStatus    = response.body.postStatus
				})
				.catch(() => {})
		},
		setLateRefresh ({ value = true, type = 'all' }) {
			type = 'all' === type ? [ 'redirects', 'logs', 'logs404' ] : [ type ]
			for (const t in type) {
				this.lateRefresh[type[t]] = value
			}
		},
		resetPageNumbers () {
			const { main, total404, logs } = this.totals
			main.page     = 1
			total404.page = 1
			logs.page     = 1

			const totals = {
				main,
				total404,
				logs
			}

			this.totals = totals
		}
	}
})