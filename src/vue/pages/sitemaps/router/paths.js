import { __ } from '@/vue/plugins/translations'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/general-sitemap'
	},
	{
		path      : '/general-sitemap',
		name      : 'general-sitemap',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_sitemap_settings',
			name   : __('General Sitemap', td)
		}
	},
	{
		path      : '/rss-sitemap',
		name      : 'rss-sitemap',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_sitemap_settings',
			name   : __('RSS Sitemap', td)
		}
	},
	{
		path      : '/video-sitemap',
		name      : 'video-sitemap',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_sitemap_settings',
			name   : __('Video Sitemap', td),
			pro    : true
		}
	},
	{
		path      : '/news-sitemap',
		name      : 'news-sitemap',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_sitemap_settings',
			name   : __('News Sitemap', td),
			pro    : true
		}
	},
	{
		path      : '/html-sitemap',
		name      : 'html-sitemap',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_sitemap_settings',
			name   : __('HTML Sitemap', td)
		}
	}
]