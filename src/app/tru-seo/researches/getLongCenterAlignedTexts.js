import intersection from 'lodash-es/intersection'
import { hashedHtmlEntities } from '@/app/tru-seo/helpers/htmlEntities'

/**
 * @typedef {import("../../languageProcessing/AbstractResearcher").default } Researcher
 * @typedef {import("../../parse/structure/").Node} Node
 * @typedef {import("../../values/").Paper } Paper
 */

/**
 * The maximum allowed length of a center-aligned paragraph or heading in characters.
 * @type {number}
 */
const MAX_CENTER_ALIGNED_LENGTH = 50

/**
 * A regular expression to match heading tags.
 * @type {RegExp}
 */
const HEADING_TAGS_REGEX = /^h[1-6]$/

/**
 * The `text-align` values a browser accepts. A declaration with any other value is ignored.
 * @type {RegExp}
 */
const VALID_TEXT_ALIGN_REGEX = /^(?:start|end|left|right|center|justify|justify-all|match-parent|inherit|initial|unset|revert|revert-layer|-webkit-(?:left|right|center|match-parent)|-moz-(?:left|right|center))$/

/**
 * The hashed HTML entities (e.g. `#quot;`) that the tree builder leaves in attribute values, with the uppercase names HTML also accepts.
 * NOTE: The builder hashes entities in any case, but a browser only decodes these exact spellings.
 * @type {Map<string, string>}
 */
const ATTRIBUTE_HTML_ENTITIES = new Map([
	...hashedHtmlEntities,
	...[ 'amp;', 'lt;', 'gt;', 'quot;', 'copy;', 'reg;', 'trade;' ].map(name => [ '#' + name.toUpperCase(), hashedHtmlEntities.get('#' + name) ])
])

/**
 * Matches the entities in `ATTRIBUTE_HTML_ENTITIES`.
 * @type {RegExp}
 */
const HASHED_HTML_ENTITIES_REGEX = new RegExp([ ...ATTRIBUTE_HTML_ENTITIES.keys() ].join('|'), 'g')

/**
 * Splits an inline style into declarations, like a browser: a `;` inside a string, brackets or a comment doesn't end one.
 * @param {string} style The style attribute value.
 * @returns {string[]} The declarations, with comments removed.
 */
function splitDeclarations (style) {
	const declarations = []
	let current = '',
		quote = '',
		depth = 0

	for (let i = 0; i < style.length; i++) {
		const char = style[i]

		if (quote) {
			current += char
			if ('\\' === char) {
				current += style[++i] || ''
			} else if (char === quote) {
				quote = ''
			}
			continue
		}

		// A comment separates tokens, so `text-/**/align` isn't `text-align`.
		if ('/' === char && '*' === style[i + 1]) {
			const end = style.indexOf('*/', i + 2)
			i = -1 === end ? style.length : end + 1
			current += ' '
			continue
		}

		if ('\\' === char) {
			current += char + (style[++i] || '')
			continue
		}

		if ('"' === char || '\'' === char) {
			quote = char
		} else if ('(' === char || '[' === char || '{' === char) {
			depth++
		} else if ((')' === char || ']' === char || '}' === char) && 0 < depth) {
			depth--
		} else if (';' === char && 0 === depth) {
			declarations.push(current)
			current = ''
			continue
		}

		current += char
	}

	declarations.push(current)

	return declarations
}

/**
 * Whether an inline style centers the text, applying the CSS cascade within the attribute.
 * @param {string} style The style attribute value.
 * @returns {boolean} Whether the text is center-aligned.
 */
function isCenterAlignedStyle (style) {
	let textAlign = '',
		isImportant = false

	splitDeclarations(style.replace(HASHED_HTML_ENTITIES_REGEX, entity => ATTRIBUTE_HTML_ENTITIES.get(entity))).forEach(declaration => {
		const colonIndex = declaration.indexOf(':')
		if (-1 === colonIndex || 'text-align' !== declaration.slice(0, colonIndex).trim().toLowerCase()) {
			return
		}

		let value = declaration.slice(colonIndex + 1).trim().toLowerCase()
		const important = /!\s*important$/.test(value)
		value = value.replace(/!\s*important$/, '').trim()

		// A later declaration wins, unless it's invalid or an earlier one is `!important` and it isn't.
		if (!VALID_TEXT_ALIGN_REGEX.test(value) || (isImportant && !important)) {
			return
		}

		textAlign   = value
		isImportant = important
	})

	return /^(?:-webkit-|-moz-)?center$/.test(textAlign)
}

/**
 * Filters out all nodes that are center-aligned and longer than 50 characters (as defined in `MAX_CENTER_ALIGNED_LENGTH`).
 * @param {Node[]} nodes An array of nodes.
 * @param {string[]} centerClasses The classes that are used to identify center-aligned elements.
 * @returns {Node[]} An array containing all center-aligned nodes that are longer than 50 characters.
 */
function getLongCenterAlignedElements (nodes, centerClasses) {
	return nodes.filter(node => {
		if (node.innerText().length <= MAX_CENTER_ALIGNED_LENGTH) {
			return false
		}

		if (node.attributes.class &&
		    0 < intersection([ ...node.attributes.class ], centerClasses).length) {
			return true
		}

		if (node.attributes.style && isCenterAlignedStyle(node.attributes.style)) {
			return true
		}

		return false
	})
}

/**
 * Finds all paragraphs and headings that are center-aligned and longer than 50 characters (after stripping HTML tags).
 * @param {Paper} paper The paper to analyze.
 * @param {Researcher} researcher The researcher.
 * @returns {Node[]} An array of nodes containing too long center-aligned paragraphs/headings.
 */
export default function (paper, researcher) {
	const tree = paper.getTree()

	const paragraphs = tree.findAll(node => 'p' === node.name)
	const headings = tree.findAll(node => HEADING_TAGS_REGEX.test(node.name))

	const centerClasses = researcher.getConfig('centerClasses')

	return getLongCenterAlignedElements(paragraphs.concat(headings), centerClasses)
}