import {
	usePostEditorStore
} from '@/vue/stores'

import { maybeUpdatePost } from '@/vue/plugins/tru-seo/components/helpers'

export const watchBlockEditor = () => {
	// initial page load.
	maybeUpdatePost()

	// Subscribe to block editor changes.
	window.wp.data.subscribe(() => {
		maybeUpdatePost(500)

		// Post save/update
		const isSavingPost     = window.wp.data.select('core/editor').isSavingPost()
		const isAutosavingPost = window.wp.data.select('core/editor').isAutosavingPost()
		if (isSavingPost && !isAutosavingPost) {
			const postEditorStore   = usePostEditorStore()
			postEditorStore.isDirty = false

			maybeUpdatePost()
		}
	})
}