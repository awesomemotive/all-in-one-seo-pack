import { __ } from '@/vue/plugins/translations'
import { removeParam } from '@/vue/utils/params'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}
const initialTab = () => {
	let output = 'general-settings'

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
		path      : '/general-settings',
		name      : 'general-settings',
		component : loadView('Main'),
		meta      : {
			access         : 'aioseo_general_settings',
			hideSaveButton : true,
			name           : window.aioseo.data.isNetworkAdmin
				? __('Network Settings', td)
				: __('General Settings', td)
		}
	},
	{
		path      : '/webmaster-tools',
		name      : 'webmaster-tools',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_general_settings',
			name   : __('Webmaster Tools', td)
		}
	},
	{
		path      : '/ai-content',
		name      : 'ai-content',
		component : loadView('Main'),
		meta      : {
			home   : 'ai-content',
			access : 'aioseo_general_settings',
			name   : __('AI Content', td),
			label  : 'new'
		}
	},
	{
		path      : '/breadcrumbs',
		name      : 'breadcrumbs',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_general_settings',
			name   : __('Breadcrumbs', td)
		}
	},
	{
		path      : '/rss-content',
		name      : 'rss-content',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_general_settings',
			name   : __('RSS Content', td)
		}
	},
	{
		path      : '/writing-assistant',
		name      : 'writing-assistant',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_page_writing_assistant_settings',
			name   : __('Writing Assistant', td)
		}
	},
	{
		path      : '/access-control',
		name      : 'access-control',
		component : loadView('Main'),
		meta      : {
			home   : 'general-settings',
			access : 'aioseo_admin',
			name   : __('Access Control', td)
		}
	},
	{
		path      : '/advanced',
		name      : 'advanced',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_general_settings',
			name   : __('Advanced', td)
		}
	}
]