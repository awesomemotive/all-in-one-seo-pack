import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

const getContent = () => {
	const postEditorStore = usePostEditorStore()

	return postEditorStore.currentPost?.processedContent || ''
}

const getTitle = () => {
	const tagsStore = useTagsStore()

	// There's no way to edit the title in the Oxygen editor, so we're using the tags store.
	return tagsStore.tags.find(tag => 'post_title' === tag.id)?.value || ''
}

const getPermalink = () => {
	const tagsStore = useTagsStore()

	// There's no way to edit the permalink in the Oxygen editor, so we're using the tags store.
	return tagsStore.tags.find(tag => 'permalink' === tag.id)?.value || ''
}

const getSlug = () => {
	try {
		const url = new URL(getPermalink())
		const path = url.pathname.replace(/\/$/, '')
		const segments = path.split('/')

		return segments[segments.length - 1]
	} catch (_error) {
		return ''
	}
}

const getExcerpt = () => {
	const tagsStore = useTagsStore()

	// There's no way to edit the excerpt in the Oxygen editor, so we're using the tags store.
	return tagsStore.tags.find(tag => 'post_excerpt' === tag.id)?.value || ''
}

const getFeaturedImage = async () => {
	return ''
}

export const getSaveButton = () => {
	return window.parent.document.querySelector('.button-save-oxygen') || null
}

/**
 * Gets all editor data for SEO analysis.
 *
 * @returns {Object} Object containing all the editor data.
 */
export const getEditorData = () => {
	return {
		content       : getContent(),
		title         : getTitle(),
		excerpt       : getExcerpt(),
		slug          : getSlug(),
		permalink     : getPermalink(),
		featuredImage : getFeaturedImage()
	}
}