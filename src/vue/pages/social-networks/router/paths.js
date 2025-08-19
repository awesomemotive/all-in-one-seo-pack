import { __ } from '@/vue/plugins/translations'
import { removeParam } from '@/vue/utils/params'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}
const initialTab = () => {
	let output = 'social-profiles'

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
		path      : '/social-profiles',
		name      : 'social-profiles',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_social_networks_settings',
			name   : __('Social Profiles', td)
		}
	},
	{
		path      : '/facebook',
		name      : 'facebook',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_social_networks_settings',
			name   : __('Facebook', td)
		}
	},
	{
		path      : '/twitter',
		name      : 'twitter',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_social_networks_settings',
			name   : __('X (Twitter)', td)
		}
	},
	{
		path      : '/pinterest',
		name      : 'pinterest',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_social_networks_settings',
			name   : __('Pinterest', td)
		}
	}
]