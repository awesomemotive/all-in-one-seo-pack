import { __, sprintf } from '@wordpress/i18n'
import { computed } from 'vue'

import {
	useAnalyzerStore,
	useRootStore
} from '@/vue/stores'
import links from '@/vue/utils/links'

const td = import.meta.env.VITE_TEXTDOMAIN

export const useSeoSiteScore = () => {
	const analyzerStore = useAnalyzerStore()
	const rootStore     = useRootStore()

	const strings = {
		weveGotWorkToDo : sprintf(
			// Translators: 1 - HTML Line break tag.
			__('We\'ve got some%1$swork to do!', td),
			'<br>'
		),
		needsImprovement : sprintf(
			// Translators: 1 - HTML Line break tag.
			__('Needs%1$sImprovement!', td),
			'<br>'
		),
		veryGood                   : __('Very Good!', td),
		excellent                  : __('Excellent!', td),
		toSeeYourSiteScore         : __('to see your Site Score.', td),
		toAnalyzeCompetitorSite    : __('to analyze a competitor site.', td),
		enterLicenseKey            : __('A valid license key is required', td),
		anErrorOccurred            : __('An error occurred while analyzing your site.', td),
		criticalIssues             : __('Important Issues', td),
		warnings                   : __('Warnings', td),
		recommendedImprovements    : __('Recommended Improvements', td),
		goodResults                : __('Good Results', td),
		completeSiteAuditChecklist : __('Complete Site Audit Checklist', td)
	}

	const errorObject = computed(() => {
		if (rootStore.aioseo.data.isLocal) {
			return {
				description : __('It looks like you are accessing our analyzer from a local install. Our SEO analyzer does not work on local installs because we are unable to access it. Please try again once the site has been published.', td),
				buttons     : [
					{
						text : __('Learn More', td),
						type : 'blue',
						tag  : 'a',
						url  : links.getDocUrl('seoAnalyzer')
					}
				]
			}
		}

		switch (analyzerStore.analyzeError) {
			case 'invalid-token':
				return {
					description : sprintf(
						// Translators: 1 - The plugin short name ('AIOSEO').
						__('Your site is currently not connected to %1$s. In order to analyze your site, you must first connect to our server. Please connect to %1$s and try again.', td),
						import.meta.env.VITE_SHORT_NAME
					),
					buttons : [
						{
							text : __('Connect to AIOSEO', td),
							type : 'blue'
						}
					]
				}
			case 'invalid-html':
			case 'missing-content':
			case 'outbound-request-failed':
				return {
					description : sprintf(
						// Translators: 1 - The plugin short name ('AIOSEO').
						__('We are unable to retrieve the content for your site. This could be due to a number of reasons, but most likely the connection timed out while our analyzer was trying to access it. Please try again soon.', td),
						import.meta.env.VITE_SHORT_NAME
					),
					buttons : [
						{
							text     : __('Try Again', td),
							type     : 'blue',
							runAgain : true
						},
						{
							text : __('Learn More', td),
							type : 'gray',
							tag  : 'a',
							url  : links.getDocUrl('seoAnalyzer')
						}
					]
				}
			default:
				return {
					description : __('The SEO analysis failed due to an unknown error. Please wait a moment and try again. If the issue continues to occur, then please contact our support team.', td),
					buttons     : [
						{
							text : __('Learn More', td),
							type : 'blue',
							tag  : 'a',
							url  : links.getDocUrl('seoAnalyzer')
						}
					]
				}
		}
	})

	return {
		errorObject,
		strings
	}
}