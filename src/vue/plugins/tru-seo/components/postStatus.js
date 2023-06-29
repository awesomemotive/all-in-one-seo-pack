import {
	usePostEditorStore
} from '@/vue/stores'

import { isBlockEditor } from '@/vue/utils/context'

/**
 * Returns the post status.
 *
 * @returns {string} Post status
 */
export const getPostStatus = () => {
	const postEditorStore = usePostEditorStore()
	let postStatus = postEditorStore.currentPost.postStatus
	if (isBlockEditor()) {
		postStatus = window.wp.data.select('core/editor').getCurrentPostAttribute('status')
	}

	return postStatus
}