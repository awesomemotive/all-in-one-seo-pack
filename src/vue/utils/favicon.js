import { useRootStore } from '@/vue/stores'

/**
 * Returns a Google Favicons API URL for the given domain.
 *
 * On local installs (.test, .local, .localhost, etc.) and in dev mode, Google's
 * favicon API can't resolve the host and returns a 404, which pollutes the
 * browser console. In those cases, return an empty string so callers can
 * hide the img via v-if. See issue #7924.
 *
 * @param {string} domain    The domain to look up a favicon for.
 * @param {number} [size=32] The icon size, in pixels.
 * @returns {string}         A Google Favicons URL, or '' when no favicon should be loaded.
 */
export const getFaviconUrl = (domain, size = 32) => {
	if (!domain) {
		return ''
	}

	const rootStore = useRootStore()
	if (rootStore.aioseo?.data?.isDev || rootStore.aioseo?.data?.isLocal) {
		return ''
	}

	return `https://www.google.com/s2/favicons?sz=${size}&domain=${encodeURIComponent(domain)}`
}