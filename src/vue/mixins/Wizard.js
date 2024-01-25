import {
	useLicenseStore,
	useSetupWizardStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

import { __ } from '@wordpress/i18n'
const td = import.meta.env.VITE_TEXTDOMAIN

export const Wizard = {
	data () {
		return {
			features : [
				{
					value       : 'optimized-search-appearance',
					name        : __('Optimized Search Appearance', td),
					description : __('Get all the right tools to make sure your website shows up in Google Search.', td),
					required    : true,
					pro         : false,
					upgrade     : false
				},
				{
					value       : 'sitemaps',
					name        : __('Sitemaps', td),
					description : __(
						'Sitemaps are a list of all your content that search engines use when they crawl your site.',
						td
					),
					required : true,
					pro      : false,
					upgrade  : false
				},
				{
					value       : 'broken-link-checker',
					name        : __('Broken Link Checker', td),
					pluginName  : __('Broken Link Checker', td),
					description : __('Get the best tool to monitor your site for broken links and easily fix them to improve your SEO.', td),
					installs    : __('Installs Broken Link Checker', td),
					required    : false,
					pro         : false,
					upgrade     : false
				},
				{
					value       : 'analytics',
					name        : __('Analytics', td),
					pluginName  : __('MonsterInsights Free', td),
					description : __('Get the #1 analytics plugin to see how people find and use your website. Simply put, see stats that matter.', td),
					installs    : __('Installs MonsterInsights Free', td),
					required    : false,
					pro         : false,
					upgrade     : false
				},
				{
					value       : 'conversion-tools',
					name        : __('Conversion Tools', td),
					pluginName  : __('OptinMonster', td),
					description : __('Get the #1 conversion optimization plugin to convert your growing website traffic into subscribers, leads and sales.', td),
					installs    : __('Installs OptinMonster', td),
					required    : false,
					pro         : false,
					upgrade     : false
				},
				{
					value       : 'image-seo',
					name        : __('Image SEO', td),
					pluginName  : __('Image SEO', td),
					description : __('Globally control Title and Alt attributes for attachment pages and images that are embedded in your content.', td),
					installs    : __('Installs AIOSEO Image SEO', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-image-seo'
				},
				{
					value       : 'local-seo',
					name        : __('Local SEO', td),
					pluginName  : __('Local Business SEO', td),
					description : __('Tell Google about your business for display as a Knowledge Graph card or business carousel.', td),
					installs    : __('Installs AIOSEO Local SEO', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-local-business'
				},
				{
					value       : 'video-sitemap',
					name        : __('Video Sitemap', td),
					pluginName  : __('Video Sitemap', td),
					description : __('Generate an XML Sitemap specifically for video content on your site.', td),
					installs    : __('Installs AIOSEO Video Sitemap', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-video-sitemap'
				},
				{
					value       : 'news-sitemap',
					name        : __('News Sitemap', td),
					pluginName  : __('News Sitemap', td),
					description : __('Submit articles to Google News that were published in the last 48 hours.', td),
					installs    : __('Installs AIOSEO News Sitemap', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-news-sitemap'
				},
				{
					value       : 'redirects',
					name        : __('Smart Redirects + 404 Detection', td),
					pluginName  : __('Redirects', td),
					description : __('Create and manage redirects for your broken links.', td),
					installs    : __('Installs AIOSEO Redirects', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-redirects'
				},
				{
					value       : 'link-assistant',
					name        : __('Link Assistant', td),
					pluginName  : __('Link Assistant', td),
					description : __('Get relevant suggestions for adding internal links to older content as well as finding any orphaned posts that have no internal links.', td),
					installs    : __('Installs AIOSEO Link Assistant', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-link-assistant'
				},
				{
					value       : 'index-now',
					name        : __('Index Now', td),
					pluginName  : __('Index Now', td),
					description : __('Add IndexNow support to instantly notify search engines when your content has changed.', td),
					installs    : __('Installs AIOSEO Index Now', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-index-now'
				},
				{
					value       : 'rest-api',
					name        : __('REST API', td),
					pluginName  : __('REST API', td),
					description : __('Manage your post and term SEO meta via the WordPress REST API. This addon also works seamlessly with headless WordPress installs.', td),
					installs    : __('Installs AIOSEO REST API', td),
					required    : false,
					pro         : true,
					upgrade     : 'aioseo-rest-api'
				},
				{
					value       : 'advanced-schema',
					name        : __('Advanced Rich Snippets + Schema Markups', td),
					description : __('Complete support for schema markup so you can get more clicks and traffic with rich snippets.', td),
					required    : false,
					pro         : true,
					upgrade     : false
				}
			]
		}
	},
	computed : {
		getSelectedUpsellFeatures () {
			const setupWizardStore = useSetupWizardStore()
			if (!setupWizardStore.features) {
				return []
			}

			return setupWizardStore.features
				.filter(feature => {
					return this.needsUpsell(this.features.find(f => f.value === feature))
				})
				.map(feature => this.features.find(f => f.value === feature))
		}
	},
	methods : {
		needsUpsell (feature) {
			if (!feature.pro) {
				return false
			}

			const licenseStore = useLicenseStore()
			if (licenseStore.isUnlicensed) {
				return true
			}

			return feature.upgrade && addons.requiresUpgrade(feature.upgrade)
		}
	},
	mounted () {
		const setupWizardStore        = useSetupWizardStore()
		setupWizardStore.currentStage = this.stage
	}
}