import {
	useRootStore
} from '@/vue/stores'

import {
	stripMarkup,
	sanitizeMarkup
} from '@/vue/utils/strings'

/**
 * Create an HTML element from a string.
 *
 * @since 4.2.1
 *
 * @param {string}  html    The HTML string.
 * @param {string}  element The wrapper element.
 * @param {boolean} trim    Whether to trim the HTML before parsing it.
 * @returns {Element}       The element.
 */
export const createElement = (html, element = 'div', trim = true) => {
	const div = document.createElement(element)
	div.innerHTML = sanitizeMarkup(trim ? html.trim() : html)
	return div
}

/**
 * Get the text from an HTML piece.
 *
 * @since   4.2.1
 * @version 4.6.5 Added the `removeBreaks` and `prop` parameters.
 *
 * @param 	{string|Object} html 		 The HTML to retrieve text.
 * @param 	{boolean} 	  	removeBreaks Whether to remove line breaks.
 * @param 	{string} 		prop 		 The property to get the text from.
 * @returns {string}           		     The text.
 */
export const getText = (html, removeBreaks = true, prop = 'textContent') => {
	if ('string' === typeof html) {
		if (removeBreaks) {
			html = html.replace(/[\t|\n]/gm, '')
		}
		html = createElement(html)
	}

	// Return the text inside the HTML.
	return html[prop] || html.textContent || html.innerText || ''
}

// Keep in sync with the server's stripInlineSvgs(). Every comment and tag is matched, so `<svg` inside a comment or an attribute value isn't read as a tag.
// The whitespace is HTML's: JavaScript's `\s` also matches a non-breaking space, which doesn't end a tag name.
const tokenPattern = /<!--(?:-?>|[\s\S]*?(?:--!?>|$))|<!\[CDATA\[|<(\/?)([a-z][^\t\n\f\r /<>]*)((?:[^<>"']|"[^"]*"|'[^']*')*)>/gi

// The HTML parser ends an SVG at these start tags (and at `</p>`/`</br>`), so the text after them is rendered.
const breakoutTags = /^(?:b|big|blockquote|body|br|center|code|dd|div|dl|dt|em|embed|h[1-6]|head|hr|i|img|li|listing|menu|meta|nobr|ol|p|pre|ruby|s|small|span|strong|strike|sub|sup|table|tt|u|ul|var)$/

// Outside an SVG, the parser reads these elements' content as text up to their end tag, so a `<svg` in it isn't a tag.
const rawTextTags = [ 'iframe', 'noembed', 'noframes', 'script', 'style', 'textarea', 'title', 'xmp' ]

const attributePattern = /([^\t\n\f\r />][^\t\n\f\r />=]*)(?:[\t\n\f\r ]*=[\t\n\f\r ]*(?:"[^"]*"|'[^']*'|[^\t\n\f\r >]*))?/g

/**
 * Removes inline SVGs, whose `<title>`/`<desc>` text is never rendered as prose, from an HTML string.
 *
 * NOTE: Runs on the raw string so entity-encoded markup (e.g. a code sample) is kept.
 *
 * @param   {string}  html      The HTML string.
 * @param   {boolean} scripting Whether `<noscript>` content is raw text, as in a browser with scripting on.
 * @returns {string}            The HTML without inline SVGs.
 */
export const stripSvgs = (html, scripting = true) => {
	if (!/<svg/i.test(html)) {
		return html
	}

	const rawText = scripting ? [ ...rawTextTags, 'noscript' ] : rawTextTags
	const stack   = [] // Open svg tags (their offset) and the integration points inside them (false), where HTML tags don't end the SVG.
	const ranges  = [] // [ start, end, replacement ] of each SVG to remove, in order.
	const record  = (start, end, replacement = '') => {
		// Drop the ranges of SVGs nested in this one.
		while (ranges.length && ranges.at(-1)[0] >= start) {
			ranges.pop()
		}
		ranges.push([ start, end, replacement ])
	}

	// The pattern is shared with the recursive `<noscript>` call, so the scan keeps its own position.
	let position = 0
	for (;;) {
		tokenPattern.lastIndex = position
		const tag = tokenPattern.exec(html)
		if (null === tag) {
			break
		}

		const start = tag.index
		const end   = start + tag[0].length
		position    = end
		if (undefined === tag[2]) {
			// In an SVG, a CDATA section is text up to its `]]>`, so a `</svg>` in it doesn't end the SVG.
			if (stack.length && tag[0].startsWith('<![')) {
				const cdataEnd = html.indexOf(']]>', end)
				position = -1 === cdataEnd ? html.length : cdataEnd + 3
			}

			continue // A comment.
		}

		const closer = '/' === tag[1]
		const name   = tag[2].toLowerCase()
		const inSvg  = 0 < stack.length && false !== stack.at(-1)
		// A `/` that ends an unquoted attribute value belongs to the value, not to a self-closing tag.
		const selfClosing = tag[3].endsWith('/') && !/=[\t\n\f\r ]*[^\t\n\f\r "']*$/.test(tag[3])

		if (!closer && !inSvg && 'plaintext' === name) {
			break // The rest of the content is text.
		}

		if (!closer && !inSvg && rawText.includes(name)) {
			const endTagPattern = new RegExp(`</${name}(?=[\\t\\n\\f\\r />])[^>]*>?`, 'gi')
			endTagPattern.lastIndex = end

			const endTag     = endTagPattern.exec(html)
			const contentEnd = endTag ? endTag.index : html.length
			position = endTag ? contentEnd + endTag[0].length : contentEnd

			// Without scripting a `<noscript>` holds HTML, so its own SVGs are stripped, but none can run past its end tag.
			if ('noscript' === name) {
				const content      = html.slice(end, contentEnd)
				const strippedHtml = stripSvgs(content, false)
				if (strippedHtml !== content) {
					record(end, contentEnd, strippedHtml)
				}
			}

			continue
		}

		if ('svg' === name) {
			if (!closer && !selfClosing) {
				stack.push(start)
			} else if (!closer && !stack.length) {
				record(start, end)
			} else if (closer && inSvg) {
				// Inside an integration point the parser ignores `</svg>`.
				record(stack.pop(), end)
			}

			continue
		}

		if ([ 'title', 'desc', 'foreignobject' ].includes(name)) {
			if (!closer && !selfClosing && inSvg) {
				stack.push(false)
			} else if (closer && false === stack.at(-1)) {
				stack.pop()
			}

			continue
		}

		let breaksOut = closer ? [ 'p', 'br' ].includes(name) : breakoutTags.test(name)
		// A `font` start tag breaks out only with a `color`, `face` or `size` attribute.
		if (!closer && 'font' === name && inSvg) {
			breaksOut = [ ...tag[3].matchAll(attributePattern) ].some((attribute) => [ 'color', 'face', 'size' ].includes(attribute[1].toLowerCase()))
		}

		if (breaksOut && inSvg) {
			let svgStart
			while (stack.length && false !== stack.at(-1)) {
				svgStart = stack.pop()
			}
			record(svgStart, start)
		}
	}

	const stripped = ranges.map(([ start, , replacement ], i) => html.slice(i ? ranges[i - 1][1] : 0, start) + replacement).join('')

	return stripped + html.slice(ranges.length ? ranges.at(-1)[1] : 0)
}

/**
 * Retrieves the `outerText` from a string or node.
 *
 * @since 4.4.6
 *
 * @param   {string|HTMLElement} html The string or node to retrieve the `outerText` property from.
 * @param   {boolean}            trim Whether to trim the HTML before parsing it. Pass false when the
 *                                    result's character offsets have to line up with the source HTML.
 * @returns {string}                  The text.
 */
export const getOuterText = (html, trim = true) => {
	if ('string' === typeof html) {
		html = createElement(html, 'div', trim)
	}

	return html?.outerText ?? ''
}

/**
 * Truncate a given string.
 *
 * @since 4.2.8
 *
 * @param {string} string The text.
 * @param {number} length Text max character length.
 * @param {string} ellipsis The ellipsis to use.
 * @returns {string} The shortened string.
 */
export const truncate = (string, length = 200, ellipsis = '&hellip;') => {
	if (!string) {
		return string
	}

	if (length < string.length) {
		string = string.substring(0, length).trim() + stripMarkup(ellipsis)
	}

	return string
}

/**
 * Get the schema graph from a string.
 *
 * @since 4.5.6
 *
 * @param   {string}      type          The schema graph to get.
 * @param   {string}      schemaContent The entire schema.
 * @returns {Object|false}              The schema graph object, or false if not found.
 */
export const parseSchemaByType = (type, schemaContent) => {
	try {
		const parseSchema = JSON.parse(schemaContent)
		if (!parseSchema) {
			return false
		}

		if (
			parseSchema['@type'] &&
			(
				type === parseSchema['@type'] ||
				(Array.isArray(parseSchema['@type']) && parseSchema['@type'].includes(type))
			)
		) {
			return parseSchema
		}

		if (Array.isArray(parseSchema['@graph'])) {
			const foundItem = parseSchema['@graph']
				.find(item => (Array.isArray(item['@type']) ? item['@type'].includes(type) : item['@type'] === type))

			return foundItem || false
		}

		return false
	} catch (_e) {
		return false
	}
}

/**
 * Retrieves the favicon URL from the DOM.
 *
 * @returns {string|null} The favicon URL, or null if not found.
 */
const getDomFaviconUrl = () => {
	const linkElements = Array.from(document.head.getElementsByTagName('link'))
	const faviconLink  = linkElements.find(link => [ 'icon', 'shortcut icon' ].includes(link.rel))

	return faviconLink ? faviconLink.href : null
}

/**
 * Extract the Google SERP snippet data from DOM.
 *
 * @since 4.2.8
 *
 * @returns {{favicon: string, url: string, title: string, description: string}} The data.
 */
export const getDomGoogleSerpData = () => {
	return {
		favicon     : getDomFaviconUrl() || '',
		url         : window.location.href,
		title       : document.title || '',
		description : document.querySelector('meta[name="description"]')?.content || ''
	}
}

/**
 * Extract the Facebook link preview data from DOM.
 *
 * @link https://ogp.me/
 * @link https://developers.facebook.com/tools/debug/
 *
 * @since 4.2.8
 *
 * @returns {{image: string, description: string, title: string, type: string}} The data.
 */
export const getFacebookSnippetData = () => {
	const facebookData = {
		description : document.querySelector('meta[property="og:description"]')?.content || '',
		image       : document.querySelector('meta[property="og:image"]')?.content || '',
		title       : document.querySelector('meta[property="og:title"]')?.content || '',
		type        : document.querySelector('meta[property="og:type"]')?.content || ''
	}

	if (!facebookData.title) {
		facebookData.title = document.title || ''

		if (!facebookData.title) {
			const rootStore = useRootStore()
			facebookData.title = rootStore.aioseo.urls.domain
		}
	}

	return facebookData
}

/**
 * Extract the Twitter card data from DOM.
 *
 * @link https://developer.x.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
 *
 * @since 4.2.8
 *
 * @returns {{image: string, description: string, title: string, card: string}} The data.
 */
export const getTwitterSnippetData = () => {
	return {
		card        : document.querySelector('meta[name="twitter:card"]')?.content || '',
		description : document.querySelector('meta[name="twitter:description"]')?.content || '',
		image       : document.querySelector('meta[name="twitter:image"]')?.content || '',
		title       : document.querySelector('meta[name="twitter:title"]')?.content || ''
	}
}