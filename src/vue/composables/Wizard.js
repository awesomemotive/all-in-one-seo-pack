import { computed, onMounted } from 'vue'

import {
	useLicenseStore,
	useSetupWizardStore
} from '@/vue/stores'

import addons from '@/vue/utils/addons'

import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN

const features = [
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
		value       : 'aioseo-image-seo',
		name        : __('Image SEO', td),
		pluginName  : __('Image SEO', td),
		description : __('Globally control the title, alt text, description and filename for attachment pages & images that are embedded in your content.', td),
		installs    : __('Installs AIOSEO Image SEO', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-image-seo'
	},
	{
		value       : 'aioseo-eeat',
		name        : __('Author SEO', td),
		pluginName  : __('Author SEO (E-E-A-T)', td),
		description : __('Boost your SEO performance by highlighting the professional expertise and trustworthiness of your authors, aligning with Google\'s E-E-A-T standards.', td),
		installs    : __('Installs AIOSEO Author SEO (E-E-A-T)', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-eeat'
	},
	{
		value       : 'aioseo-local-business',
		name        : __('Local SEO', td),
		pluginName  : __('Local Business SEO', td),
		description : __('Tell Google about your business for display as a Knowledge Graph card or business carousel.', td),
		installs    : __('Installs AIOSEO Local SEO', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-local-business'
	},
	{
		value       : 'aioseo-video-sitemap',
		name        : __('Video Sitemap', td),
		pluginName  : __('Video Sitemap', td),
		description : __('Generate an XML Sitemap specifically for videos on your site to help search engines find them.', td),
		installs    : __('Installs AIOSEO Video Sitemap', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-video-sitemap'
	},
	{
		value       : 'aioseo-news-sitemap',
		name        : __('News Sitemap', td),
		pluginName  : __('News Sitemap', td),
		description : __('Submit articles to Google News that were published in the last 48 hours.', td),
		installs    : __('Installs AIOSEO News Sitemap', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-news-sitemap'
	},
	{
		value       : 'aioseo-link-assistant',
		name        : __('Link Assistant', td),
		pluginName  : __('Link Assistant', td),
		description : __('Get relevant suggestions for adding internal links to older content as well as finding any orphaned posts that have no internal links.', td),
		installs    : __('Installs AIOSEO Link Assistant', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-link-assistant'
	},
	{
		value       : 'aioseo-index-now',
		name        : __('Index Now', td),
		pluginName  : __('Index Now', td),
		description : __('Add IndexNow support to instantly notify search engines when your content has changed.', td),
		installs    : __('Installs AIOSEO Index Now', td),
		required    : false,
		pro         : true,
		upgrade     : 'aioseo-index-now'
	},
	{
		value       : 'aioseo-rest-api',
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
	},
	{
		value       : 'email-reports',
		name        : __('Email Reports', td),
		description : __('Stay ahead in SEO with our new email digest! Get the latest tips, trends, and tools delivered right to your inbox, helping you optimize smarter and faster. Enable it today and never miss an update that can take your rankings to the next level.', td),
		required    : false,
		pro         : false,
		upgrade     : false
	}
]

export const useWizard = (params = {}) => {
	const {
		stage
	} = params

	const strings = {
		skipThisStep       : __('Skip this Step', td),
		goBack             : __('Go Back', td),
		saveAndContinue    : __('Save and Continue', td),
		closeAndExit       : __('Close and Exit Wizard Without Saving', td),
		buildABetterAioseo : sprintf(
			// Translators: 1 - Plugin short name ("AIOSEO").
			__('Build a Better %1$s', td),
			import.meta.env.VITE_SHORT_NAME
		),
		getImprovedFeatures : sprintf(
			// Translators: 1 - Plugin short name ("AIOSEO").
			__('Get improved features and faster fixes by sharing non-sensitive data via usage tracking that shows us how %1$s is being used. No personal data is tracked or stored.', td),
			import.meta.env.VITE_SHORT_NAME
		),
		noThanks     : __('No thanks', td),
		yesCountMeIn : __('Yes, count me in!', td)
	}

	const setupWizardStore = useSetupWizardStore()
	const getSelectedUpsellFeatures = computed(() => {
		if (!setupWizardStore.features) {
			return []
		}

		return setupWizardStore.features
			.filter(feature => {
				return needsUpsell(features.find(f => f.value === feature))
			})
			.map(feature => features.find(f => f.value === feature))
	})

	const needsUpsell = (feature) => {
		if (!feature.pro) {
			return false
		}

		const licenseStore = useLicenseStore()
		if (licenseStore.isUnlicensed) {
			return true
		}

		return feature.upgrade && addons.requiresUpgrade(feature.upgrade)
	}

	onMounted(() => {
		if (stage) {
			setupWizardStore.currentStage = stage
		}
	})

	return {
		features,
		getSelectedUpsellFeatures,
		needsUpsell,
		strings
	}
}