// Which of a WPBakery element's params hold the text a reader sees, and whether that text is the
// element's own or borrowed from the post. Kept free of `window.vc` so both rules are testable
// without an editor: the adapter passes in what it read.

const ALWAYS_CONTENT = new Set([ 'textarea_html', 'textarea' ])

// `el_class` and `el_id` are textfields whose values CAN appear in the rendered text (a class
// named after the copy, an anchor id echoing a heading), so the match below would take them.
const NEVER_CONTENT = new Set([ 'el_class', 'el_id', 'css' ])

// Textfields that configure an element rather than print text (`h4_el_class`, `img_size`,
// `flickr_id`, a video's `link`, a line chart's `x_values`, a grid's `meta_key`, a progress bar's
// `units`). The back-end builder's summary labels echo some of them, and the stored-value rule
// below has no render to refuse them with.
const SETTING_NAME = /(?:^|_)(?:el_class|el_id|id|size|url|link|src|width|onclick_code|values|units|key)$/

// Decoded in two passes with `&amp;` last, so `&amp;lt;` survives as the literal `&lt;` the
// author typed instead of collapsing to `<`.
const NAMED_ENTITIES = [
	[ /&nbsp;/g, ' ' ],
	[ /&lt;/g, '<' ],
	[ /&gt;/g, '>' ],
	[ /&quot;/g, '"' ],
	[ /&#0?39;|&apos;|&#x27;/gi, '\'' ]
]

// Tags are stripped BEFORE entities are decoded: a decoded `<` would otherwise open a tag that
// was never in the markup and swallow the text after it.
const normalize = (value) => {
	let text = String(value ?? '').replace(/<[^>]*>/g, ' ')

	NAMED_ENTITIES.forEach(([ pattern, replacement ]) => {
		text = text.replace(pattern, replacement)
	})

	return text.replace(/&amp;/g, '&').replace(/\s+/g, ' ').trim()
}

// Whitespace is dropped rather than folded: a stripped inline tag leaves a space the render does
// not have (`<b>bold</b>, text`), and wpautop adds block breaks the stored value does not.
const compact = (value) => normalize(value).replace(/\s+/g, '')

/**
 * Whether a param's text is among the text an element rendered.
 * NOTE: both are compared as decoded text -- a stored `&mdash;` never matches its rendered dash.
 *
 * @param {string} storedText    The param's stored value, projected to text.
 * @param {string} projectedText The element's rendered text.
 * @returns {boolean} True when it is, or when there is no text to look for.
 */
export const isRenderedIn = (storedText, projectedText) => {
	const needle = compact(storedText)

	return !needle || compact(projectedText).includes(needle)
}

// WPBakery's own rule for whether a param is shown in the edit form, and the shortcode templates
// print a param only under the same condition: a button label is stored with its default whether
// or not the button is enabled.
const isDependencyMet = (defs, def, stored, depth = 0) => {
	const dependency = def?.dependency

	if (!dependency?.element || 10 < depth) {
		return true
	}

	const parent = defs.find(candidate => candidate?.param_name === dependency.element)

	if (parent && !isDependencyMet(defs, parent, stored, depth + 1)) {
		return false
	}

	const value = String(stored[dependency.element] ?? '')

	if (dependency.not_empty) {
		return '' !== value
	}

	if (dependency.is_empty) {
		return '' === value
	}

	if (undefined !== dependency.value) {
		return [].concat(dependency.value).map(String).includes(value)
	}

	if (undefined !== dependency.value_not_equal_to) {
		return ![].concat(dependency.value_not_equal_to).map(String).includes(value)
	}

	return true
}

// Whether an unrendered value reads as text. A number or a URL is refused: settings such as a
// tab's `active_section` or a row's `parallax_speed_bg` are stored with numeric defaults no editor
// prints, so a number counts only where the render echoes it, as a pricing table's price does.
const isTextValue = (value) => /\p{L}/u.test(value) && !/^(?:[a-z][a-z\d+.-]*:)?\/\//i.test(value)

/**
 * Names the params of one element that carry reader-visible text.
 *
 * A `textfield` cannot be classified from the element map alone: `vc_btn`'s label is one and so
 * is `el_class`, and `holder` does not separate them -- only 1 of 22 textfields across five core
 * elements declares one. Matching the stored value against the text the element actually
 * rendered is the test that does separate them, and it extends to third-party elements for free.
 *
 * A param that fails the match gets no unit, so it is unpainted and unwritable. Its text still
 * reaches the analyser only when the element has exactly one unit, whose node is the whole element.
 *
 * The back-end builder renders most elements as a summary that shows only the `admin_label`
 * params, so there the match cannot be the only test: a textfield it misses is still content when
 * the edit form would show it and its value holds a letter and is not a URL.
 *
 * @param {?Object} mappedDef     The element definition from `vc.getMapped()`.
 * @param {?Object} params        The model's stored params.
 * @param {string}  projectedText The element's rendered text.
 * @param {boolean} [trustStored] Whether the render may omit params, as the back-end builder does.
 * @returns {string[]} Param names, in the map's own order.
 */
export const contentParamNames = (mappedDef, params, projectedText, trustStored = false) => {
	const defs = mappedDef?.params

	if (!Array.isArray(defs)) {
		return []
	}

	const haystack = normalize(projectedText)
	const stored   = params || {}

	return defs
		.filter(def => {
			const name = def?.param_name

			if (!name || NEVER_CONTENT.has(name)) {
				return false
			}

			if (ALWAYS_CONTENT.has(def.type)) {
				return true
			}

			if ('textfield' !== def.type || SETTING_NAME.test(name)) {
				return false
			}

			const value = normalize(stored[name])

			// An empty value would match every projection, including an empty one.
			if (!value) {
				return false
			}

			if (haystack.includes(value)) {
				return true
			}

			return trustStored && isDependencyMet(defs, def, stored) && isTextValue(value)
		})
		.map(def => def.param_name)
}

/**
 * Whether the element's text comes from the post rather than from its own params.
 *
 * `vc_custom_heading` offers "Custom text" (empty) or "Post or Page Title" (`post_title`). Under
 * the latter, `params.text` is not what the reader sees, so the unit is analysed from the render
 * and refused for writes -- the same standing Elementor gives a dynamic-bound unit.
 *
 * @param {string}  tag    The shortcode tag.
 * @param {?Object} params The model's stored params.
 * @returns {boolean} True when the text is dynamically sourced.
 */
export const isDynamicallySourced = (tag, params) => 'vc_custom_heading' === tag && !!(params || {}).source
// A `param_group` is stored URL-encoded JSON, though a model may already hold the parsed rows.
const decodeGroupRows = (value) => {
	if (Array.isArray(value)) {
		return value
	}

	try {
		const rows = JSON.parse(decodeURIComponent(String(value ?? '')))

		return Array.isArray(rows) ? rows : []
	} catch (_e) {
		return []
	}
}

// Elements whose render prints text that no stored param holds, so the back-end builder, which
// analyses stored params, never sees it. Each test is the template's own condition for printing.
// `vc_raw_html` is not here: neither editor analyses it, so the Frontend Editor is no better.
const RENDER_ONLY_TEXT = new Map([
	// The bar labels.
	[ 'vc_progress_bar', (params) => decodeGroupRows(params.values).some(row => /\p{L}/u.test(String(row?.label ?? ''))) ]
])

/**
 * Names the elements on a page whose text the back-end builder cannot fully analyse.
 *
 * @param {?Object[]} elements `{ tag, params }` per element, in page order.
 * @returns {string[]} Shortcode tags, each once, in page order.
 */
export const partiallyAnalysedTags = (elements) => {
	const tags = new Set()

	for (const element of (elements || [])) {
		const test = RENDER_ONLY_TEXT.get(element?.tag)

		if (test && test(element.params || {})) {
			tags.add(element.tag)
		}
	}

	return [ ...tags ]
}