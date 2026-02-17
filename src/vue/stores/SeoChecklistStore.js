import { defineStore } from 'pinia'
import http from '@/vue/utils/http'
import links from '@/vue/utils/links'

export const useSeoChecklistStore = defineStore('SeoChecklistStore', {
	state : () => ({
		items   : [],
		rows    : [],
		loading : false,
		error   : null,

		// Table filters
		filters        : [],
		expandedItems  : {}, // { itemName: true/false }
		dismissedItems : [], // Array of dismissed item names

		// Pagination
		totals : {
			total : 0,
			pages : 1,
			page  : 1
		},

		// Overall counts (unfiltered) for progress bar
		overallCompletedCount : 0,
		overallTotalCount     : 0,

		// Polling counts (used to detect changes regardless of current filter)
		totalCompleted  : 0,
		totalIncomplete : 0,

		// Action loading states
		actionLoading : {} // { itemName: true/false }
	}),

	getters : {
		/**
		 * All completed items
		 *
		 * @param {Object} state - The state object
		 * @returns {Array} The completed items
		 */
		completedItems : (state) => {
			return state.rows.filter(item => item.completed)
		},

		/**
		 * All pending (incomplete) items
		 *
		 * @param {Object} state - The state object
		 * @returns {Array} The pending items
		 */
		pendingItems : (state) => {
			return state.rows.filter(item => !item.completed && !state.dismissedItems.includes(item.name))
		},

		/**
		 * Progress percentage (0-100)
		 *
		 * @param {Object} state - The state object
		 * @returns {number} The progress percentage
		 */
		progressPercentage : (state) => {
			if (0 === state.overallTotalCount) {
				return 0
			}

			return Math.round((state.overallCompletedCount / state.overallTotalCount) * 100)
		},

		/**
		 * Count of completed items (overall, not filtered)
		 *
		 * @param {Object} state - The state object
		 * @returns {number} The completed count
		 */
		completedCount : (state) => {
			return state.overallCompletedCount
		},

		/**
		 * Total count (overall, not filtered)
		 *
		 * @param {Object} state - The state object
		 * @returns {number} The total count
		 */
		totalCount : (state) => {
			return state.overallTotalCount
		},

		/**
		 * Remaining tasks message
		 *
		 * @returns {string} The remaining message
		 */
		remainingMessage () {
			const remaining = this.totalCount - this.completedCount
			if (0 === remaining) {
				return 'SEO checklist complete!'
			}

			return `${remaining} task${1 === remaining ? '' : 's'} away from SEO checklist complete.`
		},

		/**
		 * Items grouped by priority
		 *
		 * @param {Object} state - The state object
		 * @returns {Object} The items grouped by priority
		 */
		itemsByPriority : (state) => {
			return {
				high     : state.rows.filter(item => 'high' === item.priority),
				medium   : state.rows.filter(item => 'medium' === item.priority),
				optional : state.rows.filter(item => 'optional' === item.priority)
			}
		}
	},

	actions : {
		/**
		 * Fetch checklist items with filtering, sorting, and pagination.
		 *
		 * @param {Object} params - The fetch parameters.
		 * @param {string} params.orderBy - The column to order by.
		 * @param {string} params.orderDir - The order direction (asc/desc).
		 * @param {number} params.limit - The number of items per page.
		 * @param {number} params.offset - The offset for pagination.
		 * @param {string} params.searchTerm - The search term.
		 * @param {string} params.filter - The filter slug.
		 * @param {Object} params.additionalFilters - Additional filters (e.g., priority).
		 * @returns {Promise} The response.
		 */
		fetchChecks ({ orderBy, orderDir, limit, offset, searchTerm, filter, additionalFilters }) {
			return http.post(links.restUrl('seo-checklist/fetch'))
				.send({
					orderBy,
					orderDir,
					limit,
					offset,
					searchTerm,
					filter,
					additionalFilters
				})
				.then(response => {
					this.filters = response.body.filters
					this.rows    = response.body.rows
					this.totals  = response.body.totals

					// Store overall counts for progress bar (unaffected by filters)
					if (response.body.completedCount !== undefined) {
						this.overallCompletedCount = response.body.completedCount
					}
					if (response.body.totalCount !== undefined) {
						this.overallTotalCount = response.body.totalCount
					}

					// Update polling counts to stay in sync with fetched data.
					if (response.body.completedCount !== undefined && response.body.totalCount !== undefined) {
						this.totalCompleted  = response.body.completedCount
						this.totalIncomplete = response.body.totalCount - response.body.completedCount
					}
				})
		},

		/**
		 * Mark an item as complete
		 *
		 * @param {string} itemName - The name of the item to complete
		 * @returns {Promise} The response
		 * @throws {Error} If the response is not successful
		 */
		async completeItem (itemName) {
			this.actionLoading[itemName] = true

			try {
				const response = await http.post(links.restUrl('seo-checklist/complete'))
					.send({ check: itemName })

				return response.body.success
			} catch (error) {
				this.error = 'Failed to complete item'
				console.error('Failed to complete item:', error)
				return false
			} finally {
				delete this.actionLoading[itemName]
			}
		},

		/**
		 * Execute an action for an item
		 *
		 * @param {string} itemName - The name of the item to execute the action for
		 * @param {string} actionName - The name of the action to execute
		 * @returns {Promise} The response
		 * @throws {Error} If the response is not successful
		 */
		async doAction (itemName, actionName) {
			this.actionLoading[itemName] = true

			try {
				const response = await http.post(links.restUrl('seo-checklist/action'))
					.send({
						check    : itemName,
						callback : actionName
					})

				return response.body.success
			} catch (error) {
				this.error = `Failed to execute action: ${actionName}`
				console.error('Failed to execute action:', error)
				return false
			} finally {
				delete this.actionLoading[itemName]
			}
		},

		/**
		 * Dismiss an item (marks it as completed via API)
		 *
		 * @param {string} itemName - The name of the item to dismiss
		 * @returns {Promise} The response
		 */
		async dismissItem (itemName) {
			this.actionLoading[itemName] = true

			try {
				const response = await http.post(links.restUrl('seo-checklist/complete'))
					.send({ check: itemName })

				if (response.body.success) {
					if (!this.dismissedItems.includes(itemName)) {
						this.dismissedItems.push(itemName)
					}
					// Remove from rows
					this.rows = this.rows.filter(row => row.name !== itemName)
					return true
				}
				return false
			} catch (error) {
				this.error = 'Failed to dismiss item'
				console.error('Failed to dismiss item:', error)
				return false
			} finally {
				delete this.actionLoading[itemName]
			}
		},

		/**
		 * Uncomplete an item (removes completed status via API)
		 *
		 * @param {string} itemName - The name of the item to uncomplete
		 * @returns {Promise} The response
		 */
		async uncompleteItem (itemName) {
			this.actionLoading[itemName] = true

			try {
				const response = await http.post(links.restUrl('seo-checklist/uncomplete'))
					.send({ check: itemName })

				if (response.body.success) {
					// Find and update the item
					const item = this.rows.find(i => i.name === itemName)
					if (item) {
						item.completed = false
					}
					return true
				}
				return false
			} catch (error) {
				this.error = 'Failed to uncomplete item'
				console.error('Failed to uncomplete item:', error)
				return false
			} finally {
				delete this.actionLoading[itemName]
			}
		},

		/**
		 * Bulk complete items (dismiss multiple items at once)
		 *
		 * @param {Array} itemNames - Array of item names to complete
		 * @returns {Promise} The response
		 */
		async bulkCompleteItems (itemNames) {
			if (!itemNames.length) {
				return false
			}

			try {
				const response = await http.post(links.restUrl('seo-checklist/bulk-complete'))
					.send({ checks: itemNames })

				return response.body.success
			} catch (error) {
				this.error = 'Failed to complete items'
				console.error('Failed to bulk complete items:', error)
				return false
			}
		},

		/**
		 * Bulk uncomplete items
		 *
		 * @param {Array} itemNames - Array of item names to uncomplete
		 * @returns {Promise} The response
		 */
		async bulkUncompleteItems (itemNames) {
			if (!itemNames.length) {
				return false
			}

			try {
				const response = await http.post(links.restUrl('seo-checklist/bulk-uncomplete'))
					.send({ checks: itemNames })

				return response.body.success
			} catch (error) {
				this.error = 'Failed to uncomplete items'
				console.error('Failed to bulk uncomplete items:', error)
				return false
			}
		},

		/**
		 * Restore a dismissed item
		 *
		 * @param {string} itemName - The name of the item to restore
		 * @returns {void}
		 */
		restoreItem (itemName) {
			const index = this.dismissedItems.indexOf(itemName)
			if (-1 < index) {
				this.dismissedItems.splice(index, 1)
			}
		},

		/**
		 * Toggle item expansion
		 *
		 * @param {string} itemName - The name of the item to toggle the expansion for
		 * @returns {void}
		 */
		toggleExpanded (itemName) {
			this.expandedItems[itemName] = !this.expandedItems[itemName]
		},

		/**
		 * Fetch the counts for polling (lightweight endpoint).
		 *
		 * @returns {Promise<Object>} Object with totalCompleted and totalIncomplete.
		 */
		async fetchCompletedChecks () {
			try {
				const response = await http.get(links.restUrl('seo-checklist/completed'))

				if (response.body.success) {
					return {
						totalCompleted  : response.body.totalCompleted || 0,
						totalIncomplete : response.body.totalIncomplete || 0
					}
				}
				return null
			} catch (error) {
				console.error('Failed to fetch completed checks:', error)
				return null
			}
		},

		/**
		 * Check if the counts from API differ from stored counts.
		 *
		 * @param {Object} apiCounts - Object with totalCompleted and totalIncomplete from API.
		 * @returns {boolean} True if the counts have changed.
		 */
		hasCompletedChecksChanged (apiCounts) {
			if (!apiCounts) {
				return false
			}

			return this.totalCompleted !== apiCounts.totalCompleted ||
				this.totalIncomplete !== apiCounts.totalIncomplete
		},

		/**
		 * Update the stored polling counts.
		 *
		 * @param {Object} counts - Object with totalCompleted and totalIncomplete.
		 * @returns {void}
		 */
		updatePollingCounts (counts) {
			if (counts) {
				this.totalCompleted  = counts.totalCompleted
				this.totalIncomplete = counts.totalIncomplete
			}
		}
	}
})