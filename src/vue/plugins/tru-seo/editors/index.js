import { isPageBuilderEditor } from '@/vue/utils/context'

import blockEditorAdapter from './blockEditor'
import classicEditorAdapter from './classicEditor'
import elementorAdapter from './elementor'
import divi4Adapter from './divi4'
import divi5Adapter from './divi5'
import elementorV4Adapter from './elementorV4'
import wpbakeryAdapter from './wpbakery'

// Order is precedence, most specific first: an Elementor page also satisfies the block/classic
// probes, V3's probe is also true on a V4 page, and a Gutenberg page can pass the classic probe.
//
// The two Divi adapters are mutually exclusive rather than ordered: Divi 4's probe is "Divi, but not
// Divi 5" ({@see utils/context.js:isDivi4Editor}), so neither can claim the other's builder.
//
// Built on call because of an import cycle (spellingReplace.js imports this module back): a
// module-level array would capture `undefined` for adapters that haven't finished loading.
const getRegistry = () => [ elementorV4Adapter, elementorAdapter, wpbakeryAdapter, divi5Adapter, divi4Adapter, blockEditorAdapter, classicEditorAdapter ]

let resolved = null,
	hasResolved = false,
	generation  = 0

/**
 * Picks the first active adapter from a registry.
 *
 * A probe that throws is treated as inactive: a missing editor global must
 * disable highlighting, never surface as an error inside the editor.
 *
 * @param   {Array} candidates The adapters to probe, in precedence order.
 * @returns {Object|null}      The active adapter, or null.
 */
export const pickAdapter = (candidates) => {
	for (const candidate of (candidates || [])) {
		try {
			if (candidate?.isActive?.()) {
				return candidate
			}
		} catch (_e) {
			continue
		}
	}

	return null
}

/**
 * Resolves the active adapter once per page load.
 *
 * @returns {Object|null} The active adapter, or null.
 */
export const resolveAdapter = () => {
	if (!hasResolved) {
		hasResolved = true
		resolved    = pickAdapter(getRegistry())
	}

	return resolved
}

/**
 * Clears the memoised adapter so the next resolveAdapter() call re-probes the
 * registry.
 *
 * NOTE: legitimate outside tests too, whenever the active editor can change
 * after boot (e.g. a page-builder toggle) and the memoised answer goes stale.
 *
 * @returns {void}
 */
export const resetAdapter = () => {
	resolved    = null
	hasResolved = false
	generation++
}

/**
 * How many times the memoised adapter has been invalidated.
 *
 * NOTE: lets a caller that kept an adapter notice it went stale
 * ({@see composables/TruSeoHighlighter.js:syncAdapter}).
 *
 * @returns {number} The current generation.
 */
export const getAdapterGeneration = () => generation

/**
 * Whether the active editor can serve document-scoped analysis features (e.g. the Spelling tab).
 *
 * NOTE: keyed on `ownsPageBuilder`, not `analyzeOnly` (which only says whether it paints).
 *
 * @returns {boolean} Whether the active editor is analyze-capable.
 */
export const isAnalyzeCapableEditor = () => !isPageBuilderEditor() || !!resolveAdapter()?.ownsPageBuilder