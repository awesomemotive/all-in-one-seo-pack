import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'

// Importing these directly to avaoid circular dependencies.
import { maybeUpdatePostTitle } from './postTitle'
import { maybeUpdatePostContent } from './postContent'
import { maybeUpdatePostExcerpt } from './postExcerpt'
import { maybeUpdatePostSlug } from './postSlug'
import { maybeUpdatePermalink } from './postPermalink'
import { maybeUpdateTaxonomies } from './taxonomies'
import { maybeUpdateTerm } from './term'
import { maybeUpdateAttachment } from './attachments'

import TruSeo from '@/vue/plugins/tru-seo'

export {
	isBlockEditor,
	isClassicEditor,
	isClassicNoEditor,
	isWooCommerceProduct
} from '@/vue/utils/context'

export const truSeoShouldAnalyze = () => {
	const postEditorStore = usePostEditorStore()
	if (!postEditorStore.currentPost?.id) {
		return false
	}

	const optionsStore = useOptionsStore()
	return (
		optionsStore.options.advanced?.truSeo &&
		!postEditorStore.currentPost.isSpecialPage &&
		'attachment' !== postEditorStore.currentPost.postType &&
		shouldShowMetaBox()
	)
}

export const shouldShowTruSeoScore = () => {
	const rootStore = useRootStore()
	if (!rootStore.aioseo.screen?.postType) {
		return false
	}

	const optionsStore = useOptionsStore()
	return !!(
		optionsStore.options.advanced?.truSeo &&
		shouldShowMetaBox(rootStore.aioseo.screen.postType)
	)
}

/**
 * Since this runs before any stores are loaded, we have to use the window object to determine if it should be shown or not.
 *
 * @version 4.4.0
 *
 * @param   {string} postType The post type to check.
 * @returns {boolean}         Returns true if the meta box should be shown.
 */
export const shouldShowMetaBox = (postType = null) => {
	if (postType) {
		return !!(
			window.aioseo.dynamicOptions.searchAppearance.postTypes[postType]?.advanced?.showMetaBox
		)
	}

	if (!window.aioseo.currentPost?.id) {
		return false
	}
	const pt       = window.aioseo.currentPost.postType
	const taxonomy = window.aioseo.currentPost.termType
	const showForPost = !!(
		pt &&
		'post' === window.aioseo.currentPost.context &&
		window.aioseo.dynamicOptions.searchAppearance.postTypes[pt] &&
		window.aioseo.dynamicOptions.searchAppearance.postTypes[pt]?.advanced?.showMetaBox
	)
	const showForTerm = !!(
		taxonomy &&
		'term' === window.aioseo.currentPost.context &&
		window.aioseo.dynamicOptions.searchAppearance.taxonomies[taxonomy] &&
		window.aioseo.dynamicOptions.searchAppearance.taxonomies[taxonomy]?.advanced?.showMetaBox
	)

	return showForPost || showForTerm
}

export const maybeUpdatePost = async (time = 900, run = true) => {
	debounce(async () => {
		await maybeUpdatePostTitle(false)
		await maybeUpdatePostContent(false)
		await maybeUpdatePostExcerpt(false)
		await maybeUpdatePostSlug(false)
		await maybeUpdatePermalink(false)
		maybeUpdateTaxonomies(false)
		maybeUpdateTerm(false)
		maybeUpdateAttachment(false)

		const postEditorStore = usePostEditorStore()
		if (run) {
			(new TruSeo()).runAnalysis({ postId: postEditorStore.currentPost.id })
		}
	}, time)
}