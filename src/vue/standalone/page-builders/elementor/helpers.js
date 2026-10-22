import { buildMarkupProjection, escapeText } from '@/vue/plugins/tru-seo/highlighter/textStream'
import { cleanForSlug } from '@/vue/utils/cleanForSlug'
import { getElementorRoot } from '@/vue/plugins/tru-seo/editors/elementor'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import { resolveAdapter } from '@/vue/plugins/tru-seo/editors'
import { shouldShowMetaBox } from '@/vue/utils/metabox'

// Elementor's opt-in marker attribute ({@see elementor.js:getUnits}).
// NOTE: not "a unit exists for this node": V4's roster dedupes global widgets by unitId, so a
// second copy carries the attribute and answers no unit.
const MARKER_ATTRIBUTE = 'data-elementor-setting-key'

// A legacy element's own wrapper class. Both tiers' units carry a `data-id` ownerId, so this is
// what tells a LEGACY unit — whose rendered markup is its content — from an atomic one, whose
// `html` comes from the props model and has no markup to recover.
const WIDGET_SELECTOR = '.elementor-widget'

// Elementor's own editor handles inside each element, e.g. the `eicon-edit` overlay.
const EDITOR_CHROME_SELECTOR = '.elementor-element-overlay, .ui-resizable-handle'

// The copies Elementor's scripts make of rendered items to loop an animation (a ticker, a looping
// carousel). The saved page holds each item once.
const ANIMATION_COPY_SELECTOR = '.e-floating-bars__headline--clone, .e-floating-bars__headlines-inner--clone, .swiper-slide-duplicate'

// What a text-less element must still hold to be worth emitting.
const KEPT_EMPTY_SELECTOR = 'a[href], br, hr, img, iframe, video'

// The empty pair `buildMarkupProjection` reads as "emit nothing for this element".
const OMITTED = { markup: '', text: '' }

// Widgets that render the site's data (other posts, menus, products, orders) rather than text the
// author wrote, so only their inline-editable settings are this page's content.
// NOTE: kept in line with what Elementor leaves out of the HTML it saves to `post_content` (an empty
// `render_plain_content()`), which is what the posts list scores, so both screens score the same text.
const SITE_DATA_WIDGETS = new Set([
	'archive-posts',
	'author-box',
	'breadcrumbs',
	'facebook-button',
	'facebook-comments',
	'facebook-embed',
	'facebook-page',
	'form',
	'login',
	'loop-carousel',
	'loop-grid',
	'nav-menu',
	'portfolio',
	'post-comments',
	'post-info',
	'post-navigation',
	'posts',
	'progress-tracker',
	'search-form',
	'sidebar',
	'sitemap',
	'template',
	'theme-post-content'
])

// WordPress widgets (core, WooCommerce and any plugin's) and every WooCommerce widget.
const SITE_DATA_WIDGET_PREFIXES = [ 'wp-widget-', 'wc-', 'woocommerce-' ]

const isSiteDataWidget = (node) => {
	const type = (node.getAttribute?.('data-widget_type') || '').split('.')[0]

	return SITE_DATA_WIDGETS.has(type) || SITE_DATA_WIDGET_PREFIXES.some(prefix => type.startsWith(prefix))
}

const isInSiteDataWidget = (node) => {
	for (let widget = node.closest?.(WIDGET_SELECTOR); widget; widget = widget.parentElement?.closest?.(WIDGET_SELECTOR)) {
		if (isSiteDataWidget(widget)) {
			return true
		}
	}

	return false
}

// Text a widget's script copies out of the rest of the page at runtime: Elementor Pro's Table of
// Contents fills its body with every heading on it. The rendered post leaves that body empty, so
// the canvas would otherwise count each heading twice and score differently from the post list.
const DERIVED_CONTENT_SELECTOR = '.elementor-toc__body'

const isDerivedContent = (element) => {
	try {
		return !!element.matches?.(DERIVED_CONTENT_SELECTOR)
	} catch (_e) {
		return false
	}
}

/**
 * Gets the markup of every image, iframe and video in the editor root.
 *
 * NOTE: the only collector of media, so it is never counted twice; units only cover text.
 *
 * @returns {string[]} The outerHTML of each media element.
 */
const getMediaMarkup = () => {
	try {
		const root = getElementorRoot()
		if (!root) {
			return []
		}

		return [ ...root.querySelectorAll('img, iframe, video') ]
			.filter(node => !isInSiteDataWidget(node) && !node.closest(ANIMATION_COPY_SELECTOR))
			.map(node => node.outerHTML)
	} catch (_e) {
		return []
	}
}

/**
 * Reads the canvas once for the three things the assembly below needs.
 *
 * NOTE: rank comes from one ordered walk rather than a `getNode()` per unit, which dominated the
 * unit phase's cost. Legacy widgets carry their own rank, since a global widget's `data-id` repeats.
 *
 * @param {Node} root The editor root.
 * @returns {{rank: Map<string, number>, widgetIds: Set<string>, widgets: Array<{node: Element, rank: number}>}} Ranks, legacy ids, legacy widgets.
 */
const readCanvas = (root) => {
	const rank      = new Map()
	const widgetIds = new Set()
	const widgets   = []

	root.querySelectorAll('[data-id]').forEach((node, index) => {
		const id = node.getAttribute('data-id')

		if (id && !rank.has(id)) {
			rank.set(id, index)
		}

		if (node.classList?.contains('elementor-widget')) {
			widgets.push({ node, rank: index })

			if (id) {
				widgetIds.add(id)
			}
		}
	})

	return { rank, widgetIds, widgets }
}

/**
 * Gets one marker's own contribution: its subtree, projected, while the unit still describes it.
 *
 * NOTE: the unit's `html` is the coordinate space marks are measured in, so the projection is used
 * only while it carries the same text. Escaped here, since a literal `<` in plain text would open
 * a tag the analyzer reads (and some run to the end of the document).
 *
 * @param {Object}  unit The content unit.
 * @param {Element} node The node to project.
 * @returns {string} The contribution.
 */
const projectMarker = (unit, node) => {
	const { markup, text } = buildMarkupProjection(node, normalizeWhitespaces, { omitClass: omitProjectedClass })

	return unit.html === text ? markup : escapeText(unit.html)
}

/**
 * Gets the node whose markup may stand in for a unit's plain `html`, or null.
 *
 * A legacy widget's unit (only when the widget arm is off) or V4's whole-canvas degraded read
 * ({@see elementorV4.js:getDegradedUnits}). Never an atomic unit, whose node may hold other units' text.
 *
 * @param {Object}      unit      The content unit.
 * @param {Set<string>} widgetIds Ids of the legacy widgets in the canvas.
 * @param {Node}        root      The editor root.
 * @returns {Element|null} The node to project, or null to keep the unit's own `html`.
 */
const markupNode = (unit, widgetIds, root) => {
	if (widgetIds.has(unit.ownerId)) {
		return unit.getNode?.() || null
	}

	return false === unit.writable && unit.getNode?.() === root ? root : null
}

/**
 * Gets one unit's contribution to the analyzed string, for a unit no widget projection covers.
 *
 * The arm the ATOMIC tier always takes, and the one where escaping is not a fallback: an atomic
 * unit's `html` comes from the props model, so it never passed through the walk that serializes a
 * text node, and a `<` a visitor reads as a character is one the analyzer reads as a tag.
 *
 * @param {Object}      unit      The content unit.
 * @param {Set<string>} widgetIds Ids of the legacy widgets in the canvas.
 * @param {Node}        root      The editor root.
 * @returns {string} The unit's contribution.
 */
const projectUnit = (unit, widgetIds, root) => {
	// Gated on `html`, not on the projection: an empty setting node must stay out of the string
	// entirely, and `<h2></h2>` is a subheading to `getSubheadings` that no reader would see.
	if (!unit?.html) {
		return ''
	}

	// The set answers only WHETHER this unit's owner is a legacy widget; the node itself always
	// comes from the unit, so a duplicated `data-id` cannot hand one unit another's node.
	const node = markupNode(unit, widgetIds, root)

	return node ? projectMarker(unit, node) : escapeText(unit.html)
}

// `alert`, `counter`, `icon-box`, `progress`, `divider` and (with `e_optimized_markup` off)
// `button` never highlight, by design: their settings sit inside classes the analyzer skips, so
// it never scores that text and a mark there could not move the score. Blockquote is skipped by
// tag instead. Dropping those classes would widen what is analysed and move every score.
/**
 * Gets one legacy widget's whole contribution, with each marker's own unit substituted into it.
 *
 * Rooted at the widget rather than each marker, because siblings of a marker (an accordion's tab
 * title, list structure, the classes the analyzer hides by) carry markup the analyzer reads.
 *
 * Each marker is emitted from its own unit and as its own block, so every sentence is findable in
 * exactly one unit's `html`. A marker the roster has no unit for contributes nothing.
 *
 * @param {Element}          node        The widget element.
 * @param {Map<Node,Object>} markerUnits Marker node to the unit that owns it.
 * @returns {string} The widget's contribution, or '' when it holds no text.
 */
const projectWidget = (node, markerUnits) => {
	const substitute = (element) => {
		if (element.matches?.(EDITOR_CHROME_SELECTOR) || element.matches?.(ANIMATION_COPY_SELECTOR)) {
			return OMITTED
		}

		if (isSiteDataWidget(element)) {
			const html = projectSiteDataWidget(element, markerUnits)

			return html ? { markup: html, text: html } : OMITTED
		}

		if (isDerivedContent(element)) {
			return OMITTED
		}

		if (!element.hasAttribute?.(MARKER_ATTRIBUTE)) {
			// A template's icon wrapper and the like: on its own line, its tags read as a sentence.
			if (!element.textContent.trim() && !element.matches(KEPT_EMPTY_SELECTOR) && !element.querySelector(KEPT_EMPTY_SELECTOR)) {
				return OMITTED
			}

			return null
		}

		const unit = markerUnits.get(element)

		return unit?.html ? { markup: projectMarker(unit, element), text: unit.html } : OMITTED
	}

	const { markup, text } = buildMarkupProjection(node, normalizeWhitespaces, { substitute, omitClass: omitProjectedClass })

	// Gated on the TEXT: a bare image, a divider or a spacer projects to tags with nothing in them,
	// so a widget with no text of its own is skipped. A CAPTIONED image passes and contributes its
	// caption.
	return text.trim() ? markup.trim() : ''
}

/**
 * Projects a site-data widget as its inline-editable settings only, e.g. a Posts widget's heading.
 *
 * @param {Element}          node        The widget element.
 * @param {Map<Node,Object>} markerUnits Marker node to the unit that owns it.
 * @returns {string} The markers' markup, one per line, or ''.
 */
const projectSiteDataWidget = (node, markerUnits) => [ ...node.querySelectorAll(`[${MARKER_ATTRIBUTE}]`) ]
	.map(marker => {
		const unit = markerUnits.get(marker)

		return unit?.html ? projectMarker(unit, marker) : ''
	})
	.filter(Boolean)
	.join('\n')

/**
 * Gets every prose contribution to the analyzed string, in document order.
 *
 * Read through the resolved adapter: a site with the V4 experiment on resolves every page to V4,
 * and V3's scrape finds nothing on an atomic page. Ordered by document position, so assessments
 * that read the opening of the content see a mixed page in reading order.
 *
 * @returns {string[]} Each contribution's markup, in document order.
 */
const getProseMarkup = () => {
	try {
		const root = getElementorRoot()
		if (!root) {
			return []
		}

		const { rank, widgetIds, widgets } = readCanvas(root)
		const units                        = resolveAdapter()?.getContentUnits?.() || []

		// Off when a `writable: false` unit exists (V4's whole-canvas degraded read,
		// {@see elementorV4.js:getDegradedUnits}), which already covers every widget; projecting them
		// too would double the word count.
		const projectWidgets = !units.some(unit => false === unit?.writable)
		const markerUnits    = new Map()

		if (projectWidgets) {
			for (const unit of units) {
				// Keyed by NODE, never by id: a duplicated `data-id` is exactly what a global widget
				// has, and both copies' markers are their own nodes. `getNode()` is read only for a
				// legacy widget's units, so the atomic tier's querySelector-per-unit cost stays out.
				const node = widgetIds.has(unit.ownerId) ? unit.getNode?.() : null
				if (node) {
					markerUnits.set(node, unit)
				}
			}
		}

		const parts   = []
		let inherited = -1

		if (projectWidgets) {
			for (const { node, rank: position } of widgets) {
				// Only the outermost of a nested pair, whose projection already carries the inner one.
				if (node.parentElement?.closest?.(WIDGET_SELECTOR)) {
					continue
				}

				const html = projectWidget(node, markerUnits)
				if (html) {
					parts.push({ rank: position, index: parts.length, html })
				}
			}
		}

		for (const unit of units) {
			inherited = rank.get(unit.ownerId) ?? inherited

			// Every legacy widget is either projected above or nested inside one that is, so a unit
			// belonging to one has already contributed — its own `html`, byte for byte, through the
			// substitution.
			if (projectWidgets && widgetIds.has(unit.ownerId)) {
				continue
			}

			parts.push({ rank: inherited, index: parts.length, html: projectUnit(unit, widgetIds, root) })
		}

		return parts
			.sort((a, b) => (a.rank - b.rank) || (a.index - b.index))
			.map(part => part.html)
	} catch (_e) {
		return []
	}
}

/**
 * Gets the post type of an Elementor document.
 * NOTE: Elementor only exposes it through the "all posts" admin URL, which omits the query arg for posts.
 *
 * @param   {Object} documentConfig The Elementor document config.
 * @returns {string}                The post type.
 */
const getPostType = (documentConfig) => {
	if (!documentConfig?.urls?.all_post_type) {
		return ''
	}

	return new URL(documentConfig.urls.all_post_type, window.location.origin).searchParams.get('post_type') || 'post'
}

/**
 * Checks whether the active Elementor document is the one AIOSEO is scoped to.
 * NOTE: Elementor swaps the active document for Site Settings and theme templates, and moves the initial one
 * when users navigate to another post from the document title dropdown.
 *
 * @returns {boolean} Whether AIOSEO should act on the active document.
 */
export const isAioseoDocument = () => {
	const documentConfig = window.elementor?.documents?.getCurrent()?.config
	if (Number(documentConfig?.id) !== Number(window.elementor?.config?.initial_document?.id)) {
		return false
	}

	// The post we booted for was already vetted server-side, so its post type needs no second look.
	if (Number(documentConfig?.id) === Number(window.aioseo?.currentPost?.id)) {
		return true
	}

	// An unreadable post type must not fall through to `shouldShowMetaBox()`, which then answers for another post.
	const postType = getPostType(documentConfig)

	return postType ? shouldShowMetaBox(postType) : false
}

/**
 * Gets the post content.
 *
 * Joined on a newline, not a space: `getSentences` breaks on `\n`, so a heading above a paragraph
 * stays two sentences.
 * NOTE: no offset indexes this string; marks are measured per unit.
 *
 * @returns {string} The post content.
 */
// Classes Elementor's editor puts on its own chrome. `elementor-element-editable` toggles on
// selection, which would make a click read as an edit. An explicit list, never a prefix match
// ({@see classAttribute}).
const EDITOR_STATE_CLASSES = new Set([
	'elementor-element-editable',
	'elementor-element-edit-mode',
	'elementor-element--toggle-edit-tools',
	'ui-resizable'
])

// The Shortcode widget's wrapper, which the analyzer skips. The saved `post_content` holds the
// shortcode bare, so the posts list scores its output (a gallery's links and captions).
const SHORTCODE_WRAPPER_CLASS = 'elementor-shortcode'

const omitProjectedClass = (name) => EDITOR_STATE_CLASSES.has(name) || SHORTCODE_WRAPPER_CLASS === name

export const getContent = () => {
	return [ ...getProseMarkup(), ...getMediaMarkup() ]
		.filter(Boolean)
		.join('\n')
		// Icon fonts render as Private Use Area characters (the Rating widget's stars), which the
		// analyzer would read as a sentence.
		.replace(/[\uE000-\uF8FF]/g, '')
}

/**
 * Gets the data that is specific to this editor.
 *
 * @returns {Object} The editorData object.
 */
export const getEditorData = () => {
	const { settings, config } = window.elementor

	return {
		content       : getContent(),
		title         : settings.page.model.get('post_title'),
		excerpt       : settings.page.model.get('post_excerpt') || '',
		slug          : cleanForSlug(settings.page.model.get('post_title')),
		permalink     : config.document.urls.permalink || '',
		featuredImage : settings.page.model.get('post_featured_image')?.url || ''
	}
}