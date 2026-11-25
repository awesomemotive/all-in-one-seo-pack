// The post content is in the site language, so TruSEO defaults to the site locale, never
// the editor's per-user admin locale. `userLocale` is empty when the site language has no
// AIOSEO dictionary, so the raw site locale covers that case.
export const getTruSeoDefaultLocale = () => window.aioseo?.spellChecker?.userLocale ||
	window.aioseo?.spellChecker?.siteLocale ||
	'en_US'