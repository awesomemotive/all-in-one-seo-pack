import htm from 'htm'
import { useTagsStore } from '@/vue/stores'
import { customFieldValue } from '@/vue/plugins/tru-seo/components/customFields'
import { canLoadBlocks } from '@/vue/utils/context'
import { decodeHTMLEntities } from '@/vue/utils/helpers'

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
 * @param {boolean} trim Should the string be trimmed.
 * @returns {string} The sanitized HTML.
 */
export const cleanHtml = (htmlString, stripTags = false, trim = true) => {
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

	const result = stripTags ? body.textContent : body.innerHTML

	return trim ? result.trim() : result
}

export const generateUniqueSchemaBlockId = () => {
	return 'aioseo-' + new Date().getTime().toString(36)
}

export const parseTags = (string, keepFormatting = false) => {
	const tagsStore = useTagsStore()

	if (!string) {
		return string
	}

	if (!tagsStore.tags) {
		return string
	}

	tagsStore.tags.forEach(tag => {
		if ('custom_field' === tag.id) {
			const customFieldRegex   = new RegExp(`#${tag.id}-([a-zA-Z0-9_-]+)`, 'g')

			string = string.replace(customFieldRegex, (match, fieldName) => {
				return customFieldValue(fieldName)
			})
			return
		}

		if ('tax_name' === tag.id) {
			const taxNameRegex = new RegExp(`#${tag.id}-([a-zA-Z0-9_-]+)`, 'g')
			string             = string.replace(taxNameRegex, `[${tag.name} - $1]`)
			return
		}

		// Pattern explained: Exact match of tag, not followed by any additional letter, number or underscore.
		// This allows us to have tags like: #post_link and #post_link_alt
		// and it will always replace the correct one.
		const regex = new RegExp(`#${tag.id}(?![a-zA-Z0-9_])`, 'g')

		const matches = string.match(regex)
		const value   = (tagsStore.liveTags[tag.id] || tag.value)
		if (matches) {
			string = string.replace(regex, '%|%' + value)
		}
	})

	// Since we added a delimiter, let's remove all of that now.
	string = string.replace(/%\|%/g, '').replace(/<(?:.|\n)*?>/gm, ' ')

	if (!keepFormatting) {
		string = string.replace(/\s/g, ' ')
	}

	// Make sure to return the processed `string` at the end.
	return decodeHTMLEntities(decodeHTMLEntities(string))
}

export const formatCurrency = (amount, currency) => {
	// Ensure the correct number of decimal places
	amount = parseFloat(amount || '').toFixed(2)

	// Return if no amount or if the amount is not a number.
	if (isNaN(amount)) {
		return ''
	}

	// Format the number with commas and the currency symbol.
	return new Intl.NumberFormat('en-US', {
		style    : 'currency',
		currency : currency || 'USD'
	}).format(amount)
}

/**
 * Unmount app and prevent Vue from creating multiple instances of the same app.
 *
 * @param {string} id The identifier of the app to unmount.
 * @param {Array} apps Array of Vue apps.
 * @returns {void}
 */
export const maybeDeleteBlockVueApp = (id, apps) => {
	const findAppIndex = apps.findIndex(a => a.id === id)
	if (-1 !== findAppIndex) {
		apps[findAppIndex].app.unmount()
		apps.splice(findAppIndex, 1)
	}
}

const {
	registerBlockCollection,
	registerBlockType
} = window.wp.blocks

export const registerBlock = (block) => {
	if (!block || !canLoadBlocks()) {
		return
	}

	const { name, settings } = block

	if (settings?.icon && !settings?.icon?.foreground) {
		const colorIcon = {
			foreground : '#141B38',
			src        : settings.icon
		}
		settings.icon = colorIcon
	}

	// If we're using a Collection for our blocks, we can use the category for something else.
	if ('function' === typeof registerBlockCollection && 'aioseo' === settings.category) {
		settings.category = 'widgets'
	}

	// If Collections aren't supported, ensure all our blocks have the aioseo category.
	if ('function' !== typeof registerBlockCollection && 'aioseo' !== settings.category) {
		settings.category = 'aioseo'
	}

	registerBlockType(name, settings)
}