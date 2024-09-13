import { __ } from '@/vue/plugins/translations'

import {
	RequiresActivation,
	RequiresUpdate
} from '@/vue/router/middleware'

const td = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/overview'
	},
	{
		path      : '/overview',
		name      : 'overview',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_link_assistant_settings',
			name           : __('Overview', td),
			hideSaveButton : true
		}
	},
	{
		path      : '/links-report',
		name      : 'links-report',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_link_assistant_settings',
			name           : __('Links Report', td),
			hideSaveButton : true,
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-link-assistant',
				routeName : 'overview'
			}
		}
	},
	{
		path      : '/domains-report',
		name      : 'domains-report',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_link_assistant_settings',
			name           : __('Domains Report', td),
			hideSaveButton : true,
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-link-assistant',
				routeName : 'overview'
			}
		}
	},
	{
		path      : '/settings',
		name      : 'settings',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_link_assistant_settings',
			name           : __('Settings', td),
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-link-assistant',
				routeName : 'overview'
			}
		}
	},
	{
		path      : '/post-report',
		name      : 'post-report',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_link_assistant_settings',
			name           : __('Post Report', td),
			hideSaveButton : true,
			middleware     : [ RequiresActivation, RequiresUpdate ],
			middlewareData : {
				addon     : 'aioseo-link-assistant',
				routeName : 'post-report'
			}
		}
	}
]