export const MODES = {
	STANDALONE : 'standalone',
	SYNCED     : 'synced'
}

/**
 * Check if the headings are nested.
 *
 * @param   {Array}   headings Heading objects.
 * @returns {boolean}          True if nested.
 */
export const areHeadingsNested = (headings) => {
	const foundNested = headings.find((headingObject) => {
		return 0 < headingObject.headings?.length
	})
	return !!(foundNested && Object.keys(foundNested).length)
}

/**
 * Iterate through an array of heading objects, recursing into nested headings and un-nesting them.
 *
 * @param   {Array}  headings Heading objects to flatten.
 * @param   {number} end 	  The end index of the specified portion of the array.
 * @returns {Array}           Flattened array of heading objects.
 */
export const flattenHeadings = (headings, end = 0) => {
	if (!areHeadingsNested([ ...headings ])) {
		return headings
	}

	const flattenedHeadings = []
	const stack             = [ ...headings ]

	while (stack.length) {
		if (end && flattenedHeadings.length === end) {
			break
		}

		const { ...heading } = stack.shift()
		if (heading.headings?.length) {
			stack.unshift(...heading.headings)
		}
		heading.headings = []
		flattenedHeadings.push(heading)
	}

	return flattenedHeadings
}

/**
 * Assign editedOrder & editedLevel values to each heading based on the index and nesting depth.
 *
 * @param   {Array} headings Heading objects to order and nest.
 * @param   {number} parentIndex The index of the parent heading.
 * @returns {Array}            Ordered array of heading objects.
 */
export const orderHeadings = (headings, parentIndex = 0) => {
	// Flatten the headings array so we can loop through it.
	headings = flattenHeadings([ ...headings ])

	// Each heading gets an editedOrder based it's index.
	headings.forEach((heading, index) => {
		const idx = parentIndex + index + 1
		heading.editedOrder = idx
	})
	return headings.sort((a, b) => a.editedOrder - b.editedOrder)
}