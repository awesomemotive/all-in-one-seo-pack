import {
	useRootStore
} from '@/vue/stores'

import { sprintf } from '@/vue/plugins/translations'
const marketingSite = 'https://aioseo.com/'
const docLinks      = {
	home                          : `${marketingSite}docs/`,
	ultimateGuide                 : `${marketingSite}ultimate-wordpress-seo-guide/`,
	quickStartGuide               : `${marketingSite}docs/quick-start-guide/`,
	googleSearchConsole           : `${marketingSite}docs/how-to-verify-your-site-with-google-search-console/`,
	bingWebmasterVerification     : `${marketingSite}docs/how-to-verify-your-site-with-bing-webmaster-tools/`,
	yandexWebmasterVerification   : `${marketingSite}docs/how-to-verify-your-site-with-yandex-webmaster-tools/`,
	baiduWebmasterVerification    : `${marketingSite}docs/baidu-webmaster-tools-verification/`,
	pinterestSiteVerification     : `${marketingSite}docs/how-to-verify-your-site-with-pinterest/`,
	indexNow                      : `${marketingSite}docs/integrating-with-indexnow-to-instantly-re-index-your-content/`,
	microsoftClarityDocumentation : `${marketingSite}docs/how-to-verify-your-site-with-microsoft-clarity/`,
	gtmContainerId                : `${marketingSite}docs/how-to-connect-your-site-with-google-tag-manager/`,
	rssContent                    : `${marketingSite}docs/how-to-protect-your-content-with-rss-content-settings/`,
	twitter                       : `${marketingSite}docs/beginners-guide-to-social-networks-settings-for-twitter/`,
	facebook                      : `${marketingSite}docs/beginners-guide-to-social-networks-settings-for-facebook/`,
	xmlSitemaps                   : `${marketingSite}docs/how-to-create-an-xml-sitemap/`,
	blankSitemap                  : `${marketingSite}docs/how-to-fix-a-404-error-when-viewing-your-sitemap/`,
	sitemapIndexes                : `${marketingSite}docs/using-sitemap-indexes-and-pagination/`,
	maxLinks                      : `${marketingSite}docs/using-sitemap-indexes-and-pagination/`,
	maxLinksRss                   : `${marketingSite}docs/how-to-create-an-rss-sitemap/#configuring-the-rss-sitemap`,
	selectPostTypes               : `${marketingSite}docs/choosing-which-content-to-include-in-your-xml-sitemap/`,
	selectPostTypesColumns        : `${marketingSite}docs/hiding-the-aioseo-column-on-all-posts-screens/`,
	selectPostTypesNews           : `${marketingSite}docs/how-to-create-a-google-news-sitemap/#configuring-the-news-sitemap`,
	selectPostTypesVideo          : `${marketingSite}docs/how-to-create-a-video-sitemap/#configuring-the-video-sitemap`,
	selectPostTypesRss            : `${marketingSite}docs/how-to-create-an-rss-sitemap/#configuring-the-rss-sitemap`,
	selectTaxonomies              : `${marketingSite}docs/choosing-which-content-to-include-in-your-xml-sitemap/`,
	selectTaxonomiesColumns       : `${marketingSite}docs/hiding-the-aioseo-column-on-taxonomy-screens/`,
	selectTaxonomiesVideo         : `${marketingSite}docs/how-to-create-a-video-sitemap/#configuring-the-video-sitemap`,
	includeArchivePages           : `${marketingSite}docs/including-date-and-author-archives-in-your-xml-sitemap/`,
	excludeImages                 : `${marketingSite}docs/excluding-images-from-the-xml-sitemap/`,
	dynamicallyGenerate           : `${marketingSite}docs/what-is-a-dynamically-generated-sitemap-and-why-is-it-better-to-use/`,
	dynamicallyGenerateVideo      : `${marketingSite}docs/what-is-a-dynamically-generated-sitemap-and-why-is-it-better-to-use/`,
	videoSitemaps                 : `${marketingSite}docs/how-to-create-a-video-sitemap/`,
	includeCustomFields           : `${marketingSite}docs/including-videos-in-custom-fields-in-your-video-sitemap/`,
	newsSitemaps                  : `${marketingSite}docs/how-to-create-a-google-news-sitemap/`,
	rssSitemaps                   : `${marketingSite}docs/how-to-create-an-rss-sitemap/`,
	facebookAdminId               : `${marketingSite}docs/adding-your-facebook-admin-id/`,
	facebookAppId                 : `${marketingSite}docs/adding-your-facebook-app-id/`,
	facebookAuthorUrl             : `${marketingSite}docs/setting-the-content-author-for-facebook/`,
	usageTracking                 : `${marketingSite}docs/usage-tracking/`,
	schemaSettings                : `${marketingSite}docs/schema-settings/`,
	imageSeo                      : `${marketingSite}docs/image-seo-module/`,
	localSeo                      : `${marketingSite}introducing-local-seo/`,
	robotsEditor                  : `${marketingSite}docs/using-the-robots-txt-tool-in-all-in-one-seo/`,
	robotsRewrite                 : `${marketingSite}docs/nginx-rewrite-rules-for-robots-txt/`,
	useKeyphrasesTooltip          : `${marketingSite}docs/using-the-focus-keyphrase-to-analyze-your-content/`,
	showSitelinks                 : `${marketingSite}docs/how-do-i-get-google-to-show-sitelinks-for-my-site/`,
	whenToUseNoindex              : `${marketingSite}docs/when-to-use-noindex-or-the-robots-txt/`,
	installAioseoPro              : `${marketingSite}docs/installing-all-in-one-seo-pro/`,
	importProcessSeoData          : `${marketingSite}docs/importing-and-exporting-aioseo-settings-and-meta-data/`,
	whatAreMediaAttachments       : `${marketingSite}docs/what-are-media-attachments-and-should-i-submit-them-to-search-engines/`,
	minimumRequirements           : `${marketingSite}docs/what-are-the-minimum-requirements-for-all-in-one-seo-pack/`,
	apiCodeExamples               : `${marketingSite}docs/how-do-i-use-your-api-code-examples/`,
	troubleshootIssues            : `${marketingSite}docs/how-to-troubleshoot-issues-with-all-in-one-seo-pack/`,
	staticHomePage                : `${marketingSite}docs/setting-the-seo-for-your-home-page/#setting-the-seo-when-your-homepage-displays-a-static-page`,
	staticHomePageFacebook        : `${marketingSite}docs/setting-facebook-social-meta-for-your-homepage/#setting-the-facebook-social-meta-when-your-homepage-displays-a-static-page`,
	staticHomePageTwitter         : `${marketingSite}docs/setting-twitter-social-meta-for-your-homepage/#setting-the-twitter-social-meta-when-your-homepage-displays-a-static-page`,
	restApi                       : `${marketingSite}docs/aioseo-uses-rest-api/`,
	configuringSchema             : `${marketingSite}docs/configuring-the-schema-settings-in-all-in-one-seo/`,
	unfilteredHtml                : `${marketingSite}docs/unfiltered-html-capability/`,
	customFields                  : `${marketingSite}docs/including-custom-fields-in-the-seo-page-analysis/`,
	productIdentifiers            : `${marketingSite}docs/unique-product-identifiers/`,
	redirectManagerRegex          : `${marketingSite}docs/redirect-manager-regex/`,
	redirectGdpr                  : `${marketingSite}docs/redirect-gdpr-privacy-information/`,
	redirectCustomRulesUserAgent  : `${marketingSite}docs/redirection-manager-custom-rules/#user-agent`,
	redirectCanonicalHttps        : `${marketingSite}docs/full-site-redirect/#canonical-settings`,
	redirectUnknownWebserver      : `${marketingSite}docs/redirect-manager-unknown-web-server/`,
	redirectServerConfigReload    : `${marketingSite}docs/redirect-manager-configuration-reload/`,
	localSeoShortcodeBusinessInfo : `${marketingSite}docs/shortcode-aioseo_local_business_info/`,
	localSeoShortcodeOpeningHours : `${marketingSite}docs/shortcode-aioseo_local_opening_hours/`,
	localSeoShortcodeLocations    : `${marketingSite}docs/shortcode-aioseo_local_locations/`,
	localSeoShortcodeMap          : `${marketingSite}docs/shortcode-aioseo_local_map/`,
	localSeoFunctionBusinessInfo  : `${marketingSite}docs/function-aioseo_local_business_info/`,
	localSeoFunctionOpeningHours  : `${marketingSite}docs/function-aioseo_local_opening_hours/`,
	localSeoFunctionLocations     : `${marketingSite}docs/function-aioseo_local_locations/`,
	localSeoFunctionMap           : `${marketingSite}docs/function-aioseo_local_map/`,
	localSeoSearchQueryConflict   : `${marketingSite}docs/enhanced-search-query-conflict/`,
	localSeoMapSetup              : `${marketingSite}docs/setting-up-google-maps/`,
	localSeoMapEmbedApi           : `${marketingSite}docs/using-places-on-your-maps/`,
	breadcrumbsDisplay            : `${marketingSite}docs/displaying-breadcrumbs-on-your-site/`,
	breadcrumbsShortcode          : `${marketingSite}docs/shortcode-aioseo_breadcrumbs/`,
	breadcrumbsFunction           : `${marketingSite}docs/function-aioseo_breadcrumbs/`,
	seoAnalyzer                   : `${marketingSite}docs/using-the-seo-analysis-tool/`,
	seoAnalyzerIssues             : `${marketingSite}docs/seo-analysis-unable-to-connect-to-your-site/`,
	htmlSitemap                   : `${marketingSite}docs/html-sitemap/`,
	htmlSitemapShortcode          : `${marketingSite}docs/shortcode-html-sitemap/`,
	htmlSitemapFunction           : `${marketingSite}docs/function-html-sitemap/`,
	htmlSitemapCompactArchives    : `${marketingSite}docs/html-sitemap#compact-archives/`,
	linkAssistant                 : `${marketingSite}docs/link-assistant`,
	linkAssistantPostTypes        : `${marketingSite}docs/link-assistant-settings/#post-types`,
	linkAssistantPostStatuses     : `${marketingSite}docs/link-assistant-settings/#post-statuses`,
	updateWordPress               : `${marketingSite}docs/update-wordpress/`,
	runningShortcodes             : `${marketingSite}docs/running-shortcodes/`,
	crawlCleanup                  : `${marketingSite}docs/crawl-cleanup-best-practices`,
	schema                        : `${marketingSite}docs/a-guide-to-schema-org-markup-for-rich-snippets/`,
	schemaJsonLd                  : `${marketingSite}docs/a-guide-to-schema-org-markup-for-rich-snippets/#schema-markup-in-all-in-one-seo`,
	smartTags                     : `${marketingSite}docs/using-the-smart-tags-in-titles-and-descriptions/`,
	openAi                        : `${marketingSite}docs/using-openai-to-generate-seo-titles-and-meta-descriptions/`,
	wpcode                        : `${marketingSite}docs/wpcode-snippet-library/`,
	primaryTerm                   : `${marketingSite}docs/setting-the-primary-term-for-breadcrumbs/`,
	cornerstoneContent            : `${marketingSite}docs/cornerstone-content/`,
	eeat                          : `${marketingSite}docs/adding-author-seo-e-e-a-t-to-your-site/`,
	eeatAuthorBioInjection        : `${marketingSite}docs/adding-author-seo-e-e-a-t-to-your-site/#aioseo-automatically-displaying-the-author-excerpt`,
	queryArgMonitor               : `${marketingSite}docs/using-the-query-arg-monitoring-in-all-in-one-seo/`,
	businessPhoneNumber           : `${marketingSite}docs/best-business-phone-services`,
	keywordRankTracker            : `${marketingSite}docs/using-the-keyword-rank-tracker-feature-in-search-statistics/`
}

const upsellLinks = {
	home           : marketingSite,
	liteUpgrade    : `${marketingSite}lite-upgrade/`,
	pricing        : `${marketingSite}pricing/`,
	semrushPricing : `${marketingSite}semrush-pricing/`
}

const getUpsellUrl = (medium, content = null, link) => {
	if ('feature-manager-upgrade' === medium && 'no-license-key' !== content) {
		const feature = 'aioseo-local-business' === content
			? '&features[]=local-seo'
			: '&features[]=' + (content ? content.replace('aioseo-', '') : '')

		// This ensures that we only show plans on the pricing page that include the relevant addon.
		return utmUrl(medium, content, upsellLinks[link]) + feature
	}

	return utmUrl(medium, content, upsellLinks[link])
}

const getDocUrl = (link) => {
	return utmUrl('documentation', link, docLinks[link])
}

const getUpsellLink = (medium, text, link, addArrow = false) => {
	const arrow = addArrow
		? sprintf(
			'<a href="%1$s" class="no-underline" target="_blank">&nbsp;&rarr;</a>',
			utmUrl(medium, link, upsellLinks[link])
		)
		: ''
	return sprintf(
		'<a href="%1$s" target="_blank">%2$s</a>%3$s',
		utmUrl(medium, link, upsellLinks[link]),
		text,
		arrow
	)
}

const getPlainLink = (text, url, addArrow = false, openInNewTab = true) => {
	const target = openInNewTab ? 'target="_blank"' : '_self'

	const arrow = addArrow
		? sprintf(
			`<a href="%1$s" class="no-underline" target="${target}">&nbsp;&rarr;</a>`,
			url
		)
		: ''

	return sprintf(
		`<a href="%1$s" target="${target}">%2$s</a>%3$s`,
		url,
		text,
		arrow
	)
}

const getDocLink = (text, link, addArrow = false) => {
	const arrow = addArrow
		? sprintf(
			'<a href="%1$s" class="no-underline" target="_blank">&nbsp;&rarr;</a>',
			utmUrl('documentation', link, docLinks[link])
		)
		: ''
	return sprintf(
		'<a href="%1$s" target="_blank">%2$s</a>%3$s',
		utmUrl('documentation', link, docLinks[link]),
		text,
		arrow
	)
}

const getPricingUrl = (feature, medium, content, url = `${marketingSite}pricing/`) => {
	return utmUrl(medium, content, url) + '&features[]=' + feature
}

const utmUrl = (medium, content = null, url = `${marketingSite}pricing/`) => {
	let isUpgradeUrl = false
	if (
		(`${marketingSite}pricing/` === url || `${marketingSite}lite-upgrade/` === url) &&
		'pro' !== import.meta.env.VITE_VERSION.toLowerCase()
	) {
		const rootStore  = useRootStore()
		const upgradeUrl = rootStore.aioseo.urls.upgradeUrl
		isUpgradeUrl = (upgradeUrl !== marketingSite)
		url = `${marketingSite}lite-upgrade/`
	}

	const urlParts = url.split('#')

	// Generate the new arguments.
	const args = [
		{ key: 'utm_source', value: 'WordPress' },
		{ key: 'utm_campaign', value: 'pro' === import.meta.env.VITE_VERSION.toLowerCase() ? 'proplugin' : 'liteplugin' },
		{ key: 'utm_medium', value: medium }
	]

	// Content is not used by default.
	if (content) {
		args.push({ key: 'utm_content', value: content })
	}

	// Append the marketing site domain if this is a relative url.
	const pattern = /^https?:\/\//i
	if (!pattern.test(urlParts[0])) {
		urlParts[0] = marketingSite + urlParts[0]
	}

	// Build the new URL.
	const newUrlParts = urlParts[0].split('?')
	urlParts[0]       = newUrlParts[0] + (newUrlParts[1] ? '?' + newUrlParts[1] + '&' : '?')
	urlParts[0]      += args
		.map(arg => `${arg.key}=${arg.value}`)
		.join('&')

	url = urlParts[0]
	if (urlParts[1]) {
		url = url + '#' + urlParts[1]
	}

	if (isUpgradeUrl) {
		const rootStore  = useRootStore()
		const upgradeUrl = rootStore.aioseo.urls.upgradeUrl

		url = upgradeUrl.replace('https%3A%2F%2Faioseo.com%2F', rawUrlEncode(url))
	}
	return url
}

const rawUrlEncode = (url) => {
	const histogram = {}

	histogram['\''] = '%27'
	histogram['(']  = '%28'
	histogram[')']  = '%29'
	histogram['*']  = '%2A'
	histogram['~']  = '%7E'
	histogram['!']  = '%21'

	url = encodeURIComponent(url)
	url = url.replace('%20', ' ')

	for (const search in histogram) {
		url = url.replace(search, histogram[search])
	}

	return url.replace(/(%([a-z0-9]{2}))/g, function (full, m1, m2) {
		return '%' + m2.toUpperCase()
	})
}

const unForwardSlashIt = str => {
	return str ? str.replace(/^\//, '') : str
}

const unTrailingSlashIt = str => {
	return str ? str.replace(/\/$/, '') : str
}

const trailingSlashIt = str => {
	return unTrailingSlashIt(str) + '/'
}

const restUrl = (path, namespace = 'aioseo/v1') => {
	const rootStore = useRootStore()
	path = rootStore.aioseo.data.hasUrlTrailingSlash ? trailingSlashIt(path) : unTrailingSlashIt(path)
	return trailingSlashIt(rootStore.aioseo.urls.restUrl) + trailingSlashIt(namespace) + unForwardSlashIt(path)
}

export default {
	docLinks,
	getDocLink,
	getDocUrl,
	getPlainLink,
	getPricingUrl,
	getUpsellLink,
	getUpsellUrl,
	restUrl,
	trailingSlashIt,
	unForwardSlashIt,
	unTrailingSlashIt,
	utmUrl
}