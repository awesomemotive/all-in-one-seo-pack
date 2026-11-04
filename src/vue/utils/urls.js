export const makeUrlRelative = function (url, baseUrl) {
	if ('string' !== typeof url) {
		return url
	}

	const pattern = new RegExp('^' + baseUrl.replace(/\/$/, ''), 'i')
	return url.replace(pattern, '')
}

// Whitespace is part of the path, so it has to be encoded rather than dropped - dropping it points
// the redirect at a different path than the one requested (e.g. a `/a%20b` request logs as `/a b`
// and would be saved as `/ab`, which matches nothing).
// NOTE: deliberately does not trim, so a trailing space stays typeable. `Utils\Request::normalizeUrl()`
// trims on save.
export const encodeUrlWhitespace = function (url) {
	if ('string' !== typeof url) {
		return url
	}

	return url.replace(/\s/g, character => encodeURIComponent(character))
}

/**
 * Reverts {@link encodeUrlWhitespace}: decodes percent-encoded whitespace and leaves every other escape alone.
 *
 * @param   {string} url The URL.
 * @returns {string}     The URL with its whitespace decoded.
 */
export const decodeUrlWhitespace = function (url) {
	if ('string' !== typeof url) {
		return url
	}

	// One match per UTF-8 encoded character, so neighbouring escapes are left alone.
	const encodedCharacter = /%[0-7][0-9a-f]|%[c-d][0-9a-f]%[89ab][0-9a-f]|%e[0-9a-f](?:%[89ab][0-9a-f]){2}|%f[0-7](?:%[89ab][0-9a-f]){3}/gi

	return url.replace(encodedCharacter, sequence => {
		try {
			const decoded = decodeURIComponent(sequence)

			return /^\s$/.test(decoded) ? decoded : sequence
		} catch {
			return sequence
		}
	})
}

// Also drops a trailing dot on the host, since `SITE.` is the same host as `SITE`.
const toSchemeAndWwwAgnostic = url => url
	.replace(/^(?:https?:)?\/\/(?:www\.)?/i, '//')
	.replace(/^(\/\/[^/?#:]*?)\.+(?=[/?#:]|$)/, '$1')

// Collapses repeated slashes and resolves `.` and `..` segments, like a browser does before requesting the path.
const normalizePath = url => {
	const [ , path, suffix ] = url.match(/^([^?#]*)([\s\S]*)$/)
	const segments = path.replace(/\/{2,}/g, '/').split('/').slice(1)
	const output   = []
	segments.forEach((segment, index) => {
		const isLast = index === segments.length - 1
		if ('.' === segment || '..' === segment) {
			if ('..' === segment) {
				output.pop()
			}

			if (isLast) {
				output.push('')
			}

			return
		}

		output.push(segment)
	})

	return '/' + output.join('/') + suffix
}

/**
 * Returns a URL on the home URL (any scheme, with or without `www.`) as a normalized relative path; other URLs are returned untouched.
 *
 * @param   {string} url     The URL.
 * @param   {string} homeUrl The home URL.
 * @returns {string}         The relative path, or the original URL.
 */
export const getPathRelativeToHome = function (url, homeUrl) {
	if ('string' !== typeof url || 'string' !== typeof homeUrl || !homeUrl) {
		return url
	}

	// A single leading slash is already relative; `//` would be a protocol-relative host.
	if (/^\/(?!\/)/.test(url)) {
		return normalizePath(url)
	}

	const normalizedHome = toSchemeAndWwwAgnostic(homeUrl).replace(/\/$/, '').toLowerCase()
	const normalizedUrl  = toSchemeAndWwwAgnostic(url)
	if (!normalizedHome.startsWith('//') || !normalizedUrl.toLowerCase().startsWith(normalizedHome)) {
		return url
	}

	// The match has to end on a boundary, so a home path of `/wp` doesn't match `/wpfoo`.
	const rest = normalizedUrl.substring(normalizedHome.length)
	if ('' !== rest && !/^[/?#]/.test(rest)) {
		return url
	}

	return normalizePath('/' === rest.charAt(0) ? rest : '/' + rest)
}