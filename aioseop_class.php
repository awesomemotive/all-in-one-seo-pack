<?php
/**
 * All in One SEO Pack Main Class file.
 *
 * Main class file, to be broken up later.
 *
 * @package All-in-One-SEO-Pack
 */

require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' ); // Include the module base class.

/**
 * Class All_in_One_SEO_Pack
 *
 * The main class.
 */
class All_in_One_SEO_Pack extends All_in_One_SEO_Pack_Module {

	// Current version of the plugin.
	var $version = AIOSEOP_VERSION;

	// Max numbers of chars in auto-generated description.
	var $maximum_description_length = 160;

	// Minimum number of chars an excerpt should be so that it can be used as description.
	var $minimum_description_length = 1;

	// Whether output buffering is already being used during forced title rewrites.
	var $ob_start_detected = false;

	// The start of the title text in the head section for forced title rewrites.
	var $title_start = - 1;

	// The end of the title text in the head section for forced title rewrites.
	var $title_end = - 1;

	// The title before rewriting.
	var $orig_title = '';

	// Filename of log file.
	var $log_file;

	// Flag whether there should be logging.
	var $do_log;

	var $token;
	var $secret;
	var $access_token;
	var $ga_token;
	var $account_cache;
	var $profile_id;
	var $meta_opts = false;
	var $is_front_page = null;

	/**
	 * All_in_One_SEO_Pack constructor.
	 */
	function __construct() {
		global $aioseop_options;
		$this->log_file = dirname( __FILE__ ) . '/all-in-one-seo-pack.log'; // PHP <5.3 compatibility, once we drop support we can use __DIR___.

		if ( ! empty( $aioseop_options ) && isset( $aioseop_options['aiosp_do_log'] ) && $aioseop_options['aiosp_do_log'] ) {
			$this->do_log = true;
		} else {
			$this->do_log = false;
		}

		$this->name      = sprintf( __( '%s Plugin Options', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
		$this->menu_name = __( 'General Settings', 'all-in-one-seo-pack' );

		$this->prefix       = 'aiosp_';                        // Option prefix.
		$this->option_name  = 'aioseop_options';
		$this->store_option = true;
		$this->file         = __FILE__;                                // The current file.
		$blog_name          = esc_attr( get_bloginfo( 'name' ) );
		parent::__construct();

		$this->help_text = array(
			'license_key'                 => __( 'This will be the license key received when the product was purchased. This is used for automatic upgrades.', 'all-in-one-seo-pack' ),
			'can'                         => __( "This option will automatically generate Canonical URLs for your entire WordPress installation.  This will help to prevent duplicate content penalties by Google", 'all-in-one-seo-pack' ),
			'no_paged_canonical_links'    => __( 'Checking this option will set the Canonical URL for all paginated content to the first page.', 'all-in-one-seo-pack' ),
			'customize_canonical_links'   => __( 'Checking this option will allow you to customize Canonical URLs for specific posts.', 'all-in-one-seo-pack' ),
			'use_original_title'          => __( 'Use wp_title to get the title used by the theme; this is disabled by default. If you use this option, set your title formats appropriately, as your theme might try to do its own title SEO as well.', 'all-in-one-seo-pack' ),
			'do_log'                      => __( 'Check this and All in One SEO Pack will create a log of important events (all-in-one-seo-pack.log) in its plugin directory which might help debugging. Make sure this directory is writable.', 'all-in-one-seo-pack' ),
			'home_title'                  => __( 'As the name implies, this will be the Meta Title of your homepage. This is independent of any other option. If not set, the default Site Title (found in WordPress under Settings, General, Site Title) will be used.', 'all-in-one-seo-pack' ),
			'home_description'            => __( 'This will be the Meta Description for your homepage. This is independent of any other option. The default is no Meta Description at all if this is not set.', 'all-in-one-seo-pack' ),
			'home_keywords'               => __( 'Enter a comma separated list of your most important keywords for your site that will be written as Meta Keywords on your homepage. Do not stuff everything in here.', 'all-in-one-seo-pack' ),
			'use_static_home_info'        => __( 'Checking this option uses the title, description, and keywords set on your static Front Page.', 'all-in-one-seo-pack' ),
			'togglekeywords'              => __( 'This option allows you to toggle the use of Meta Keywords throughout the whole of the site.', 'all-in-one-seo-pack' ),
			'use_categories'              => __( 'Check this if you want your categories for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'use_tags_as_keywords'        => __( 'Check this if you want your tags for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'all-in-one-seo-pack' ),
			'dynamic_postspage_keywords'  => __( 'Check this if you want your keywords on your Posts page (set in WordPress under Settings, Reading, Front Page Displays) and your archive pages to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.', 'all-in-one-seo-pack' ),
			'rewrite_titles'              => __( "Note that this is all about the title tag. This is what you see in your browser's window title bar. This is NOT visible on a page, only in the title bar and in the source code. If enabled, all page, post, category, search and archive page titles get rewritten. You can specify the format for most of them. For example: Using the default post title format below, Rewrite Titles will write all post titles as 'Post Title | Blog Name'. If you have manually defined a title using All in One SEO Pack, this will become the title of your post in the format string.", 'all-in-one-seo-pack' ),
			'home_page_title_format'      =>
				__( 'This controls the format of the title tag for your Home Page.<br />The following macros are supported:', 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_login% - This page's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_nicename% - This page's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_firstname% - This page's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_lastname% - This page's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%current_date% - The current date (localized)", 'all-in-one-seo-pack' ) . '</li></ul>',
			'page_title_format'           =>
				__( 'This controls the format of the title tag for Pages.<br />The following macros are supported:', 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_login% - This page's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_nicename% - This page's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_firstname% - This page's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_lastname% - This page's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%current_date% - The current date (localized)", 'all-in-one-seo-pack' ) . '</li><li>'.
			    __( "%post_date% - The date the page was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
			    __( "%post_year% - The year the page was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
			    __( "%post_month% - The month the page was published (localized)", 'all-in-one-seo-pack' ) . '</li>' ,
			'post_title_format'           =>
				__( 'This controls the format of the title tag for Posts.<br />The following macros are supported:', 'all-in-one-seo-pack' )
				. '<li><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_title% - The (main) category of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category% - Alias for %category_title%', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_login% - This post's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_nicename% - This post's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_firstname% - This post's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_lastname% - This post's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%current_date% - The current date (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_date% - The date the post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_year% - The year the post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_month% - The month the post was published (localized)", 'all-in-one-seo-pack' ) . '</li>' ,
			'category_title_format'       =>
				__( 'This controls the format of the title tag for Category Archives.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_title% - The original title of the category', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_description% - The description of the category', 'all-in-one-seo-pack' ) . '</li></ul>',
			'archive_title_format'        =>
				__( 'This controls the format of the title tag for Custom Post Archives.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%archive_title - The original archive title given by wordpress', 'all-in-one-seo-pack' ) . '</li></ul>',
			'date_title_format'           =>
				__( 'This controls the format of the title tag for Date Archives.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%date% - The original archive title given by wordpress, e.g. "2007" or "2007 August"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%day% - The original archive day given by wordpress, e.g. "17"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%month% - The original archive month given by wordpress, e.g. "August"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%year% - The original archive year given by wordpress, e.g. "2007"', 'all-in-one-seo-pack' ) . '</li></ul>',
			'author_title_format'         =>
				__( 'This controls the format of the title tag for Author Archives.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%author% - The original archive title given by wordpress, e.g. "Steve" or "John Smith"', 'all-in-one-seo-pack' ) . '</li></ul>',
			'tag_title_format'            =>
				__( 'This controls the format of the title tag for Tag Archives.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%tag% - The name of the tag', 'all-in-one-seo-pack' ) . '</li></ul>',
			'search_title_format'         =>
				__( 'This controls the format of the title tag for the Search page.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%search% - What was searched for', 'all-in-one-seo-pack' ) . '</li></ul>',
			'description_format'          => __( 'This controls the format of Meta Descriptions.The following macros are supported:', 'all-in-one-seo-pack' ) .
			                                 '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%description% - This outputs the description you write for each page/post or the autogenerated description, if you have that option enabled. Auto-generated descriptions are generated from the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt.', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%wp_title% - The original WordPress title, e.g. post_title for posts', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%current_date% - The current date (localized)', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( "%post_date% - The date the page/post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( "%post_year% - The year the page/post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( "%post_month% - The month the page/post was published (localized)", 'all-in-one-seo-pack' ) . '</li>' ,
			'404_title_format'            => __( 'This controls the format of the title tag for the 404 page.<br />The following macros are supported:', 'all-in-one-seo-pack' ) .
			                                 '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%request_url% - The original URL path, like "/url-that-does-not-exist/"', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%request_words% - The URL path in human readable form, like "Url That Does Not Exist"', 'all-in-one-seo-pack' ) . '</li><li>' .
			                                 __( '%404_title% - Additional 404 title input"', 'all-in-one-seo-pack' ) . '</li></ul>',
			'paged_format'                => __( 'This string gets appended/prepended to titles of paged index pages (like home or archive pages).', 'all-in-one-seo-pack' )
			                                 . __( 'The following macros are supported:', 'all-in-one-seo-pack' )
			                                 . '<ul><li>' . __( '%page% - The page number', 'all-in-one-seo-pack' ) . '</li></ul>',
			'enablecpost'                 => __( 'Check this if you want to use All in One SEO Pack with any Custom Post Types on this site.', 'all-in-one-seo-pack' ),
			'cpostadvanced'               => __( 'This will show or hide the advanced options for SEO for Custom Post Types.', 'all-in-one-seo-pack' ),
			'cpostactive'                 => __( 'Use these checkboxes to select which Post Types you want to use All in One SEO Pack with.', 'all-in-one-seo-pack' ),
			'taxactive'                   => __( 'Use these checkboxes to select which Taxonomies you want to use All in One SEO Pack with.', 'all-in-one-seo-pack' ),
			'cposttitles'                 => __( 'This allows you to set the title tags for each Custom Post Type.', 'all-in-one-seo-pack' ),
			'posttypecolumns'             => __( 'This lets you select which screens display the SEO Title, SEO Keywords and SEO Description columns.', 'all-in-one-seo-pack' ),
			'google_verify'               => __( "Enter your verification code here to verify your site with Google Webmaster Tools.<br /><a href='https://semperplugins.com/documentation/google-webmaster-tools-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			'bing_verify'                 => __( "Enter your verification code here to verify your site with Bing Webmaster Tools.<br /><a href='https://semperplugins.com/documentation/bing-webmaster-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			'pinterest_verify'            => __( "Enter your verification code here to verify your site with Pinterest.<br /><a href='https://semperplugins.com/documentation/pinterest-site-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			'google_publisher'            => __( 'Enter your Google+ Profile URL here to add the rel=“author” tag to your site for Google authorship. It is recommended that the URL you enter here should be your personal Google+ profile.  Use the Advanced Authorship Options below if you want greater control over the use of authorship.', 'all-in-one-seo-pack' ),
			'google_disable_profile'      => __( 'Check this to remove the Google Plus field from the user profile screen.', 'all-in-one-seo-pack' ),
			'google_author_advanced'      => __( 'Enable this to display advanced options for controlling Google Plus authorship information on your website.', 'all-in-one-seo-pack' ),
			'google_author_location'      => __( 'This option allows you to control which types of pages you want to display rel=\"author\" on for Google authorship. The options include the Front Page (the homepage of your site), Posts, Pages, and any Custom Post Types. The Everywhere Else option includes 404, search, categories, tags, custom taxonomies, date archives, author archives and any other page template.', 'all-in-one-seo-pack' ),
			'google_enable_publisher'     => __( 'This option allows you to control whether rel=\"publisher\" is displayed on the homepage of your site. Google recommends using this if the site is a business website.', 'all-in-one-seo-pack' ),
			'google_specify_publisher'    => __( 'The Google+ profile you enter here will appear on your homepage only as the rel=\"publisher\" tag. It is recommended that the URL you enter here should be the Google+ profile for your business.', 'all-in-one-seo-pack' ),
			'google_sitelinks_search'     => __( 'Add markup to display the Google Sitelinks Search Box next to your search results in Google.', 'all-in-one-seo-pack' ),
			'google_set_site_name'        => __( 'Add markup to tell Google the preferred name for your website.', 'all-in-one-seo-pack' ),
			'google_connect'              => __( 'Press the connect button to connect with Google Analytics; or if already connected, press the disconnect button to disable and remove any stored analytics credentials.', 'all-in-one-seo-pack' ),
			'google_analytics_id'         => __( 'Enter your Google Analytics ID here to track visitor behavior on your site using Google Analytics.', 'all-in-one-seo-pack' ),
			'ga_advanced_options'         => __( 'Check to use advanced Google Analytics options.', 'all-in-one-seo-pack' ),
			'ga_domain'                   => __( 'Enter your domain name without the http:// to set your cookie domain.', 'all-in-one-seo-pack' ),
			'ga_multi_domain'             => __( 'Use this option to enable tracking of multiple or additional domains.', 'all-in-one-seo-pack' ),
			'ga_addl_domains'             => __( 'Add a list of additional domains to track here.  Enter one domain name per line without the http://.', 'all-in-one-seo-pack' ),
			'ga_anonymize_ip'             => __( 'This enables support for IP Anonymization in Google Analytics.', 'all-in-one-seo-pack' ),
			'ga_display_advertising'      => __( 'This enables support for the Display Advertiser Features in Google Analytics.', 'all-in-one-seo-pack' ),
			'ga_exclude_users'            => __( 'Exclude logged-in users from Google Analytics tracking by role.', 'all-in-one-seo-pack' ),
			'ga_track_outbound_links'     => __( 'Check this if you want to track outbound links with Google Analytics.', 'all-in-one-seo-pack' ),
			'ga_link_attribution'         => __( 'This enables support for the Enhanced Link Attribution in Google Analytics.', 'all-in-one-seo-pack' ),
			'ga_enhanced_ecommerce'       => __( 'This enables support for the Enhanced Ecommerce in Google Analytics.', 'all-in-one-seo-pack' ),
			'cpostnoindex'                => __( 'Set the default NOINDEX setting for each Post Type.', 'all-in-one-seo-pack' ),
			'cpostnofollow'               => __( 'Set the default NOFOLLOW setting for each Post Type.', 'all-in-one-seo-pack' ),

			'category_noindex'            => __( 'Check this to ask search engines not to index Category Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'archive_date_noindex'        => __( 'Check this to ask search engines not to index Date Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'archive_author_noindex'      => __( 'Check this to ask search engines not to index Author Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'tags_noindex'                => __( 'Check this to ask search engines not to index Tag Archives. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'search_noindex'              => __( 'Check this to ask search engines not to index the Search page. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'404_noindex'                 => __( 'Check this to ask search engines not to index the 404 page.', 'all-in-one-seo-pack' ),
			'tax_noindex'                 => __( 'Check this to ask search engines not to index custom Taxonomy archive pages. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'paginated_noindex'           => __( 'Check this to ask search engines not to index paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'paginated_nofollow'          => __( 'Check this to ask search engines not to follow links from paginated pages/posts. Useful for avoiding duplicate content.', 'all-in-one-seo-pack' ),
			'skip_excerpt'                => __( "This option will auto generate your meta descriptions from your post content instead of your post excerpt. This is useful if you want to use your content for your autogenerated meta descriptions instead of the excerpt. WooCommerce users should read the documentation regarding this setting.", 'all-in-one-seo-pack' ),
			'generate_descriptions'       => __( 'Check this and your Meta Descriptions for any Post Type will be auto-generated using the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt. You can overwrite any auto-generated Meta Description by editing the post or page.', 'all-in-one-seo-pack' ),
			'run_shortcodes'              => __( 'Check this and shortcodes will get executed for descriptions auto-generated from content.', 'all-in-one-seo-pack' ),
			'hide_paginated_descriptions' => __( 'Check this and your Meta Descriptions will be removed from page 2 or later of paginated content.', 'all-in-one-seo-pack' ),
			'dont_truncate_descriptions'  => __( 'Check this to prevent your Description from being truncated regardless of its length.', 'all-in-one-seo-pack' ),
			'schema_markup'               => __( 'Check this to support Schema.org markup, i.e., itemprop on supported metadata.', 'all-in-one-seo-pack' ),
			'unprotect_meta'              => __( "Check this to unprotect internal postmeta fields for use with XMLRPC. If you don't know what that is, leave it unchecked.", 'all-in-one-seo-pack' ),
			'redirect_attachement_parent' => __( 'Redirect attachment pages to post parent.', 'all-in-one-seo-pack' ),
			'ex_pages'                    => __( 'Enter a comma separated list of pages here to be excluded by All in One SEO Pack.  This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: <em>/forum/, /contact/</em>  For instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is add forum or /forum or /forum/ or and any URL with the word \"forum\" in it, such as http://mysite.com/forum or http://mysite.com/forum/someforumpage here and it will be excluded from All in One SEO Pack.', 'all-in-one-seo-pack' ),
			'post_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Posts. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'page_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of all Pages. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
			'front_meta_tags'             => __( 'What you enter here will be copied verbatim to the header of the front page if you have set a static page in Settings, Reading, Front Page Displays. You can enter whatever additional headers you want here, even references to stylesheets. This will fall back to using Additional Page Headers if you have them set and nothing is entered here.', 'all-in-one-seo-pack' ),
			'home_meta_tags'              => __( 'What you enter here will be copied verbatim to the header of the home page if you have Front page displays your latest posts selected in Settings, Reading.  It will also be copied verbatim to the header on the Posts page if you have one set in Settings, Reading. You can enter whatever additional headers you want here, even references to stylesheets.', 'all-in-one-seo-pack' ),
		);

		$this->help_anchors = array(
			'license_key'                 => '#license-key',
			'can'                         => '#canonical-urls',
			'no_paged_canonical_links'    => '#no-pagination-for-canonical-urls',
			'customize_canonical_links'   => '#enable-custom-canonical-urls',
			'use_original_title'          => '#use-original-title',
			'schema_markup'               => '#use-schema-markup',
			'do_log'                      => '#log-important-events',
			'home_title'                  => '#home-title',
			'home_description'            => '#home-description',
			'home_keywords'               => '#home-keywords',
			'use_static_home_info'        => '#use-static-front-page-instead',
			'togglekeywords'              => '#use-keywords',
			'use_categories'              => '#use-categories-for-meta-keywords',
			'use_tags_as_keywords'        => '#use-tags-for-meta-keywords',
			'dynamic_postspage_keywords'  => '#dynamically-generate-keywords-for-posts-page',
			'rewrite_titles'              => '#rewrite-titles',
			'home_page_title_format'      => '#title-format-fields',
			'page_title_format'           => '#title-format-fields',
			'post_title_format'           => '#title-format-fields',
			'category_title_format'       => '#title-format-fields',
			'archive_title_format'        => '#title-format-fields',
			'date_title_format'           => '#title-format-fields',
			'author_title_format'         => '#title-format-fields',
			'tag_title_format'            => '#title-format-fields',
			'search_title_format'         => '#title-format-fields',
			'description_format'          => '#title-format-fields',
			'404_title_format'            => '#title-format-fields',
			'paged_format'                => '#title-format-fields',
			'enablecpost'                 => '#seo-for-custom-post-types',
			'cpostadvanced'               => '#enable-advanced-options',
			'cpostactive'                 => '#seo-on-only-these-post-types',
			'taxactive'                   => '#seo-on-only-these-taxonomies',
			'cposttitles'                 => '#custom-titles',
			'posttypecolumns'             => '#show-column-labels-for-custom-post-types',
			'google_verify'               => '',
			'bing_verify'                 => '',
			'pinterest_verify'            => '',
			'google_publisher'            => '#google-plus-default-profile',
			'google_disable_profile'      => '#disable-google-plus-profile',
			'google_sitelinks_search'     => '#display-sitelinks-search-box',
			'google_set_site_name'        => '#set-preferred-site-name',
			'google_author_advanced'      => '#advanced-authorship-options',
			'google_author_location'      => '#display-google-authorship',
			'google_enable_publisher'     => '#display-publisher-meta-on-front-page',
			'google_specify_publisher'    => '#specify-publisher-url',
			'google_analytics_id'         => 'https://semperplugins.com/documentation/setting-up-google-analytics/',
			'ga_domain'                   => '#tracking-domain',
			'ga_multi_domain'             => '#track-multiple-domains-additional-domains',
			'ga_addl_domains'             => '#track-multiple-domains-additional-domains',
			'ga_anonymize_ip'             => '#anonymize-ip-addresses',
			'ga_display_advertising'      => '#display-advertiser-tracking',
			'ga_exclude_users'            => '#exclude-users-from-tracking',
			'ga_track_outbound_links'     => '#track-outbound-links',
			'ga_link_attribution'         => '#enhanced-link-attribution',
			'ga_enhanced_ecommerce'       => '#enhanced-ecommerce',
			'cpostnoindex'                => '#noindex',
			'cpostnofollow'               => '#nofollow',
			'category_noindex'			  => '#noindex-settings',
			'archive_date_noindex'        => '#noindex-settings',
			'archive_author_noindex'      => '#noindex-settings',
			'tags_noindex'                => '#noindex-settings',
			'search_noindex'              => '#use-noindex-for-the-search-page',
			'404_noindex'                 => '#use-noindex-for-the-404-page',
			'tax_noindex'                 => '#use-noindex-for-the-taxonomy-archives',
			'paginated_noindex'           => '#use-noindex-for-paginated-pages-posts',
			'paginated_nofollow'          => '#use-nofollow-for-paginated-pages-posts',
			'skip_excerpt'                => '#avoid-using-the-excerpt-in-descriptions',
			'generate_descriptions'       => '#autogenerate-descriptions',
			'run_shortcodes'              => '#run-shortcodes-in-autogenerated-descriptions',
			'hide_paginated_descriptions' => '#remove-descriptions-for-paginated-pages',
			'dont_truncate_descriptions'  => '#never-shorten-long-descriptions',
			'unprotect_meta'              => '#unprotect-post-meta-fields',
			'redirect_attachement_parent' => '#redirect-attachments-to-post-parent',
			'ex_pages'                    => '#exclude-pages',
			'post_meta_tags'              => '#additional-post-headers',
			'page_meta_tags'              => '#additional-page-headers',
			'front_meta_tags'             => '#additional-front-page-headers',
			'home_meta_tags'              => '#additional-blog-page-headers',
			'snippet'					  => '#preview-snippet',
			'title'						  => '#title',
			'description'				  => '#description',
			'keywords'					  => '#keywords',
			'custom_link'				  => '#custom-canonical-url',
			'noindex'					  => '#robots-meta-noindex',
			'nofollow'					  => '#robots-meta-nofollow',
			'sitemap_exclude'			  => '#exclude-from-sitemap',
			'disable'					  => '#disable-on-this-post',
			'disable_analytics'			  => '#disable-google-analytics',
		);

		$meta_help_text = array(
			'snippet'           => __( 'A preview of what this page might look like in search engine results.', 'all-in-one-seo-pack' ),
			'title'             => __( 'A custom title that shows up in the title tag for this page.', 'all-in-one-seo-pack' ),
			'description'       => __( 'The META description for this page. This will override any autogenerated descriptions.', 'all-in-one-seo-pack' ),
			'keywords'          => __( 'A comma separated list of your most important keywords for this page that will be written as META keywords.', 'all-in-one-seo-pack' ),
			'custom_link'       => __( 'Override the canonical URLs for this post.', 'all-in-one-seo-pack' ),
			'noindex'           => __( 'Check this box to ask search engines not to index this page.', 'all-in-one-seo-pack' ),
			'nofollow'          => __( 'Check this box to ask search engines not to follow links from this page.', 'all-in-one-seo-pack' ),
			'sitemap_exclude'   => __( "Don't display this page in the sitemap.", 'all-in-one-seo-pack' ),
			'disable'           => __( 'Disable SEO on this page.', 'all-in-one-seo-pack' ),
			'disable_analytics' => __( 'Disable Google Analytics on this page.', 'all-in-one-seo-pack' ),
		);

		$this->default_options = array(
			'license_key'                 => array(
				'name' => __( 'License Key:', 'all-in-one-seo-pack' ),
				'type' => 'text',
			),
			'home_title'                  => array(
				'name'     => __( 'Home Title:', 'all-in-one-seo-pack' ),
				'default'  => null,
				'type'     => 'text',
				'sanitize' => 'text',
				'count'    => true,
				'rows'     => 1,
				'cols'     => 60,
				'condshow' => array( 'aiosp_use_static_home_info' => 0 ),
			),
			'home_description'            => array(
				'name'     => __( 'Home Description:', 'all-in-one-seo-pack' ),
				'default'  => '',
				'type'     => 'textarea',
				'sanitize' => 'text',
				'count'    => true,
				'cols'     => 80,
				'rows'     => 2,
				'condshow' => array( 'aiosp_use_static_home_info' => 0 ),
			),
			'togglekeywords'              => array(
				'name'            => __( 'Use Keywords:', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'radio',
				'initial_options' => array(
					0 => __( 'Enabled', 'all-in-one-seo-pack' ),
					1 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'home_keywords'               => array(
				'name'     => __( 'Home Keywords (comma separated):', 'all-in-one-seo-pack' ),
				'default'  => null,
				'type'     => 'textarea',
				'sanitize' => 'text',
				'condshow' => array( 'aiosp_togglekeywords' => 0, 'aiosp_use_static_home_info' => 0 ),
			),
			'use_static_home_info'        => array(
				'name'            => __( 'Use Static Front Page Instead', 'all-in-one-seo-pack' ),
				'default'         => 0,
				'type'            => 'radio',
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'can'                         => array(
				'name'    => __( 'Canonical URLs:', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'no_paged_canonical_links'    => array(
				'name'     => __( 'No Pagination for Canonical URLs:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_can' => 'on' ),
			),
			'customize_canonical_links'   => array(
				'name'     => __( 'Enable Custom Canonical URLs:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_can' => 'on' ),
			),
			'rewrite_titles'              => array(
				'name'            => __( 'Rewrite Titles:', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'radio',
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'force_rewrites'              => array(
				'name'            => __( 'Force Rewrites:', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'hidden',
				'prefix'          => $this->prefix,
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'use_original_title'          => array(
				'name'            => __( 'Use Original Title:', 'all-in-one-seo-pack' ),
				'type'            => 'radio',
				'default'         => 0,
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'home_page_title_format'      => array(
				'name'     => __( 'Home Page Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%page_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'page_title_format'           => array(
				'name'     => __( 'Page Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%page_title% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'post_title_format'           => array(
				'name'     => __( 'Post Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%post_title% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'category_title_format'       => array(
				'name'     => __( 'Category Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%category_title% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'archive_title_format'        => array(
				'name'     => __( 'Archive Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%archive_title% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'date_title_format'           => array(
				'name'     => __( 'Date Archive Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%date% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'author_title_format'         => array(
				'name'     => __( 'Author Archive Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%author% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'tag_title_format'            => array(
				'name'     => __( 'Tag Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%tag% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'search_title_format'         => array(
				'name'     => __( 'Search Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%search% | %blog_title%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'description_format'          => array(
				'name'     => __( 'Description Format', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '%description%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'404_title_format'            => array(
				'name'     => __( '404 Title Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => __( 'Nothing found for %request_words%', 'all-in-one-seo-pack' ),
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'paged_format'                => array(
				'name'     => __( 'Paged Format:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => ' - Part %page%',
				'condshow' => array( 'aiosp_rewrite_titles' => 1 ),
			),
			'enablecpost'                 => array(
				'name'            => __( 'SEO for Custom Post Types:', 'all-in-one-seo-pack' ),
				'default'         => 'on',
				'type'            => 'radio',
				'initial_options' => array(
					'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
					0    => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'cpostactive'                 => array(
				'name'     => __( 'SEO on only these post types:', 'all-in-one-seo-pack' ),
				'type'     => 'multicheckbox',
				'default'  => array( 'post', 'page' ),
				'condshow' => array( 'aiosp_enablecpost' => 'on' ),
			),
			'taxactive'                   => array(
				'name'     => __( 'SEO on only these taxonomies:', 'all-in-one-seo-pack' ),
				'type'     => 'multicheckbox',
				'default'  => array( 'category', 'post_tag' ),
				'condshow' => array( 'aiosp_enablecpost' => 'on' ),
			),
			'cpostadvanced'               => array(
				'name'            => __( 'Enable Advanced Options:', 'all-in-one-seo-pack' ),
				'default'         => 0,
				'type'            => 'radio',
				'initial_options' => array(
					'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
					0    => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
				'label'           => null,
				'condshow'        => array( 'aiosp_enablecpost' => 'on' ),
			),
			'cpostnoindex'                => array(
				'name'    => __( 'Default to NOINDEX:', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array(),
			),
			'cpostnofollow'               => array(
				'name'    => __( 'Default to NOFOLLOW:', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array(),
			),
			'cposttitles'                 => array(
				'name'     => __( 'Custom titles:', 'all-in-one-seo-pack' ),
				'type'     => 'checkbox',
				'default'  => 0,
				'condshow' => array(
					'aiosp_rewrite_titles' => 1,
					'aiosp_enablecpost'    => 'on',
					'aiosp_cpostadvanced'  => 'on',
				),
			),
			'posttypecolumns' => array(
				'name'     => __( 'Show Column Labels for Custom Post Types:', 'all-in-one-seo-pack' ),
				'type'     => 'multicheckbox',
				'default'  => array( 'post', 'page' ),
				'condshow' => array( 'aiosp_enablecpost' => 'on' ),
			),
			'google_verify'               => array(
				'name'    => __( 'Google Webmaster Tools:', 'all-in-one-seo-pack' ),
				'default' => '',
				'type'    => 'text',
			),
			'bing_verify'                 => array(
				'name'    => __( 'Bing Webmaster Center:', 'all-in-one-seo-pack' ),
				'default' => '',
				'type'    => 'text',
			),
			'pinterest_verify'            => array(
				'name'    => __( 'Pinterest Site Verification:', 'all-in-one-seo-pack' ),
				'default' => '',
				'type'    => 'text',
			),
			'google_publisher'            => array(
				'name'    => __( 'Google Plus Default Profile:', 'all-in-one-seo-pack' ),
				'default' => '',
				'type'    => 'text',
			),
			'google_disable_profile'      => array(
				'name'    => __( 'Disable Google Plus Profile:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'type'    => 'checkbox',
			),
			'google_sitelinks_search'     => array(
				'name' => __( 'Display Sitelinks Search Box:', 'all-in-one-seo-pack' ),
			),
			'google_set_site_name'        => array(
				'name' => __( 'Set Preferred Site Name:', 'all-in-one-seo-pack' ),
			),
			'google_specify_site_name'    => array(
				'name'        => __( 'Specify A Preferred Name:', 'all-in-one-seo-pack' ),
				'type'        => 'text',
				'placeholder' => $blog_name,
				'condshow'    => array( 'aiosp_google_set_site_name' => 'on' ),
			),
			'google_author_advanced'      => array(
				'name'            => __( 'Advanced Authorship Options:', 'all-in-one-seo-pack' ),
				'default'         => 0,
				'type'            => 'radio',
				'initial_options' => array(
					'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
					0    => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
				'label'           => null,
			),
			'google_author_location'      => array(
				'name'     => __( 'Display Google Authorship:', 'all-in-one-seo-pack' ),
				'default'  => array( 'all' ),
				'type'     => 'multicheckbox',
				'condshow' => array( 'aiosp_google_author_advanced' => 'on' ),
			),
			'google_enable_publisher'     => array(
				'name'            => __( 'Display Publisher Meta on Front Page:', 'all-in-one-seo-pack' ),
				'default'         => 'on',
				'type'            => 'radio',
				'initial_options' => array(
					'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
					0    => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
				'condshow'        => array( 'aiosp_google_author_advanced' => 'on' ),
			),
			'google_specify_publisher'    => array(
				'name'     => __( 'Specify Publisher URL:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'condshow' => array( 'aiosp_google_author_advanced' => 'on', 'aiosp_google_enable_publisher' => 'on' ),
			),
			// "google_connect"=>array( 'name' => __( 'Connect With Google Analytics', 'all-in-one-seo-pack' ), ),
			'google_analytics_id'         => array(
				'name'        => __( 'Google Analytics ID:', 'all-in-one-seo-pack' ),
				'default'     => null,
				'type'        => 'text',
				'placeholder' => 'UA-########-#',
			),
			'ga_advanced_options'         => array(
				'name'            => __( 'Advanced Analytics Options:', 'all-in-one-seo-pack' ),
				'default'         => 'on',
				'type'            => 'radio',
				'initial_options' => array(
					'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
					0    => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
				'condshow'        => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
				),
			),
			'ga_domain'                   => array(
				'name'     => __( 'Tracking Domain:', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_multi_domain'             => array(
				'name'     => __( 'Track Multiple Domains:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_addl_domains'             => array(
				'name'     => __( 'Additional Domains:', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
					'aiosp_ga_multi_domain'     => 'on',
				),
			),
			'ga_anonymize_ip'             => array(
				'name'     => __( 'Anonymize IP Addresses:', 'all-in-one-seo-pack' ),
				'type'     => 'checkbox',
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_display_advertising'      => array(
				'name'     => __( 'Display Advertiser Tracking:', 'all-in-one-seo-pack' ),
				'type'     => 'checkbox',
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_exclude_users'            => array(
				'name'     => __( 'Exclude Users From Tracking:', 'all-in-one-seo-pack' ),
				'type'     => 'multicheckbox',
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_track_outbound_links'     => array(
				'name'     => __( 'Track Outbound Links:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_link_attribution'         => array(
				'name'     => __( 'Enhanced Link Attribution:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array(
					'aiosp_google_analytics_id' => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options' => 'on',
				),
			),
			'ga_enhanced_ecommerce'       => array(
				'name'     => __( 'Enhanced Ecommerce:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array(
					'aiosp_google_analytics_id'        => array(
						'lhs' => 'aiosp_google_analytics_id',
						'op'  => '!=',
						'rhs' => '',
					),
					'aiosp_ga_advanced_options'        => 'on',
				),
			),
			'use_categories'              => array(
				'name'     => __( 'Use Categories for META keywords:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'use_tags_as_keywords'        => array(
				'name'     => __( 'Use Tags for META keywords:', 'all-in-one-seo-pack' ),
				'default'  => 1,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'dynamic_postspage_keywords'  => array(
				'name'     => __( 'Dynamically Generate Keywords for Posts Page/Archives:', 'all-in-one-seo-pack' ),
				'default'  => 1,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'category_noindex'            => array(
				'name'    => __( 'Use noindex for Categories:', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'archive_date_noindex'        => array(
				'name'    => __( 'Use noindex for Date Archives:', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'archive_author_noindex'      => array(
				'name'    => __( 'Use noindex for Author Archives:', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'tags_noindex'                => array(
				'name'    => __( 'Use noindex for Tag Archives:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'search_noindex'              => array(
				'name'    => __( 'Use noindex for the Search page:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'404_noindex'                 => array(
				'name'    => __( 'Use noindex for the 404 page:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'tax_noindex'                 => array(
				'name'     => __( 'Use noindex for Taxonomy Archives:', 'all-in-one-seo-pack' ),
				'type'     => 'multicheckbox',
				'default'  => array(),
				'condshow' => array( 'aiosp_enablecpost' => 'on', 'aiosp_cpostadvanced' => 'on' ),
			),
			'paginated_noindex'           => array(
				'name'    => __( 'Use noindex for paginated pages/posts:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'paginated_nofollow'          => array(
				'name'    => __( 'Use nofollow for paginated pages/posts:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'generate_descriptions'       => array(
				'name'    => __( 'Autogenerate Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'skip_excerpt'                => array(
				'name'    => __( 'Use Content For Autogenerated Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => array( 'aiosp_generate_descriptions' => 'on' ),
			),
			'run_shortcodes'              => array(
				'name'     => __( 'Run Shortcodes In Autogenerated Descriptions:', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_generate_descriptions' => 'on' ),
			),
			'hide_paginated_descriptions' => array(
				'name'    => __( 'Remove Descriptions For Paginated Pages:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'dont_truncate_descriptions'  => array(
				'name'    => __( 'Never Shorten Long Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'schema_markup'               => array(
				'name'    => __( 'Use Schema.org Markup', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'unprotect_meta'              => array(
				'name'    => __( 'Unprotect Post Meta Fields:', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'redirect_attachement_parent' => array(
				'name'    => __( 'Redirect Attachments to Post Parent:', 'all-in-one-seo-pack'),
				'default' => 0,
			),
			'ex_pages'                    => array(
				'name'    => __( 'Exclude Pages:', 'all-in-one-seo-pack' ),
				'type'    => 'textarea',
				'default' => '',
			),
			'post_meta_tags'              => array(
				'name'     => __( 'Additional Post Headers:', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'page_meta_tags'              => array(
				'name'     => __( 'Additional Page Headers:', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'front_meta_tags'             => array(
				'name'     => __( 'Additional Front Page Headers:', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'home_meta_tags'              => array(
				'name'     => __( 'Additional Blog Page Headers:', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'do_log'                      => array(
				'name'    => __( 'Log important events:', 'all-in-one-seo-pack' ),
				'default' => null,
			),
		);

		if ( ! AIOSEOPPRO ) {
			unset( $this->default_options['license_key'] );
			unset( $this->default_options['taxactive'] );
		}

		$this->locations = array(
			'default' => array( 'name' => $this->name, 'prefix' => 'aiosp_', 'type' => 'settings', 'options' => null ),
			'aiosp'   => array(
				'name'            => $this->plugin_name,
				'type'            => 'metabox',
				'prefix'          => '',
				'help_link'       => 'https://semperplugins.com/documentation/post-settings/',
				'options'         => array(
					'edit',
					'nonce-aioseop-edit',
					AIOSEOPPRO ? 'support' : 'upgrade',
					'snippet',
					'title',
					'description',
					'keywords',
					'custom_link',
					'noindex',
					'nofollow',
					'sitemap_exclude',
					'disable',
					'disable_analytics',
				),
				'default_options' => array(
					'edit'               => array(
						'type'    => 'hidden',
						'default' => 'aiosp_edit',
						'prefix'  => true,
						'nowrap'  => 1,
					),
					'nonce-aioseop-edit' => array(
						'type'    => 'hidden',
						'default' => null,
						'prefix'  => false,
						'nowrap'  => 1,
					),
					'upgrade'            => array(
						'type'    => 'html',
						'label'   => 'none',
						'default' => aiosp_common::get_upgrade_hyperlink( 'meta', __( 'Upgrade to All in One SEO Pack Pro Version', 'all-in-one-seo-pack' ), __( 'UPGRADE TO PRO VERSION', 'all-in-one-seo-pack' ), '_blank' ),
					),
					'support'            => array(
						'type'    => 'html',
						'label'   => 'none',
						'default' => '<a target="_blank" href="https://semperplugins.com/support/">'
						             . __( 'Support Forum', 'all-in-one-seo-pack' ) . '</a>',
					),
					'snippet'            => array(
						'name'    => __( 'Preview Snippet', 'all-in-one-seo-pack' ),
						'type'    => 'custom',
						'label'   => 'top',
						'default' => '
																									<script>
																									jQuery(document).ready(function() {
																										jQuery("#aiosp_title_wrapper").bind("input", function() {
																										    jQuery("#aiosp_snippet_title").text(jQuery("#aiosp_title_wrapper input").val().replace(/<(?:.|\n)*?>/gm, ""));
																										});
																										jQuery("#aiosp_description_wrapper").bind("input", function() {
																										    jQuery("#aioseop_snippet_description").text(jQuery("#aiosp_description_wrapper textarea").val().replace(/<(?:.|\n)*?>/gm, ""));
																										});
																									});
																									</script>
																									<div class="preview_snippet"><div id="aioseop_snippet"><h3><a>%s</a></h3><div><div><cite id="aioseop_snippet_link">%s</cite></div><span id="aioseop_snippet_description">%s</span></div></div></div>',
					),
					'title'              => array(
						'name'  => __( 'Title', 'all-in-one-seo-pack' ),
						'type'  => 'text',
						'count' => true,
						'size'  => 60,
					),
					'description'        => array(
						'name'  => __( 'Description', 'all-in-one-seo-pack' ),
						'type'  => 'textarea',
						'count' => true,
						'cols'  => 80,
						'rows'  => 2,
					),

					'keywords'          => array(
						'name' => __( 'Keywords (comma separated)', 'all-in-one-seo-pack' ),
						'type' => 'text',
					),
					'custom_link'       => array(
						'name' => __( 'Custom Canonical URL', 'all-in-one-seo-pack' ),
						'type' => 'text',
						'size' => 60,
					),
					'noindex'           => array(
						'name'    => __( 'NOINDEX this page/post', 'all-in-one-seo-pack' ),
						'default' => '',
					),
					'nofollow'          => array(
						'name'    => __( 'NOFOLLOW this page/post', 'all-in-one-seo-pack' ),
						'default' => '',
					),
					'sitemap_exclude'   => array( 'name' => __( 'Exclude From Sitemap', 'all-in-one-seo-pack' ) ),
					'disable'           => array( 'name' => __( 'Disable on this page/post', 'all-in-one-seo-pack' ) ),
					'disable_analytics' => array(
						'name'     => __( 'Disable Google Analytics', 'all-in-one-seo-pack' ),
						'condshow' => array( 'aiosp_disable' => 'on' ),
					),
				),
				'display'         => null,
			),
		);

		if ( ! empty( $meta_help_text ) ) {
			foreach ( $meta_help_text as $k => $v ) {
				$this->locations['aiosp']['default_options'][ $k ]['help_text'] = $v;
			}
		}

		$this->layout = array(
			'default'   => array(
				'name'      => __( 'General Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/general-settings/',
				'options'   => array(), // This is set below, to the remaining options -- pdb.
			),
			'home'      => array(
				'name'      => __( 'Home Page Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/home-page-settings/',
				'options'   => array( 'home_title', 'home_description', 'home_keywords', 'use_static_home_info' ),
			),
			'title'     => array(
				'name'      => __( 'Title Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/title-settings/',
				'options'   => array(
					'rewrite_titles',
					'force_rewrites',
					'home_page_title_format',
					'page_title_format',
					'post_title_format',
					'category_title_format',
					'archive_title_format',
					'date_title_format',
					'author_title_format',
					'tag_title_format',
					'search_title_format',
					'description_format',
					'404_title_format',
					'paged_format',
				),
			),
			'cpt'       => array(
				'name'      => __( 'Custom Post Type Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/custom-post-type-settings/',
				'options'   => array( 'enablecpost', 'cpostadvanced', 'taxactive', 'cpostactive', 'cposttitles' ),
			),
			'display'   => array(
				'name'      => __( 'Display Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/display-settings/',
				'options'   => array( 'posttypecolumns' ),
			),
			'webmaster' => array(
				'name'      => __( 'Webmaster Verification', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/sections/webmaster-verification/',
				'options'   => array( 'google_verify', 'bing_verify', 'pinterest_verify' ),
			),
			'google'    => array(
				'name'      => __( 'Google Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/google-settings/',
				'options'   => array(
					'google_publisher',
					'google_disable_profile',
					'google_sitelinks_search',
					'google_set_site_name',
					'google_specify_site_name',
					'google_author_advanced',
					'google_author_location',
					'google_enable_publisher',
					'google_specify_publisher',
					//	"google_connect",
					'google_analytics_id',
					'ga_advanced_options',
					'ga_domain',
					'ga_multi_domain',
					'ga_addl_domains',
					'ga_anonymize_ip',
					'ga_display_advertising',
					'ga_exclude_users',
					'ga_track_outbound_links',
					'ga_link_attribution',
					'ga_enhanced_ecommerce',
				),
			),
			'noindex'   => array(
				'name'      => __( 'Noindex Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/noindex-settings/',
				'options'   => array(
					'cpostnoindex',
					'cpostnofollow',
					'category_noindex',
					'archive_date_noindex',
					'archive_author_noindex',
					'tags_noindex',
					'search_noindex',
					'404_noindex',
					'tax_noindex',
					'paginated_noindex',
					'paginated_nofollow',
				),
			),
			'advanced'  => array(
				'name'      => __( 'Advanced Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/all-in-one-seo-pack-advanced-settings/',
				'options'   => array(
					'generate_descriptions',
					'skip_excerpt',
					'run_shortcodes',
					'hide_paginated_descriptions',
					'dont_truncate_descriptions',
					'unprotect_meta',
					'redirect_attachement_parent',
					'ex_pages',
					'post_meta_tags',
					'page_meta_tags',
					'front_meta_tags',
					'home_meta_tags',
				),
			),
			'keywords'  => array(
				'name'      => __( 'Keyword Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/keyword-settings/',
				'options'   => array(
					'togglekeywords',
					'use_categories',
					'use_tags_as_keywords',
					'dynamic_postspage_keywords',
				),
			),
		);

		if ( AIOSEOPPRO ) {
			// Add Pro options.
			$this->default_options = aioseop_add_pro_opt( $this->default_options );
			$this->help_text       = aioseop_add_pro_help( $this->help_text );
			$this->layout          = aioseop_add_pro_layout( $this->layout );
		}

		if ( ! AIOSEOPPRO ) {
			unset( $this->layout['cpt']['options']['2'] );
		}

		$other_options = array();
		foreach ( $this->layout as $k => $v ) {
			$other_options = array_merge( $other_options, $v['options'] );
		}

		$this->layout['default']['options'] = array_diff( array_keys( $this->default_options ), $other_options );

		if ( is_admin() ) {
			$this->add_help_text_links();
			add_action( 'aioseop_global_settings_header', array( $this, 'display_right_sidebar' ) );
			add_action( 'aioseop_global_settings_footer', array( $this, 'display_settings_footer' ) );
			add_action( 'output_option', array( $this, 'custom_output_option' ), 10, 2 );
			add_action( 'all_admin_notices', array( $this, 'visibility_warning' ) );

			if ( ! AIOSEOPPRO ) {
				//	add_action('all_admin_notices', array( $this, 'woo_upgrade_notice'));
			}
		}
		if ( AIOSEOPPRO ) {
			add_action( 'split_shared_term', array( $this, 'split_shared_term' ), 10, 4 );
		}
	}

	//good candidate for pro dir

	/**
	 * Use custom callback for outputting snippet
	 *
	 * @param $buf
	 * @param $args
	 *
	 * @return string
	 */
	function custom_output_option( $buf, $args ) {
		if ( 'aiosp_snippet' === $args['name'] ) {
			$args['options']['type']   = 'html';
			$args['options']['nowrap'] = false;
			$args['options']['save']   = false;
			$info                      = $this->get_page_snippet_info();
			extract( $info );
		} else {
			return '';
		}

		if ( $this->strlen( $title ) > 70 ) {
			$title = $this->trim_excerpt_without_filters( $title, 70 ) . '...';
		}
		if ( $this->strlen( $description ) > 156 ) {
			$description = $this->trim_excerpt_without_filters( $description, 156 ) . '...';
		}
		$extra_title_len = 0;
		if ( empty( $title_format ) ) {
			$title = '<span id="' . $args['name'] . '_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title ) ) ) . '</span>';
		} else {
			if ( strpos( $title_format, '%blog_title%' ) !== false ) {
				$title_format = str_replace( '%blog_title%', get_bloginfo( 'name' ), $title_format );
			}
			$title_format  = $this->apply_cf_fields( $title_format );
			$replace_title = '<span id="' . $args['name'] . '_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title ) ) ) . '</span>';
			if ( strpos( $title_format, '%post_title%' ) !== false ) {
				$title_format = str_replace( '%post_title%', $replace_title, $title_format );
			}
			if ( strpos( $title_format, '%page_title%' ) !== false ) {
				$title_format = str_replace( '%page_title%', $replace_title, $title_format );
			}
			if ( strpos( $title_format, '%current_date%' ) !== false ) {
				$title_format = str_replace( '%current_date%', date_i18n( get_option( 'date_format' ) ), $title_format );
			}
			if ( strpos( $title_format, "%post_date%" ) !== false ){
				$title_format = str_replace( '%post_date%', get_the_date(), $title_format );
			}
			if ( strpos( $title_format, "%post_year%" ) !== false ){
				$title_format = str_replace( '%post_year%', get_the_date( 'Y' ), $title_format );
			}
			if ( strpos( $title_format, "%post_month%" ) !== false ){
				$title_format = str_replace( '%post_month%', get_the_date( 'F' ), $title_format );
			}
			if ( $w->is_category || $w->is_tag || $w->is_tax ) {
				if ( AIOSEOPPRO && ! empty( $_GET ) && ! empty( $_GET['taxonomy'] ) && ! empty( $_GET['tag_ID'] ) && function_exists( 'wp_get_split_terms' ) ) {
					$term_id   = intval( $_GET['tag_ID'] );
					$was_split = get_term_meta( $term_id, '_aioseop_term_was_split', true );
					if ( ! $was_split ) {
						$split_terms = wp_get_split_terms( $term_id, $_GET['taxonomy'] );
						if ( ! empty( $split_terms ) ) {
							foreach ( $split_terms as $new_tax => $new_term ) {
								$this->split_shared_term( $term_id, $new_term );
							}
						}
					}
				}
				if ( strpos( $title_format, '%category_title%' ) !== false ) {
					$title_format = str_replace( '%category_title%', $replace_title, $title_format );
				}
				if ( strpos( $title_format, '%taxonomy_title%' ) !== false ) {
					$title_format = str_replace( '%taxonomy_title%', $replace_title, $title_format );
				}
			} else {
				if ( strpos( $title_format, '%category%' ) !== false ) {
					$title_format = str_replace( '%category%', $category, $title_format );
				}
				if ( strpos( $title_format, '%category_title%' ) !== false ) {
					$title_format = str_replace( '%category_title%', $category, $title_format );
				}
				if ( strpos( $title_format, '%taxonomy_title%' ) !== false ) {
					$title_format = str_replace( '%taxonomy_title%', $category, $title_format );
				}
				if ( AIOSEOPPRO ) {
					if ( strpos( $title_format, '%tax_' ) && ! empty( $p ) ) {
						$taxes = get_object_taxonomies( $p, 'objects' );
						if ( ! empty( $taxes ) ) {
							foreach ( $taxes as $t ) {
								if ( strpos( $title_format, "%tax_{$t->name}%" ) ) {
									$terms = $this->get_all_terms( $p->ID, $t->name );
									$term  = '';
									if ( count( $terms ) > 0 ) {
										$term = $terms[0];
									}
									$title_format = str_replace( "%tax_{$t->name}%", $term, $title_format );
								}
							}
						}
					}
				}
			}
			if ( strpos( $title_format, '%taxonomy_description%' ) !== false ) {
				$title_format = str_replace( '%taxonomy_description%', $description, $title_format );
			}

			$title_format    = preg_replace( '/%([^%]*?)%/', '', $title_format );
			$title           = $title_format;
			$extra_title_len = strlen( str_replace( $replace_title, '', $title_format ) );
		}

		$args['value']   = sprintf( $args['value'], $title, esc_url( $url ), esc_attr( $description ) );
		$extra_title_len = (int) $extra_title_len;
		$args['value'] .= "<script>var aiosp_title_extra = {$extra_title_len};</script>";
		$buf = $this->get_option_row( $args['name'], $args['options'], $args );

		return $buf;
	}

//good candidate for pro dir

	/**
	 * @return array
	 */
	function get_page_snippet_info() {
		static $info = array();
		if ( ! empty( $info ) ) {
			return $info;
		}
		global $post, $aioseop_options, $wp_query;
		$title = $url = $description = $term = $category = '';
		$p     = $post;
		$w     = $wp_query;
		if ( ! is_object( $post ) ) {
			$post = $this->get_queried_object();
		}
		if ( empty( $this->meta_opts ) ) {
			$this->meta_opts = $this->get_current_options( array(), 'aiosp' );
		}
		if ( ! is_object( $post ) && is_admin() && ! empty( $_GET ) && ! empty( $_GET['post_type'] ) && ! empty( $_GET['taxonomy'] ) && ! empty( $_GET['tag_ID'] ) ) {
			$term = get_term_by( 'id', $_GET['tag_ID'], $_GET['taxonomy'] );
		}
		if ( is_object( $post ) ) {
			$opts    = $this->meta_opts;
			$post_id = $p->ID;
			if ( empty( $post->post_modified_gmt ) ) {
				$wp_query = new WP_Query( array( 'p' => $post_id, 'post_type' => $post->post_type ) );
			}
			if ( 'page' === $post->post_type ) {
				$wp_query->is_page = true;
			} elseif ( 'attachment' === $post->post_type ) {
				$wp_query->is_attachment = true;
			} else {
				$wp_query->is_single = true;
			}
			if ( empty( $this->is_front_page ) ) {
				$this->is_front_page = false;
			}
			if ( 'page' === get_option( 'show_on_front' ) ) {
				if ( is_page() && $post->ID == get_option( 'page_on_front' ) ) {
					$this->is_front_page = true;
				} elseif ( $post->ID == get_option( 'page_for_posts' ) ) {
					$wp_query->is_home = true;
				}
			}
			$wp_query->queried_object = $post;
			if ( ! empty( $post ) && ! $wp_query->is_home && ! $this->is_front_page ) {
				$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
				if ( empty( $title ) ) {
					$title = $post->post_title;
				}
			}
			$title_format = '';
			if ( empty( $title ) ) {
				$title = $this->wp_title();
			}
			$description = $this->get_main_description( $post );

			// All this needs to be in it's own function (class really)

			if ( empty( $title_format ) ) {
				if ( is_page() ) {
					$title_format = $aioseop_options['aiosp_page_title_format'];

				} elseif ( is_single() || is_attachment() ) {
					$title_format = $this->get_post_title_format( 'post', $post );
				}
			}
			if ( empty( $title_format ) ) {
				$title_format = '%post_title%';
			}
			$categories = $this->get_all_categories( $post_id );
			$category   = '';
			if ( count( $categories ) > 0 ) {
				$category = $categories[0];
			}
		} else if ( is_object( $term ) ) {
			if ( 'category' === $_GET['taxonomy'] ) {
				query_posts( array( 'cat' => $_GET['tag_ID'] ) );
			} else if ( 'post_tag' === $_GET['taxonomy'] ) {
				query_posts( array( 'tag' => $term->slug ) );
			} else {
				query_posts( array(
					'page'            => '',
					$_GET['taxonomy'] => $term->slug,
					'post_type'       => $_GET['post_type'],
				) );
			}
			if ( empty( $this->meta_opts ) ) {
				$this->meta_opts = $this->get_current_options( array(), 'aiosp' );
			}
			$title        = $this->get_tax_name( $_GET['taxonomy'] );
			$title_format = $this->get_tax_title_format();
			$opts         = $this->meta_opts;
			if ( ! empty( $opts ) ) {
				$description = $opts['aiosp_description'];
			}
			if ( empty( $description ) ) {
				$description = term_description();
			}
			$description = $this->internationalize( $description );
		}
		if($this->is_front_page == true ){
			//$title_format = $aioseop_options['aiosp_home_page_title_format'];
			$title_format = ''; // Not sure why this needs to be this way, but we should extract all this out to figure out what's going on.
		}
		$show_page = true;
		if ( ! empty( $aioseop_options['aiosp_no_paged_canonical_links'] ) ) {
			$show_page = false;
		}
		if ( $aioseop_options['aiosp_can'] ) {
			if ( ! empty( $aioseop_options['aiosp_customize_canonical_links'] ) && ! empty( $opts['aiosp_custom_link'] ) ) {
				$url = $opts['aiosp_custom_link'];
			}
			if ( empty( $url ) ) {
				$url = $this->aiosp_mrt_get_url( $wp_query, $show_page );
			}
			$url = apply_filters( 'aioseop_canonical_url', $url );
		}
		if ( ! $url ) {
			$url = get_permalink();
		}

		$title       = $this->apply_cf_fields( $title );
		$description = $this->apply_cf_fields( $description );
		$description = apply_filters( 'aioseop_description', $description );

		$keywords = $this->get_main_keywords();
		$keywords = $this->apply_cf_fields( $keywords );
		$keywords = apply_filters( 'aioseop_keywords', $keywords );

		$info = array(
			'title'        => $title,
			'description'  => $description,
			'keywords'     => $keywords,
			'url'          => $url,
			'title_format' => $title_format,
			'category'     => $category,
			'w'            => $wp_query,
			'p'            => $post,
		);
		wp_reset_postdata();
		$wp_query = $w;
		$post     = $p;

		return $info;
	}

	/**
	 * @return null|object|WP_Post
	 */
	function get_queried_object() {
		static $p = null;
		global $wp_query, $post;
		if ( null !== $p ) {
			return $p;
		}
		if ( is_object( $post ) ) {
			$p = $post;
		} else {
			if ( ! $wp_query ) {
				return null;
			}
			$p = $wp_query->get_queried_object();
		}

		return $p;
	}

	/**
	 * @param array $opts
	 * @param null $location
	 * @param null $defaults
	 * @param null $post
	 *
	 * @return array
	 */
	function get_current_options( $opts = array(), $location = null, $defaults = null, $post = null ) {
		if ( ( 'aiosp' === $location) && ( 'metabox' == $this->locations[ $location ]['type'] ) ) {
			if ( null === $post ) {
				global $post;
			}
			$post_id = $post;
			if ( is_object( $post_id ) ) {
				$post_id = $post_id->ID;
			}
			$get_opts = $this->default_options( $location );
			$optlist  = array(
				'keywords',
				'description',
				'title',
				'custom_link',
				'sitemap_exclude',
				'disable',
				'disable_analytics',
				'noindex',
				'nofollow',
			);
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) && ( ! empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist['custom_link'] );
			}
			foreach ( $optlist as $f ) {
				$meta  = '';
				$field = "aiosp_$f";

				if ( AIOSEOPPRO ) {
					if ( ( isset( $_GET['taxonomy'] ) && isset( $_GET['tag_ID'] ) ) || is_category() || is_tag() || is_tax() ) {
						if ( is_admin() && isset( $_GET['tag_ID'] ) ) {
							$meta = get_term_meta( $_GET['tag_ID'], '_aioseop_' . $f, true );
						} else {
							$queried_object = get_queried_object();
							if ( ! empty( $queried_object ) && ! empty( $queried_object->term_id ) ) {
								$meta = get_term_meta( $queried_object->term_id, '_aioseop_' . $f, true );
							}
						}
					} else {
						$meta = get_post_meta( $post_id, '_aioseop_' . $f, true );
					}
					if ( 'title' === $f || 'description' === $f ) {
						$get_opts[ $field ] = htmlspecialchars( $meta );
					} else {
						$get_opts[ $field ] = htmlspecialchars( stripslashes( $meta ) );
					}
				} else {
					if ( ! is_category() && ! is_tag() && ! is_tax() ) {
						$field = "aiosp_$f";
						$meta  = get_post_meta( $post_id, '_aioseop_' . $f, true );
						if ( 'title' === $f || 'description' === $f ) {
							$get_opts[ $field ] = htmlspecialchars( $meta );
						} else {
							$get_opts[ $field ] = htmlspecialchars( stripslashes( $meta ) );
						}
					}
				}

			}
			$opts = wp_parse_args( $opts, $get_opts );

			return $opts;
		} else {
			$options = parent::get_current_options( $opts, $location, $defaults );

			return $options;
		}
	}

	/**
	 * @param $in
	 *
	 * @return mixed|void
	 */
	function internationalize( $in ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'localization', $in );
	}

	/*** Used to filter wp_title(), get our title. ***/
	function wp_title() {
		global $aioseop_options;
		$title = false;
		$post  = $this->get_queried_object();
		if ( ! empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
			$title = $this->get_aioseop_title( $post );
			$title = $this->apply_cf_fields( $title );
		}

		if ( false === $title ) {
			$title = $this->get_original_title();
		}

		return apply_filters( 'aioseop_title', $title );
	}

	/**
	 * Gets the title that will be used by AIOSEOP for title rewrites or returns false.
	 *
	 * @param $post
	 *
	 * @return bool|string
	 */
	function get_aioseop_title( $post ) {
		global $aioseop_options;
		// the_search_query() is not suitable, it cannot just return.
		global $s, $STagging;
		$opts = $this->meta_opts;
		if ( is_front_page() ) {
			if ( ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				global $post;
				if ( get_option( 'show_on_front' ) == 'page' && is_page() && $post->ID == get_option( 'page_on_front' ) ) {
					$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
					if ( ! $title ) {
						$title = $this->internationalize( $post->post_title );
					}
					if ( ! $title ) {
						$title = $this->internationalize( $this->get_original_title( '', false ) );
					}
					if ( ! empty( $aioseop_options['aiosp_home_page_title_format'] ) ) {
						$title = $this->apply_page_title_format( $title, $post, $aioseop_options['aiosp_home_page_title_format'] );
					}
					$title = $this->paged_title( $title );
					$title = apply_filters( 'aioseop_home_page_title', $title );
				}
			} else {
				$title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
				if ( ! empty( $aioseop_options['aiosp_home_page_title_format'] ) ) {
					$title = $this->apply_page_title_format( $title, null, $aioseop_options['aiosp_home_page_title_format'] );
				}
			}
			if ( empty( $title ) ) {
				$title = $this->internationalize( get_option( 'blogname' ) ) . ' | ' . $this->internationalize( get_bloginfo( 'description' ) );
			}

			global $post;
			$post_id = $post->ID;

			if ( is_post_type_archive() && is_post_type_archive( 'product' ) && $post_id = wc_get_page_id( 'shop' ) && $post = get_post( $post_id ) ) {
				$frontpage_id = get_option( 'page_on_front' );

				if ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
					$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
				}
				// $title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
				if ( ! $title ) {
					$title = $this->internationalize( get_post_meta( $frontpage_id, '_aioseop_title', true ) );
				} // This is/was causing the first product to come through.
				if ( ! $title ) {
					$title = $this->internationalize( $post->post_title );
				}
				if ( ! $title ) {
					$title = $this->internationalize( $this->get_original_title( '', false ) );
				}

				$title = $this->apply_page_title_format( $title, $post );
				$title = $this->paged_title( $title );
				$title = apply_filters( 'aioseop_title_page', $title );

				return $title;

			}

			return $this->paged_title( $title ); //this is returned for woo
		} else if ( is_attachment() ) {
			if ( null === $post ) {
				return false;
			}
			$title = get_post_meta( $post->ID, '_aioseop_title', true );
			if ( empty( $title ) ) {
				$title = $post->post_title;
			}
			if ( empty( $title ) ) {
				$title = $this->get_original_title( '', false );
			}
			if ( empty( $title ) ) {
				$title = get_the_title( $post->post_parent );
			}
			$title = apply_filters( 'aioseop_attachment_title', $this->internationalize( $this->apply_post_title_format( $title, '', $post ) ) );

			return $title;
		} else if ( is_page() || $this->is_static_posts_page() || ( is_home() && ! $this->is_static_posts_page() ) ) {
			if ( null === $post ) {
				return false;
			}
			if ( $this->is_static_front_page() && ( $home_title = $this->internationalize( $aioseop_options['aiosp_home_title'] ) ) ) {
				if ( ! empty( $aioseop_options['aiosp_home_page_title_format'] ) ) {
					$home_title = $this->apply_page_title_format( $home_title, $post, $aioseop_options['aiosp_home_page_title_format'] );
				}

				// Home title filter.
				return apply_filters( 'aioseop_home_page_title', $home_title );
			} else {
				$page_for_posts = '';
				if ( is_home() ) {
					$page_for_posts = get_option( 'page_for_posts' );
				}
				if ( $page_for_posts ) {
					$title = $this->internationalize( get_post_meta( $page_for_posts, '_aioseop_title', true ) );
					if ( ! $title ) {
						$post_page = get_post( $page_for_posts );
						$title     = $this->internationalize( $post_page->post_title );
					}
				} else {
					$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
					if ( ! $title ) {
						$title = $this->internationalize( $post->post_title );
					}
				}
				if ( ! $title ) {
					$title = $this->internationalize( $this->get_original_title( '', false ) );
				}

				$title = $this->apply_page_title_format( $title, $post );
				$title = $this->paged_title( $title );
				$title = apply_filters( 'aioseop_title_page', $title );
				if ( $this->is_static_posts_page() ) {
					$title = apply_filters( 'single_post_title', $title );
				}

				return $title;
			}
		} else if ( function_exists( 'wc_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = wc_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
			// Too far down? -mrt.
			$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
			if ( ! $title ) {
				$title = $this->internationalize( $post->post_title );
			}
			if ( ! $title ) {
				$title = $this->internationalize( $this->get_original_title( '', false ) );
			}
			$title = $this->apply_page_title_format( $title, $post );
			$title = $this->paged_title( $title );
			$title = apply_filters( 'aioseop_title_page', $title );

			return $title;
		} else if ( is_single() ) {
			// We're not in the loop :(.
			if ( null === $post ) {
				return false;
			}
			$categories = $this->get_all_categories();
			$category   = '';
			if ( count( $categories ) > 0 ) {
				$category = $categories[0];
			}
			$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
			if ( ! $title ) {
				$title = $this->internationalize( get_post_meta( $post->ID, 'title_tag', true ) );
				if ( ! $title ) {
					$title = $this->internationalize( $this->get_original_title( '', false ) );
				}
			}
			if ( empty( $title ) ) {
				$title = $post->post_title;
			}
			if ( ! empty( $title ) ) {
				$title = $this->apply_post_title_format( $title, $category, $post );
			}
			$title = $this->paged_title( $title );

			return apply_filters( 'aioseop_title_single', $title );
		} else if ( is_search() && isset( $s ) && ! empty( $s ) ) {
			$search = esc_attr( stripslashes( $s ) );
			$title_format = $aioseop_options['aiosp_search_title_format'];
			$title        = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( strpos( $title, '%blog_description%' ) !== false ) {
				$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
			}
			if ( strpos( $title, '%search%' ) !== false ) {
				$title = str_replace( '%search%', $search, $title );
			}
			$title = $this->paged_title( $title );

			return $title;
		} else if ( is_tag() ) {
			global $utw;
			$tag = $tag_description = '';
			if ( $utw ) {
				$tags = $utw->GetCurrentTagSet();
				$tag  = $tags[0]->tag;
				$tag  = str_replace( '-', ' ', $tag );
			} else {
				if ( AIOSEOPPRO ) {
					if ( ! empty( $opts ) && ! empty( $opts['aiosp_title'] ) ) {
						$tag = $opts['aiosp_title'];
					}
					if ( ! empty( $opts ) ) {
						if ( ! empty( $opts['aiosp_title'] ) ) {
							$tag = $opts['aiosp_title'];
						}
						if ( ! empty( $opts['aiosp_description'] ) ) {
							$tag_description = $opts['aiosp_description'];
						}
					}
				}
				if ( empty( $tag ) ) {
					$tag = $this->get_original_title( '', false );
				}
				if ( empty( $tag_description ) ) {
					$tag_description = tag_description();
				}
				$tag             = $this->internationalize( $tag );
				$tag_description = $this->internationalize( $tag_description );
			}
			if ( $tag ) {
				$title_format = $aioseop_options['aiosp_tag_title_format'];
				$title        = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
				if ( strpos( $title, '%blog_description%' ) !== false ) {
					$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( strpos( $title, '%tag%' ) !== false ) {
					$title = str_replace( '%tag%', $tag, $title );
				}
				if ( strpos( $title, '%tag_description%' ) !== false ) {
					$title = str_replace( '%tag_description%', $tag_description, $title );
				}
				if ( strpos( $title, '%taxonomy_description%' ) !== false ) {
					$title = str_replace( '%taxonomy_description%', $tag_description, $title );
				}
				$title = trim( wp_strip_all_tags( $title ) );
				$title = str_replace( array( '"', "\r\n", "\n" ), array( '&quot;', ' ', ' ' ), $title );
				$title = $this->paged_title( $title );

				return $title;
			}
		} else if ( ( is_tax() || is_category() ) && ! is_feed() ) {
			return $this->get_tax_title();
		} else if ( isset( $STagging ) && $STagging->is_tag_view() ) { // Simple tagging support.
			$tag = $STagging->search_tag;
			if ( $tag ) {
				$title_format = $aioseop_options['aiosp_tag_title_format'];
				$title        = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
				if ( strpos( $title, '%blog_description%' ) !== false ) {
					$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( strpos( $title, '%tag%' ) !== false ) {
					$title = str_replace( '%tag%', $tag, $title );
				}
				$title = $this->paged_title( $title );

				return $title;
			}
		} else if ( is_archive() || is_post_type_archive() ) {
			if ( is_author() ) {
				$author       = $this->internationalize( $this->get_original_title( '', false ) );
				$title_format = $aioseop_options['aiosp_author_title_format'];
				$new_title    = str_replace( '%author%', $author, $title_format );
			} else if ( is_date() ) {
				global $wp_query;
				$date         = $this->internationalize( $this->get_original_title( '', false ) );
				$title_format = $aioseop_options['aiosp_date_title_format'];
				$new_title    = str_replace( '%date%', $date, $title_format );
				$day          = get_query_var( 'day' );
				if ( empty( $day ) ) {
					$day = '';
				}
				$new_title = str_replace( '%day%', $day, $new_title );
				$monthnum  = get_query_var( 'monthnum' );
				$year      = get_query_var( 'year' );
				if ( empty( $monthnum ) || is_year() ) {
					$month    = '';
					$monthnum = 0;
				}
				$month     = date( 'F', mktime( 0, 0, 0, (int) $monthnum, 1, (int) $year ) );
				$new_title = str_replace( '%monthnum%', $monthnum, $new_title );
				if ( strpos( $new_title, '%month%' ) !== false ) {
					$new_title = str_replace( '%month%', $month, $new_title );
				}
				if ( strpos( $new_title, '%year%' ) !== false ) {
					$new_title = str_replace( '%year%', get_query_var( 'year' ), $new_title );
				}
			} else if ( is_post_type_archive() ) {
				if ( empty( $title ) ) {
					$title = $this->get_original_title( '', false );
				}
				$new_title = apply_filters( 'aioseop_archive_title', $this->apply_archive_title_format( $title ) );
			} else {
				return false;
			}
			$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
			if ( strpos( $new_title, '%blog_description%' ) !== false ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			$title = trim( $new_title );
			$title = $this->paged_title( $title );

			return $title;
		} else if ( is_404() ) {
			$title_format = $aioseop_options['aiosp_404_title_format'];
			$new_title    = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( strpos( $new_title, '%blog_description%' ) !== false ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( strpos( $new_title, '%request_url%' ) !== false ) {
				$new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title );
			}
			if ( strpos( $new_title, '%request_words%' ) !== false ) {
				$new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			}
			if ( strpos( $new_title, '%404_title%' ) !== false ) {
				$new_title = str_replace( '%404_title%', $this->internationalize( $this->get_original_title( '', false ) ), $new_title );
			}

			return $new_title;
		}

		return false;
	}

	/**
	 * @param string $sep
	 * @param bool $echo
	 * @param string $seplocation
	 *
	 * @return The original title as delivered by WP (well, in most cases).
	 */
	function get_original_title( $sep = '|', $echo = false, $seplocation = '' ) {
		global $aioseop_options;
		if ( ! empty( $aioseop_options['aiosp_use_original_title'] ) ) {
			$has_filter = has_filter( 'wp_title', array( $this, 'wp_title' ) );
			if ( false !== $has_filter ) {
				remove_filter( 'wp_title', array( $this, 'wp_title' ), $has_filter );
			}
			if ( current_theme_supports( 'title-tag' ) ) {
				$sep         = '|';
				$echo        = false;
				$seplocation = 'right';
			}
			$title = wp_title( $sep, $echo, $seplocation );
			if (  false !== $has_filter ) {
				add_filter( 'wp_title', array( $this, 'wp_title' ), $has_filter );
			}
			if ( $title && ( $title = trim( $title ) ) ) {
				return trim( $title );
			}
		}

		// the_search_query() is not suitable, it cannot just return.
		global $s;

		$title = null;

		if ( is_home() ) {
			$title = get_option( 'blogname' );
		} else if ( is_single() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} else if ( is_search() && isset( $s ) && ! empty( $s ) ) {
			$search = esc_attr( stripslashes( $s ) );
			$title = $search;
		} else if ( ( is_tax() || is_category() ) && ! is_feed() ) {
			$category_name = $this->ucwords( $this->internationalize( single_cat_title( '', false ) ) );
			$title         = $category_name;
		} else if ( is_page() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} else if ( is_tag() ) {
			global $utw;
			if ( $utw ) {
				$tags = $utw->GetCurrentTagSet();
				$tag  = $tags[0]->tag;
				$tag  = str_replace( '-', ' ', $tag );
			} else {
				// For WordPress > 2.3.
				$tag = $this->internationalize( single_term_title( '', false ) );
			}
			if ( $tag ) {
				$title = $tag;
			}
		} else if ( is_author() ) {
			$author = get_userdata( get_query_var( 'author' ) );
			if ( $author === false ) {
				global $wp_query;
				$author = $wp_query->get_queried_object();
			}
			if ( $author !== false ) {
				$title = $author->display_name;
			}
		} else if ( is_day() ) {
			$title = get_the_date();
		} else if ( is_month() ) {
			$title = get_the_date( 'F, Y' );
		} else if ( is_year() ) {
			$title = get_the_date( 'Y' );
		} else if ( is_archive() ) {
			$title = $this->internationalize( post_type_archive_title( '', false ) );
		} else if ( is_404() ) {
			$title_format = $aioseop_options['aiosp_404_title_format'];
			$new_title    = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( strpos( $new_title, '%blog_description%' ) !== false ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( strpos( $new_title, '%request_url%' ) !== false ) {
				$new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title );
			}
			if ( strpos( $new_title, '%request_words%' ) !== false ) {
				$new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			}
			$title = $new_title;
		}

		return trim( $title );
	}

	/**
	 * @param $request
	 *
	 * @return User -readable nice words for a given request.
	 */
	function request_as_words( $request ) {
		$request     = htmlspecialchars( $request );
		$request     = str_replace( '.html', ' ', $request );
		$request     = str_replace( '.htm', ' ', $request );
		$request     = str_replace( '.', ' ', $request );
		$request     = str_replace( '/', ' ', $request );
		$request     = str_replace( '-', ' ', $request );
		$request_a   = explode( ' ', $request );
		$request_new = array();
		foreach ( $request_a as $token ) {
			$request_new[] = $this->ucwords( trim( $token ) );
		}
		$request = implode( ' ', $request_new );

		return $request;
	}

	/**
	 * @param $title
	 * @param null $p
	 * @param string $title_format
	 *
	 * @return string
	 */
	function apply_page_title_format( $title, $p = null, $title_format = '' ) {
		global $aioseop_options;
		if ( $p === null ) {
			global $post;
		} else {
			$post = $p;
		}
		if ( empty( $title_format ) ) {
			$title_format = $aioseop_options['aiosp_page_title_format'];
		}

		return $this->title_placeholder_helper( $title, $post, 'page', $title_format );
	}

	/**
     * Replace title templates inside % symbol.
     *
	 * @param $title
	 * @param $post
	 * @param string $type
	 * @param string $title_format
	 * @param string $category
	 *
	 * @return string
	 */
	function title_placeholder_helper( $title, $post, $type = 'post', $title_format = '', $category = '' ) {
		if ( ! empty( $post ) ) {
			$authordata = get_userdata( $post->post_author );
		} else {
			$authordata = new WP_User();
		}
		$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
		if ( strpos( $new_title, '%blog_description%' ) !== false ) {
			$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
		}
		if ( strpos( $new_title, "%{$type}_title%" ) !== false ) {
			$new_title = str_replace( "%{$type}_title%", $title, $new_title );
		}
		if ( $type == 'post' ) {
			if ( strpos( $new_title, '%category%' ) !== false ) {
				$new_title = str_replace( '%category%', $category, $new_title );
			}
			if ( strpos( $new_title, '%category_title%' ) !== false ) {
				$new_title = str_replace( '%category_title%', $category, $new_title );
			}
			if ( strpos( $new_title, '%tax_' ) && ! empty( $post ) ) {
				$taxes = get_object_taxonomies( $post, 'objects' );
				if ( ! empty( $taxes ) ) {
					foreach ( $taxes as $t ) {
						if ( strpos( $new_title, "%tax_{$t->name}%" ) ) {
							$terms = $this->get_all_terms( $post->ID, $t->name );
							$term  = '';
							if ( count( $terms ) > 0 ) {
								$term = $terms[0];
							}
							$new_title = str_replace( "%tax_{$t->name}%", $term, $new_title );
						}
					}
				}
			}
		}
		if ( strpos( $new_title, "%{$type}_author_login%" ) !== false ) {
			$new_title = str_replace( "%{$type}_author_login%", $authordata->user_login, $new_title );
		}
		if ( strpos( $new_title, "%{$type}_author_nicename%" ) !== false ) {
			$new_title = str_replace( "%{$type}_author_nicename%", $authordata->user_nicename, $new_title );
		}
		if ( strpos( $new_title, "%{$type}_author_firstname%" ) !== false ) {
			$new_title = str_replace( "%{$type}_author_firstname%", $this->ucwords( $authordata->first_name ), $new_title );
		}
		if ( strpos( $new_title, "%{$type}_author_lastname%" ) !== false ) {
			$new_title = str_replace( "%{$type}_author_lastname%", $this->ucwords( $authordata->last_name ), $new_title );
		}
		if ( strpos( $new_title, "%current_date%" ) !== false ){
			$new_title = str_replace( '%current_date%', date_i18n( get_option( 'date_format' ) ), $new_title );
		}
		if ( strpos( $new_title, "%post_date%" ) !== false ){
			$new_title = str_replace( '%post_date%', get_the_date(), $new_title );
		}
		if ( strpos( $new_title, "%post_year%" ) !== false ){
			$new_title = str_replace( '%post_year%', get_the_date( 'Y' ), $new_title );
		}
		if ( strpos( $new_title, "%post_month%" ) !== false ){
			$new_title = str_replace( '%post_month%', get_the_date( 'F' ), $new_title );
		}

		$title = trim( $new_title );

		return $title;
	}

	/**
	 * @param $id
	 * @param $taxonomy
	 *
	 * @return array
	 */
	function get_all_terms( $id, $taxonomy ) {
		$keywords = array();
		$terms    = get_the_terms( $id, $taxonomy );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$keywords[] = $this->internationalize( $term->name );
			}
		}

		return $keywords;
	}

	/**
	 * @param $title
	 *
	 * @return string
	 */
	function paged_title( $title ) {
		// The page number if paged.
		global $paged;
		global $aioseop_options;
		// Simple tagging support.
		global $STagging;
		$page = get_query_var( 'page' );
		if ( $paged > $page ) {
			$page = $paged;
		}
		if ( is_paged() || ( isset( $STagging ) && $STagging->is_tag_view() && $paged ) || ( $page > 1 ) ) {
			$part = $this->internationalize( $aioseop_options['aiosp_paged_format'] );
			if ( isset( $part ) || ! empty( $part ) ) {
				$part = ' ' . trim( $part );
				$part = str_replace( '%page%', $page, $part );
				$this->log( "paged_title() [$title] [$part]" );
				$title .= $part;
			}
		}

		return $title;
	}

	/**
	 * @param $message
	 */
	function log( $message ) {
		if ( $this->do_log ) {
			@error_log( date( 'Y-m-d H:i:s' ) . ' ' . $message . "\n", 3, $this->log_file );
		}
	}

	/**
	 * @param $title
	 * @param string $category
	 * @param null $p
	 *
	 * @return string
	 */
	function apply_post_title_format( $title, $category = '', $p = null ) {
		if ( $p === null ) {
			global $post;
		} else {
			$post = $p;
		}
		$title_format = $this->get_post_title_format( 'post', $post );

		return $this->title_placeholder_helper( $title, $post, 'post', $title_format, $category );
	}

	/**
	 * @param string $title_type
	 * @param null $p
	 *
	 * @return bool|string
	 */
	function get_post_title_format( $title_type = 'post', $p = null ) {
		global $aioseop_options;
		if ( ( $title_type != 'post' ) && ( $title_type != 'archive' ) ) {
			return false;
		}
		$title_format = "%{$title_type}_title% | %blog_title%";
		if ( isset( $aioseop_options["aiosp_{$title_type}_title_format"] ) ) {
			$title_format = $aioseop_options["aiosp_{$title_type}_title_format"];
		}
		if ( ! empty( $aioseop_options['aiosp_enablecpost'] ) && ! empty( $aioseop_options['aiosp_cpostactive'] ) ) {
			$wp_post_types = $aioseop_options['aiosp_cpostactive'];
			if ( ! empty( $aioseop_options['aiosp_cposttitles'] ) ) {
				if ( ( ( $title_type == 'archive' ) && is_post_type_archive( $wp_post_types ) && $prefix = "aiosp_{$title_type}_" ) ||
				     ( ( $title_type == 'post' ) && $this->is_singular( $wp_post_types, $p ) && $prefix = 'aiosp_' )
				) {
					$post_type = get_post_type( $p );
					if ( ! empty( $aioseop_options["{$prefix}{$post_type}_title_format"] ) ) {
						$title_format = $aioseop_options["{$prefix}{$post_type}_title_format"];
					}
				}
			}
		}

		return $title_format;
	}

	/**
	 * @param array $post_types
	 * @param null $post
	 *
	 * @return bool
	 */
	function is_singular( $post_types = array(), $post = null ) {
		if ( ! empty( $post_types ) && is_object( $post ) ) {
			return in_array( $post->post_type, (array) $post_types );
		} else {
			return is_singular( $post_types );
		}
	}

	/**
	 * @return bool|null
	 */
	function is_static_posts_page() {
		static $is_posts_page = null;
		if ( $is_posts_page !== null ) {
			return $is_posts_page;
		}
		$post          = $this->get_queried_object();
		$is_posts_page = ( get_option( 'show_on_front' ) == 'page' && is_home() && ! empty( $post ) && $post->ID == get_option( 'page_for_posts' ) );

		return $is_posts_page;
	}

	/**
	 * @return bool|null
	 */
	function is_static_front_page() {
		if ( isset( $this->is_front_page ) && $this->is_front_page !== null ) {
			return $this->is_front_page;
		}
		$post                = $this->get_queried_object();
		$this->is_front_page = ( get_option( 'show_on_front' ) == 'page' && is_page() && ! empty( $post ) && $post->ID == get_option( 'page_on_front' ) );

		return $this->is_front_page;
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	function get_all_categories( $id = 0 ) {
		$keywords   = array();
		$categories = get_the_category( $id );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$keywords[] = $this->internationalize( $category->cat_name );
			}
		}

		return $keywords;
	}

	/**
	 * @param string $tax
	 *
	 * @return string
	 */
	function get_tax_title( $tax = '' ) {
		if ( AIOSEOPPRO ) {
			if ( empty( $this->meta_opts ) ) {
				$this->meta_opts = $this->get_current_options( array(), 'aiosp' );
			}
		}
		if ( empty( $tax ) ) {
			if ( is_category() ) {
				$tax = 'category';
			} else {
				$tax = get_query_var( 'taxonomy' );
			}
		}
		$name = $this->get_tax_name( $tax );
		$desc = $this->get_tax_desc( $tax );

		return $this->apply_tax_title_format( $name, $desc, $tax );
	}

	// Handle prev / next links.

	/**
	 *
	 * Gets taxonomy name.
	 *
	 * @param $tax
	 *
	 * @since 2.3.10 Remove option for capitalize categories. We still respect the option,
	 * and the default (true) or a legacy option in the db can be overridden with the new filter hook aioseop_capitalize_categories
     * @since 2.3.15 Remove category capitalization completely
	 *
	 * @return mixed|void
	 */
	function get_tax_name( $tax ) {
		global $aioseop_options;
		if ( AIOSEOPPRO ) {
			$opts = $this->meta_opts;
			if ( ! empty( $opts ) ) {
				$name = $opts['aiosp_title'];
			}
		} else {
			$name = '';
		}
		if ( empty( $name ) ) {
			$name = single_term_title( '', false );
		}

		return $this->internationalize( $name );
	}

	/**
	 * @param $tax
	 *
	 * @return mixed|void
	 */
	function get_tax_desc( $tax ) {
		if ( AIOSEOPPRO ) {
			$opts = $this->meta_opts;
			if ( ! empty( $opts ) ) {
				$desc = $opts['aiosp_description'];
			}
		} else {
			$desc = '';
		}
		if ( empty( $desc ) ) {
			$desc = term_description( '', $tax );
		}

		return $this->internationalize( $desc );
	}

	/**
	 * @param $category_name
	 * @param $category_description
	 * @param string $tax
	 *
	 * @return string
	 */
	function apply_tax_title_format( $category_name, $category_description, $tax = '' ) {
		if ( empty( $tax ) ) {
			$tax = get_query_var( 'taxonomy' );
		}
		$title_format = $this->get_tax_title_format( $tax );
		$title        = str_replace( '%taxonomy_title%', $category_name, $title_format );
		if ( strpos( $title, '%taxonomy_description%' ) !== false ) {
			$title = str_replace( '%taxonomy_description%', $category_description, $title );
		}
		if ( strpos( $title, '%category_title%' ) !== false ) {
			$title = str_replace( '%category_title%', $category_name, $title );
		}
		if ( strpos( $title, '%category_description%' ) !== false ) {
			$title = str_replace( '%category_description%', $category_description, $title );
		}
		if ( strpos( $title, '%blog_title%' ) !== false ) {
			$title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
		}
		if ( strpos( $title, '%blog_description%' ) !== false ) {
			$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
		}
		$title = wp_strip_all_tags( $title );

		return $this->paged_title( $title );
	}

	/**
	 * @param string $tax
	 *
	 * @return string
	 */
	function get_tax_title_format( $tax = '' ) {
		global $aioseop_options;
		if ( AIOSEOPPRO ) {
			$title_format = '%taxonomy_title% | %blog_title%';
			if ( is_category() ) {
				$title_format = $aioseop_options['aiosp_category_title_format'];
			} else {
				$taxes = $aioseop_options['aiosp_taxactive'];
				if ( empty( $tax ) ) {
					$tax = get_query_var( 'taxonomy' );
				}
				if ( ! empty( $aioseop_options["aiosp_{$tax}_tax_title_format"] ) ) {
					$title_format = $aioseop_options["aiosp_{$tax}_tax_title_format"];
				}
			}
			if ( empty( $title_format ) ) {
				$title_format = '%category_title% | %blog_title%';
			}
		} else {
			$title_format = '%category_title% | %blog_title%';
			if ( ! empty( $aioseop_options['aiosp_category_title_format'] ) ) {
				$title_format = $aioseop_options['aiosp_category_title_format'];
			}

			return $title_format;
		}

		return $title_format;
	}

	/**
	 * @param $title
	 * @param string $category
	 *
	 * @return string
	 */
	function apply_archive_title_format( $title, $category = '' ) {
		$title_format = $this->get_archive_title_format();
		$r_title      = array( '%blog_title%', '%blog_description%', '%archive_title%' );
		$d_title      = array(
			$this->internationalize( get_bloginfo( 'name' ) ),
			$this->internationalize( get_bloginfo( 'description' ) ),
			post_type_archive_title( '', false ),
		);
		$title        = trim( str_replace( $r_title, $d_title, $title_format ) );

		return $title;
	}

	/**
	 * @return bool|string
	 */
	function get_archive_title_format() {
		return $this->get_post_title_format( 'archive' );
	}

	/**
	 * @since 2.3.14 #932 Adds filter "aioseop_description", removes extra filtering.
	 *
	 * @param null $post
	 *
	 * @return mixed|string|void
	 */
	function get_main_description( $post = null ) {
		global $aioseop_options;
		$opts        = $this->meta_opts;
		$description = '';
		if ( is_author() && $this->show_page_description() ) {
			$description = $this->internationalize( get_the_author_meta( 'description' ) );
		} else if ( function_exists( 'wc_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = wc_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
			// $description = $this->get_post_description( $post );
			// $description = $this->apply_cf_fields( $description );
			if ( ! ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) ) ) {
				$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
			} else if ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				// $description = $this->get_aioseop_description( $post );
				$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
			} else if ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				$description = $this->get_aioseop_description( $post );
			}
		} else if ( is_front_page() ) {
			$description = $this->get_aioseop_description( $post );
		} else if ( is_single() || is_page() || is_attachment() || is_home() || $this->is_static_posts_page() ) {
			$description = $this->get_aioseop_description( $post );
		} else if ( ( is_category() || is_tag() || is_tax() ) && $this->show_page_description() ) {
			if ( ! empty( $opts ) && AIOSEOPPRO ) {
				$description = $opts['aiosp_description'];
			}
			if ( empty( $description ) ) {
				$description = term_description();
			}
			$description = $this->internationalize( $description );
		}
		if ( empty( $aioseop_options['aiosp_dont_truncate_descriptions'] ) ) {
			$description = $this->trim_excerpt_without_filters( $description );
		}

		return apply_filters( 'aioseop_description', $description );
	}

	/**
	 * @return bool
	 */
	function show_page_description() {
		global $aioseop_options;
		if ( ! empty( $aioseop_options['aiosp_hide_paginated_descriptions'] ) ) {
			$page = $this->get_page_number();
			if ( ! empty( $page ) && ( $page > 1 ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return mixed
	 */
	function get_page_number() {
		$page = get_query_var( 'page' );
		if ( empty( $page ) ) {
			$page = get_query_var( 'paged' );
		}

		return $page;
	}

	/**
	 * @param null $post
	 *
	 * @return mixed|string
	 */
	function get_aioseop_description( $post = null ) {
		global $aioseop_options;
		if ( null === $post ) {
			$post = $GLOBALS['post'];
		}
		$blog_page   = aiosp_common::get_blog_page();
		$description = '';
		if ( is_front_page() && empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
			$description = trim( $this->internationalize( $aioseop_options['aiosp_home_description'] ) );
		} elseif ( ! empty( $blog_page ) ) {
			$description = $this->get_post_description( $blog_page );
		}
		if ( empty( $description ) && is_object( $post ) && ! is_archive() && empty( $blog_page ) ) {
			$description = $this->get_post_description( $post );
		}
		$description = $this->apply_cf_fields( $description );

		return $description;
	}

	/**
	 * Gets post description.
	 * Auto-generates description if settings are ON.
	 *
	 * @since 2.3.13 #899 Fixes non breacking space, applies filter "aioseop_description".
	 * @since 2.3.14 #932 Removes filter "aioseop_description".
	 *
	 * @param object $post Post object.
	 *
	 * @return mixed|string
	 */
	function get_post_description( $post ) {
		global $aioseop_options;
		$description = '';
		if ( ! $this->show_page_description() ) {
			return '';
		}
		$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
		if ( ! empty( $post ) && post_password_required( $post ) ) {
			return $description;
		}
		if ( ! $description ) {
			if ( empty( $aioseop_options['aiosp_skip_excerpt'] ) ) {
				$description = $this->trim_excerpt_without_filters_full_length( $this->internationalize( $post->post_excerpt ) );
			}
			if ( ! $description && isset( $aioseop_options['aiosp_generate_descriptions'] ) && $aioseop_options['aiosp_generate_descriptions'] ) {
				$content = $post->post_content;
				if ( ! empty( $aioseop_options['aiosp_run_shortcodes'] ) ) {
					$content = do_shortcode( $content );
				}
				$content     = wp_strip_all_tags( $content );
				$description = $this->trim_excerpt_without_filters( $this->internationalize( $content ) );
			}
		}

		return $description;
	}

	/**
	 * @since 2.3.15 Brackets not longer replaced from filters.
	 *
	 * @param $text
	 *
	 * @return string
	 */
	function trim_excerpt_without_filters_full_length( $text ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text );
		$text = wp_strip_all_tags( $text );

		return trim( $text );
	}

	/**
	 * @since 2.3.15 Brackets not longer replaced from filters.
	 *
	 * @param $text
	 * @param int $max
	 *
	 * @return string
	 */
	function trim_excerpt_without_filters( $text, $max = 0 ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text );
		$text = wp_strip_all_tags( $text );
		// Treat other common word-break characters like a space.
		$text2 = preg_replace( '/[,._\-=+&!\?;:*]/s', ' ', $text );
		if ( ! $max ) {
			$max = $this->maximum_description_length;
		}
		$max_orig = $max;
		$len      = $this->strlen( $text2 );
		if ( $max < $len ) {
			if ( function_exists( 'mb_strrpos' ) ) {
				$pos = mb_strrpos( $text2, ' ', - ( $len - $max ) );
				if ( false === $pos ) {
					$pos = $max;
				}
				if ( $pos > $this->minimum_description_length ) {
					$max = $pos;
				} else {
					$max = $this->minimum_description_length;
				}
			} else {
				while ( ' ' != $text2[ $max ] && $max > $this->minimum_description_length ) {
					$max --;
				}
			}

			// Probably no valid chars to break on?
			if ( $len > $max_orig && $max < intval( $max_orig / 2 ) ) {
				$max = $max_orig;
			}
		}
		$text = $this->substr( $text, 0, $max );

		return trim( $text );
	}

	/**
	 * @param $query
	 * @param bool $show_page
	 *
	 * @return bool|false|string
	 */
	function aiosp_mrt_get_url( $query, $show_page = true ) {
		if ( $query->is_404 || $query->is_search ) {
			return false;
		}
		$link    = '';
		$haspost = count( $query->posts ) > 0;
		if ( get_query_var( 'm' ) ) {
			$m = preg_replace( '/[^0-9]/', '', get_query_var( 'm' ) );
			switch ( $this->strlen( $m ) ) {
				case 4:
					$link = get_year_link( $m );
					break;
				case 6:
					$link = get_month_link( $this->substr( $m, 0, 4 ), $this->substr( $m, 4, 2 ) );
					break;
				case 8:
					$link = get_day_link( $this->substr( $m, 0, 4 ), $this->substr( $m, 4, 2 ), $this->substr( $m, 6, 2 ) );
					break;
				default:
					return false;
			}
		} elseif ( $query->is_home && ( get_option( 'show_on_front' ) == 'page' ) && ( $pageid = get_option( 'page_for_posts' ) ) ) {
			$link = get_permalink( $pageid );
		} elseif ( is_front_page() || ( $query->is_home && ( get_option( 'show_on_front' ) != 'page' || ! get_option( 'page_for_posts' ) ) ) ) {
			if ( function_exists( 'icl_get_home_url' ) ) {
				$link = icl_get_home_url();
			} else {
				$link = trailingslashit( home_url() );
			}
		} elseif ( ( $query->is_single || $query->is_page ) && $haspost ) {
			$post = $query->posts[0];
			$link = get_permalink( $post->ID );
		} elseif ( $query->is_author && $haspost ) {
			$author = get_userdata( get_query_var( 'author' ) );
			if ( false === $author ) {
				return false;
			}
			$link = get_author_posts_url( $author->ID, $author->user_nicename );
		} elseif ( $query->is_category && $haspost ) {
			$link = get_category_link( get_query_var( 'cat' ) );
		} elseif ( $query->is_tag && $haspost ) {
			$tag = get_term_by( 'slug', get_query_var( 'tag' ), 'post_tag' );
			if ( ! empty( $tag->term_id ) ) {
				$link = get_tag_link( $tag->term_id );
			}
		} elseif ( $query->is_day && $haspost ) {
			$link = get_day_link( get_query_var( 'year' ),
				get_query_var( 'monthnum' ),
				get_query_var( 'day' ) );
		} elseif ( $query->is_month && $haspost ) {
			$link = get_month_link( get_query_var( 'year' ),
				get_query_var( 'monthnum' ) );
		} elseif ( $query->is_year && $haspost ) {
			$link = get_year_link( get_query_var( 'year' ) );
		} elseif ( $query->is_tax && $haspost ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$term     = get_query_var( 'term' );
			if ( ! empty( $term ) ) {
				$link = get_term_link( $term, $taxonomy );
			}
		} elseif ( $query->is_archive && function_exists( 'get_post_type_archive_link' ) && ( $post_type = get_query_var( 'post_type' ) ) ) {
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$link = get_post_type_archive_link( $post_type );
		} else {
			return false;
		}
		if ( empty( $link ) || ! is_string( $link ) ) {
			return false;
		}
		if ( apply_filters( 'aioseop_canonical_url_pagination', $show_page ) ) {
			$link = $this->get_paged( $link );
		}

		return $link;
	}

	/**
	 * @param $link
	 *
	 * @return string
	 */
	function get_paged( $link ) {
		global $wp_rewrite;
		$page      = $this->get_page_number();
		$page_name = 'page';
		if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
			$page_name = $wp_rewrite->pagination_base;
		}
		if ( ! empty( $page ) && $page > 1 ) {
			if ( $page == get_query_var( 'page' ) ) {
				$link = trailingslashit( $link ) . "$page";
			} else {
				$link = trailingslashit( $link ) . trailingslashit( $page_name ) . $page;
			}
			$link = user_trailingslashit( $link, 'paged' );
		}

		return $link;
	}

	/**
	 * @return comma|string
	 */
	function get_main_keywords() {
		global $aioseop_options;
		global $aioseop_keywords;
		global $post;
		$opts = $this->meta_opts;
		if ( ( is_front_page() && $aioseop_options['aiosp_home_keywords'] && ! $this->is_static_posts_page() ) || $this->is_static_front_page() ) {
			if ( ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				$keywords = $this->get_all_keywords();
			} else {
				$keywords = trim( $this->internationalize( $aioseop_options['aiosp_home_keywords'] ) );
			}
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && $this->is_static_posts_page() ) {
			$keywords = stripslashes( $this->internationalize( $opts['aiosp_keywords'] ) ); // And if option = use page set keywords instead of keywords from recent posts.
		} elseif ( ( $blog_page = aiosp_common::get_blog_page( $post ) ) && empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) ) {
			$keywords = stripslashes( $this->internationalize( get_post_meta( $blog_page->ID, '_aioseop_keywords', true ) ) );
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && ( is_archive() || is_post_type_archive() ) ) {
			$keywords = '';
		} else {
			$keywords = $this->get_all_keywords();
		}

		return $keywords;
	}

	/**
	 * @return comma-separated list of unique keywords
	 */
	function get_all_keywords() {
		global $posts;
		global $aioseop_options;
		if ( is_404() ) {
			return null;
		}
		// If we are on synthetic pages.
		if ( ! is_home() && ! is_page() && ! is_single() && ! $this->is_static_front_page() && ! $this->is_static_posts_page() && ! is_archive() && ! is_post_type_archive() && ! is_category() && ! is_tag() && ! is_tax() ) {
			return null;
		}
		$keywords = array();
		$opts     = $this->meta_opts;
		if ( ! empty( $opts['aiosp_keywords'] ) ) {
			$traverse = $this->keyword_string_to_list( $this->internationalize( $opts['aiosp_keywords'] ) );
			if ( ! empty( $traverse ) ) {
				foreach ( $traverse as $keyword ) {
					$keywords[] = $keyword;
				}
			}
		}
		if ( empty( $posts ) ) {
			global $post;
			$post_arr = array( $post );
		} else {
			$post_arr = $posts;
		}
		if ( is_array( $post_arr ) ) {
			$postcount = count( $post_arr );
			foreach ( $post_arr as $p ) {
				if ( $p ) {
					$id = $p->ID;
					if ( 1 == $postcount || ! empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) ) {
						// Custom field keywords.
						$keywords_i = null;
						$keywords_i = stripslashes( $this->internationalize( get_post_meta( $id, '_aioseop_keywords', true ) ) );
						if ( is_attachment() ) {
							$id = $p->post_parent;
							if ( empty( $keywords_i ) ) {
								$keywords_i = stripslashes( $this->internationalize( get_post_meta( $id, '_aioseop_keywords', true ) ) );
							}
						}
						$traverse = $this->keyword_string_to_list( $keywords_i );
						if ( ! empty( $traverse ) ) {
							foreach ( $traverse as $keyword ) {
								$keywords[] = $keyword;
							}
						}
					}

					if ( ! empty( $aioseop_options['aiosp_use_tags_as_keywords'] ) ) {
						$keywords = array_merge( $keywords, $this->get_all_tags( $id ) );
					}
					// Autometa.
					$autometa = stripslashes( get_post_meta( $id, 'autometa', true ) );
					if ( isset( $autometa ) && ! empty( $autometa ) ) {
						$autometa_array = explode( ' ', $autometa );
						foreach ( $autometa_array as $e ) {
							$keywords[] = $e;
						}
					}

					if ( isset( $aioseop_options['aiosp_use_categories'] ) && $aioseop_options['aiosp_use_categories'] && ! is_page() ) {
						$keywords = array_merge( $keywords, $this->get_all_categories( $id ) );
					}
				}
			}
		}

		return $this->get_unique_keywords( $keywords );
	}

	/**
	 * @param $keywords
	 *
	 * @return array
	 */
	function keyword_string_to_list( $keywords ) {
		$traverse   = array();
		$keywords_i = str_replace( '"', '', $keywords );
		if ( isset( $keywords_i ) && ! empty( $keywords_i ) ) {
			$traverse = explode( ',', $keywords_i );
		}

		return $traverse;
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	function get_all_tags( $id = 0 ) {
		$keywords = array();
		$tags     = get_the_tags( $id );
		if ( ! empty( $tags ) && is_array( $tags ) ) {
			foreach ( $tags as $tag ) {
				$keywords[] = $this->internationalize( $tag->name );
			}
		}
		// Ultimate Tag Warrior integration.
		global $utw;
		if ( $utw ) {
			$tags = $utw->GetTagsForPost( $p );
			if ( is_array( $tags ) ) {
				foreach ( $tags as $tag ) {
					$tag        = $tag->tag;
					$tag        = str_replace( '_', ' ', $tag );
					$tag        = str_replace( '-', ' ', $tag );
					$tag        = stripslashes( $tag );
					$keywords[] = $tag;
				}
			}
		}

		return $keywords;
	}

	/**
	 * @param $keywords
	 *
	 * @return string
	 */
	function get_unique_keywords( $keywords ) {
		return implode( ',', $this->clean_keyword_list( $keywords ) );
	}

	/**
	 * @param $keywords
	 *
	 * @return array
	 */
	function clean_keyword_list( $keywords ) {
		$small_keywords = array();
		if ( ! is_array( $keywords ) ) {
			$keywords = $this->keyword_string_to_list( $keywords );
		}
		if ( ! empty( $keywords ) ) {
			foreach ( $keywords as $word ) {
				$small_keywords[] = trim( $this->strtolower( $word ) );
			}
		}

		return array_unique( $small_keywords );
	}

	/**
	 * @param $term_id
	 * @param $new_term_id
	 * @param string $term_taxonomy_id
	 * @param string $taxonomy
	 */
	function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id = '', $taxonomy = '' ) {
		$terms = $this->get_all_term_data( $term_id );
		if ( ! empty( $terms ) ) {
			$new_terms = $this->get_all_term_data( $new_term_id );
			if ( empty( $new_terms ) ) {
				foreach ( $terms as $k => $v ) {
					add_term_meta( $new_term_id, $k, $v, true );
				}
				add_term_meta( $term_id, '_aioseop_term_was_split', true, true );
			}
		}
	}

	/**
	 * @param $term_id
	 *
	 * @return array
	 */
	function get_all_term_data( $term_id ) {
		$terms   = array();
		$optlist = array(
			'keywords',
			'description',
			'title',
			'custom_link',
			'sitemap_exclude',
			'disable',
			'disable_analytics',
			'noindex',
			'nofollow',
		);
		foreach ( $optlist as $f ) {
			$meta = get_term_meta( $term_id, '_aioseop_' . $f, true );
			if ( ! empty( $meta ) ) {
				$terms[ '_aioseop_' . $f ] = $meta;
			}
		}

		return $terms;
	}

	function add_page_icon() {
		wp_enqueue_script( 'wp-pointer', false, array( 'jquery' ) );
		wp_enqueue_style( 'wp-pointer' );
		//$this->add_admin_pointers();

		wp_enqueue_style( 'aiosp_admin_style', AIOSEOP_PLUGIN_URL . 'css/aiosp_admin.css', array(), AIOSEOP_VERSION );
		?>
		<script>
			function aioseop_show_pointer(handle, value) {
				if (typeof( jQuery ) != 'undefined') {
					var p_edge = 'bottom';
					var p_align = 'center';
					if (typeof( jQuery(value.pointer_target).pointer) != 'undefined') {
						if (typeof( value.pointer_edge ) != 'undefined') p_edge = value.pointer_edge;
						if (typeof( value.pointer_align ) != 'undefined') p_align = value.pointer_align;
						jQuery(value.pointer_target).pointer({
							content: value.pointer_text,
							position: {
								edge: p_edge,
								align: p_align
							},
							close: function () {
								jQuery.post(ajaxurl, {
									pointer: handle,
									action: 'dismiss-wp-pointer'
								});
							}
						}).pointer('open');
					}
				}
			}
			<?php
			if ( ! empty( $this->pointers ) ) {
			?>
			if (typeof( jQuery ) != 'undefined') {
				jQuery(document).ready(function () {
					var admin_pointer;
					var admin_index;
					<?php
					foreach ( $this->pointers as $k => $p )
						{if ( ! empty( $p['pointer_scope'] ) && ( 'global' === $p['pointer_scope'] ) ) {
						?>admin_index = "<?php echo esc_attr( $k ); ?>";
					admin_pointer = <?php echo json_encode( $p ); ?>;
					aioseop_show_pointer(admin_index, admin_pointer);
					<?php
					}
					}	?>
				});
			}
			<?php	}	?>
		</script>
		<?php
	}

	function add_admin_pointers() {

		$pro = '';
		if ( AIOSEOPPRO ) {
			$pro = '-pro';
		}

		$this->pointers['aioseop_menu_2640'] = array(
			'pointer_target' => "#toplevel_page_all-in-one-seo-pack$pro-aioseop_class",
			'pointer_text'   => '<h3>' . __( 'Review Your Settings', 'all-in-one-seo-pack' )
			                    . '</h3><p>' . sprintf( __( 'Welcome to version %s. Thank you for running the latest and greatest All in One SEO Pack Pro ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION ) . '</p>',
			'pointer_edge'   => 'top',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'global',
		);

		$this->pointers['aioseop_menu_2361']   = array(
			'pointer_target' => '#aioseop_top_button',
			'pointer_text'   => '<h3>' . sprintf( __( 'Welcome to Version %s!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
			                    . '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack Pro ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ) . '</p>',
			'pointer_edge'   => 'top',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'global',
		);
		$this->pointers['aioseop_welcome_230'] = array(
			'pointer_target' => '#aioseop_top_button',
			'pointer_text'   => '<h3>' . sprintf( __( 'Review Your Settings', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
			                    . '</h3><p>' . __( 'New in 2.4: Improved support for taxonomies, Woocommerce and massive performance improvements under the hood! Please review your settings on each options page!', 'all-in-one-seo-pack' ) . '</p>',
			'pointer_edge'   => 'bottom',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'local',
		);
		$this->filter_pointers();

		$this->pointers['aioseop_menu_2205']      = array(
			'pointer_target' => '#toplevel_page_all-in-one-seo-pack-aioseop_class',
			'pointer_text'   => '<h3>' . sprintf( __( 'Welcome to Version %s!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
			                    . '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ) . '</p>',
			'pointer_edge'   => 'top',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'global',
		);
		$this->pointers['aioseop_welcome_220534'] = array(
			'pointer_target' => '#aioseop_top_button',
			'pointer_text'   => '<h3>' . sprintf( __( 'Review Your Settings', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
			                    . '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack ever! New since 2.2: Control who accesses your site with the new Robots.txt Editor and File Editor modules!  Enable them from the Feature Manager.  Remember to review your settings, we have added some new ones!', 'all-in-one-seo-pack' ) . '</p>',
			'pointer_edge'   => 'bottom',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'local',
		);
		$this->filter_pointers();

	}

	function add_page_hooks() {

		global $aioseop_options;

		$post_objs  = get_post_types( '', 'objects' );
		$pt         = array_keys( $post_objs );
		$rempost    = array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ); // Don't show these built-in types as options for CPT SEO.
		$pt         = array_diff( $pt, $rempost );
		$post_types = array();

		$aiosp_enablecpost = '';
		if ( isset( $_REQUEST['aiosp_enablecpost'] ) ) {
			$aiosp_enablecpost = $_REQUEST['aiosp_enablecpost'];
		}

		foreach ( $pt as $p ) {
			if ( ! empty( $post_objs[ $p ]->label ) ) {
				if ( $post_objs[ $p ]->_builtin && empty( $aioseop_options['aiosp_enablecpost'] ) ) {
					$post_types[ $p ] = $post_objs[ $p ]->label;
				} elseif ( ! empty( $aioseop_options['aiosp_enablecpost'] ) || $aiosp_enablecpost == 'on' ) {
					$post_types[ $p ] = $post_objs[ $p ]->label;
				}
			} else {
				$post_types[ $p ] = $p;
			}
		}

		foreach ( $pt as $p ) {
			if ( ! empty( $post_objs[ $p ]->label ) ) {
				$all_post_types[ $p ] = $post_objs[ $p ]->label;
			}
		}

		$taxes     = get_taxonomies( '', 'objects' );
		$tx        = array_keys( $taxes );
		$remtax    = array( 'nav_menu', 'link_category', 'post_format' );
		$tx        = array_diff( $tx, $remtax );
		$tax_types = array();
		foreach ( $tx as $t ) {
			if ( ! empty( $taxes[ $t ]->label ) ) {
				$tax_types[ $t ] = $taxes[ $t ]->label;
			} else {
				$taxes[ $t ] = $t;
			}
		}

		$this->default_options['posttypecolumns']['initial_options'] = $post_types;
		$this->default_options['cpostactive']['initial_options']     = $all_post_types;
		$this->default_options['cpostnoindex']['initial_options']    = $post_types;
		$this->default_options['cpostnofollow']['initial_options']   = $post_types;
		if ( AIOSEOPPRO ) {
			$this->default_options['taxactive']['initial_options'] = $tax_types;
		}
		$this->default_options['google_author_location']['initial_options'] = $post_types;
		$this->default_options['google_author_location']['initial_options'] = array_merge( array( 'front' => __( 'Front Page', 'all-in-one-seo-pack' ) ), $post_types, array( 'all' => __( 'Everywhere Else', 'all-in-one-seo-pack' ) ) );
		$this->default_options['google_author_location']['default']         = array_keys( $this->default_options['google_author_location']['initial_options'] );

		foreach ( $post_types as $p => $pt ) {
			$field = $p . '_title_format';
			$name  = $post_objs[ $p ]->labels->singular_name;
			if ( ! isset( $this->default_options[ $field ] ) ) {
				$this->default_options[ $field ] = array(
					'name'     => "$name " . __( 'Title Format:', 'all-in-one-seo-pack' ) . "<br />($p)",
					'type'     => 'text',
					'default'  => '%post_title% | %blog_title%',
					'condshow' => array(
						'aiosp_rewrite_titles'  => 1,
						'aiosp_enablecpost'     => 'on',
						'aiosp_cpostadvanced'   => 'on',
						'aiosp_cposttitles'     => 'on',
						'aiosp_cpostactive\[\]' => $p,
					),
				);
				$this->help_text[ $field ]       = __( 'The following macros are supported:', 'all-in-one-seo-pack' )
				                                   . '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				                                   __( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				                                   __( '%post_title% - The original title of the post.', 'all-in-one-seo-pack' ) . '</li><li>';
				$taxes                           = get_object_taxonomies( $p, 'objects' );
				if ( ! empty( $taxes ) ) {
					foreach ( $taxes as $n => $t ) {
						$this->help_text[ $field ] .= sprintf( __( "%%tax_%s%% - This post's associated %s taxonomy title", 'all-in-one-seo-pack' ), $n, $t->label ) . '</li><li>';
					}
				}
				$this->help_text[ $field ]        .=
					__( "%post_author_login% - This post's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_nicename% - This post's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_firstname% - This post's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_lastname% - This post's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li>' .
					__( "%current_date% - The current date (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
                    __( "%post_date% - The date the post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
                    __( "%post_year% - The year the post was published (localized)", 'all-in-one-seo-pack' ) . '</li><li>' .
                    __( "%post_month% - The month the post was published (localized)", 'all-in-one-seo-pack' ) . '</li>' .
                    '</ul>' .
					'</ul>';
				$this->help_anchors[ $field ]     = '#custom-titles';
				$this->layout['cpt']['options'][] = $field;
			}
		}
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$role_names = $wp_roles->get_names();
		ksort( $role_names );
		$this->default_options['ga_exclude_users']['initial_options'] = $role_names;

		unset( $tax_types['category'] );
		unset( $tax_types['post_tag'] );
		$this->default_options['tax_noindex']['initial_options'] = $tax_types;
		if ( empty( $tax_types ) ) {
			unset( $this->default_options['tax_noindex'] );
		}

		if ( AIOSEOPPRO ) {
			foreach ( $tax_types as $p => $pt ) {
				$field = $p . '_tax_title_format';
				$name  = $pt;
				if ( ! isset( $this->default_options[ $field ] ) ) {
					$this->default_options[ $field ]  = array(
						'name'     => "$name " . __( 'Taxonomy Title Format:', 'all-in-one-seo-pack' ),
						'type'     => 'text',
						'default'  => '%taxonomy_title% | %blog_title%',
						'condshow' => array(
							'aiosp_rewrite_titles' => 1,
							'aiosp_enablecpost'    => 'on',
							'aiosp_cpostadvanced'  => 'on',
							'aiosp_cposttitles'    => 'on',
							'aiosp_taxactive\[\]'  => $p,
						),
					);
					$this->help_text[ $field ]        = __( 'The following macros are supported:', 'all-in-one-seo-pack' ) .
					                                    '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
					                                    __( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
					                                    __( '%taxonomy_title% - The original title of the taxonomy', 'all-in-one-seo-pack' ) . '</li><li>' .
					                                    __( '%taxonomy_description% - The description of the taxonomy', 'all-in-one-seo-pack' ) . '</li></ul>';
					$this->help_anchors[ $field ]     = '#custom-titles';
					$this->layout['cpt']['options'][] = $field;
				}
			}
		}
		$this->setting_options();
		$this->add_help_text_links();

		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;
			add_action( "{$this->prefix}update_options", array(
				$aioseop_update_checker,
				'license_change_check',
			), 10, 2 );
			add_action( "{$this->prefix}settings_update", array( $aioseop_update_checker, 'update_check' ), 10, 2 );
		}

		add_filter( "{$this->prefix}display_options", array( $this, 'filter_options' ), 10, 2 );
		parent::add_page_hooks();
	}

	function settings_page_init() {
		add_filter( "{$this->prefix}submit_options", array( $this, 'filter_submit' ) );
	}

	function enqueue_scripts() {
		add_filter( "{$this->prefix}display_settings", array( $this, 'filter_settings' ), 10, 3 );
		add_filter( "{$this->prefix}display_options", array( $this, 'filter_options' ), 10, 2 );
		parent::enqueue_scripts();
	}

	/**
	 * @param $submit
	 *
	 * @return mixed
	 */
	function filter_submit( $submit ) {
		$submit['Submit_Default']['value'] = __( 'Reset General Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;';
		$submit['Submit_All_Default']      = array(
			'type'  => 'submit',
			'class' => 'button-secondary',
			'value' => __( 'Reset ALL Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;',
		);

		return $submit;
	}

	/**
	 * Handle resetting options to defaults, but preserve the license key if pro.
	 *
	 * @param null $location
	 * @param bool $delete
	 */
	function reset_options( $location = null, $delete = false ) {
		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;
		}
		if ( $delete === true ) {

			if ( AIOSEOPPRO ) {
				$license_key = '';
				if ( isset( $this->options ) && isset( $this->options['aiosp_license_key'] ) ) {
					$license_key = $this->options['aiosp_license_key'];
				}
			}

			$this->delete_class_option( $delete );

			if ( AIOSEOPPRO ) {
				$this->options = array( 'aiosp_license_key' => $license_key );
			} else {
				$this->options = array();
			}
		}
		$default_options = $this->default_options( $location );

		if ( AIOSEOPPRO ) {
			foreach ( $default_options as $k => $v ) {
				if ( $k != 'aiosp_license_key' ) {
					$this->options[ $k ] = $v;
				}
			}
			$aioseop_update_checker->license_key = $this->options['aiosp_license_key'];
		} else {
			foreach ( $default_options as $k => $v ) {
				$this->options[ $k ] = $v;
			}
		}
		$this->update_class_option( $this->options );
	}

	/**
	 * @param $settings
	 * @param $location
	 * @param $current
	 *
	 * @return mixed
	 */
	function filter_settings( $settings, $location, $current ) {
		if ( $location == null ) {
			$prefix = $this->prefix;

			foreach ( array( 'seopostcol', 'seocustptcol', 'debug_info', 'max_words_excerpt' ) as $opt ) {
				unset( $settings["{$prefix}$opt"] );
			}

			if ( ! class_exists( 'DOMDocument' ) ) {
				unset( $settings['{prefix}google_connect'] );
			}
			if ( AIOSEOPPRO ) {
				if ( ! empty( $this->options['aiosp_license_key'] ) ) {
					$settings['aiosp_license_key']['type'] = 'password';
					$settings['aiosp_license_key']['size'] = 38;
				}
			}
		} elseif ( $location == 'aiosp' ) {
			global $post, $aioseop_sitemap;
			$prefix = $this->get_prefix( $location ) . $location . '_';
			if ( ! empty( $post ) ) {
				$post_type = get_post_type( $post );
				if ( ! empty( $this->options['aiosp_cpostnoindex'] ) && in_array( $post_type, $this->options['aiosp_cpostnoindex'] ) ) {
					$settings["{$prefix}noindex"]['type']            = 'select';
					$settings["{$prefix}noindex"]['initial_options'] = array(
						''    => __( 'Default - noindex', 'all-in-one-seo-pack' ),
						'off' => __( 'index', 'all-in-one-seo-pack' ),
						'on'  => __( 'noindex', 'all-in-one-seo-pack' ),
					);
				}
				if ( ! empty( $this->options['aiosp_cpostnofollow'] ) && in_array( $post_type, $this->options['aiosp_cpostnofollow'] ) ) {
					$settings["{$prefix}nofollow"]['type']            = 'select';
					$settings["{$prefix}nofollow"]['initial_options'] = array(
						''    => __( 'Default - nofollow', 'all-in-one-seo-pack' ),
						'off' => __( 'follow', 'all-in-one-seo-pack' ),
						'on'  => __( 'nofollow', 'all-in-one-seo-pack' ),
					);
				}

				global $post;
				$info = $this->get_page_snippet_info();
				extract( $info );
				$settings["{$prefix}title"]['placeholder']       = $title;
				$settings["{$prefix}description"]['placeholder'] = $description;
				$settings["{$prefix}keywords"]['placeholder']    = $keywords;
			}

			if ( ! AIOSEOPPRO ) {
				if ( ! current_user_can( 'update_plugins' ) ) {
					unset( $settings["{$prefix}upgrade"] );
				}
			}

			if ( ! is_object( $aioseop_sitemap ) ) {
				unset( $settings['aiosp_sitemap_exclude'] );
			}

			if ( ! empty( $this->options[ $this->prefix . 'togglekeywords' ] ) ) {
				unset( $settings["{$prefix}keywords"] );
				unset( $settings["{$prefix}togglekeywords"] );
			} elseif ( ! empty( $current["{$prefix}togglekeywords"] ) ) {
				unset( $settings["{$prefix}keywords"] );
			}
			if ( empty( $this->options['aiosp_can'] ) || empty( $this->options['aiosp_customize_canonical_links'] ) ) {
				unset( $settings["{$prefix}custom_link"] );
			}
		}

		return $settings;
	}

	/**
	 * @param $options
	 * @param $location
	 *
	 * @return mixed
	 */
	function filter_options( $options, $location ) {
		if ( $location == 'aiosp' ) {
			global $post;
			if ( ! empty( $post ) ) {
				$prefix    = $this->prefix;
				$post_type = get_post_type( $post );
				foreach ( array( 'noindex', 'nofollow' ) as $no ) {
					if ( empty( $this->options[ 'aiosp_cpost' . $no ] ) || ( ! in_array( $post_type, $this->options[ 'aiosp_cpost' . $no ] ) ) ) {
						if ( isset( $options["{$prefix}{$no}"] ) && ( $options["{$prefix}{$no}"] != 'on' ) ) {
							unset( $options["{$prefix}{$no}"] );
						}
					}
				}
			}
		}
		if ( $location == null ) {
			$prefix = $this->prefix;
			if ( isset( $options["{$prefix}rewrite_titles"] ) && ( ! empty( $options["{$prefix}rewrite_titles"] ) ) ) {
				$options["{$prefix}rewrite_titles"] = 1;
			}
			if ( isset( $options["{$prefix}enablecpost"] ) && ( $options["{$prefix}enablecpost"] === '' ) ) {
				$options["{$prefix}enablecpost"] = 0;
			}
			if ( isset( $options["{$prefix}use_original_title"] ) && ( $options["{$prefix}use_original_title"] === '' ) ) {
				$options["{$prefix}use_original_title"] = 0;
			}
		}

		return $options;
	}

	function template_redirect() {
		global $aioseop_options;

		$post = $this->get_queried_object();

		if ( ! $this->is_page_included() ) {
			return;
		}

		if ( ! empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
			$force_rewrites = 1;
			if ( isset( $aioseop_options['aiosp_force_rewrites'] ) ) {
				$force_rewrites = $aioseop_options['aiosp_force_rewrites'];
			}
			if ( $force_rewrites ) {
				ob_start( array( $this, 'output_callback_for_title' ) );
			} else {
				add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );
			}
		}
	}

	/**
	 * @return bool
	 */
	function is_page_included() {
		global $aioseop_options;
		if ( is_feed() ) {
			return false;
		}
		if ( aioseop_mrt_exclude_this_page() ) {
			return false;
		}
		$post      = $this->get_queried_object();
		$post_type = '';
		if ( ! empty( $post ) && ! empty( $post->post_type ) ) {
			$post_type = $post->post_type;
		}
		if ( empty( $aioseop_options['aiosp_enablecpost'] ) ) {
			$wp_post_types = get_post_types( array( '_builtin' => true ) ); // Don't display meta if SEO isn't enabled on custom post types -- pdb.
			if ( is_singular() && ! in_array( $post_type, $wp_post_types ) && ! is_front_page() ) {
				return false;
			}
		} else {
			$wp_post_types = $aioseop_options['aiosp_cpostactive'];
			if ( empty( $wp_post_types ) ) {
				$wp_post_types = array();
			}
			if ( AIOSEOPPRO ) {
				if ( is_tax() ) {
					if ( empty( $aioseop_options['aiosp_taxactive'] ) || ! is_tax( $aioseop_options['aiosp_taxactive'] ) ) {
						return false;
					}
				} elseif ( is_category() ) {
					if ( empty( $aioseop_options['aiosp_taxactive'] ) || ! in_array( 'category', $aioseop_options['aiosp_taxactive'] ) ) {
						return false;
					}
				} elseif ( is_tag() ) {
					if ( empty( $aioseop_options['aiosp_taxactive'] ) || ! in_array( 'post_tag', $aioseop_options['aiosp_taxactive'] ) ) {
						return false;
					}
				} else if ( ! in_array( $post_type, $wp_post_types ) && ! is_front_page() && ! is_post_type_archive( $wp_post_types ) && ! is_404() ) {
					return false;
				}
			} else {
				if ( is_singular() && ! in_array( $post_type, $wp_post_types ) && ! is_front_page() ) {
					return false;
				}
				if ( is_post_type_archive() && ! is_post_type_archive( $wp_post_types ) ) {
					return false;
				}
			}
		}

		$this->meta_opts = $this->get_current_options( array(), 'aiosp' );

		$aiosp_disable = $aiosp_disable_analytics = false;

		if ( ! empty( $this->meta_opts ) ) {
			if ( isset( $this->meta_opts['aiosp_disable'] ) ) {
				$aiosp_disable = $this->meta_opts['aiosp_disable'];
			}
			if ( isset( $this->meta_opts['aiosp_disable_analytics'] ) ) {
				$aiosp_disable_analytics = $this->meta_opts['aiosp_disable_analytics'];
			}
		}

		$aiosp_disable = apply_filters( 'aiosp_disable', $aiosp_disable ); // API filter to disable AIOSEOP.

		if ( $aiosp_disable ) {
			if ( ! $aiosp_disable_analytics ) {
				if ( aioseop_option_isset( 'aiosp_google_analytics_id' ) ) {
					remove_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
					add_action( 'wp_head', array( $this, 'aiosp_google_analytics' ) );
				}
			}

			return false;
		}

		if ( ! empty( $this->meta_opts ) && $this->meta_opts['aiosp_disable'] == true ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $content
	 *
	 * @return mixed|string
	 */
	function output_callback_for_title( $content ) {
		return $this->rewrite_title( $content );
	}

	/**
	 * Used for forcing title rewrites.
	 *
	 * @param $header
	 *
	 * @return mixed|string
	 */
	function rewrite_title( $header ) {

		global $wp_query;
		if ( ! $wp_query ) {
			$header .= "<!-- AIOSEOP no wp_query found! -->\n";
			return $header;
		}

	    // Check if we're in the main query to support bad themes and plugins.
		$old_wp_query = null;
		if ( ! $wp_query->is_main_query() ) {
			$old_wp_query = $wp_query;
			wp_reset_query();
		}

		$title = $this->wp_title();
		if ( ! empty( $title ) ) {
			$header = $this->replace_title( $header, $title );
		}

		if ( ! empty( $old_wp_query ) ) {
			$GLOBALS['wp_query'] = $old_wp_query;
			// Change the query back after we've finished.
			unset( $old_wp_query );
		}
		return $header;
	}

	/**
	 * @param $content
	 * @param $title
	 *
	 * @return mixed
	 */
	function replace_title( $content, $title ) {
		// We can probably improve this... I'm not sure half of this is even being used.
		$title             = trim( strip_tags( $title ) );
		$title_tag_start   = '<title';
		$title_tag_end     = '</title';
		$start             = $this->strpos( $content, $title_tag_start );
		$end               = $this->strpos( $content, $title_tag_end );
		$this->title_start = $start;
		$this->title_end   = $end;
		$this->orig_title  = $title;

		return preg_replace( '/<title([^>]*?)\s*>([^<]*?)<\/title\s*>/is', '<title\\1>' . preg_replace( '/(\$|\\\\)(?=\d)/', '\\\\\1', strip_tags( $title ) ) . '</title>', $content, 1 );
	}

	/**
	 * Adds wordpress hooks.
	 *
	 * @since 2.3.13 #899 Adds filter:aioseop_description.
	 * @since 2.3.14 #593 Adds filter:aioseop_title.
	 */
	function add_hooks() {
		global $aioseop_options, $aioseop_update_checker;

		// MOVED TO MAIN PLUGIN FILE IN ORDER TO FIRE SOONS
		//$role = get_role( 'administrator' );
		//if ( is_object( $role ) ) {
		//	$role->add_cap( 'aiosp_manage_seo' );
		//}

		aioseop_update_settings_check();
		add_filter( 'user_contactmethods', 'aioseop_add_contactmethods' );
		if ( is_user_logged_in() && is_admin_bar_showing() && current_user_can( 'aiosp_manage_seo' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );
		}

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'add_page_icon' ) );
			add_action( 'admin_init', 'aioseop_addmycolumns', 1 );
			add_action( 'admin_init', 'aioseop_handle_ignore_notice' );
			if ( AIOSEOPPRO ) {
				if ( current_user_can( 'update_plugins' ) ) {
					add_action( 'admin_notices', array( $aioseop_update_checker, 'key_warning' ) );
				}
				add_action( 'after_plugin_row_' . AIOSEOP_PLUGIN_BASENAME, array(
					$aioseop_update_checker,
					'add_plugin_row',
				) );
			}
		} else {
			if ( $aioseop_options['aiosp_can'] == '1' || $aioseop_options['aiosp_can'] == 'on' ) {
				remove_action( 'wp_head', 'rel_canonical' );
			}
			// Analytics.
			if ( aioseop_option_isset( 'aiosp_google_analytics_id' ) ) {
				add_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
			}
			add_action( 'wp_head', array( $this, 'wp_head' ), apply_filters( 'aioseop_wp_head_priority', 1 ) );
			add_action( 'amp_post_template_head', array( $this, 'amp_head' ), 11 );
			add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );
		}
		add_filter( 'aioseop_description', array( &$this, 'filter_description' ) );
		add_filter( 'aioseop_title', array( &$this, 'filter_title' ) );
	}

	function visibility_warning() {

		$aioseop_visibility_notice_dismissed = get_user_meta( get_current_user_id(), 'aioseop_visibility_notice_dismissed', true );

		if ( '0' == get_option( 'blog_public' ) && empty( $aioseop_visibility_notice_dismissed ) ) {

			printf( '
			<div id="message" class="error notice is-dismissible aioseop-notice visibility-notice">
				<p>
					<strong>%1$s</strong>
					%2$s

				</p>
			</div>',
				__( 'Warning: You\'re blocking access to search engines.', 'all-in-one-seo-pack' ),
				sprintf( __( 'You can %s click here%s to go to your reading settings and toggle your blog visibility.', 'all-in-one-seo-pack' ), sprintf( '<a href="%s">', esc_url( admin_url( 'options-reading.php' ) ) ), '</a>' ) );

		} elseif ( '1' == get_option( 'blog_public' ) && ! empty( $aioseop_visibility_notice_dismissed ) ) {
			delete_user_meta( get_current_user_id(), 'aioseop_visibility_notice_dismissed' );
		}
	}

	function woo_upgrade_notice() {

		$aioseop_woo_upgrade_notice_dismissed = get_user_meta( get_current_user_id(), 'aioseop_woo_upgrade_notice_dismissed', true );

		if ( class_exists( 'WooCommerce' ) && empty( $aioseop_woo_upgrade_notice_dismissed ) && current_user_can( 'manage_options' ) ) {

			printf( '
			<div id="message" class="notice-info notice is-dismissible aioseop-notice woo-upgrade-notice">
				<p>
					<strong>%1$s</strong>
					%2$s

				</p>
			</div>',
				__( 'We\'ve detected you\'re running WooCommerce.', 'all-in-one-seo-pack' ),
				sprintf( __( '%s Upgrade%s to All in One SEO Pack Pro for increased SEO compatibility for your products.', 'all-in-one-seo-pack' ), sprintf( '<a target="_blank" href="%s">', esc_url( 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=woo' ) ), '</a>' ) );

		} elseif ( ! class_exists( 'WooCommerce' ) && ! empty( $aioseop_woo_upgrade_notice_dismissed ) ) {
			delete_user_meta( get_current_user_id(), 'aioseop_woo_upgrade_notice_dismissed' );
		}
	}

	/**
	 * @param $description
	 *
	 * @return string
	 */
	function make_unique_att_desc( $description ) {
		global $wp_query;
		if ( is_attachment() ) {

			$url = $this->aiosp_mrt_get_url( $wp_query );
			if ( $url ) {
				$matches = array();
				preg_match_all( '/(\d+)/', $url, $matches );
				if ( is_array( $matches ) ) {
					$uniqueDesc = join( '', $matches[0] );
				}
			}
			$description .= ' ' . $uniqueDesc;
		}

		return $description;
	}

	/**
	 * Adds meta description to AMP pages.
     *
     * @since 2.3.11.5
	 */
	function amp_head() {
		$post = $this->get_queried_object();
		$description = apply_filters( 'aioseop_amp_description', $this->get_main_description( $post ) );    // Get the description.

		// To disable AMP meta description just __return_false on the aioseop_amp_description filter.
		if ( isset( $description ) && false == $description ) {
			return;
		}

		// Handle the description format.
		if ( isset( $description ) && ( $this->strlen( $description ) > $this->minimum_description_length ) && ! ( is_front_page() && is_paged() ) ) {
			$description = $this->trim_description( $description );
			if ( ! isset( $meta_string ) ) {
				$meta_string = '';
			}
			// Description format.
			$description = apply_filters( 'aioseop_amp_description_full', $this->apply_description_format( $description, $post ) );
			$desc_attr   = '';
			if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) ) {
				$desc_attr = '';
			}
			$desc_attr = apply_filters( 'aioseop_amp_description_attributes', $desc_attr );
			$meta_string .= sprintf( "<meta name=\"description\" %s content=\"%s\" />\n", $desc_attr, $description );
		}
		if( ! empty( $meta_string) ){
		    echo $meta_string;
        }
    }

    /**
	 * @since 2.3.14 #932 Removes filter "aioseop_description".
     */
	function wp_head() {

		// Check if we're in the main query to support bad themes and plugins.
		global $wp_query;
		$old_wp_query = null;
		if ( ! $wp_query->is_main_query() ) {
			$old_wp_query = $wp_query;
			wp_reset_query();
		}

		if ( ! $this->is_page_included() ) {
			if ( ! empty( $old_wp_query ) ) {
				// Change the query back after we've finished.
				$GLOBALS['wp_query'] = $old_wp_query;
				unset( $old_wp_query );
			}

			return;
		}
		$opts = $this->meta_opts;
		global $aioseop_update_checker, $wp_query, $aioseop_options, $posts;
		static $aioseop_dup_counter = 0;
		$aioseop_dup_counter ++;
		if ( $aioseop_dup_counter > 1 ) {
			echo "\n<!-- " . sprintf( __( 'Debug Warning: All in One SEO Pack meta data was included again from %s filter. Called %s times!', 'all-in-one-seo-pack' ), current_filter(), $aioseop_dup_counter ) . " -->\n";
			if ( ! empty( $old_wp_query ) ) {
				// Change the query back after we've finished.
				$GLOBALS['wp_query'] = $old_wp_query;
				unset( $old_wp_query );
			}

			return;
		}
		if ( is_home() && ! is_front_page() ) {
			$post = aiosp_common::get_blog_page();
		} else {
			$post = $this->get_queried_object();
		}
		$meta_string = null;
		$description = '';
		// Logging - rewrite handler check for output buffering.
		$this->check_rewrite_handler();
		if ( AIOSEOPPRO ) {
			echo "\n<!-- All in One SEO Pack Pro $this->version by Michael Torbert of Semper Fi Web Design";
		} else {
			echo "\n<!-- All in One SEO Pack $this->version by Michael Torbert of Semper Fi Web Design";
		}
		if ( $this->ob_start_detected ) {
			echo 'ob_start_detected ';
		}
		echo "[$this->title_start,$this->title_end] ";
		echo "-->\n";
		if ( AIOSEOPPRO ) {
			echo '<!-- ' . __( 'Debug String', 'all-in-one-seo-pack' ) . ': ' . $aioseop_update_checker->get_verification_code() . " -->\n";
		}
		$blog_page  = aiosp_common::get_blog_page( $post );
		$save_posts = $posts;

		// This outputs robots meta tags and custom canonical URl on WooCommerce product archive page.
        // See Github issue https://github.com/semperfiwebdesign/all-in-one-seo-pack/issues/755.
		if ( function_exists( 'wc_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = wc_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
			global $posts;
			$opts    = $this->meta_opts = $this->get_current_options( array(), 'aiosp', null, $post );
			$posts   = array();
			$posts[] = $post;
		}

		$posts       = $save_posts;
		$description = $this->get_main_description( $post );    // Get the description.
		// Handle the description format.
		if ( isset( $description ) && ( $this->strlen( $description ) > $this->minimum_description_length ) && ! ( is_front_page() && is_paged() ) ) {
			$description = $this->trim_description( $description );
			if ( ! isset( $meta_string ) ) {
				$meta_string = '';
			}
			// Description format.
			$description = apply_filters( 'aioseop_description_full', $this->apply_description_format( $description, $post ) );
			$desc_attr   = '';
			if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) ) {
				$desc_attr = '';
			}
			$desc_attr = apply_filters( 'aioseop_description_attributes', $desc_attr );
			$meta_string .= sprintf( "<meta name=\"description\" %s content=\"%s\" />\n", $desc_attr, $description );
		}
		// Get the keywords.
		$togglekeywords = 0;
		if ( isset( $aioseop_options['aiosp_togglekeywords'] ) ) {
			$togglekeywords = $aioseop_options['aiosp_togglekeywords'];
		}
		if ( $togglekeywords == 0 && ! ( is_front_page() && is_paged() ) ) {
			$keywords = $this->get_main_keywords();
			$keywords = $this->apply_cf_fields( $keywords );
			$keywords = apply_filters( 'aioseop_keywords', $keywords );

			if ( isset( $keywords ) && ! empty( $keywords ) ) {
				if ( isset( $meta_string ) ) {
					$meta_string .= "\n";
				}
				$keywords = wp_filter_nohtml_kses( str_replace( '"', '', $keywords ) );
				$key_attr = apply_filters( 'aioseop_keywords_attributes', '' );
				$meta_string .= sprintf( "<meta name=\"keywords\" %s content=\"%s\" />\n", $key_attr, $keywords );
			}
		}
		// Handle noindex, nofollow - robots meta.
		$robots_meta = apply_filters( 'aioseop_robots_meta', $this->get_robots_meta() );
		if ( ! empty( $robots_meta ) ) {
			$meta_string .= '<meta name="robots" content="' . esc_attr( $robots_meta ) . '" />' . "\n";
		}
		// Handle site verification.
		if ( is_front_page() ) {
			foreach (
				array(
					'google'    => 'google-site-verification',
					'bing'      => 'msvalidate.01',
					'pinterest' => 'p:domain_verify',
				) as $k => $v
			) {
				if ( ! empty( $aioseop_options["aiosp_{$k}_verify"] ) ) {
					$meta_string .= '<meta name="' . $v . '" content="' . trim( strip_tags( $aioseop_options["aiosp_{$k}_verify"] ) ) . '" />' . "\n";
				}
			}

			// Sitelinks search.
			if ( ! empty( $aioseop_options['aiosp_google_sitelinks_search'] ) || ! empty( $aioseop_options['aiosp_google_set_site_name'] ) ) {
				$meta_string .= $this->sitelinks_search_box() . "\n";
			}
		}
		// Handle extra meta fields.
		foreach ( array( 'page_meta', 'post_meta', 'home_meta', 'front_meta' ) as $meta ) {
			if ( ! empty( $aioseop_options["aiosp_{$meta}_tags"] ) ) {
				$$meta = html_entity_decode( stripslashes( $aioseop_options["aiosp_{$meta}_tags"] ), ENT_QUOTES );
			} else {
				$$meta = '';
			}
		}
		if ( is_page() && isset( $page_meta ) && ! empty( $page_meta ) && ( ! is_front_page() || empty( $front_meta ) ) ) {
			if ( isset( $meta_string ) ) {
				$meta_string .= "\n";
			}
			$meta_string .= $page_meta;
		}
		if ( is_single() && isset( $post_meta ) && ! empty( $post_meta ) ) {
			if ( isset( $meta_string ) ) {
				$meta_string .= "\n";
			}
			$meta_string .= $post_meta;
		}
		// Handle authorship.
		$authorship = $this->get_google_authorship( $post );
		$publisher  = apply_filters( 'aioseop_google_publisher', $authorship['publisher'] );
		if ( ! empty( $publisher ) ) {
			$meta_string = '<link rel="publisher" href="' . esc_url( $publisher ) . '" />' . "\n" . $meta_string;
		}
		$author = apply_filters( 'aioseop_google_author', $authorship['author'] );
		if ( ! empty( $author ) ) {
			$meta_string = '<link rel="author" href="' . esc_url( $author ) . '" />' . "\n" . $meta_string;
		}

		if ( is_front_page() && ! empty( $front_meta ) ) {
			if ( isset( $meta_string ) ) {
				$meta_string .= "\n";
			}
			$meta_string .= $front_meta;
		} else {
			if ( is_home() && ! empty( $home_meta ) ) {
				if ( isset( $meta_string ) ) {
					$meta_string .= "\n";
				}
				$meta_string .= $home_meta;
			}
		}
		$prev_next = $this->get_prev_next_links( $post );
		$prev      = apply_filters( 'aioseop_prev_link', $prev_next['prev'] );
		$next      = apply_filters( 'aioseop_next_link', $prev_next['next'] );
		if ( ! empty( $prev ) ) {
			$meta_string .= "<link rel='prev' href='" . esc_url( $prev ) . "' />\n";
		}
		if ( ! empty( $next ) ) {
			$meta_string .= "<link rel='next' href='" . esc_url( $next ) . "' />\n";
		}
		if ( $meta_string != null ) {
			echo "$meta_string\n";
		}

		// Handle canonical links.
		$show_page = true;
		if ( ! empty( $aioseop_options['aiosp_no_paged_canonical_links'] ) ) {
			$show_page = false;
		}

		if ( $aioseop_options['aiosp_can'] ) {
			$url = '';
			if ( ! empty( $aioseop_options['aiosp_customize_canonical_links'] ) && ! empty( $opts['aiosp_custom_link'] ) && ! is_home() ) {
				$url = $opts['aiosp_custom_link'];
			}
			if ( empty( $url ) ) {
				$url = $this->aiosp_mrt_get_url( $wp_query, $show_page );
			}

			$url = $this->validate_url_scheme( $url );

			$url = apply_filters( 'aioseop_canonical_url', $url );
			if ( ! empty( $url ) ) {
				echo '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
			}
		}
		do_action( 'aioseop_modules_wp_head' );
		if ( AIOSEOPPRO ) {
			echo "<!-- /all in one seo pack pro -->\n";
		} else {
			echo "<!-- /all in one seo pack -->\n";
		}

		if ( ! empty( $old_wp_query ) ) {
			// Change the query back after we've finished.
			$GLOBALS['wp_query'] = $old_wp_query;
			unset( $old_wp_query );
		}

	}

	/**
	 * Check rewrite handler.
	 */
	function check_rewrite_handler() {
		global $aioseop_options;

		$force_rewrites = 1;
		if ( isset( $aioseop_options['aiosp_force_rewrites'] ) ) {
			$force_rewrites = $aioseop_options['aiosp_force_rewrites'];
		}

		if ( ! empty( $aioseop_options['aiosp_rewrite_titles'] ) && $force_rewrites ) {
			// Make the title rewrite as short as possible.
			if ( function_exists( 'ob_list_handlers' ) ) {
				$active_handlers = ob_list_handlers();
			} else {
				$active_handlers = array();
			}
			if ( sizeof( $active_handlers ) > 0 &&
			     $this->strtolower( $active_handlers[ sizeof( $active_handlers ) - 1 ] ) ==
			     $this->strtolower( 'All_in_One_SEO_Pack::output_callback_for_title' )
			) {
				ob_end_flush();
			} else {
				$this->log( 'another plugin interfering?' );
				// If we get here there *could* be trouble with another plugin :(.
				$this->ob_start_detected = true;
				if ( $this->option_isset( 'rewrite_titles' ) ) { // Try alternate method -- pdb.
					$aioseop_options['aiosp_rewrite_titles'] = 0;
					$force_rewrites                          = 0;
					add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );
				}
				if ( function_exists( 'ob_list_handlers' ) ) {
					foreach ( ob_list_handlers() as $handler ) {
						$this->log( "detected output handler $handler" );
					}
				}
			}
		}
	}

	/**
	 * @param $description
	 *
	 * @return mixed|string
	 */
	function trim_description( $description ) {
		$description = trim( wp_strip_all_tags( $description ) );
		$description = str_replace( '"', '&quot;', $description );
		$description = str_replace( "\r\n", ' ', $description );
		$description = str_replace( "\n", ' ', $description );

		return $description;
	}

	/**
	 * @param $description
	 * @param null $post
	 *
	 * @return mixed
	 */
	function apply_description_format( $description, $post = null ) {
		global $aioseop_options;
		$description_format = $aioseop_options['aiosp_description_format'];
		if ( ! isset( $description_format ) || empty( $description_format ) ) {
			$description_format = '%description%';
		}
		$description = str_replace( '%description%', apply_filters( 'aioseop_description_override', $description ), $description_format );
		if ( strpos( $description, '%blog_title%' ) !== false ) {
			$description = str_replace( '%blog_title%', get_bloginfo( 'name' ), $description );
		}
		if ( strpos( $description, '%blog_description%' ) !== false ) {
			$description = str_replace( '%blog_description%', get_bloginfo( 'description' ), $description );
		}
		if ( strpos( $description, '%wp_title%' ) !== false ) {
			$description = str_replace( '%wp_title%', $this->get_original_title(), $description );
		}
		if ( strpos( $description, '%post_title%' ) !== false ) {
			$description = str_replace( '%post_title%', $this->get_aioseop_title( $post ), $description );
		}
		if ( strpos( $description, '%current_date%' ) !== false ) {
			$description = str_replace( '%current_date%', date_i18n( get_option( 'date_format' ) ), $description );
		}
		if ( strpos( $description, "%post_date%" ) !== false ){
			$description = str_replace( '%post_date%', get_the_date(), $description );
		}
		if ( strpos( $description, "%post_year%" ) !== false ){
			$description = str_replace( '%post_year%', get_the_date( 'Y' ), $description );
		}
		if ( strpos( $description, "%post_month%" ) !== false ){
			$description = str_replace( '%post_month%', get_the_date( 'F' ), $description );
		}

		/*this was intended to make attachment descriptions unique if pulling from the parent... let's remove it and see if there are any problems
		*on the roadmap is to have a better hierarchy for attachment description pulling
		* if ($aioseop_options['aiosp_can']) $description = $this->make_unique_att_desc($description);
		*/
		$description = $this->apply_cf_fields($description);
		return $description;
	}

	/**
	 * @return string
     * @since 0.0
     * @since 2.3.11.5 Added no index API filter hook for password protected posts.
	 */
	function get_robots_meta() {
		global $aioseop_options;
		$opts        = $this->meta_opts;
		$page        = $this->get_page_number();
		$robots_meta = $tax_noindex = '';
		if ( isset( $aioseop_options['aiosp_tax_noindex'] ) ) {
			$tax_noindex = $aioseop_options['aiosp_tax_noindex'];
		}

		if ( empty( $tax_noindex ) || ! is_array( $tax_noindex ) ) {
			$tax_noindex = array();
		}

		$aiosp_noindex = $aiosp_nofollow = '';
		$noindex       = 'index';
		$nofollow      = 'follow';
		if ( ( is_category() && ! empty( $aioseop_options['aiosp_category_noindex'] ) ) || ( ! is_category() && is_archive() && ! is_tag() && ! is_tax()
		                                                                                     && ( ( is_date() && ! empty( $aioseop_options['aiosp_archive_date_noindex'] ) ) || ( is_author() && ! empty( $aioseop_options['aiosp_archive_author_noindex'] ) ) ) )
		     || ( is_tag() && ! empty( $aioseop_options['aiosp_tags_noindex'] ) )
		     || ( is_search() && ! empty( $aioseop_options['aiosp_search_noindex'] ) )
		     || ( is_404() && ! empty( $aioseop_options['aiosp_404_noindex'] ) )
		     || ( is_tax() && in_array( get_query_var( 'taxonomy' ), $tax_noindex ) )
		) {
			$noindex = 'noindex';
		} elseif ( is_single() || is_page() || $this->is_static_posts_page() || is_attachment() || is_category() || is_tag() || is_tax() || ( $page > 1 ) ) {
			$post_type = get_post_type();
			if ( ! empty( $opts ) ) {
				$aiosp_noindex  = htmlspecialchars( stripslashes( $opts['aiosp_noindex'] ) );
				$aiosp_nofollow = htmlspecialchars( stripslashes( $opts['aiosp_nofollow'] ) );
			}
			if ( $aiosp_noindex || $aiosp_nofollow || ! empty( $aioseop_options['aiosp_cpostnoindex'] )
			     || ! empty( $aioseop_options['aiosp_cpostnofollow'] ) || ! empty( $aioseop_options['aiosp_paginated_noindex'] ) || ! empty( $aioseop_options['aiosp_paginated_nofollow'] )
			) {
				if ( ( $aiosp_noindex == 'on' ) || ( ( ! empty( $aioseop_options['aiosp_paginated_noindex'] ) ) && $page > 1 ) ||
				     ( ( $aiosp_noindex == '' ) && ( ! empty( $aioseop_options['aiosp_cpostnoindex'] ) ) && in_array( $post_type, $aioseop_options['aiosp_cpostnoindex'] ) )
				) {
					$noindex = 'noindex';
				}
				if ( ( $aiosp_nofollow == 'on' ) || ( ( ! empty( $aioseop_options['aiosp_paginated_nofollow'] ) ) && $page > 1 ) ||
				     ( ( $aiosp_nofollow == '' ) && ( ! empty( $aioseop_options['aiosp_cpostnofollow'] ) ) && in_array( $post_type, $aioseop_options['aiosp_cpostnofollow'] ) )
				) {
					$nofollow = 'nofollow';
				}
			}
		}
		if ( is_singular() && $this->is_password_protected() && apply_filters( 'aiosp_noindex_password_posts', false ) ){
		    $noindex = 'noindex';
        }

		$robots_meta = $noindex . ',' . $nofollow;
		if ( $robots_meta == 'index,follow' ) {
			$robots_meta = '';
		}

		return $robots_meta;
	}

	/**
     * Determine if post is password protected.
     * @since 2.3.11.5
	 * @return bool
	 */
    function is_password_protected(){
	    global $post;

	    if (!empty($post->post_password)) {
		    return true;
	    }

        return false;

    }

	/**
	 * @return mixed|void
	 */
	function sitelinks_search_box() {
		global $aioseop_options;
		$home_url   = esc_url( get_home_url() );
		$name_block = $search_block = '';
		if ( ! empty( $aioseop_options['aiosp_google_set_site_name'] ) ) {
			if ( ! empty( $aioseop_options['aiosp_google_specify_site_name'] ) ) {
				$blog_name = $aioseop_options['aiosp_google_specify_site_name'];
			} else {
				$blog_name = get_bloginfo( 'name' );
			}
			$blog_name  = esc_attr( $blog_name );
			$name_block = <<<EOF
		  "name": "{$blog_name}",
EOF;
		}

		if ( ! empty( $aioseop_options['aiosp_google_sitelinks_search'] ) ) {
			$search_block = <<<EOF
        "potentialAction": {
          "@type": "SearchAction",
          "target": "{$home_url}/?s={search_term}",
          "query-input": "required name=search_term"
        },
EOF;
		}

		$search_box = <<<EOF
<script type="application/ld+json">
        {
          "@context": "http://schema.org",
          "@type": "WebSite",
EOF;
		if ( ! empty( $name_block ) ) {
			$search_box .= $name_block;
		}
		if ( ! empty( $search_block ) ) {
			$search_box .= $search_block;
		}
		$search_box .= <<<EOF
		  "url": "{$home_url}/"
        }
</script>
EOF;

		return apply_filters( 'aiosp_sitelinks_search_box', $search_box );
	}

	/**
	 * @param $post
	 *
	 * @return array
	 */
	function get_google_authorship( $post ) {
		global $aioseop_options;
		$page = $this->get_page_number();
		// Handle authorship.
		$googleplus = $publisher = $author = '';

		if ( ! empty( $post ) && isset( $post->post_author ) && empty( $aioseop_options['aiosp_google_disable_profile'] ) ) {
			$googleplus = get_the_author_meta( 'googleplus', $post->post_author );
		}

		if ( empty( $googleplus ) && ! empty( $aioseop_options['aiosp_google_publisher'] ) ) {
			$googleplus = $aioseop_options['aiosp_google_publisher'];
		}

		if ( is_front_page() && ( $page < 2 ) ) {
			if ( ! empty( $aioseop_options['aiosp_google_publisher'] ) ) {
				$publisher = $aioseop_options['aiosp_google_publisher'];
			}

			if ( ! empty( $aioseop_options['aiosp_google_author_advanced'] ) ) {
				if ( empty( $aioseop_options['aiosp_google_enable_publisher'] ) ) {
					$publisher = '';
				} elseif ( ! empty( $aioseop_options['aiosp_google_specify_publisher'] ) ) {
					$publisher = $aioseop_options['aiosp_google_specify_publisher'];
				}
			}
		}
		if ( is_singular() && ( ! empty( $googleplus ) ) ) {
			$author = $googleplus;
		} else if ( ! empty( $aioseop_options['aiosp_google_publisher'] ) ) {
			$author = $aioseop_options['aiosp_google_publisher'];
		}

		if ( ! empty( $aioseop_options['aiosp_google_author_advanced'] ) && isset( $aioseop_options['aiosp_google_author_location'] ) ) {
			if ( empty( $aioseop_options['aiosp_google_author_location'] ) ) {
				$aioseop_options['aiosp_google_author_location'] = array();
			}
			if ( is_front_page() && ! in_array( 'front', $aioseop_options['aiosp_google_author_location'] ) ) {
				$author = '';
			} else {
				if ( in_array( 'all', $aioseop_options['aiosp_google_author_location'] ) ) {
					if ( is_singular() && ! is_singular( $aioseop_options['aiosp_google_author_location'] ) ) {
						$author = '';
					}
				} else {
					if ( ! is_singular( $aioseop_options['aiosp_google_author_location'] ) ) {
						$author = '';
					}
				}
			}
		}

		return array( 'publisher' => $publisher, 'author' => $author );
	}

	/**
	 * @param null $post
	 *
	 * @return array
	 */
	function get_prev_next_links( $post = null ) {
		$prev = $next = '';
		$page = $this->get_page_number();
		if ( is_home() || is_archive() || is_paged() ) {
			global $wp_query;
			$max_page = $wp_query->max_num_pages;
			if ( $page > 1 ) {
				$prev = get_previous_posts_page_link();
			}
			if ( $page < $max_page ) {
				$paged = $GLOBALS['paged'];
				if ( ! is_single() ) {
					if ( ! $paged ) {
						$paged = 1;
					}
					$nextpage = intval( $paged ) + 1;
					if ( ! $max_page || $max_page >= $nextpage ) {
						$next = get_pagenum_link( $nextpage );
					}
				}
			}
		} else if ( is_page() || is_single() ) {
			$numpages  = 1;
			$multipage = 0;
			$page      = get_query_var( 'page' );
			if ( ! $page ) {
				$page = 1;
			}
			if ( is_single() || is_page() || is_feed() ) {
				$more = 1;
			}
			$content = $post->post_content;
			if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
				if ( $page > 1 ) {
					$more = 1;
				}
				$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
				$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
				// Ignore nextpage at the beginning of the content.
				if ( 0 === strpos( $content, '<!--nextpage-->' ) ) {
					$content = substr( $content, 15 );
				}
				$pages    = explode( '<!--nextpage-->', $content );
				$numpages = count( $pages );
				if ( $numpages > 1 ) {
					$multipage = 1;
				}
			}
			if ( ! empty( $page ) ) {
				if ( $page > 1 ) {
					$prev = _wp_link_page( $page - 1 );
				}
				if ( $page + 1 <= $numpages ) {
					$next = _wp_link_page( $page + 1 );
				}
			}
			if ( ! empty( $prev ) ) {
				$prev = $this->substr( $prev, 9, - 2 );
			}
			if ( ! empty( $next ) ) {
				$next = $this->substr( $next, 9, - 2 );
			}
		}

		return array( 'prev' => $prev, 'next' => $next );
	}

	/**
	 *
	 * Validates whether the url should be https or http.
	 *
	 * Mainly we're just using this for canonical URLS, but eventually it may be useful for other things
	 *
	 * @param $url
	 *
	 * @return string $url
	 *
	 * @since 2.3.5
	 * @since 2.3.11 Removed check for legacy protocol setting. Added filter.
	 */
	function validate_url_scheme( $url ) {

		// TODO we should check for the site setting in the case of auto.

		global $aioseop_options;

		$scheme = apply_filters( 'aioseop_canonical_protocol', false );

		if ( 'http' === $scheme ) {
			$url = preg_replace( '/^https:/i', 'http:', $url );
		}
		if ( 'https' === $scheme ) {
			$url = preg_replace( '/^http:/i', 'https:', $url );
		}

		return $url;
	}

	/**
	 * @param $options
	 * @param $location
	 * @param $settings
	 *
	 * @return mixed
	 */
	function override_options( $options, $location, $settings ) {
		if ( class_exists( 'DOMDocument' ) ) {
			$options['aiosp_google_connect'] = $settings['aiosp_google_connect']['default'];
		}

		return $options;
	}

	function aiosp_google_analytics() {
		new aioseop_google_analytics;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	function save_post_data( $id ) {
		$awmp_edit = $nonce = null;
		if ( empty( $_POST ) ) {
			return false;
		}
		if ( isset( $_POST['aiosp_edit'] ) ) {
			$awmp_edit = $_POST['aiosp_edit'];
		}
		if ( isset( $_POST['nonce-aioseop-edit'] ) ) {
			$nonce = $_POST['nonce-aioseop-edit'];
		}

		if ( isset( $awmp_edit ) && ! empty( $awmp_edit ) && wp_verify_nonce( $nonce, 'edit-aioseop-nonce' ) ) {

			$optlist = array(
				'keywords',
				'description',
				'title',
				'custom_link',
				'sitemap_exclude',
				'disable',
				'disable_analytics',
				'noindex',
				'nofollow',
			);
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) && ( ! empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist['custom_link'] );
			}
			foreach ( $optlist as $f ) {
				$field = "aiosp_$f";
				if ( isset( $_POST[ $field ] ) ) {
					$$field = $_POST[ $field ];
				}
			}

			$optlist = array(
				'keywords',
				'description',
				'title',
				'custom_link',
				'noindex',
				'nofollow',
			);
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) && ( ! empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist['custom_link'] );
			}
			foreach ( $optlist as $f ) {
				delete_post_meta( $id, "_aioseop_{$f}" );
			}

				delete_post_meta( $id, '_aioseop_sitemap_exclude' );
				delete_post_meta( $id, '_aioseop_disable' );
				delete_post_meta( $id, '_aioseop_disable_analytics' );

			foreach ( $optlist as $f ) {
				$var   = "aiosp_$f";
				$field = "_aioseop_$f";
				if ( isset( $$var ) && ! empty( $$var ) ) {
					add_post_meta( $id, $field, $$var );
				}
			}
			if ( isset( $aiosp_sitemap_exclude ) && ! empty( $aiosp_sitemap_exclude ) ) {
				add_post_meta( $id, '_aioseop_sitemap_exclude', $aiosp_sitemap_exclude );
			}
			if ( isset( $aiosp_disable ) && ! empty( $aiosp_disable ) ) {
				add_post_meta( $id, '_aioseop_disable', $aiosp_disable );
				if ( isset( $aiosp_disable_analytics ) && ! empty( $aiosp_disable_analytics ) ) {
					add_post_meta( $id, '_aioseop_disable_analytics', $aiosp_disable_analytics );
				}
			}
		}
	}

	/**
	 * @param $post
	 * @param $metabox
	 */
	function display_tabbed_metabox( $post, $metabox ) {
		$tabs = $metabox['args'];
		echo '<div class="aioseop_tabs">';
		$header = $this->get_metabox_header( $tabs );
		echo $header;
		$active = '';
		foreach ( $tabs as $m ) {
			echo '<div id="' . $m['id'] . '" class="aioseop_tab"' . $active . '>';
			if ( ! $active ) {
				$active = ' style="display:none;"';
			}
			$m['args'] = $m['callback_args'];
			$m['callback'][0]->{$m['callback'][1]}( $post, $m );
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * @param $tabs
	 *
	 * @return string
	 */
	function get_metabox_header( $tabs ) {
		$header = '<ul class="aioseop_header_tabs hide">';
		$active = ' active';
		foreach ( $tabs as $t ) {
			if ( $active ) {
				$title = __( 'Main Settings', 'all-in-one-seo-pack' );
			} else {
				$title = $t['title'];
			}
			$header .= '<li><label class="aioseop_header_nav"><a class="aioseop_header_tab' . $active . '" href="#' . $t['id'] . '">' . $title . '</a></label></li>';
			$active = '';
		}
		$header .= '</ul>';

		return $header;
	}

	function admin_bar_menu() {


		if ( apply_filters( 'aioseo_show_in_admin_bar', true ) === false ) {
			// API filter hook to disable showing SEO in admin bar.
			return;
		}


		global $wp_admin_bar, $aioseop_admin_menu, $post, $aioseop_options;

		$toggle = '';
		if ( isset( $_POST['aiosp_use_original_title'] ) && isset( $_POST['aiosp_admin_bar'] ) && AIOSEOPPRO ) {
			$toggle = 'on';
		}
		if ( isset( $_POST['aiosp_use_original_title'] ) && ! isset( $_POST['aiosp_admin_bar'] ) && AIOSEOPPRO ) {
			$toggle = 'off';
		}

		if ( ( ! isset( $aioseop_options['aiosp_admin_bar'] ) && 'off' !== $toggle  ) || ( ! empty( $aioseop_options['aiosp_admin_bar'] ) && 'off' !== $toggle ) || isset( $_POST['aiosp_admin_bar'] ) || true == apply_filters( 'aioseo_show_in_admin_bar', false ) ) {

			if ( apply_filters( 'aioseo_show_in_admin_bar', true ) === false ) {
				// API filter hook to disable showing SEO in admin bar.
				return;
			}

			$menu_slug = plugin_basename( __FILE__ );

			$url = '';
			if ( function_exists( 'menu_page_url' ) ) {
				$url = menu_page_url( $menu_slug, 0 );
			}
			if ( empty( $url ) ) {
				$url = esc_url( admin_url( 'admin.php?page=' . $menu_slug ) );
			}

			$wp_admin_bar->add_menu( array(
				'id'    => AIOSEOP_PLUGIN_DIRNAME,
				'title' => __( 'SEO', 'all-in-one-seo-pack' ),
				'href'  => $url,
			) );

			if ( current_user_can( 'update_plugins' ) && ! AIOSEOPPRO ) {
				$wp_admin_bar->add_menu( array(
					'parent' => AIOSEOP_PLUGIN_DIRNAME,
					'title'  => __( 'Upgrade To Pro', 'all-in-one-seo-pack' ),
					'id'     => 'aioseop-pro-upgrade',
					'href'   => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=menu',
					'meta'   => array( 'target' => '_blank' ),
				) );
				// add_action( 'admin_bar_menu', array( $this, 'admin_bar_upgrade_menu' ), 1101 );
			}

			$aioseop_admin_menu = 1;
			if ( ! is_admin() && ! empty( $post ) ) {

				$blog_page = aiosp_common::get_blog_page( $post );
				if ( ! empty( $blog_page ) ) {
					$post = $blog_page;
				}
				// Don't show if we're on the home page and the home page is the latest posts.
				if ( ! is_home() || ( ! is_front_page() && ! is_home() ) ) {
					global $wp_the_query;
					$current_object = $wp_the_query->get_queried_object();

					if ( is_singular() ) {
						if ( ! empty( $current_object ) && ! empty( $current_object->post_type ) ) {
							// Try the main query.
							$edit_post_link = get_edit_post_link( $current_object->ID );
							$wp_admin_bar->add_menu( array(
								'id'     => 'aiosp_edit_' . $current_object->ID,
								'parent' => AIOSEOP_PLUGIN_DIRNAME,
								'title'  => 'Edit SEO',
								'href'   => $edit_post_link . '#aiosp',
							) );
						} else {
							// Try the post object.
							$wp_admin_bar->add_menu( array(
								'id'     => 'aiosp_edit_' . $post->ID,
								'parent' => AIOSEOP_PLUGIN_DIRNAME,
								'title'  => __( 'Edit SEO', 'all-in-one-seo-pack' ),
								'href'   => get_edit_post_link( $post->ID ) . '#aiosp',
							) );
						}
					}

					if ( AIOSEOPPRO && ( is_category() || is_tax() || is_tag() ) ) {
						// SEO for taxonomies are only available in Pro version.
						$edit_term_link = get_edit_term_link( $current_object->term_id, $current_object->taxonomy );
						$wp_admin_bar->add_menu( array(
							'id'     => 'aiosp_edit_' . $post->ID,
							'parent' => AIOSEOP_PLUGIN_DIRNAME,
							'title'  => __( 'Edit SEO', 'all-in-one-seo-pack' ),
							'href'   => $edit_term_link . '#aiosp',
						) );
					}


				}
			}
		}
	}

	/**
	 * Order for adding the menus for the aioseop_modules_add_menus hook.
	 */
	function menu_order() {
		return 5;
	}

	/**
	 * @param $tax
	 */
	function display_category_metaboxes( $tax ) {
		$screen = 'edit-' . $tax->taxonomy;
		?>
		<div id="poststuff">
			<?php do_meta_boxes( '', 'advanced', $tax ); ?>
		</div>
		<?php
	}

	/**
	 * @param $id
	 */
	function save_category_metaboxes( $id ) {
		$awmp_edit = $nonce = null;
		if ( isset( $_POST['aiosp_edit'] ) ) {
			$awmp_edit = $_POST['aiosp_edit'];
		}
		if ( isset( $_POST['nonce-aioseop-edit'] ) ) {
			$nonce = $_POST['nonce-aioseop-edit'];
		}

		if ( isset( $awmp_edit ) && ! empty( $awmp_edit ) && wp_verify_nonce( $nonce, 'edit-aioseop-nonce' ) ) {
			$optlist = array(
				'keywords',
				'description',
				'title',
				'custom_link',
				'sitemap_exclude',
				'disable',
				'disable_analytics',
				'noindex',
				'nofollow',
			);
			foreach ( $optlist as $f ) {
				$field = "aiosp_$f";
				if ( isset( $_POST[ $field ] ) ) {
					$$field = $_POST[ $field ];
				}
			}

			$optlist = array(
				'keywords',
				'description',
				'title',
				'custom_link',
				'noindex',
				'nofollow',
			);
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) && ( ! empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist['custom_link'] );
			}
			foreach ( $optlist as $f ) {
				delete_term_meta( $id, "_aioseop_{$f}" );
			}

			if ( current_user_can( 'activate_plugins' ) ) {
				delete_term_meta( $id, '_aioseop_sitemap_exclude' );
				delete_term_meta( $id, '_aioseop_disable' );
				delete_term_meta( $id, '_aioseop_disable_analytics' );
			}

			foreach ( $optlist as $f ) {
				$var   = "aiosp_$f";
				$field = "_aioseop_$f";
				if ( isset( $$var ) && ! empty( $$var ) ) {
					add_term_meta( $id, $field, $$var );
				}
			}
			if ( isset( $aiosp_sitemap_exclude ) && ! empty( $aiosp_sitemap_exclude ) && current_user_can( 'activate_plugins' ) ) {
				add_term_meta( $id, '_aioseop_sitemap_exclude', $aiosp_sitemap_exclude );
			}
			if ( isset( $aiosp_disable ) && ! empty( $aiosp_disable ) && current_user_can( 'activate_plugins' ) ) {
				add_term_meta( $id, '_aioseop_disable', $aiosp_disable );
				if ( isset( $aiosp_disable_analytics ) && ! empty( $aiosp_disable_analytics ) ) {
					add_term_meta( $id, '_aioseop_disable_analytics', $aiosp_disable_analytics );
				}
			}
		}
	}

	function admin_menu() {
		$file      = plugin_basename( __FILE__ );
		$menu_name = __( 'All in One SEO', 'all-in-one-seo-pack' );

		$this->locations['aiosp']['default_options']['nonce-aioseop-edit']['default'] = wp_create_nonce( 'edit-aioseop-nonce' );

		$custom_menu_order = false;
		global $aioseop_options;
		if ( ! isset( $aioseop_options['custom_menu_order'] ) ) {
			$custom_menu_order = true;
		}

		$this->update_options();

		// For now we're removing admin pointers.
		/*
		$this->add_admin_pointers();
		if ( ! empty( $this->pointers ) ) {
			foreach ( $this->pointers as $k => $p ) {
				if ( ! empty( $p['pointer_scope'] ) && ( $p['pointer_scope'] == 'global' ) ) {
					unset( $this->pointers[ $k ] );
				}
			}
		}
		*/

		if ( isset( $_POST ) && isset( $_POST['module'] ) && isset( $_POST['nonce-aioseop'] ) && ( $_POST['module'] == 'All_in_One_SEO_Pack' ) && wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) ) {
			if ( isset( $_POST['Submit'] ) && AIOSEOPPRO ) {
				if ( isset( $_POST['aiosp_custom_menu_order'] ) ) {
					$custom_menu_order = $_POST['aiosp_custom_menu_order'];
				} else {
					$custom_menu_order = false;
				}
			} else if ( isset( $_POST['Submit_Default'] ) || isset( $_POST['Submit_All_Default'] ) ) {
				$custom_menu_order = true;
			}
		} else {
			if ( isset( $this->options['aiosp_custom_menu_order'] ) ) {
				$custom_menu_order = $this->options['aiosp_custom_menu_order'];
			}
		}

		if ( ( $custom_menu_order && false !== apply_filters( 'aioseo_custom_menu_order', $custom_menu_order ) ) || true === apply_filters( 'aioseo_custom_menu_order', $custom_menu_order ) ) {
			add_filter( 'custom_menu_order', '__return_true' );
			add_filter( 'menu_order', array( $this, 'set_menu_order' ), 11 );
		}

		if ( ! AIOSEOPPRO ) {
			if ( ! empty( $this->pointers ) ) {
				foreach ( $this->pointers as $k => $p ) {
					if ( ! empty( $p['pointer_scope'] ) && ( $p['pointer_scope'] == 'global' ) ) {
						unset( $this->pointers[ $k ] );
					}
				}
			}

			$this->filter_pointers();
		}

		if ( ! empty( $this->options['aiosp_enablecpost'] ) && $this->options['aiosp_enablecpost'] ) {
			if ( AIOSEOPPRO ) {
				if( is_array( $this->options['aiosp_cpostactive'] ) ) {
     				     $this->locations['aiosp']['display'] = $this->options['aiosp_cpostactive'];
				}else{
					$this->locations['aiosp']['display'][] = $this->options['aiosp_cpostactive']; // Store as an array in case there are taxonomies to add also.
				}

				if ( ! empty( $this->options['aiosp_taxactive'] ) ) {
					foreach ( $this->options['aiosp_taxactive'] as $tax ) {
						$this->locations['aiosp']['display'][] = 'edit-' . $tax;
						add_action( "{$tax}_edit_form", array( $this, 'display_category_metaboxes' ) );
						add_action( "edited_{$tax}", array( $this, 'save_category_metaboxes' ) );
					}
				}
			} else {
				if ( ! empty( $this->options['aiosp_cpostactive'] ) ) {
					$this->locations['aiosp']['display'] = $this->options['aiosp_cpostactive'];
				} else {
					$this->locations['aiosp']['display'] = array();
				}
			}
		} else {
			$this->locations['aiosp']['display'] = array( 'post', 'page' );
		}


		add_menu_page( $menu_name, $menu_name, apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ), $file, array(
			$this,
			'display_settings_page',
		) );

		add_meta_box( 'aioseop-list', __( 'Join Our Mailing List', 'all-in-one-seo-pack' ), array(
			'aiosp_metaboxes',
			'display_extra_metaboxes',
		), 'aioseop_metaboxes', 'normal', 'core' );
		if ( AIOSEOPPRO ) {
			add_meta_box( 'aioseop-about', __( 'About', 'all-in-one-seo-pack' ), array(
				'aiosp_metaboxes',
				'display_extra_metaboxes',
			), 'aioseop_metaboxes', 'side', 'core' );
		} else {
			add_meta_box( 'aioseop-about', __( 'About', 'all-in-one-seo-pack' ) . "<span class='Taha' style='float:right;'>" . __( 'Version', 'all-in-one-seo-pack' ) . " <b>" . AIOSEOP_VERSION . '</b></span>', array(
				'aiosp_metaboxes',
				'display_extra_metaboxes',
			), 'aioseop_metaboxes', 'side', 'core' );
		}
		add_meta_box( 'aioseop-support', __( 'Support', 'all-in-one-seo-pack' ) . " <span  class='Taha' style='float:right;'>" . __( 'Version', 'all-in-one-seo-pack' ) . ' <b>' . AIOSEOP_VERSION . '</b></span>', array(
			'aiosp_metaboxes',
			'display_extra_metaboxes',
		), 'aioseop_metaboxes', 'side', 'core' );

		add_action( 'aioseop_modules_add_menus', array( $this, 'add_menu' ), 5 );
		do_action( 'aioseop_modules_add_menus', $file );

		$metaboxes = apply_filters( 'aioseop_add_post_metabox', array() );

		if ( ! empty( $metaboxes ) ) {
			if ( $this->tabbed_metaboxes ) {
				$tabs    = array();
				$tab_num = 0;
				foreach ( $metaboxes as $m ) {
					if ( ! isset( $tabs[ $m['post_type'] ] ) ) {
						$tabs[ $m['post_type'] ] = array();
					}
					$tabs[ $m['post_type'] ][] = $m;
				}

				if ( ! empty( $tabs ) ) {
					foreach ( $tabs as $p => $m ) {
						$tab_num = count( $m );
						$title   = $m[0]['title'];
						if ( $title != $this->plugin_name ) {
							$title = $this->plugin_name . ' - ' . $title;
						}
						if ( $tab_num <= 1 ) {
							if ( ! empty( $m[0]['callback_args']['help_link'] ) ) {
								$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $m[0]['callback_args']['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . '</span></a>';
							}
							add_meta_box( $m[0]['id'], $title, $m[0]['callback'], $m[0]['post_type'], $m[0]['context'], $m[0]['priority'], $m[0]['callback_args'] );
						} elseif ( $tab_num > 1 ) {
							add_meta_box( $m[0]['id'] . '_tabbed', $title, array(
								$this,
								'display_tabbed_metabox',
							), $m[0]['post_type'], $m[0]['context'], $m[0]['priority'], $m );
						}
					}
				}
			} else {
				foreach ( $metaboxes as $m ) {
					$title = $m['title'];
					if ( $title != $this->plugin_name ) {
						$title = $this->plugin_name . ' - ' . $title;
					}
					if ( ! empty( $m['help_link'] ) ) {
						$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $m['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . '</span></a>';
					}
					add_meta_box( $m['id'], $title, $m['callback'], $m['post_type'], $m['context'], $m['priority'], $m['callback_args'] );
				}
			}
		}
	}

	/**
	 * @param $menu_order
	 *
	 * @return array
	 */
	function set_menu_order( $menu_order ) {
		$order = array();
		$file  = plugin_basename( __FILE__ );
		foreach ( $menu_order as $index => $item ) {
			if ( $item != $file ) {
				$order[] = $item;
			}
			if ( $index == 0 ) {
				$order[] = $file;
			}
		}

		return $order;
	}

	function display_settings_header() {
	}

	function display_settings_footer() {
	}

	/**
	 * Filters title and meta titles and applies cleanup.
	 * - Decode HTML entities.
	 * - Encodes to SEO ready HTML entities.
	 * Returns cleaned value.
	 *
	 * @since 2.3.14
	 *
	 * @param string $value Value to filter.
	 *
	 * @return string
	 */
	public function filter_title( $value ) {
		// Decode entities
		$value = $this->html_entity_decode( $value );
		// Encode to valid SEO html entities
		return $this->seo_entity_encode( $value );
	}

	/**
	 * Filters meta value and applies generic cleanup.
	 * - Decode HTML entities.
	 * - Removal of urls.
	 * - Internal trim.
	 * - External trim.
	 * - Strips HTML except anchor texts.
	 * - Returns cleaned value.
	 *
	 * @since 2.3.13
	 * @since 2.3.14 Strips excerpt anchor texts.
	 * @since 2.3.14 Encodes to SEO ready HTML entities.
	 * @since 2.3.14 #593 encode/decode refactored.
	 *
	 * @param string $value Value to filter.
	 *
	 * @return string
	 */
	public function filter_description( $value) {
		if ( preg_match( '/5.2[\s\S]+/', PHP_VERSION ) )
			$value = htmlspecialchars( wp_strip_all_tags( htmlspecialchars_decode( $value ) ) );
		// Decode entities
		$value = $this->html_entity_decode( $value );
		$value = preg_replace(
			array(
				'#<a.*?>([^>]*)</a>#i', // Remove link but keep anchor text
				'@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',// Remove URLs
			),
			array(
				'$1', // Replacement link's anchor text.
				'', // Replacement URLs
			),
			$value
		);
		// Strip html
		$value = wp_strip_all_tags( $value );
		// Encode to valid SEO html entities
		$value = $this->seo_entity_encode( $value );
		// Internal whitespace trim.
		$value = preg_replace( '/\s\s+/u', ' ', $value );
		// External trim.
		return trim( $value );
	}

	/**
 	 * Returns string with decoded html entities.
 	 * - Custom html_entity_decode supported on PHP 5.2
 	 *
 	 * @since 2.3.14
 	 * @since 2.3.14.2 Hot fix on apostrophes.
 	 *
 	 * @param string $value Value to decode.
 	 *
 	 * @return string
 	 */
 	private function html_entity_decode( $value ) {
 		// Special conversions
 		$value = preg_replace(
 			array(
 				'/\“|\”|&#[xX]00022;|&#34;|&[lLrRbB](dquo|DQUO)(?:[rR])?;|&#[xX]0201[dDeE];'
 					.'|&[OoCc](pen|lose)[Cc]urly[Dd]ouble[Qq]uote;|&#822[012];|&#[xX]27;/', // Double quotes
 				'/&#8217;|&apos;/', // Apostrophes
 			),
 			array(
 				'"', // Double quotes
 				'\'', // Apostrophes
 			),
 			$value
 		);
 		return html_entity_decode( $value );
 	}

	/**
	 * Returns SEO ready string with encoded HTML entitites.
	 *
	 * @since 2.3.14
 	 * @since 2.3.14.1 Hot fix on apostrophes.
	 *
	 * @param string $value Value to encode.
	 *
	 * @return string
	 */
	private function seo_entity_encode( $value ) {
		return preg_replace(
			array(
				'/\"|\“|\”|\„/', // Double quotes
				'/\'|\’|\‘/',	// Apostrophes
			),
			array(
				'&quot;', // Double quotes
				'&#039;', // Apostrophes
			),
			esc_html( $value )
		);
	}

	function display_right_sidebar() {
		global $wpdb;

		if ( ! get_option( 'aioseop_options' ) ) {
			$msg = "<div style='text-align:center;'><p><strong>Your database options need to be updated.</strong><em>(Back up your database before updating.)</em>
				<FORM action='' method='post' name='aioseop-migrate-options'>
					<input type='hidden' name='nonce-aioseop-migrate-options' value='" . wp_create_nonce( 'aioseop-migrate-nonce-options' ) . "' />
					<input type='submit' name='aioseop_migrate_options' class='button-primary' value='Update Database Options'>
		 		</FORM>
			</p></div>";
			aioseop_output_dismissable_notice( $msg, '', 'error' );
		}
		?>
		<div class="aioseop_top">
			<div class="aioseop_top_sidebar aioseop_options_wrapper">
				<?php do_meta_boxes( 'aioseop_metaboxes', 'normal', array( 'test' ) ); ?>
			</div>
		</div>
		<style>
			#wpbody-content {
				min-width: 900px;
			}
		</style>
		<div class="aioseop_right_sidebar aioseop_options_wrapper">

			<div class="aioseop_sidebar">
				<?php
				do_meta_boxes( 'aioseop_metaboxes', 'side', array( 'test' ) );
				?>
				<script type="text/javascript">
					//<![CDATA[
					jQuery(document).ready(function ($) {
						// Close postboxes that should be closed.
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						// Postboxes setup.
						if (typeof postboxes !== 'undefined')
							postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
					});
					//]]>
				</script>
				<?php if ( ! AIOSEOPPRO ) { ?>
					<div class="aioseop_advert aioseop_nopad_all">
						<?php $adid = mt_rand( 21, 22 ); ?>
							<a href="https://www.wincher.com/?referer=all-in-one-seo-pack&adreferer=banner<?php echo $adid; ?>"
							   target="_blank">
								<div class=wincherad id=wincher<?php echo $adid; ?>>
								</div>
							</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

}
