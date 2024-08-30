import { __ } from '@wordpress/i18n'
import { RequiresUpgrade } from '@/vue/router/middleware'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/dashboard'
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