function sortHelper (a, b, sortColumn, sortDir) {
	if ('number' === typeof a[sortColumn]) {
		if ('asc' === sortDir) {
			return a[sortColumn] - b[sortColumn]
		}
		if ('desc' === sortDir) {
			return b[sortColumn] - a[sortColumn]
		}
	}

	if ('string' === typeof a[sortColumn]) {
		if ('asc' === sortDir) {
			return a[sortColumn].localeCompare(b[sortColumn], 'en', { sensitivity: 'base' })
		}
		if ('desc' === sortDir) {
			return b[sortColumn].localeCompare(a[sortColumn], 'en', { sensitivity: 'base' })
		}
	}

	return 0
}

export {
	sortHelper
}