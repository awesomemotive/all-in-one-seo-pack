import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/keyword-reports'
	},
	{
		path      : '/keyword-reports/:uuid?',
		name      : 'keyword-reports',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_ai_insights_settings',
			name           : __('Keyword Reports', td),
			showSaveButton : false,
			label          : 'beta'
		}
	},
	{
		path      : '/brand-tracker',
		name      : 'brand-tracker',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_ai_insights_settings',
			name           : __('Brand Tracker', td),
			showSaveButton : false,
			label          : 'coming-soon'
		}
	}
]