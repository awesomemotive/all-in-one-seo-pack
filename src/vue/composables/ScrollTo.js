export const useScrollTo = () => {
	const scrollTo = (elementId, optionalOptions = {}) => {
		// Create a reference to the element.
		const element = document.getElementById(elementId)

		// If the element doesn't exist, return early.
		if (!element) {
			return
		}

		// Extract the mods object from optionalOptions and then remove it from optionalOptions.
		const mods = optionalOptions.mods || {}
		delete optionalOptions.mods

		if (mods?.scrollMarginClass) {
			element.classList.add(mods.scrollMarginClass)
		}

		// Merge the optionalOptions object with the options object.
		const options = {
			behavior : 'smooth',
			...optionalOptions
		}

		// Scroll to the element.
		element.scrollIntoView(options)

		// Remove the scrollMarginClass from the element after the timeout.
		setTimeout(() => {
			if (mods?.scrollMarginClass) {
				element.classList.remove(mods.scrollMarginClass)
			}
		}, mods?.timeout || 500)
	}

	return {
		scrollTo
	}
}