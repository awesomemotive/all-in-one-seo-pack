import { setLocaleData } from '@wordpress/i18n'

if (window.aioseoTranslations) {
	setLocaleData(window.aioseoTranslations.translations, import.meta.env.VITE_TEXTDOMAIN)

	if (window.aioseoTranslationsPro && window.aioseoTranslationsPro.translationsPro) {
		setLocaleData(window.aioseoTranslationsPro.translationsPro, import.meta.env.VITE_TEXTDOMAIN_PRO)
	}
} else {
	console.warn('Translations couldn\'t be loaded.')
}

export * from '@wordpress/i18n'