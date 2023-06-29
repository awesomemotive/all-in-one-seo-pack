import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostEditedContent } from './postContent'
import { getPostEditedPermalink } from './postPermalink'
import { isBlockEditor, isClassicEditor, isClassicNoEditor, isElementorEditor, isDiviEditor, isSeedProdEditor } from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/seedprod/helpers'

/**
 * Returns the post title from page builders.
 *
 * @returns {string} Post Title.
 */
const getEditorTitle = () => {
	let postTitle = ''

	if (isElementorEditor()) {
		postTitle = getElementorData().title
	}

	if (isDiviEditor()) {
		postTitle = getDiviData().title
	}

	if (isSeedProdEditor()) {
		postTitle = getSeedProdData().title
	}

	return postTitle
}

/**
 * Returns the stored post title.
 *
 * @returns {string} Post
 */
export const getPostTitle = () => {
	const tagsStore = useTagsStore()
	if (tagsStore.liveTags.post_title) {
		return tagsStore.liveTags.post_title
	}

	let postTitle

	if (isClassicEditor() || isClassicNoEditor()) {
		const titleInput = document.querySelector('#post input#title')
		postTitle = titleInput ? titleInput.value : ''
	}

	if (isBlockEditor()) {
		postTitle = window.wp.data.select('core/editor').getCurrentPost().title
	}

	if (!postTitle) {
		postTitle = getEditorTitle()
	}

	if (postTitle) {
		tagsStore.updatePostTitle(postTitle)
	}

	return postTitle
}

/**
 * Returns the edited post title.
 *
 * @returns {string} Post Title
 */
export const getPostEditedTitle = () => {
	let postTitle

	if (isClassicEditor() || isClassicNoEditor()) {
		const titleInput = document.querySelector('#post input#title')
		postTitle = titleInput ? titleInput.value : ''
	}

	if (isBlockEditor()) {
		postTitle = window.wp.data.select('core/editor').getEditedPostAttribute('title')
	}

	if (!postTitle) {
		postTitle = getEditorTitle()
	}

	return postTitle
}

// Update post data
export const maybeUpdatePostTitle = async (run = true) => {
	let postTitle      = getPostTitle()
	const newPostTitle = getPostEditedTitle()

	if (postTitle !== newPostTitle) {
		postTitle = newPostTitle

		const postEditorStore = usePostEditorStore()
		const tagsStore       = useTagsStore()
		tagsStore.updatePostTitle(postTitle)

		if (!run) {
			return
		}

		(new TruSeo()).runAnalysis({
			postId   : postEditorStore.currentPost.id,
			postData : { ...postEditorStore.currentPost },
			content  : getPostEditedContent(),
			slug     : getPostEditedPermalink()
		})
	}
}