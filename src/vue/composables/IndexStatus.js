import { __, sprintf } from '@/vue/plugins/translations'

import SvgCalendar from '@/vue/components/common/svg/Calendar'
import SvgCircleCheck from '@/vue/components/common/svg/circle/Check'
import SvgCircleClose from '@/vue/components/common/svg/circle/Close'
import SvgCircleExclamation from '@/vue/components/common/svg/circle/Exclamation'
import SvgCourse from '@/vue/components/common/svg/schema/Course'
import SvgDesktop from '@/vue/components/common/svg/Desktop'
import SvgDoubleRightArrow from '@/vue/components/common/svg/DoubleRightArrow'
import SvgEvent from '@/vue/components/common/svg/schema/Event'
import SvgFactCheck from '@/vue/components/common/svg/schema/FactCheck'
import SvgFaqPage from '@/vue/components/common/svg/schema/FaqPage'
import SvgFileEdit from '@/vue/components/common/svg/schema/FileEdit'
import SvgJobPosting from '@/vue/components/common/svg/schema/JobPosting'
import SvgLink from '@/vue/components/common/svg/Link'
import SvgMobile from '@/vue/components/common/svg/Mobile'
import SvgProduct from '@/vue/components/common/svg/schema/Product'
import SvgProductReview from '@/vue/components/common/svg/schema/ProductReview'
import SvgRecipe from '@/vue/components/common/svg/schema/Recipe'
import SvgSoftwareApplication from '@/vue/components/common/svg/schema/SoftwareApplication'
import SvgStorefront from '@/vue/components/common/svg/Storefront'
import SvgVideo from '@/vue/components/common/svg/schema/Video'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useIndexStatus = () => {
	const strings = {
		noResultsYetLowercase : __('No results yet', td),
		inspectOnGsc          : __('Inspect in Google Search Console', td),
		testWithGoogle        : __('Test with Google', td)
	}

	const items = {
		verdict : {
			title       : __('Status', td),
			key         : 'verdict',
			description : __('Indicates the index status of the page in Search Statistics. This is the verdict result for the analysis.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'PASS':
						return SvgCircleCheck
					case 'PARTIAL':
					case 'FAIL':
					case 'NEUTRAL':
						return SvgCircleClose
					case 'VERDICT_UNSPECIFIED':
					default:
						return SvgCircleExclamation
				}
			},
			parseValue : (value) => {
				switch (value) {
					case 'PASS':
						return {
							value : __('URL is indexed', td)
						}
					case 'PARTIAL':
					case 'FAIL':
					case 'NEUTRAL':
						return {
							value : __('URL not indexed', td)
						}
					case 'VERDICT_UNSPECIFIED':
					default:
						return {
							value : __('Unknown indexing status', td)
						}
				}
			}
		},
		lastCrawlTime : {
			title       : __('Last Crawl', td),
			key         : 'lastCrawlTime',
			description : __('This shows the date and time when Google\'s crawler (Googlebot) last visited and crawled the page.', td),
			getIcon     : () => SvgCalendar,
			parseValue  : (value) => {
				return {
					value : value || __('Never', td)
				}
			}
		},
		crawledAs : {
			title       : __('Crawled As', td),
			key         : 'crawledAs',
			description : __('Indicates whether Google crawled the page as a mobile or desktop user agent. This is important because Google uses mobile-first indexing for most websites.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'DESKTOP':
						return SvgDesktop
					case 'MOBILE':
						return SvgMobile
					case 'CRAWLING_USER_AGENT_UNSPECIFIED':
					default:
						return SvgCircleExclamation
				}
			},
			parseValue : (value) => {
				switch (value) {
					case 'DESKTOP':
						return {
							value : __('Desktop user agent', td)
						}
					case 'MOBILE':
						return {
							value : __('Mobile user agent', td)
						}
					case 'CRAWLING_USER_AGENT_UNSPECIFIED':
					default:
						return {
							value : __('Unknown user agent', td)
						}
				}
			}
		},
		robotsTxtState : {
			title       : __('Robots.txt', td),
			key         : 'robotsTxtState',
			description : __('This specifies whether your website\'s robots.txt file allows Googlebot to crawl the page.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'ALLOWED':
						return SvgCircleCheck
					case 'DISALLOWED':
						return SvgCircleClose
					case 'ROBOTS_TXT_STATE_UNSPECIFIED':
					default:
						return SvgCircleExclamation
				}
			},
			parseValue : (value) => {
				switch (value) {
					case 'ALLOWED':
						return {
							value : __('Crawling allowed', td)
						}
					case 'DISALLOWED':
						return {
							value : __('Crawling blocked', td)
						}
					case 'ROBOTS_TXT_STATE_UNSPECIFIED':
					default:
						return {
							value : __('Unknown', td)
						}
				}
			}
		},
		indexingState : {
			title       : __('Indexing Allowed?', td),
			key         : 'indexingState',
			description : __('This specifies whether your website\'s robots meta tag allows Googlebot to index the page.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'INDEXING_ALLOWED':
						return SvgCircleCheck
					case 'BLOCKED_BY_META_TAG':
					case 'BLOCKED_BY_HTTP_HEADER':
					case 'BLOCKED_BY_ROBOTS_TXT':
						return SvgCircleClose
					case 'INDEXING_STATE_UNSPECIFIED':
					default:
						return SvgCircleExclamation
				}
			},
			parseValue : (value) => {
				switch (value) {
					case 'INDEXING_ALLOWED':
						return {
							value : __('Indexing allowed', td)
						}
					case 'BLOCKED_BY_META_TAG':
						return {
							value       : __('Indexing not allowed', td),
							description : __('\'noindex\' detected in \'robots\' meta tag', td)
						}
					case 'BLOCKED_BY_HTTP_HEADER':
						return {
							value       : __('Indexing not allowed', td),
							description : __('\'noindex\' detected in \'X-Robots-Tag\' http header', td)
						}
					case 'BLOCKED_BY_ROBOTS_TXT':
						return {
							value       : __('Indexing not allowed', td),
							description : __('Blocking robots.txt rule detected', td)
						}
					case 'INDEXING_STATE_UNSPECIFIED':
					default:
						return {
							value : __('Unknown', td)
						}
				}
			}
		},
		pageFetchState : {
			title       : __('Page Fetch', td),
			key         : 'pageFetchState',
			description : __('Indicates whether Google successfully fetched the page during its last visit.', td),
			getIcon     : (value) => {
				switch (value) {
					case 'SUCCESSFUL':
						return SvgCircleCheck
					case 'SOFT_404':
					case 'BLOCKED_ROBOTS_TXT':
					case 'NOT_FOUND':
					case 'ACCESS_DENIED':
					case 'SERVER_ERROR':
					case 'REDIRECT_ERROR':
					case 'ACCESS_FORBIDDEN':
					case 'BLOCKED_4XX':
					case 'INTERNAL_CRAWL_ERROR':
					case 'INVALID_URL':
						return SvgCircleClose
					case 'PAGE_FETCH_STATE_UNSPECIFIED':
					default:
						return SvgCircleExclamation
				}
			},
			parseValue : (value) => {
				switch (value) {
					case 'SUCCESSFUL':
						return {
							value : __('Successful', td)
						}
					case 'SOFT_404':
						return {
							value : __('Soft 404', td)
						}
					case 'BLOCKED_ROBOTS_TXT':
						return {
							value : __('Blocked by robots.txt', td)
						}
					case 'NOT_FOUND':
						return {
							value : __('Not found (404)', td)
						}
					case 'ACCESS_DENIED':
						return {
							value : __('Blocked due to unauthorized request (401)', td)
						}
					case 'SERVER_ERROR':
						return {
							value : __('Server error (5xx)', td)
						}
					case 'REDIRECT_ERROR':
						return {
							value : __('Redirection error', td)
						}
					case 'ACCESS_FORBIDDEN':
						return {
							value : __('Blocked due to access forbidden (403)', td)
						}
					case 'BLOCKED_4XX':
						return {
							value : __('Blocked due to other 4xx issue (not 403, 404)', td)
						}
					case 'INTERNAL_CRAWL_ERROR':
						return {
							value : __('Internal error', td)
						}
					case 'INVALID_URL':
						return {
							value : __('Invalid URL', td)
						}
					case 'PAGE_FETCH_STATE_UNSPECIFIED':
					default:
						return {
							value : __('Unknown fetch state', td)
						}
				}
			}
		},
		userCanonical : {
			title       : __('User-Declared Canonical', td),
			key         : 'userCanonical',
			description : __('Shows the canonical URL specified by you (the website owner). Canonical URLs help indicate the preferred version of a page, especially for duplicate content.', td),
			getIcon     : () => SvgLink,
			parseValue  : (value) => !value
				? { value: __('None', td) }
				: { value: `<a href="${value}" target="_blank">${value}</a>` }
		},
		googleCanonical : {
			title       : __('Google-Selected Canonical', td),
			key         : 'googleCanonical',
			description	: __('Reveals the canonical URL chosen by Googlebot. Sometimes, Googlebot may select a different canonical URL than the user-declared one.', td),
			getIcon     : () => SvgLink,
			parseValue  : (value) => !value
				? { value: __('None', td) }
				: { value: `<a href="${value}" target="_blank" title=${value}>${value}</a>` }
		}
	}

	const getRichResultTypeIcon = (type) => {
		type = type.toLowerCase()

		if (-1 !== type.indexOf('breadcrumb')) {
			return SvgDoubleRightArrow
		}

		if (-1 !== type.indexOf('course')) {
			return SvgCourse
		}

		if (-1 !== type.indexOf('event')) {
			return SvgEvent
		}

		if (-1 !== type.indexOf('faq')) {
			return SvgFaqPage
		}

		if (-1 !== type.indexOf('fact')) {
			return SvgFactCheck
		}

		if (-1 !== type.indexOf('job')) {
			return SvgJobPosting
		}

		if (-1 !== type.indexOf('review')) {
			return SvgProductReview
		}

		if (-1 !== type.indexOf('recipe')) {
			return SvgRecipe
		}

		if (-1 !== type.indexOf('software')) {
			return SvgSoftwareApplication
		}

		if (-1 !== type.indexOf('video')) {
			return SvgVideo
		}

		if (-1 !== type.indexOf('product')) {
			return SvgProduct
		}

		if (-1 !== type.indexOf('merchant')) {
			return SvgStorefront
		}

		return SvgFileEdit
	}

	const parseRichResults = (value) => {
		if (!(value?.detectedItems || []).length) {
			return []
		}

		const types = []
		value.detectedItems.forEach(detectedItem => {
			types.push({
				label : detectedItem?.richResultType,
				items : detectedItem?.items?.map(item => {
					// Group issues by message to count occurrences.
					const messageGroups = {}

					// Create groups of identical messages.
					item?.issues?.forEach(issue => {
						const message = issue?.issueMessage
						if (!messageGroups[message]) {
							messageGroups[message] = {
								count    : 0,
								severity : issue?.issueSeverity
							}
						}
						messageGroups[message].count++
					})

					// Convert groups back to array with counts included.
					const groupedIssues = Object.entries(messageGroups).map(([ message, data ]) => ({
						message : 1 < data.count
							? sprintf(
								// Translators: 1 - The issue message, 2 - The number of occurrences of the issue message.
								__('%1$s (%2$d occurrences)', td),
								message,
								data.count
							)
							: message,
						icon : () => {
							switch (data.severity) {
								case 'ERROR':
									return SvgCircleClose
								case 'WARNING':
								case 'SEVERITY_UNSPECIFIED':
								default:
									return SvgCircleExclamation
							}
						}
					}))

					return {
						name   : item?.name,
						issues : groupedIssues || []
					}
				}) || [],
				icon : getRichResultTypeIcon(detectedItem?.richResultType)
			})
		})

		return types.filter(type => type.label)
	}

	return {
		items,
		parseRichResults,
		strings
	}
}