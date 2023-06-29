import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostContent, getPostEditedContent } from './postContent'
import { getPostEditedPermalink } from './postPermalink'
import { isBlockEditor, isClassicEditor, isClassicNoEditor, isElementorEditor, isDiviEditor, isSeedProdEditor } from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/seedprod/helpers'

/**
 * Retrieves the excerpt from the given content.
 *
 * @since 4.2.2
 *
 * @param {string} content The content to extract the excerpt.
 * @returns {string}       The excerpt.
 */
const excerptFromContent = (content) => {
	if (!content) {
		return ''
	}

	const cleanupPatterns = [
		/\[.*?\]/g, // WordPress shortcode tags.
		/<\/?[a-z][^>]*?>/gi, // HTML tags.
		/<!--[\s\S]*?-->/g, // HTML comments.
		/(\r\n|\n|\r)/g // Line breaks.
	]

	// Remove linebreak.
	content = content.replaceAll(/\n\n/g, ' ')

	cleanupPatterns.forEach(regex => {
		content = content.replace(regex, '')
	})

	return content.trim()
}

/**
 * Returns the post excerpt from page builders.
 *
 * @returns {string} Post Excerpt.
 */
const getEditorExcerpt = () => {
	let postExcerpt = ''

	if (isElementorEditor()) {
		postExcerpt = getElementorData().excerpt
	}

	if (isDiviEditor()) {
		postExcerpt = getDiviData().excerpt
	}

	if (isSeedProdEditor()) {
		postExcerpt = getSeedProdData().excerpt
	}

	return postExcerpt
}

/**
 * Returns the stored post excerpt.
 *
 * @returns {string} Post Excerpt
 */
export const getPostExcerpt = () => {
	const tagsStore = useTagsStore()
	if (tagsStore.liveTags.post_excerpt) {
		return tagsStore.liveTags.post_excerpt
	}

	let postExcerpt

	if (isClassicEditor() || isClassicNoEditor()) {
		postExcerpt = getClassicExcerpt()
	}

	if (isBlockEditor()) {
		postExcerpt = window.wp.data.select('core/editor').getCurrentPost().excerpt
	}

	if (!postExcerpt) {
		postExcerpt = getEditorExcerpt()
	}

	if (!postExcerpt) {
		postExcerpt = excerptFromContent(getPostContent())
	}

	if (postExcerpt) {
		tagsStore.updatePostExcerpt(postExcerpt)
	}
	return postExcerpt
}

/**
 * Returns the edited post excerpt.
 *
 * @returns {string} Post excerpt
 */
export const getPostEditedExcerpt = () => {
	let postExcerpt

	if (isClassicEditor() || isClassicNoEditor()) {
		postExcerpt = getClassicExcerpt()
	}

	if (isBlockEditor()) {
		postExcerpt = window.wp.data.select('core/editor').getEditedPostAttribute('excerpt')
	}

	if (!postExcerpt) {
		postExcerpt = getEditorExcerpt()
	}

	if (!postExcerpt) {
		postExcerpt = excerptFromContent(getPostEditedContent())
	}

	return postExcerpt
}

export const maybeUpdatePostExcerpt = async (run = true) => {
	let postExcerpt   = getPostExcerpt()
	const newPostExcerpt = getPostEditedExcerpt()
	if (postExcerpt !== newPostExcerpt) {
		postExcerpt = newPostExcerpt

		const postEditorStore = usePostEditorStore()
		const tagsStore       = useTagsStore()
		tagsStore.updatePostExcerpt(postExcerpt)

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

export const getClassicExcerpt = () => {
	let excerpt = ''

	// Default excerpt content. Uses the textarea
	const textEditor = document.querySelector('#postexcerpt textarea#excerpt')
	excerpt = textEditor ? textEditor.value : ''

	if (document.querySelector('#wp-excerpt-wrap.tmce-active')) {
		// Get the excerpt content if tinyMCE is enabled
		const editor = window.tinyMCE ? window.tinyMCE.get('excerpt') : ''
		if (editor) {
			excerpt = editor.getContent({ format: 'raw' })
		}
	}

	return excerpt
}