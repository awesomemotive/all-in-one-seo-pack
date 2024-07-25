import {
	useSettingsStore
} from '@/vue/stores'

export const WpTable = {
	data () {
		return {
			resultsPerPage : 20,
			orderBy        : null,
			orderDir       : null,
			searchTerm     : '',
			pageNumber     : 1,
			filter         : 'all',
			wpTableKey     : 0,
			wpTableLoading : false
		}
	},
	methods : {
		refreshTable () {
			this.wpTableLoading = true

			return this.processFetchTableData()
				.then(() => {
					this.wpTableLoading = false
					this.$refs.table.editRow(null)
				})
		},
		processAdditionalFilters ({ filters }) {
			this.wpTableLoading = true
			this.selectedFilters = filters

			this.processFetchTableData(filters)
				.then(() => (this.wpTableLoading = false))
		},
		processSearch (searchTerm) {
			if ('object' === typeof searchTerm) {
				searchTerm = searchTerm.target.value
			}
			this.pageNumber     = 1
			this.searchTerm     = searchTerm
			this.wpTableLoading = true

			this.processFetchTableData()
				.then(() => (this.wpTableLoading = false))
		},
		processPagination (pageNumber) {
			this.pageNumber     = pageNumber
			this.wpTableLoading = true

			this.processFetchTableData()
				.then(() => (this.wpTableLoading = false))
		},
		processFilterTable (filter) {
			this.filter         = filter.slug
			this.searchTerm     = ''
			this.pageNumber     = 1
			this.wpTableLoading = true
			this.resetSelectedFilters()

			this.processFetchTableData()
				.then(() => (this.wpTableLoading = false))
		},
		processChangeItemsPerPage (newNumber) {
			this.pageNumber     = 1
			this.resultsPerPage = newNumber
			this.wpTableLoading = true

			const settingsStore = useSettingsStore()
			settingsStore.changeItemsPerPage({
				slug  : this.changeItemsPerPageSlug,
				value : newNumber
			})
				.then(() => this.processFetchTableData()
					.then(() => this.$scrollTo(`#${this.tableId}`, { offset: -110 }))
				)
				.then(() => (this.wpTableLoading = false))
		},
		processSort (column, event) {
			event.target.blur()
			this.orderBy        = column.slug
			this.orderDir       = this.orderBy !== column.slug ? column.sortDir : ('asc' === column.sortDir ? 'desc' : 'asc')
			this.wpTableLoading = true

			this.processFetchTableData()
				.then(() => (this.wpTableLoading = false))
		},
		processFetchTableData (additionalFilters) {
			return this.fetchData({
				slug              : this.slug || null,
				orderBy           : this.orderBy,
				orderDir          : this.orderDir,
				limit             : this.resultsPerPage,
				offset            : 1 === this.pageNumber ? 0 : (this.pageNumber - 1) * this.resultsPerPage,
				searchTerm        : this.searchTerm,
				filter            : this.filter,
				additionalFilters : additionalFilters || this.selectedFilters
			})
		},
		// Wrapper function that can be extended/utilized.
		resetSelectedFilters () {}
	},
	created () {
		const settingsStore = useSettingsStore()
		this.resultsPerPage = settingsStore.settings.tablePagination[this.changeItemsPerPageSlug] || this.resultsPerPage
	}
}