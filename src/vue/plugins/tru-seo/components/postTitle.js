import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostEditedContent } from './postContent'
import { getPostEditedPermalink } from './postPermalink'
import {
	isBlockEditor,
	isClassicEditor,
	isClassicNoEditor,
	isElementorEditor,
	isDiviEditor,
	isSeedProdEditor,
	isWPBakeryEditor,
	isAvadaEditor,
	isSiteOriginEditor,
	isThriveArchitectEditor,
	isBricksEditor,
	isOxygenEditor
} from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/page-builders/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/page-builders/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/page-builders/seedprod/helpers'
import { getEditorData as getWPBakeryData } from '@/vue/standalone/page-builders/wpbakery/helpers'
import { getEditorData as getAvadaData } from '@/vue/standalone/page-builders/avada/helpers'
import { getEditorData as getSiteOriginData } from '@/vue/standalone/page-builders/siteorigin/helpers'
import { getEditorData as getThriveArchitectData } from '@/vue/standalone/page-builders/thrive-architect/helpers'
import { getEditorData as getBricksData } from '@/vue/standalone/page-builders/bricks/helpers'
import { getEditorData as getOxygenData } from '@/vue/standalone/page-builders/oxygen/helpers'

/**
 * Retrieves the title from the active page builder editor.
 *
 * @returns {string} The title from the active page builder editor.
 */
const getEditorTitle = () => {
	const editorMap = [
		{ isEditor: isElementorEditor, getData: getElementorData },
		{ isEditor: isDiviEditor, getData: getDiviData },
		{ isEditor: isSeedProdEditor, getData: getSeedProdData },
		{ isEditor: isWPBakeryEditor, getData: getWPBakeryData },
		{ isEditor: isAvadaEditor, getData: getAvadaData },
		{ isEditor: isSiteOriginEditor, getData: getSiteOriginData },
		{ isEditor: isThriveArchitectEditor, getData: getThriveArchitectData },
		{ isEditor: isBricksEditor, getData: getBricksData },
		{ isEditor: isOxygenEditor, getData: getOxygenData }
	]

	for (const editor of editorMap) {
		if (editor.isEditor()) {
			return editor.getData()?.title ?? ''
		}
	}

	return ''
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