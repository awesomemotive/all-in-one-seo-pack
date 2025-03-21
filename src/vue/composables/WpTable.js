import { ref, computed, onMounted } from 'vue'

import {
	useSettingsStore
} from '@/vue/stores'

import { useScrollTo } from '@/vue/composables/ScrollTo'

export const useWpTable = (params = {}) => {
	const {
		changeItemsPerPageSlug,
		fetchData,
		orderBy: defaultOrderBy,
		orderDir: defaultOrderDir,
		pageNumber: defaultPageNumber,
		resetSelectedFilters = () => {},
		resultsPerPage: defaultResultsPerPage,
		selectedFilters: defaultSelectedFilters,
		slug,
		tableId,
		tableRef,
		wpTableLoading: defaultWpTableLoading
	} = params

	const filter          = ref('all')
	const orderBy         = defaultOrderBy || ref(null)
	const orderDir        = defaultOrderDir || ref(null)
	const pageNumber      = defaultPageNumber || ref(1)
	const resultsPerPage  = ref(defaultResultsPerPage || 20)
	const searchTerm      = ref(null)
	const selectedFilters = defaultSelectedFilters || ref(null)
	const wpTableKey      = ref(0)
	const wpTableLoading  = defaultWpTableLoading || ref(false)
	const settingsStore   = useSettingsStore()
	const scrollTo        = useScrollTo().scrollTo

	const refreshTable = () => {
		wpTableLoading.value = true

		return processFetchTableData()
			.then(() => {
				wpTableLoading.value = false
				tableRef?.editRow(null)
			})
	}

	const processAdditionalFilters = ({ filters, term = null, number = null }) => {
		wpTableLoading.value  = true
		selectedFilters.value = filters
		searchTerm.value      = term ?? searchTerm.value
		pageNumber.value      = number ?? pageNumber.value

		processFetchTableData(filters)
			.then(() => (wpTableLoading.value = false))
	}

	const processSearch = (term) => {
		if ('object' === typeof term) {
			term = term?.target?.value
		}
		pageNumber.value     = 1
		searchTerm.value     = term
		wpTableLoading.value = true

		processFetchTableData()
			.then(() => (wpTableLoading.value = false))
	}

	const processPagination = (number) => {
		pageNumber.value     = number
		wpTableLoading.value = true

		processFetchTableData()
			.then(() => (wpTableLoading.value = false))
	}

	const processFilterTable = (tableFilter) => {
		filter.value         = tableFilter.slug
		searchTerm.value     = null
		pageNumber.value     = 1
		wpTableLoading.value = true

		resetSelectedFilters()

		processFetchTableData()
			.then(() => (wpTableLoading.value = false))
	}

	const processChangeItemsPerPage = (newNumber) => {
		pageNumber.value     = 1
		resultsPerPage.value = newNumber
		wpTableLoading.value = true

		settingsStore.changeItemsPerPage({
			slug  : changeItemsPerPageSlug,
			value : newNumber
		})
			.then(() => processFetchTableData()
				.then(() => scrollTo(tableId))
			)
			.then(() => (wpTableLoading.value = false))
	}

	const processSort = (column, event) => {
		event.target.blur()
		orderBy.value        = column.slug
		orderDir.value       = orderBy.value !== column.slug ? column.sortDir : ('asc' === column.sortDir ? 'desc' : 'asc')
		wpTableLoading.value = true

		return processFetchTableData()
			.then(() => (wpTableLoading.value = false))
	}

	const offset = computed(() => {
		return 1 === pageNumber.value ? 0 : (pageNumber.value - 1) * resultsPerPage.value
	})

	const processFetchTableData = (additionalFilters = selectedFilters.value) => {
		return fetchData({
			slug,
			orderBy    : orderBy.value,
			orderDir   : orderDir.value,
			limit      : resultsPerPage.value,
			offset     : offset.value,
			searchTerm : searchTerm.value,
			filter     : filter.value,
			additionalFilters
		})
	}

	onMounted(() => {
		if (!changeItemsPerPageSlug) {
			return
		}

		resultsPerPage.value = settingsStore.settings.tablePagination[changeItemsPerPageSlug] || resultsPerPage.value
	})

	return {
		filter,
		offset,
		orderBy,
		orderDir,
		pageNumber,
		processAdditionalFilters,
		processChangeItemsPerPage,
		processFetchTableData,
		processFilterTable,
		processPagination,
		processSearch,
		processSort,
		refreshTable,
		resultsPerPage,
		searchTerm,
		selectedFilters,
		wpTableKey,
		wpTableLoading
	}
}