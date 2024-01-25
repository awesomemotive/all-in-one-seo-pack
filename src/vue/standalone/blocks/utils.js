import htm from 'htm'

/**
 * Use standard JavaScript Tagged Template Literals in place of WPs JSX
 *
 * https://github.com/developit/htm
 */
export const html = htm.bind(window.wp.element.createElement)

/**
 * More accurately check the type of a JavaScript object
 *
 * @param   {Object} obj The object
 * @returns {string}     The object type
 */
export const trueTypeOf = (obj) => {
	return Object.prototype.toString.call(obj).slice(8, -1).toLowerCase()
}

/**
 * Check if two objects or arrays are equal
 *
 * @param   {*} item1 The first item
 * @param   {*} item2 The second item
 * @returns {boolean} Returns true if they're equal in value
 */
export const isEqual = (item1, item2) => {
	function areArraysEqual () {
		if (item1.length !== item2.length) {
			return false
		}
		for (let i = 0; i < item1.length; i++) {
			if (!isEqual(item1[i], item2[i])) {
				return false
			}
		}
		return true
	}

	function areObjectsEqual () {
		if (Object.keys(item1).length !== Object.keys(item2).length) {
			return false
		}
		for (const key in item1) {
			if (Object.prototype.hasOwnProperty.call(item1, key)) {
				if (!isEqual(item1[key], item2[key])) {
					return false
				}
			}
		}
		return true
	}

	function areFunctionsEqual () {
		return item1.toString() === item2.toString()
	}

	function arePrimativesEqual () {
		return item1 === item2
	}

	const type = trueTypeOf(item1)
	if (type !== trueTypeOf(item2)) {
		return false
	}

	if ('array' === type) return areArraysEqual()
	if ('object' === type) return areObjectsEqual()
	if ('function' === type) return areFunctionsEqual()
	return arePrimativesEqual()
}

/**
 * Create an immutable clone of data (an array, object, map, set, etc.)
 *
 * @param   {*} obj The data object to copy
 * @returns {*}     The clone of the array or object
 */
export const deepCopy = (obj) => {
	// Copy properties from the original object to the clone
	const copyProps = (clone) => {
		for (const key in obj) {
			if (Object.prototype.hasOwnProperty.call(obj, key)) {
				clone[key] = deepCopy(obj[key])
			}
		}
	}
	const cloneObj = () => {
		const clone = {}
		copyProps(clone)
		return clone
	}
	const cloneArr = () => {
		return obj.map(function (item) {
			return deepCopy(item)
		})
	}
	const cloneMap = () => {
		const clone = new Map()
		for (const [ key, val ] of obj) {
			clone.set(key, deepCopy(val))
		}
		return clone
	}
	const cloneSet = () => {
		const clone = new Set()
		for (const item of obj) {
			clone.add(deepCopy(item))
		}
		return clone
	}
	const cloneFunction = () => {
		const clone = obj.bind(this)
		copyProps(clone)
		return clone
	}
	const type = trueTypeOf(obj)

	// Return a clone based on the object type
	if ('object' === type) return cloneObj()
	if ('array' === type) return cloneArr()
	if ('map' === type) return cloneMap()
	if ('set' === type) return cloneSet()
	if ('function' === type) return cloneFunction()
	return obj
}

/**
 * Strips scripts and on* attributes from HTML.
 *
 * @param {string} htmlString HTML to sanitize.
 * @param {boolean} stripTags Should HTML be removed and only text returned.
 * @returns {string} The sanitized HTML.
 */
export const cleanHtml = (htmlString, stripTags = false) => {
	const { body } = document.implementation.createHTMLDocument('')
	body.innerHTML = htmlString
	const elements = body.getElementsByTagName('*')
	let elementIndex = elements.length

	while (elementIndex--) {
		const element = elements[elementIndex]

		if ('SCRIPT' === element.tagName) {
			element.parentNode.removeChild(element)
		} else {
			let attributeIndex = element.attributes.length

			while (attributeIndex--) {
				const { name: key } = element.attributes[attributeIndex]

				if (key.startsWith('on')) {
					element.removeAttribute(key)
				}
			}
		}
	}

	return stripTags ? body.textContent.trim() : body.innerHTML
}

export const generateUniqueSchemaBlockId = () => {
	return 'aioseo-' + new Date().getTime().toString(36)
}

/**
 * Unmount app and prevent Vue from creating multiple instances of the same app.
 *
 * @param {string} clientId A block ID.
 * @param {Array} apps Array of Vue apps.
 * @returns {void}
 */
export const maybeDeleteBlockVueApp = (clientId, apps) => {
	const findAppIndex = apps.findIndex(a => a.clientId === clientId)
	if (-1 !== findAppIndex) {
		apps[findAppIndex].app.unmount()
		apps.splice(findAppIndex, 1)
	}
}