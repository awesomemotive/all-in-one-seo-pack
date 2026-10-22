import { getCurrentInstance } from 'vue'

import { useSettingsStore } from '@/vue/stores'

/**
 * Reads the pre-publish panel's deep link into a sidebar tab (and optionally a section inside it).
 *
 * `settingsStore.metaBoxTabs.mainSidebar` is a one-shot intent, not a record of which tab is open:
 * the sidebar app is re-created on every open, so an intent left in the store replays on the next
 * open instead of landing on the tab list.
 *
 * @returns {Object} The intent reader and its clear.
 */
export const useMainSidebarIntent = () => {
	const settingsStore = useSettingsStore()
	const instance      = getCurrentInstance()

	// Closing the panel removes the app's container but not the app itself — that only happens
	// when the next open replaces it, so until then it keeps running its watchers against the
	// shared store. A detached instance must leave the intent for its replacement to act on.
	const isLive = () => false !== instance?.vnode?.el?.isConnected

	return {
		getIntent   : () => isLive() ? settingsStore.metaBoxTabs.mainSidebar : {},
		clearIntent : () => settingsStore.changeTabSettings({ setting: 'mainSidebar', value: {} })
	}
}