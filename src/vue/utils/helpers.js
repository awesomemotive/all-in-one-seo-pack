/**
 * Removes <script> tags from the given string.
 *
 * @param 	{string} string The input string that may contain <script> tags.
 * @returns {string} 		A new string with all <script> tags removed.
 */
export const removeScriptTag = string => {
	return string.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '')
}

/**
 * Converts a number of HTML entities into their special characters.
 * JS equivalent of wp_specialchars_decode().
 *
 * @param   {string}  string The text which is to be decoded.
 * @returns {string}         The decoded text without HTML entities.
 */
export const decodeSpecialChars = string => {
	// Not a string, or no entities to decode.
	if ('string' !== typeof string || !string.includes('&')) {
		return string
	}

	const charMap = {
		'&amp;'  : '&',
		'&lt;'   : '<',
		'&gt;'   : '>',
		'&quot;' : '"',
		'&nbsp;' : ' '
	}

	Object.entries(charMap).forEach(([ key, value ]) => {
		string = string.replaceAll(key, value)
	})

	return removeScriptTag(string)
}

export const observeElement = params => {
	const mutationCallback = (_list, obs) => {
		let el = params.id ? document.getElementById(params.id) : document.querySelector(params.selector)

		// If we can't find the element, also look for it inside the iFrame of the Full Site Editor (if present).
		// This is required for blocks like the Table of Contents which have a Vue UI inside the editor.
		const fullSiteEditor = document.querySelector('.edit-site-visual-editor__editor-canvas')
		if (!el && fullSiteEditor) {
			el = params.id
				? fullSiteEditor.contentWindow.document.getElementById(params.id)
				: fullSiteEditor.contentWindow.document.querySelector(params.selector)
		}

		if (el) {
			if (!params.loop) {
				obs.disconnect()
			}

			params.done(el)
		}
	}

	const observer = new MutationObserver(mutationCallback)

	observer.observe(params.parent || document, {
		subtree   : !!params.subtree || !params.parent,
		childList : true
	})
}

export const isUrl = url => {
	return /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/.test(url)
}

export const getAssetUrl = url => {
	return new URL(url, import.meta.url).href
}

export const cloneObject = obj => JSON.parse(JSON.stringify(obj))

/**
 * Compares two given version strings against one another using a given operator.
 *
 * @since 4.1.9
 *
 * @param   {string}  v1       The first version.
 * @param   {string}  v2       The second version.
 * @param   {string}  operator The operator.
 * @returns {boolean}          The result.
 */
export const versionCompare = (v1, v2, operator) => {
	let i,
		compare = 0

	const vm = {
		dev   : -6,
		alpha : -5,
		a     : -5,
		beta  : -4,
		b     : -4,
		RC    : -3,
		rc    : -3,
		'#'   : -2,
		p     : 1,
		pl    : 1
	}

	const _prepVersion = function (v) {
		v = ('' + v).replace(/[_\-+]/g, '.')
		v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
		return (!v.length ? [ -8 ] : v.split('.'))
	}

	const _numVersion = function (v) {
		return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
	}
	v1 = _prepVersion(v1)
	v2 = _prepVersion(v2)

	const x = Math.max(v1.length, v2.length)
	for (i = 0; i < x; i++) {
		if (v1[i] === v2[i]) {
			continue
		}
		v1[i] = _numVersion(v1[i])
		v2[i] = _numVersion(v2[i])
		if (v1[i] < v2[i]) {
			compare = -1
			break
		} else if (v1[i] > v2[i]) {
			compare = 1
			break
		}
	}
	if (!operator) {
		return compare
	}

	switch (operator) {
		case '>':
		case 'gt':
			return (0 < compare)
		case '>=':
		case 'ge':
			return (0 <= compare)
		case '<=':
		case 'le':
			return (0 >= compare)
		case '===':
		case '=':
		case 'eq':
			return (0 === compare)
		case '<>':
		case '!==':
		case 'ne':
			return (0 !== compare)
		case '':
		case '<':
		case 'lt':
			return (0 > compare)
		default:
			return null
	}
}

/**
 * Iterate through an array of block objects, recursing into innerBlocks and un-nesting them.
 *
 * @param   {Array} blocks Blocks to flatten.
 * @returns {Array}        Flattened array of block objects.
 */
export const flattenBlocks = (blocks) => {
	const result = []
	const stack = [ ...blocks ]

	while (stack.length) {
		const { ...block } = stack.shift()
		if (block.innerBlocks?.length) {
			stack.push(...block.innerBlocks)
		}
		block.innerBlocks = []
		result.push(block)
	}

	return result
}

/**
 * Returns difference of an elements between two arrays
 *
 * @param   {Array} array1 To look into.
 * @param   {Array} array2 Elements to look for.
 * @returns {Array}        Difference of an elements.
 */
export const arrayDiff = (array1, array2) => array1.filter(el => !array2.includes(el))

/**
 * Returns common array elements.
 *
 * @param   {Array} array1 To look into.
 * @param   {Array} array2 Elements to look for.
 * @returns {Array}        Common elements.
 */
export const arrayIntersection = (array1, array2) => array1.filter(el => array2.includes(el))

/**
 * Return an array containing the values of a specified column from an array of objects or an array of arrays.
 *
 * @param 	{Array|Object}  input     The input array or object.
 * @param 	{string|number} columnKey The key or index of the column to retrieve.
 * @returns {Array} 				  An array containing the values of the specified column.
 */
export const arrayColumn = (input, columnKey) => {
	if (Array.isArray(input)) {
		return input.map(function (item) {
			return item[columnKey]
		})
	} else if ('object' === typeof input) {
		const output = []
		for (const key in input) {
			output.push(input[key][columnKey])
		}

		return output
	}
}

/**
 * Returns a new array containing only the unique elements of the input array.
 *
 * @param 	{Array} array The input array to extract unique elements from.
 * @returns {Array}	   	  An array containing only the unique elements of the input array.
 */
export const arrayUnique = (array) => {
	return Array.from(new Set(array))
}