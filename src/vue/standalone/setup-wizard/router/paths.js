import { __ } from '@/vue/plugins/translations'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/welcome'
	},
	{
		path      : '/welcome',
		name      : 'welcome',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Welcome', td)
		}
	},
	{
		path      : '/import',
		name      : 'import',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Import', td)
		}
	},
	{
		path      : '/category',
		name      : 'category',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Category', td)
		}
	},
	{
		path      : '/additional-information',
		name      : 'additional-information',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Additional Site Information', td)
		}
	},
	{
		path      : '/features',
		name      : 'features',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Choose Features', td)
		}
	},
	{
		path      : '/search-appearance',
		name      : 'search-appearance',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Search Appearance', td)
		}
	},
	{
		path      : '/search-console',
		name      : 'search-console',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Search Console', td)
		}
	},
	{
		path      : '/smart-recommendations',
		name      : 'smart-recommendations',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Smart Recommendations', td)
		}
	},
	{
		path      : '/license-key',
		name      : 'license-key',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('License Key', td)
		}
	},
	{
		path      : '/success',
		name      : 'success',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_setup_wizard',
			name   : __('Success', td)
		}
	}
]