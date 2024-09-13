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
		name      : 'dashboard',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_dashboard',
			name   : __('Dashboard', td)
		}
	}
]