export const escapeRegex = (string) => {
	return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

// Letters (any script), numbers, combining marks, and underscore — the "word"
// characters a boundary must not sit inside. ASCII `\b` only knows [A-Za-z0-9_],
// so it can't anchor a token that starts/ends with punctuation (%imply, *of) or
// is written in a non-Latin script (হলো, こんにちは); those flagged words then
// fail to highlight, jump, or replace.
const WORD_CHARACTER = '\\p{L}\\p{N}\\p{M}_'

// Unicode-aware whole-word regex source for a single flagged word. Zero-width
// look-arounds assert the neighbours aren't word characters in any script, so
// "cat" still won't match inside "category" while %imply/*of/হলো match as whole
// tokens. Compile with the `u` flag (required by the \p classes), e.g.
// `new RegExp(wordBoundaryPattern(word), 'gu')`.
export const wordBoundaryPattern = (word) => {
	return `(?<![${WORD_CHARACTER}])${escapeRegex(word)}(?![${WORD_CHARACTER}])`
}

// Applies the case pattern of replaced text to its replacement, per occurrence, since dictionary
// suggestions come back lowercase. A single letter never reads as all-caps, so a lone "I" doesn't
// upper-case a whole replacement; caseless scripts keep the dictionary's form.
export const matchCase = (original, replacement) => {
	if (!original || !replacement) {
		return replacement
	}

	const isAllCaps = 1 < original.length &&
		original === original.toUpperCase() &&
		original !== original.toLowerCase()

	if (isAllCaps) {
		return replacement.toUpperCase()
	}

	const first = original.charAt(0)
	if (first === first.toUpperCase() && first !== first.toLowerCase()) {
		return replacement.charAt(0).toUpperCase() + replacement.slice(1)
	}

	return replacement
}
// The one rule for where a flagged sentence occurs: a single word on Unicode word boundaries, a
// phrase verbatim. Shared so paint and write agree on the occurrence; a bare indexOf would find
// 'cat' in 'concatenate'.
export const sentencePattern = (sentence) => {
	const isSingleWord = !sentence.includes(' ')

	return new RegExp(
		isSingleWord ? wordBoundaryPattern(sentence) : escapeRegex(sentence),
		isSingleWord ? 'gu' : 'g'
	)
}

// Every occurrence of `sentence` in `text`, as `[start, end)` ranges in document order.
// NOTE: an empty sentence would match zero-length everywhere and loop forever.
export const findSentenceRanges = (text, sentence) => {
	const ranges = []
	if (!text || !sentence) {
		return ranges
	}

	const regex = sentencePattern(sentence)
	let match

	while (null !== (match = regex.exec(text))) {
		ranges.push({ start: match.index, end: match.index + match[0].length })
	}

	return ranges
}

export const isRegexValid = (string) => {
	try {
		new RegExp(string)
		return true
	} catch (e) {
		return false
	}
}