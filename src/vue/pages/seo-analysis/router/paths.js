import { __ } from '@/vue/plugins/translations'

const td       = import.meta.env.VITE_TEXTDOMAIN
const loadView = view => {
	return () => import(`../views/${view}.vue`)
}

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : '/seo-audit-checklist'
	},
	{
		path      : '/seo-audit-checklist',
		name      : 'seo-audit-checklist',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_seo_analysis_settings',
			name   : __('SEO Audit Checklist', td)
		}
	},
	{
		path      : '/analyze-competitor-site',
		name      : 'analyze-competitor-site',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_seo_analysis_settings',
			name   : __('Analyze Competitor Site', td)
		}
	},
	{
		path      : '/headline-analyzer',
		name      : 'headline-analyzer',
		component : loadView('Main'),
		meta      : {
			access : 'aioseo_seo_analysis_settings',
			name   : __('Headline Analyzer', td)
		}
	}
]