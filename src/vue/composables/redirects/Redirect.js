import {
	useRedirectsStore,
	useRootStore
} from '@/vue/stores'

import { sanitizeString } from '@/vue/utils/strings'
import { decodeUrlWhitespace, encodeUrlWhitespace, getPathRelativeToHome } from '@/vue/utils/urls'
import links from '@/vue/utils/links'

import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// The characters PHP's trim() strips. Unlike String.prototype.trim(), it keeps non-breaking and other Unicode spaces.
const phpTrimCharacters = [ ' ', '\t', '\n', '\r', '\0', '\x0B' ]

const phpLtrim = (string) => {
	let start = 0
	while (start < string.length && phpTrimCharacters.includes(string[start])) {
		start++
	}

	return string.slice(start)
}

const phpTrim = (string) => {
	let end = string.length
	while (0 < end && phpTrimCharacters.includes(string[end - 1])) {
		end--
	}

	return phpLtrim(string.slice(0, end))
}

export const useRedirect = () => {
	const redirectHasUnPublishedPost = (redirect) => {
		return redirect.post_id && 'publish' !== redirect.postStatus
	}

	const validateRedirect = (redirect) => {
		const errors = []

		// If we don't have a URL, let's not validate anything else.
		if (!redirect.url.url) {
			return errors
		}

		if (redirect.url.regex) {
			try {
				new RegExp(redirect.url.url)
			} catch (e) {
				errors.push(__('The regex syntax is invalid.', td))
				return errors
			}
		}

		if (!redirect.url.regex && !sanitizeString(redirect.url.url)) {
			errors.push(__('Your URL is invalid.', td))
			return errors
		}

		if ('http' === redirect.url.url.substr(0, 4)) {
			errors.push(__('Please enter a valid relative source URL.', td))
		}

		const placeholderRegex = /%[a-zA-Z_]+%/
		const percentEncodedRegex = /%[0-9A-Fa-f]{2}/ // Allow unicode characters in the URL

		if (redirect.url.url.match(placeholderRegex) && !redirect.url.url.match(percentEncodedRegex)) {
			errors.push(__('Permalinks are not currently supported.', td))
		}

		if ('/(.*)' === redirect.url.url || '^/(.*)' === redirect.url.url) {
			errors.push(__('This redirect is supported using the Relocate Site feature under Full Site Redirect tab.', td))
		}

		// Loop detection.
		if (redirect.url.url && redirect.url.url.length && redirect.targetUrl && redirect.targetUrl.length) {
			// Sources are always relative to the home URL, so a target on our own domain has to be relativized to be comparable.
			const relativeTarget = getPathRelativeToHome(redirect.targetUrl, useRootStore().aioseo.urls.home)

			// Remove the hash first, so Ignore Slash sees the slash in front of it.
			let compareSource = redirect.url.regex ? redirect.url.url : redirect.url.url.replace(/#.*?$/, ''),
				compareTarget = relativeTarget.replace(/#.*?$/, '')
			if (!redirect.url.regex) {
				// The source field holds whitespace encoded and the target field holds it raw.
				// Trim each the way it's saved: PHP's trim() for the source, esc_url_raw()'s ltrim() for the target.
				compareSource = encodeUrlWhitespace(phpTrim(decodeUrlWhitespace(compareSource)))
				compareTarget = encodeUrlWhitespace(phpLtrim(compareTarget))
			}
			compareSource = redirect.url.ignoreSlash ? links.unTrailingSlashIt(compareSource) : compareSource
			compareTarget = redirect.url.ignoreSlash ? links.unTrailingSlashIt(compareTarget) : compareTarget
			compareSource = redirect.url.ignoreCase ? compareSource.toLowerCase() : compareSource
			compareTarget = redirect.url.ignoreCase ? compareTarget.toLowerCase() : compareTarget

			if (compareSource === compareTarget ||
				(
					redirect.url.regex &&
					compareTarget.match(compareSource)
				)
			) {
				errors.push(__('Your source is the same as a target and this will create a loop.', td))
			}
		}

		// Protected path
		const redirectsStore = useRedirectsStore()
		if (0 < redirectsStore?.protectedPaths.length) {
			const normalizedPaths = redirectsStore.protectedPaths.map(path => path.replace(/\/$/, ''))
			if (redirect.url.url.match(new RegExp('^(' + normalizedPaths.join('|') + ')'))) {
				errors.push(__('Your source is a protected path and cannot be redirected.', td))
			}
		}

		return errors
	}

	return {
		redirectHasUnPublishedPost,
		validateRedirect
	}
}