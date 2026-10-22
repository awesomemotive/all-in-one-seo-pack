import { getOuterText } from '@/vue/utils/html'
import { normalizeWhitespaces } from '@/vue/utils/postData/helpers'
import { findSentenceRanges } from '@/vue/utils/regex'
import { overlapsSkipped } from './textStream'

/**
 * Converts a flagged sentence to the plain text marks are matched with.
 *
 * @param {string} sentence The sentence, as the assessment returns it.
 * @returns {string} The plain text.
 */
export const toHighlightText = (sentence) => {
	let text = String(sentence || '').replace(/&[a-zA-Z0-9#]{2,};$/, '')
	text     = text.replace(/<br[^>]*>/gi, '\n')

	return getOuterText(normalizeWhitespaces(text))
}

/**
 * Converts an assessment's flagged sentences to the list marks are built from.
 *
 * @param {Array} highlightSentences The sentences, as the assessment returns them.
 * @returns {string[]} The plain texts, without empty ones.
 */
export const toHighlightTexts = (highlightSentences) => {
	return (highlightSentences || []).flat()
		.map(toHighlightText)
		.filter(text => !!text.trim())
}

/**
 * Whether marks can be built from a content unit.
 *
 * NOTE: a `writable: false` unit could only offer a fix that refuses, and a `highlightable: false`
 * one was never analyzed ({@see editors/types.js:ContentUnit}).
 *
 * @param {Object} unit The content unit.
 * @returns {boolean} Whether it is markable.
 */
export const isMarkableUnit = (unit) => false !== unit?.writable && false !== unit?.highlightable

/**
 * Picks the range each still-unmarked sentence gets in a piece of content: its first occurrence
 * that no other sentence of the same analyzer has taken there, outside the text the analyzer drops.
 *
 * @param {string}   content   The content's text.
 * @param {string[]} sentences One analyzer's flagged sentences ({@see toHighlightTexts}).
 * @param {Set}      claimed   Indexes of the sentences already marked; the picked ones are added.
 * @param {Object[]} [taken]   Ranges already marked in this content.
 * @param {Object[]} [skipped] Ranges of the content the analyzer drops ({@see textStream.js}).
 * @returns {Object[]} `{ index, range }` for each sentence marked here.
 */
export const claimSentenceRanges = (content, sentences, claimed, taken = [], skipped = []) => {
	const claims = []
	if (!content) {
		return claims
	}

	const isTaken = (range) => taken.concat(claims.map(claim => claim.range))
		.some(other => other.start === range.start && other.end === range.end)

	sentences.forEach((sentence, index) => {
		if (claimed.has(index) || !sentence) {
			return
		}

		const range = findSentenceRanges(content, sentence)
			.map(found => ({ start: found.start, end: found.end || 1 }))
			.find(found => !isTaken(found) && !overlapsSkipped(found, skipped))

		if (range) {
			claims.push({ index, range })
			claimed.add(index)
		}
	})

	return claims
}

// Claims each flagged sentence in the first markable unit holding it, in reading order, and
// tallies how many of those claims fell in units the editor's view doesn't show.
const locateSentences = (highlightSentences, units) => {
	const sentences = toHighlightTexts(highlightSentences)
	const claimed   = new Set()
	let hidden      = 0

	for (const unit of units) {
		if (claimed.size === sentences.length) {
			break
		}

		if (isMarkableUnit(unit)) {
			const claims = claimSentenceRanges(unit?.html, sentences, claimed, [], unit?.skipped)

			if (unit.hiddenInView) {
				hidden += claims.length
			}
		}
	}

	return { located: claimed.size, hidden }
}

/**
 * Counts the marks an analyzer's flagged sentences get in an editor's content units, whether or
 * not their highlight is on.
 *
 * @param {Array}    highlightSentences The sentences, as the assessment returns them.
 * @param {Object[]} units              The editor's content units, in reading order.
 * @returns {number} The number of marks.
 */
export const countLocatableSentences = (highlightSentences, units) => locateSentences(highlightSentences, units).located

/**
 * Counts the marks of {@see countLocatableSentences} that land in a `hiddenInView` unit: analysed
 * and fixable, but in text the editor's view doesn't show, so they never paint.
 *
 * @param {Array}    highlightSentences The sentences, as the assessment returns them.
 * @param {Object[]} units              The editor's content units, in reading order.
 * @returns {number} The number of those marks.
 */
export const countHiddenSentences = (highlightSentences, units) => locateSentences(highlightSentences, units).hidden