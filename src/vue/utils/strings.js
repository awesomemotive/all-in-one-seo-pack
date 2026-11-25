import DOMPurify from 'dompurify'

import { sprintf } from '@/vue/plugins/translations'
import { decode } from 'he'
import { escape, escapeRegExp, isString } from 'lodash-es'

function decodeHtml (string) {
	while (string !== decode(string)) {
		string = decode(string)
	}
	return string
}

/**
 * Decodes only the entities that end with a semicolon, repeating until none are left.
 *
 * NOTE: A legacy name without `;`, like `&copy&b=2` or a trailing `&reg`, is a URL parameter, not an entity.
 *
 * @param   {string} string The string to decode.
 * @returns {string}        The decoded string.
 */
const decodeTerminatedEntities = (string) => {
	let decoded = string
	do {
		string  = decoded
		decoded = string.replace(/&(?:#\d+|#x[\da-f]+|[a-z][a-z\d]*);/gi, (entity) => decode(entity, { isAttributeValue: true }))
	} while (decoded !== string)

	return string
}

/**
 * Decodes the entities in a URL, then strips any markup.
 *
 * @param   {string} url The URL.
 * @returns {string}     The URL without entities or markup.
 */
const stripUrlMarkup = (url) => DOMPurify.sanitize(decodeTerminatedEntities(url), {
	ALLOWED_TAGS : [],
	ALLOWED_ATTR : []
})

/**
 * Sanitizes a string by stripping out everything that contains dangerous HTML to prevent XSS attacks.
 * In contrary to the `sanitizeMarkup` function, we strip the HTML tags completely.
 *
 * @since   ?
 * @version 4.8.2 Refactored to use DOMPurify.
 *
 * @param   {string} string       The string to sanitize.
 * @param   {boolean} decodeFinal Whether to decode the final string if requested.
 * @returns {string} 		      Returns the sanitized string.
 */
export const stripMarkup = (string, decodeFinal = false) => {
	if (!isString(string)) {
		return ''
	}

	// Decode HTML entities to prevent XSS attacks.
	string = decodeHtml(string)

	const sanitized = DOMPurify.sanitize(string, {
		ALLOWED_TAGS : [],
		ALLOWED_ATTR : []
	})

	// DOMPurify re-encodes entities for HTML safety, so decode the result to get plain text if requested.
	return decodeFinal ? decode(sanitized) : sanitized
}

/**
 * Sanitizes a string by stripping out everything that contains dangerous HTML to prevent XSS attacks.
 * In contrary to the `stripMarkup` function, we do not strip the HTML tags completely.
 *
 * @since   4.4.6
 * @version 4.8.2 Refactored to use DOMPurify.
 *
 * @param   {string} string The string to sanitize.
 * @returns {string}        Returns the sanitized string.
 */
export const sanitizeMarkup = (string) => {
	if (!isString(string)) {
		return ''
	}

	return DOMPurify.sanitize(decodeHtml(string))
}

/**
 * Sanitizes markup that comes from a remote source (our API, an LLM, a third-party service) before it is rendered.
 *
 * NOTE: Unlike `sanitizeMarkup`, entities are not decoded first, so encoded text such as `&lt;head&gt;` stays visible.
 *
 * @param   {string} string The markup to sanitize.
 * @returns {string}        Returns the sanitized markup.
 */
export const sanitizeRemoteMarkup = (string) => {
	if (!isString(string)) {
		return ''
	}

	return DOMPurify.sanitize(string, {
		ADD_ATTR    : [ 'target' ],
		FORBID_TAGS : [ 'style', 'form', 'input', 'button', 'textarea', 'select', 'option', 'iframe', 'object', 'embed' ],
		FORBID_ATTR : [ 'style' ]
	})
}

/**
 * Escapes a string so that it is rendered as text when it is inserted into markup.
 *
 * NOTE: Entities are decoded first so that an already encoded string doesn't end up double escaped.
 * Only those that end with `;` are, so a URL's `&section=` or `&copy&b=2` isn't read as an entity.
 *
 * @param   {string} string The string to escape.
 * @returns {string}        Returns the escaped string.
 */
export const escapeHtml = (string) => {
	if (!isString(string)) {
		return ''
	}

	return escape(decodeTerminatedEntities(string))
}

const RAW_HTML = Symbol('rawHtml')

/**
 * Marks a value as trusted markup so htmlSprintf() interpolates it as-is.
 *
 * Use this only for markup we built ourselves. Anything that originated with a user, a post, or
 * a remote API must go in unwrapped so it gets escaped.
 *
 * @since 5.0.6
 *
 * @param  {*}      value The already-safe markup.
 * @return {Object}       The wrapped value.
 */
export const raw = (value) => ({
	[RAW_HTML] : true,
	value      : null === value || undefined === value ? '' : String(value)
})

/**
 * sprintf() for templates that contain markup. Every argument is escaped unless it is wrapped in
 * raw(), so a URL or a post title cannot break out of the attribute or element it lands in.
 *
 * @since 5.0.6
 *
 * @param  {string} template The format string.
 * @param  {...*}   args     The values to interpolate.
 * @return {string}          The formatted markup.
 */
export const htmlSprintf = (template, ...args) => sprintf(
	template,
	...args.map(arg => {
		if (arg && arg[RAW_HTML]) {
			return arg.value
		}

		return escapeHtml(null === arg || undefined === arg ? '' : String(arg))
	})
)

/**
 * Sanitizes a URL by ensuring it is valid and only allows the http, https, mailto and tel
 * protocols. Document-relative URLs are resolved against the current page.
 *
 * @param   {string} url The URL to sanitize.
 * @returns {string}     Returns the sanitized URL.
 */
export const sanitizeUrl = (url) => {
	if (!url) {
		return '#'
	}

	// If relative URL, return it as is
	if (url.startsWith('#') || url.startsWith('/')) {
		return stripUrlMarkup(url)
	}

	try {
		// Resolve document-relative URLs like "admin.php?page=aioseo" against the current page
		// instead of rejecting them. A URL carrying its own scheme ignores the base, so the
		// protocol check below still sees javascript: and data: for what they are.
		const base        = 'undefined' === typeof window ? undefined : window.location.href
		const urlInstance = new URL(url, base)
		if (urlInstance.protocol && ![ 'http:', 'https:', 'mailto:', 'tel:' ].includes(urlInstance.protocol)) {
			return '#'
		}

		return stripUrlMarkup(urlInstance.toString())
	} catch (e) {
		return '#'
	}
}

/**
 * Strips the markup from a title and wraps every match of the search term in a highlight span.
 *
 * @param   {string} title  The title.
 * @param   {string} search The search term.
 * @returns {string}        Returns escaped markup that is safe to render.
 */
export const highlightSearchTerm = (title, search) => {
	if (!isString(title)) {
		return ''
	}

	// Strip tags before decoding, unlike stripMarkup(): titles saved by Authors hold `&lt;`, which must stay text.
	const text = decode(DOMPurify.sanitize(title, { ALLOWED_TAGS: [], ALLOWED_ATTR: [] }))
	const term = isString(search) ? search : ''
	if (!term) {
		return escape(text)
	}

	// The capture group keeps the matches in the split result, at the odd indexes.
	return text.split(new RegExp(`(${escapeRegExp(term)})`, 'gi'))
		.map((part, index) => index % 2 ? `<span class="search-term">${escape(part)}</span>` : escape(part))
		.join('')
}