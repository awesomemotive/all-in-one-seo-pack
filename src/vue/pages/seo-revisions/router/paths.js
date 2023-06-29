import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/'
	},
	{
		path      : '/',
		name      : 'seo-revisions',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_page_seo_revisions_settings',
			name   : __('SEO Revisions Comparison', td)
		}
	}
]