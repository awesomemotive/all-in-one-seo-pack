<?php
/**
 * AIOSEOP Helper/Info Class
 *
 * Description. (use period)
 *
 * @link URL
 *
 * @package All-in-One-SEO-Pack
 * @since 2.4.2
 */

/**
 * All in One SEO Plugin Helper
 *
 * @since 2.4.2
 */
class AIOSEOP_Helper {
	/**
	 * Help Text for jQuery UI Tooltips.
	 *
	 * @since 2.4.2
	 * @var array $help_text {
	 *     @type string
	 * }
	 */
	private $help_text = array();

	/**
	 * Constructor
	 *
	 * @since 2.4.2
	 *
	 * @param string $module Module/Class name.
	 */
	public function __construct( $module = '' ) {
		if ( current_user_can( 'aiosp_manage_seo' ) ) {
			$this->_set_help_text( $module );
		}
	}

	/**
	 * Set this Help Text
	 *
	 * Sets the Help Text according to the module/class in use, but if there is
	 * no class name in $module, then this Help Text will add all module help texts.
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @param string $module All_in_One_SEO_Pack module.
	 */
	private function _set_help_text( $module ) {

		switch ( $module ) {
			case 'All_in_One_SEO_Pack':
				$this->help_text = $this->help_text_general();
				$this->help_text = array_merge( $this->help_text, $this->help_text_post_meta() );
				break;
			case 'All_in_One_SEO_Pack_Performance':
				$this->help_text = $this->help_text_performance();
				break;
			case 'All_in_One_SEO_Pack_Sitemap':
				$this->help_text = $this->help_text_sitemap();
				break;
			case 'All_in_One_SEO_Pack_Opengraph':
				$this->help_text = $this->help_text_opengraph();
				break;
			case 'All_in_One_SEO_Pack_Robots':
				$this->help_text = $this->help_text_robots_generator();
				break;
			case 'All_in_One_SEO_Pack_File_Editor':
				$this->help_text = $this->help_text_file_editor();
				break;
			case 'All_in_One_SEO_Pack_Importer_Exporter':
				$this->help_text = $this->help_text_importer_exporter();
				break;
			case 'All_in_One_SEO_Pack_Bad_Robots':
				$this->help_text = $this->help_text_bad_robots();
				break;
			default:
				$this->help_text = array_merge( $this->help_text, $this->help_text_general() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_performance() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_sitemap() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_opengraph() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_robots_generator() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_file_editor() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_importer_exporter() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_bad_robots() );
				$this->help_text = array_merge( $this->help_text, $this->help_text_post_meta() );
				break;
		}

		if ( AIOSEOPPRO ) {
			$help_text = aioseop_add_pro_help( $this->help_text );

			// TODO - Get Prefix from PRO Version.
			foreach ( $help_text as $key => $text ) {
				$this->help_text[ $key ] = $text;
			}
		}

	}

	/**
	 * Help Text General Settings
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_general() {
		$rtn_help_text = array(
			// General Settings.
			'aiosp_can'                         => __( 'This option will automatically generate Canonical URLs for your entire WordPress installation.  This will help to prevent duplicate content penalties by Google', 'all-in-one-seo-pack' ),
			'aiosp_no_paged_canonical_links'    => __( 'Checking this option will set the Canonical URL for all paginated content to the first page.', 'all-in-one-seo-pack' ),
			'aiosp_customize_canonical_links'   => __( 'Checking this option will allow you to customize Canonical URLs for specific posts.', 'all-in-one-seo-pack' ),
			'aiosp_use_original_title'          => __( 'Use wp_title to get the title used by the theme; this is disabled by default. If you use this option, set your title formats appropriately, as your theme might try to do its own title SEO as well.', 'all-in-one-seo-pack' ),
			'aiosp_schema_markup'               => __( 'Check this to support Schema.org markup, i.e., itemprop on supported metadata.', 'all-in-one-seo-pack' ),
			'aiosp_do_log'                      => __( 'Check this and All in One SEO Pack will create a log of important events (all-in-one-seo-pack.log) in its plugin directory which might help debugging. Make sure this directory is writable.', 'all-in-one-seo-pack' ),

			// Home Page Settings.
			'aiosp_home_title'                  => __( 'As the name implies, this will be the Meta Title of your homepage. This is independent of any other option. If not set, the default Site Title (found in WordPress under Settings, General, Site Title) will be used.', 'all-in-one-seo-pack' ),
			'aiosp_home_description'            => __( 'This will be the Meta Description for your homepage. This is independent of any other option. The default is no Meta Description at all if this is not set.', 'all-in-one-seo-pack' ),
			'aiosp_home_keywords'               => __( 'Enter a comma separated list of your most important keywords for your site that will be written as Meta Keywords on your homepage. Do not stuff everything in here.', 'all-in-one-seo-pack' ),
			'aiosp_use_static_home_info'        => __( 'Checking this option uses the title, description, and keywords set on your static Front Page.', 'all-in-one-seo-pack' ),

			// Title Settings.
			'aiosp_rewrite_titles'              => __( 'Note that this is all about the title tag. This is what you see in your browser\'s window title bar. This is NOT visible on a page, only in the title bar and in the source code. If enabled, all page, post, category, search and archive page titles get rewritten. You can specify the format for most of them. For example: Using the default post title format below, Rewrite Titles will write all post titles as \'Post Title | Blog Name\'. If you have manually defined a title using All in One SEO Pack, this will become the title of your post in the format string.', 'all-in-one-seo-pack' ),
			'aiosp_home_page_title_format'      => __( 'This controls the format of the title tag for your Home Page.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_login% - This page\'s author\' login', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_nicename% - This page\'s author\' nicename', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_firstname% - This page\'s author\' first name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_lastname% - This page\'s author\' last name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_page_title_format'           => __( 'This controls the format of the title tag for Pages.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_login% - This page\'s author\' login', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_nicename% - This page\'s author\' nicename', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_firstname% - This page\'s author\' first name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%page_author_lastname% - This page\'s author\' last name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_date% - The date the page was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_year% - The year the page was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_month% - The month the page was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_post_title_format'           => __( 'This controls the format of the title tag for Posts.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%category_title% - The (main) category of the post', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%category% - Alias for %category_title%', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_author_login% - This post\'s author\' login', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_author_nicename% - This post\'s author\' nicename', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_author_firstname% - This post\'s author\' first name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_author_lastname% - This post\'s author\' last name (capitalized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_date% - The date the post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_year% - The year the post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_month% - The month the post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>',
			'aiosp_category_title_format'       => __( 'This controls the format of the title tag for Category Archives.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%category_title% - The original title of the category', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%category_description% - The description of the category', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_archive_title_format'        => __( 'This controls the format of the title tag for Custom Post Archives.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%archive_title - The original archive title given by wordpress', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_date_title_format'           => __( 'This controls the format of the title tag for Date Archives.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%date% - The original archive title given by wordpress, e.g. &quot;2007&quot; or &quot;2007 August&quot;', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%day% - The original archive day given by wordpress, e.g. &quot;17&quot;', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%month% - The original archive month given by wordpress, e.g. &quot;August&quot;', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%year% - The original archive year given by wordpress, e.g. &quot;2007&quot;', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_author_title_format'         => __( 'This controls the format of the title tag for Author Archives.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%author% - The original archive title given by wordpress, e.g. &quot;Steve&quot; or &quot;John Smith&quot;', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_tag_title_format'            => __( 'This controls the format of the title tag for Tag Archives.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%tag% - The name of the tag', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_search_title_format'         => __( 'This controls the format of the title tag for the Search page.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%search% - What was searched for', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_description_format'          => __( 'This controls the format of Meta Descriptions. ', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%description% - This outputs the description you write for each page/post or the autogenerated description, if you have that option enabled. Auto-generated descriptions are generated from the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt.', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%wp_title% - The original WordPress title, e.g. post_title for posts', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_date% - The date the page/post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_year% - The year the page/post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%post_month% - The month the page/post was published (localized)', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_404_title_format'            => __( 'This controls the format of the title tag for the 404 page.', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%request_url% - The original URL path, like "/url-that-does-not-exist/"', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%request_words% - The URL path in human readable form, like "Url That Does Not Exist"', 'all-in-one-seo-pack' ) .
														'</li>' .
														'<li>' .
															__( '%404_title% - Additional 404 title input', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',
			'aiosp_paged_format'                => __( 'This string gets appended/prepended to titles of paged index pages (like home or archive pages).', 'all-in-one-seo-pack' ) . '<br />' .
													__( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
													'<ul>' .
														'<li>' .
															__( '%page% - The page number', 'all-in-one-seo-pack' ) .
														'</li>' .
													'</ul>',

			// Custom Post Type Settings.
			'aiosp_enablecpost'                 => __( 'Check this if you want to use All in One SEO Pack with any Custom Post Types on this site.', 'all-in-one-seo-pack' ),
			'aiosp_cpostactive'                 => __( 'Use these checkboxes to select which Post Types you want to use All in One SEO Pack with.', 'all-in-one-seo-pack' ),
			'aiosp_cpostadvanced'               => __( 'This will show or hide the advanced options for SEO for Custom Post Types.', 'all-in-one-seo-pack' ),
			'aiosp_cposttitles'                 => __( 'This allows you to set the title tags for each Custom Post Type.', 'all-in-one-seo-pack' ),

			// Display Settings.
			'aiosp_posttypecolumns'             => __( 'This lets you select which screens display the SEO Title, SEO Keywords and SEO Description columns.', 'all-in-one-seo-pack' ),

			// Webmaster Verification.
			'aiosp_google_verify'               => __( 'Enter your verification code here to verify your site with Google Webmaster Tools.', 'all-in-one-seo-pack' ),
			'aiosp_bing_verify'                 => __( 'Enter your verification code here to verify your site with Bing Webmaster Tools.', 'all-in-one-seo-pack' ),
			'aiosp_pinterest_verify'            => __( 'Enter your verification code here to verify your site with Pinterest.', 'all-in-one-seo-pack' ),

			// Google Settings.
			'aiosp_google_publisher'            => __( 'Enter your Google+ Profile URL here to add the rel=“author” tag to your site for Google authorship. It is recommended that the URL you enter here should be your personal Google+ profile.  Use the Advanced Authorship Options below if you want greater control over the use of authorship.', 'all-in-one-seo-pack' ),
			'aiosp_google_disable_profile'      => __( 'Check this to remove the Google Plus field from the user profile screen.', 'all-in-one-seo-pack' ),
			'aiosp_google_sitelinks_search'     => __( 'Add markup to display the Google Sitelinks Search Box next to your search results in Google.', 'all-in-one-seo-pack' ),
			'aiosp_google_set_site_name'        => __( 'Add markup to tell Google the preferred name for your website.', 'all-in-one-seo-pack' ),
			'aiosp_google_author_advanced'      => __( 'Enable this to display advanced options for controlling Google Plus authorship information on your website.', 'all-in-one-seo-pack' ),
			'aiosp_google_author_location'      => __( 'This option allows you to control which types of pages you want to display rel="author" on for Google authorship. The options include the Front Page (the homepage of your site), Posts, Pages, and any Custom Post Types. The Everywhere Else option includes 404, search, categories, tags, custom taxonomies, date archives, author archives and any other page template.', 'all-in-one-seo-pack' ),
			'aiosp_google_enable_publisher'     => __( 'This option allows you to control whether rel=&quot;publisher&quot; is displayed on the homepage of your site. Google recommends using this if the site is a business website.', 'all-in-one-seo-pack' ),
			'aiosp_google_specify_publisher'    => __( 'The Google+ profile you enter here will appear on your homepage only as the rel=&quot;publisher&quot; tag. It is recommended that the URL you enter here should be the Google+ profile for your business.', 'all-in-one-seo-pack' ),
			'aiosp_google_analytics_id'         => __( 'Enter your Google Analytics ID here to track visitor behavior on your site using Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_advanced_options'         => __( 'Check to use advanced Google Analytics options.', 'all-in-one-seo-pack' ),
			'aiosp_ga_domain'                   => __( 'Enter your domain name without the http:// to set your cookie domain.', 'all-in-one-seo-pack' ),
			'aiosp_ga_multi_domain'             => __( 'Use this option to enable tracking of multiple or additional domains.', 'all-in-one-seo-pack' ),
			'aiosp_ga_addl_domains'             => __( 'Add a list of additional domains to track here.  Enter one domain name per line without the http://.', 'all-in-one-seo-pack' ),
			'aiosp_ga_anonymize_ip'             => __( 'This enables support for IP Anonymization in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_display_advertising'      => __( 'This enables support for the Display Advertiser Features in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_exclude_users'            => __( 'Exclude logged-in users from Google Analytics tracking by role.', 'all-in-one-seo-pack' ),
			'aiosp_ga_track_outbound_links'     => __( 'Check this if you want to track outbound links with Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_link_attribution'         => __( 'This enables support for the Enhanced Link Attribution in Google Analytics.', 'all-in-one-seo-pack' ),
			'aiosp_ga_enhanced_ecommerce'       => __( 'This enables support for the Enhanced Ecommerce in Google Analytics.', 'all-in-one-seo-pack' ),

			// Noindex Settings.
			'aiosp_cpostnoindex'                => __( 'Set the default NOINDEX setting for each Post Type.', 'all-in-one-seo-pack' ),
			'aiosp_cpostnofollow'               => __( 'Set the default NOFOLLOW setting for each Post Type.', 'all-in-one-seo-pack' ),
			'aiosp_category_noindex'            => __( 'Check this to ask search engines not to index Category Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_archive_date_noindex'        => __( 'Check this to ask search engines not to index Date Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_archive_author_noindex'      => __( 'Check this to ask search engines not to index Author Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_tags_noindex'                => __( 'Check this to ask search engines not to index Tag Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_search_noindex'              => __( 'Check this to ask search engines not to index the Search page. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_404_noindex'                 => __( 'Check this to ask search engines not to index the 404 page.', 'all-in-one-seo-pack' ),
			'aiosp_paginated_noindex'           => __( 'Check this to ask search engines not to index paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_paginated_nofollow'          => __( 'Check this to ask search engines not to follow links from paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'aiosp_tax_noindex'                 => __( 'Check this to ask search engines not to index custom Taxonomy archive pages. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),

			// Advanced Settings.
			'aiosp_generate_descriptions'       => __( 'Check this and your Meta Descriptions for any Post Type will be auto-generated using the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt. You can overwrite any auto-generated Meta Description by editing the post or page.', 'all-in-one-seo-pack' ),
			'aiosp_skip_excerpt'                => __( 'This option will auto generate your meta descriptions from your post content instead of your post excerpt. This is useful if you want to use your content for your autogenerated meta descriptions instead of the excerpt. WooCommerce users should read the documentation regarding this setting.', 'all-in-one-seo-pack' ),
			'aiosp_run_shortcodes'              => __( 'Check this and shortcodes will get executed for descriptions auto-generated from content.', 'all-in-one-seo-pack' ),
			'aiosp_hide_paginated_descriptions' => __( 'Check this and your Meta Descriptions will be removed from page 2 or later of paginated content.', 'all-in-one-seo-pack' ),
			'aiosp_dont_truncate_descriptions'  => __( 'Check this to prevent your Description from being truncated regardless of its length.', 'all-in-one-seo-pack' ),
			'aiosp_unprotect_meta'              => __( 'Check this to unprotect internal postmeta fields for use with XMLRPC. If you don\'t know what that is, leave it unchecked.', 'all-in-one-seo-pack' ),
			'aiosp_redirect_attachement_parent' => __( 'Redirect attachment pages to post parent.', 'all-in-one-seo-pack' ),
			'aiosp_ex_pages'                    => __( 'Enter a comma separated list of pages here to be excluded by All in One SEO Pack. This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: <em>/forum/, /contact/</em>  For instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is add forum or /forum or /forum/ or and any URL with the word &quot;forum&quot; in it, such as http://mysite.com/forum or http://mysite.com/forum/someforumpage here and it will be excluded from All in One SEO Pack.', 'all-in-one-seo-pack' ),
			'aiosp_post_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Posts. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'aiosp_page_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Pages. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'aiosp_front_meta_tags'             => __( 'What you enter here will be copied verbatim to the header of the front page if you have set a static page in Settings, Reading, Front Page Displays. You can enter whatever additional headers you want here, even references to stylesheets. This will fall back to using Additional Page Headers if you have them set and nothing is entered here.', 'all-in-one-seo-pack' ),
			'aiosp_home_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of the home page if you have Front page displays your latest posts selected in Settings, Reading.  It will also be copied verbatim to the header on the Posts page if you have one set in Settings, Reading. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),

			// Keyword Settings.
			'aiosp_togglekeywords'              => __( 'This option allows you to toggle the use of Meta Keywords throughout the whole of the site.', 'all-in-one-seo-pack' ),
			'aiosp_use_categories'              => __( 'Check this if you want your categories for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'aiosp_use_tags_as_keywords'        => __( 'Check this if you want your tags for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'aiosp_dynamic_postspage_keywords'  => __( 'Check this if you want your keywords on your Posts page (set in WordPress under Settings, Reading, Front Page Displays) and your archive pages to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.', 'all-in-one-seo-pack' ),

			// Unknown/Pro?
			'aiosp_license_key'                 => __( 'This will be the license key received when the product was purchased. This is used for automatic upgrades.', 'all-in-one-seo-pack' ),
			'aiosp_taxactive'                   => __( 'Use these checkboxes to select which Taxonomies you want to use All in One SEO Pack with.', 'all-in-one-seo-pack' ),
			'aiosp_google_connect'              => __( 'Press the connect button to connect with Google Analytics; or if already connected, press the disconnect button to disable and remove any stored analytics credentials.', 'all-in-one-seo-pack' ),
		);

		$cpt_help_text = array(
			'_title_format'  => __( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
								'<ul>' .
									'<li>' .
										__( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%page_author_login% - This page\'s author\' login', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%page_author_nicename% - This page\'s author\' nicename', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%page_author_firstname% - This page\'s author\' first name (capitalized)', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%page_author_lastname% - This page\'s author\' last name (capitalized)', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%post_date% - The date the page was published (localized)', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%post_year% - The year the page was published (localized)', 'all-in-one-seo-pack' ) .
									'</li>' .
									'<li>' .
										__( '%post_month% - The month the page was published (localized)', 'all-in-one-seo-pack' ) .
									'</li>' .
								'</ul>' .
								'<br />' .
								'<a href="https://semperplugins.com/documentation/custom-post-type-settings/#custom-titles" target="_blank">' . __( 'Click here for documentation on this setting', 'all-in-one-seo-pack' ) . '</a>',
		);

		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'names' );
		foreach ( $post_types as $v1_pt ) {
			if ( ! isset( $rtn_help_text[ 'aiosp_' . $v1_pt . '_title_format' ] ) ) {
				$rtn_help_text[ 'aiosp_' . $v1_pt . '_title_format' ] = $cpt_help_text['_title_format'];
			}
		}

		$help_doc_link = array(
			// General Settings.
			'aiosp_can'                         => 'https://semperplugins.com/documentation/general-settings/#canonical-urls',
			'aiosp_no_paged_canonical_links'    => 'https://semperplugins.com/documentation/general-settings/#no-pagination-for-canonical-urls',
			'aiosp_customize_canonical_links'   => 'https://semperplugins.com/documentation/general-settings/#enable-custom-canonical-urls',
			'aiosp_use_original_title'          => 'https://semperplugins.com/documentation/general-settings/#use-original-title',
			'aiosp_schema_markup'               => 'https://semperplugins.com/documentation/general-settings/#use-schema-markup',
			'aiosp_do_log'                      => 'https://semperplugins.com/documentation/general-settings/#log-important-events',

			// Home Page Settings.
			'aiosp_home_title'                  => 'https://semperplugins.com/documentation/home-page-settings/#home-title',
			'aiosp_home_description'            => 'https://semperplugins.com/documentation/home-page-settings/#home-description',
			'aiosp_home_keywords'               => 'https://semperplugins.com/documentation/home-page-settings/#home-keywords',
			'aiosp_use_static_home_info'        => 'https://semperplugins.com/documentation/home-page-settings/#use-static-front-page-instead',

			// Title Settings.
			'aiosp_rewrite_titles'              => 'https://semperplugins.com/documentation/title-settings/#rewrite-titles',
			'aiosp_home_page_title_format'      => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_page_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_post_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_category_title_format'       => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_archive_title_format'        => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_date_title_format'           => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_author_title_format'         => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_tag_title_format'            => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_search_title_format'         => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_description_format'          => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_404_title_format'            => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',
			'aiosp_paged_format'                => 'https://semperplugins.com/documentation/title-settings/#title-format-fields',

			// Custom Post Type Settings.
			'aiosp_enablecpost'                 => 'https://semperplugins.com/documentation/custom-post-type-settings/#seo-for-custom-post-types',
			'aiosp_cpostactive'                 => 'https://semperplugins.com/documentation/custom-post-type-settings/#seo-on-only-these-post-types',
			'aiosp_cpostadvanced'               => 'https://semperplugins.com/documentation/custom-post-type-settings/#enable-advanced-options',
			'aiosp_cposttitles'                 => 'https://semperplugins.com/documentation/custom-post-type-settings/#custom-titles',

			// Display Settings.
			'aiosp_posttypecolumns'             => 'https://semperplugins.com/documentation/display-settings/#show-column-labels-for-custom-post-types',

			// Webmaster Verification.
			'aiosp_google_verify'               => 'https://semperplugins.com/documentation/google-webmaster-tools-verification/',
			'aiosp_bing_verify'                 => 'https://semperplugins.com/documentation/bing-webmaster-verification/',
			'aiosp_pinterest_verify'            => 'https://semperplugins.com/documentation/pinterest-site-verification/',

			// Google Settings.
			'aiosp_google_publisher'            => 'https://semperplugins.com/documentation/google-settings/#google-plus-default-profile',
			'aiosp_google_disable_profile'      => 'https://semperplugins.com/documentation/google-settings/#disable-google-plus-profile',
			'aiosp_google_sitelinks_search'     => 'https://semperplugins.com/documentation/google-settings/#display-sitelinks-search-box',
			'aiosp_google_set_site_name'        => 'https://semperplugins.com/documentation/google-settings/#set-preferred-site-name',
			'aiosp_google_author_advanced'      => 'https://semperplugins.com/documentation/google-settings/#advanced-authorship-options',
			'aiosp_google_author_location'      => 'https://semperplugins.com/documentation/google-settings/#display-google-authorship',
			'aiosp_google_enable_publisher'     => 'https://semperplugins.com/documentation/google-settings/#display-publisher-meta-on-front-page',
			'aiosp_google_specify_publisher'    => 'https://semperplugins.com/documentation/google-settings/#specify-publisher-url',
			'aiosp_google_analytics_id'         => 'https://semperplugins.com/documentation/setting-up-google-analytics/',
			'aiosp_ga_advanced_options'         => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/',
			'aiosp_ga_domain'                   => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#tracking-domain',
			'aiosp_ga_multi_domain'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-multiple-domains-additional-domains',
			'aiosp_ga_addl_domains'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-multiple-domains-additional-domains',
			'aiosp_ga_anonymize_ip'             => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#anonymize-ip-addresses',
			'aiosp_ga_display_advertising'      => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#display-advertiser-tracking',
			'aiosp_ga_exclude_users'            => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#exclude-users-from-tracking',
			'aiosp_ga_track_outbound_links'     => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#track-outbound-links',
			'aiosp_ga_link_attribution'         => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#enhanced-link-attribution',
			'aiosp_ga_enhanced_ecommerce'       => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/#enhanced-ecommerce',

			// Noindex Settings.
			'aiosp_cpostnoindex'                => 'https://semperplugins.com/documentation/noindex-settings/#noindex',
			'aiosp_cpostnofollow'               => 'https://semperplugins.com/documentation/noindex-settings/#nofollow',
			'aiosp_category_noindex'            => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_archive_date_noindex'        => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_archive_author_noindex'      => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_tags_noindex'                => 'https://semperplugins.com/documentation/noindex-settings/#noindex-settings',
			'aiosp_search_noindex'              => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-search-page',
			'aiosp_404_noindex'                 => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-404-page',
			'aiosp_paginated_noindex'           => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-paginated-pages-posts',
			'aiosp_paginated_nofollow'          => 'https://semperplugins.com/documentation/noindex-settings/#use-nofollow-for-paginated-pages-posts',
			'aiosp_tax_noindex'                 => 'https://semperplugins.com/documentation/noindex-settings/#use-noindex-for-the-taxonomy-archives',

			// Advanced Settings.
			'aiosp_generate_descriptions'       => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#autogenerate-descriptions',
			'aiosp_skip_excerpt'                => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#remove-descriptions-for-paginated-pages',
			'aiosp_run_shortcodes'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#never-shorten-long-descriptions',
			'aiosp_hide_paginated_descriptions' => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#unprotect-post-meta-fields',
			'aiosp_dont_truncate_descriptions'  => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#never-shorten-long-descriptions',
			'aiosp_unprotect_meta'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#unprotect-post-meta-fields',
			'aiosp_redirect_attachement_parent' => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#redirect-attachments-to-post-parent',
			'aiosp_ex_pages'                    => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#exclude-pages',
			'aiosp_post_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-post-headers',
			'aiosp_page_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-page-headers',
			'aiosp_front_meta_tags'             => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-front-page-headers',
			'aiosp_home_meta_tags'              => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/#additional-blog-page-headers',

			// Keyword Settings.
			'aiosp_togglekeywords'              => 'https://semperplugins.com/documentation/keyword-settings/#use-keywords',
			'aiosp_use_categories'              => 'https://semperplugins.com/documentation/keyword-settings/#use-categories-for-meta-keywords',
			'aiosp_use_tags_as_keywords'        => 'https://semperplugins.com/documentation/keyword-settings/#use-tags-for-meta-keywords',
			'aiosp_dynamic_postspage_keywords'  => 'https://semperplugins.com/documentation/keyword-settings/#dynamically-generate-keywords-for-posts-page',

			// Unknown/Pro?
//			'aiosp_license_key'                 => '',
//			'aiosp_taxactive'                   => '',
//			'aiosp_google_connect'              => '',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			// Any help text that ends with a ul or ol element will cause text to start at the next line.
			$tooltips_with_ul = array(
				'aiosp_home_page_title_format',
				'aiosp_page_title_format',
				'aiosp_post_title_format',
				'aiosp_category_title_format',
				'aiosp_archive_title_format',
				'aiosp_date_title_format',
				'aiosp_author_title_format',
				'aiosp_tag_title_format',
				'aiosp_search_title_format',
				'aiosp_description_format',
				'aiosp_404_title_format',
				'aiosp_paged_format',
			);

			$br = '<br /><br />';
			if ( in_array( $k1_slug, $tooltips_with_ul, true ) ) {
				$br = '<br />';
			}

			$rtn_help_text[ $k1_slug ] .= $br . '<a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Performance Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_performance() {
		$rtn_help_text = array(
			'aiosp_performance_memory_limit'   => __( 'This setting allows you to raise your PHP memory limit to a reasonable value. Note: WordPress core and other WordPress plugins may also change the value of the memory limit.', 'all-in-one-seo-pack' ),
			'aiosp_performance_execution_time' => __( 'This setting allows you to raise your PHP execution time to a reasonable value.', 'all-in-one-seo-pack' ),
			'aiosp_performance_force_rewrites' => __( 'Use output buffering to ensure that the title gets rewritten. Enable this option if you run into issues with the title tag being set by your theme or another plugin.', 'all-in-one-seo-pack' ),
		);

		$help_doc_link = array(
			'aiosp_performance_memory_limit'   => 'https://semperplugins.com/documentation/performance-settings/#raise-memory-limit',
			'aiosp_performance_execution_time' => 'https://semperplugins.com/documentation/performance-settings/#raise-execution-time',
			'aiosp_performance_force_rewrites' => 'https://semperplugins.com/documentation/performance-settings/#force-rewrites',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Sitemap Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_sitemap() {
		$rtn_help_text = array(
			'aiosp_sitemap_daily_cron'      => __( 'Notify search engines based on the selected schedule, and also update static sitemap daily if in use. (this uses WP-Cron, so make sure this is working properly on your server as well)', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_indexes'         => __( 'Organize sitemap entries into distinct files in your sitemap. Enable this only if your sitemap contains over 50,000 URLs or the file is over 5MB in size.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_max_posts'       => __( 'Allows you to specify the maximum number of posts in a sitemap (up to 50,000).', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_posttypes'       => __( 'Select which Post Types appear in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_taxonomies'      => __( 'Select which taxonomy archives appear in your sitemap', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_archive'         => __( 'Include Date Archives in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_author'          => __( 'Include Author Archives in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_images'          => __( 'Exclude Images in your sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_gzipped'         => __( 'Create a compressed sitemap file in .xml.gz format.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_robots'          => __( 'Places a link to your Sitemap.xml into your virtual Robots.txt file.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_rewrite'         => __( 'Dynamically creates the XML sitemap instead of using a static file.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_url'        => __( 'URL to the page. This field accepts relative URLs or absolute URLs with the protocol specified.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_prio'       => __( 'The priority of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_freq'       => __( 'The frequency of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_addl_mod'        => __( 'Last modified date of the page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_excl_categories' => __( 'Entries from these categories will be excluded from the sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_excl_pages'      => __( 'Use page slugs or page IDs, seperated by commas, to exclude pages from the sitemap.', 'all-in-one-seo-pack' ),
		);

		$parent_prio_help = __( 'Manually set the priority of your ', 'all-in-one-seo-pack' );
		$parent_freq_help = __( 'Manually set the frequency of your ', 'all-in-one-seo-pack' );
		$prio_help        = __( 'Manually set the priority for the ', 'all-in-one-seo-pack' );
		$freq_help        = __( 'Manually set the frequency for the ', 'all-in-one-seo-pack' );
		$post_name        = __( ' Post Type', 'all-in-one-seo-pack' );
		$tax_name         = __( ' Taxonomy', 'all-in-one-seo-pack' );

		$args = array(
			'public' => true,
		);

		$rtn_help_text['aiosp_sitemap_prio_homepage']   = $parent_prio_help . __( 'Homepage.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_prio_post']       = $parent_prio_help . __( 'posts.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_prio_taxonomies'] = $parent_prio_help . __( 'taxonomies.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_prio_archive']    = $parent_prio_help . __( 'archive pages.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_prio_author']     = $parent_prio_help . __( 'author pages.', 'all-in-one-seo-pack' );

		$post_types = get_post_types( $args, 'names' );
		foreach ( $post_types as $pt ) {
			$pt_obj = get_post_type_object( $pt );
			$rtn_help_text[ 'aiosp_sitemap_prio_post_' . $pt ] = $prio_help . $pt_obj->label . $post_name;
			$rtn_help_text[ 'aiosp_sitemap_freq_post_' . $pt ] = $freq_help . $pt_obj->label . $post_name;
		}

		$args = array(
			'public' => true,
		);

		$rtn_help_text['aiosp_sitemap_freq_homepage']   = $parent_freq_help . __( 'Homepage.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_freq_post']       = $parent_freq_help . __( 'posts.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_freq_taxonomies'] = $parent_freq_help . __( 'taxonomies.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_freq_archive']    = $parent_freq_help . __( 'archive pages.', 'all-in-one-seo-pack' );
		$rtn_help_text['aiosp_sitemap_freq_author']     = $parent_freq_help . __( 'author pages.', 'all-in-one-seo-pack' );

		$taxonomies = get_taxonomies( $args, 'object' );
		foreach ( $taxonomies as $tax ) {
			$rtn_help_text[ 'aiosp_sitemap_prio_taxonomies_' . $tax->name ] = $prio_help . $tax->label . $tax_name;
			$rtn_help_text[ 'aiosp_sitemap_freq_taxonomies_' . $tax->name ] = $freq_help . $tax->label . $tax_name;
		}

		$help_doc_link = array(
			// XML Sitemap.
			'aiosp_sitemap_daily_cron'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#schedule-updates',
			'aiosp_sitemap_indexes'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#enable-sitemap-indexes',
			'aiosp_sitemap_max_posts'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#enable-sitemap-indexes',
			'aiosp_sitemap_posttypes'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#post-types-and-taxonomies',
			'aiosp_sitemap_taxonomies'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#post-types-and-taxonomies',
			'aiosp_sitemap_archive'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#include-archive-pages',
			'aiosp_sitemap_author'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#include-archive-pages',
			'aiosp_sitemap_images'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#exclude-images',
			'aiosp_sitemap_gzipped'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#create-compressed-sitemap',
			'aiosp_sitemap_robots'          => 'https://semperplugins.com/documentation/xml-sitemaps-module/#link-from-virtual-robots',
			'aiosp_sitemap_rewrite'         => 'https://semperplugins.com/documentation/xml-sitemaps-module/#dynamically-generate-sitemap',

			// Additional Pages.
			'aiosp_sitemap_addl_url'        => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_prio'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_freq'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',
			'aiosp_sitemap_addl_mod'        => 'https://semperplugins.com/documentation/xml-sitemaps-module/#additional-pages',

			// Exclude Items.
			'aiosp_sitemap_excl_categories' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#excluded-items',
			'aiosp_sitemap_excl_pages'      => 'https://semperplugins.com/documentation/xml-sitemaps-module/#excluded-items',

			// Priorities.
			'aiosp_sitemap_prio_homepage'   => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_post'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_taxonomies' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_archive'    => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_prio_author'     => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',

			// Frequencies.
			'aiosp_sitemap_freq_homepage'   => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_post'       => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_taxonomies' => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_archive'    => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',
			'aiosp_sitemap_freq_author'     => 'https://semperplugins.com/documentation/xml-sitemaps-module/#priorities-and-frequencies',

		);

		/*
		 * Currently has no links, but may be added later.
		foreach ( $post_types as $pt ) {
			$help_doc_link[ 'aiosp_sitemap_prio_post_' . $pt ] = '';
			$help_doc_link[ 'aiosp_sitemap_freq_post_' . $pt ] = '';
		}
		*/

		/*
		 * Currently has no links, but may be added later.
		foreach ( $taxonomies as $tax ) {
			$help_doc_link[ 'aiosp_sitemap_prio_taxonomies_' . $tax->name ] = '';
			$help_doc_link[ 'aiosp_sitemap_freq_taxonomies_' . $tax->name ] = '';
		}
		*/

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Opengraph Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_opengraph() {
		$rtn_help_text = array(
			// Home Page Settings.
			'aiosp_opengraph_setmeta'                      => __( 'Checking this box will use the Home Title and Home Description set in All in One SEO Pack, General Settings as the Open Graph title and description for your home page.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_sitename'                     => __( 'The Site Name is the name that is used to identify your website.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_hometitle'                    => __( 'The Home Title is the Open Graph title for your home page.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_description'                  => __( 'The Home Description is the Open Graph description for your home page.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_homeimage'                    => __( 'The Home Image is the Open Graph image for your home page.', 'all-in-one-seo-pack' ),

			// Image Settings.
			'aiosp_opengraph_defimg'                       => __( 'This option lets you choose which image will be displayed by default for the Open Graph image. You may override this on individual posts.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_fallback'                     => __( 'This option lets you fall back to the default image if no image could be found above.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimg'                         => __( 'This option sets a default image that can be used for the Open Graph image. You can upload an image, select an image from your Media Library or paste the URL of an image here.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimgwidth'                    => __( 'This option lets you set a default width for your images, where unspecified.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_dimgheight'                   => __( 'This option lets you set a default height for your images, where unspecified.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_meta_key'                     => __( 'Enter the name of a custom field (or multiple field names separated by commas) to use that field to specify the Open Graph image on Pages or Posts.', 'all-in-one-seo-pack' ),

			// Social Profile Links.
			'aiosp_opengraph_profile_links'                => __( 'Add URLs for your website\'s social profiles here (Facebook, Twitter, Google+, Instagram, LinkedIn), one per line.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_person_or_org'                => __( 'Are the social profile links for your website for a person or an organization?', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_social_name'                  => __( 'Add the name of the person or organization who owns these profiles.', 'all-in-one-seo-pack' ),

			// Facebook Settings.
			'aiosp_opengraph_key'                          => __( 'Enter your Facebook Admin ID here. You can enter multiple IDs separated by a comma. You can look up your Facebook ID using this tool http://findmyfbid.com/', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_appid'                        => __( 'Enter your Facebook App ID here. Information about how to get your Facebook App ID can be found at https://developers.facebook.com/docs/apps/register', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_tags'                     => __( 'Automatically generate article tags for Facebook type article when not provided.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_keywords'                 => __( 'Use keywords in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_categories'               => __( 'Use categories in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_gen_post_tags'                => __( 'Use post tags in generated article tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_types'                        => __( 'Select which Post Types you want to use All in One SEO Pack to set Open Graph meta values for.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_facebook_publisher'           => __( 'Link articles to the Facebook page associated with your website.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_facebook_author'              => __( 'Allows your authors to be identified by their Facebook pages as content authors on the Opengraph meta for their articles.', 'all-in-one-seo-pack' ),

			// Twitter Settings.
			'aiosp_opengraph_defcard'                      => __( 'Select the default type of Twitter Card to display.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_site'                 => __( 'Enter the Twitter username associated with your website here.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_creator'              => __( 'Allows your authors to be identified by their Twitter usernames as content creators on the Twitter cards for their posts.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_twitter_domain'               => __( 'Enter the name of your website here.', 'all-in-one-seo-pack' ),

			// Advanced Settings.
			'aiosp_opengraph_title_shortcodes'             => __( 'Run shortcodes that appear in social title meta tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_description_shortcodes'       => __( 'Run shortcodes that appear in social description meta tags.', 'all-in-one-seo-pack' ),
			'aiosp_opengraph_generate_descriptions'        => __( 'This option will auto generate your Open Graph descriptions from your post content instead of your post excerpt. WooCommerce users should read the documentation regarding this setting.', 'all-in-one-seo-pack' ),

			// POST META.
			'aioseop_opengraph_settings_title'             => __( 'This is the Open Graph title of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_desc'              => __( 'This is the Open Graph description of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_image'             => __( 'This option lets you select the Open Graph image that will be used for this Page or Post, overriding the default settings.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_customimg'         => __( 'This option lets you upload an image to use as the Open Graph image for this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_imagewidth'        => __( 'Enter the width for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_imageheight'       => __( 'Enter the height for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_video'             => __( 'This option lets you specify a link to the Open Graph video used on this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_videowidth'        => __( 'Enter the width for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_videoheight'       => __( 'Enter the height for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_category'          => __( 'Select the Open Graph type that best describes the content of this Page or Post.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_facebook_debug'    => __( 'Press this button to have Facebook re-fetch and debug this page.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_section'           => __( 'This Open Graph meta allows you to add a general section name that best describes this content.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_tag'               => __( 'This Open Graph meta allows you to add a list of keywords that best describe this content.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_setcard'           => __( 'Select the Twitter Card type to use for this Page or Post, overriding the default setting.', 'all-in-one-seo-pack' ),
			'aioseop_opengraph_settings_customimg_twitter' => __( 'This option lets you upload an image to use as the Twitter image for this Page or Post.', 'all-in-one-seo-pack' ),
		);

		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'names' );
		foreach ( $post_types as $pt ) {
			$rtn_help_text[ 'aiosp_opengraph_' . $pt . '_fb_object_type' ]  = __( 'Choose a default value that best describes the content of your post type.', 'all-in-one-seo-pack' );
			$rtn_help_text[ 'aiosp_opengraph_' . $pt . '_fb_object_type' ] .= '<br /><br /><a href="https://semperplugins.com/documentation/social-meta-module/#content-object-types" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		$help_doc_link = array(
			// Home Page Settings.
			'aiosp_opengraph_setmeta'                      => 'https://semperplugins.com/documentation/social-meta-module/#use-aioseo-title-and-description',
			'aiosp_opengraph_sitename'                     => 'https://semperplugins.com/documentation/social-meta-module/#site-name',
			'aiosp_opengraph_hometitle'                    => 'https://semperplugins.com/documentation/social-meta-module/#home-title-and-description',
			'aiosp_opengraph_description'                  => 'https://semperplugins.com/documentation/social-meta-module/#home-title-and-description',
			'aiosp_opengraph_homeimage'                    => 'https://semperplugins.com/documentation/social-meta-module/#home-image',

			// Image Settings.
			'aiosp_opengraph_defimg'                       => 'https://semperplugins.com/documentation/social-meta-module/#select-og-image-source',
			'aiosp_opengraph_fallback'                     => 'https://semperplugins.com/documentation/social-meta-module/#use-default-if-no-image-found',
			'aiosp_opengraph_dimg'                         => 'https://semperplugins.com/documentation/social-meta-module/#default-og-image',
			'aiosp_opengraph_dimgwidth'                    => 'https://semperplugins.com/documentation/social-meta-module/#default-image-width',
			'aiosp_opengraph_dimgheight'                   => 'https://semperplugins.com/documentation/social-meta-module/#default-image-height',
			'aiosp_opengraph_meta_key'                     => 'https://semperplugins.com/documentation/social-meta-module/#use-custom-field-for-image',

			// Social Profile Links.
			'aiosp_opengraph_profile_links'                => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',
			'aiosp_opengraph_person_or_org'                => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',
			'aiosp_opengraph_social_name'                  => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',

			// Facebook Settings.
			'aiosp_opengraph_key'                          => 'https://semperplugins.com/documentation/social-meta-module/#facebook-admin-id',
			'aiosp_opengraph_appid'                        => 'https://semperplugins.com/documentation/social-meta-module/#facebook-app-id',
			'aiosp_opengraph_gen_tags'                     => 'https://semperplugins.com/documentation/social-meta-module/#automatically-generate-article-tags',
			'aiosp_opengraph_gen_keywords'                 => 'https://semperplugins.com/documentation/social-meta-module/#use-keywords-in-article-tags',
			'aiosp_opengraph_gen_categories'               => 'https://semperplugins.com/documentation/social-meta-module/#use-categories-in-article-tags',
			'aiosp_opengraph_gen_post_tags'                => 'https://semperplugins.com/documentation/social-meta-module/#use-post-tags-in-article-tags',
			'aiosp_opengraph_types'                        => 'https://semperplugins.com/documentation/social-meta-module/#enable-facebook-meta-for',
			'aiosp_opengraph_facebook_publisher'           => 'https://semperplugins.com/documentation/social-meta-module/#show-facebook-publisher-on-articles',
			'aiosp_opengraph_facebook_author'              => 'https://semperplugins.com/documentation/social-meta-module/#show-facebook-author-on-articles',

			// Twitter Settings.
			'aiosp_opengraph_defcard'                      => 'https://semperplugins.com/documentation/social-meta-module/#default-twitter-card',
			'aiosp_opengraph_twitter_site'                 => 'https://semperplugins.com/documentation/social-meta-module/#twitter-site',
			'aiosp_opengraph_twitter_creator'              => 'https://semperplugins.com/documentation/social-meta-module/#show-twitter-author',
			'aiosp_opengraph_twitter_domain'               => 'https://semperplugins.com/documentation/social-meta-module/#twitter-domain',

			// Advanced Settings.
			'aiosp_opengraph_title_shortcodes'             => 'https://semperplugins.com/documentation/social-meta-module/#run-shortcodes-in-title',
			'aiosp_opengraph_description_shortcodes'       => 'https://semperplugins.com/documentation/social-meta-module/#run-shortcodes-in-description',
			'aiosp_opengraph_generate_descriptions'        => 'https://semperplugins.com/documentation/social-meta-module/#auto-generate-og-descriptions',

			// POST META.
			'aioseop_opengraph_settings_title'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#title',
			'aioseop_opengraph_settings_desc'              => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#description',
			'aioseop_opengraph_settings_image'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#image',
			'aioseop_opengraph_settings_customimg'         => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-image',
			'aioseop_opengraph_settings_imagewidth'        => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
			'aioseop_opengraph_settings_imageheight'       => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
			'aioseop_opengraph_settings_video'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-video',
			'aioseop_opengraph_settings_videowidth'        => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
			'aioseop_opengraph_settings_videoheight'       => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
			'aioseop_opengraph_settings_category'          => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-object-type',
			'aioseop_opengraph_settings_facebook_debug'    => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-debug',
			'aioseop_opengraph_settings_section'           => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-section',
			'aioseop_opengraph_settings_tag'               => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-tags',
			'aioseop_opengraph_settings_setcard'           => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#twitter-card-type',
			'aioseop_opengraph_settings_customimg_twitter' => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-twitter-image',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Robots Generator Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_robots_generator() {
		$rtn_help_text = array(
			'aiosp_robots_type'  => __( 'Rule Type', 'all-in-one-seo-pack' ),
			'aiosp_robots_agent' => __( 'User Agent', 'all-in-one-seo-pack' ),
			'aiosp_robots_path'  => __( 'Directory Path', 'all-in-one-seo-pack' ),
		);

		return $rtn_help_text;
	}

	/**
	 * Help Text File Editor Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_file_editor() {
		return array(
			'aiosp_file_editor_robotfile' => __( 'Robots.txt editor', 'all-in-one-seo-pack' ),
			'aiosp_file_editor_htaccfile' => __( '.htaccess editor', 'all-in-one-seo-pack' ),
		);
	}

	/**
	 * Help Text Importer Exporter Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_importer_exporter() {
		$rtn_help_text = array(
			// Possible HTML link concept IF links become usable inside jQuery UI Tooltips.
			'aiosp_importer_exporter_import_submit'     => __( 'Choose a valid All in One SEO Pack ini file and click &quot;Import&quot; to import options from a previous state or install of All in One SEO Pack.', 'all-in-one-seo-pack' ),
			'aiosp_importer_exporter_export_choices'    => __( 'You may choose to export settings from active modules, and content from post data.', 'all-in-one-seo-pack' ),
			'aiosp_importer_exporter_export_post_types' => __( 'Select which Post Types you want to export your All in One SEO Pack meta data for.', 'all-in-one-seo-pack' ),
		);

		$help_doc_link = array(
			'aiosp_importer_exporter_import_submit'     => 'https://semperplugins.com/documentation/importer-exporter-module/',
			'aiosp_importer_exporter_export_choices'    => 'https://semperplugins.com/documentation/importer-exporter-module/',
			'aiosp_importer_exporter_export_post_types' => 'https://semperplugins.com/documentation/importer-exporter-module/',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Help Text Bad Robots Module
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @return array
	 */
	private function help_text_bad_robots() {
		return array(
			'aiosp_bad_robots_block_bots'   => __( 'Block requests from user agents that are known to misbehave with 503.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_block_refer'  => __( 'Block Referral Spam using HTTP.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_track_blocks' => __( 'Log and show recent requests from blocked bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_edit_blocks'  => __( 'Check this to edit the list of disallowed user agents for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_blocklist'    => __( 'This is the list of disallowed user agents used for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_referlist'    => __( 'This is the list of disallowed referers used for blocking bad bots.', 'all-in-one-seo-pack' ),
			'aiosp_bad_robots_blocked_log'  => __( 'Shows log of most recent requests from blocked bots. Note: this will not track any bots that were already blocked at the web server / .htaccess level.', 'all-in-one-seo-pack' ),
		);
	}

	/**
	 * Help Text Post Meta (Core Module)
	 *
	 * @ignore
	 * @since 2.4.2
	 * @access private
	 *
	 * @see self::_help_text_opengraph() Also adds Post Meta info.
	 *
	 * @return array
	 */
	private function help_text_post_meta() {
		$rtn_help_text = array(
			'aiosp_snippet'           => __( 'A preview of what this page might look like in search engine results.', 'all-in-one-seo-pack' ),
			'aiosp_title'             => __( 'A custom title that shows up in the title tag for this page.', 'all-in-one-seo-pack' ),
			'aiosp_description'       => __( 'The META description for this page. This will override any autogenerated descriptions.', 'all-in-one-seo-pack' ),
			'aiosp_keywords'          => __( 'A comma separated list of your most important keywords for this page that will be written as META keywords.', 'all-in-one-seo-pack' ),
			'aiosp_custom_link'       => __( 'Override the canonical URLs for this post.', 'all-in-one-seo-pack' ),
			'aiosp_noindex'           => __( 'Check this box to ask search engines not to index this page.', 'all-in-one-seo-pack' ),
			'aiosp_nofollow'          => __( 'Check this box to ask search engines not to follow links from this page.', 'all-in-one-seo-pack' ),
			'aiosp_sitemap_exclude'   => __( 'Don\'t display this page in the sitemap.', 'all-in-one-seo-pack' ),
			'aiosp_disable'           => __( 'Disable SEO on this page.', 'all-in-one-seo-pack' ),
			'aiosp_disable_analytics' => __( 'Disable Google Analytics on this page.', 'all-in-one-seo-pack' ),
		);

		$help_doc_link = array(
			'aiosp_snippet'           => 'https://semperplugins.com/documentation/post-settings/#preview-snippet',
			'aiosp_title'             => 'https://semperplugins.com/documentation/post-settings/#title',
			'aiosp_description'       => 'https://semperplugins.com/documentation/post-settings/#description',
			'aiosp_keywords'          => 'https://semperplugins.com/documentation/post-settings/#keywords',
			'aiosp_custom_link'       => 'https://semperplugins.com/documentation/post-settings/#custom-canonical-url',
			'aiosp_noindex'           => 'https://semperplugins.com/documentation/post-settings/#robots-meta-noindex',
			'aiosp_nofollow'          => 'https://semperplugins.com/documentation/post-settings/#robots-meta-nofollow',
			'aiosp_sitemap_exclude'   => 'https://semperplugins.com/documentation/post-settings/#exclude-from-sitemap',
			'aiosp_disable'           => 'https://semperplugins.com/documentation/post-settings/#disable-on-this-post',
			'aiosp_disable_analytics' => 'https://semperplugins.com/documentation/post-settings/#disable-google-analytics',
		);

		foreach ( $help_doc_link as $k1_slug => $v1_url ) {
			$rtn_help_text[ $k1_slug ] .= '<br /><br /><a href="' . $v1_url . '" target="_blank">' . __( 'Click here for documentation on this setting.', 'all-in-one-seo-pack' ) . '</a>';
		}

		return $rtn_help_text;
	}

	/**
	 * Get Help Text
	 *
	 * Gets an individual help text if it exists, otherwise an error is returned
	 * to notify the AIOSEOP Devs.
	 * NOTE: Returning an empty string causes issues with the UI.
	 *
	 * @since 2.4.2
	 *
	 * @param string $slug Module option slug.
	 * @return string
	 */
	public function get_help_text( $slug ) {
		if ( isset( $this->help_text[ $slug ] ) ) {
			return esc_html( $this->help_text[ $slug ] );
		}
		return 'DEV: Missing Help Text: ' . $slug;
	}
}
