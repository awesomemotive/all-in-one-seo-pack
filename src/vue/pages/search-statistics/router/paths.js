import { __ } from '@/vue/plugins/translations'
import { RequiresUpgrade } from '@/vue/router/middleware'
import { removeParam } from '@/vue/utils/params'

const td         = import.meta.env.VITE_TEXTDOMAIN
const loadView   = view => {
	return () => import(`../views/${view}.vue`)
}
const initialTab = () => {
	let output = 'dashboard'

	try {
		const params = new URLSearchParams(window.location?.search || '') || {}
		if (params?.has('aioseo-tab')) {
			output = params.get('aioseo-tab')

			removeParam('aioseo-tab')
		}
	} catch (_e) {
		// Do nothing.
	}

	return output
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : `/${initialTab()}`
	},
	{
		path      : '/dashboard',
		name      : 'dashboard',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_search_statistics_settings',
			name           : __('Dashboard', td),
			hideSaveButton : true
		}
	},
	{
		path      : '/seo-statistics',
		name      : 'seo-statistics',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_search_statistics_settings',
			name           : __('SEO Statistics', td),
			hideSaveButton : true
		}
	},
	{
		path      : '/keyword-rank-tracker',
		name      : 'keyword-rank-tracker',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_search_statistics_settings',
			name           : __('Keyword Rank Tracker', td),
			hideSaveButton : true,
			label          : 'new'
		}
	},
	{
		path      : '/content-rankings',
		name      : 'content-rankings',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_search_statistics_settings',
			name           : __('Content Rankings', td),
			hideSaveButton : true
		}
	},
	{
		path      : '/settings',
		name      : 'settings',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_search_statistics_settings',
			name   : __('Settings', td)
		}
	},
	{
		path      : '/post-detail',
		name      : 'post-detail',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_search_statistics_settings',
			name           : __('Post Detail', td),
			hideSaveButton : true,
			licenseFeature : [ 'search-statistics', 'post-detail' ],
			middleware     : [ RequiresUpgrade ],
			middlewareData : {
				routeName : 'dashboard'
			}
		}
	}
]