const STORE_NAME = 'aioseo/tru-seo-highlights'

const DEFAULT_STATE = {
	highlights : {}
}

/**
 * Converts a camelCase analyzer name to a kebab-case slug
 * suitable for use in format type names.
 *
 * @param {string} analyzer The analyzer name (e.g. 'keyphraseInIntroduction').
 * @returns {string} The kebab-case slug (e.g. 'keyphrase-in-introduction').
 */
const analyzerToSlug = (analyzer) => {
	return analyzer.replace(/([A-Z])/g, '-$1').toLowerCase()
}

/**
 * Builds the format type name for a given analyzer.
 *
 * @param {string} analyzer The analyzer name.
 * @returns {string} The format type name (e.g. 'aioseo/highlight-keyphrase-in-introduction').
 */
export const getFormatName = (analyzer) => {
	return `aioseo/highlight-${analyzerToSlug(analyzer)}`
}

// The class prefix of every painted mark, shared with the DOM walks that skip our decoration.
export const HIGHLIGHT_CLASS_PREFIX = 'aioseo-highlight-'

/**
 * Builds the CSS class name for a given analyzer.
 *
 * @param {string} analyzer The analyzer name.
 * @returns {string} The class name (e.g. 'aioseo-highlight-keyphrase-in-introduction').
 */
export const getFormatClassName = (analyzer) => {
	return `${HIGHLIGHT_CLASS_PREFIX}${analyzerToSlug(analyzer)}`
}

const actions = {
	addHighlights (blockClientId, richTextIdentifier, analyzer, ranges) {
		return {
			type : 'ADD_HIGHLIGHTS',
			blockClientId,
			richTextIdentifier,
			analyzer,
			ranges
		}
	},
	clearAll () {
		return {
			type : 'CLEAR_ALL'
		}
	}
}

const reducer = (state = DEFAULT_STATE, action) => {
	switch (action.type) {
		case 'ADD_HIGHLIGHTS': {
			const key = `${action.blockClientId}:${action.richTextIdentifier}:${action.analyzer}`
			return {
				...state,
				highlights : {
					...state.highlights,
					[key] : action.ranges
				}
			}
		}
		case 'CLEAR_ALL': {
			return { highlights: {} }
		}
		default:
			return state
	}
}

const EMPTY_ARRAY = []

const selectors = {
	getHighlightsForRichText (state, blockClientId, richTextIdentifier, analyzer) {
		const key = `${blockClientId}:${richTextIdentifier}:${analyzer}`

		return state.highlights[key] || EMPTY_ARRAY
	}
}

export const registerHighlightStore = () => {
	if (!window?.wp?.data) {
		return
	}

	if (window.wp.data.select(STORE_NAME)) {
		return
	}

	const storeConfig = {
		reducer,
		actions,
		selectors
	}

	if ('function' === typeof window.wp.data.createReduxStore) {
		const store = window.wp.data.createReduxStore(STORE_NAME, storeConfig)
		window.wp.data.register(store)
	} else {
		window.wp.data.registerStore(STORE_NAME, storeConfig)
	}
}

export { STORE_NAME }