import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useWebmasterTools = () => {
	const strings = {
		installMi : sprintf(
			// Translators: 1 - A plugin name (e.g. "MonsterInsights", "Broken Link Checker", etc.).
			__('Install %1$s', td),
			'MonsterInsights'
		),
		miInstalled : __('Success!', td)
	}

	return {
		strings
	}
}