/* globals ETBuilderBackendDynamic */
import {
	usePostEditorStore,
	useSeoRevisionsStore
} from '@/vue/stores'

import { isEqual, set } from 'lodash-es'
import { debounce } from '@/vue/utils/debounce'
import { maybeUpdatePost as updatePostData } from '@/vue/plugins/tru-seo/components/helpers'
import { getEditorData } from './helpers'

let editorData = {}

/**
 * Run TruSEO analysis when content updates.
 *
 * @returns {void}.
 */
const handleEditorChange = () => {
	// Don't run the analysis if we're in wireframe mode.
	if (document.documentElement.classList.contains('et-fb-preview--wireframe')) {
		return
	}

	const oldData = { ...editorData }
	const data = getEditorData()

	if (!isEqual(oldData, data)) {
		editorData = data
		updatePostData()
	}
}

/**
 * Save SEO Settings when Divi editor is saved.
 *
 * @returns {void}.
 */
const handleEditorSave = () => {
	const postEditorStore = usePostEditorStore()
	postEditorStore.saveCurrentPost(postEditorStore.currentPost).then(() => {
		const seoRevisionsStore = useSeoRevisionsStore()
		seoRevisionsStore.fetch()
	})
}

export default () => {
	// First load and analyze the content.
	handleEditorChange()

	// This hook will fire when the Divi content changes.
	window.addEventListener('message', (event) => {
		const etEvent = event.data.eventType
		if ('et_fb_section_content_change' === etEvent) {
			debounce(handleEditorChange, 1000)
		}
	})

	// This hook will fire when the Divi settings changes.
	window.wp && window.wp.hooks.addFilter(
		'et.builder.store.setting.update',
		'aioseo',
		(value, setting) => {
			if (value) {
				switch (setting) {
					case 'et_pb_post_settings_title':
						set(ETBuilderBackendDynamic, 'postTitle', value)
						debounce(handleEditorChange, 1000)
						break
					case 'et_pb_post_settings_excerpt':
						set(ETBuilderBackendDynamic, 'postMeta.post_excerpt', value)
						debounce(handleEditorChange, 1000)
						break
				}
			}
			return value
		}
	)

	// This hook will fire when the Save button is triggered.
	document.querySelector('.et-fb-button--save-draft, .et-fb-button--publish').addEventListener('click', handleEditorSave)
}