import { __ } from '@/vue/plugins/translations'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/redirects'
	},
	{
		path      : '/redirects',
		name      : 'redirects',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_manage',
			name   : __('Redirects', td)
		}
	},
	{
		path      : '/full-site-redirect',
		name      : 'full-site-redirect',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_manage',
			name   : __('Full Site Redirect', td)
		}
	},
	{
		path      : '/http-headers',
		name      : 'http-headers',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_manage',
			name   : __('HTTP Headers', td)
		}
	},
	{
		path      : '/logs',
		name      : 'logs',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_manage',
			name   : __('Logs', td)
		}
	},
	{
		path      : '/import-export',
		name      : 'import-export',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_settings',
			name   : __('Import / Export', td)
		}
	},
	{
		path      : '/settings',
		name      : 'settings',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_redirects_settings',
			name   : __('Settings', td)
		}
	}
]