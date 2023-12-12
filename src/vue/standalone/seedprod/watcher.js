import {
	usePostEditorStore,
	useSeoRevisionsStore,
	useLicenseStore,
	loadPiniaStores
} from '@/vue/stores'

import { isEmpty } from 'lodash-es'

/**
 * Save SEO Settings when SeedProd editor is saved.
 *
 * @returns {void}.
 */
const handleEditorSave = () => {
	// We need to load the Pinia here since we are using the store outside an App.
	// So when using useRootStore, it will throw an error because the store wasn't initialized.
	loadPiniaStores()
	const postEditorStore = usePostEditorStore()
	if (isEmpty(postEditorStore.currentPost)) {
		return
	}

	postEditorStore.saveCurrentPost(postEditorStore.currentPost).then(() => {
		const licenseStore      = useLicenseStore()
		const seoRevisionsStore = useSeoRevisionsStore()
		if (!licenseStore.isUnlicensed) {
			seoRevisionsStore.fetch()
		}
	})
}

export default () => {
	// This hook will fire when the Save button is triggered.
	document.getElementById('seedprod-builder-save').addEventListener('click', handleEditorSave)
}