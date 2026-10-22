// Which of a Divi 5 module's attributes hold reader-visible text, and where that text sits inside
// one. Kept free of `divi.data` so both rules are testable without a builder: the adapter passes in
// what it read.

// Only the desktop breakpoint is analysed or written, by decision. A module CAN carry different
// text per breakpoint; tablet and phone overrides are invisible here, which is a documented
// limitation rather than an oversight.
const BREAKPOINT = 'desktop'

// Attributes that hold a SETTING as a string under `innerContent`, which the value shape cannot tell
// apart from prose: an admin notification template, a redirect URL, an input placeholder. None is
// shown to a reader as page content.
const SETTING_ATTRS = {
	'divi/contact-form' : [ 'email', 'redirect' ],
	'divi/search'       : [ 'searchPlaceholder' ]
}

// The level a heading attribute renders at until the user picks one, from each module's
// `module-default-render-attributes.json`, which Divi never copies into the stored attributes.
// NOTE: a child item inherits its parent's choice first ({@see PricingTablesItemModule.php}); only
// the child's own setting and this fallback are read here.
const DEFAULT_HEADING_LEVELS = {
	'divi/accordion-item'      : { title: 'h5' },
	'divi/audio'               : { title: 'h2' },
	'divi/blurb'               : { title: 'h4' },
	'divi/circle-counter'      : { title: 'h3' },
	'divi/contact-form'        : { title: 'h1' },
	'divi/countdown-timer'     : { title: 'h4' },
	'divi/cta'                 : { title: 'h2' },
	'divi/fullwidth-header'    : { title: 'h1' },
	'divi/fullwidth-portfolio' : { title: 'h2' },
	'divi/heading'             : { title: 'h1' },
	'divi/login'               : { title: 'h2' },
	'divi/number-counter'      : { title: 'h3' },
	'divi/pricing-table'       : { title: 'h2' },
	'divi/signup'              : { title: 'h2' },
	'divi/team-member'         : { name: 'h4' },
	'divi/toggle'              : { title: 'h5' }
}

/**
 * Reads one attribute's desktop text, or null when the attribute holds no text.
 *
 * Divi 5 exposes no attribute-type map through `select('divi/edit-post')`, so the role is decided
 * from the VALUE SHAPE. That is tighter than it sounds: every text attribute nests under
 * `innerContent`, while `builderVersion` is a bare string and `module` holds `decoration`, so
 * neither can reach this test. A curated list of attribute names would exclude third-party modules
 * for no gain.
 *
 * TWO SHAPES, because Divi stores a button's label one level deeper than its prose:
 *   content / title -> innerContent.desktop.value             (a string)
 *   button          -> innerContent.desktop.value.text        (a string on an object)
 *
 * @param {*} attrValue One entry of `getModuleAttrs()`.
 * @returns {?{text: string, subKey: ?string}} The text and the sub-key it came from, or null.
 */
export const readAttrText = (attrValue) => {
	const value = attrValue?.innerContent?.[BREAKPOINT]?.value

	if ('string' === typeof value) {
		return { text: value, subKey: null }
	}

	if (value && 'string' === typeof value.text) {
		return { text: value.text, subKey: 'text' }
	}

	return null
}

/**
 * Names the attributes of one module that carry reader-visible text.
 *
 * An empty attribute answers nothing: a text module with no content is not a unit, and an empty
 * projection would reach the analyser as a subheading no reader would ever see.
 *
 * @param {?Object} attrs      The module's attributes.
 * @param {string}  moduleName The module's name, e.g. `divi/text`.
 * @returns {string[]} Attribute names, in the model's own order.
 */
export const textAttrNames = (attrs, moduleName = '') => Object.entries(attrs || {})
	.filter(([ name ]) => !SETTING_ATTRS[moduleName]?.includes(name))
	.filter(([ , value ]) => !!readAttrText(value)?.text.trim())
	.map(([ name ]) => name)

/**
 * The dot path `editModuleAttribute` takes for one attribute's desktop text.
 *
 * `attrName` is a path from the module's attrs ROOT, which is how Divi's own call sites address a
 * nested value ({@see edit-post.js} `module.decoration.layout.desktop.value.display`). The
 * button's extra `.text` segment is why this is built from the read rather than hard-coded.
 *
 * @param {string}  attrName The attribute name.
 * @param {?string} subKey   The sub-key `readAttrText` reported, if any.
 * @returns {string} The dot path.
 */
export const attrWritePath = (attrName, subKey) => {
	const base = `${attrName}.innerContent.${BREAKPOINT}.value`

	return subKey ? `${base}.${subKey}` : base
}

/**
 * Reads the heading level an attribute renders at, or null when it is not a heading.
 * NOTE: Divi omits the level until the user changes it, so an unset one falls back to the module's
 * default, which only built-in modules have here.
 *
 * @param {*}      attrValue  One entry of `getModuleAttrs()`.
 * @param {string} moduleName The module's name, e.g. `divi/heading`.
 * @param {string} attrName   The attribute's name.
 * @returns {?string} The tag name (`h1`-`h6`), or null.
 */
export const readHeadingLevel = (attrValue, moduleName = '', attrName = '') => {
	const level = attrValue?.decoration?.font?.font?.[BREAKPOINT]?.value?.headingLevel

	return /^h[1-6]$/.test(level || '') ? level : (DEFAULT_HEADING_LEVELS[moduleName]?.[attrName] || null)
}

/**
 * The heading tag a module renders a plain-text value in, or null when it renders none.
 * NOTE: A title is stored plain, while the analyzer only counts `<h1>`-`<h6>` as subheadings.
 *
 * @param {?Element} node A module's rendered node, or the heading itself.
 * @param {string}   text The value's projected text.
 * @returns {?string} The tag name, or null.
 */
export const findRenderedHeadingTag = (node, text) => {
	const wanted = String(text ?? '').replace(/\s+/g, ' ').trim()
	const match  = [ node, ...(node?.querySelectorAll?.('h1, h2, h3, h4, h5, h6') || []) ]
		.find(heading => /^H[1-6]$/.test(heading?.nodeName || '') && heading.textContent.replace(/\s+/g, ' ').trim() === wanted)

	return match ? match.tagName.toLowerCase() : null
}

/**
 * Whether an attribute's text is markup rather than plain prose.
 *
 * `content` holds HTML while a title or a button label is plain, but the test is on the VALUE, not
 * the attribute name, so a third-party module storing markup under another name is handled too.
 * Projection and the write path both ask this, so they cannot disagree about what a value is.
 *
 * @param {string} text The attribute's text.
 * @returns {boolean} True when it must be parsed as HTML.
 */
export const looksLikeHtml = (text) => /[<&]/.test(String(text ?? ''))