import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostEditedContent } from './postContent'
import { isBlockEditor, isClassicEditor, isClassicNoEditor, isElementorEditor, isDiviEditor, isSeedProdEditor } from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/seedprod/helpers'

/**
 * Returns the post permalink from page builders.
 *
 * @returns {string} Post Permalink.
 */
const getEditorPermalink = () => {
	let postPermalink = ''

	if (isElementorEditor()) {
		postPermalink = getElementorData().permalink
	}

	if (isDiviEditor()) {
		postPermalink = getDiviData().permalink
	}

	if (isSeedProdEditor()) {
		postPermalink = getSeedProdData().permalink
	}

	return postPermalink
}

/**
 * Returns the stored post permalink.
 *
 * @returns {string} Post Permalink
 */
export const getPostPermalink = () => {
	const tagsStore = useTagsStore()
	if (tagsStore.liveTags.permalink) {
		return tagsStore.liveTags.permalink
	}

	let postPermalink

	if (isClassicEditor() || isClassicNoEditor) {
		const classicLink = document.querySelector('#edit-slug-box a')
		if (classicLink && classicLink.href) {
			postPermalink = classicLink.href
		}
	}

	if (isBlockEditor()) {
		postPermalink = window.wp.data.select('core/editor').getPermalink()
	}

	if (!postPermalink) {
		postPermalink = getEditorPermalink()
	}

	if (postPermalink) {
		tagsStore.updatePermalink(postPermalink)
	}

	return postPermalink
}

/**
 * Returns the edited post permalink.
 *
 * @returns {string} Post permalink
 */
export const getPostEditedPermalink = () => {
	let postPermalink

	if (isClassicEditor() || isClassicNoEditor) {
		const classicLink = document.querySelector('#edit-slug-box a')
		if (classicLink && classicLink.href) {
			postPermalink = classicLink.href
		}
	}

	if (isBlockEditor()) {
		postPermalink = window.wp.data.select('core/editor').getPermalink()
	}

	if (!postPermalink) {
		postPermalink = getEditorPermalink()
	}

	return postPermalink
}

export const maybeUpdatePermalink = async (run = true) => {
	let postPermalink = getPostPermalink()
	const newPermalink = getPostEditedPermalink()
	if (postPermalink !== newPermalink) {
		postPermalink = newPermalink
		if (postPermalink) {
			const postEditorStore = usePostEditorStore()
			const tagsStore       = useTagsStore()
			tagsStore.updatePermalink(postPermalink)

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
}