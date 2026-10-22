import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

// Saturated per-analyzer hues for highlight marks. Rendered as a solid
// underline or a translucent background fill depending on the highlighter
// style setting (see buildHighlightStateCss).
export const assessmentColors = Object.freeze({
	textParagraphTooLong : '#EC4899',
	passiveVoice         : '#8B5CF6',
	sentenceBeginnings   : '#F59E0B',
	spellingChecker      : '#DC2626',
	textSentenceLength   : '#3B82F6',
	subheadingsTooLong   : '#14B8A6',
	textAlignment        : '#D946EF',
	textPresence         : '#10B981',
	textTransitionWords  : '#06B6D4',
	wordComplexity       : '#84CC16'
})

// Saturated accents (same hue, 500-shade of each pastel above). Used only for
// the readability checkboxes, where a pastel fill reads as washed out.
export const assessmentAccentColors = Object.freeze({
	textParagraphTooLong : '#EC4899',
	passiveVoice         : '#8B5CF6',
	sentenceBeginnings   : '#EAB308',
	spellingChecker      : '#EF4444',
	textSentenceLength   : '#3B82F6',
	subheadingsTooLong   : '#14B8A6',
	textAlignment        : '#D946EF',
	textPresence         : '#10B981',
	textTransitionWords  : '#06B6D4',
	wordComplexity       : '#84CC16'
})

export const assessmentNames = Object.freeze({
	textParagraphTooLong : __('Paragraph length', td),
	passiveVoice         : __('Passive voice', td),
	sentenceBeginnings   : __('Repeated sentence starts', td),
	spellingChecker      : __('Spelling', td),
	textSentenceLength   : __('Sentence length', td),
	subheadingsTooLong   : __('Subheading distribution', td),
	textAlignment        : __('Text alignment', td),
	textPresence         : __('Content length', td),
	textTransitionWords  : __('Transition words', td),
	wordComplexity       : __('Word complexity', td)
})

// Plain-language explanations shown in a single highlight's popover. Unlike the
// analysis check text, these explain what the check is and why it matters, so
// the tip makes sense on a single highlight.
export const assessmentDescriptions = Object.freeze({
	textParagraphTooLong  : __('Long paragraphs look dense on screen. Splitting them into shorter ones makes your content easier to read.', td),
	passiveVoice          : __('Passive voice (e.g. "mistakes were made") is often wordier and less direct than active voice ("we made mistakes"). Prefer active voice to keep sentences clear.', td),
	sentenceBeginnings    : __('Starting several sentences in a row with the same word reads as repetitive. Varying your openings keeps the writing engaging.', td),
	textSentenceLength    : __('Long sentences are harder to follow. Breaking them into shorter ones makes your content easier to read and scan.', td),
	subheadingsTooLong    : __('Long stretches of text without a subheading are hard to skim. Add subheadings to break big sections into smaller ones.', td),
	textAlignment         : __('Large blocks of centered or justified text are harder to read. Left-aligned text is easiest for most readers.', td),
	textPresence          : __('Search engines need enough text to understand your page. Add more content so your topic comes through clearly.', td),
	textTransitionWords   : __('Transition words — like "however", "because" or "for example" — connect your ideas so your writing flows. Use them to link sentences that build on each other.', td),
	wordComplexity        : __('Long or unusual words slow readers down. Where you can, swap them for simpler alternatives to keep your content accessible.', td),
	keyphraseDistribution : __('Your keyword should appear throughout the post, not clustered in one spot. Spreading it out helps search engines see it\'s the topic of the whole page.', td),
	keyphraseDensity      : __('Keyword density is how often your keyword appears relative to your total word count. Too little and search engines may miss your topic; too much reads as spam.', td),
	textCompetingLinks    : __('Linking to your own pages using your exact keyword makes them compete with this post in search. Vary that link text to avoid it.', td)
})

/**
 * Converts a hex color to rgba.
 *
 * @since 5.0.0
 *
 * @param {string} hex   The hex color string (e.g. '#FF0000').
 * @param {number} alpha The alpha value (0-1).
 * @returns {string}     The rgba string.
 */
export function hexToRgba (hex, alpha = 1) {
	const r = parseInt(hex.slice(1, 3), 16)
	const g = parseInt(hex.slice(3, 5), 16)
	const b = parseInt(hex.slice(5, 7), 16)

	return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

/**
 * Darkens a hex color by mixing it toward black.
 *
 * @param {string} hex    The hex color string (e.g. '#FF0000').
 * @param {number} amount Mix ratio toward black (0-1).
 * @returns {string}      The darkened hex color string.
 */
export function darken (hex, amount = 0.1) {
	const factor = 1 - amount
	const toHex = (value) => Math.max(0, Math.min(255, Math.round(value))).toString(16).padStart(2, '0')

	const r = parseInt(hex.slice(1, 3), 16) * factor
	const g = parseInt(hex.slice(3, 5), 16) * factor
	const b = parseInt(hex.slice(5, 7), 16) * factor

	return `#${toHex(r)}${toHex(g)}${toHex(b)}`
}

/**
 * Gets the color for an assessment identifier.
 *
 * @since 5.0.0
 *
 * @param {string} identifier The assessment identifier.
 * @returns {string}          The hex color string.
 */
export function getAssessmentColor (identifier) {
	return assessmentColors[identifier] || '#E2E8F0'
}

/**
 * Gets the saturated accent color for an assessment identifier.
 *
 * @param {string} identifier The assessment identifier.
 * @returns {string}          The hex color string.
 */
export function getAssessmentAccentColor (identifier) {
	return assessmentAccentColors[identifier] || '#64748B'
}

/**
 * Gets the display name for an assessment identifier.
 *
 * @since 5.0.0
 *
 * @param {string} identifier The assessment identifier.
 * @returns {string}          The display name.
 */
export function getAssessmentName (identifier) {
	return assessmentNames[identifier] || identifier
}

/**
 * Gets the plain-language description for an assessment identifier.
 *
 * @param {string} identifier The assessment identifier.
 * @returns {string}          The description, or an empty string if none.
 */
export function getAssessmentDescription (identifier) {
	return assessmentDescriptions[identifier] || ''
}