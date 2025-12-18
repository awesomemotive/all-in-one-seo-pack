import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostContent, getPostEditedContent } from './postContent'
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
import { getText } from '@/vue/utils/html'

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

	content = content.replaceAll(/\n\n/g, ' ')
	content = getText(content, false, 'innerText') // Get the visible text from the content.
	content = content.replace(/\[.*?]/g, '') // Remove WordPress shortcode tags.

	return content.trim()
}

/**
 * Retrieves the excerpt from the active page builder editor.
 *
 * @returns {string} The excerpt from the active page builder editor.
 */
const getEditorExcerpt = () => {
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
			return editor.getData()?.excerpt ?? ''
		}
	}

	return ''
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