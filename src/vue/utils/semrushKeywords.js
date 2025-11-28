import { COUNTRY_LIST, SEMRUSH_DATABASE } from '@/vue/plugins/constants'

/**
 * Transforms the list of countries to match Semrush database format.
 *
 * @returns {Array} Array of country objects formatted for Semrush.
 */
export const getSemrushDatabaseOptions = () => {
	const list = JSON.parse(JSON.stringify(COUNTRY_LIST))

	return list
		.map(country => {
			if ('GB' === country.value) {
				country.value = 'UK'
				country.label = 'United Kingdom'
			}

			if ('KR' === country.value) {
				country.label = 'South Korea'
			}

			return country
		})
		.filter(country => SEMRUSH_DATABASE.includes(country.value.toLowerCase()))
		.map(country => {
			country.label = country.label + ' - ' + country.value.toUpperCase()
			return country
		})
}

/**
 * Transforms Semrush trend data (comma-separated string) to chart points.
 *
 * @param   {string} trend Comma-separated trend values.
 * @returns {Array}        Array of {x, y} coordinate objects for charting.
 */
export const transformTrendDataToChartPoints = (trend) => {
	if (!trend || 'string' !== typeof trend) {
		return []
	}

	const trendArray = trend.split(',')
	return trendArray.map((value, index) => ({
		x : index,
		y : parseFloat(value)
	}))
}

/**
 * Fetches keywords from Semrush store.
 *
 * @param {Object}   options                Options object.
 * @param {Object}   options.semrushStore   Semrush Pinia store instance.
 * @param {string}   options.database       Database/country code to search.
 * @param {string}   options.focusKeyphrase Focus keyphrase to get related keywords for.
 * @param {Function} options.onStart        Callback when fetch starts.
 * @param {Function} options.onSuccess      Callback when fetch succeeds.
 * @param {Function} options.onError        Callback when fetch fails.
 * @returns {Promise}                       Promise that resolves when the keywords are fetched.
 */
export const fetchSemrushKeywords = async ({ semrushStore, database, focusKeyphrase, onStart, onSuccess, onError }) => {
	if (!focusKeyphrase) {
		return
	}

	if (onStart) {
		onStart()
	}

	try {
		await semrushStore.getKeyphrases(database)
		if (onSuccess) {
			onSuccess()
		}
	} catch (error) {
		if (onError) {
			onError(error)
		}
	}
}