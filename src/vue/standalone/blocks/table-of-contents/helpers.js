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
 * @param   {Array}   headings Heading objects to order and nest.
 * @param   {boolean} flatten  Do the headings need to be unnested first.
 * @returns {Array}            Ordered array of heading objects.
 */
export const orderHeadings = (headings) => {
	// Returns an object where each key is a headingId.
	// The value of each is an array of it's direct descendant headingIds.
	const mapHeadingLevel = (headingsList, rootId = 'root') => {
		const headingLevelMap = { [rootId]: [] }

		headingsList.forEach((heading) => {
			headingLevelMap[rootId].push(heading.blockClientId)
			Object.assign(headingLevelMap, mapHeadingLevel(heading.headings, heading.blockClientId))
		})

		return headingLevelMap
	}

	// We need to get the map of heading levels before flattening.
	const headingMap = mapHeadingLevel([ ...headings ])

	// Flatten the headings array so we can loop through it.
	headings = flattenHeadings([ ...headings ])

	// Loop through the heading map and give each heading an editedLevel based on context.
	Object.entries(headingMap).forEach(([ parentId, childrenIds ]) => {
		if (!childrenIds.length) {
			return
		}

		if ('root' === parentId) {
			// Top level headings get an editedLevel of 1.
			childrenIds.map(headingId => {
				const headingIndex = headings.findIndex(heading => heading.blockClientId === headingId)
				headings[headingIndex].editedLevel = 1
				return headingId
			})
		} else {
			// Descendant headings get the editedLevel of their parent + 1.
			childrenIds.map(headingId => {
				const parentIndex = headings.findIndex(heading => heading.blockClientId === parentId)
				const headingIndex = headings.findIndex(heading => heading.blockClientId === headingId)
				headings[headingIndex].editedLevel = headings[parentIndex].editedLevel + 1
				return headingId
			})
		}
	})

	// Each heading gets an editedOrder based it's index.
	headings.forEach((heading, index) => {
		heading.editedOrder = index + 1
	})
	return headings.sort((a, b) => a.editedOrder - b.editedOrder)
}

/**
 * Sort and nest array of heading objects.
 *
 * @param   {Array}   headings Array of heading objects.
 * @param   {boolean} flatten  Do the headings need to be unnested first.
 * @returns {Array}            Formatted array of heading objects.
 */
export const formatHeadingList = (headings) => {
	const list = []
	let nextLevelHeaders = [],
		lastLevel = -1

	if (0 === headings.length) {
		return []
	}

	headings = flattenHeadings([ ...headings ])

	const buildSubTree = () => {
		if (0 < nextLevelHeaders.length) {
			list[list.length - 1].headings = formatHeadingList(nextLevelHeaders)
		}
	}

	headings.forEach(heading => {
		heading = { ...heading }

		const currentLevel = heading.editedLevel || heading.level
		if (-1 !== lastLevel && lastLevel < currentLevel) {
			nextLevelHeaders.push(heading)
			return
		}

		buildSubTree()

		lastLevel = currentLevel
		list.push(heading)
		nextLevelHeaders = []
	})

	buildSubTree()

	return list
}