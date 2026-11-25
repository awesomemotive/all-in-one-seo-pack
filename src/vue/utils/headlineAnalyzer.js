import { useOptionsStore, usePostEditorStore } from '@/vue/stores'
import { isBlockEditor, isClassicEditor, isClassicNoEditor } from '@/vue/utils/context'
import { getTruSeoDefaultLocale } from '@/vue/utils/truSeoLocale'

// Extracts the language subtag from a locale string (en_GB -> en).
const languageOf = (locale) => String(locale || '').toLowerCase().split(/[-_]/)[0]

// The scoring engine matches against hard-coded English word lists and strips the
// headline to ASCII before scoring, so a non-English headline either scores
// meaninglessly or can't be scored at all. Mirrors HeadlineAnalyzer::supportsLocale().
export const supportsHeadlineLocale = (locale) => 'en' === languageOf(locale)

// Mirrors HeadlineAnalyzer::supportsPostType(): the scoring targets editorial
// headlines, and WooCommerce product titles are product names.
export const supportsHeadlinePostType = (postType) => 'product' !== postType

// The per-post TruSEO analysis language wins over the resolved site/user locale.
export const getHeadlineAnalysisLocale = () => {
	const postEditorStore = usePostEditorStore()

	return postEditorStore.currentPost?.truseo_locale || getTruSeoDefaultLocale()
}

// Single source of truth for whether the Headline Analyzer applies to the post being
// edited. The UI gates and the analysis triggers both read this, so hidden UI can't
// leave analysis requests firing behind it.
export const headlineAnalyzerApplies = () => {
	const optionsStore    = useOptionsStore()
	const postEditorStore = usePostEditorStore()

	return !!optionsStore.options.advanced.headlineAnalyzer &&
		(isBlockEditor() || isClassicEditor() || isClassicNoEditor()) &&
		supportsHeadlineLocale(getHeadlineAnalysisLocale()) &&
		supportsHeadlinePostType(postEditorStore.currentPost?.postType)
}