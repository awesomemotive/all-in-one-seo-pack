import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'

// Importing these directly to avoid circular dependencies.
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

/**
 * Reverses a Selection object in order to modify it from left to right.
 *
 * @since 4.4.6
 *
 * @param 	{Object} selection The Selection object.
 * @returns {Object} 		   The reversed Selection object.
 */
export const reverseWindowSelection = (selection) => {
	const selectionRange = selection.getRangeAt(0)
	const cloneRange = selectionRange.cloneRange()

	cloneRange.collapse(false)
	selection.removeAllRanges()
	selection.addRange(cloneRange)
	selection.extend(selectionRange.startContainer, selectionRange.startOffset)

	return selection
}

/**
 * Normalizes whitespace characters.
 *
 * @since 4.4.6
 *
 * @param   {string} string The string.
 * @returns {string}        The normalized string.
 */
export const normalizeWhitespaces = (string) => {
	const NBSP = new RegExp(String.fromCharCode(160), 'g')

	return string
		.replace(/&nbsp;/g, ' ')
		.replace(NBSP, ' ')
}

/**
 * Finds the closest parent node of a given element that has a specific property value.
 *
 * @since 4.4.6
 *
 * @param   {Object}  options  		   The options for finding the node.
 * @param   {Element} options.element  The element from which to start searching.
 * @param   {string}  options.property The CSS property to compare against.
 * @param   {string}  options.value    The value to match against the property value.
 * @returns {Element} 	   			   The closest element with the specified property value, or the html root element if not found.
 */
export const getClosestNodeByPropertyValue = ({ element, property, value }) => {
	if (!element) {
		return document.documentElement
	}

	let parent = element.parentElement
	while (parent) {
		if (
			parent.isEqualNode(document.documentElement) ||
			value === document.defaultView.getComputedStyle(parent).getPropertyValue(property)
		) {
			return parent
		}

		parent = parent.parentElement
	}
}

/**
 * Creates a highlight popover container node.
 *
 * @since 4.4.6
 *
 * @returns {HTMLElement} The created popover node element wrapper.
 */
export const createHighlightPopoverNode = () => {
	const el = document.createElement('div')

	el.classList.add('tru-seo-highlight-popover-wrapper')
	el.style.position = 'absolute'
	el.style.zIndex = '999'
	el.style.display = 'flex'
	el.setAttribute('tabindex', -1)

	return el
}