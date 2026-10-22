import { escapeText } from './textStream'

// Its own module so adapters can use it without importing `spellingReplace`, which would deepen
// the import cycle with `editors/index`.

/**
 * Parses a remote suggestion string into a list of structural parts suitable for building blocks.
 *
 * Lines starting with `## ` become H2 heading parts. Lines starting with `### ` become H3 heading
 * parts. Text separated by blank lines becomes separate paragraph parts.
 *
 * @param {string} text The replacement text from the AI Generator.
 * @returns {Array} An array of part objects, each with a `type`, optional `level`, and `content`.
 */
export const parseStructuredReplacement = (text) => {
	const parts = []
	const chunks = String(text).split(/\n{2,}/).map(c => c.trim()).filter(Boolean)

	for (const chunk of chunks) {
		if (chunk.startsWith('### ')) {
			parts.push({ type: 'heading', level: 3, content: chunk.slice(4).trim() })
			continue
		}

		if (chunk.startsWith('## ')) {
			parts.push({ type: 'heading', level: 2, content: chunk.slice(3).trim() })
			continue
		}

		parts.push({ type: 'paragraph', content: chunk })
	}

	return parts
}

/**
 * Renders structured parts as the block markup an html-valued editor stores.
 *
 * NOTE: parts come from an AI suggestion, so they are escaped.
 *
 * @param {Array} parts Parts from `parseStructuredReplacement`.
 * @returns {string} The block markup.
 */
export const buildStructuredMarkup = (parts) => (parts || []).map(part => ('heading' === part.type
	? `<h${part.level}>${escapeText(part.content)}</h${part.level}>`
	: `<p>${escapeText(part.content)}</p>`)).join('')