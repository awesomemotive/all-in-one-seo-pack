<?php
/**
 * Class AIOSP_Common_Strings
 *
 * This is just for Pro strings to be translated.
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

/**
 * Class AIOSP_Common_Strings
 *
 * @since ?
 */
class AIOSP_Common_Strings {

	/**
	 * AIOSP_Common_Strings constructor.
	 *
	 * We'll just put all the strings in the contruct for lack of a better place.
	 */
	private function __construct() {

		// From aioseop-helper-filters.php.
		__( 'This will be the license key received when the product was purchased. This is used for automatic upgrades.', 'all-in-one-seo-pack' );
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
		__( 'Use these checkboxes to select which Taxonomies you want to use %s with.', 'all-in-one-seo-pack' );
		__( 'This displays an SEO News widget on the dashboard.', 'all-in-one-seo-pack' );
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
		__( 'Check this to add %s to the Toolbar for easy access to your SEO settings.', 'all-in-one-seo-pack' );
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
		__( 'Check this to move the %s menu item to the top of your WordPress Dashboard menu.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track outbound forms with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track events with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track URL changes for single pages with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track how long pages are in visible state with Google Analytics.', 'all-in-one-seo-pack' );
		/* translators: 'This option allows users to track media queries, allowing them to find out if users are viewing a responsive layout or not and which layout changes have been applied if the browser window has been resized by the user, see https://github.com/googleanalytics/autotrack/blob/master/docs/plugins/media-query-tracker.md. */
		__( 'Check this if you want to track media query matching and queries with Google Analytics.', 'all-in-one-seo-pack' );
		/* translators: The term "viewport" refers to the area of the page that is visible to the user, see https://www.w3schools.com/css/css_rwd_viewport.asp. */
		__( 'Check this if you want to track when elements are visible within the viewport with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track how far down a user scrolls a page with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to track interactions with the official Facebook and Twitter widgets with Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Check this if you want to ensure consistency in URL paths reported to Google Analytics.', 'all-in-one-seo-pack' );
		__( 'Your site title', 'all-in-one-seo-pack' );
		__( 'Your site description', 'all-in-one-seo-pack' );

		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with a noun. */
		__( 'The original title of the %s', 'all-in-one-seo-pack' );
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with a noun. */
		__( 'The description of the %s', 'all-in-one-seo-pack' );
		__( 'Taxonomy', 'all-in-one-seo-pack' );
		__( 'The following macros are supported:', 'all-in-one-seo-pack' );
		__( 'Click here for documentation on this setting', 'all-in-one-seo-pack' );
		__( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' );
		__( 'Create RSS Sitemap as well.', 'all-in-one-seo-pack' );
		__( 'Notify search engines based on the selected schedule, and also update static sitemap daily if in use. (this uses WP-Cron, so make sure this is working properly on your server as well)', 'all-in-one-seo-pack' );
		__( 'Organize sitemap entries into distinct files in your sitemap. We recommend you enable this setting if your sitemap contains more than 1,000 URLs.', 'all-in-one-seo-pack' );
		__( 'Allows you to specify the maximum number of posts in a sitemap (up to 50,000).', 'all-in-one-seo-pack' );
		__( 'Select which Post Types appear in your sitemap.', 'all-in-one-seo-pack' );
		__( 'Select which taxonomy archives appear in your sitemap', 'all-in-one-seo-pack' );
		__( 'Include Date Archives in your sitemap.', 'all-in-one-seo-pack' );
		__( 'Include Author Archives in your sitemap.', 'all-in-one-seo-pack' );
		__( 'Exclude Images in your sitemap.', 'all-in-one-seo-pack' );
		__( 'Places a link to your Sitemap.xml into your virtual Robots.txt file.', 'all-in-one-seo-pack' );
		__( 'Dynamically creates the XML sitemap instead of using a static file.', 'all-in-one-seo-pack' );
		__( 'If checked, only posts that have videos in them will be displayed on the sitemap.', 'all-in-one-seo-pack' );
		__( 'Enable this option to look for videos in custom fields as well.', 'all-in-one-seo-pack' );
		__( 'URL to the page. This field accepts relative URLs or absolute URLs with the protocol specified.', 'all-in-one-seo-pack' );
		__( 'The priority of the page.', 'all-in-one-seo-pack' );
		__( 'The frequency of the page.', 'all-in-one-seo-pack' );
		__( 'Last modified date of the page.', 'all-in-one-seo-pack' );
		__( 'Entries from these taxonomy terms will be excluded from the sitemap.', 'all-in-one-seo-pack' );
		__( 'Use page slugs or page IDs, separated by commas, to exclude pages from the sitemap.', 'all-in-one-seo-pack' );
		/* translators: %1$s and %2$s are placeholders, which means that it should not be translated. They will be replaced with nouns in the application. */
		__( 'Manually set the %1$s of your %2$s.', 'all-in-one-seo-pack' );
		__( 'priority', 'all-in-one-seo-pack' );
		__( 'Homepage', 'all-in-one-seo-pack' );
		__( 'Post', 'all-in-one-seo-pack' );
		__( 'Taxonomies', 'all-in-one-seo-pack' );
		__( 'Archive Pages', 'all-in-one-seo-pack' );
		__( 'Author Pages', 'all-in-one-seo-pack' );

		// From aioseop_taxonomy_functions.php.
		__( 'SEO Title', 'all-in-one-seo-pack' );
		__( 'SEO Description', 'all-in-one-seo-pack' );
		__( 'SEO Keywords', 'all-in-one-seo-pack' );

		// From functions_general.php.
		__( 'Show SEO News', 'all-in-one-seo-pack' );
		__( 'Display Menu In Toolbar:', 'all-in-one-seo-pack' );
		__( 'Display Menu At The Top:', 'all-in-one-seo-pack' );
		__( 'Track Outbound Forms:', 'all-in-one-seo-pack' );
		__( 'Track Events:', 'all-in-one-seo-pack' );
		__( 'Track URL Changes:', 'all-in-one-seo-pack' );
		__( 'Track Page Visibility:', 'all-in-one-seo-pack' );
		__( 'Track Media Query:', 'all-in-one-seo-pack' );
		__( 'Track Elements Visibility:', 'all-in-one-seo-pack' );
		__( 'Track Page Scrolling:', 'all-in-one-seo-pack' );
		__( 'Track Facebook and Twitter:', 'all-in-one-seo-pack' );
		__( 'Ensure URL Consistency:', 'all-in-one-seo-pack' );

		// From sfwd_update_checker.php.
		__( '%s is almost ready.', 'all-in-one-seo-pack' );
		/* translators: leave all the code inside the brackets < and > unchanged.*/
		__( 'You must <a href="%s">enter a valid License Key</a> for it to work.', 'all-in-one-seo-pack' );
		__( 'Need a license key?', 'all-in-one-seo-pack' );
		__( 'Purchase one now', 'all-in-one-seo-pack' );
		/* translators: leave all the code inside the brackets < and > unchanged.*/
		__( "There is a new version of %1\$s available. Go to <a href='%2\$s'>the plugins page</a> for details.", 'all-in-one-seo-pack' );
		/* translators: %1$s and %2$s are placeholders, which means that it should not be translated. They will be replaced with nouns in the application. */
		__( 'Your license has expired. Please %1$s click here %2$s to purchase a new one.', 'all-in-one-seo-pack' );
		__( 'Manage Licenses', 'all-in-one-seo-pack' );
		__( 'License Key is not set yet or invalid. ', 'all-in-one-seo-pack' );
		/* transalators: Following this alert is the text of the notice. For example... "Notice: you have not entered a valid license key". */
		__( 'Notice: ', 'all-in-one-seo-pack' );

		// From video_sitemap.php.
		__( 'Video Sitemap', 'all-in-one-seo-pack' );
		__( 'Show Only Posts With Videos', 'all-in-one-seo-pack' );
		__( 'Include Custom Fields', 'all-in-one-seo-pack' );
		__( 'Video sitemap scan completed successfully!', 'all-in-one-seo-pack' );
		/* translators: This expression means "a small period/brief period of time". */
		__( 'a short while', 'all-in-one-seo-pack' );
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with a period of time, such as "5 minutes" or "a short while". */
		__( 'Video sitemap scan in progress. Please check again in %s.', 'all-in-one-seo-pack' );
	}
}
