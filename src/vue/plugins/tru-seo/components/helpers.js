import {
	useOptionsStore,
	usePostEditorStore,
	useRootStore,
	useSchemaStore
} from '@/vue/stores'

import { debounce } from '@/vue/utils/debounce'

import { shouldShowMetaBox } from '@/vue/utils/metabox'

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

export const truSeoShouldAnalyze = () => {
	const postEditorStore = usePostEditorStore()
	if (!postEditorStore?.currentPost?.id) {
		return false
	}

	return !!postEditorStore.currentPost?.isTruSeoEligible || false
}

export const shouldShowTruSeoScore = () => {
	const rootStore = useRootStore()
	if (!rootStore.aioseo.screen?.postType || 'web-story' === rootStore.aioseo.screen?.postType) {
		return false
	}

	const postEditorStore = usePostEditorStore()
	const optionsStore    = useOptionsStore()
	return !!(
		optionsStore.options.advanced?.truSeo &&
		shouldShowMetaBox(rootStore.aioseo.screen.postType) &&
		!postEditorStore.currentPost.isStaticPostsPage
	)
}

export const maybeUpdatePost = async (time = 900, run = true) => {
	debounce(async () => {
		const schemaStore     = useSchemaStore()
		const postEditorStore = usePostEditorStore()

		await maybeUpdatePostTitle(false)
		await maybeUpdatePostContent(false)
		await maybeUpdatePostExcerpt(false)
		await maybeUpdatePostSlug(false)
		await maybeUpdatePermalink(false)
		maybeUpdateTaxonomies(false)
		maybeUpdateTerm(false)
		maybeUpdateAttachment(false)

		debounce(schemaStore.updateSchemaOutput, Math.max(time * 2, 1800))

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
 * @param   {Object} selection The Selection object.
 * @returns {Object}           The reversed Selection object.
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
 * @param   {Object}  options          The options for finding the node.
 * @param   {Element} options.element  The element from which to start searching.
 * @param   {string}  options.property The CSS property to compare against.
 * @param   {string}  options.value    The value to match against the property value.
 * @returns {Element}                  The closest element with the specified property value, or the html root element if not found.
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

	el.classList.add('aioseo-app')
	el.style.position = 'absolute'
	el.style.zIndex = '999'
	el.style.display = 'flex'
	el.setAttribute('tabindex', -1)

	return el
}