import {
	useRootStore,
	loadPiniaStores
} from '@/vue/stores'

import { isBlockEditor } from '@/vue/utils/context'

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
	getSelectedTerms : (taxName, all = false) => {
		const taxonomyData = getTaxonomyData(taxName, all)

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
	getSelectedTerms : (taxSlug, all = false) => {
		const selectedTerms = []
		const taxonomyData  = getTaxonomyData(taxSlug, all)
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
 * @param {boolean} all Should get all posts not only the ones that supports primary term..
 * @returns {Array} The taxonomies that have primary term support.
 */
export const getTaxonomies = (all = false) => {
	// We need to load the Pinia here since we are using the store outside an App.
	// So when using useRootStore, it will throw an error because the store wasn't initialized.
	loadPiniaStores()
	const rootStore = useRootStore()
	const taxonomies = rootStore.aioseo.postData?.taxonomies || []

	if (all) {
		return taxonomies
	}

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
 * @param {boolean} all Should get all posts not only the ones that supports primary term..
 * @returns {Object} The taxonomy data.
 */
export const getTaxonomyData = (taxName, all = false) => {
	const taxonomyData = getTaxonomies(all).filter(taxonomy => {
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
 * @param {boolean} all Should get all posts not only the ones that supports primary term..
 * @returns {Array} The selected terms for the taxonomy.
 */
export const getSelectedTerms = (taxName, all = false) => {
	if (isBlockEditor()) {
		return blockEditor.getSelectedTerms(taxName, all)
	}

	return classicEditor.getSelectedTerms(taxName, all)
}