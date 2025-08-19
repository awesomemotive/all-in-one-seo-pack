import BasicIssues from '@/vue/classes/SeoAnalysis/BasicIssues'
import AdvancedIssues from '@/vue/classes/SeoAnalysis/AdvancedIssues'
import PerformanceIssues from '@/vue/classes/SeoAnalysis/PerformanceIssues'

class SeoAnalysis {
	get basic () {
		return BasicIssues.strings
	}

	get advanced () {
		return AdvancedIssues.strings
	}

	get performance () {
		return PerformanceIssues.strings
	}

	get strings () {
		return {
			...this.basic,
			...this.advanced,
			...this.performance
		}
	}

	getFilteredResults (resultsSet, section) {
		if ('object' !== typeof resultsSet || !resultsSet) {
			return {}
		}

		const results = {}

		// Drop all items/results that do not have a matching title.
		// If a item has no title, it means it is deprecated/not supported by this version of the plugin.
		Object.keys(resultsSet).forEach(group => {
			results[group] = resultsSet[group].filter(result => {
				if (!this.strings[result.code]?.title || result.status !== section) {
					return false
				}

				return true
			})
		})

		Object.keys(results).forEach(group => {
			if (0 === results[group].length) {
				delete results[group]
			}
		})

		return results
	}

	getResultsCount (resultsSet, section) {
		const results = this.getFilteredResults(resultsSet, section)

		return results
			? Object.keys(results).reduce((acc, group) => {
				return acc + results[group].length
			}, 0)
			: 0
	}
}

export default new SeoAnalysis()