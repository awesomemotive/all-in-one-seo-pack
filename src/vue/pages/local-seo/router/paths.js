import { __ } from '@wordpress/i18n'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/locations'
	},
	{
		path      : '/locations',
		name      : 'locations',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_local_seo_settings',
			name   : __('Locations', td)
		}
	},
	{
		path      : '/opening-hours',
		name      : 'opening-hours',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_local_seo_settings',
			name   : __('Opening Hours', td)
		}
	},
	{
		path      : '/maps',
		name      : 'maps',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_local_seo_settings',
			name   : __('Maps', td)
		}
	},
	{
		path      : '/import',
		name      : 'import',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_local_seo_settings',
			name   : __('Import', td)
		}
	}
]