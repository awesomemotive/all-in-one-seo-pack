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
			selected   = []

		const rootStore = useRootStore()
		if (rootStore.aioseo.user.data.allcaps?.manageCategories || rootStore.aioseo.user.data.allcaps?.manage_categories) {
			categories = window.wp.data.select('core').getEntityRecords('taxonomy', 'category')
			selected   = window.wp.data.select('core/editor').getEditedPostAttribute('categories')
		}

		if (selected && selected.length && categories) {
			const selectedCategory = postEditorStore?.currentPost?.primary_term?.category || selected[0]
			const category = categories.find(c => c.id === selectedCategory)
			if (category && (taxonomyTitle !== category.name)) {
				taxonomyTitle = category.name
				tagsStore.updateTaxonomyTitle(taxonomyTitle)
			}
			listOfCategories = categories.filter(c => selected.includes(c.id)).map(c => c.name).join(', ')
			tagsStore.updateCategories(listOfCategories)
		} else {
			if ('' !== taxonomyTitle) {
				taxonomyTitle = listOfCategories = ''
				tagsStore.updateTaxonomyTitle(taxonomyTitle)
				tagsStore.updateCategories(listOfCategories)
			}
		}
	}

	if (run) {
		(new TruSeo()).runAnalysis({ postId: postEditorStore.currentPost.id })
	}
}