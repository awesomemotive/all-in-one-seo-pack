import { computed } from 'vue'

import {
	usePostEditorStore,
	useSpellCheckerDictionaryStore,
	useTruSeoHighlighterStore
} from '@/vue/stores'

import { __, sprintf } from '@/vue/plugins/translations'
import { useTruSeoScore } from '@/vue/composables/TruSeoScore'

const td = import.meta.env.VITE_TEXTDOMAIN

// Locale display order, roughly following WordPress.org "Languages in use"
// popularity. Locales not listed here fall back to alphabetical order.
const LOCALE_POPULARITY = [
	'ja', 'de_DE', 'es_ES', 'fr_FR', 'pt_BR', 'it_IT', 'ru_RU', 'nl_NL',
	'id_ID', 'pl_PL', 'tr_TR', 'sv_SE', 'cs_CZ', 'el', 'hu_HU', 'fa_IR',
	'he_IL', 'nb_NO', 'ca', 'sk_SK', 'ar'
]

// Extracts the 2-letter language code from a locale string (e.g. en_GB -> en).
const codeOf = (locale) => (locale || '').split(/[-_]/)[0]

const hasVariants = (l) => Array.isArray(l.variants) && 0 < l.variants.length

export function useTruSeoLocale () {
	const postEditorStore        = usePostEditorStore()
	const spellCheckerDictStore  = useSpellCheckerDictionaryStore()
	const truSeoHighlighterStore = useTruSeoHighlighterStore()
	const { runAnalysis }        = useTruSeoScore()

	const strings = {
		description : __('Select the language used to analyze the content.', td)
	}

	// Resolved user locale (already mapped through `resolveUserLocale` on the PHP
	// side — an en_GB site resolves to en_GB, en_NZ falls back to en_US) is what
	// powers the "Default" option and the per-site dialect default.
	const userResolvedLocale = computed(() => spellCheckerDictStore.userLocale)
	const userResolvedLabel  = computed(() => spellCheckerDictStore.languageLabel)
	const userResolvedCode   = computed(() => codeOf(userResolvedLocale.value))

	// The per-post override, a full locale string ('' = inherit the site default).
	const storedLocale = computed(() => postEditorStore.currentPost?.truseo_locale || '')

	const defaultLabel = computed(() => {
		if (userResolvedLabel.value) {
			return sprintf(
				// Translators: 1 - The name of the user's current WordPress language (e.g. "English").
				__('Default (%1$s)', td),
				userResolvedLabel.value
			)
		}

		return __('Default (User Language)', td)
	})

	// One row per language (never per dialect). The resolved language is dropped
	// because it is already represented by the "Default" option.
	const localeOptions = computed(() => {
		const languages = spellCheckerDictStore.supportedLanguages
			.filter(l => codeOf(l.locale) !== userResolvedCode.value)
			.map(l => ({ value: l.locale, label: l.nativeLabel || l.label, hasSpellChecker: !!l.hasSpellChecker }))
			.sort((a, b) => {
				const ia = LOCALE_POPULARITY.indexOf(a.value)
				const ib = LOCALE_POPULARITY.indexOf(b.value)
				const ra = -1 === ia ? Number.MAX_SAFE_INTEGER : ia
				const rb = -1 === ib ? Number.MAX_SAFE_INTEGER : ib

				return ra === rb ? a.label.localeCompare(b.label) : ra - rb
			})

		return [
			{ value: '', label: defaultLabel.value, hasSpellChecker: spellCheckerDictStore.isLocaleSpellCheckable(userResolvedLocale.value) },
			...languages
		]
	})

	// Selects the language option matching the stored locale by code, so a dialect
	// override (e.g. de_AT) still resolves to its parent language (German). Falls
	// back to the "Default" option, which also covers the resolved language.
	const getSelectedOption = () => {
		const stored = storedLocale.value
		if (!stored) {
			return localeOptions.value[0]
		}

		const code = codeOf(stored)

		return localeOptions.value.find(opt => opt.value && codeOf(opt.value) === code) || localeOptions.value[0]
	}

	// Side effects for a locale change: wipe stale highlights, install the target
	// dictionary, re-run analysis, re-enable highlighting. Analysis itself is
	// language-only (the worker derives the code from the locale).
	const applyLocaleChange = async (newLocale, label) => {
		postEditorStore.currentPost.truseo_locale = newLocale

		// Wipe stale highlight marks from the previous locale and arm the
		// auto-enable flag so the first analysis to land re-enables highlighting
		// with every available analyzer checked.
		const languageSwitchId = truSeoHighlighterStore.beginLanguageSwitch()

		// Resolve the effective locale (empty value = inherit the site default).
		const effectiveLocale = newLocale || userResolvedLocale.value

		// Show the badge spinner for the whole switch — dictionary install through
		// re-analysis — so it never flickers back to the stale score in between.
		postEditorStore.currentPost.loading.score = true
		try {
			// Install the dictionary for the target locale (no-op when already
			// installed or unsupported). Block re-analysis until it settles so the
			// worker can load the correct dictionary files.
			await spellCheckerDictStore.downloadForLocale(effectiveLocale, label)

			await runAnalysis({ postId: postEditorStore.currentPost.id })
		} finally {
			postEditorStore.currentPost.loading.score = false
			truSeoHighlighterStore.endLanguageSwitch(languageSwitchId)
		}

		// Enable highlight + check every analyzer that already has sentences.
		// `spellingChecker` typically isn't ready yet — TruSeoWrapper's
		// `onSpellCheckerReady` callback runs this again once the dictionary
		// finishes loading inside the worker.
		if (truSeoHighlighterStore.awaitingFreshHighlights) {
			truSeoHighlighterStore.enableAllAvailableHighlights()
		}
	}

	// ---- Single sectioned control ----------------------------------------------
	// One menu instead of two pills: languages with dialects become a titled group
	// with indented variant rows; single-standard languages are flat rows.

	// The effective locale drives which row is checked and what the trigger shows —
	// the per-post override if set, otherwise the resolved site default.
	const effectiveLocale = computed(() => storedLocale.value || userResolvedLocale.value)

	const rankOf = (locale) => {
		const index = LOCALE_POPULARITY.indexOf(locale)

		return -1 === index ? Number.MAX_SAFE_INTEGER : index
	}

	// Menu items: `{ type: 'group', label, options: [...] }` for languages with
	// dialects, `{ type: 'option', value, label, hasSpellChecker }` otherwise.
	// Grouped languages come first, then the flat ones; within each block the
	// active language sorts first, then popularity, then alphabetically.
	const menuItems = computed(() => {
		const activeCode = codeOf(effectiveLocale.value)

		const compare = (a, b) => {
			const aActive = a.code === activeCode ? 0 : 1
			const bActive = b.code === activeCode ? 0 : 1
			if (aActive !== bActive) {
				return aActive - bActive
			}

			const ra = rankOf(a.locale)
			const rb = rankOf(b.locale)

			return ra === rb ? (a.nativeLabel || a.label).localeCompare(b.nativeLabel || b.label) : ra - rb
		}

		// Match on the native + English name plus the language code and locale, so
		// "nl", "Dutch", or "Nederlands" all find Dutch.
		const toItem = (l) => {
			const label    = l.nativeLabel || l.label
			const keywords = `${label} ${l.label} ${l.code} ${l.locale}`.toLowerCase()
			if (hasVariants(l)) {
				return {
					type    : 'group',
					label,
					keywords,
					options : l.variants.map(v => ({ value: v.locale, label: v.label, keywords: `${v.label} ${v.locale}`.toLowerCase(), hasSpellChecker: true }))
				}
			}

			return { type: 'option', value: l.locale, label, keywords, hasSpellChecker: !!l.hasSpellChecker }
		}

		const languages = spellCheckerDictStore.supportedLanguages
		const grouped   = languages.filter(hasVariants).sort(compare).map(toItem)
		const flat      = languages.filter(l => !hasVariants(l)).sort(compare).map(toItem)

		return [ ...grouped, ...flat ]
	})

	const selectedLocaleValue = computed(() => effectiveLocale.value)

	// The trigger shows the full current selection — the dialect label for languages
	// with variants (e.g. "English (US)"), the language name otherwise.
	const selectedLocaleLabel = computed(() => {
		const target = effectiveLocale.value

		for (const item of menuItems.value) {
			if ('group' === item.type) {
				const hit = item.options.find(option => option.value === target)
				if (hit) {
					return hit.label
				}

				continue
			}

			if (item.value === target) {
				return item.label
			}
		}

		return userResolvedLabel.value || ''
	})

	const onSelect = (option) => applyLocaleChange(option.value, option.label)

	return {
		localeOptions,
		getSelectedOption,
		menuItems,
		selectedLocaleValue,
		selectedLocaleLabel,
		onSelect,
		userResolvedLabel,
		strings
	}
}