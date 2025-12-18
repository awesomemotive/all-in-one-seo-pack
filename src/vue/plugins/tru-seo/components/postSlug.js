import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'
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
 * Retrieves the slug from the active page builder editor.
 *
 * @returns {string} The slug from the active page builder editor.
 */
const getEditorSlug = () => {
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
			return editor.getData()?.slug ?? ''
		}
	}

	return ''
}

/**
 * Returns the stored post slug.
 *
 * @returns {string} Post Slug
 */
export const getPostSlug = () => {
	const tagsStore = useTagsStore()
	if (tagsStore.permalinkSlug) {
		return tagsStore.permalinkSlug
	}

	let postSlug = ''

	if (isClassicEditor() || isClassicNoEditor()) {
		const classicSlug = document.querySelector('#post_name')
		if (classicSlug) {
			postSlug = cleanForSlug(classicSlug.value)
		}
	}

	if (isBlockEditor()) {
		postSlug = window.wp.data.select('core/editor').getCurrentPost().slug
	}

	if (!postSlug) {
		postSlug = getEditorSlug()
	}

	if (postSlug) {
		tagsStore.updatePermalinkSlug(postSlug)
	}

	return postSlug
}

/**
 * Returns the edited post slug.
 *
 * @returns {string} Post slug
 */
export const getPostEditedSlug = () => {
	let postSlug = ''

	if (isClassicEditor() || isClassicNoEditor()) {
		const postName    = document.querySelector('#post_name')?.value || document.querySelector('#editable-post-name-full')?.textContent
		const classicSlug = postName || document.querySelector('#title').value
		if (classicSlug) {
			postSlug = cleanForSlug(classicSlug)
		}
	}

	if (isBlockEditor()) {
		if ('function' === typeof window.wp?.data?.select('core/editor').getEditedPostSlug) {
			postSlug = window.wp.data.select('core/editor').getEditedPostSlug()
		} else {
			postSlug = window.wp.data.select('core/editor').getEditedPostAttribute('slug')
		}
	}

	if (!postSlug) {
		postSlug = getEditorSlug()
	}

	return postSlug
}

export const maybeUpdatePostSlug = async (run = true) => {
	let postSlug      = getPostSlug()
	const newPostSlug = getPostEditedSlug()
	if (postSlug !== newPostSlug) {
		postSlug = newPostSlug

		const postEditorStore = usePostEditorStore()
		const tagsStore       = useTagsStore()
		tagsStore.updatePermalinkSlug(postSlug)

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