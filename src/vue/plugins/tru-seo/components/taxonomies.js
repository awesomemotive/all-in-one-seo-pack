import {
	usePostEditorStore,
	useRootStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { isBlockEditor, isClassicEditor, isClassicNoEditor } from '@/vue/utils/context'

let taxonomyTitle = '',
	listOfCategories = ''

// Update post data
export const maybeUpdateTaxonomies = (run = true) => {
	const tagsStore = useTagsStore()
	const postEditorStore = usePostEditorStore()

	if (isClassicEditor() || isClassicNoEditor()) {
		const categories = document.querySelectorAll('#post input[name="post_category[]"]:checked')

		if (categories.length) {
			if (postEditorStore?.currentPost?.primary_term?.category) {
				const categoryElement = document.querySelector(`#categorychecklist input[value="${postEditorStore.currentPost.primary_term.category}"]`)
				if (categoryElement?.parentNode?.innerText) {
					taxonomyTitle = categoryElement.parentNode.innerText
					tagsStore.updateTaxonomyTitle(taxonomyTitle)
				}
			} else if (taxonomyTitle !== categories[0].parentNode.innerText) {
				taxonomyTitle = categories[0].parentNode.innerText
				tagsStore.updateTaxonomyTitle(categories[0].parentNode.innerText)
			}

			listOfCategories = Array.from(categories).map(category => category.parentNode.innerText).join(', ')
			tagsStore.updateCategories(listOfCategories)
		} else {
			if ('' !== taxonomyTitle) {
				taxonomyTitle = listOfCategories = ''
				tagsStore.updateTaxonomyTitle(taxonomyTitle)
				tagsStore.updateCategories(listOfCategories)
			}
		}
	}

	if (isBlockEditor()) {
		let categories = [],
			selected   = [],
			defaultCategoryId = null,
			defaultCategory   = null

		const rootStore = useRootStore()
		if (rootStore.aioseo.user.capabilities?.manageCategories || rootStore.aioseo.user.capabilities?.manage_categories) {
			categories = window.wp.data.select('core').getEntityRecords('taxonomy', 'category')
			selected   = window.wp.data.select('core/editor').getEditedPostAttribute('categories')

			defaultCategoryId = window.wp.data.select('core').getEntityRecord('root', 'site')?.default_category
			defaultCategory   = window.wp.data.select('core').getEntityRecord('taxonomy', 'category', defaultCategoryId)
		}

		if (selected && selected.length && categories) {
			const selectedCategories = categories.filter(c => selected.includes(c.id))
			listOfCategories = selectedCategories.map(c => c.name).join(', ')
			tagsStore.updateCategories(listOfCategories)

			const selectedCategory = postEditorStore?.currentPost?.primary_term?.category || selectedCategories[0]?.id
			let category = selectedCategories.find(c => c.id === selectedCategory)

			if (!category && defaultCategory && selected.includes(defaultCategoryId)) {
				category = defaultCategory
			}

			taxonomyTitle = category?.name || ''
			tagsStore.updateTaxonomyTitle(taxonomyTitle)
		} else {
			taxonomyTitle = ''
			tagsStore.updateTaxonomyTitle('')
			tagsStore.updateCategories('')
		}
	}

	if (run) {
		(new TruSeo()).runAnalysis({ postId: postEditorStore.currentPost.id })
	}
}