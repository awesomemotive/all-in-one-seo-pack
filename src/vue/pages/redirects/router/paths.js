import { __ } from '@/vue/plugins/translations'

import {
	RequiresActivation,
	RequiresUpdate
} from '@/vue/router/middleware'

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
			access         : 'aioseo_redirects_manage',
			name           : __('Full Site Redirect', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-redirects',
				routeName : 'redirects'
			}
		}
	},
	{
		path      : '/logs',
		name      : 'logs',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_redirects_manage',
			name           : __('Logs', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-redirects',
				routeName : 'redirects'
			}
		}
	},
	{
		path      : '/404-logs',
		name      : 'logs-404',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_redirects_manage',
			name           : __('404 Logs', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-redirects',
				routeName : 'redirects'
			}
		}
	},
	{
		path      : '/import-export',
		name      : 'import-export',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_redirects_settings',
			name           : __('Import / Export', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-redirects',
				routeName : 'redirects'
			}
		}
	},
	{
		path      : '/settings',
		name      : 'settings',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_redirects_settings',
			name           : __('Settings', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-redirects',
				routeName : 'redirects'
			}
		}
	}
]