import {
	usePostEditorStore,
	useRootStore
} from '@/vue/stores'

import { shouldShowMetaBox } from '@/vue/utils/metabox'
import {
	isBlockEditor,
	isClassicEditor,
	isClassicNoEditor,
	isWooCommerceProduct
} from '@/vue/utils/context'
import { maybeUpdatePost } from '@/vue/plugins/tru-seo/components/helpers'
import { maybeUpdateTerm } from '@/vue/plugins/tru-seo/components/term'
import { maybeUpdateAttachment } from '@/vue/plugins/tru-seo/components/attachments'
import {
	watchClassicEditor,
	watchBlockEditor,
	watchWooCommerce
} from '@/vue/plugins/tru-seo/context'

export default (app, populateHiddenField = true) => {
	if (!shouldShowMetaBox()) {
		return
	}

	// Update post analysis on initial page load.
	maybeUpdatePost()

	const postEditorStore = usePostEditorStore()
	if ('term' === postEditorStore.currentPost.context) {
		(document.querySelector('#edittag') || {})?.addEventListener('submit', () => {
			postEditorStore.isDirty = false
		})

		maybeUpdateTerm()
	} else {
		// Make sure the API is available.
		const rootStore = useRootStore()
		rootStore.ping()

		if (populateHiddenField) {
			postEditorStore.savePostState()
		}

		if (isBlockEditor()) {
			const interval = window.setInterval(() => {
				const post = window.wp.data.select('core/editor').getCurrentPost()
				if (post.id) {
					window.clearInterval(interval)
					watchBlockEditor()
				}
			}, 50)
		} else {
			if (isWooCommerceProduct()) {
				watchWooCommerce()
			}

			if (isClassicEditor() || isClassicNoEditor()) {
				(document.querySelector('#post') || {})?.addEventListener('submit', () => {
					postEditorStore.isDirty = false
				})

				watchClassicEditor()
			}

			// Attachemet Page
			maybeUpdateAttachment()
		}
	}
}