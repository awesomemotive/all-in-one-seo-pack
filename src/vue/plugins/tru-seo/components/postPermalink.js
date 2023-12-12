import {
	usePostEditorStore,
	useTagsStore
} from '@/vue/stores'

import TruSeo from '@/vue/plugins/tru-seo'
import { getPostEditedContent } from './postContent'
import {
	isBlockEditor,
	isClassicEditor,
	isClassicNoEditor,
	isElementorEditor,
	isDiviEditor,
	isSeedProdEditor,
	isWPBakeryEditor,
	isAvadaEditor
} from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/page-builders/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/page-builders/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/page-builders/seedprod/helpers'
import { getEditorData as getWPBakeryData } from '@/vue/standalone/page-builders/wpbakery/helpers'
import { getEditorData as getAvadaData } from '@/vue/standalone/page-builders/avada/helpers'

/**
 * Returns the post permalink from page builders.
 *
 * @returns {string} Post Permalink.
 */
const getEditorPermalink = () => {
	let postPermalink = ''

	switch (true) {
		case isElementorEditor():
			postPermalink = getElementorData().permalink
			break
		case isDiviEditor():
			postPermalink = getDiviData().permalink
			break
		case isSeedProdEditor():
			postPermalink = getSeedProdData().permalink
			break
		case isWPBakeryEditor():
			postPermalink = getWPBakeryData().permalink
			break
		case isAvadaEditor():
			postPermalink = getAvadaData().permalink
			break
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