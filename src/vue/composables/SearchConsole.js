import { __, sprintf } from '@wordpress/i18n'
import { useRootStore } from '@/vue/stores'

const td = import.meta.env.VITE_TEXTDOMAIN

export function useSearchConsole () {
	const strings = {
		aioseoCanNowVerify : sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__('%1$s can now verify whether your site is correctly verified with Google Search Console and that your sitemaps have been submitted correctly. Connect with Google Search Console now to ensure your content is being added to Google as soon as possible for increased rankings.', td),
			import.meta.env.VITE_SHORT_NAME
		),
		connectToGoogleSearchConsole       : __('Connect to Google Search Console', td),
		connectToGoogleToAddSitemaps       : __('Connect to Google to automatically add sitemaps and keep them in sync.', td),
		syncYourSiteWithGsc                : __('Upgrade to Pro to unlock Search Statistics and sync your site with Google Search Console. Get valuable insights right inside your WordPress dashboard, track keyword rankings and search performance for individual posts with actionable insights to help you rank higher in search results!', td),
		fixSitemapErrors                   : __('Fix Errors', td),
		aioseoHasFoundSomeErrorsInSitemaps : sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__('%1$s has found some errors in sitemaps that were previously submitted to Google Search Console. Since %1$s manages your sitemaps, these additional sitemaps can be removed.', td),
			import.meta.env.VITE_SHORT_NAME
		),
		thereAreSitemapsWithErrors : __('There are sitemaps with errors', td),
		gscFeature1                : __('Google Search Console Metrics', td),
		gscFeature2                : __('SEO Changes Performance Tracking', td),
		gscFeature3                : __('Top Content Discovery', td),
		gscFeature4                : __('Content Decay Tracking', td)
	}

	const redirectToGscSettings = () => {
		const rootStore      = useRootStore()
		window.location.href = `${rootStore.aioseo.urls.aio.settings}&aioseo-scroll=google-search-console-settings&aioseo-highlight=google-search-console-settings#/webmaster-tools?activetool=googleSearchConsole`
	}

	return {
		strings,
		redirectToGscSettings
	}
}