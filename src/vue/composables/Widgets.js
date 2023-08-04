import {
	useRootStore
} from '@/vue/stores'

import { __, sprintf } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useWidgets = ({ name }) => {
	const rootStore = useRootStore()
	let strings = {
		names : {
			htmlSitemap : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__('%1$s - HTML Sitemap', td),
				import.meta.env.VITE_SHORT_NAME
			),
			breadcrumbs : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__('%1$s - Breadcrumbs', td),
				import.meta.env.VITE_SHORT_NAME
			),
			localOpeningHours : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__('%1$s Local - Opening Hours', td),
				import.meta.env.VITE_SHORT_NAME
			),
			localMap : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__('%1$s Local - Map', td),
				import.meta.env.VITE_SHORT_NAME
			),
			localBusinessInfo : sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__('%1$s Local - Business Info', td),
				import.meta.env.VITE_SHORT_NAME
			)
		},
		noWidgetSupport : __('This functionality relies on widget support, which is not available in your current theme.', td)
	}

	strings = {
		...strings,
		visitWidgetsPage : rootStore.aioseo.theme.features?.widgets
			? sprintf(
				// Translators: 1 - Opening HTML link tag, 2 - Closing HTML link tag, 3 - Widget name
				__('To add this widget, visit the %1$swidgets page%2$s and look for the "%3$s" widget.', td),
				`<a href="${rootStore.aioseo.urls.admin.widgets}" target="_blank">`,
				'</a>',
				strings.names[name]
			)
			: strings.noWidgetSupport
	}

	return {
		strings
	}
}