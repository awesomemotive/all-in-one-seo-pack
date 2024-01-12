import { __, sprintf } from '@wordpress/i18n'
import { useRootStore } from '@/vue/stores'
import dateFormat from '@/vue/utils/dateFormat'

import SvgCalendar from '@/vue/components/common/svg/Calendar'
import SvgDesktop from '@/vue/components/common/svg/Desktop'
import SvgMobile from '@/vue/components/common/svg/Mobile'
import SvgCheckmark from '@/vue/components/common/svg/Checkmark'
import SvgClose from '@/vue/components/common/svg/Close'
import SvgCircleExclamationSolid from '@/vue/components/common/svg/circle/ExclamationSolid'
import SvgLink from '@/vue/components/common/svg/Link'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useIndexStatus = () => {
	const rootStore = useRootStore()

	const items = [
		{
			title       : __('Index Status:', td),
			key         : 'verdict',
			description : __('Indicates the index status of the page in Search Statistics. This is the verdict result for the analysis.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'PASS':
						return SvgCheckmark
					case 'NEUTRAL':
					case 'PARTIAL':
						return SvgCircleExclamationSolid
					case 'VERDICT_UNSPECIFIED':
					case 'FAIL':
					default:
						return SvgClose
				}
			},
			getDescription : (value, { resultLink }) => {
				switch (value) {
					case 'PASS':
						return __('The page is indexed.', td)
					case 'NEUTRAL':
						return sprintf(
							// Translators: 1 - Opening link tag, 2 - Closing link tag.
							__('The page has not been indexed. Please check %1$sGoogle Search Console%2$s for more details.', td),
							'<a href="' + resultLink + '" target="_blank">',
							'</a>'
						)
					case 'FAIL':
					case 'VERDICT_UNSPECIFIED':
					case 'PARTIAL':
					default:
						return sprintf(
							// Translators: 1 - Opening link tag, 2 - Closing link tag.
							__('The page is invalid or has errors. Please check %1$sGoogle Search Console%2$s for more details.', td),
							'<a href="' + resultLink + '" target="_blank">',
							'</a>'
						)
				}
			}
		},
		{
			title          : __('Last Crawl:', td),
			key            : 'lastCrawlTime',
			description    : __('This shows the date and time when Google\'s crawler (Googlebot) last visited and crawled the page.', td),
			getIcon        : () => SvgCalendar,
			getDescription : (value) => {
				if (!value) {
					return __('Never', td)
				}

				const dateObject = new Date(value)
				const date = dateFormat(dateObject, rootStore.aioseo.data.dateFormat)
				const time = dateFormat(dateObject, rootStore.aioseo.data.timeFormat)

				return `${date}, ${time}`
			}
		},
		{
			title       : __('Crawled As:', td),
			key         : 'crawledAs',
			description : __('Indicates whether Google crawled the page as a mobile or desktop user agent. This is important because Google uses mobile-first indexing for most websites.', td),
			getIcon     : (value) => {
				if ('DESKTOP' === value) {
					return SvgDesktop
				}

				return SvgMobile
			},
			getDescription : (value) => {
				switch (value) {
					case 'DESKTOP':
						return __('Desktop user agent', td)
					case 'MOBILE':
						return __('Mobile user agent', td)
					default:
						return __('Unknown user agent', td)
				}
			}
		},
		{
			title       : __('Crawl Allowed?', td),
			key         : 'robotsTxtState',
			description : __('This specifies whether your website\'s robots.txt file allows Googlebot to crawl the page.', td),
			getIcon     : (value) => {
				if ('ALLOWED' === value) {
					return SvgCheckmark
				}

				return SvgClose
			},
			getDescription : (value) => {
				switch (value) {
					case 'ALLOWED':
						return __('Crawl allowed by robots.txt', td)
					case 'DISALLOWED':
						return __('Crawl blocked by robots.txt', td)
					default:
						return __('Unknown robots.txt state, typically because the page wasn\'t fetched or found, or because robots.txt itself couldn\'t be reached', td)
				}
			}
		},
		{
			title       : __('Indexing Allowed?', td),
			key         : 'indexingState',
			description : __('This specifies whether your website\'s robots meta tag allows Googlebot to index the page.', td),
			getIcon     : (value) => {
				if ('INDEXING_ALLOWED' === value) {
					return SvgCheckmark
				}

				return SvgClose
			},
			getDescription : (value) => {
				switch (value) {
					case 'INDEXING_ALLOWED':
						return __('Indexing allowed', td)
					case 'BLOCKED_BY_META_TAG':
						return __('Indexing not allowed, \'noindex\' detected in \'robots\' meta tag', td)
					case 'BLOCKED_BY_HTTP_HEADER':
						return __('Indexing not allowed, \'noindex\' detected in \'X-Robots-Tag\' http header', td)
					case 'BLOCKED_BY_ROBOTS_TXT':
						return __('Indexing not allowed, blocking robots.txt rule detected', td)
					default:
						return __('Unknown indexing status', td)
				}
			}
		},
		{
			title       : __('Page Fetch:', td),
			key         : 'pageFetchState',
			description : __('Indicates whether Google successfully fetched the page during its last visit.', td),
			getIcon     : (value) => {
				if ('SUCCESSFUL' === value) {
					return SvgCheckmark
				}

				return SvgClose
			},
			getDescription : (value) => {
				switch (value) {
					case 'SUCCESSFUL':
						return __('Successful fetch', td)
					case 'SOFT_404':
						return __('Soft 404', td)
					case 'BLOCKED_ROBOTS_TXT':
						return __('Blocked by robots.txt', td)
					case 'NOT_FOUND':
						return __('Not found (404)', td)
					case 'ACCESS_DENIED':
						return __('Blocked due to unauthorized request (401)', td)
					case 'SERVER_ERROR':
						return __('Server error (5xx)', td)
					case 'REDIRECT_ERROR':
						return __('Redirection error', td)
					case 'ACCESS_FORBIDDEN':
						return __('Blocked due to access forbidden (403)', td)
					case 'BLOCKED_4XX':
						return __('Blocked due to other 4xx issue (not 403, 404)', td)
					case 'INTERNAL_CRAWL_ERROR':
						return __('Internal error', td)
					case 'INVALID_URL':
						return __('Invalid URL', td)
					default:
						return __('Unknown fetch state', td)
				}
			}
		},
		{
			title          : __('User-Declared Canonical:', td),
			key            : 'userCanonical',
			description    : __('Shows the canonical URL specified by you (the website owner). Canonical URLs help indicate the preferred version of a page, especially for duplicate content.', td),
			getIcon        : () => SvgLink,
			getDescription : (value) => {
				if (!value) {
					return __('None', td)
				}

				return `<a href="${value}" target="_blank">${value}</a>`
			}
		},
		{
			title          : __('Google-Selected Canonical:', td),
			key            : 'googleCanonical',
			description   	: __('Reveals the canonical URL chosen by Googlebot. Sometimes, Googlebot may select a different canonical URL than the user-declared one.', td),
			getIcon        : () => SvgLink,
			getDescription : (value) => {
				if (!value) {
					return __('None', td)
				}

				return `<a href="${value}" target="_blank" title=${value}>${value}</a>`
			}
		}
	]

	return {
		items
	}
}