<?php
/**
 * Class AIOSP_Common_Strings
 *
 * This is just for Pro strings to be translated.
 *
 * @package All-in-One-SEO-Pack
 */

class AIOSP_Common_Strings {

	/**
	 * AIOSP_Common_Strings constructor.
	 *
	 * We'll just put all the strings in the contruct for lack of a better.
	 */

	private function __construct() {

		// Video sitemap strings.
		__( 'Video Sitemap', 'all-in-one-seo-pack' );
		__( 'Show Only Posts With Videos', 'all-in-one-seo-pack' );
		__( 'Scan Posts For Videos', 'all-in-one-seo-pack' );
		__( 'Restrict Access to Video Sitemap', 'all-in-one-seo-pack' );
		__( 'Press the Scan button to scan your posts for videos! Do this if video content from a post or posts is not showing up in your sitemap.', 'all-in-one-seo-pack' );
		__( 'If checked, only posts that have videos in them will be displayed on the sitemap.', 'all-in-one-seo-pack' );
		__( 'Enable this option to only allow access to your sitemap by site administrators and major search engines.', 'all-in-one-seo-pack' );
		__( 'You do not have access to this page; try logging in as an administrator.', 'all-in-one-seo-pack' );
		__( 'Scan', 'all-in-one-seo-pack' );

		// These are functions_class strings.
		__( 'Finished scanning posts', 'all-in-one-seo-pack' );

		// Update checker strings (incomplete... need to separate out html).
		__( 'Purchase one now', 'all-in-one-seo-pack' );
		__( 'License Key is not set yet or invalid. ', 'all-in-one-seo-pack' );
		/* translators: Please mind the space at the beginning of the string. */
		__( ' Need a license key?', 'all-in-one-seo-pack' );
		__( 'Notice: ', 'all-in-one-seo-pack' );
		__( 'Manage Licenses', 'all-in-one-seo-pack' );

		// These are Pro option strings.
		__( 'Show SEO News', 'all-in-one-seo-pack' );
		__( 'Display Menu In Admin Bar:', 'all-in-one-seo-pack' );
		__( 'Display Menu At The Top:', 'all-in-one-seo-pack' );
		__( 'This displays an SEO News widget on the dashboard.', 'all-in-one-seo-pack' );
		__( 'Check this to add All in One SEO Pack to the Admin Bar for easy access to your SEO settings.', 'all-in-one-seo-pack' );
		__( 'Check this to move the All in One SEO Pack menu item to the top of your WordPress Dashboard menu.', 'all-in-one-seo-pack' );
		__('%s is almost ready.', 'all-in-one-seo-pack' );
		__('You must <a href="%s">enter a valid License Key</a> for it to work.', 'all-in-one-seo-pack' );
		__( "There is a new version of %s available. Go to <a href='%s'>the plugins page</a> for details.", 'all-in-one-seo-pack' );
		sprintf( __( 'Your license has expired. Please %1$s click here %2$s to purchase a new one.', 'all-in-one-seo-pack' ), '<a href="https://semperplugins.com/all-in-one-seo-pack-pro-version/" target="_blank">', '</a>' );
		__( 'Track Outbound Forms:', 'all-in-one-seo-pack' );
		__( 'Track Events:', 'all-in-one-seo-pack' );
		__( 'Track URL Changes:', 'all-in-one-seo-pack' );
		__( 'Track Page Visibility:', 'all-in-one-seo-pack' );
		__( 'Track Media Query:', 'all-in-one-seo-pack' );
		__( 'Track Elements Visibility:', 'all-in-one-seo-pack' );
		__( 'Track Page Scrolling:', 'all-in-one-seo-pack' );
		__( 'Track Facebook and Twitter:', 'all-in-one-seo-pack' );
		__( 'Ensure URL Consistency:', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track outbound forms with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track events with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track URL changes for single pages with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track how long pages are in visible state with Google Analytics.', 'all-in-one-seo-pack' );
		/* translators: 'This option allows users to track media queries, allowing them to find out if users are viewing a responsive layout or not and which layout changes 
		have been applied if the browser window has been resized by the user, see https://github.com/googleanalytics/autotrack/blob/master/docs/plugins/media-query-tracker.md. */
		__( 'Check this if you want to track media query matching and queries with Google Analytics.', 'all-in-one-seo-pack' );
		/* translators: The term 'viewport' refers to the area of the page that is visible to the user, see https://www.w3schools.com/css/css_rwd_viewport.asp. */
		__( 'Check this if you want to track when elements are visible within the viewport with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to ensure consistency in URL paths reported to Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track how far down a user scrolls a page with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track interactions with the official Facebook and Twitter widgets with Google Analytics.', 'all-in-one-seo-pack' );
	}
}
