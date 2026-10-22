import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { getScrapedContent } from './dom-helpers'

// Wireframe-mode content cache and guards.
let _cachedWireframeContent = '',
	_isRefreshing = false,
	_isModalOpen = false

document.addEventListener('aioseo-pagebuilder-modal-state', (e) => {
	_isModalOpen = !!e.detail?.isOpen
})

/**
 * Get the Divi 5 app window.
 *
 * @returns {Window|null} The Divi 5 app window.
 */
export const getDivi5AppWindow = () => document.querySelector('iframe#et-vb-app-frame')?.contentWindow

/**
 * Get the Divi 5 data registry.
 *
 * @returns {Object|undefined} The `divi.data` registry.
 */
const getDivi5Data = () => getDivi5AppWindow()?.divi?.data

/**
 * Get a setting from the `divi/page-settings` store.
 *
 * @param   {string} setting The setting name.
 * @returns {any}            The setting value.
 */
export const getDivi5Setting = (setting) => getDivi5Data()?.select('divi/page-settings')?.getSetting(setting)

/**
 * Get a setting from the `divi/settings` store.
 *
 * @param   {string|string[]} path The setting path (e.g., 'post' or ['post', 'status']).
 * @returns {any}                  The setting value.
 */
export const getDivi5SettingsStoreSetting = (path) => getDivi5Data()?.select('divi/settings')?.getSetting(path)

/**
 * Check if Divi 5 is ready.
 *
 * @returns {boolean} True if ready.
 */
export const isDivi5Ready = () => !!getDivi5Data()?.select('divi/page-settings')

/**
 * Check if the Divi 5 builder shows the Wireframe view, where modules render no content.
 *
 * @returns {boolean} True in Wireframe view.
 */
export const isDivi5Wireframe = () => 'wireframe' === getDivi5Data()?.select('divi/app-ui')?.getView()

/**
 * Get the Divi 5 featured image ID.
 *
 * @returns {number} The image ID.
 */
export const getDivi5FeaturedImageId = () => getDivi5Setting('postImage') || 0

/**
 * Get the Divi 5 permalink.
 *
 * @returns {string} The permalink.
 */
export const getDivi5Permalink = () => getDivi5SettingsStoreSetting([ 'post', 'url' ]) || ''

/**
 * Get the Divi 5 title.
 *
 * @returns {string} The title.
 */
export const getDivi5Title = () => getDivi5Setting('postTitle') || ''

/**
 * Get the Divi 5 excerpt.
 *
 * @returns {string} The excerpt.
 */
export const getDivi5Excerpt = () => getDivi5Setting('postExcerpt') || ''

/**
 * Get the Divi 5 slug.
 *
 * @returns {string} The slug.
 */
export const getDivi5Slug = () => {
	try {
		const slug = new URL(getDivi5Permalink()).pathname.replace(/\/$/, '').split('/').pop()
		if (slug) {
			return slug
		}
	} catch {
		// URL parsing failed; fall through to title-based fallback.
	}

	return cleanForSlug(getDivi5Title())
}

/**
 * Get the Divi 5 post ID.
 *
 * @returns {number} The post ID.
 */
export const getDivi5PostId = () => getDivi5SettingsStoreSetting([ 'post', 'id' ]) || 0

/**
 * Get the Divi 5 post status.
 *
 * @returns {string} The post status.
 */
export const getDivi5PostStatus = () => getDivi5SettingsStoreSetting([ 'post', 'status' ]) || 'publish'

/**
 * Get the Divi 5 content.
 * In visual mode, scrapes the DOM directly.
 * In wireframe mode, returns cached content populated by {@link refreshDivi5Content}.
 *
 * @returns {string} The rendered HTML content.
 */
export const getDivi5Content = () => {
	if (!isDivi5Wireframe()) {
		return getScrapedContent()
	}

	return _cachedWireframeContent
}

/**
 * Whether a wireframe content refresh is in progress (view switching).
 * Used by the watcher to skip subscribe callbacks caused by our own view switches.
 *
 * @returns {boolean} True if refreshing.
 */
export const isRefreshingDivi5Content = () => _isRefreshing

/**
 * Refresh the wireframe content cache by briefly switching to visual mode.
 * Only switches views when the AIOSEO modal is open to avoid UI disruption.
 *
 * @returns {Promise<boolean>} Whether up-to-date content is available for analysis.
 */
export const refreshDivi5Content = async () => {
	if (_isRefreshing) {
		return false
	}

	const diviData = getDivi5Data()
	if (!diviData) {
		_cachedWireframeContent = ''
		return false
	}

	const currentView = diviData.select('divi/app-ui').getView()
	if ('wireframe' !== currentView) {
		return true
	}

	if (!_isModalOpen) {
		return false
	}

	_isRefreshing = true

	await diviData.dispatch('divi/app-ui').setView('desktop')

	_cachedWireframeContent = getScrapedContent()

	await diviData.dispatch('divi/app-ui').setView(currentView)

	_isRefreshing = false

	return true
}

/**
 * Save the current Divi 5 post via the serialized-post store.
 *
 * @returns {Promise<void>} Resolves when the save completes.
 */
export const saveDivi5 = async () => {
	const syncToServer = getDivi5Data()?.dispatch('divi/serialized-post')?.syncToServer
	if ('function' !== typeof syncToServer) {
		return
	}

	try {
		const syncType = 'publish' === getDivi5PostStatus() ? 'publish' : 'draft'

		await syncToServer({ syncType })
	} catch (e) {
		console.error('Failed to trigger save via API:', e)
	}
}