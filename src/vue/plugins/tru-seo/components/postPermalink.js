import {
	useRootStore,
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
	isAvadaEditor,
	isSiteOriginEditor,
	isThriveArchitectEditor
} from '@/vue/utils/context'
import { getEditorData as getElementorData } from '@/vue/standalone/page-builders/elementor/helpers'
import { getEditorData as getDiviData } from '@/vue/standalone/page-builders/divi/helpers'
import { getEditorData as getSeedProdData } from '@/vue/standalone/page-builders/seedprod/helpers'
import { getEditorData as getWPBakeryData } from '@/vue/standalone/page-builders/wpbakery/helpers'
import { getEditorData as getAvadaData } from '@/vue/standalone/page-builders/avada/helpers'
import { getEditorData as getSiteOriginData } from '@/vue/standalone/page-builders/siteorigin/helpers'
import { getEditorData as getThriveArchitectData } from '@/vue/standalone/page-builders/thrive-architect/helpers'
import { getPostEditedSlug } from '@/vue/plugins/tru-seo/components/postSlug'

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
		case isSiteOriginEditor():
			postPermalink = getSiteOriginData().permalink
			break
		case isThriveArchitectEditor():
			postPermalink = getThriveArchitectData().permalink
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
	const rootStore = useRootStore()
	let postPermalink

	if (isClassicEditor() || isClassicNoEditor()) {
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

	if (postPermalink && rootStore.aioseo.data.usingPermalinks && getPostEditedSlug()) {
		// Always replace the last part of the URL with the new slug being edited.
		const parsedUrl = new URL(postPermalink)

		// Split the pathname into segments, replace the last one, and join them back.
		const paths = parsedUrl.pathname.split('/').filter(segment => '' !== segment)

		paths[0 < paths.length ? paths.length - 1 : 0] = getPostEditedSlug()
		parsedUrl.pathname = paths.join('/') + (parsedUrl.pathname.endsWith('/') ? '/' : '')

		// Remove query string and hash fragment
		parsedUrl.search = ''
		parsedUrl.hash = ''

		postPermalink = parsedUrl.toString()
	}

	return decodeURIComponent(postPermalink)
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