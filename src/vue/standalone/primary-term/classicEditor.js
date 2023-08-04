import { isClassicEditor } from '@/vue/plugins/tru-seo/components/helpers'
import { getTaxonomies } from './helpers'
import {
	loadPiniaStores
} from '@/vue/stores'

if (isClassicEditor()) {
	// We need to load the Pinia here since we are using the store outside an App.
	// So when using getTaxonomies, it will throw an error because the store wasn't initialized.
	loadPiniaStores()
	getTaxonomies().forEach(taxonomyData => {
		const metaboxTaxonomyDiv = document.querySelector(`#${taxonomyData.name}div .inside`)
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