import {
	useRootStore
} from '@/vue/stores'

import { softSanitizeHtml } from '@/vue/utils/strings'
import { __, sprintf } from '@/vue/plugins/translations'

const td = import.meta.env.VITE_TEXTDOMAIN
class SiteAnalysis {
	personalize = false

	head = (test, result) => {
		if (undefined !== this[test + 'Head']) {
			return this[test + 'Head'](result)
		}

		// Return nothing so that we hide the check.
		return ''
	}

	body = (test, result) => {
		if (undefined !== this[test + 'Body']) {
			return this[test + 'Body'](result)
		}

		return ''
	}

	titleHead = result => {
		const error = result?.error || ''

		switch (error) {
			case 'title-missing':
				return __('We couldn\'t find an SEO Title.', td)
			case 'title-too-short':
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('Your SEO title is only %1$d characters long, which is too short.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('The SEO title is only %1$d characters long, which is too short.', td),
						result.value.length
					)
			case 'title-too-long':
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('Your SEO title is %1$d characters long, which is too long.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('The SEO title is %1$d characters long, which is too long.', td),
						result.value.length
					)
			default:
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('Your SEO title is set and is %1$d characters long.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the SEO title as a number.
						__('The SEO title is set and is %1$d characters long.', td),
						result.value.length
					)
		}
	}

	titleBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : result.value,
			message    : __('Ensure your page\'s title includes your target keywords, and design it to encourage users to click.', td) + '<br><br>' + __('Writing compelling titles is both a science and an art. There are automated tools that can analyze your title against known metrics for readability and click-worthiness. You also need to understand the psychology of your target audience.', td),
			buttonText : __('Edit Your Page Title', td),
			buttonLink : rootStore.aioseo.data.staticHomePage
				? `${rootStore.aioseo.urls.staticHomePage}&aioseo-scroll=aioseo-post-settings-post-title-row&aioseo-highlight=aioseo-post-settings-post-title-row`
				: `${rootStore.aioseo.urls.aio.searchAppearance}&aioseo-scroll=aioseo-home-page-site-title&aioseo-highlight=aioseo-home-page-site-title`
		}

		return body
	}

	descriptionHead = result => {
		const error = result?.error || ''

		switch (error) {
			case 'description-missing':
				return this.personalize
					? __('No meta description was found for your page.', td)
					: __('No meta description was found for the page.', td)
			case 'description-too-short':
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('Your meta description is only %1$d characters long, which is too short.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('The meta description is only %1$d characters long, which is too short.', td),
						result.value.length
					)
			case 'description-too-long':
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('Your meta description is %1$d characters long, which is too long.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('The meta description is %1$d characters long, which is too long.', td),
						result.value.length
					)
			default:
				return this.personalize
					? sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('Your meta description is set and is %1$d characters long.', td),
						result.value.length
					)
					: sprintf(
						// Translators: 1 - The length of the meta description as a number.
						__('The meta description is set and is %1$d characters long.', td),
						result.value.length
					)
		}
	}

	descriptionBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : 'description-missing' === result.error ? null : result.value,
			message    : __('Write a meta description for your page. Use your target keywords (in a natural way) and write with human readers in mind. Summarize the content - describe the topics your article discusses.', td) + '<br><br>' + __('The description should stimulate reader interest and get them to click on the article. Think of it as a mini-advertisement for your content.', td),
			buttonText : __('Edit Your Meta Description', td),
			buttonLink : rootStore.aioseo.data.staticHomePage
				? `${rootStore.aioseo.urls.staticHomePage}&aioseo-scroll=aioseo-post-settings-meta-description-row&aioseo-highlight=aioseo-post-settings-meta-description-row`
				: `${rootStore.aioseo.urls.aio.searchAppearance}&aioseo-scroll=aioseo-home-page-meta-description&aioseo-highlight=aioseo-home-page-meta-description`
		}

		return body
	}

	h1TagsHead = result => {
		const error = result?.error || ''

		switch (error) {
			case 'h1-missing':
				return __('No H1 tag was found.', td) + ' ' + __('For the best user experience there should be exactly one H1 tag on each page.', td)
			case 'h1-too-many':
				return sprintf(
					// Translators: 1 - The number of H1 tags found.
					__('%1$d H1 tags were found.', td),
					result.value.length
				) + ' ' + __('For the best user experience there should be exactly one H1 tag on each page.', td)
			default:
				return this.personalize
					? __('One H1 tag was found on your page.', td)
					: __('One H1 tag was found on the page.', td)
		}
	}

	h1TagsBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : 'h1-missing' === result.error ? null : result.value.join('<br>'),
			message    : __('WordPress sites usually insert the page or post title as an H1 tag (although custom themes can change this behavior).', td) + '<br><br>' + __('Ensure your most important keywords appear in the H1 tag - don\'t force it, use them in a natural way that makes sense to human readers.', td) + '<br><br>' + __('Because your headline plays a large role in reader engagement, it\'s worth spending extra time perfecting it. Many top copywriters spend hours getting their headlines just right - sometimes they spend longer on the headline than the rest of the article!', td) + '<br><br>' + __('A good headline stimulates reader interest and offers a compelling reason to read your content. It promises a believable benefit.', td) + '<br><br>' + __('You should write as if your readers are selfish people with short attention spans (because that describes a large percentage of the world\'s population). Readers visit websites for selfish reasons - they\'re not there to make you happy.', td),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage ? rootStore.aioseo.urls.staticHomePage : null
		}

		return body
	}

	h2TagsHead = result => {
		if ('h2-missing' === result.error) {
			return this.personalize
				? __('No H2 tags were found on your page.', td)
				: __('No H2 tags were found on the page.', td)
		}

		return this.personalize
			? __('H2 tags were found on your page.', td) + ' (' + result.value.length + ')'
			: __('H2 tags were found on the page.', td) + ' (' + result.value.length + ')'
	}

	h2TagsBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : 'h2-missing' === result.error ? null : result.value.join('<br>'),
			message    : __('Make sure you have a good balance of H2 tags to plain text in your content. Break the content down into logical sections, and use headings to introduce each new topic.', td) + '<br><br>' + __('Also, try to include synonyms and relevant terminology in H2 tag text. Search engines are pretty smart - they know which words usually occur together in each niche.', td) + '<br><br>' + __('It should be easy to include your main and supporting keywords in the H2 tags - after all, these keywords describe your content! If it\'s hard to work the keywords into your subheadings, it could be a sign that the keywords aren\'t closely related to your content.', td) + '<br><br>' + __('Don\'t try to force keywords into sub-headings if they feel unnatural. It will send the wrong message to your readers, possibly driving them away.', td),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage ? rootStore.aioseo.urls.staticHomePage : null
		}

		return body
	}

	noImgAltAttsHead = result => {
		if ('image-missing-alt' === result.error) {
			return this.personalize
				? __('Some images on your page have no alt attribute.', td) + ' (' + result.value.length + ')'
				: __('Some images on the page have no alt attribute.', td) + ' (' + result.value.length + ')'
		}

		return this.personalize
			? __('All images on your page have alt attributes.', td)
			: __('All images on the page have alt attributes.', td)
	}

	noImgAltAttsBody = result => {
		const rootStore = useRootStore()
		const body      = {
			codeAlt    : 'image-missing-alt' !== result.error ? null : result.value.map(tag => softSanitizeHtml(tag)).join('\n'),
			message    : __('Make sure every image has an alt tag, and add useful descriptions to each image. Add your keywords or synonyms - but do it in a natural way.', td),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage ? rootStore.aioseo.urls.staticHomePage : null
		}

		return body
	}

	linksRatioHead = result => {
		const error = result?.error || ''

		switch (error) {
			case 'internal-links-missing':
				return this.personalize
					? __('No internal links were found on your page.', td)
					: __('No internal links were found on the page.', td)
			case 'internal-links-too-few':
				return this.personalize
					? __('Too few internal links on your page.', td)
					: __('Too few internal links on the page.', td)
			case 'invalid-ratio':
				return __('The ratio of internal links to external links is uneven.', td)
			default:
				return this.personalize
					? __('Your page has a correct number of internal and external links.', td)
					: __('The page has a correct number of internal and external links.', td)
		}
	}

	linksRatioBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : __('Internal:', td) + ' ' + result.value.internal + '<br>' + __('External:', td) + ' ' + result.value.external,
			message    : __('Add links to internal and external resources that are useful for your readers. For Internal links, make sure the links are highly relevant to the subject you\'re writing about. For external links, make sure you link to high-quality sites - Google penalizes pages that link to "spammy" sites (ones that break the Google webmaster guidelines).', td) + '<br><br>' + __('It\'s impossible to cover every aspect of a subject on a single page, but your readers may be fascinated by some detail you barely touch on. If you link to a resource where they can learn more, they\'ll be grateful. What\'s more, you\'ll be rewarded with higher rankings!', td),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage ? rootStore.aioseo.urls.staticHomePage : null
		}

		return body
	}

	canonicalTagHead = result => {
		if ('canonical-missing' === result.error) {
			return this.personalize
				? __('No canonical link tag found on your page.', td)
				: __('No canonical link tag found on the page.', td)
		}

		return this.personalize
			? __('Your page is using the canonical link tag.', td)
			: __('The page is using the canonical link tag.', td)
	}

	canonicalTagBody = result => {
		const rootStore = useRootStore()
		const body = {
			code       : result.value,
			message    : __('Every page on your site should have a <link> tag with a \'rel="canonical"\' attribute. The link tag should go inside the page\'s head tag, and it should contain the page\'s "correct" URL.', td) + '<br><br>' + __('If you\'ve republished an article from another source (such as another site or a different section of your own site) then you need to pick which URL is the "correct" one and use that!', td),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage
				? `${rootStore.aioseo.urls.staticHomePage}&aioseo-tab=advanced&aioseo-scroll=aioseo-post-canonical-url&aioseo-highlight=aioseo-post-canonical-url`
				: null
		}

		return body
	}

	noindexHead = result => {
		if ('noindex' === result.error) {
			return this.personalize
				? __('Your page contains a noindex header or meta tag.', td)
				: __('The page contains a noindex header or meta tag.', td)
		}

		return this.personalize
			? __('Your page does not contain any noindex header or meta tag.', td)
			: __('The page does not contain any noindex header or meta tag.', td)
	}

	noindexBody = () => {
		const rootStore = useRootStore()
		const body = {
			message    : __('Only ever use noindex meta tag or header on pages you want to keep out of the reach of search engines!', td),
			buttonText : __('Edit Your Settings', td),
			buttonLink : rootStore.aioseo.urls.admin.optionsReading
		}

		return body
	}

	wwwCanonicalizationHead = result => {
		if ('www-canonicalization' === result.error) {
			return this.personalize
				? __('The www and non-www versions of your URL are not redirected to the same site.', td)
				: __('The www and non-www versions of the URL are not redirected to the same site.', td)
		}

		return this.personalize
			? __('Both the www and non-www versions of your URL are redirected to the same site.', td)
			: __('Both the www and non-www versions of the URL are redirected to the same site.', td)
	}

	wwwCanonicalizationBody = () => {
		const rootStore = useRootStore()
		const body = {
			message    : __('Decide whether you want your site\'s URLs to include a "www", or if you prefer a plain domain name. There are marketing pros and cons for each choice, but neither one is better or worse for SEO purposes - as long as you\'re consistent.', td) + '<br><br>' + __('You should use HTTP redirections (301 permanent redirects) to pass PageRank from the "wrong" URLs to the standard (canonical) ones. That way, your content will still benefit from backlinks if someone makes a mistake and uses the wrong URL.', td),
			buttonText : __('Edit Your Settings', td),
			buttonLink : rootStore.aioseo.urls.admin.generalSettings
		}

		return body
	}

	robotsRulesHead = result => {
		if ('no-robots' === result.error) {
			return this.personalize
				? __('Your robots.txt file is missing or unavailable.', td)
				: __('The robots.txt file is missing or unavailable.', td)
		}

		const matchDisallow = Boolean(result.value.match(/disallow:/i))

		if (matchDisallow) {
			return this.personalize
				? __('Your site has a robots.txt file which includes one or more "disallow" directives.', td)
				: __('The site has a robots.txt file which includes one or more "disallow" directives.', td)
		}

		return this.personalize
			? __('Your site has a robots.txt file.', td)
			: __('The site has a robots.txt file.', td)
	}

	robotsRulesBody = result => {
		const rootStore = useRootStore()
		const body = {
			code    : 'no-robots' === result.error ? null : result.value,
			message : __('Make sure that you only block parts you don\'t want to be indexed.', td) +
				'<br><br>' +
				__('You can manually create a robots.txt file and upload it to your site\'s web root. A simpler option is to use a plugin for your CMS platform.', td) +
				'<br><br>' +
				sprintf(
					// Translators: 1 - The Plugin short name ("AIOSEO").
					__('%1$s has a full suite of tools to manage the robots.txt file, along with other related technologies, like XML Sitemaps.', td),
					import.meta.env.VITE_SHORT_NAME
				),
			buttonText : __('Edit Your Robots.txt', td),
			buttonLink : rootStore.aioseo.urls.aio.tools
		}

		return body
	}

	openGraphHead = result => {
		if ('ogp-missing' === result.error) {
			return __('Some Open Graph meta tags are missing.', td)
		}

		if ('ogp-duplicates' === result.error) {
			return __('Duplicate Open Graph meta tags were found.', td)
		}

		return __('All the required Open Graph meta tags have been found.', td)
	}

	openGraphBody = result => {
		const message = 'ogp-missing' === result.error
			? __('Open Graph (OG) tags are used to enhance how your website content appears when shared on social media platforms, allowing you to control the title, description, and image displayed. This results in more engaging and informative previews, which can lead to increased click-through rates and better social media engagement.', td)
			: __('Duplicate Open Graph meta tags were found. This means that another plugin on your site or your current theme is generating social meta tags for Facebook (Open Graph) that are also generated by AIOSEO, and there are two sets of Open Graph tags present on your website. You\'ll want to deactivate this setting in your theme or the plugin that\'s causing this to avoid issues when sharing content on Facebook, LinkedIn, WhatsApp, or Instagram.', td)
		const rootStore = useRootStore()
		const staticLink = 'ogp-missing' === result.error
			? `${rootStore.aioseo.urls.staticHomePage}&aioseo-tab=social&aioseo-scroll=aioseo-post-facebook-image-source&aioseo-highlight=aioseo-post-facebook-image-source`
			: rootStore.aioseo.data.staticHomePage
		const link = 'ogp-missing' === result.error
			? `${rootStore.aioseo.urls.aio.socialNetworks}&aioseo-tab=facebook&aioseo-scroll=aioseo-facebook-default-image-source&aioseo-highlight=aioseo-facebook-default-image-source`
			: null
		const buttonText = rootStore.aioseo.data.staticHomePage
			? __('Edit Your Page', td)
			: __('Edit Your Settings', td)
		const body = {
			code       : result.value ? result.value.join('<br>') : null,
			message,
			buttonText,
			buttonLink : rootStore.aioseo.data.staticHomePage
				? staticLink
				: link
		}

		return body
	}

	schemaHead = result => {
		if ('schema-missing' === result.error) {
			return this.personalize
				? __('No Schema.org data was found on your page.', td)
				: __('No Schema.org data was found on the page.', td)
		}

		return this.personalize
			? __('We found Schema.org data on your page.', td)
			: __('We found Schema.org data on the page.', td)
	}

	schemaBody = () => {
		const rootStore = useRootStore()
		const body = {
			message : sprintf(
				// Translators: 1 - The Plugin short name ("AIOSEO").
				__('%1$s makes it extremely easy to add highly relevant Schema.org markup to your site. It has a simple graphical interface, so you don\'t have to get your hands dirty with complex HTML markup.', td),
				import.meta.env.VITE_SHORT_NAME
			),
			buttonText : __('Edit Your Page', td),
			buttonLink : rootStore.aioseo.data.staticHomePage
				? `${rootStore.aioseo.urls.staticHomePage}&aioseo-tab=schema&aioseo-scroll=aioseo-post-schema&aioseo-highlight=aioseo-post-schema`
				: null
		}

		return body
	}

	hasImgExpiresHead = result => {
		if ('image-expires-missing' === result.error) {
			return this.personalize
				? __('Your server is not using "expires" headers for your images.', td)
				: __('The server is not using "expires" headers for the images.', td)
		}

		return this.personalize
			? __('Your server is using "expires" headers for your images.', td)
			: __('The server is using "expires" headers for the images.', td)
	}

	hasImgExpiresBody = () => {
		const body = {
			message : __('If you use the Apache or NGINX web servers, you can edit the configuration files to set the "expires" header for all image files. For Apache, you can also use a ".htaccess" file to change the settings for each folder.', td) + '<br><br>' + __('Alternatively, you can use a CMS plugin to simplify the process - it\'s a more user-friendly option. WordPress has a host of caching plugins, and most of them give you options to control the caching headers.', td)
		}

		return body
	}

	unminifiedJsHead = result => {
		if ('js-unminified' === result.error) {
			return __('Some Javascript files don\'t seem to be minified.', td)
		}

		return __('All Javascript files appear to be minified.', td)
	}

	unminifiedJsBody = result => {
		const body = {
			codeAlt : 'js-unminified' !== result.error ? null : result.value.join('\n'),
			message : __('JavaScript files appear in many places, including frameworks (like Bootstrap), themes and templates, and third-party plugins.', td) + '<br><br>' + __('We recommend tracking down where the un-minified JavaScript files come from', td) + '<br><br>' + __('There are server-side tools (including WordPress plugins) to automatically minify JavaScript files.', td)
		}

		return body
	}

	unminifiedCssHead = result => {
		if ('css-unminified' === result.error) {
			return __('Some CSS files don\'t seem to be minified.', td)
		}

		return __('All CSS files appear to be minified.', td)
	}

	unminifiedCssBody = result => {
		const body = {
			codeAlt : 'css-unminified' !== result.error ? null : result.value.join('\n'),
			message : __('CSS files appear in many places, including frameworks (like Bootstrap), themes and templates, and third-party plugins.', td) + '<br><br>' + __('We recommend tracking down where the un-minified CSS files come from.', td) + '<br><br>' + __('There are server-side tools (including WordPress plugins) to automatically minify CSS files.', td)
		}

		return body
	}

	pageObjectsHead = result => {
		const pageRequests = this.personalize
			? sprintf(
				// Translators: 1 - The total number of page requests.
				__('Your page makes %1$d requests.', td),
				result.total
			)
			: sprintf(
				// Translators: 1 - The total number of page requests.
				__('The page makes %1$d requests.', td),
				result.total
			)
		if ('page-objects-too-many' === result.error) {
			return pageRequests + ' ' + __('More than 20 requests can result in slow page loading.', td)
		}

		return pageRequests
	}

	pageObjectsBody = result => {
		const body = {
			code    : 'page-objects-too-many' !== result.error ? null : __('Images:', td) + ' ' + result.value.images + '<br>' + __('JavaScript:', td) + ' ' + result.value.js + '<br>' + __('CSS:', td) + ' ' + result.value.css,
			message : __('Try to replace embedded objects with HTML5 alternatives.', td)
		}

		return body
	}

	pageSizeHead = result => {
		let pageSize = Math.round(result.value / 1000),
		 extraText   = ''

		if ('page-size-too-big' === result.error) {
			pageSize  = Math.ceil(result.value / 1000) // Round upwards to prevent issues where the page size is larger but is rounded down to 50 KB.
			extraText = __('This is over our recommendation of 50 KB.', td)
		}

		if (33 > Math.round(result.value / 1000)) {
			extraText = __('This is under the average of 33 KB.', td)
		}

		const text = sprintf(
			// Translators: 1 - The total number of page requests.
			__('The size of the HTML document is %1$d KB.', td),
			pageSize
		)

		return text + ' ' + extraText
	}

	pageSizeBody = () => {
		const body = {
			message : __('In order to reduce page size, remove any unnecessary tags from your markup. This includes developer comments, which are invisible to your users - search engines ignore the text in comments, too.', td) + '<br><br>' + __('Sometimes inline CSS is a culprit. A little inline CSS can help your page render faster. Too much will bloat the HTML file and increase the page loading time.', td) + '<br><br>' + __('You can reduce CSS repetition with HTML class and ID attributes. Often the same rules will be repeated across many page elements, embedded in each tag\'s "style" attribute. You can extract them into a single "style" tag and use classes and ID\'s to target each element.', td) + '<br><br>' + __('Removing white space can also have an impact on your HTML page\'s size. White space characters like carriage returns and tabs are ignored by the browser, but they make the markup easier for developers to read. So you should always strip them from your templates or themes before you use them in a production environment.', td)
		}

		return body
	}

	responseTimeHead = result => {
		if ('response-time-too-long' === result.error) {
			return this.personalize
				? sprintf(
					// Translators: 1 - The total number of page requests.
					__('The response time of your page is %1$f seconds. It is recommended to keep it equal to or below 0.2 seconds.', td),
					result.value
				)
				: sprintf(
					// Translators: 1 - The total number of page requests.
					__('The response time of the page is %1$f seconds. It is recommended to keep it equal to or below 0.2 seconds.', td),
					result.value
				)
		}

		return this.personalize
			? __('Your response time is under 0.2 seconds.', td)
			: __('The response time is under 0.2 seconds.', td)
	}

	responseTimeBody = () => {
		const body = {
			message : __('If you want to continue to improve your response time, the simplest and fastest fix is to use a caching plugin. Caching plugins keep a cached version of each page on your site. Instead of building the page from scratch, the server will send the cached copy.', td) + '<br><br>' + __('You can get an even greater boost in speed with a content delivery network service. These services host a copy of your content on multiple servers spread out across the globe. A user\'s request is handled by the edge server that\'s closest to their physical location, so the content arrives incredibly fast.', td)
		}

		return body
	}

	visiblePluginsHead = result => {
		if ('plugins-visible' === result.error) {
			return this.personalize
				? __('Plugins from your website are publicly visible.', td) + ' (' + result.value.length + ')'
				: __('Plugins from the website are publicly visible.', td) + ' (' + result.value.length + ')'
		}

		return this.personalize
			? __('You have no visible plugins!', td)
			: __('There are no visible plugins.', td)
	}

	visiblePluginsBody = result => {
		const body = {
			code    : 'plugins-visible' !== result.error ? null : result.value.join('<br>'),
			message : __('It\'s a great idea to try and hide the plugins you have visible. From time to time vulnerabilities are found in plugins and if your site is not updated in a timely fashion, outdated plugins and themes can be exploited.', td)
		}

		return body
	}

	visibleThemesHead = result => {
		if ('themes-visible' === result.error) {
			return this.personalize
				? sprintf(
					// Translators: 1 - The name of the theme.
					__('Anyone can see that you are using the %1$s theme.', td),
					result.value[0]
				)
				: sprintf(
					// Translators: 1 - The name of the theme.
					__('Anyone can see that they are using the %1$s theme.', td),
					result.value[0]
				)
		}

		return this.personalize
			? __('Your theme is not visible!', td)
			: __('The theme is not visible.', td)
	}

	visibleThemesBody = () => {
		const body = {
			message : __('It\'s a great idea to try and hide the theme you have visible. From time to time vulnerabilities are found in themes and if your site is not updated in a timely fashion, outdated plugins and themes can be exploited.', td)
		}

		return body
	}

	directoryListingHead = result => {
		if ('directory-listing-open' === result.error) {
			return this.personalize
				? __('Directory Listing seems to be enabled on your server.', td)
				: __('Directory Listing seems to be enabled on the server.', td)
		}

		return this.personalize
			? __('Directory Listing seems to be disabled on your server.', td)
			: __('Directory Listing seems to be disabled on the server.', td)
	}

	directoryListingBody = () => {
		const body = {
			message : __('Fortunately, every popular web server has options to prevent directory listings. They\'ll show a "403 forbidden" message instead.', td) + '<br><br>' + __('Alternatively, you can create an empty index.php file and save it in every directory on your site. That\'s an approach that WordPress uses and it works well.', td)
		}

		return body
	}

	googleSafeBrowsingHead = result => {
		if ('google-safe-browsing' === result.error) {
			return this.personalize
				? __('It looks like your site has been added to one of Google\'s malwares lists.', td)
				: __('It looks like this site has been added to one of Google\'s malwares lists.', td)
		}

		return this.personalize
			? __('Google has not flagged your site for malware!', td)
			: __('Google has not flagged this site for malware.', td)
	}

	googleSafeBrowsingBody = () => {
		const body = {
			message : __('Google Safe browsing shows warnings and alerts to users if they visit a suspicious website. If you are flagged by Google Safe Browsing, you should take immediate steps to fix that.', td)
		}

		return body
	}

	secureConnectionHead = result => {
		if ('insecure-connection' === result.error) {
			return this.personalize
				? __('Your site is not using a secure transfer protocol (https).', td)
				: __('The site is not using a secure transfer protocol (https).', td)
		}

		return this.personalize
			? __('Your site is using a secure transfer protocol (https).', td)
			: __('The site is using a secure transfer protocol (https).', td)
	}

	secureConnectionBody = () => {
		const body = {
			message : __('If you aren\'t using an SSL certificate for your site that means you are losing a lot of potential traffic. We recommend getting an SSL certificate installed immediately.', td)
		}

		return body
	}

	getFilteredResults = (resultSet, section) => {
		const results = JSON.parse(JSON.stringify(resultSet))

		// Filter out results that aren't rendered as rows.
		Object.keys(results).forEach(testName => {
			const testResult = results[testName]
			if (!this.head(testName, testResult)) {
				const exceptions = [
					'keywords',
					'keywordsInTitleDescription',
					'searchPreview',
					'mobileSearchPreview',
					'mobileSnapshot'
				]

				if (exceptions.includes(testName)) {
					delete results[testName]
				}
			}
		})

		Object.keys(results).forEach(r => {
			const result = results[r]
			if ('all' !== section && result.status !== section) {
				delete results[r]
			}
		})

		return results
	}

	getFilteredGroups = (results) => {
		// Drop all tests/results that do not have a matching title.
		// If a test has no title, it means it is deprecated/not supported by this version of the plugin.
		Object.keys(results).forEach(testName => {
			const testResult = results[testName]
			if (!this.head(testName, testResult)) {
				const exceptions = [
					'searchPreview',
					'mobileSearchPreview',
					'mobileSnapshot'
				]

				if (!exceptions.includes(testName)) {
					delete results[testName]
				}
			}
		})

		return results
	}

	getResultsCount = (results, section) => {
		let total = 0

		if (!results) {
			return total
		}

		Object.keys(results).forEach(group => {
			const groupResults = this.getFilteredGroups(results[group])

			Object.keys(groupResults).forEach(r => {
				const result = groupResults[r]
				if (section === result.status) {
					total++
				}
			})
		})

		return total
	}
}

export default new SiteAnalysis()