import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'

export const maybeUpdateTerm = (run = false) => {
	const postEditorStore = usePostEditorStore()
	if ('term' !== postEditorStore.currentPost.context) {
		return
	}

	const tagsStore = useTagsStore()

	// Term Title
	const titleInput = document.querySelector('#edittag input#name')
	if (titleInput) {
		tagsStore.updateTaxonomyTitle(titleInput.value)
		titleInput.addEventListener('input', () => {
			tagsStore.updateTaxonomyTitle(titleInput.value)
		})
	}

	// Term Description
	const descriptionInput = document.querySelector('#edittag textarea#description')
	if (descriptionInput) {
		tagsStore.updateTaxonomyDescription(descriptionInput.value)
		descriptionInput.addEventListener('input', () => {
			tagsStore.updateTaxonomyDescription(descriptionInput.value)
		})
	}

	// Term Slug
	const slugInput = document.querySelector('#edittag input#slug')
	if (slugInput) {
		const slug = cleanForSlug(slugInput.value)
		tagsStore.updatePermalinkSlug(slug)

		tagsStore.updatePermalink(postEditorStore.currentPost.permalink.replace(`/${tagsStore.permalinkSlug}`, `/${slug.replace(/ /gi, '-').replace(/[^a-z0-9-]/gi, '').toLowerCase()}`))

		slugInput.addEventListener('input', () => {
			tagsStore.updatePermalinkSlug(slug)

			tagsStore.updatePermalink(postEditorStore.currentPost.permalink.replace(`/${tagsStore.permalinkSlug}`, `/${slug.replace(/ /gi, '-').replace(/[^a-z0-9-]/gi, '').toLowerCase()}`))
		})
	}

	postEditorStore.savePostState()

	if (run) {
		TruSeo.runAnalysis({ postId: postEditorStore.currentPost.id })
	}
}