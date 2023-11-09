import {
	loadPiniaStores,
	useOptionsStore,
	usePostEditorStore
} from '@/vue/stores'

/**
 * Determine whether or not we should show the metabox.
 *
 * @version 4.4.0
 * @version {next} Moved to its own utils file instead of the TruSEO one.
 *
 * @param   {string} postType The post type to check.
 * @returns {boolean}         Returns true if the meta box should be shown.
 */
export const shouldShowMetaBox = (postType = null) => {
	// We need to load the Pinia here since we are using the store outside an App.
	loadPiniaStores()

	const optionsStore = useOptionsStore()
	const postEditor   = usePostEditorStore()

	if (postType) {
		return !!(
			optionsStore.dynamicOptions.searchAppearance.postTypes[postType]?.advanced?.showMetaBox
		)
	}

	if (!postEditor.currentPost?.id) {
		return false
	}
	const pt       = postEditor.currentPost.postType
	const taxonomy = postEditor.currentPost.termType
	const showForPost = !!(
		pt &&
		'post' === postEditor.currentPost.context &&
		optionsStore.dynamicOptions.searchAppearance.postTypes[pt] &&
		optionsStore.dynamicOptions.searchAppearance.postTypes[pt]?.advanced?.showMetaBox
	)
	const showForTerm = !!(
		taxonomy &&
		'term' === postEditor.currentPost.context &&
		optionsStore.dynamicOptions.searchAppearance.taxonomies[taxonomy] &&
		optionsStore.dynamicOptions.searchAppearance.taxonomies[taxonomy]?.advanced?.showMetaBox
	)

	return showForPost || showForTerm
}