import { buildStructuredMarkup } from '@/vue/plugins/tru-seo/highlighter/structuredMarkup'
import { findSentenceRanges, matchCase, wordBoundaryPattern } from '@/vue/utils/regex'
import { isWPBakeryBuilderActive } from '@/vue/utils/context'
import { requestPostUpdate } from '@/vue/plugins/tru-seo/spellingSuggestions'

import { editStoredBlock, editStoredHtml, parseInert } from './storedHtmlEdit'
import { buildMarkupProjection, buildTextStream, escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import {
	alignProjectedMarks,
	clearDecorations as clearDomDecorations,
	decorateNode,
	ensureDecorationStyles,
	foldNbsp
} from './domDecorator'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'

import { contentParamNames, isDynamicallySourced, isRenderedIn, partiallyAnalysedTags } from './wpbakeryUnits'

/**
 * WPBakery Page Builder adapter.
 *
 * Two surfaces, one model. `window.vc.shortcodes` is authoritative in BOTH, and it always lives
 * in the ADMIN window; what changes is where the content is rendered:
 *
 *   - `admin_frontend_editor` -- the page renders inside `#vc_inline-frame`, so the content
 *     document is the iframe's. Structurally the same split as Elementor's canvas.
 *   - `admin_page` -- the back-end builder renders element previews into `#wpbakery_content`,
 *     a metabox in the same document as the model.
 *
 * Every member that answers "where is the content" must therefore resolve the CONTENT document
 * rather than reaching for `document`, because in the frontend editor those are different realms.
 * Nothing here may compare across them with `instanceof`.
 */

const CONTENT_FRAME = '#vc_inline-frame'

// The builder's own content root in the back-end editor, hosted in the metabox WPBakery adds
// beside the classic editor. Ordered most-specific first; a rename upstream costs paint, not
// correctness ({@see utils/context.js:isWPBakeryBuilderActive}).
const BACKEND_ROOTS = [ '#wpbakery_content', '#vc_content-wrapper', '#wpb_wpbakery' ]

const isFrontendEditor = () => 'admin_frontend_editor' === window.vc_mode

const getContentFrame = () => document.querySelector(CONTENT_FRAME)

const getContentDocument = () => {
	if (!isFrontendEditor()) {
		return document
	}

	// Same-origin by construction (the frontend editor loads the site's own page), but a frame
	// mid-navigation answers null and must not be mistaken for "use the admin document".
	return getContentFrame()?.contentDocument || null
}

const getContentWindow = () => {
	if (!isFrontendEditor()) {
		return window
	}

	return getContentFrame()?.contentWindow || null
}

const getContentRoot = () => {
	const doc = getContentDocument()
	if (!doc) {
		return null
	}

	if (isFrontendEditor()) {
		return doc.body || null
	}

	for (const selector of BACKEND_ROOTS) {
		const node = doc.querySelector(selector)

		if (node) {
			return node
		}
	}

	return null
}

const getModels = () => {
	const collection = window.vc?.shortcodes

	if (!collection || 'function' !== typeof collection.each) {
		return []
	}

	const models = []

	collection.each(model => models.push(model))

	return models
}

const getMapped = (tag) => {
	try {
		return window.vc?.getMapped?.(tag) || null
	} catch (_e) {
		// getMapped throws for a tag whose element was never mapped (a shortcode left behind by a
		// deactivated addon), and one such element must not cost the units after it.
		return null
	}
}

const getElementNode = (ownerId) => getContentDocument()?.querySelector(`[data-model-id="${ownerId}"]`) || null

// WPBakery renders its per-element controls INSIDE the element, unlike Elementor, so the chrome is
// dropped as whole subtrees rather than by class name. Class attributes are left exactly as
// authored: the analyzer's own filter families read `class`, and while its list carries
// `elementor-` content classes it carries no `vc_` ones, so there is nothing here to keep in step
// with -- and a prefix matcher would be the wrong tool anyway
// ({@see highlighter/textStream.js:classAttribute}).
const CHROME_CLASS = /^vc_control|^vc_controls|^vc_element-name|^vc_empty-element|^vc_ui-/

// The back-end builder does not RENDER an element it cannot preview -- it prints a summary of the
// element's params instead:
//
//   <h4 class="wpb_element_title">… Custom heading</h4>
//   <span class="vc_admin_label admin_label_text"><label>Content</label>: Community Forum …</span>
//
// So the preview's text is `Custom heading Content: … CSS animation:` -- the element's NAME and its
// param LABELS, none of which a reader ever sees. Left in, they reach analysis as prose.
const SUMMARY_CLASS = /^wpb_element_title|^vc_admin_label/

// Boxes other plugins print into the content through `the_content`. The frontend editor's first
// render runs that filter, so a table of contents lands INSIDE whichever element holds the first
// heading -- text the author cannot edit there, which the back-end builder and every later
// re-render of the element never show.
const INJECTED_SELECTOR = '#toc_container, #ez-toc-container, .ez-toc-container, .lwptoc'

// A Widget Sidebar element prints a whole widget area after its own title: recent posts, comment
// authors, category names. That is site data the author cannot edit in the element, and the
// back-end builder never renders it, so only the title is kept.
const WIDGET_AREA_SELECTOR = '.wpb_widgetised_column > .wpb_wrapper > :not(.wpb_widgetised_column_heading)'

// Text the template builds from settings rather than copy: a progress bar prints each bar's value
// and `units` (`60%`) inside its label's `<small>`.
const GENERATED_SELECTOR = '.vc_label_units'

// The same goes for the elements that print live site data under their title. The WordPress Widgets
// group renders a core widget through `the_widget()`, whose default `before_title` is
// `<h2 class="widgettitle">`; the Posts Slider and Flickr widget print theirs through
// `wpb_widget_title()`. A grid fetches its posts after render, and has no title of its own.
// `vc_wp_text` is left out: its body is the author's copy.
const WP_WIDGET_TAGS = [
	'vc_wp_archives',
	'vc_wp_calendar',
	'vc_wp_categories',
	'vc_wp_custommenu',
	'vc_wp_links',
	'vc_wp_meta',
	'vc_wp_pages',
	'vc_wp_posts',
	'vc_wp_recentcomments',
	'vc_wp_rss',
	'vc_wp_search',
	'vc_wp_tagcloud'
]

const SITE_DATA_SELECTOR = [
	...WP_WIDGET_TAGS.map(tag => `.${tag} > .widget > :not(.widgettitle)`),
	'.wpb_posts_slider > .wpb_wrapper > :not(.wpb_posts_slider_heading)',
	'.wpb_flickr_widget > .wpb_wrapper > :not(.wpb_flickr_heading)',
	'.vc_grid-container-wrapper'
].join(', ')

const isChrome = (node) => [ ...(node.classList || []) ].some(name => CHROME_CLASS.test(name))

const isInjected = (node) => {
	try {
		return !!node.matches?.(`${INJECTED_SELECTOR}, ${WIDGET_AREA_SELECTOR}, ${SITE_DATA_SELECTOR}`)
	} catch (_e) {
		return false
	}
}

const isSummaryLabel = (node) => [ ...(node.classList || []) ].some(name => SUMMARY_CLASS.test(name))

// Whether THIS PARAM is summarised rather than rendered, asked per param and not per element.
//
// Measured on 9.0.1: a heading carries `.admin_label_text` VISIBLE (its text is a label) plus
// `.admin_label_css_animation` hidden; a text block carries only the hidden css_animation one and
// renders its content normally; a button carries none. So "the element has any admin label" is far
// too loose -- it called every text block summarised and sent its content down the param path.
//
// The hidden ones are WPBakery's placeholders for unset params and say nothing about rendering.
const isParamSummarised = (elementNode, attrName) => {
	try {
		const label = elementNode?.querySelector?.(`.admin_label_${attrName}`)

		if (!label) {
			return false
		}

		return !label.classList.contains('hidden-label')
	} catch (_e) {
		return false
	}
}

// `parseInert` hands back the parse's `<body>`, and buildMarkupProjection emits its ROOT tag -- so
// projecting it straight put a literal `<body>` around every contribution. Wrapped in a div
// instead, which is the same shape the rendered element wrapper gives the frontend surface.
const parseParamHtml = (stored) => {
	const body = parseInert(`<div>${stored || ''}</div>`)

	return body.firstElementChild || body
}

// The block tag a summarised heading will render as, which no node in this surface can show us.
// WPBakery keeps it in a `font_container` param as `tag:h2|text_align:left` -- the element's own for
// `text`, a prefixed one (`hover_title_font_container`) for the others -- and `vc_cta` names its
// heading params after their tag. Without it a heading reaches analysis as bare prose and
// `getSubheadings` counts no subheading at all -- the frontend surface, which has a real `<h2>`,
// would then score the same page differently.
const summarisedBlockTag = (params, attrName) => {
	if (/^h[1-6]$/.test(attrName)) {
		return attrName
	}

	const container = (params || {})['text' === attrName ? 'font_container' : `${attrName}_font_container`]
	const match     = /(?:^|\|)tag:(h[1-6])(?:\||$)/i.exec(String(container || ''))

	return match ? match[1].toLowerCase() : null
}

// A nested element is its OWN unit, so its text is dropped here rather than counted twice -- once
// in its own projection and again inside its container's. `getContent()` joins the units, so a
// double count would inflate every word-based assessment.
const isNestedElement = (node, rootId) => node.hasAttribute?.('data-model-id') && node.getAttribute('data-model-id') !== rootId

const isGenerated = (node) => !!node.matches?.(GENERATED_SELECTOR)

const isDroppedFromProjection = (node, rootId) => isChrome(node) || isSummaryLabel(node) || isNestedElement(node, rootId) || isInjected(node) || isGenerated(node)

// An empty substitution drops the subtree with no block separator of its own
// ({@see highlighter/textStream.js} -- `if (!replacement.markup && !replacement.text) return`),
// which is what keeps a control block from splitting the sentence it sits inside.
const projectFull = (node, rootId) => {
	if (!node) {
		return { markup: '', text: '' }
	}

	const substitute = (candidate) => isDroppedFromProjection(candidate, rootId)
		? { markup: '', text: '' }
		: null

	return buildMarkupProjection(node, normalizeWhitespaces, { substitute })
}

// WPBakery renders an element's controls AFTER its content, so with their label left in, the
// projection never sat in the stream as one run and every mark fell to the occurrence search --
// which cannot place a repeated sentence or word unless some unique mark in the unit agrees on it.
/**
 * Builds the paint node's text stream with every character the projection drops blanked to a space.
 * NOTE: blanked rather than removed, so an offset into it still addresses the node's own stream.
 *
 * @param {Element} node   The unit's paint node.
 * @param {string}  rootId The unit's own model id.
 * @returns {string} The nbsp-folded stream, the same length as the node's own.
 */
const buildAlignmentText = (node, rootId) => {
	const { text, segments } = buildTextStream(node)
	const chars   = foldNbsp(text).split('')
	const dropped = new Map()

	const isDropped = (element) => {
		if (!element || element === node) {
			return false
		}

		if (!dropped.has(element)) {
			dropped.set(element, isDroppedFromProjection(element, rootId) || isDropped(element.parentElement))
		}

		return dropped.get(element)
	}

	for (const segment of segments) {
		const owner = segment.element ? segment.node : segment.node.parentElement

		if (isDropped(owner)) {
			chars.fill(' ', segment.start, segment.end)
		}
	}

	return chars.join('')
}

// The text a param's ROLE is decided against, which deliberately KEEPS the summary labels: in the
// back-end builder a param's value appears only inside its own `vc_admin_label`, and the textfield
// rule needs to see it to tell a button's label from `el_class`
// ({@see wpbakeryUnits.js:contentParamNames}).
const projectForClassification = (node, rootId) => {
	if (!node) {
		return ''
	}

	const substitute = (candidate) => (isChrome(candidate) || isNestedElement(candidate, rootId) || isInjected(candidate))
		? { markup: '', text: '' }
		: null

	return buildMarkupProjection(node, normalizeWhitespaces, { substitute }).text
}

// A unit's own text, from whichever source the surface makes honest.
//
// The frontend editor renders the real page, so the node is the truer source -- it carries what the
// theme and the shortcode actually produced. The back-end builder renders a SUMMARY for some
// elements, whose text is labels rather than content, so there the stored param is the only source
// that says what the reader sees. An HTML param is projected through the same stream a rendered node
// goes through, so both surfaces produce the same coordinate space.
const projectUnitText = (elementNode, ownerId, attrName, stored, mappedDef, contentCount, offCanvas) => {
	if (!offCanvas) {
		const unitNode = resolveUnitNode(elementNode, mappedDef, attrName, contentCount, stored)

		return projectNode(unitNode, ownerId)
	}

	if ('string' !== typeof stored) {
		return ''
	}

	return isHtmlParam(mappedDef, attrName)
		? projectNode(parseParamHtml(stored), null)
		: normalizeWhitespaces(stored)
}

const projectNode = (node, rootId) => projectFull(node, rootId).text

// Whether the back-end builder shows a param's text nowhere but in the stored value: summarised
// behind a label, or not shown at all -- a hover box prints only its primary title, so its hover
// title and body are in neither the canvas nor a label.
const isParamOffCanvas = (elementNode, ownerId, attrName, stored) => {
	if (isFrontendEditor()) {
		return false
	}

	if (isParamSummarised(elementNode, attrName)) {
		return true
	}

	return !isRenderedIn(projectNode(parseParamHtml(stored), null), projectNode(elementNode, ownerId))
}

// Parsed as HTML whatever the param's type: a param sharing its node is rendered inline into it,
// typically through `wp_kses_post`, so its tags are markup on the page rather than text.
const projectStoredText = (stored) => 'string' === typeof stored ? projectNode(parseParamHtml(stored), null) : ''

// Blanks a shared node's stream outside the one run this unit's text occupies, so its marks align by
// the constant offset rather than an occurrence search that cannot tell which param a repeated word
// belongs to. Left whole when the run is missing or repeated.
const isolateRun = (rendered, projected) => {
	const index = projected ? rendered.indexOf(projected) : -1

	if (0 > index || -1 !== rendered.indexOf(projected, index + 1)) {
		return rendered
	}

	return ' '.repeat(index) + projected + ' '.repeat(rendered.length - index - projected.length)
}

// Whether a candidate sits inside this element's own chrome, checked by walking to the element
// rather than by a selector: the chrome is nested several levels deep and its inner nodes carry no
// class of their own. An injected box counts too -- a table of contents repeats the heading's text,
// so its link would otherwise be the tightest match for the heading's param.
const isInChrome = (candidate, elementNode) => {
	for (let node = candidate; node && node !== elementNode; node = node.parentElement) {
		if (isChrome(node) || isInjected(node)) {
			return true
		}
	}

	return false
}

const stripTags = (value) => String(value ?? '')
	.replace(/<[^>]*>/g, ' ')
	.replace(/&nbsp;/g, ' ')
	.replace(/\s+/g, ' ')
	.trim()

// Which node carries ONE param, when an element holds more than one. `holder` is the tag WPBakery
// renders that param into (`vc_toggle`: title -> h4, content -> div), which narrows the search but
// does not finish it. With one content param the element node IS the unit's node.
//
// TWO WAYS A BARE `querySelector(holder)` GETS THIS WRONG, both measured:
//
//   - The first `div` inside an element is its CONTROL BAR, so a content param resolved to chrome
//     and projected to nothing at all.
//   - `vc_toggle` renders BOTH its params into divs -- `.vc_toggle_title` first, then
//     `.vc_toggle_content` -- so `content` resolved to the TITLE's div and that unit projected the
//     title's text. The misspelling in it was then flagged in two units at once and painted twice,
//     one mark nested inside the other.
//
// So the candidate is chosen by matching the param's own stored value against what the node
// rendered -- the same "ask reality" rule that decides which params are content at all
// ({@see wpbakeryUnits.js:contentParamNames}) -- and the TIGHTEST match wins.
//
// Tightest, not first: document order puts the wrappers first, and `.wpb_element_wrapper` holds
// BOTH params, so a first-match rule handed `content` the wrapper and that unit swallowed the
// title along with it. The duplicate mark survived the holder fix until the tie-break was turned
// around.
const resolveUnitNode = (elementNode, mappedDef, attrName, contentCount, stored) => {
	if (!elementNode || 2 > contentCount) {
		return elementNode
	}

	// Without a holder (`vc_cta` declares none for its heading or its body) every descendant is a
	// candidate, and only a match may narrow it: falling back to the element gave EACH param the
	// whole element, so the heading was counted once per param.
	const holder = (mappedDef?.params || []).find(param => param?.param_name === attrName)?.holder

	const candidates = [ ...elementNode.querySelectorAll(holder || '*') ].filter(candidate =>
		!isInChrome(candidate, elementNode) &&
		candidate.closest('[data-model-id]') === elementNode
	)

	if (!candidates.length) {
		return elementNode
	}

	const needle = stripTags(stored)

	if (needle) {
		const matched = candidates
			.map(candidate => ({ candidate, text: stripTags(candidate.textContent) }))
			.filter(entry => entry.text.includes(needle))
			.sort((a, b) => a.text.length - b.text.length)[0]

		if (matched) {
			return matched.candidate
		}
	}

	return holder ? candidates[0] : elementNode
}

// A progress bar's labels are in its `values` group, which no unit holds: they reach the analyser
// through the title's whole-element unit, so an untitled bar keeps that unit for them.
const withLabelUnit = (tag, names) => 'vc_progress_bar' === tag && !names.length && isFrontendEditor() ? [ 'title' ] : names

const buildUnits = () => {
	const units = []

	getModels().forEach(model => {
		const ownerId = model.get('id')
		const tag     = model.get('shortcode')
		const params  = model.get('params') || {}
		const node    = getElementNode(ownerId)

		if (!node) {
			return
		}

		const mappedDef = getMapped(tag)
		const names     = withLabelUnit(tag, contentParamNames(mappedDef, params, projectForClassification(node, ownerId), !isFrontendEditor()))
		const resolved  = names.map(attrName => resolveUnitNode(node, mappedDef, attrName, names.length, params[attrName]))

		// A container answers none, so rows and columns drop out here rather than through a list
		// of tag names this file would then have to maintain.
		names.forEach((attrName, index) => {
			const offCanvas = isParamOffCanvas(node, ownerId, attrName, params[attrName])
			// A summarised param's text is on screen, in its label; only the rest is hidden.
			const hiddenInView = offCanvas && !isParamSummarised(node, attrName)
			// `vc_copyright` renders prefix and postfix as bare text in ONE `<p>`, so no node holds
			// either alone, and projecting the shared node counted each param's words once per param.
			const sharesNode = resolved.some((other, otherIndex) => otherIndex !== index && other === resolved[index])
			const html       = sharesNode && !offCanvas
				? projectStoredText(params[attrName])
				: projectUnitText(node, ownerId, attrName, params[attrName], mappedDef, names.length, offCanvas)

			units.push({
				unitId   : `${ownerId}:${attrName}`,
				ownerId,
				attrName,
				html,
				offCanvas,
				hiddenInView,
				sharesNode,
				writable : !isDynamicallySourced(tag, params),
				getNode  : () => getElementNode(ownerId) && resolveUnitNode(getElementNode(ownerId), mappedDef, attrName, names.length, params[attrName])
			})
		})
	})

	return units
}

const getModel = (ownerId) => getModels().find(model => model.get('id') === ownerId) || null

const paramType = (mappedDef, attrName) =>
	(mappedDef?.params || []).find(param => param?.param_name === attrName)?.type || null

// Only `textarea_html` is stored as HTML. A `textarea` (a heading's text) or a `textfield` (a
// button's label) holds plain text, and parsing one as HTML would treat a typed `<` as a tag.
const isHtmlParam = (mappedDef, attrName) => 'textarea_html' === paramType(mappedDef, attrName)

// Why a write needs a gate at all, when paint does not: the edit panel's TinyMCE is seeded from
// the param and writes the WHOLE param back when it closes, so a `model.save` behind an open panel
// is overwritten by the stale editor content the moment the user dismisses it. Scoped to the one
// element the panel is editing, never the document -- a panel open on some other element holds
// nothing this write touches.
//
// FAILS CLOSED, and the asymmetry is deliberate: guessing "closed" silently loses the user's fix,
// while guessing "open" refuses one with a reason they can act on.
const isPanelOpenAt = (ownerId) => {
	try {
		const panel = window.vc?.active_panel
		if (!panel) {
			return false
		}

		const editing = panel.model?.get?.('id') || panel.options?.model?.get?.('id') || null

		return editing === ownerId
	} catch (_e) {
		return true
	}
}

let writeRefusal = null

const recordRefusal = (reason) => {
	writeRefusal = reason || null
}

const saveParam = (model, attrName, value) => {
	const params = { ...(model.get('params') || {}), [attrName]: value }

	// The call WPBakery's own inline view makes ({@see InlineShortcodeView_vc_column_text}), so the
	// re-render, the undo entry and the dirty flag all behave as they do for a hand edit.
	model.save({ params })
}

// Bounds the wait for a frontend re-render, which is a full page request to the server.
const ELEMENT_RENDER_WAIT_MS = 8000

/**
 * Starts recording frontend re-renders, so a write can wait until its elements are back in the canvas.
 * NOTE: start it BEFORE the write — the render can land before the caller gets to listen.
 *
 * @returns {{ until: function(Set): Promise<void>, stop: Function }} The waiter and its teardown.
 */
const watchElementRenders = () => {
	const rendered = new Set()
	let notify = null

	const handler = (model) => {
		const id = model?.get?.('id')
		if (id) {
			rendered.add(id)
			notify?.()
		}
	}

	const stop = () => {
		notify = null
		window.vc?.events?.off?.('shortcodeView:updated', handler)
	}

	window.vc?.events?.on?.('shortcodeView:updated', handler)

	const until = (ids) => new Promise(resolve => {
		let timer = null

		const done = () => {
			window.clearTimeout(timer)
			stop()
			resolve()
		}

		const check = () => {
			if ([ ...ids ].every(id => rendered.has(id))) {
				done()
			}
		}

		timer  = window.setTimeout(done, ELEMENT_RENDER_WAIT_MS)
		notify = check
		check()
	})

	return { until, stop }
}

// Everything a write needs about one unit, or null with the refusal already recorded.
// `knownUnit` is for a caller that is ALREADY iterating the unit list. Everything volatile
// here is re-read below regardless, so the only thing the rebuild supplied was the unit's own
// identity -- and rebuilding re-joins and re-projects every element on the page.
// `replaceAllOccurrences` resolving each of its own units through here made that quadratic.
const resolveWriteTarget = (unitId, knownUnit = null) => {
	const unit = knownUnit || (unitId ? buildUnits().find(entry => entry.unitId === unitId) : null)

	if (!unit) {
		recordRefusal('unit-gone')

		return null
	}

	// Reserved reason strings, so the alert names the real fix ({@see types.js:consumeWriteRefusal}).
	if (false === unit.writable) {
		const source = (getModel(unit.ownerId)?.get('params') || {}).source
		recordRefusal('post_title' === source ? 'post-title' : 'dynamic')

		return null
	}

	if (isPanelOpenAt(unit.ownerId)) {
		recordRefusal('editor-open')

		return null
	}

	const model = getModel(unit.ownerId)
	if (!model) {
		recordRefusal('unit-gone')

		return null
	}

	const stored = (model.get('params') || {})[unit.attrName]

	if ('string' !== typeof stored) {
		recordRefusal('not-structural')

		return null
	}

	return { unit, model, stored, mappedDef: getMapped(model.get('shortcode')) }
}

// A sentence is located by its TEXT in the stored value and taken only when it occurs once. The
// mark's own offsets address the unit's PROJECTION, which came from the rendered node, so reusing
// one here would rewrite whatever happens to sit at that position in the stored param -- and with
// occurrence 1 rewritten while the user watches the highlight on occurrence 3.
const locateSentence = (haystack, sentence) => {
	if (!sentence) {
		return null
	}

	const found = findSentenceRanges(haystack, sentence)

	return 1 === found.length ? found[0] : null
}

const replaceAllInValue = (value, word, replacement) => {
	const pattern = wordBoundaryPattern(word)
	if (!pattern) {
		return null
	}

	let count = 0

	const next = String(value).replace(new RegExp(pattern, 'gu'), (match) => {
		count++

		// The matched text, not `word`: the needle was flagged against rendered text, so the
		// casing to carry onto a lowercase suggestion is the stored value's own.
		return matchCase(match, replacement)
	})

	return count ? { value: next, count } : null
}

// decorate() is document-scoped and receives the whole store mark set, so the grouping is the
// adapter's job.
const isAddressableMark = (mark) => !!mark?.unitId && !!mark.range

const groupMarksByUnit = (marks) => {
	const grouped = new Map()

	for (const mark of marks.filter(isAddressableMark)) {
		if (!grouped.has(mark.unitId)) {
			grouped.set(mark.unitId, [])
		}

		grouped.get(mark.unitId).push(mark)
	}

	return grouped
}

/**
 * One unit's contribution to the ANALYZED string, which carries markup where the unit's own `html`
 * carries only text.
 *
 * The markup is what lets the analyzer see structure plain text cannot show it: `getSubheadings`
 * regexes for `<h1>`-`<h6>`, `getAnchors` for `<a>`, and `getBlocks` splits a paper at the block
 * tags. A WPBakery heading renders as a real `<h2>`, so dropping the markup would hide every
 * subheading on the page from analysis.
 *
 * Gated on the projection's text being the unit's own coordinate space, exactly as the Elementor
 * adapter gates it: a mark's range is measured against `html`, and a sentence the analyzer found has
 * to be findable in both strings. Where they diverge the escaped text is used instead, which costs
 * structure rather than correctness.
 *
 * @param {Object} unit A unit from `getContentUnits()`.
 * @returns {string} The contribution, or an empty string when there is nothing to analyze.
 */
export const projectUnitContribution = (unit) => {
	try {
		// An empty unit stays out of the string entirely: `<h2></h2>` is a subheading to
		// `getSubheadings` that no reader would ever see.
		if (!unit?.html) {
			return ''
		}

		const node = unit.getNode?.()
		if (!node) {
			return escapeText(unit.html)
		}

		// THE SAME SOURCE `html` CAME FROM, or the identity below can never hold. A summarised
		// param's `html` is its stored value while the node carries only that value's label, so
		// re-projecting the node here degraded the contribution to escaped plain text.
		const model  = getModel(unit.ownerId)
		const params = model?.get('params') || {}
		const stored = params[unit.attrName]
		const mapped = getMapped(model?.get('shortcode'))

		if (unit.offCanvas && 'string' === typeof stored) {
			if (isHtmlParam(mapped, unit.attrName)) {
				const parsed = projectFull(parseParamHtml(stored), null)

				return unit.html === parsed.text ? parsed.markup : escapeText(unit.html)
			}

			// Plain text, so the markup is built rather than projected -- and the text side is
			// `html` by construction, which is the identity the projected branch has to check for.
			const tag = summarisedBlockTag(params, unit.attrName)

			return tag ? `<${tag}>${escapeText(unit.html)}</${tag}>` : escapeText(unit.html)
		}

		// The shared node holds every param's text, so it never equals this unit's `html`.
		if (unit.sharesNode && 'string' === typeof stored) {
			const parsed = projectFull(parseParamHtml(stored), null)

			return unit.html === parsed.text ? parsed.markup : escapeText(unit.html)
		}

		const { markup, text } = projectFull(node, unit.ownerId)

		return unit.html === text ? markup : escapeText(unit.html)
	} catch (_e) {
		return ''
	}
}

// A tab, pagination dot or heading link names its panel by `data-vc-target` in the Frontend
// Editor (`[data-model-id=…]`) and by `href` (`#tab_id`) on the page. Compared as ids, not run as
// a selector: an unquoted model id that starts with a digit is not a valid one.
const controlsPanel = (control, panel) => {
	const target  = control.getAttribute('data-vc-target') || control.getAttribute('href') || ''
	const modelId = target.match(/^\[data-model-id=["']?([^"'\]]+)["']?\]$/)?.[1]

	if (modelId) {
		return panel.getAttribute('data-model-id') === modelId
	}

	return 1 < target.length && '#' === target[0] && panel.id === target.slice(1)
}

// Ends in the state WPBakery's own click leaves on Tabs, Tour, Accordion, Pageable and Toggle
// containers (`vc_active` is all `Accordion.isActive()` reads), minus the height animation.
const revealTtaPanel = (panel) => {
	const container = panel.closest('.vc_tta-container')
	const siblings  = [ ...(panel.parentElement?.children || []) ].filter(child => child.classList.contains('vc_tta-panel'))

	for (const sibling of siblings) {
		sibling.classList.toggle('vc_active', sibling === panel)
	}

	if (!container) {
		return
	}

	// A nested container's controls belong to its own panels.
	const owned = selector => [ ...container.querySelectorAll(selector) ].filter(element => element.closest('.vc_tta-container') === container)

	for (const control of owned('[data-vc-tabs]')) {
		const item = control.closest('.vc_tta-tab, .vc_pagination-item')
		if (item && siblings.some(sibling => controlsPanel(control, sibling))) {
			item.classList.toggle('vc_active', controlsPanel(control, panel))
		}
	}

	// `vc_ttaToggleBehaviour` shows the switch "on" whenever the first section is not the active one.
	for (const toggle of owned('.wpb-tta-toggle')) {
		toggle.classList.toggle('wpb-tta-toggle-active', siblings[0] !== panel)
	}
}

// The Hover box flips on `:hover` alone, so the side holding the node is turned to face the viewer
// inline, and handed back to the stylesheet the first time the pointer leaves the box.
const revealHoverBoxSide = (node) => {
	const side = node.closest('.vc-hoverbox-front, .vc-hoverbox-back')
	const box  = side?.closest('.vc-hoverbox')
	if (!box) {
		return
	}

	const transform = (side.ownerDocument.defaultView || window).getComputedStyle(side).transform
	if (!transform || 'none' === transform || 0 < new DOMMatrix(transform).m11) {
		return
	}

	const other = box.querySelector(side.classList.contains('vc-hoverbox-back') ? '.vc-hoverbox-front' : '.vc-hoverbox-back')

	side.style.transform = 'rotateY(0deg)'
	if (other) {
		other.style.transform = 'rotateY(-180deg)'
	}

	box.addEventListener('mouseleave', () => {
		side.style.transform = ''
		if (other) {
			other.style.transform = ''
		}
	}, { once: true })
}

// The clear has just detached every node a previous pass handed out, and sanitizeHighlightMarks()
// keeps any mark whose node is truthy, so a mark this pass does not paint must not keep one.
const releaseMarkNodes = (marks) => {
	for (const mark of (Array.isArray(marks) ? marks : [])) {
		if (mark && 'object' === typeof mark) {
			mark.node = null
		}
	}
}

export default {
	id : 'wpbakery',

	// The builder is a real editing surface with its own model, so controls are offered.
	analyzeOnly : false,

	// This adapter owns the builder, which is what isHighlightableEditor() rejects a WPBakery
	// page for today: the classic adapter resolves underneath but owns only the editor beneath
	// the builder, never the builder itself.
	ownsPageBuilder : true,

	// Paint travels through `decorate` below rather than a wp.data store.
	paintsViaContract : true,

	// Ownership and capability, never readiness -- Classic Mode leaves `window.vc` and the
	// builder's markup in place, so this asks whether the builder is the surface on screen
	// ({@see utils/context.js:isWPBakeryBuilderActive}).
	isActive () {
		try {
			return !!isWPBakeryBuilderActive()
		} catch (_e) {
			return false
		}
	},

	getRootNode () {
		try {
			return getContentRoot()
		} catch (_e) {
			return null
		}
	},

	getDocument () {
		try {
			return getContentDocument() || document
		} catch (_e) {
			return document
		}
	},

	getWindow () {
		try {
			return getContentWindow() || window
		} catch (_e) {
			return window
		}
	},

	getContentUnits () {
		try {
			return buildUnits()
		} catch (_e) {
			return []
		}
	},

	getContentUnit (unitId) {
		try {
			return buildUnits().find(unit => unit.unitId === unitId) || null
		} catch (_e) {
			return null
		}
	},

	getUnitNode (unitId) {
		try {
			return buildUnits().find(unit => unit.unitId === unitId)?.getNode() || null
		} catch (_e) {
			return null
		}
	},

	// A FAQ ends in the state WPBakery's own title click leaves (`vc_toggleBehaviour`), minus its 300 ms
	// slideDown -- a jump scrolling mid-animation would land short. The inline style is cleared
	// because a previous slideUp leaves `display: none` there, which outranks the active class.
	revealNode (node) {
		try {
			for (let toggle = node?.closest?.('.vc_toggle'); toggle; toggle = toggle.parentElement?.closest('.vc_toggle')) {
				if (toggle.classList.contains('vc_toggle_active')) {
					continue
				}

				toggle.classList.add('vc_toggle_active')

				const content = [ ...toggle.children ].find(child => child.classList.contains('vc_toggle_content'))
				if (content) {
					content.style.display = ''
				}
			}

			for (let panel = node?.closest?.('.vc_tta-panel'); panel; panel = panel.parentElement?.closest('.vc_tta-panel')) {
				if (!panel.classList.contains('vc_active')) {
					revealTtaPanel(panel)
				}
			}

			if (node?.closest) {
				revealHoverBoxSide(node)
			}
		} catch (_e) {
			// Nothing to open.
		}
	},

	// NO PEN GATE HERE, and that is not an omission. Elementor needs one because its inline pen
	// mounts ON the unit's node and persists `trim(innerHTML)` per keystroke, so a painted mark
	// sitting in that node reaches stored content. WPBakery edits through a PANEL instead: the
	// canvas is never made contenteditable, and the panel's TinyMCE is seeded from the model param
	// rather than from the canvas DOM -- measured on 9.0.1, where the panel editor's content
	// carries no `vc_control` chrome while the canvas node does. So a canvas mark cannot round-trip
	// into a param, and Invariant 3 holds structurally rather than by a gate.
	decorate (marks) {
		try {
			const root = getContentRoot()

			clearDomDecorations(root)
			releaseMarkNodes(marks)

			if (!root || !Array.isArray(marks) || !marks.length) {
				return
			}

			const byUnit = groupMarksByUnit(marks)
			if (!byUnit.size) {
				return
			}

			const units     = new Map((this.getContentUnits?.() || []).map(unit => [ unit.unitId, unit ]))
			const analyzers = new Set()

			for (const [ unitId, unitMarks ] of byUnit) {
				try {
					const unit = units.get(unitId)

					// A unit whose text is borrowed from the post carries no addressable source, so
					// a mark on it could only ever offer a fix that refuses (Invariant 4).
					if (!unit || false === unit.writable) {
						continue
					}

					const node = unit.getNode?.()
					if (!node) {
						continue
					}

					// An off-canvas param shows its LABEL or nothing, not the reader's text, so a mark there
					// would point at chrome and misreport where the issue is. Analysed and
					// fixable, never painted -- Invariant 4 applied to a preview rather than to a
					// refusal.
					if (unit.offCanvas) {
						continue
					}

					// The projection and the paint node are the same node, so with the dropped
					// subtrees blanked this alignment is near-tautological -- but not always: `html`
					// was built with normalizeWhitespaces, which collapses a literal `&nbsp;` entity
					// to one character where foldNbsp does not, and text carrying one has to fall
					// through to the occurrence search rather than paint six characters off.
					const rendered = buildAlignmentText(node, unit.ownerId)
					const aligned  = alignProjectedMarks({
						rendered  : unit.sharesNode ? isolateRun(rendered, unit.html) : rendered,
						projected : unit.html,
						marks     : unitMarks
					})

					const decorated = decorateNode({ node, marks: aligned.aligned })

					for (const painted of decorated.painted) {
						// The hand-off the store depends on: it resolves a mark's live node from
						// here and drops any mark without one, and the popover's jump scrolls this
						// node. Without it a mark is painted and unreachable. Adopted once the cached
						// node is detached, as on Divi: the clear above leaves `mark.node` truthy but dead.
						painted.mark.node = painted.mark.node?.isConnected ? painted.mark.node : painted.node
						analyzers.add(painted.mark.analyzer)
					}
				} catch (_e) {
					// One unit's failure must not cost the units after it.
				}
			}

			// In the frontend editor the content is an iframe, so the composable's main-document
			// stylesheet cannot reach these marks -- without this they are in the DOM and invisible.
			ensureDecorationStyles(root.ownerDocument, [ ...analyzers ])
		} catch (_e) {
			// Never throws: a failed paint degrades to no highlighting.
		}
	},

	clearDecorations () {
		try {
			clearDomDecorations(getContentRoot())
		} catch (_e) {
			// Nothing to undo.
		}
	},

	// The Frontend Editor renders every element, so only the back-end builder misses any text.
	getPartialAnalysisElements () {
		try {
			if (isFrontendEditor()) {
				return []
			}

			const elements = getModels().map(model => ({ tag: model.get('shortcode'), params: model.get('params') }))

			return partiallyAnalysedTags(elements).map(tag => getMapped(tag)?.name || tag)
		} catch (_e) {
			return []
		}
	},

	// WPBakery's own "Frontend" navbar button, which carries the `vc_get_inline_url` filter and is
	// left out for a user or post type that can't use the Frontend Editor.
	getFullViewUrl () {
		try {
			if (isFrontendEditor()) {
				return null
			}

			return document.querySelector('#wpb-edit-inline')?.href || null
		} catch (_e) {
			return null
		}
	},

	// Switching to Classic Mode leaves the builder's markup in the DOM, hidden, with whatever was
	// painted still inside it -- so the surface change has to be reported, not just refused.
	// Declared as a PAIR with the channel below on purpose: the composable samples this at mount
	// and then only when that channel fires, so an `isSuspended` without one can only ever close.
	isSuspended () {
		try {
			// POSITIVELY classic, and nothing else. `!isWPBakeryBuilderActive()` is the wrong
			// question here even though it is the right one for `isActive`: that predicate answers
			// false while WPBakery is still booting, and this gate is sampled at MOUNT and then
			// only when onSuspensionChange fires. `allowHighlighting` defaults true, so a sample
			// landing before `window.vc` exists suspends highlighting for the life of the app
			// instance AND wipes the analyzer selection with it -- a race that painted fine on most
			// loads and silently killed every highlight on the rest.
			//
			// The field is absent in the frontend editor and reads `backend` in the builder, so
			// both answer false without ever asking whether WPBakery has finished loading.
			return 'classic' === document.querySelector('#wpb_vc_editor_type')?.value
		} catch (_e) {
			return false
		}
	},

	// WPBakery emits no event for the swap between its builder and the classic editor -- the
	// switch is a link that re-lays out the page -- so the controls themselves are the channel.
	// All three exist in the DOM from page load, whichever surface is showing.
	onSuspensionChange (cb) {
		const noop = () => {}

		try {
			if ('function' !== typeof cb) {
				return noop
			}

			const SWITCHES = '.wpb_switch-to-composer, .wpb_switch-to-front-composer, .wpb_switch-to-gutenberg'

			const handler = (event) => {
				if (!event.target?.closest?.(SWITCHES)) {
					return
				}

				// Next tick: the click is what CAUSES the swap, so the field this reads is still
				// reporting the outgoing surface while the handler runs.
				setTimeout(() => {
					try {
						cb()
					} catch (_e) {
						// A subscriber's failure must not break the switch itself.
					}
				}, 0)
			}

			document.addEventListener('click', handler, true)

			return () => {
				try {
					document.removeEventListener('click', handler, true)
				} catch (_e) {
					// Already gone.
				}
			}
		} catch (_e) {
			return noop
		}
	},

	// The signal that keeps a repaint honest, and the reason no DOM observer is needed: the
	// composable skips `observeEditor()`'s contract arm precisely because this exists.
	//
	// Both channels are subscribed because the two surfaces use different ones -- the frontend
	// editor re-renders a view (`shortcodeView:updated`) while the back-end builder syncs the
	// collection -- and `change` covers a write that alters params without either. Delivered raw:
	// the composable debounces at 250 ms, and WPBakery firing once per element on a page-wide
	// re-render is exactly what the contract says to expect.
	//
	// A repaint after a re-render cannot paint a stale range: `decorate` aligns through
	// `alignProjectedMarks`, which compares each mark's expected text against the fresh projection
	// and relocates or drops it rather than painting over whatever now sits at those offsets.
	onInvalidated (cb) {
		const noop = () => {}

		try {
			if ('function' !== typeof cb) {
				return noop
			}

			const events     = window.vc?.events
			const collection = window.vc?.shortcodes

			const handler = () => {
				try {
					cb()
				} catch (_e) {
					// A subscriber's failure must not tear down WPBakery's own event bus.
				}
			}

			events?.on?.('shortcodeView:updated', handler)
			collection?.on?.('sync change', handler)

			return () => {
				try {
					events?.off?.('shortcodeView:updated', handler)
					collection?.off?.('sync change', handler)
				} catch (_e) {
					// Already gone.
				}
			}
		} catch (_e) {
			return noop
		}
	},

	// The popover mounts in the ADMIN document in both surfaces, so in the frontend editor its
	// coordinates and a mark's rect live in different realms; the two members below are the bridge.
	getPopoverHost () {
		try {
			return document.body
		} catch (_e) {
			return null
		}
	},

	getViewportBounds () {
		const viewportBottom = window.innerHeight || 0

		try {
			const frame = isFrontendEditor() ? getContentFrame() : null
			if (!frame) {
				return { top: 0, bottom: viewportBottom }
			}

			const rect = frame.getBoundingClientRect()

			return {
				top    : Math.max(0, rect.top),
				bottom : Math.min(viewportBottom, rect.bottom)
			}
		} catch (_e) {
			return { top: 0, bottom: viewportBottom }
		}
	},

	getMarkRectOffset () {
		try {
			// Zero in the back-end builder: the previews are in the admin document already, so a
			// mark's rect is in the popover's own space.
			const frame = isFrontendEditor() ? getContentFrame() : null
			if (!frame) {
				return { top: 0, left: 0 }
			}

			const rect = frame.getBoundingClientRect()

			return { top: rect.top, left: rect.left }
		} catch (_e) {
			return { top: 0, left: 0 }
		}
	},

	consumeWriteRefusal () {
		const reason = writeRefusal

		writeRefusal = null

		return reason
	},

	// One mark's sentence, replaced in the param that stores it.
	replaceMark (mark, replacement) {
		recordRefusal(null)

		try {
			if (!replacement || 'string' !== typeof replacement) {
				recordRefusal('no-replacement')

				return Promise.resolve(false)
			}

			const target = resolveWriteTarget(mark?.unitId)
			if (!target) {
				return Promise.resolve(false)
			}

			const { unit, model, stored, mappedDef } = target
			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			let next = null

			if (isHtmlParam(mappedDef, unit.attrName)) {
				const edited = editStoredHtml(stored, (projected) => {
					const range = locateSentence(projected, sentence)

					return range ? [ { start: range.start, end: range.end, text: replacement } ] : []
				})

				next = edited?.html ?? null
			} else {
				const range = locateSentence(stored, sentence)

				next = range ? stored.slice(0, range.start) + replacement + stored.slice(range.end) : null
			}

			if (null === next) {
				recordRefusal('not-found')

				return Promise.resolve(false)
			}

			saveParam(model, unit.attrName, next)

			// A programmatic save fires the collection events analysis listens to, but the debounce
			// those share is armed here so a fix and its re-analysis cannot race.
			requestPostUpdate(500)

			return Promise.resolve(true)
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	},

	// Document-scoped and marks-independent: the Spelling tab calls this straight off the resolved
	// adapter, so the per-unit gates inside are the only ones it gets.
	//
	// In the frontend editor it resolves only once the written elements have re-rendered: the analysis
	// reads the canvas, so a caller re-analyzing on resolve would otherwise read the pre-fix text.
	replaceAllOccurrences (word, replacement, { triggerReanalysis = true } = {}) {
		recordRefusal(null)

		let renders = null

		try {
			// wordBoundaryPattern('') matches at every boundary with zero length and a `g` exec
			// loop never advances past it, so an unguarded call hangs the editor rather than
			// failing.
			if (!word || !replacement) {
				return Promise.resolve(0)
			}

			renders = isFrontendEditor() ? watchElementRenders() : null

			const written = new Set()
			let total     = 0

			const pattern  = wordBoundaryPattern(word)
			const mentions = pattern ? new RegExp(pattern, 'u') : null

			for (const unit of buildUnits()) {
				const previous = writeRefusal
				const target   = resolveWriteTarget(unit.unitId, unit)
				if (!target) {
					// Only a unit that shows the word may explain why it wasn't fixed -- an open panel
					// on an unrelated element would otherwise name the wrong cause.
					if (!mentions?.test(unit.html || '')) {
						recordRefusal(previous)
					}

					continue
				}

				const { model, stored, mappedDef } = target

				if (isHtmlParam(mappedDef, unit.attrName)) {
					const edited = editStoredHtml(stored, (projected) => {
						const pattern = wordBoundaryPattern(word)
						if (!pattern) {
							return []
						}

						const edits = []
						const regex = new RegExp(pattern, 'gu')
						let match

						while (null !== (match = regex.exec(projected))) {
							edits.push({
								start : match.index,
								end   : match.index + match[0].length,
								text  : matchCase(match[0], replacement)
							})
						}

						return edits
					})

					if (edited?.html) {
						saveParam(model, unit.attrName, edited.html)
						written.add(model.get('id'))
						total += edited.count
					}

					continue
				}

				const replaced = replaceAllInValue(stored, word, replacement)

				if (replaced) {
					saveParam(model, unit.attrName, replaced.value)
					written.add(model.get('id'))
					total += replaced.count
				}
			}

			// Only a refusal that cost the caller everything is worth reporting: one unit refusing
			// while others were written is a partial write, and the Spelling tab reports the count.
			if (!total && !writeRefusal) {
				recordRefusal('not-found')
			}

			if (total && triggerReanalysis) {
				requestPostUpdate(500)
			}

			if (!renders || !written.size) {
				renders?.stop()

				return Promise.resolve(total)
			}

			return renders.until(written).then(() => total)
		} catch (_e) {
			renders?.stop()
			recordRefusal('write-error')

			return Promise.resolve(0)
		}
	},

	// FAILS OPEN, the inverse of the write gate above: this is asked BEFORE the AI request is
	// billed, so a wrong "no" removes the only fix a genuinely failing check has, while a wrong
	// "yes" costs one request. False only where the param POSITIVELY cannot hold a block -- a
	// heading's `textarea` and a button's `textfield` are plain text, and a `<p>` written into
	// either would be stored as literal characters.
	canReplaceMarkBlocks (mark) {
		try {
			const unit = mark?.unitId ? buildUnits().find(entry => entry.unitId === mark.unitId) : null
			if (!unit) {
				return true
			}

			const type = paramType(getMapped(getModel(unit.ownerId)?.get('shortcode')), unit.attrName)

			return !type || 'textarea_html' === type
		} catch (_e) {
			return true
		}
	},

	replaceMarkBlocks (mark, parts) {
		recordRefusal(null)

		try {
			const target = resolveWriteTarget(mark?.unitId)
			if (!target) {
				return Promise.resolve(false)
			}

			const { unit, model, stored, mappedDef } = target

			// The write gate's polarity, not canReplaceMarkBlocks': storing `<p>` inside a plain
			// param would persist the tags as text the reader sees.
			if (!isHtmlParam(mappedDef, unit.attrName)) {
				recordRefusal('not-structural')

				return Promise.resolve(false)
			}

			const sentence = 'string' === typeof mark?.sentence ? mark.sentence : null

			const edited = editStoredBlock(stored, (projected) => {
				const range = locateSentence(projected, sentence)

				return range
					? { start: range.start, end: range.end, markup: buildStructuredMarkup(parts) }
					: null
			})

			if (!edited?.html) {
				recordRefusal(edited?.refusal || 'not-found')

				return Promise.resolve(false)
			}

			saveParam(model, unit.attrName, edited.html)
			requestPostUpdate(500)

			return Promise.resolve(true)
		} catch (_e) {
			recordRefusal('write-error')

			return Promise.resolve(false)
		}
	}
}