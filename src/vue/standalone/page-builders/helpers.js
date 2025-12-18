import {
	useLicenseStore,
	usePostEditorStore,
	useSeoRevisionsStore
} from '@/vue/stores'

import { isEqual, isEmpty } from 'lodash-es'
import { maybeUpdatePost } from '@/vue/plugins/tru-seo/components/helpers'

/**
 * Get the DOM node for the iframe.
 *
 * @param   {string} iframeId The DOM ID for the iframe.
 * @returns {Object|null}     The DOM node or null if not found.
 */
export const iframeRef = iframeId => {
	const frameRef = document.getElementById(iframeId)
	if (!frameRef) {
		return null
	}

	return frameRef?.contentWindow
		? frameRef.contentWindow.document
		: frameRef.contentDocument
}

/**
 * Handler function for the editor.
 *
 * @returns {Function} An asynchronous handler function that takes an object with `editorData` as an argument.
 */
export const createEditorChangeHandler = () => {
	// This variable is private to the closure.
	let previousEditorData = null

	// Return the actual handler function that has access to previousEditorData.
	return async ({ editorData }) => {
		try {
			// Compare the stored previous data with current data.
			if (
				null === previousEditorData ||
				!isEqual(previousEditorData, editorData)
			) {
				await maybeUpdatePost()
			}

			// Update the stored data for the next call with a deep clone.
			previousEditorData = JSON.parse(JSON.stringify(editorData))
		} catch (error) {
			console.error(error)
		}
	}
}

export const handleEditorSave = (builder = null) => {
	const postEditorStore = usePostEditorStore()
	if (isEmpty(postEditorStore.currentPost)) {
		return
	}

	const saveCurrentPost = () => {
		// Clear isDirty flag as soon as save is initiated.
		postEditorStore.isDirty = false

		postEditorStore.saveCurrentPost(postEditorStore.currentPost).then(() => {
			const licenseStore      = useLicenseStore()
			const seoRevisionsStore = useSeoRevisionsStore()
			if (!licenseStore.isUnlicensed) {
				seoRevisionsStore.fetch()
			}
		})
	}

	if ('elementor' === builder) {
		/**
		 * Do not save our data to a revision.
		 * WordPress saves the metadata to the post parent, not the revision. See `update_post_meta`.
		 * Most likely this is because saving a revision on a published post will unpublish in WordPress itself.
		 * But Elementor does not unpublish your post when you save a draft.
		 * This would result in AIOSEO data being live while saving a draft.
		 */
		if (window.elementor.config.document.id === window.elementor.config.document.revisions.current_id) {
			saveCurrentPost()
		}
	} else {
		saveCurrentPost()
	}
}

/**
 * Injects CSS into the parent document.
 *
 * @param {string} cssText The CSS text or URL to inject.
 * @param {string} id      The ID of the element to inject the CSS into.
 * @returns {void}
 */
export const injectCSSToParent = (cssText, id) => {
	const $parent = window.parent.document

	const existingStyle = $parent.getElementById(id)
	if (existingStyle) {
	  existingStyle.remove()
	}

	try {
		new URL(cssText)

		const link = $parent.createElement('link')
		link.id = id
		link.href = cssText
		link.rel = 'stylesheet'
		$parent.head.appendChild(link)
	} catch (_error) {
		const style = $parent.createElement('style')
		style.id = id
		style.textContent = cssText
		$parent.head.appendChild(style)
	}
}