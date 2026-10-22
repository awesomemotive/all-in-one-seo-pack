import { isBlockEditor } from '@/vue/utils/context'

import {
	useLicenseStore,
	useSeoRevisionsStore
} from '@/vue/stores'

let wasSaving = false

const isSaving = () => {
	const coreEditor = window.wp.data.select('core/editor')
	const editPost   = window.wp.data.select('core/edit-post')

	return (coreEditor.isSavingPost() && !coreEditor.isAutosavingPost()) || !!editPost?.isSavingMetaBoxes?.()
}

const refresh = () => {
	// On older WordPress versions (6.3 and below) the meta box save starts only once `isSavingPost()`
	// turns false, from another subscriber that may run after this one, so check again afterwards.
	if (isSaving()) {
		return
	}

	// Tolerate the selector being absent: skipping on an unknown outcome would leave the list
	// stale with nothing to signal it.
	const coreEditor = window.wp.data.select('core/editor')
	if (coreEditor.didPostSaveRequestSucceed && !coreEditor.didPostSaveRequestSucceed()) {
		return
	}

	const licenseStore      = useLicenseStore()
	const seoRevisionsStore = useSeoRevisionsStore()
	if (!licenseStore.isUnlicensed && seoRevisionsStore.hasPermission) {
		seoRevisionsStore.fetch()
	}
}

/**
 * Refreshes the SEO Revisions list once a post save, meta boxes included, has finished.
 *
 * Lives here rather than in the post settings view because that view is mounted more than once per
 * page (metabox, block editor sidebar, and a page builder sidebar on top of that), and a watcher per
 * instance means one request per instance.
 *
 * @returns {void}
 */
export default () => {
	if (!isBlockEditor()) {
		// Every other context already refreshes on its own: page builders re-fetch when their save
		// resolves ({@see handleEditorSave}), and the classic editor and term screens reload the page.
		return
	}

	window.wp.data.subscribe(() => {
		if (!window.wp.data.select('core/editor')) {
			return
		}

		const saving = isSaving()
		if (saving === wasSaving) {
			return
		}

		wasSaving = saving

		// The meta box save is where our data — so the new revision — is written.
		if (!saving) {
			setTimeout(refresh)
		}
	})
}