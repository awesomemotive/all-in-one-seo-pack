import { isClassicEditor, isClassicNoEditor } from '@/vue/utils/context'
import { getTaxonomies } from './helpers'
import {
	loadPiniaStores
} from '@/vue/stores'

if (isClassicEditor() || isClassicNoEditor()) {
	// We need to load the Pinia here since we are using the store outside an App.
	// So when using getTaxonomies, it will throw an error because the store wasn't initialized.
	loadPiniaStores()
	getTaxonomies().forEach(taxonomyData => {
		// Use `getElementById` and `querySelector` together to avoid errors thrown from IDs starting with a number.
		const metaboxTaxonomyDiv = document.getElementById(`${taxonomyData.name}div`)?.querySelector('.inside')
		if (!metaboxTaxonomyDiv) {
			return
		}

		const primaryTermDiv = document.createElement('div')
		primaryTermDiv.setAttribute('id', `aioseo-primary-term-${taxonomyData.name}`)
		primaryTermDiv.setAttribute('class', 'aioseo-primary-term-app')
		primaryTermDiv.setAttribute('taxonomy', taxonomyData.name)

		metaboxTaxonomyDiv.append(primaryTermDiv)

		// We need to use jQuery to listen to these events because jQuery provides an event layer over Vanilla JS.
		// That means that Vanilla JS cannot talk to that added layer.
		;(function ($) {
			$(`#${taxonomyData.name}checklist`).on('change', 'input[type="checkbox"]', () => {
				window.aioseoBus.$emit('updateSelectedTerms')
			})
			$(`#${taxonomyData.name}checklist`).on('wpListAddEnd', () => {
				window.aioseoBus.$emit('updateSelectedTerms')
			})
		})(window.jQuery)
	})
}