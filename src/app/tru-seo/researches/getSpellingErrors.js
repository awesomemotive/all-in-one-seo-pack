import spellChecker from '@/app/tru-seo/helpers/spellChecker'
import removeHtmlBlocks from '@/app/tru-seo/languageProcessing/helpers/html/htmlParser'
import { filterShortcodesFromHTML } from '@/app/tru-seo/languageProcessing/helpers'
import { stripFullTags } from '@/app/tru-seo/languageProcessing/helpers/sanitize/stripHTMLTags'
import { normalizeSingle } from '@/app/tru-seo/languageProcessing/helpers/sanitize/quotes'

/**
 * Regex to match word-like tokens while tracking positions.
 * Matches sequences of word characters including hyphens and apostrophes
 * (straight and curly), so contractions like "don't" and possessives like
 * "John's" stay as a single token regardless of smart-quote autocorrect.
 *
 * @type {RegExp}
 */
const wordRegex = /[^\s.,;:!?"()[\]{}<>/\\…—–\u201C\u201D\u00AB\u00BB]+/g

/**
 * Matches a hyphen-minus or a Unicode hyphen pasted in its place (U+2010,
 * U+2011, U+FE63, U+FF0D).
 *
 * @type {RegExp}
 */
const hyphenRegex = /[-\u2010\u2011\uFE63\uFF0D]/

/**
 * Matches a run of two or more hyphens, typed as a dash ("wait--what").
 *
 * @type {RegExp}
 */
const dashRegex = /[-\u2010\u2011\uFE63\uFF0D]{2,}/g

/**
 * Matches the Unicode hyphens only.
 *
 * @type {RegExp}
 */
const unicodeHyphenRegex = /[\u2010\u2011\uFE63\uFF0D]/g

/**
 * Matches a single emoji, pictographic symbol, or emoji-sequence joiner
 * (variation selectors, skin-tone modifiers, regional indicators, ZWJ, tags).
 *
 * @type {RegExp}
 */
const emojiRegex = /\p{Extended_Pictographic}|[\u{1F1E6}-\u{1F1FF}]|[\u{1F3FB}-\u{1F3FF}]|[\u{FE00}-\u{FE0F}]|[\u{E0020}-\u{E007F}]|\u200D/gu

/**
 * Matches URL-like and email-like spans, ordered so the leftmost branch wins.
 *
 * The tokenizer splits on ':', '/', and '.', so a URL never reaches
 * shouldSkipWord's URL guard as a whole token \u2014 its fragments ('https', 'ufl',
 * 'edu') would each be flagged. Neutralizing the whole span before tokenizing
 * is the only place that guard can work.
 *
 * Branches: email (matched first so its domain isn't split off by a later
 * branch), protocol/protocol-relative URL, www-prefixed host, then bare domain
 * ending in a common TLD. The bare-domain lookahead stops a TLD-looking prefix
 * from eating an ordinary word (e.g. "end.Start" stays checkable).
 *
 * @type {RegExp}
 */
const urlLikeRegex = new RegExp([
	'[^\\s<>"\'@]+@[^\\s<>"\'@]+\\.[a-z]{2,}',
	'(?:https?:)?\\/\\/[^\\s<>"\']+',
	'www\\.[^\\s<>"\']+',
	'[a-z0-9-]+(?:\\.[a-z0-9-]+)*\\.(?:com|org|net|edu|gov|mil|int|io|co|dev|app|info|biz|xyz|online|site|tech|store|blog|[a-z]{2})(?![a-z])(?:\\/[^\\s<>"\']*)?'
].join('|'), 'gi')

/**
 * Replaces every emoji code point with an equal-length run of spaces.
 *
 * Spaces (not removal) so character offsets stay identical to the source text —
 * downstream highlight/replace positions must keep matching. Emoji then act as
 * word boundaries, so an emoji glued to a word is checked without it and a lone
 * emoji vanishes.
 *
 * @param {string} text The text to process.
 * @returns {string}    The text with emoji replaced by spaces.
 */
function neutralizeEmoji (text) {
	return text.replace(emojiRegex, match => ' '.repeat(match.length))
}

/**
 * Replaces every URL, email address, and bare domain with an equal-length run
 * of spaces so their components act as word boundaries and vanish from the
 * spell check. Equal-length (not removal) keeps character offsets identical to
 * the source text, matching neutralizeEmoji, so highlight/replace positions
 * stay correct.
 *
 * @param {string} text The text to process.
 * @returns {string}    The text with URLs and emails replaced by spaces.
 */
function neutralizeUrls (text) {
	return text.replace(urlLikeRegex, match => ' '.repeat(match.length))
}

/**
 * Replaces every dash typed as hyphens with an equal-length run of spaces.
 *
 * A dash separates words, like the "—" and "–" the tokenizer already splits on.
 * Left in, the compound split drops the words beside it as affixes and hides
 * their typos. No shipped dictionary holds an entry with a doubled hyphen.
 *
 * @param {string} text The text to process.
 * @returns {string}    The text with dashes replaced by spaces.
 */
function neutralizeDashes (text) {
	return text.replace(dashRegex, match => ' '.repeat(match.length))
}

/**
 * Checks if a word should be skipped during spell checking.
 *
 * @param {string}  word The word to check.
 * @returns {boolean}    True if the word should be skipped.
 */
function shouldSkipWord (word) {
	// Single characters.
	if (2 > word.length) {
		return true
	}

	// Numbers and numeric strings.
	if (/^\d+([.,]\d+)*$/.test(word)) {
		return true
	}

	// camelCase / intercaps brand & technical terms (eCommerce, iPhone, WordPress, macOS).
	if (/\p{Ll}\p{Lu}/u.test(word)) {
		return true
	}

	// All-caps acronyms, optionally pluralised or possessive (SEO, TCG, RPGs, PDF'S).
	// Which ones the dictionary holds is arbitrary (AI is in, SEO is not), so short
	// all-caps runs go unchecked. Capped at 6 because 44 of the 45 acronyms our
	// en_US misses fit within 6, while a longer all-caps run reads as a shouted
	// misspelling (RECIEVE, CONCLUSHON) and stays checked. All-caps only: a merely
	// capitalized word is indistinguishable from a typo opening a sentence. An
	// uppercase S needs the apostrophe (PDF'S): bare, it is part of a shouted
	// word, and BUSINES is no acronym.
	if (/^\p{Lu}{2,6}(?:['\u2019][sS]|s)?$/u.test(word)) {
		return true
	}

	// URLs.
	if (/^https?:\/\//i.test(word)) {
		return true
	}

	// Email addresses.
	if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(word)) {
		return true
	}

	// Hashtags and mentions.
	if (/^[#@]/.test(word)) {
		return true
	}

	// Words with numbers mixed in (e.g. "h2", "3rd").
	if (/\d/.test(word)) {
		return true
	}

	return false
}

/**
 * Splits a hyphenated token into the parts that are worth checking on their own.
 *
 * Hunspell breaks a word on the hyphen and our en_US holds no hyphenated entry
 * at all, so a compound only ever passes because every one of its parts passes.
 * Mirroring that split is what lets the skip rules reach a part: without it an
 * acronym loses its skip the moment it joins a compound ("SEO-friendly"), and a
 * mark covers the whole compound instead of the part that is actually wrong.
 *
 * A fragment before a trailing hyphen is dropped rather than checked. It is
 * half of a suspended compound ("pre- and post-launch"), so it is an affix
 * rather than a word and no dictionary can judge it. A leading hyphen forms no
 * such compound in English ("-recieve the file" is a list item), so a token
 * that opens with one is checked in full.
 *
 * @param {string} word  The hyphenated token.
 * @param {number} start The token's start offset in the text.
 * @returns {Array<{word: string, start: number, end: number}>} The checkable parts.
 */
function splitHyphenatedParts (word, start) {
	const fragments = word.split(hyphenRegex)
	const parts     = []
	let offset      = 0

	fragments.forEach((fragment, index) => {
		const suspendedPrefix = '' !== fragments[0] && '' === fragments[index + 1]

		if (fragment.length && !suspendedPrefix) {
			parts.push({
				word  : fragment,
				start : start + offset,
				end   : start + offset + fragment.length
			})
		}

		// The hyphen the split consumed still occupies a character in the source.
		offset += fragment.length + 1
	})

	return parts
}

/**
 * Reports whether a word is accepted, either by a skip rule or by the dictionary.
 *
 * @param {string} word The word to check.
 * @returns {boolean}   True if the word must not be flagged.
 */
function isAccepted (word) {
	if (shouldSkipWord(word)) {
		return true
	}

	// Hunspell dictionaries store contractions with the straight apostrophe and
	// compounds with the hyphen-minus, so normalize both before lookup while
	// preserving the original word for downstream highlight/replace matching
	// against the source text.
	const normalized = normalizeSingle(word).replace(unicodeHyphenRegex, '-')

	// Match-case ("strict") safe words are accepted only in their exact
	// casing and are intentionally absent from Hunspell, so check them here
	// before the case-insensitive Hunspell lookup. Normalize apostrophes the
	// same way as the Hunspell path so strict words with apostrophes match.
	if (spellChecker.hasSafeWordStrict(normalized)) {
		return true
	}

	if (spellChecker.check(normalized)) {
		return true
	}

	// Non-strict ("match case off") safe words are meant to be accepted in any
	// casing, but Hunspell only accepts case variants of a lowercase entry — a
	// capitalized or all-caps custom word ("Cuple"/"CUPLE") won't accept the
	// lowercase form. Match those case-insensitively before flagging.
	return spellChecker.hasSafeWordLoose(normalized)
}

/**
 * Tokenizes text into words with their positions.
 *
 * @param {string} text The text to tokenize.
 * @returns {Array<{word: string, start: number, end: number}>} Array of word tokens with positions.
 */
function tokenizeWithPositions (text) {
	const words = []
	let match

	wordRegex.lastIndex = 0
	while (null !== (match = wordRegex.exec(text))) {
		const word = match[0]

		// Strip leading/trailing punctuation that may have been captured (e.g. quotes around words).
		// Escaped so an editor can't flatten the curly quotes, which wordRegex doesn't split on.
		const stripped = word.replace(/^['`‘’]+|['`‘’.,;:!?]+$/g, '')
		if (stripped.length) {
			const offset = word.indexOf(stripped)
			words.push({
				word  : stripped,
				start : match.index + offset,
				end   : match.index + offset + stripped.length
			})
		}
	}

	return words
}

/**
 * Researches spelling errors in the paper's text.
 *
 * @since 5.0.0
 *
 * @param {Paper}      paper       The paper to analyze.
 * @param {Researcher} _researcher Unused — spell checker is a module singleton.
 * @returns {Object}               The spelling research result.
 */
export default function getSpellingErrors (paper, _researcher) { // eslint-disable-line no-unused-vars
	if (!spellChecker.isReady()) {
		return {
			errors          : [],
			totalWords      : 0,
			misspelledCount : 0,
			notInstalled    : spellChecker.isFailed(),
			settingsUrl     : spellChecker.getSettingsUrl()
		}
	}

	let text = paper.getText()
	text = removeHtmlBlocks(text)
	text = filterShortcodesFromHTML(text, paper._attributes && paper._attributes.shortcodes)
	text = stripFullTags(text)
	text = neutralizeUrls(text)
	text = neutralizeEmoji(text)
	text = neutralizeDashes(text)

	if (!text) {
		return {
			errors          : [],
			totalWords      : 0,
			misspelledCount : 0
		}
	}

	const words = tokenizeWithPositions(text)
	const errors = []

	for (const { word, start, end } of words) {
		if (isAccepted(word)) {
			continue
		}

		// A rejected compound is rejected because one of its parts is, so re-check
		// the parts and flag only those. The whole token never becomes an error.
		if (hyphenRegex.test(word)) {
			for (const part of splitHyphenatedParts(word, start)) {
				if (!isAccepted(part.word)) {
					errors.push(part)
				}
			}

			continue
		}

		errors.push({ word, start, end })
	}

	return {
		errors,
		totalWords      : words.length,
		misspelledCount : errors.length
	}
}