import {
	useRootStore
} from '@/vue/stores'

import { isBlockEditor } from '@/vue/plugins/tru-seo/components/helpers'

const blockEditor = {
	getTerms : async (taxName) => {
		const { apiFetch }     = window.wp
		const { addQueryArgs } = window.wp.url

		const taxonomyData = getTaxonomyData(taxName)
		const response     = await apiFetch({
			path : addQueryArgs(
				`/wp/v2/${taxonomyData.restBase}`,
				{
					per_page : -1,
					orderby  : 'count',
					order    : 'desc',
					_fields  : 'id,name'
				}
			)
		})

		return response
	},
	getSelectedTerms : (taxName) => {
		const taxonomyData = getTaxonomyData(taxName)

		return window.wp.data.select('core/editor').getEditedPostAttribute(taxonomyData.restBase) || []
	}
}

const classicEditor = {
	getTerms : async (taxSlug) => {
		const taxonomyTerms = []
		const taxonomyData  = getTaxonomyData(taxSlug)
		const taxonomyList  = document.getElementById(`${taxonomyData.name}checklist`)?.querySelectorAll('li') || []

		taxonomyList.forEach(taxonomy => {
			const termId   = taxonomy.querySelector('input').value
			const termName = taxonomy.querySelector('label').innerText

			taxonomyTerms.push({
				id   : parseInt(termId, 10),
				name : termName.trim()
			})
		})

		return new Promise(resolve => {
			resolve(taxonomyTerms)
		})
	},
	getSelectedTerms : (taxSlug) => {
		const selectedTerms = []
		const taxonomyData  = getTaxonomyData(taxSlug)
		const taxonomyTerms = document.getElementById(`${taxonomyData.name}checklist`)?.querySelectorAll('input:checked') || []

		taxonomyTerms.forEach(term => {
			selectedTerms.push(parseInt(term.value, 10))
		})

		return selectedTerms
	}
}

/**
 * Returns the taxonomies that have primary term support.
 *
 * @returns {Array} The taxonomies that have primary term support.
 */
export const getTaxonomies = () => {
	const rootStore = useRootStore()
	const taxonomies = rootStore.aioseo.postData?.taxonomies || []

	return taxonomies.filter(taxonomy => {
		return true === taxonomy.primaryTermSupport
	})
}

/**
 * Checks if the taxonomy has primary term support.
 *
 * @param {string} taxName The taxonomy slug.
 * @returns {boolean} Whether the taxonomy has primary term support.
 */
export const taxonomyHasPrimaryTermSupport = (taxName) => {
	return getTaxonomies().some(taxonomy => {
		return taxName === taxonomy.name
	})
}

/**
 * Returns the taxonomy data.
 *
 * @param {string} taxName The taxonomy slug.
 * @returns {Object} The taxonomy data.
 */
export const getTaxonomyData = (taxName) => {
	const taxonomyData = getTaxonomies().filter(taxonomy => {
		return taxName === taxonomy.name
	})

	return taxonomyData.length ? taxonomyData[0] : {}
}

/**
 * Returns the terms for the taxonomy.
 *
 * @param {string} taxName The taxonomy slug.
 * @returns {Array} The terms for the taxonomy.
 */
export const getTerms = (taxName) => {
	if (isBlockEditor()) {
		return blockEditor.getTerms(taxName)
	}

	return classicEditor.getTerms(taxName)
}

/**
 * Returns the selected terms for the taxonomy.
 *
 * @param {string} taxName The taxonomy slug.
 * @returns {Array} The selected terms for the taxonomy.
 */
export const getSelectedTerms = (taxName) => {
	if (isBlockEditor()) {
		return blockEditor.getSelectedTerms(taxName)
	}

	return classicEditor.getSelectedTerms(taxName)
}