// Render-only analysis fields. The server drops them before persisting (Post::sanitizeTruseo()
// and Post::sanitizeAdditionalKeywords()), and on shortcode-heavy content they can push the
// request past a WAF body limit, so they must not be sent.
const EPHEMERAL_KEYS = [ 'title', 'text', 'highlightSentences', 'marks' ]

/**
 * Maps over an array or a keyed object without changing which of the two it is.
 *
 * @param   {Object|Array}   collection The collection to map over.
 * @param   {Function}       callback   Receives each value, returns its replacement.
 * @returns {Object|Array}              The collection, or a mapped copy when any value changed.
 */
const mapCollection = (collection, callback) => {
	if (!collection || 'object' !== typeof collection) {
		return collection
	}

	if (Array.isArray(collection)) {
		const mapped = collection.map(value => callback(value))

		return mapped.some((value, index) => value !== collection[index]) ? mapped : collection
	}

	const mapped = {}
	let changed  = false

	Object.keys(collection).forEach(key => {
		mapped[key] = callback(collection[key])

		if (mapped[key] !== collection[key]) {
			changed = true
		}
	})

	return changed ? mapped : collection
}

/**
 * Returns an analysis entry without its render-only fields.
 *
 * @param   {Object} entry An analysis entry.
 * @returns {Object}       The entry, or a pruned copy when it carried any render-only field.
 */
const pruneEntry = (entry) => {
	if (!entry || 'object' !== typeof entry || Array.isArray(entry)) {
		return entry
	}

	if (!EPHEMERAL_KEYS.some(key => key in entry)) {
		return entry
	}

	const pruned = { ...entry }
	EPHEMERAL_KEYS.forEach(key => delete pruned[key])

	return pruned
}

/**
 * Returns a collection of analysis entries with every entry pruned.
 *
 * @param   {Object|Array} entries Analysis entries, keyed by analyzer or keyword ID.
 * @returns {Object|Array}         The entries, or a pruned copy when any entry changed.
 */
const pruneEntries = (entries) => mapCollection(entries, pruneEntry)

/**
 * Prunes the `truseo` payload. Mirrors Post::sanitizeTruseo().
 *
 * @param   {Object} truseo The truseo payload.
 * @returns {Object}        The payload, or a pruned copy when anything changed.
 */
const pruneTruseo = (truseo) => {
	if (!truseo || 'object' !== typeof truseo) {
		return truseo
	}

	const general = pruneEntries(truseo.general)
	const items   = pruneEntries(truseo.focus_keyword?.items)

	if (general === truseo.general && items === truseo.focus_keyword?.items) {
		return truseo
	}

	const pruned = { ...truseo }

	if (general !== truseo.general) {
		pruned.general = general
	}

	if (truseo.focus_keyword && items !== truseo.focus_keyword.items) {
		pruned.focus_keyword = { ...truseo.focus_keyword, items }
	}

	return pruned
}

/**
 * Prunes the `additional_keywords` payload. Mirrors Post::sanitizeAdditionalKeywords().
 *
 * @param   {Object|Array} additionalKeywords The additional keywords payload.
 * @returns {Object|Array}                    The payload, or a pruned copy when anything changed.
 */
const pruneAdditionalKeywords = (additionalKeywords) => mapCollection(additionalKeywords, (keyword) => {
	const items = pruneEntries(keyword?.items)

	return keyword && items !== keyword.items
		? { ...keyword, items }
		: keyword
})

/**
 * Returns a post payload with the render-only analysis data removed.
 *
 * NOTE: The store keeps the full analysis, since the highlighter still reads its marks.
 *
 * @param   {Object} currentPost The post payload.
 * @returns {Object}             The payload, or a pruned copy when anything changed.
 */
export const withoutEphemeralAnalysisData = (currentPost) => {
	if (!currentPost || 'object' !== typeof currentPost) {
		return currentPost
	}

	const truseo             = pruneTruseo(currentPost.truseo)
	const additionalKeywords = pruneAdditionalKeywords(currentPost.additional_keywords)

	if (truseo === currentPost.truseo && additionalKeywords === currentPost.additional_keywords) {
		return currentPost
	}

	const pruned = { ...currentPost }

	if (truseo !== currentPost.truseo) {
		pruned.truseo = truseo
	}

	if (additionalKeywords !== currentPost.additional_keywords) {
		pruned.additional_keywords = additionalKeywords
	}

	return pruned
}