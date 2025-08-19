import { __ } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

export const ROUTES = {
	HOME       : '/seo-audit-checklist',
	SITE_AUDIT : '/seo-site-audit',
	COMPETITOR : '/analyze-competitor-site',
	HEADLINE   : '/headline-analyzer'
}

const loadView = (view) => {
	return () => import(`../views/${view}.vue`)
}

const createRoute = (path, name, component, metaName) => ({
	path,
	name,
	component : loadView(component),
	meta      : {
		access : 'aioseo_seo_analysis_settings',
		name   : __(metaName, td)
	}
})

export default [
	{
		path     : '/:pathMatch(.*)*',
		redirect : ROUTES.HOME
	},
	createRoute(ROUTES.HOME, 'seo-homepage-audit', 'Main', 'Homepage Audit'),
	createRoute(ROUTES.SITE_AUDIT, 'seo-site-audit', 'Main', 'Site Audit'),
	createRoute(ROUTES.COMPETITOR, 'analyze-competitor-site', 'Main', 'Analyze Competitor Site'),
	createRoute(ROUTES.HEADLINE, 'headline-analyzer', 'Main', 'Headline Analyzer')
]