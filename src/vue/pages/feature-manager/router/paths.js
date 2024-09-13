import { __ } from '@/vue/plugins/translations'

const td       = import.meta.env.VITE_TEXTDOMAIN
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
		name      : 'feature-manager',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_feature_manager_settings',
			name   : __('Feature Manager', td)
		}
	}
]