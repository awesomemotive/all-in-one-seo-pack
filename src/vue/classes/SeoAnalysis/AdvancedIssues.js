/* eslint-disable no-unused-vars */
import { __ } from '@/vue/plugins/translations'
const td = import.meta.env.VITE_TEXTDOMAIN

class AdvancedIssues {
	get noindex () {
		const fixActionParams = '&aioseo-tab=advanced&aioseo-scroll=aioseo-post-robots-setting&aioseo-highlight=aioseo-post-robots-setting'

		return {
			noindex : {
				title       : __('The page cannot be indexed.', td),
				description : __('The noindex tag tells search engines not to index this page. As a result, this page will not appear in search results and will not generate any traffic.', td),
				fixActionParams
			},
			'noindex-ok' : {
				title       : __('The page can be indexed.', td),
				description : __('The noindex tag tells search engines not to index this page. As a result, this page will not appear in search results and will not generate any traffic.', td)
			}

		}
	}

	get openGraph () {
		const fixActionParams = '&aioseo-tab=social&aioseo-scroll=aioseo-post-settings-facebook&aioseo-highlight=aioseo-post-settings-facebook'

		return {
			'ogp-missing' : {
				title   : __('Open Graph meta tags missing.', td),
				text    : (result) => __('The following Open Graph meta tags are missing:', td),
				content : (result) => result?.items?.map(item => {
					return item
				}).join('<br/>'),
				description : __('Open Graph meta tags are used to control how your page appears when shared on social media and can help increase click-through rates.', td),
				fixActionParams
			},
			'ogp-duplicates' : {
				title   : __('Duplicate Open Graph meta tags found.', td),
				text    : (result) => __('The following Open Graph meta tags are duplicated:', td),
				content : (result) => result?.items?.map(item => {
					return item
				}).join('<br/>'),
				description : __('Open Graph meta tags are used to control how your page appears when shared on social media and can help increase click-through rates. Duplicate Open Graph meta tags can lead to conflicts and unexpected results. We recommend identifying which other plugin or theme is outputting them and disabling it.', td),
				fixActionParams
			},
			'ogp-ok' : {
				title       : __('All Open Graph meta tags found.', td),
				description : __('Open Graph meta tags are used to control how your page appears when shared on social media and can help increase click-through rates.', td)
			}
		}
	}

	get schema () {
		const fixActionParams = '&aioseo-tab=schema&aioseo-scroll=aioseo-post-schema&aioseo-highlight=aioseo-post-schema'

		return {
			'schema-missing' : {
				title       : __('No schema data found.', td),
				text        : (result) => __('No schema found.', td),
				description : __('Schema, also referred to as structured data, helps search engines understand the content on your page. Search engines also use schema to display rich snippets in search results, such as star ratings and prices. which improves user experience and click-through rates.', td),
				fixActionParams
			},
			'schema-ok' : {
				title       : __('The schema data was found.', td),
				description : __('Schema, also referred to as structured data, helps search engines understand the content on your page. Search engines also use schema to display rich snippets in search results, such as star ratings and prices. which improves user experience and click-through rates.', td)
			}
		}
	}

	get canonicalTag () {
		const fixActionParams = '&aioseo-tab=advanced&aioseo-scroll=aioseo-post-canonical-url&aioseo-highlight=aioseo-post-canonical-url'

		return {
			'canonical-missing' : {
				title       : __('No canonical link found.', td),
				description : __('The canonical link tag tells search engines which version of a page to index. This helps prevent duplicate content issues and improves search engine indexing.', td),
				fixActionParams
			},
			'canonical-ok' : {
				title       : __('The canonical link was found.', td),
				description : __('The canonical link tag tells search engines which version of a page to index. This helps prevent duplicate content issues and improves search engine indexing.', td)
			}
		}
	}

	get authorBio () {
		const fixActionParams = 'aioseo-tab=author-seo&aioseo-scroll=aioseo-author-bio-settings-row&aioseo-highlight=aioseo-author-bio-settings-row'

		return {
			'author-bio-missing' : {
				title       : __('No author bio found.', td),
				description : __('The author bio is the text that appears below the author\'s name on the page. It should be included to help search engines understand the author of the page and improve user experience. Experience, Expertise, Authoritativeness, and Trustworthiness (E-E-A-T) is a ranking factor.', td),
				fixActionParams
			},
			'author-bio-ok' : {
				title       : __('The author bio was found.', td),
				description : __('The author bio is the text that appears below the author\'s name on the page. It should be included to help search engines understand the author of the page and improve user experience. Experience, Expertise, Authoritativeness, and Trustworthiness (E-E-A-T) is a ranking factor.', td)
			}
		}
	}

	get mainKeyword () {
		const fixActionParams = '&aioseo-scroll=aioseo-post-settings-snippet-focus-keyphrase-row&aioseo-highlight=aioseo-post-settings-snippet-focus-keyphrase-row'

		return {
			'main-keyword-missing' : {
				title       : __('No focus keyword found.', td),
				description : __('The focus keyword is the main keyword that you want this page to rank for. It should be unique to the page and not used on other pages. It should be 2 to 5 words long.', td),
				fixActionParams
			},
			'main-keyword-ok' : {
				title       : __('The focus keyword was found.', td),
				description : __('The focus keyword is the main keyword that you want this page to rank for. It should be unique to the page and not used on other pages. It should be 2 to 5 words long.', td)
			}
		}
	}

	get staleContent () {
		return {
			'stale-content-too-old' : {
				title       : __('Stale content found.', td),
				description : __('The content is more than 2 years old and may be outdated. Stale content can hurt rankings if it is outdated or irrelevant. Search engines favor fresh, updated content—especially for topics that change over time. If your content stays accurate and useful, freshness matters less.', td)
			},
			'stale-content-ok' : {
				title       : __('The content is up to date.', td),
				description : __('The content is up to date and fresh. Search engines favor fresh, updated content—especially for topics that change over time. If your content stays accurate and useful, freshness matters less.', td)
			}
		}
	}

	get strings () {
		return {
			...this.noindex,
			...this.openGraph,
			...this.schema,
			...this.canonicalTag,
			...this.authorBio,
			...this.mainKeyword,
			...this.staleContent
		}
	}
}

export default new AdvancedIssues()