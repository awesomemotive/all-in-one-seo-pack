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

	if (isClassicEditor() || isClassicNoEditor()) {
		const categories = document.querySelectorAll('#post input[name="post_category[]"]:checked')
		if (categories.length) {
			if (taxonomyTitle !== categories[0].parentNode.innerText) {
				taxonomyTitle = categories[0].parentNode.innerText
				tagsStore.updateTaxonomyTitle(taxonomyTitle)
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
		if (rootStore.aioseo.user.data.allcaps?.manageCategories) {
			categories = window.wp.data.select('core').getEntityRecords('taxonomy', 'category')
			selected   = window.wp.data.select('core/editor').getEditedPostAttribute('categories')
		}

		if (selected && selected.length && categories) {
			const category = categories.find(c => c.id === selected[0])
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

	const postEditorStore = usePostEditorStore()
	if (run) {
		(new TruSeo()).runAnalysis({ postId: postEditorStore.currentPost.id })
	}
}