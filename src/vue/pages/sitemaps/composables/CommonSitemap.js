export const useCommonSitemap = (minLinks = 1, maxLinks = 50000) => {
	const validateLinksPerIndex = (value) => {
		// Handle empty, null, undefined values immediately
		if ('' === value || null === value || value === undefined) {
			return minLinks
		}

		const numValue = parseInt(value, 10)

		if (isNaN(numValue) || numValue < minLinks) {
			return minLinks
		}
		if (numValue > maxLinks) {
			return maxLinks
		}

		return numValue
	}

	return {
		validateLinksPerIndex
	}
}