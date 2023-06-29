import {
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'
import {
	truSeoShouldAnalyze,
	maybeUpdatePost
} from '@/vue/plugins/tru-seo/components/helpers'

/**
 * Get custom fields values used in content.
 * @returns {string} string of field values.
 */
export const customFieldsContent = () => {
	const postEditorStore = usePostEditorStore()
	const postType        = postEditorStore.currentPost.postType
	if (!postType) {
		return ''
	}

	const optionsStore = useOptionsStore()
	const truFields           = optionsStore.dynamicOptions.searchAppearance.postTypes[postType].customFields

	// No custom fields have been specified.
	if (!truFields || !truSeoShouldAnalyze) {
		return ''
	}

	const updateValues = () => {
		maybeUpdatePost(500)
	}
	const truFieldsArray = truFields.replace(/\n/g, ',').split(',')
	const fields         = []
	const inputTypes     = [ 'INPUT', 'TEXTAREA', 'IMG' ]

	// Check if a selector is valid CSS syntax.
	const isSelectorValid = (selector) => {
		try {
			document.createDocumentFragment().querySelector(selector)
		} catch {
			return false
		}
		return true
	}

	truFieldsArray.forEach((truField) => {
		truField = truField.trim()
		const customField    = isSelectorValid(`#${truField}`) ? document.querySelector(`#${truField}`) : false
		const WpCustomFields = document.querySelectorAll('#the-list > tr')
		const acfFields      = document.querySelectorAll('.acf-field')

		// make sure it's one of our input types and isn't an acf-field
		if (inputTypes.includes(customField?.tagName) && !customField?.closest('.acf-field')) {
			fields.push(customField)
		} else {
			// Maybe we have a core meta_box. Add the values.
			WpCustomFields.forEach((row) => {
				const key   = row.querySelector(`#${row.id}-key`)
				const value = row.querySelector(`#${row.id}-value`)

				if (inputTypes.includes(value?.tagName) && truFieldsArray.includes(key?.value)) {
					fields.push(value)
				}
			})
		}

		// If we have an acf meta_box. Add the values.
		acfFields.forEach((acfField) => {
			if (truField !== acfField.dataset.name) {
				return ''
			}

			let fieldEl = acfField.querySelector(`[id^="acf"][name$="[${acfField.dataset.key}]"]`)

			if ('image' === acfField.dataset.type) {
				fieldEl = acfField.querySelector('.has-value img')
			}

			if ('gallery' === acfField.dataset.type) {
				fieldEl = acfField.querySelector('.acf-gallery-attachment img')
			}

			if (!fieldEl) {
				return ''
			}

			if (fieldEl.type && 'hidden' === fieldEl.type) {
				return ''
			}

			if ('wysiwyg' === acfField.dataset.type) {
				const acfTmceInterval = window.setInterval(() => {
					if (!window.tinyMCE?.activeEditor?.acf) {
						return
					}
					window.clearInterval(acfTmceInterval)
					window.tinyMCE.activeEditor.on('keyup', function () {
						maybeUpdatePost(500)
					})
					// Fires when adding, removing links, cut, paste, etc..
					window.tinyMCE.activeEditor.on('PostProcess', function () {
						debounce(() => {
							maybeUpdatePost(500)
						}, 10)
					})
				}, 50)
				// Watch for switching between tinyMCE and the text editor.
				const acfTmceSwitch = function (mutationsList) {
					mutationsList.forEach(mutation => {
						if (
							'class' === mutation.attributeName &&
							acfField.querySelector('.wp-editor-wrap.tmce-active') &&
							window.tinyMCE?.activeEditor
						) {
							window.tinyMCE.activeEditor.on('keyup', function () {
								maybeUpdatePost(500)
							})
							const textEditor = acfField.querySelector(`[name="acf[${acfField.dataset.key}]"]`)
							if (textEditor) {
								textEditor.addEventListener('keyup', () => {
									maybeUpdatePost(500)
								})
								textEditor.addEventListener('paste', () => {
									maybeUpdatePost(500)
								})
							}
						}
					})
				}
				const mutationObserver = new MutationObserver(acfTmceSwitch)
				const contentWrap = acfField.querySelector('.wp-editor-wrap')
				if (contentWrap) {
					mutationObserver.observe(contentWrap, { attributes: true })
				}
			}

			if (inputTypes.includes(fieldEl.tagName)) {
				fields.push(fieldEl)
			}
		})
	})

	let fieldsContent = ''

	fields.forEach((field) => {
		let content = ''

		if (inputTypes.includes(field.tagName)) {
			field.addEventListener('keyup', updateValues)
		}

		if (field.value) {
			content = field.value
		}

		if ('IMG' === field.tagName && field.src) {
			const alt = field.alt ? `alt="${field.alt}"` : ''
			content = `<img src="${field.src}" ${alt}>`
		}

		if (field.value && field.type && 'url' === field.type) {
			content = `<a href="${content}">${content}</a>`
		}

		if (content) {
			fieldsContent += `${content} `
		}
	})
	return fieldsContent
}

/**
 * Get custom field image URL.
 *
 * @param   {string} fieldKey   The field key.
 * @param   {Array}  inputTypes The valid input types.
 * @returns {string}            URL of the image.
 */
export const customFieldValue = (fieldKey, inputTypes = [ 'INPUT', 'TEXTAREA', 'IMG' ]) => {
	if (!fieldKey) {
		return ''
	}

	const customField    = document.querySelector(`#${fieldKey}`)
	const wpCustomFields = document.querySelectorAll('#the-list > tr')
	const acfFields      = document.querySelectorAll('.acf-field')
	let value            = ''

	if (customField && (-1 !== inputTypes.indexOf(customField.tagName))) {
		// Make sure it isn't an acf-field
		if (!customField.closest('.acf-field')) {
			// We have a meta_box, add the value.
			value = 'IMG' === customField.tagName ? customField.getAttribute('src') : customField.value
		}
	}

	if (wpCustomFields.length) {
		// Maybe we have a core meta_box, get the value.
		wpCustomFields.forEach((row) => {
			const inputKey   = row.querySelector(`#${row.id}-key`)
			const inputValue = row.querySelector(`#${row.id}-value`)

			if (inputValue && (-1 !== inputTypes.indexOf(inputValue.tagName)) && inputKey.value === fieldKey) {
				value = 'IMG' === inputValue.tagName ? inputValue.getAttribute('src') : inputValue.value
			}
		})
	}

	if (acfFields.length) {
		// We have an acf meta_box. Add the values.
		acfFields.forEach((acfField) => {
			if (fieldKey !== acfField.dataset.name) {
				return
			}

			let acfFieldElement
			inputTypes.forEach(type => {
				const inputTag = type.toLowerCase()
				acfFieldElement = acfField.querySelector(`[data-key="${acfField.dataset.key}"] ${inputTag}`) || acfFieldElement
			})

			if (acfFieldElement) {
				value = 'IMG' === acfFieldElement.tagName ? acfFieldElement.getAttribute('src') : acfFieldElement.value
			}
		})
	}

	return value
}