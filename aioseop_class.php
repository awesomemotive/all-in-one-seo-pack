<?php
/**
 * @package All-in-One-SEO-Pack
 */
/**
 * Include the module base class.
 */
require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' );
/**
 * The main class.
 */
class All_in_One_SEO_Pack extends All_in_One_SEO_Pack_Module {

	/** The current version of the plugin. **/
 	var $version = AIOSEOP_VERSION;

 	/** Max numbers of chars in auto-generated description */
 	var $maximum_description_length = 160;

 	/** Minimum number of chars an excerpt should be so that it can be used
 	 * as description. Touch only if you know what you're doing
 	 */
 	var $minimum_description_length = 1;

	/** Whether output buffering is already being used during forced title rewrites. **/
 	var $ob_start_detected = false;

 	/** The start of the title text in the head section for forced title rewrites. **/
 	var $title_start = -1;

 	/** The end of the title text in the head section for forced title rewrites. **/
 	var $title_end = -1;

 	/** The title before rewriting */
 	var $orig_title = '';

 	/** Filename of log file. */
 	var $log_file;

 	/** Flag whether there should be logging. */
 	var $do_log;

	var $token;
	var $secret;
	var $access_token;
	var $ga_token;
	var $account_cache;
	var $profile_id;
	var $meta_opts = false;
	var $is_front_page = null;

	function __construct() {
		global $aioseop_options;
		$this->log_file = dirname( __FILE__ ) . '/all-in-one-seo-pack.log';

		if ( !empty( $aioseop_options ) && isset( $aioseop_options['aiosp_do_log'] ) && $aioseop_options['aiosp_do_log'] )
			$this->do_log = true;
		else
			$this->do_log = false;

		$this->name = sprintf( __( '%s Plugin Options', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
		$this->menu_name = __( 'General Settings', 'all-in-one-seo-pack' );

		$this->prefix = 'aiosp_';						// option prefix
		$this->option_name = 'aioseop_options';
		$this->store_option = true;
		$this->file = __FILE__;								// the current file
		$blog_name = esc_attr( get_bloginfo( 'name' ) );
		parent::__construct();

		$this->help_text = Array(
			"donate"				=> __( "All donations support continued development of this free software.", 'all-in-one-seo-pack'),
			"license_key"			=> __( "This will be the license key received when the product was purchased. This is used for automatic upgrades.", 'all-in-one-seo-pack'),
			"can"					=> __( "This option will automatically generate Canonical URLs for your entire WordPress installation.  This will help to prevent duplicate content penalties by <a href=\'http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html\' target=\'_blank\'>Google</a>.", 'all-in-one-seo-pack'),
			"no_paged_canonical_links"=> __( "Checking this option will set the Canonical URL for all paginated content to the first page.", 'all-in-one-seo-pack'),
			"customize_canonical_links"=> __( "Checking this option will allow you to customize Canonical URLs for specific posts.", 'all-in-one-seo-pack'),
			"can_set_protocol" => __( "Set protocol for canonical URLs.", 'all-in-one-seo-pack' ),
			"use_original_title"	=> __( "Use wp_title to get the title used by the theme; this is disabled by default. If you use this option, set your title formats appropriately, as your theme might try to do its own title SEO as well.", 'all-in-one-seo-pack' ),
			"do_log"				=> __( "Check this and All in One SEO Pack will create a log of important events (all-in-one-seo-pack.log) in its plugin directory which might help debugging. Make sure this directory is writable.", 'all-in-one-seo-pack' ),
			"home_title"			=> __( "As the name implies, this will be the Meta Title of your homepage. This is independent of any other option. If not set, the default Site Title (found in WordPress under Settings, General, Site Title) will be used.", 'all-in-one-seo-pack' ),
			"home_description"		=> __( "This will be the Meta Description for your homepage. This is independent of any other option. The default is no Meta Description at all if this is not set.", 'all-in-one-seo-pack' ),
			"home_keywords"			=> __( "Enter a comma separated list of your most important keywords for your site that will be written as Meta Keywords on your homepage. Don\'t stuff everything in here.", 'all-in-one-seo-pack' ),
			"use_static_home_info"	=> __( "Checking this option uses the title, description, and keywords set on your static Front Page.", 'all-in-one-seo-pack' ),
			"togglekeywords"		=> __( "This option allows you to toggle the use of Meta Keywords throughout the whole of the site.", 'all-in-one-seo-pack' ),
			"use_categories"		=> __( "Check this if you want your categories for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).", 'all-in-one-seo-pack' ),
			"use_tags_as_keywords"	=> __( "Check this if you want your tags for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).", 'all-in-one-seo-pack' ),
			"dynamic_postspage_keywords"=> 	__( "Check this if you want your keywords on your Posts page (set in WordPress under Settings, Reading, Front Page Displays) and your archive pages to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.", 'all-in-one-seo-pack'),
			"rewrite_titles"		=> __( "Note that this is all about the title tag. This is what you see in your browser's window title bar. This is NOT visible on a page, only in the title bar and in the source code. If enabled, all page, post, category, search and archive page titles get rewritten. You can specify the format for most of them. For example: Using the default post title format below, Rewrite Titles will write all post titles as 'Post Title | Blog Name'. If you have manually defined a title using All in One SEO Pack, this will become the title of your post in the format string.", 'all-in-one-seo-pack' ),
			"cap_titles"			=> __( "Check this and Search Page Titles and Tag Page Titles will have the first letter of each word capitalized.", 'all-in-one-seo-pack' ),
			"cap_cats"				=> __( "Check this and Category Titles will have the first letter of each word capitalized.", 'all-in-one-seo-pack'),
			"home_page_title_format"		=>
				__( "This controls the format of the title tag for your Home Page.<br />The following macros are supported:", 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_login% - This page's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_nicename% - This page's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_firstname% - This page's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_lastname% - This page's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li>' .
				'</ul>',
			"page_title_format"		=>
				__( "This controls the format of the title tag for Pages.<br />The following macros are supported:", 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%page_title% - The original title of the page', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_login% - This page's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_nicename% - This page's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_firstname% - This page's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%page_author_lastname% - This page's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li>' .
				'</ul>',
			"post_title_format"		=>
				__( "This controls the format of the title tag for Posts.<br />The following macros are supported:", 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_title% - The (main) category of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category% - Alias for %category_title%', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_login% - This post's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_nicename% - This post's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_firstname% - This post's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
				__( "%post_author_lastname% - This post's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li>' .
				'</ul>',
			"category_title_format"	=>
				__( "This controls the format of the title tag for Category Archives.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_title% - The original title of the category', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%category_description% - The description of the category', 'all-in-one-seo-pack' ) . '</li></ul>',
			"archive_title_format"	=>
				__( "This controls the format of the title tag for Custom Post Archives.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%archive_title - The original archive title given by wordpress', 'all-in-one-seo-pack' ) . '</li></ul>',
			"date_title_format"	=>
				__( "This controls the format of the title tag for Date Archives.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%date% - The original archive title given by wordpress, e.g. "2007" or "2007 August"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%day% - The original archive day given by wordpress, e.g. "17"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%month% - The original archive month given by wordpress, e.g. "August"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%year% - The original archive year given by wordpress, e.g. "2007"', 'all-in-one-seo-pack' ) . '</li></ul>',
			"author_title_format"	=>
				__( "This controls the format of the title tag for Author Archives.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%author% - The original archive title given by wordpress, e.g. "Steve" or "John Smith"', 'all-in-one-seo-pack' ) . '</li></ul>',
			"tag_title_format"	=>
				__( "This controls the format of the title tag for Tag Archives.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%tag% - The name of the tag', 'all-in-one-seo-pack' ) . '</li></ul>',
			"search_title_format"	=>
				__( "This controls the format of the title tag for the Search page.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%search% - What was searched for', 'all-in-one-seo-pack' ) . '</li></ul>',
			"description_format"	=> __( "This controls the format of Meta Descriptions.The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%description% - The original description as determined by the plugin, e.g. the excerpt if one is set or an auto-generated one if that option is set', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%post_title% - The original title of the post', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%wp_title% - The original wordpress title, e.g. post_title for posts', 'all-in-one-seo-pack' ) . '</li></ul>',
			"404_title_format"	=> __( "This controls the format of the title tag for the 404 page.<br />The following macros are supported:", 'all-in-one-seo-pack' ) .
				'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%request_url% - The original URL path, like "/url-that-does-not-exist/"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%request_words% - The URL path in human readable form, like "Url That Does Not Exist"', 'all-in-one-seo-pack' ) . '</li><li>' .
				__( '%404_title% - Additional 404 title input"', 'all-in-one-seo-pack' ) . '</li></ul>',
			"paged_format"	=> __( "This string gets appended/prepended to titles of paged index pages (like home or archive pages).", 'all-in-one-seo-pack' )
				. __( 'The following macros are supported:', 'all-in-one-seo-pack' )
				. '<ul><li>' . __( '%page% - The page number', 'all-in-one-seo-pack' ) . '</li></ul>',
			"enablecpost"			=> __( "Check this if you want to use All in One SEO Pack with any Custom Post Types on this site.", 'all-in-one-seo-pack' ),
			"cpostadvanced" 		=> __( "This will show or hide the advanced options for SEO for Custom Post Types.", 'all-in-one-seo-pack' ),
			"cpostactive" 			=> __( "Use these checkboxes to select which Post Types you want to use All in One SEO Pack with.", 'all-in-one-seo-pack' ),
			"taxactive" 			=> __( "Use these checkboxes to select which Taxonomies you want to use All in One SEO Pack with.", 'all-in-one-seo-pack' ),
			"cposttitles" 			=> __( "This allows you to set the title tags for each Custom Post Type.", 'all-in-one-seo-pack' ),
			"posttypecolumns" 		=> __( "This lets you select which screens display the SEO Title, SEO Keywords and SEO Description columns.", 'all-in-one-seo-pack' ),
			"admin_bar" 			=> __( "Check this to add All in One SEO Pack to the Admin Bar for easy access to your SEO settings.", 'all-in-one-seo-pack' ),
			"custom_menu_order" 	=> __( "Check this to move the All in One SEO Pack menu item to the top of your WordPress Dashboard menu.", 'all-in-one-seo-pack' ),
			"google_verify" 		=> __( "Enter your verification code here to verify your site with Google Webmaster Tools.<br /><a href='http://semperplugins.com/documentation/google-webmaster-tools-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			"bing_verify" 			=> __( "Enter your verification code here to verify your site with Bing Webmaster Tools.<br /><a href='http://semperplugins.com/documentation/bing-webmaster-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			"pinterest_verify" 		=> __( "Enter your verification code here to verify your site with Pinterest.<br /><a href='http://semperplugins.com/documentation/pinterest-site-verification/' target='_blank'>Click here for documentation on this setting</a>", 'all-in-one-seo-pack' ),
			"google_publisher"		=> __( "Enter your Google+ Profile URL here to add the rel=“author” tag to your site for Google authorship. It is recommended that the URL you enter here should be your personal Google+ profile.  Use the Advanced Authorship Options below if you want greater control over the use of authorship.", 'all-in-one-seo-pack' ),
			"google_disable_profile"=> __( "Check this to remove the Google Plus field from the user profile screen.", 'all-in-one-seo-pack' ),
			"google_author_advanced"=> __( "Enable this to display advanced options for controlling Google Plus authorship information on your website.", 'all-in-one-seo-pack' ),
			"google_author_location"=> __( "This option allows you to control which types of pages you want to display rel=\"author\" on for Google authorship. The options include the Front Page (the homepage of your site), Posts, Pages, and any Custom Post Types. The Everywhere Else option includes 404, search, categories, tags, custom taxonomies, date archives, author archives and any other page template.", 'all-in-one-seo-pack' ),
			"google_enable_publisher"=> __( "This option allows you to control whether rel=\"publisher\" is displayed on the homepage of your site. Google recommends using this if the site is a business website.", 'all-in-one-seo-pack' ),
			"google_specify_publisher"=> __( "The Google+ profile you enter here will appear on your homepage only as the rel=\"publisher\" tag. It is recommended that the URL you enter here should be the Google+ profile for your business.", 'all-in-one-seo-pack' ),
			"google_sitelinks_search"=> __( "Add markup to display the Google Sitelinks Search Box next to your search results in Google.", 'all-in-one-seo-pack' ),
			"google_set_site_name"   => __( "Add markup to tell Google the preferred name for your website.", 'all-in-one-seo-pack' ),
			"google_connect"		=> __( "Press the connect button to connect with Google Analytics; or if already connected, press the disconnect button to disable and remove any stored analytics credentials.", 'all-in-one-seo-pack' ),
			"google_analytics_id"	=> __( "Enter your Google Analytics ID here to track visitor behavior on your site using Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_use_universal_analytics" => __( "Use the new Universal Analytics tracking code for Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_advanced_options"	=> __( "Check to use advanced Google Analytics options.", 'all-in-one-seo-pack' ),
			"ga_domain"				=> __( "Enter your domain name without the http:// to set your cookie domain.", 'all-in-one-seo-pack' ),
			"ga_multi_domain"		=> __( "Use this option to enable tracking of multiple or additional domains.", 'all-in-one-seo-pack' ),
			"ga_addl_domains"		=> __( "Add a list of additional domains to track here.  Enter one domain name per line without the http://.", 'all-in-one-seo-pack' ),
			"ga_anonymize_ip"		=> __( "This enables support for IP Anonymization in Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_display_advertising"=> __( "This enables support for the Display Advertiser Features in Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_exclude_users"		=> __( "Exclude logged-in users from Google Analytics tracking by role.", 'all-in-one-seo-pack' ),
			"ga_track_outbound_links"=> __( "Check this if you want to track outbound links with Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_link_attribution"=> __( "This enables support for the Enhanced Link Attribution in Google Analytics.", 'all-in-one-seo-pack' ),
			"ga_enhanced_ecommerce" => __( "This enables support for the Enhanced Ecommerce in Google Analytics.", 'all-in-one-seo-pack' ),
			"cpostnoindex" 			=> __( "Set the default NOINDEX setting for each Post Type.", 'all-in-one-seo-pack' ),
			"cpostnofollow" 		=> __( "Set the default NOFOLLOW setting for each Post Type.", 'all-in-one-seo-pack' ),
			"category_noindex"		=> 	__( "Check this to ask search engines not to index Category Archives. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"archive_date_noindex"	=> 	__( "Check this to ask search engines not to index Date Archives. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"archive_author_noindex"=> 	__( "Check this to ask search engines not to index Author Archives. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"tags_noindex"			=> __( "Check this to ask search engines not to index Tag Archives. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"search_noindex"		=> 	__( "Check this to ask search engines not to index the Search page. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"404_noindex"		=> 	__( "Check this to ask search engines not to index the 404 page.", 'all-in-one-seo-pack' ),
			"tax_noindex"			=> __( "Check this to ask search engines not to index custom Taxonomy archive pages. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"paginated_noindex"		=> 	__( "Check this to ask search engines not to index paginated pages/posts. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			"paginated_nofollow"		=> 	__( "Check this to ask search engines not to follow links from paginated pages/posts. Useful for avoiding duplicate content.", 'all-in-one-seo-pack' ),
			'noodp'			 	 => __( 'Check this box to ask search engines not to use descriptions from the Open Directory Project for your entire site.', 'all-in-one-seo-pack' ),
			'cpostnoodp'		 => __( "Set the default noodp setting for each Post Type.", 'all-in-one-seo-pack' ),
			'noydir'			 => __( 'Check this box to ask Yahoo! not to use descriptions from the Yahoo! directory for your entire site.', 'all-in-one-seo-pack' ),
			'cpostnoydir'		 => __( "Set the default noydir setting for each Post Type.", 'all-in-one-seo-pack' ),
			"skip_excerpt"		 => __( "Check this and your Meta Descriptions won't be generated from the excerpt.", 'all-in-one-seo-pack' ),
			"generate_descriptions"	=> __( "Check this and your Meta Descriptions will be auto-generated from your excerpt or content.", 'all-in-one-seo-pack' ),
			"run_shortcodes"	=> __( "Check this and shortcodes will get executed for descriptions auto-generated from content.", 'all-in-one-seo-pack' ),
			"hide_paginated_descriptions"=> __( "Check this and your Meta Descriptions will be removed from page 2 or later of paginated content.", 'all-in-one-seo-pack' ),
			"dont_truncate_descriptions"=> __( "Check this to prevent your Description from being truncated regardless of its length.", 'all-in-one-seo-pack' ),
			"schema_markup"=> __( "Check this to support Schema.org markup, i.e., itemprop on supported metadata.", 'all-in-one-seo-pack' ),
			"unprotect_meta"		=> __( "Check this to unprotect internal postmeta fields for use with XMLRPC. If you don't know what that is, leave it unchecked.", 'all-in-one-seo-pack' ),
			"ex_pages" 				=> 	__( "Enter a comma separated list of pages here to be excluded by All in One SEO Pack.  This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: <em>/forum/, /contact/</em>  For instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is add forum or /forum or /forum/ or and any URL with the word \"forum\" in it, such as http://mysite.com/forum or http://mysite.com/forum/someforumpage here and it will be excluded from All in One SEO Pack.", 'all-in-one-seo-pack' ),
			"post_meta_tags"		=> __( "What you enter here will be copied verbatim to the header of all Posts. You can enter whatever additional headers you want here, even references to stylesheets.", 'all-in-one-seo-pack' ),
			"page_meta_tags"		=> __( "What you enter here will be copied verbatim to the header of all Pages. You can enter whatever additional headers you want here, even references to stylesheets.", 'all-in-one-seo-pack' ),
			"front_meta_tags"		=> 	__( "What you enter here will be copied verbatim to the header of the front page if you have set a static page in Settings, Reading, Front Page Displays. You can enter whatever additional headers you want here, even references to stylesheets. This will fall back to using Additional Page Headers if you have them set and nothing is entered here.", 'all-in-one-seo-pack' ),
			"home_meta_tags"		=> 	__( "What you enter here will be copied verbatim to the header of the home page if you have Front page displays your latest posts selected in Settings, Reading.  It will also be copied verbatim to the header on the Posts page if you have one set in Settings, Reading. You can enter whatever additional headers you want here, even references to stylesheets.", 'all-in-one-seo-pack' ),
		);

		$this->help_anchors = Array(
			'license_key' => '#license-key',
			'can'		  => '#canonical-urls',
			'no_paged_canonical_links' => '#no-pagination-for-canonical-urls',
			'customize_canonical_links' => '#enable-custom-canonical-urls',
			'use_original_title' => '#use-original-title',
			'schema_markup' => '#use-schema-markup',
			'do_log' => '#log-important-events',
			'home_title' => '#home-title',
			'home_description' => '#home-description',
			'home_keywords' => '#home-keywords',
			'togglekeywords' => '#use-keywords',
			'use_categories' => '#use-categories-for-meta-keywords',
			'use_tags_as_keywords' => '#use-tags-for-meta-keywords',
			'dynamic_postspage_keywords' => '#dynamically-generate-keywords-for-posts-page',
			'rewrite_titles' => '#rewrite-titles',
			'cap_titles' => '#capitalize-titles',
			'home_page_title_format' => '#title-format-fields',
			'page_title_format' => '#title-format-fields',
			'post_title_format' => '#title-format-fields',
			'category_title_format' => '#title-format-fields',
			'archive_title_format' => '#title-format-fields',
			'date_title_format' => '#title-format-fields',
			'author_title_format' => '#title-format-fields',
			'tag_title_format' => '#title-format-fields',
			'search_title_format' => '#title-format-fields',
			'404_title_format' => '#title-format-fields',
			'enablecpost' => '#seo-for-custom-post-types',
			'cpostadvanced' => '#enable-advanced-options',
			'cpostactive' => '#seo-on-only-these-post-types',
			'taxactive' => '#seo-on-only-these-taxonomies',
			'cposttitles' => '#custom-titles',
			'posttypecolumns' => '#show-column-labels-for-custom-post-types',
			'admin_bar' => '#display-menu-in-admin-bar',
			'custom_menu_order' => '#display-menu-at-the-top',
			'google_verify' => '',
			'bing_verify' => '',
			'pinterest_verify' => '',
			'google_publisher' => '#google-plus-default-profile',
			'google_disable_profile' => '#disable-google-plus-profile',
			'google_author_advanced' => '#advanced-authorship-options',
			'google_author_location' => '#display-google-authorship',
			'google_enable_publisher' => '#display-publisher-meta-on-front-page',
			'google_specify_publisher' => '#specify-publisher-url',
			'google_analytics_id' => 'http://semperplugins.com/documentation/setting-up-google-analytics/',
			'ga_use_universal_analytics' => '#use-universal-analytics',
			'ga_domain' => '#tracking-domain',
			'ga_multi_domain' => '#track-multiple-domains-additional-domains',
			'ga_addl_domains' => '#track-multiple-domains-additional-domains',
			'ga_anonymize_ip' => '#anonymize-ip-addresses',
			'ga_display_advertising' => '#display-advertiser-tracking',
			'ga_exclude_users' => '#exclude-users-from-tracking',
			'ga_track_outbound_links' => '#track-outbound-links',
			'ga_link_attribution' => '#enhanced-link-attribution',
			'ga_enhanced_ecommerce' => '#enhanced-ecommerce',
			'cpostnoindex' => '#use-noindex-for-paginated-pages-posts',
			'cpostnofollow' => '#use-nofollow-for-paginated-pages-posts',
			'noodp' => '#exclude-site-from-the-open-directory-project',
			'noydir' => '#exclude-site-from-yahoo-directory',
			'generate_descriptions' => '#autogenerate-descriptions',
			'run_shortcodes' => '#run-shortcodes-in-autogenerated-descriptions',
			'hide_paginated_descriptions' => '#remove-descriptions-for-paginated-pages',
			'dont_truncate_descriptions' => '#never-shorten-long-descriptions',
			'unprotect_meta' => '#unprotect-post-meta-fields',
			'ex_pages' => '#exclude-pages',
			'post_meta_tags' => '#additional-post-headers',
			'page_meta_tags' => '#additional-page-headers',
			'front_meta_tags' => '#additional-front-page-headers',
			'home_meta_tags' => '#additional-blog-page-headers'
		);

		$meta_help_text = Array(
			'snippet'			 => __( 'A preview of what this page might look like in search engine results.', 'all-in-one-seo-pack' ),
			'title'				 => __( 'A custom title that shows up in the title tag for this page.', 'all-in-one-seo-pack' ),
			'description'		 => __( 'The META description for this page. This will override any autogenerated descriptions.', 'all-in-one-seo-pack' ),
			'keywords'			 => __( 'A comma separated list of your most important keywords for this page that will be written as META keywords.', 'all-in-one-seo-pack' ),
			'custom_link'		 => __( "Override the canonical URLs for this post.", 'all-in-one-seo-pack'),
			'noindex'			 => __( 'Check this box to ask search engines not to index this page.', 'all-in-one-seo-pack' ),
			'nofollow'			 => __( 'Check this box to ask search engines not to follow links from this page.', 'all-in-one-seo-pack' ),
			'noodp'			 	 => __( 'Check this box to ask search engines not to use descriptions from the Open Directory Project for this page.', 'all-in-one-seo-pack' ),
			'noydir'			 => __( 'Check this box to ask Yahoo! not to use descriptions from the Yahoo! directory for this page.', 'all-in-one-seo-pack' ),
			'titleatr'			 => __( 'Set the title attribute for menu links.', 'all-in-one-seo-pack' ),
			'menulabel'			 => __( 'Set the label for this page menu item.', 'all-in-one-seo-pack' ),
			'sitemap_exclude'	 => __( "Don't display this page in the sitemap.", 'all-in-one-seo-pack' ),
			'disable'			 => __( 'Disable SEO on this page.', 'all-in-one-seo-pack' ),
			'disable_analytics'	 => __( 'Disable Google Analytics on this page.', 'all-in-one-seo-pack' )
		);

		$this->default_options = array(
			"license_key" => Array(
				'name' => __( 'License Key:', 'all-in-one-seo-pack' ),
				'type' => 'text' ),
			"donate" => Array(
				'name' => __( 'I enjoy this plugin and have made a donation:', 'all-in-one-seo-pack' ) ),
			"home_title"=> Array(
				'name' => __( 'Home Title:', 'all-in-one-seo-pack' ),
				'default' => null, 'type' => 'textarea', 'sanitize' => 'text',
				'count' => true, 'rows' => 1, 'cols' => 60,
				'condshow' => Array( "aiosp_use_static_home_info" => 0 ) ),
		   "home_description"=> Array(
				'name' => __( 'Home Description:', 'all-in-one-seo-pack' ),
				'default' => '', 'type' => 'textarea', 'sanitize' => 'text',
				'count' => true, 'cols' => 80, 'rows' => 2,
				'condshow' => Array( "aiosp_use_static_home_info" => 0 ) ),
		   "togglekeywords" => Array(
				'name' => __( 'Use Keywords:', 'all-in-one-seo-pack' ),
				'default' =>  1,
				'type' => 'radio',
			    'initial_options' => Array( 0 => __( 'Enabled', 'all-in-one-seo-pack' ),
			                                1 => __( 'Disabled', 'all-in-one-seo-pack' ) )
				),
		   "home_keywords"=> Array(
				'name' => __( 'Home Keywords (comma separated):', 'all-in-one-seo-pack' ),
				'default' => null, 'type' => 'textarea', 'sanitize' => 'text',
				'condshow' => Array( "aiosp_togglekeywords" => 0, "aiosp_use_static_home_info" => 0 ) ),
		   "use_static_home_info" => Array(
				'name' => __( "Use Static Front Page Instead", 'all-in-one-seo-pack' ),
				'default' => 0,
				'type' => 'radio',
				'initial_options' => Array( 1 => __( 'Enabled', 'all-in-one-seo-pack' ),
			                                0 => __( 'Disabled', 'all-in-one-seo-pack' ) )
			),
		   "can"=> Array(
				'name' => __( 'Canonical URLs:', 'all-in-one-seo-pack' ),
				'default' => 1),
		   "no_paged_canonical_links"=> Array(
				'name' => __( 'No Pagination for Canonical URLs:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => Array( "aiosp_can" => 'on' ) ),
		   "customize_canonical_links"	=> Array(
				'name' => __( 'Enable Custom Canonical URLs:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => Array( "aiosp_can" => 'on' ) ),
			"can_set_protocol" => Array(
				'name' => __( 'Set Protocol For Canonical URLs:', 'all-in-one-seo-pack' ),
				'type' => 'radio',
				'default' => 'auto',
				'initial_options' => Array( 'auto' => __( 'Auto', 'all-in-one-seo-pack' ),
											'http' => __( 'HTTP', 'all-in-one-seo-pack' ),
											'https' => __( 'HTTPS', 'all-in-one-seo-pack' ) ),
				'condshow' => Array( "aiosp_can" => 'on' )
				),
			"rewrite_titles"=> Array(
				'name' => __( 'Rewrite Titles:', 'all-in-one-seo-pack' ),
				'default' => 1,
				'type' => 'radio',
				'initial_options' => Array( 1 => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) )
				),
			"force_rewrites"=> Array(
				'name' => __( 'Force Rewrites:', 'all-in-one-seo-pack' ),
				'default' => 1,
				'type' => 'hidden',
				'prefix' => $this->prefix,
				'initial_options' => Array( 1 => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) )
				),
			"use_original_title"=> Array(
					'name' => __( 'Use Original Title:', 'all-in-one-seo-pack' ),
					'type' => 'radio',
					'default' => 0,
					'initial_options' => Array( 1 => __( 'Enabled', 'all-in-one-seo-pack' ),
												0 => __( 'Disabled', 'all-in-one-seo-pack' ) )
				),
			"cap_titles"=> Array(
				'name' => __( 'Capitalize Tag and Search Titles:', 'all-in-one-seo-pack' ), 'default' => 1),
			"cap_cats"=> Array(
				'name' => __( 'Capitalize Category Titles:', 'all-in-one-seo-pack' ), 'default' => 1),
		   "home_page_title_format"=> Array(
				'name' => __( 'Home Page Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%page_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "page_title_format"=> Array(
				'name' => __( 'Page Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%page_title% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "post_title_format"=> Array(
				'name' => __( 'Post Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%post_title% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "category_title_format"=> Array(
				'name' => __( 'Category Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%category_title% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "archive_title_format"=> Array(
				'name' => __( 'Archive Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%archive_title% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "date_title_format"=> Array(
				'name' => __( 'Date Archive Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%date% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
			"author_title_format"=> Array(
				'name' => __( 'Author Archive Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%author% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "tag_title_format"=> Array(
				'name' => __( 'Tag Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%tag% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "search_title_format"=> Array(
				'name' => __( 'Search Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%search% | %blog_title%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "description_format"=> Array(
				'name' => __( 'Description Format', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => '%description%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
		   "404_title_format"=> Array(
				'name' => __( '404 Title Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => 'Nothing found for %request_words%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
			"paged_format"=> Array(
				'name' => __( 'Paged Format:', 'all-in-one-seo-pack' ),
				'type' => 'text', 'default' => ' - Part %page%',
				'condshow' => Array( "aiosp_rewrite_titles" => 1 ) ),
			"enablecpost"=> Array(
				'name' => __( 'SEO for Custom Post Types:', 'all-in-one-seo-pack' ),
				'default' => 'on',
				'type' => 'radio',
				'initial_options' => Array( 'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) )
				),
			"cpostactive" => Array(
				'name' => __( 'SEO on only these post types:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array('post', 'page'),
				'condshow' => Array( 'aiosp_enablecpost' => 'on' )
				),
			"taxactive" => Array(
				'name' => __( 'SEO on only these taxonomies:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array('category', 'post_tag'),
				'condshow' => Array( 'aiosp_enablecpost' => 'on' )
				),
			"cpostadvanced" => Array(
				'name' => __( 'Enable Advanced Options:', 'all-in-one-seo-pack' ),
				'default' => 0, 'type' => 'radio',
				'initial_options' => Array( 'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) ),
				'label' => null,
				'condshow' => Array( "aiosp_enablecpost" => 'on' )
				),
			"cpostnoindex" => Array(
				'name' => __( 'Default to NOINDEX:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array(),
				),
			"cpostnofollow" => Array(
				'name' => __( 'Default to NOFOLLOW:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array(),
				),
			"cpostnoodp"=> Array(
					'name' => __( 'Default to NOODP:', 'all-in-one-seo-pack' ),
					'type' => 'multicheckbox', 'default' => array(),
				),
			"cpostnoydir"=> Array(
				'name' => __( 'Default to NOYDIR:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array(),
				),
			"cposttitles" => Array(
				'name' => __( 'Custom titles:', 'all-in-one-seo-pack' ),
				'type' => 'checkbox', 'default' => 0,
				'condshow' => Array( "aiosp_rewrite_titles" => 1, 'aiosp_enablecpost' => 'on', 'aiosp_cpostadvanced' => 'on' )
				),
			"posttypecolumns" => Array(
				'name' => __( 'Show Column Labels for Custom Post Types:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' =>  array('post', 'page'),
				'condshow' => Array( 'aiosp_enablecpost' => 'on' ) ),
			"admin_bar" => Array(
				'name' => __( 'Display Menu In Admin Bar:', 'all-in-one-seo-pack' ), 'default' => 'on',
				),
			"custom_menu_order" => Array(
				'name' => __( 'Display Menu At The Top:', 'all-in-one-seo-pack' ), 'default' => 'on',
				),
			"google_verify" => Array(
				'name' => __( 'Google Webmaster Tools:', 'all-in-one-seo-pack' ), 'default' => '', 'type' => 'text'
				),
			"bing_verify" => Array(
				'name' => __( 'Bing Webmaster Center:', 'all-in-one-seo-pack' ), 'default' => '', 'type' => 'text'
				),
			"pinterest_verify" => Array(
				'name' => __( 'Pinterest Site Verification:', 'all-in-one-seo-pack' ), 'default' => '', 'type' => 'text'
				),
			"google_publisher"=> Array(
				'name' => __( 'Google Plus Default Profile:', 'all-in-one-seo-pack' ), 'default' => '', 'type' => 'text'
				),
			"google_disable_profile"=> Array(
				'name' => __( 'Disable Google Plus Profile:', 'all-in-one-seo-pack' ), 'default' => 0, 'type' => 'checkbox'
				),
			"google_sitelinks_search" => Array(
					'name' => __( 'Display Sitelinks Search Box:', 'all-in-one-seo-pack' )
			),
			"google_set_site_name" => Array(
					'name' => __( 'Set Preferred Site Name:', 'all-in-one-seo-pack' )
			),
			"google_specify_site_name" => Array(
					'name' => __( 'Specify A Preferred Name:', 'all-in-one-seo-pack' ),
					'type' => 'text',
					'placeholder' => $blog_name,
					'condshow' => Array( 'aiosp_google_set_site_name' => 'on' )
			),
			"google_author_advanced" => Array(
					'name' => __( 'Advanced Authorship Options:', 'all-in-one-seo-pack' ),
					'default' => 0, 'type' => 'radio',
					'initial_options' => Array( 'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
												0 => __( 'Disabled', 'all-in-one-seo-pack' ) ),
					'label' => null
					),
			"google_author_location"=> Array(
				'name' => __( 'Display Google Authorship:', 'all-in-one-seo-pack' ), 'default' => array( 'all' ), 'type' => 'multicheckbox',
				'condshow' => Array( 'aiosp_google_author_advanced' => 'on' )
				),
			"google_enable_publisher" => Array(
				'name' => __( 'Display Publisher Meta on Front Page:', 'all-in-one-seo-pack' ),
				'default' => 'on', 'type' => 'radio',
				'initial_options' => Array( 'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) ),
				'condshow' => Array( 'aiosp_google_author_advanced' => 'on' )
				),
			"google_specify_publisher" => Array(
					'name' => __( 'Specify Publisher URL:', 'all-in-one-seo-pack' ), 'type' => 'text',
					'condshow' => Array( 'aiosp_google_author_advanced' => 'on', 'aiosp_google_enable_publisher' => 'on' )
				),
//			"google_connect"=>Array( 'name' => __( 'Connect With Google Analytics', 'all-in-one-seo-pack' ), ),
			"google_analytics_id"=> Array(
				'name' => __( 'Google Analytics ID:', 'all-in-one-seo-pack' ),
				'default' => null, 'type' => 'text', 'placeholder' => 'UA-########-#' ),
			"ga_use_universal_analytics" => Array(
				'name' => __( 'Use Universal Analytics:', 'all-in-one-seo-pack' ),
				'default' => 0,
				 'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ) ) ),
			"ga_advanced_options"=> Array(
				'name' => __( 'Advanced Analytics Options:', 'all-in-one-seo-pack' ),
				'default' => 'on',
				'type' => 'radio',
				'initial_options' => Array( 'on' => __( 'Enabled', 'all-in-one-seo-pack' ),
											0 => __( 'Disabled', 'all-in-one-seo-pack' ) ),
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ) ) ),
			"ga_domain"=> Array(
				'name' => __( 'Tracking Domain:', 'all-in-one-seo-pack' ),
				'type' => 'text',
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on') ),
			"ga_multi_domain"=> Array(
				'name' => __( 'Track Multiple Domains:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_addl_domains" => Array(
								'name' => __( 'Additional Domains:', 'all-in-one-seo-pack' ),
								'type' => 'textarea',
								'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on', 'aiosp_ga_multi_domain' => 'on' ) ),
			"ga_anonymize_ip"=> Array(
				'name' => __( 'Anonymize IP Addresses:', 'all-in-one-seo-pack' ),
				'type' => 'checkbox',
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_display_advertising"=> Array(
				'name' => __( 'Display Advertiser Tracking:', 'all-in-one-seo-pack' ),
				'type' => 'checkbox',
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_exclude_users"=> Array(
				'name' => __( 'Exclude Users From Tracking:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox',
				'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_track_outbound_links"=> Array(
				'name' => __( 'Track Outbound Links:', 'all-in-one-seo-pack' ),
				'default' => 0,
				 'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_link_attribution"=> Array(
				'name' => __( 'Enhanced Link Attribution:', 'all-in-one-seo-pack' ),
				'default' => 0,
				 'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_advanced_options' => 'on' ) ),
			"ga_enhanced_ecommerce"=> Array(
				'name' => __( 'Enhanced Ecommerce:', 'all-in-one-seo-pack' ),
				'default' => 0,
				 'condshow' => Array( 'aiosp_google_analytics_id' => Array( 'lhs' => 'aiosp_google_analytics_id', 'op' => '!=', 'rhs' => '' ), 'aiosp_ga_use_universal_analytics' => 'on', 'aiosp_ga_advanced_options' => 'on' ) ),
			"use_categories"=> Array(
				'name' => __( 'Use Categories for META keywords:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => Array( "aiosp_togglekeywords" => 0 ) ),
			"use_tags_as_keywords" => Array(
				'name' => __( 'Use Tags for META keywords:', 'all-in-one-seo-pack' ),
				'default' => 1,
				'condshow' => Array( "aiosp_togglekeywords" => 0 ) ),
			"dynamic_postspage_keywords"=> Array(
				'name' => __( 'Dynamically Generate Keywords for Posts Page/Archives:', 'all-in-one-seo-pack' ),
				'default' => 1,
				'condshow' => Array( "aiosp_togglekeywords" => 0 ) ),
			"category_noindex"=> Array(
				'name' => __( 'Use noindex for Categories:', 'all-in-one-seo-pack' ),
				'default' => 1),
			"archive_date_noindex"=> Array(
				'name' => __( 'Use noindex for Date Archives:', 'all-in-one-seo-pack' ),
				'default' => 1),
			"archive_author_noindex"=> Array(
				'name' => __( 'Use noindex for Author Archives:', 'all-in-one-seo-pack' ),
				'default' => 1),
			"tags_noindex"=> Array(
				'name' => __( 'Use noindex for Tag Archives:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"search_noindex"=> Array(
				'name' => __( 'Use noindex for the Search page:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"404_noindex"=> Array(
				'name' => __( 'Use noindex for the 404 page:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"tax_noindex"=> Array(
				'name' => __( 'Use noindex for Taxonomy Archives:', 'all-in-one-seo-pack' ),
				'type' => 'multicheckbox', 'default' => array(),
				'condshow' => Array( 'aiosp_enablecpost' => 'on', 'aiosp_cpostadvanced' => 'on' )
				),
			"paginated_noindex"	=> Array(
				'name' => __( 'Use noindex for paginated pages/posts:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"paginated_nofollow"=> Array(
				'name' => __( 'Use nofollow for paginated pages/posts:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"noodp"=> Array(
				'name' => __( 'Exclude site from the Open Directory Project:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"noydir"=> Array(
				'name' => __( 'Exclude site from Yahoo! Directory:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"skip_excerpt"=> Array(
				'name' => __( 'Avoid Using The Excerpt In Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0 ),
			"generate_descriptions"=> Array(
				'name' => __( 'Autogenerate Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 1),
			"run_shortcodes"=> Array(
				'name' => __( 'Run Shortcodes In Autogenerated Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0,
				'condshow' => Array( 'aiosp_generate_descriptions' => 'on' ) ),
			"hide_paginated_descriptions"=> Array(
				'name' => __( 'Remove Descriptions For Paginated Pages:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"dont_truncate_descriptions"=> Array(
				'name' => __( 'Never Shorten Long Descriptions:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"schema_markup"=> Array(
				'name' => __( 'Use Schema.org Markup', 'all-in-one-seo-pack' ),
				'default' => 1),
			"unprotect_meta"=> Array(
				'name' => __( 'Unprotect Post Meta Fields:', 'all-in-one-seo-pack' ),
				'default' => 0),
			"ex_pages" => Array(
				'name' => __( 'Exclude Pages:', 'all-in-one-seo-pack' ),
				'type' => 'textarea', 'default' =>  '' ),
			"post_meta_tags"=> Array(
				'name' => __( 'Additional Post Headers:', 'all-in-one-seo-pack' ),
				'type' => 'textarea', 'default' => '', 'sanitize' => 'default' ),
			"page_meta_tags"=> Array(
				'name' => __( 'Additional Page Headers:', 'all-in-one-seo-pack' ),
				'type' => 'textarea', 'default' => '', 'sanitize' => 'default' ),
			"front_meta_tags"=> Array(
				'name' => __( 'Additional Front Page Headers:', 'all-in-one-seo-pack' ),
				'type' => 'textarea', 'default' => '', 'sanitize' => 'default' ),
			"home_meta_tags"=> Array(
				'name' => __( 'Additional Blog Page Headers:', 'all-in-one-seo-pack' ),
				'type' => 'textarea', 'default' => '', 'sanitize' => 'default' ),
			"do_log"=> Array(
				'name' => __( 'Log important events:', 'all-in-one-seo-pack' ),
				'default' => null ),
			);

			if ( AIOSEOPPRO ) {
				unset($this->default_options['donate']);
				} else {
				unset($this->default_options['license_key']);
				unset($this->default_options['taxactive']);
				}

			$this->locations = Array(
					'default' => Array( 'name' => $this->name, 'prefix' => 'aiosp_', 'type' => 'settings', 'options' => null ),
				    'aiosp' => Array( 'name' => $this->plugin_name, 'type' => 'metabox', 'prefix' => '', 'help_link' => 'http://semperplugins.com/sections/postpage-settings/',
																	'options' => Array( 'edit', 'nonce-aioseop-edit', AIOSEOPPRO ? 'support' : 'upgrade' , 'snippet', 'title', 'description', 'keywords', 'custom_link', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel', 'sitemap_exclude', 'disable', 'disable_analytics' ),
																	'default_options' => Array(
																		'edit' 				 => Array( 'type' => 'hidden', 'default' => 'aiosp_edit', 'prefix' => true, 'nowrap' => 1 ),
																		'nonce-aioseop-edit' => Array( 'type' => 'hidden', 'default' => null, 'prefix' => false, 'nowrap' => 1 ),
																		'upgrade' 			 => Array( 'type' => 'html', 'label' => 'none',
																										'default' => aiosp_common::get_upgrade_hyperlink( 'meta', __('Upgrade to All in One SEO Pack Pro Version', 'all-in-one-seo-pack'), __('UPGRADE TO PRO VERSION', 'all-in-one-seo-pack'), '_blank' )
																					 		),
																		'support' 			 => Array( 'type' => 'html', 'label' => 'none',
																										'default' => '<a target="_blank" href="http://semperplugins.com/support/">'
																										. __( 'Support Forum', 'all-in-one-seo-pack' ) . '</a>'
																					 		),
																		'snippet'			 => Array( 'name' => __( 'Preview Snippet', 'all-in-one-seo-pack' ), 'type' => 'custom', 'label' => 'top',
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
																									<div class="preview_snippet"><div id="aioseop_snippet"><h3><a>%s</a></h3><div><div><cite id="aioseop_snippet_link">%s</cite></div><span id="aioseop_snippet_description">%s</span></div></div></div>' ),
																		'title'				 => Array( 'name' => __( 'Title', 'all-in-one-seo-pack' ), 'type' => 'text', 'count' => true, 'size' => 60 ),
																		'description'		 => Array( 'name' => __( 'Description', 'all-in-one-seo-pack' ), 'type' => 'textarea', 'count' => true, 'cols' => 80, 'rows' => 2 ),

																		'keywords'			 => Array( 'name' => __( 'Keywords (comma separated)', 'all-in-one-seo-pack' ), 'type' => 'text' ),
																		'custom_link'		 => Array( 'name' => __( 'Custom Canonical URL', 'all-in-one-seo-pack' ), 'type' => 'text', 'size' => 60 ),
																		'noindex'			 => Array( 'name' => __( "Robots Meta NOINDEX", 'all-in-one-seo-pack' ), 'default' => '' ),
																		'nofollow'			 => Array( 'name' => __( "Robots Meta NOFOLLOW", 'all-in-one-seo-pack' ), 'default' => '' ),
																		'noodp'			 	 => Array( 'name' => __( "Robots Meta NOODP", 'all-in-one-seo-pack' ) ),
																		'noydir'			 => Array( 'name' => __( "Robots Meta NOYDIR", 'all-in-one-seo-pack' ) ),
																		'titleatr'			 => Array( 'name' => __( 'Title Attribute', 'all-in-one-seo-pack' ), 'type' => 'text', 'size' => 60 ),
																		'menulabel'			 => Array( 'name' => __( 'Menu Label', 'all-in-one-seo-pack' ), 'type' => 'text', 'size' => 60 ),
																		'sitemap_exclude'	 => Array( 'name' => __( 'Exclude From Sitemap', 'all-in-one-seo-pack' ) ),
																		'disable'			 => Array( 'name' => __( 'Disable on this page/post', 'all-in-one-seo-pack' ) ),
																		'disable_analytics'	 => Array( 'name' => __( 'Disable Google Analytics', 'all-in-one-seo-pack' ), 'condshow' => Array( 'aiosp_disable' => 'on' ) ) ),
																	'display' => null )
				);

			if ( !empty( $meta_help_text ) )
				foreach( $meta_help_text as $k => $v )
					$this->locations['aiosp']['default_options'][$k]['help_text'] = $v;

			$this->layout = Array(
				'default' => Array(
						'name' => __( 'General Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/general-settings/',
						'options' => Array() // this is set below, to the remaining options -- pdb
					),
				'home'  => Array(
						'name' => __( 'Home Page Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/home-page-settings/',
						'options' => Array( 'home_title', 'home_description', 'home_keywords', 'use_static_home_info' )
					),
				'title'	=> Array(
						'name' => __( 'Title Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/title-settings/',
						'options' => Array( "rewrite_titles", "force_rewrites", "cap_titles", "cap_cats", "home_page_title_format", "page_title_format", "post_title_format", "category_title_format", "archive_title_format", "date_title_format", "author_title_format",
						 					"tag_title_format", "search_title_format", "description_format", "404_title_format", "paged_format" )
					),
				'cpt' => Array(
						'name' => __( 'Custom Post Type Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/custom-post-type-settings/',
						'options' => Array( "enablecpost", "cpostadvanced", "taxactive","cpostactive", "cposttitles" )
					),
				'display' => Array(
						'name' => __( 'Display Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/display-settings/',
						'options' => Array( "posttypecolumns", "admin_bar", "custom_menu_order" )
					),
				'webmaster' => Array(
						'name' => __( 'Webmaster Verification', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/sections/webmaster-verification/',
						'options' => Array( "google_verify", "bing_verify", "pinterest_verify" )
					),
				'google' => Array(
						'name' => __( 'Google Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/google-settings/',
						'options' => Array( "google_publisher", "google_disable_profile", "google_sitelinks_search", "google_set_site_name", "google_specify_site_name", "google_author_advanced", "google_author_location", "google_enable_publisher" , "google_specify_publisher",
										//	"google_connect",
											"google_analytics_id", "ga_use_universal_analytics", "ga_advanced_options", "ga_domain", "ga_multi_domain", "ga_addl_domains", "ga_anonymize_ip", "ga_display_advertising", "ga_exclude_users", "ga_track_outbound_links", "ga_link_attribution", "ga_enhanced_ecommerce" )
					),
				'noindex' => Array(
						'name' => __( 'Noindex Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/noindex-settings/',
						'options' => Array( 'cpostnoindex', 'cpostnofollow', 'cpostnoodp', 'cpostnoydir', 'category_noindex', 'archive_date_noindex', 'archive_author_noindex', 'tags_noindex', 'search_noindex', '404_noindex', 'tax_noindex', 'paginated_noindex', 'paginated_nofollow', 'noodp', 'noydir' )
					),
				'advanced' => Array(
						'name' => __( 'Advanced Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/advanced-settings/',
						'options' => Array( 'generate_descriptions', 'skip_excerpt', 'run_shortcodes', 'hide_paginated_descriptions', 'dont_truncate_descriptions', 'unprotect_meta', 'ex_pages', 'post_meta_tags', 'page_meta_tags', 'front_meta_tags', 'home_meta_tags' )
					),
				'keywords' => Array(
						'name' => __( 'Keyword Settings', 'all-in-one-seo-pack' ),
						'help_link' => 'http://semperplugins.com/documentation/keyword-settings/',
						'options' => Array( "togglekeywords", "use_categories", "use_tags_as_keywords", "dynamic_postspage_keywords" )
					)
				);

				if(!AIOSEOPPRO){
					unset($this->layout['cpt']['options']['2']);
				}

			$other_options = Array();
			foreach( $this->layout as $k => $v )
				$other_options = array_merge( $other_options, $v['options'] );

			$this->layout['default']['options'] = array_diff( array_keys( $this->default_options ), $other_options );

			if ( is_admin() ) {
				$this->add_help_text_links();
				add_action( "aioseop_global_settings_header",	Array( $this, 'display_right_sidebar' ) );
				add_action( "aioseop_global_settings_footer",	Array( $this, 'display_settings_footer' ) );
				add_action( "output_option", Array( $this, 'custom_output_option' ), 10, 2 );
				add_action('all_admin_notices', array( $this, 'visibility_warning'));
				
				if(!AIOSEOPPRO){
				//	add_action('all_admin_notices', array( $this, 'woo_upgrade_notice'));
				}
						}
						if(AIOSEOPPRO){
							add_action( 'split_shared_term', Array( $this, 'split_shared_term' ), 10, 4 );
						}
				}

//good candidate for pro dir
				function get_all_term_data( $term_id ) {
					$terms = Array();
					$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'sitemap_exclude', 'disable', 'disable_analytics', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
					foreach( $optlist as $f ) {
						$meta = get_term_meta( $term_id, '_aioseop_' . $f, true );
						if ( !empty( $meta ) ) {
							$terms['_aioseop_' . $f] = $meta;
						}
					}
					return $terms;
				}

//good candidate for pro dir
				function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id = '', $taxonomy = '' ) {
					$terms = $this->get_all_term_data( $term_id );
					if ( !empty( $terms ) ) {
						$new_terms = $this->get_all_term_data( $new_term_id );
						if ( empty( $new_terms ) ) {
							foreach( $terms as $k => $v ) {
								add_term_meta( $new_term_id, $k, $v, true );
							}
							add_term_meta( $term_id, '_aioseop_term_was_split', true, true );
						}
					}
			}

	function get_page_snippet_info() {
		static $info = Array();
		if ( !empty( $info ) )
			return $info;
		global $post, $aioseop_options, $wp_query;
		$title = $url = $description = $term = $category = '';
		$p = $post; $w = $wp_query;
		if ( !is_object( $post ) ) $post = $this->get_queried_object();
		if ( empty( $this->meta_opts ) )
			$this->meta_opts = $this->get_current_options( Array(), 'aiosp' );
		if ( !is_object( $post ) && is_admin() && !empty( $_GET ) && !empty( $_GET['post_type'] ) && !empty( $_GET['taxonomy'] ) && !empty( $_GET['tag_ID'] ) ) {
			$term = get_term_by( 'id', $_GET['tag_ID'], $_GET['taxonomy'] );
		}
		if ( is_object( $post ) ) {
			$opts = $this->meta_opts;
			$post_id = $p->ID;
			if ( empty( $post->post_modified_gmt ) )
				$wp_query = new WP_Query( array( 'p' => $post_id, 'post_type' => $post->post_type ) );
			if ( $post->post_type == 'page' )
				$wp_query->is_page = true;
			elseif ( $post->post_type == 'attachment' )
				$wp_query->is_attachment = true;
			else
				$wp_query->is_single = true;
			if ( empty( $this->is_front_page ) ) $this->is_front_page = false;
			if 	( get_option( 'show_on_front' ) == 'page' ) {
				if ( is_page() && $post->ID == get_option( 'page_on_front' ) )
					$this->is_front_page = true;
				elseif ( $post->ID == get_option( 'page_for_posts' ) )
					$wp_query->is_home = true;
			}
			$wp_query->queried_object = $post;
			if ( !empty( $post ) && !$wp_query->is_home && !$this->is_front_page ) {
				$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
				if ( empty( $title ) ) $title = $post->post_title;
			}
			$title_format = '';
			if ( empty( $title ) ) {
				$title = $this->wp_title();
			}
			$description = $this->get_main_description( $post );
			if ( empty( $title_format ) ) {
				if ( is_page() )
					$title_format = $aioseop_options['aiosp_page_title_format'];
				elseif ( is_single() || is_attachment() )
					$title_format = $this->get_post_title_format( 'post', $post );
			}
			if ( empty( $title_format ) ) {
				$title_format = '%post_title%';
			}
			$categories = $this->get_all_categories( $post_id );
			$category = '';
			if ( count( $categories ) > 0 )
				$category = $categories[0];
		} else if ( is_object( $term ) ) {
			if ( $_GET['taxonomy'] == 'category' ) {
				query_posts( Array( 'cat' => $_GET['tag_ID'] ) );
			} else if ( $_GET['taxonomy'] == 'post_tag' ) {
				query_posts( Array( 'tag' => $term->slug ) );
			} else {
				query_posts( Array( 'page' => '', $_GET['taxonomy'] => $term->slug, 'post_type' => $_GET['post_type'] ) );
			}
			if ( empty( $this->meta_opts ) )
				$this->meta_opts = $this->get_current_options( Array(), 'aiosp' );
			$title = $this->get_tax_name( $_GET['taxonomy'] );
			$title_format = $this->get_tax_title_format();
			$opts = $this->meta_opts;
			if ( !empty( $opts ) ) $description = $opts['aiosp_description'];
			if ( empty( $description ) ) $description = term_description();
			$description = $this->internationalize( $description );
		}

		$show_page = true;
		if ( !empty( $aioseop_options["aiosp_no_paged_canonical_links"] ) ) $show_page = false;
		if ( $aioseop_options['aiosp_can'] ) {
			if ( !empty( $aioseop_options['aiosp_customize_canonical_links'] ) && !empty( $opts['aiosp_custom_link'] ) ) $url = $opts['aiosp_custom_link'];
			if ( empty( $url ) )
				$url = $this->aiosp_mrt_get_url( $wp_query, $show_page );
			$url = apply_filters( 'aioseop_canonical_url', $url );
		}
		if ( !$url ) $url = get_permalink();

		$title = $this->apply_cf_fields( $title );
		$description = $this->apply_cf_fields( $description );
		$description = apply_filters( 'aioseop_description', $description );

		$keywords = $this->get_main_keywords();
		$keywords = $this->apply_cf_fields( $keywords );
		$keywords = apply_filters( 'aioseop_keywords', $keywords );

		$info = Array( 'title' => $title, 'description' => $description, 'keywords' => $keywords, 'url' => $url,
					   'title_format' => $title_format, 'category' => $category, 'w' => $wp_query, 'p' => $post );
		wp_reset_postdata();
		$wp_query = $w; $post = $p;
		return $info;
	}

	/*** Use custom callback for outputting snippet ***/
	function custom_output_option( $buf, $args ) {
		if ( $args['name'] == 'aiosp_snippet' )  {
			$args['options']['type'] = 'html';
            $args['options']['nowrap'] = false;
            $args['options']['save'] = false;
			$info = $this->get_page_snippet_info();
			extract( $info );
		} else return '';

		if ( $this->strlen( $title ) > 70 ) $title = $this->trim_excerpt_without_filters( $title, 70 ) . '...';
		if ( $this->strlen( $description ) > 156 ) $description = $this->trim_excerpt_without_filters( $description, 156 ) . '...';
		$extra_title_len = 0;
		if ( empty( $title_format ) ) {
			$title = '<span id="' . $args['name'] . '_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title ) ) ) . '</span>';
		} else {
			if ( strpos( $title_format, '%blog_title%' ) !== false ) $title_format = str_replace( '%blog_title%', get_bloginfo( 'name' ), $title_format );
			$title_format = $this->apply_cf_fields( $title_format );
			$replace_title = '<span id="' . $args['name'] . '_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title ) ) ) . '</span>';
			if ( strpos( $title_format, '%post_title%' ) !== false ) $title_format = str_replace( '%post_title%', $replace_title, $title_format );
			if ( strpos( $title_format, '%page_title%' ) !== false ) $title_format = str_replace( '%page_title%', $replace_title, $title_format );
			if ( $w->is_category || $w->is_tag || $w->is_tax ) {
				if(AIOSEOPPRO){
					if ( !empty( $_GET ) && !empty( $_GET['taxonomy'] ) && !empty( $_GET['tag_ID'] ) && function_exists( 'wp_get_split_terms' ) ) {
						$term_id = intval( $_GET['tag_ID'] );
						$was_split = get_term_meta( $term_id, '_aioseop_term_was_split', true );
						if ( !$was_split ) {
							$split_terms = wp_get_split_terms( $term_id, $_GET['taxonomy'] );
							if ( !empty( $split_terms ) ) {
								foreach ( $split_terms as $new_tax => $new_term ) {
									$this->split_shared_term( $term_id, $new_term );
								}
							}
						}
					}
				}
				if ( strpos( $title_format, '%category_title%' ) !== false ) $title_format = str_replace( '%category_title%', $replace_title, $title_format );
				if ( strpos( $title_format, '%taxonomy_title%' ) !== false ) $title_format = str_replace( '%taxonomy_title%', $replace_title, $title_format );
			} else {
				if ( strpos( $title_format, '%category%' ) !== false )		 $title_format = str_replace( '%category%', 	  $category, $title_format );
				if ( strpos( $title_format, '%category_title%' ) !== false ) $title_format = str_replace( '%category_title%', $category, $title_format );
				if ( strpos( $title_format, '%taxonomy_title%' ) !== false ) $title_format = str_replace( '%taxonomy_title%', $category, $title_format );
				if(AIOSEOPPRO){
					if ( strpos( $title_format, "%tax_" ) && !empty( $p ) ) {
						$taxes = get_object_taxonomies( $p, 'objects' );
						if ( !empty( $taxes ) )
							foreach( $taxes as $t )
								if ( strpos( $title_format, "%tax_{$t->name}%" ) ) {
									$terms = $this->get_all_terms( $p->ID, $t->name );
									$term = '';
									if ( count( $terms ) > 0 )
										$term = $terms[0];
									$title_format = str_replace( "%tax_{$t->name}%", $term, $title_format );
								}
							}
						}
					}
			if ( strpos( $title_format, '%taxonomy_description%' ) !== false ) $title_format = str_replace( '%taxonomy_description%', $description, $title_format );

			$title_format = preg_replace( '/%([^%]*?)%/', '', $title_format );
			$title = $title_format;
			$extra_title_len = strlen( str_replace( $replace_title, '', $title_format ) );
		}

		$args['value'] = sprintf( $args['value'], $title, esc_url( $url ), esc_attr( wp_strip_all_tags( $description ) ) );
		$extra_title_len = (int)$extra_title_len;
		$args['value'] .= "<script>var aiosp_title_extra = {$extra_title_len};</script>";
		$buf = $this->get_option_row( $args['name'], $args['options'], $args );

		return $buf;
	}

	function add_page_icon() {
		wp_enqueue_script( 'wp-pointer', false, array( 'jquery' ) );
		wp_enqueue_style( 'wp-pointer' );
		$this->add_admin_pointers();

	
	   	wp_enqueue_style( 'aiosp_admin_style' , AIOSEOP_PLUGIN_URL . 'css/aiosp_admin.css' );
?>	
			<script>
			function aioseop_show_pointer( handle, value ) {
				if ( typeof( jQuery ) != 'undefined' ) {
					var p_edge = 'bottom';
					var p_align = 'center';
					if ( typeof( jQuery( value.pointer_target ).pointer) != 'undefined' ) {
						if ( typeof( value.pointer_edge ) != 'undefined' ) p_edge = value.pointer_edge;
						if ( typeof( value.pointer_align ) != 'undefined' ) p_align = value.pointer_align;
						jQuery(value.pointer_target).pointer({
							content    : value.pointer_text,
							position: {
								edge: p_edge,
								align: p_align
							},
							close  : function() {
								jQuery.post( ajaxurl, {
									pointer: handle,
									action: 'dismiss-wp-pointer'
								});
							}
						}).pointer('open');
					}
				}
			}
			<?php
			if ( !empty( $this->pointers ) ) {
			?>
			if ( typeof( jQuery ) != 'undefined' ) {
				jQuery(document).ready(function() {
					var admin_pointer;
					var admin_index;
					<?php
						foreach( $this->pointers as $k => $p )
							if ( !empty( $p["pointer_scope"] ) && ( $p["pointer_scope"] == 'global' ) ) {
								?>admin_index = "<?php echo esc_attr($k); ?>";
								admin_pointer = <?php echo json_encode( $p ); ?>;
								aioseop_show_pointer( admin_index, admin_pointer );
								<?php
							}
					?>
				});
			}
			<?php
			}
			?>
		</script>
		<?php
	}

	function add_page_hooks() {

global $aioseop_options;

		$post_objs = get_post_types( '', 'objects' );
		$pt = array_keys( $post_objs );
		$rempost = array( 'revision', 'nav_menu_item' );
		$pt = array_diff( $pt, $rempost );
		$post_types = Array();

		$aiosp_enablecpost = '';
		if (isset($_REQUEST['aiosp_enablecpost'])) $aiosp_enablecpost = $_REQUEST['aiosp_enablecpost'];

		foreach ( $pt as $p ) {
			if ( !empty( $post_objs[$p]->label ) ){
				if ( $post_objs[$p]->_builtin && empty( $aioseop_options['aiosp_enablecpost'] )){
				$post_types[$p] = $post_objs[$p]->label;
			}elseif (!empty( $aioseop_options['aiosp_enablecpost'] )  || $aiosp_enablecpost == 'on' ) {
				$post_types[$p] = $post_objs[$p]->label;
			}
			}
			else{
				$post_types[$p] = $p;
			}
		}
		
		foreach ($pt as $p){
			if ( !empty( $post_objs[$p]->label)){
				$all_post_types[$p] = $post_objs[$p]->label;
			}
		}
		
		$taxes = get_taxonomies( '', 'objects' );
		$tx = array_keys( $taxes );
		$remtax = array( 'nav_menu', 'link_category', 'post_format' );
		$tx = array_diff( $tx, $remtax );
		$tax_types = Array();
		foreach( $tx as $t )
			if ( !empty( $taxes[$t]->label ) )
				$tax_types[$t] = $taxes[$t]->label;
			else
				$taxes[$t] = $t;
		$this->default_options["posttypecolumns"]['initial_options'] = $post_types;
		$this->default_options["cpostactive"]['initial_options'] = $all_post_types;
		$this->default_options["cpostnoindex"]['initial_options'] = $post_types;
		$this->default_options["cpostnofollow"]['initial_options'] = $post_types;
		$this->default_options["cpostnoodp"]['initial_options'] = $post_types;
		$this->default_options["cpostnoydir"]['initial_options'] = $post_types;
		if ( AIOSEOPPRO )	$this->default_options["taxactive"]['initial_options'] = $tax_types;
		$this->default_options["google_author_location"]['initial_options'] = $post_types;
		$this->default_options['google_author_location' ]['initial_options'] = array_merge( Array( 'front' => __( 'Front Page', 'all-in-one-seo-pack' ) ), $post_types, Array( 'all' => __( 'Everywhere Else', 'all-in-one-seo-pack' ) ) );
		$this->default_options["google_author_location"]['default'] = array_keys( $this->default_options["google_author_location"]['initial_options'] );

		foreach ( $post_types as $p => $pt ) {
			$field = $p . "_title_format";
			$name = $post_objs[$p]->labels->singular_name;
			if ( !isset( $this->default_options[$field] ) ) {
				$this->default_options[$field] = Array (
						'name' => "$name " . __( 'Title Format:', 'all-in-one-seo-pack' ) . "<br />($p)",
						'type' => 'text',
						'default' => '%post_title% | %blog_title%',
						'condshow' => Array( 'aiosp_rewrite_titles' => 1, 'aiosp_enablecpost' => 'on', 'aiosp_cpostadvanced' => 'on', 'aiosp_cposttitles' => 'on', 'aiosp_cpostactive\[\]' => $p )
				);
				$this->help_text[$field] = __( 'The following macros are supported:', 'all-in-one-seo-pack' )
					. '<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
					__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
					__( '%post_title% - The original title of the post.', 'all-in-one-seo-pack' ) . '</li><li>';
				$taxes = get_object_taxonomies( $p, 'objects' );
				if ( !empty( $taxes ) )
					foreach( $taxes as $n => $t )
						$this->help_text[$field] .= sprintf( __( "%%tax_%s%% - This post's associated %s taxonomy title", 'all-in-one-seo-pack' ), $n, $t->label ) . '</li><li>';
				$this->help_text[$field] .=
					__( "%post_author_login% - This post's author' login", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_nicename% - This post's author' nicename", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_firstname% - This post's author' first name (capitalized)", 'all-in-one-seo-pack' ) . '</li><li>' .
					__( "%post_author_lastname% - This post's author' last name (capitalized)", 'all-in-one-seo-pack' ) . '</li>' .
					'</ul>';
				$this->help_anchors[$field] = '#custom-titles';
				$this->layout['cpt']['options'][] = $field;
			}
		}
		global $wp_roles;
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
		$role_names = $wp_roles->get_names();
		ksort( $role_names );
		$this->default_options["ga_exclude_users"]['initial_options'] = $role_names;

		unset( $tax_types['category'] );
		unset( $tax_types['post_tag'] );
		$this->default_options["tax_noindex"]['initial_options'] = $tax_types;
		if ( empty( $tax_types ) )
			unset( $this->default_options["tax_noindex"] );

		if (AIOSEOPPRO) {
		foreach ( $tax_types as $p => $pt ) {
			$field = $p . "_tax_title_format";
			$name = $pt;
			if ( !isset( $this->default_options[$field] ) ) {
				$this->default_options[$field] = Array (
						'name' => "$name " . __( 'Taxonomy Title Format:', 'all-in-one-seo-pack' ),
						'type' => 'text',
						'default' => '%taxonomy_title% | %blog_title%',
						'condshow' => Array( 'aiosp_rewrite_titles' => 1, 'aiosp_enablecpost' => 'on', 'aiosp_cpostadvanced' => 'on', 'aiosp_cposttitles' => 'on', 'aiosp_taxactive\[\]' => $p )
				);
				$this->help_text[$field] = __( "The following macros are supported:", 'all-in-one-seo-pack' ) .
					'<ul><li>' . __( '%blog_title% - Your blog title', 'all-in-one-seo-pack' ) . '</li><li>' .
					__( '%blog_description% - Your blog description', 'all-in-one-seo-pack' ) . '</li><li>' .
					__( '%taxonomy_title% - The original title of the taxonomy', 'all-in-one-seo-pack' ) . '</li><li>' .
					__( '%taxonomy_description% - The description of the taxonomy', 'all-in-one-seo-pack' ) . '</li></ul>';
				$this->help_anchors[$field] = '#custom-titles';
				$this->layout['cpt']['options'][] = $field;
				}
			}
		}
		$this->setting_options();
		$this->add_help_text_links();

		if (AIOSEOPPRO){
		global $aioseop_update_checker;
		add_action( "{$this->prefix}update_options", Array( $aioseop_update_checker, 'license_change_check' ), 10, 2 );
		add_action( "{$this->prefix}settings_update", Array( $aioseop_update_checker, 'update_check' ), 10, 2 );
		}

		add_filter( "{$this->prefix}display_options", Array( $this, 'filter_options' ), 10, 2 );
		parent::add_page_hooks();
	}

	function add_admin_pointers() {
		if ( AIOSEOPPRO ) {
		$this->pointers['aioseop_menu_236'] = Array( 'pointer_target' => '#toplevel_page_all-in-one-seo-pack-pro-aioseop_class',
												 'pointer_text' => 	'<h3>' . sprintf( __( 'Welcome to Version %s!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
													. '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack Pro ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ) . '</p>',
												 'pointer_edge' => 'top',
												 'pointer_align' => 'left',
												 'pointer_scope' => 'global'
											);
		$this->pointers['aioseop_welcome_230'] = Array( 'pointer_target' => '#aioseop_top_button',
													'pointer_text' => '<h3>' . sprintf( __( 'Review Your Settings', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
													. '</h3><p>' . __( 'New in 2.4: Improved support for taxonomies, Woocommerce and massive performance improvements under the hood! Please review your settings on each options page!', 'all-in-one-seo-pack' ) . '</p>',
													 'pointer_edge' => 'bottom',
													 'pointer_align' => 'left',
													 'pointer_scope' => 'local'
											 );
		$this->filter_pointers();
		}
		else {
			$this->pointers['aioseop_menu_220'] = Array( 'pointer_target' => '#toplevel_page_all-in-one-seo-pack-aioseop_class',
													 'pointer_text' => 	'<h3>' . sprintf( __( 'Welcome to Version %s!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
														. '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ) . '</p>',
													 'pointer_edge' => 'top',
													 'pointer_align' => 'left',
													 'pointer_scope' => 'global'
												);
			$this->pointers['aioseop_welcome_220'] = Array( 'pointer_target' => '#aioseop_top_button',
														'pointer_text' => '<h3>' . sprintf( __( 'Review Your Settings', 'all-in-one-seo-pack' ), AIOSEOP_VERSION )
														. '</h3><p>' . __( 'Thank you for running the latest and greatest All in One SEO Pack ever! New since 2.2: Control who accesses your site with the new Robots.txt Editor and File Editor modules!  Enable them from the Feature Manager.  Remember to review your settings, we have added some new ones!', 'all-in-one-seo-pack' ) . '</p>',
														 'pointer_edge' => 'bottom',
														 'pointer_align' => 'left',
														 'pointer_scope' => 'local'
												 );
			$this->filter_pointers();
		}
	}

	function settings_page_init() {
		add_filter( "{$this->prefix}submit_options",	Array( $this, 'filter_submit'   ) );
	}

	function enqueue_scripts() {
		add_filter( "{$this->prefix}display_settings",	Array( $this, 'filter_settings' ), 10, 3 );
		add_filter( "{$this->prefix}display_options", Array( $this, 'filter_options' ), 10, 2 );
		parent::enqueue_scripts();
	}

	function filter_submit( $submit ) {
		$submit['Submit_Default']['value'] = __( 'Reset General Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;';
		$submit['Submit_All_Default'] = Array( 'type' => 'submit', 'class' => 'button-secondary', 'value' => __( 'Reset ALL Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;' );
		return $submit;
	}

	/**
	 * Handle resetting options to defaults, but preserve the license key if pro.
	 */
	function reset_options( $location = null, $delete = false ) {
		if ( AIOSEOPPRO) {
			global $aioseop_update_checker;
		}
		if ( $delete === true ) {

			if ( AIOSEOPPRO ) {
			$license_key = '';
			if ( isset( $this->options ) && isset( $this->options['aiosp_license_key'] ) )
				$license_key = $this->options['aiosp_license_key'];
			}

			$this->delete_class_option( $delete );

			if ( AIOSEOPPRO ) {
			$this->options = Array( 'aiosp_license_key' => $license_key );
			} else {
				$this->options = Array();
			}
		}
		$default_options = $this->default_options( $location );

		if ( AIOSEOPPRO ) {
		foreach ( $default_options as $k => $v )
			if ( $k != 'aiosp_license_key' )
				$this->options[$k] = $v;
		$aioseop_update_checker->license_key = $this->options['aiosp_license_key'];
		} else {
			foreach ( $default_options as $k => $v )
				$this->options[$k] = $v;
			}
		$this->update_class_option( $this->options );
	}

	function get_current_options( $opts = Array(), $location = null, $defaults = null, $post = null ) {
		if ( ( $location === 'aiosp' ) && ( $this->locations[$location]['type'] == 'metabox' ) ) {
			if ( $post == null ) {
				global $post;
			}
			$post_id = $post;
			if ( is_object( $post_id ) )
				$post_id = $post_id->ID;
			$get_opts = $this->default_options( $location );
			$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'sitemap_exclude', 'disable', 'disable_analytics', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
			if ( !( !empty( $this->options['aiosp_can'] ) ) && ( !empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist["custom_link"] );
			}
			foreach ( $optlist as $f ) {
				$meta = '';
				$field = "aiosp_$f";

				if ( AIOSEOPPRO ) {
					if ( ( isset( $_GET['taxonomy'] ) && isset( $_GET['tag_ID'] ) ) || is_category() || is_tag() || is_tax() ) {
						if ( is_admin() && isset( $_GET['tag_ID'] ) ) {
							$meta = get_term_meta( $_GET['tag_ID'], '_aioseop_' . $f, true );
						} else {
							$queried_object = get_queried_object();
							if ( !empty( $queried_object ) && !empty( $queried_object->term_id ) ) {
								$meta = get_term_meta( $queried_object->term_id, '_aioseop_' . $f, true );
							}
						}
					} else 
						$meta = get_post_meta( $post_id, '_aioseop_' . $f, true );
					if ( 'title' === $f || 'description' === $f ) {
						$get_opts[$field] = htmlspecialchars( ( $meta ) );
					} else {
						$get_opts[$field] = htmlspecialchars( stripslashes( $meta ) );
					}
				} else {
					$field = "aiosp_$f";
					$meta = get_post_meta( $post_id, '_aioseop_' . $f, true );
					if ( 'title' === $f || 'description' === $f ) {
						$get_opts[$field] = htmlspecialchars( ( $meta ) );
					} else {
						$get_opts[$field] = htmlspecialchars( stripslashes( $meta ) );
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

	function filter_settings( $settings, $location, $current ) {
		if ( $location == null ) {
			$prefix = $this->prefix;

			foreach ( Array( 'seopostcol', 'seocustptcol', 'debug_info', 'max_words_excerpt' ) as $opt )
				unset( $settings["{$prefix}$opt"] );

			if ( !class_exists( 'DOMDocument' ) ) {
				unset( $settings["{prefix}google_connect"] );
			}
			if ( AIOSEOPPRO ) {
			if ( !empty( $this->options['aiosp_license_key'] ) ) {
				$settings['aiosp_license_key']['type'] = 'password';
				$settings['aiosp_license_key']['size'] = 38;
			}
			}
		} elseif ( $location == 'aiosp' ) {
			global $post, $aioseop_sitemap;
			$prefix = $this->get_prefix( $location ) . $location . '_';
			if ( !empty( $post ) ) {
				$post_type = get_post_type( $post );
				if ( !empty( $this->options['aiosp_cpostnoindex'] ) && ( in_array( $post_type, $this->options['aiosp_cpostnoindex'] ) ) ) {
					$settings["{$prefix}noindex"]['type'] = 'select';
					$settings["{$prefix}noindex"]['initial_options'] = Array( '' => __( 'Default - noindex', 'all-in-one-seo-pack' ), 'off' => __( 'index', 'all-in-one-seo-pack' ), 'on' => __( 'noindex', 'all-in-one-seo-pack' ) );
				}
				if ( !empty( $this->options['aiosp_cpostnofollow'] ) && ( in_array( $post_type, $this->options['aiosp_cpostnofollow'] ) ) ) {
					$settings["{$prefix}nofollow"]['type'] = 'select';
					$settings["{$prefix}nofollow"]['initial_options'] = Array( '' => __( 'Default - nofollow', 'all-in-one-seo-pack' ), 'off' => __( 'follow', 'all-in-one-seo-pack' ), 'on' => __( 'nofollow', 'all-in-one-seo-pack' ) );
				}
				if ( !empty( $this->options['aiosp_cpostnoodp'] ) && ( in_array( $post_type, $this->options['aiosp_cpostnoodp'] ) ) ) {
					$settings["{$prefix}noodp"]['type'] = 'select';
					$settings["{$prefix}noodp"]['initial_options'] = Array( '' => __( 'Default - noodp', 'all-in-one-seo-pack' ), 'off' => __( 'odp', 'all-in-one-seo-pack' ), 'on' => __( 'noodp', 'all-in-one-seo-pack' ) );
				}
				if ( !empty( $this->options['aiosp_cpostnoydir'] ) && ( in_array( $post_type, $this->options['aiosp_cpostnoydir'] ) ) ) {
					$settings["{$prefix}noydir"]['type'] = 'select';
					$settings["{$prefix}noydir"]['initial_options'] = Array( '' => __( 'Default - noydir', 'all-in-one-seo-pack' ), 'off' => __( 'ydir', 'all-in-one-seo-pack' ), 'on' => __( 'noydir', 'all-in-one-seo-pack' ) );
				}
				global $post;
				$info = $this->get_page_snippet_info();
				extract( $info );
				$settings["{$prefix}title"]['placeholder'] = $title;
				$settings["{$prefix}description"]['placeholder'] = $description;
				$settings["{$prefix}keywords"]['placeholder'] = $keywords;
			}

			if ( !AIOSEOPPRO ){
				if ( !current_user_can( 'update_plugins' ) )
					unset( $settings["{$prefix}upgrade"] );
			}

			if ( !is_object( $aioseop_sitemap ) )
				unset( $settings['aiosp_sitemap_exclude'] );
			if ( is_object( $post ) ) {
				if ( $post->post_type != 'page' ) {
					unset( $settings["{$prefix}titleatr"] );
					unset( $settings["{$prefix}menulabel"] );
				}
			}
			if ( !empty( $this->options[$this->prefix . 'togglekeywords'] ) ) {
				unset( $settings["{$prefix}keywords"] );
				unset( $settings["{$prefix}togglekeywords"] );
			} elseif ( !empty( $current["{$prefix}togglekeywords"] ) ) {
				unset( $settings["{$prefix}keywords"] );
			}
			if ( empty( $this->options['aiosp_can'] ) || ( empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $settings["{$prefix}custom_link"] );
			}
		}
		return $settings;
	}

	function filter_options( $options, $location ) {
		if ( $location == 'aiosp' ) {
			global $post;
			if ( !empty( $post ) ) {
				$prefix = $this->prefix;
				$post_type = get_post_type( $post );
				foreach( Array( 'noindex', 'nofollow', 'noodp', 'noydir' ) as $no ) {
					if ( empty( $this->options['aiosp_cpost' . $no] ) || ( !in_array( $post_type, $this->options['aiosp_cpost' . $no] ) ) )
						if ( isset( $options["{$prefix}{$no}"] ) && ( $options["{$prefix}{$no}"] != 'on' ) )
							unset( $options["{$prefix}{$no}"] );
				}
			}
		}
		if ( $location == null ) {
			$prefix = $this->prefix;
			if ( isset( $options["{$prefix}rewrite_titles"] ) && ( !empty( $options["{$prefix}rewrite_titles"] ) ) )
				$options["{$prefix}rewrite_titles"] = 1;
			if ( ( isset( $options["{$prefix}enablecpost"] ) ) && ( $options["{$prefix}enablecpost"] === '' ) )
				$options["{$prefix}enablecpost"] = 0;
			if ( ( isset( $options["{$prefix}use_original_title"] ) ) && ( $options["{$prefix}use_original_title"] === '' ) )
				$options["{$prefix}use_original_title"] = 0;
		}
		return $options;
	}

	

	function get_queried_object() {
		static $p = null;
		global $wp_query, $post;
		if ( $p !== null ) return $p;
		if ( is_object( $post ) )
			$p = $post;
		else {
			if ( !$wp_query ) return null;
			$p = $wp_query->get_queried_object();
		}
		return $p;
	}

	function is_page_included() {
		global $aioseop_options;
		if ( is_feed() ) return false;
		if ( aioseop_mrt_exclude_this_page() ) return false;
		$post = $this->get_queried_object();
		$post_type = '';
		if ( !empty( $post ) && !empty( $post->post_type ) )
			$post_type = $post->post_type;
		if ( empty( $aioseop_options['aiosp_enablecpost'] ) ) {
			$wp_post_types = get_post_types( Array( '_builtin' => true ) ); // don't display meta if SEO isn't enabled on custom post types -- pdb
			if( is_singular() && !in_array( $post_type, $wp_post_types ) && !is_front_page() ) return false;
		} else {
			$wp_post_types = $aioseop_options['aiosp_cpostactive'];
			if ( empty( $wp_post_types ) ) $wp_post_types = Array();
			if ( AIOSEOPPRO ) {
			if ( is_tax() ) {
				if ( empty( $aioseop_options['aiosp_taxactive'] ) || !is_tax( $aioseop_options['aiosp_taxactive'] ) ) return false;
			} elseif ( is_category() ) {
				if ( empty( $aioseop_options['aiosp_taxactive'] ) || !in_array( 'category', $aioseop_options['aiosp_taxactive'] ) ) return false;
			} elseif ( is_tag() ) {
				if ( empty( $aioseop_options['aiosp_taxactive'] ) || !in_array( 'post_tag', $aioseop_options['aiosp_taxactive'] ) ) return false;
			} else if ( !in_array( $post_type, $wp_post_types ) && !is_front_page() && !is_post_type_archive( $wp_post_types ) && !is_404() ) return false;
			} else {
				if ( is_singular() && !in_array( $post_type, $wp_post_types ) && !is_front_page() ) return false;
				if ( is_post_type_archive() && !is_post_type_archive( $wp_post_types ) ) return false;
			}
		}

		$this->meta_opts = $this->get_current_options( Array(), 'aiosp' );

		$aiosp_disable = $aiosp_disable_analytics = false;

		if  ( !empty( $this->meta_opts ) ) {
			if ( isset( $this->meta_opts['aiosp_disable'] ) ) 			$aiosp_disable			 = $this->meta_opts['aiosp_disable'];
			if ( isset( $this->meta_opts['aiosp_disable_analytics'] ) ) $aiosp_disable_analytics = $this->meta_opts['aiosp_disable_analytics'];
		}

		if ( $aiosp_disable ) {
			if ( !$aiosp_disable_analytics ) {
				if ( aioseop_option_isset( 'aiosp_google_analytics_id' ) ) {
					remove_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
					add_action( 'wp_head', array( $this, 'aiosp_google_analytics' ) );
				}
			}
			return false;
		}

		if ( !empty( $this->meta_opts ) && $this->meta_opts['aiosp_disable'] == true ) return false;

		return true;
	}

	function template_redirect() {
		global $aioseop_options;

		$post = $this->get_queried_object();

		if ( !$this->is_page_included() ) return;

		if ( !empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
			$force_rewrites = 1;
			if ( isset( $aioseop_options['aiosp_force_rewrites'] ) )
				$force_rewrites = $aioseop_options['aiosp_force_rewrites'];
			if ( $force_rewrites )
				ob_start( array( $this, 'output_callback_for_title' ) );
			else
				add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );
		}
	}

	function output_callback_for_title( $content ) {
		return $this->rewrite_title( $content );
	}

	function add_hooks() {
		global $aioseop_options, $aioseop_update_checker;
		
		$role = get_role( 'administrator' );
	    if ( is_object( $role ) ) $role->add_cap( 'aiosp_manage_seo' );
		
		aioseop_update_settings_check();
		add_filter( 'user_contactmethods', 'aioseop_add_contactmethods' );
		if ( is_user_logged_in() && function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() && current_user_can( 'aiosp_manage_seo' ) )
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'add_page_icon' ) );
			add_action( 'admin_init', 'aioseop_addmycolumns', 1 );
			add_action( 'admin_init', 'aioseop_handle_ignore_notice' );
			if ( AIOSEOPPRO ){
			if ( current_user_can( 'update_plugins' ) )
				add_action( 'admin_notices', Array( $aioseop_update_checker, 'key_warning' ) );
			add_action( 'after_plugin_row_' . AIOSEOP_PLUGIN_BASENAME, Array( $aioseop_update_checker, 'add_plugin_row' ) );
			}
		} else {
			if ( $aioseop_options['aiosp_can'] == '1' || $aioseop_options['aiosp_can'] == 'on' )
			        remove_action( 'wp_head', 'rel_canonical' );
			////analytics
			if ( aioseop_option_isset( 'aiosp_google_analytics_id' ) )
				add_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
			add_filter( 'wp_list_pages', 'aioseop_list_pages' );
			add_action( 'wp_head', array( $this, 'wp_head'), apply_filters( 'aioseop_wp_head_priority', 1 ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );
			add_filter( 'wp_list_pages_excludes', 'aioseop_get_pages_start' );
			add_filter( 'get_pages', 'aioseop_get_pages' );
		}
	}

	function is_static_front_page() {
		if ( isset( $this->is_front_page ) && $this->is_front_page !== null ) return $this->is_front_page;
		$post = $this->get_queried_object();
		$this->is_front_page = ( get_option( 'show_on_front' ) == 'page' && is_page() && !empty( $post ) && $post->ID == get_option( 'page_on_front' ) );
		return $this->is_front_page;
	}

	function is_static_posts_page() {
		static $is_posts_page = null;
		if ( $is_posts_page !== null ) return $is_posts_page;
		$post = $this->get_queried_object();
		$is_posts_page = ( get_option( 'show_on_front' ) == 'page' && is_home() && !empty( $post ) && $post->ID == get_option( 'page_for_posts' ) );
		return $is_posts_page;
	}

	function check_rewrite_handler() {
		global $aioseop_options;

		$force_rewrites = 1;
		if ( isset( $aioseop_options['aiosp_force_rewrites'] ) )
			$force_rewrites = $aioseop_options['aiosp_force_rewrites'];

		if ( !empty( $aioseop_options['aiosp_rewrite_titles'] ) && $force_rewrites ) {
			// make the title rewrite as short as possible
			if (function_exists( 'ob_list_handlers' ) ) {
				$active_handlers = ob_list_handlers();
			} else {
				$active_handlers = array();
			}
			if (sizeof($active_handlers) > 0 &&
				$this->strtolower( $active_handlers[sizeof( $active_handlers ) - 1] ) ==
				$this->strtolower( 'All_in_One_SEO_Pack::output_callback_for_title' ) ) {
				ob_end_flush();
			} else {
				$this->log( "another plugin interfering?" );
				// if we get here there *could* be trouble with another plugin :(
				$this->ob_start_detected = true;
				if ( $this->option_isset( "rewrite_titles" ) ) { // try alternate method -- pdb
					$aioseop_options['aiosp_rewrite_titles'] = 0;
					$force_rewrites = 0;
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

	// handle prev / next links
	function get_prev_next_links( $post = null ) {
		$prev = $next = '';
		$page = $this->get_page_number();
		if ( is_home() || is_archive() || is_paged() ) {
			global $wp_query;
			$max_page = $wp_query->max_num_pages;
			if ( $page > 1 )
				$prev = get_previous_posts_page_link();
			if ( $page < $max_page ) {
				$paged = $GLOBALS['paged'];
				if ( !is_single() ) {
					if ( !$paged )
						$paged = 1;
					$nextpage = intval($paged) + 1;
					if ( !$max_page || $max_page >= $nextpage )
						$next = get_pagenum_link($nextpage);
				}
			}
		} else if ( is_page() || is_single() ) {
			$numpages = 1;
	        $multipage = 0;
	        $page = get_query_var('page');
	        if ( ! $page )
	                $page = 1;
	        if ( is_single() || is_page() || is_feed() )
	                $more = 1;
	        $content = $post->post_content;
	        if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
	                if ( $page > 1 )
	                        $more = 1;
	                $content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
	                $content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
	                $content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
	                // Ignore nextpage at the beginning of the content.
	                if ( 0 === strpos( $content, '<!--nextpage-->' ) )
	                        $content = substr( $content, 15 );
	                $pages = explode('<!--nextpage-->', $content);
	                $numpages = count($pages);
	                if ( $numpages > 1 )
	                        $multipage = 1;
	        }
			if ( !empty( $page ) ) {
				if ( $page > 1 )
					$prev = _wp_link_page( $page - 1 );
				if ( $page + 1 <= $numpages )
					$next = _wp_link_page( $page + 1 );
			}
			if ( !empty( $prev ) ) {
				$prev = $this->substr( $prev, 9, -2 );
			}
			if ( !empty( $next ) ) {
				$next = $this->substr( $next, 9, -2 );
			}
		}
		return Array( 'prev' => $prev, 'next' => $next );
	}

	function get_google_authorship( $post ) {
		global $aioseop_options;
		$page = $this->get_page_number();
		// handle authorship
		$googleplus = $publisher = $author = '';

		if ( !empty( $post ) && isset( $post->post_author ) && empty( $aioseop_options['aiosp_google_disable_profile'] ) )
			$googleplus = get_the_author_meta( 'googleplus', $post->post_author );

		if ( empty( $googleplus ) && !empty( $aioseop_options['aiosp_google_publisher'] ) )
			$googleplus = $aioseop_options['aiosp_google_publisher'];

		if ( ( is_front_page() ) && ( $page < 2 ) ) {
			if ( !empty( $aioseop_options['aiosp_google_publisher'] ) )
				$publisher = $aioseop_options['aiosp_google_publisher'];

			if ( !empty( $aioseop_options["aiosp_google_author_advanced"] ) ) {
				if ( empty( $aioseop_options["aiosp_google_enable_publisher"] ) ) {
					$publisher = '';
				} elseif ( !empty( $aioseop_options["aiosp_google_specify_publisher"] ) ) {
					$publisher = $aioseop_options["aiosp_google_specify_publisher"];
				}
			}
		}
		if ( is_singular() && ( !empty( $googleplus ) ) )
			$author = $googleplus;
		else if ( !empty( $aioseop_options['aiosp_google_publisher'] ) )
			$author = $aioseop_options['aiosp_google_publisher'];

		if ( !empty( $aioseop_options['aiosp_google_author_advanced'] ) && isset( $aioseop_options['aiosp_google_author_location'] ) ) {
			if ( empty( $aioseop_options['aiosp_google_author_location'] ) )
				$aioseop_options['aiosp_google_author_location'] = Array();
			if ( is_front_page() && !in_array( 'front', $aioseop_options['aiosp_google_author_location'] ) ) {
				$author = '';
			} else {
				if ( in_array( 'all', $aioseop_options['aiosp_google_author_location'] ) ) {
					if ( is_singular() && !is_singular( $aioseop_options['aiosp_google_author_location'] ) )
						$author = '';
				} else {
					if ( !is_singular( $aioseop_options['aiosp_google_author_location'] ) )
						$author = '';
				}
			}
		}

		return Array( 'publisher' => $publisher, 'author' => $author );
	}

	function visibility_warning() {

		$aioseop_visibility_notice_dismissed = get_user_meta( get_current_user_id(), 'aioseop_visibility_notice_dismissed', true );

	  if ( '0' == get_option('blog_public') && empty( $aioseop_visibility_notice_dismissed ) ) {

		printf( '
			<div id="message" class="error notice is-dismissible aioseop-notice visibility-notice">
				<p>
					<strong>%1$s</strong>
					%2$s

				</p>
			</div>',
			__( 'Warning: You\'re blocking access to search engines.', 'all-in-one-seo-pack' ),
			sprintf( __( 'You can %s click here%s to go to your reading settings and toggle your blog visibility.', 'all-in-one-seo-pack' ), sprintf( '<a href="%s">', esc_url( admin_url( 'options-reading.php' ) ) ), '</a>' ));

	  }elseif( '1' == get_option('blog_public') && !empty( $aioseop_visibility_notice_dismissed ) ){
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
			sprintf( __( '%s Upgrade%s to All in One SEO Pack Pro for increased SEO compatibility for your products.', 'all-in-one-seo-pack' ), sprintf( '<a target="_blank" href="%s">', esc_url( 'http://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=woo' ) ), '</a>' ));

	  }elseif( !class_exists( 'WooCommerce' ) && !empty( $aioseop_woo_upgrade_notice_dismissed ) ){
			delete_user_meta( get_current_user_id(), 'aioseop_woo_upgrade_notice_dismissed' );
			}
	}

	function get_robots_meta() {
		global $aioseop_options;
		$opts = $this->meta_opts;
		$page = $this->get_page_number();
		$robots_meta = $tax_noindex = '';
		if ( isset( $aioseop_options['aiosp_tax_noindex'] ) ) $tax_noindex = $aioseop_options['aiosp_tax_noindex'];

		if ( empty( $tax_noindex ) || !is_array( $tax_noindex) ) $tax_noindex = Array();

		$aiosp_noindex = $aiosp_nofollow = $aiosp_noodp = $aiosp_noydir = '';
		$noindex = "index";
		$nofollow = "follow";
		if ( ( is_category() && !empty( $aioseop_options['aiosp_category_noindex'] ) ) || ( !is_category() && is_archive() && !is_tag() && !is_tax()
			&& ( ( is_date() && !empty( $aioseop_options['aiosp_archive_date_noindex'] ) ) || ( is_author() && !empty( $aioseop_options['aiosp_archive_author_noindex'] ) ) ) )
			|| ( is_tag() && !empty( $aioseop_options['aiosp_tags_noindex'] ) )
			|| ( is_search() && !empty( $aioseop_options['aiosp_search_noindex'] ) )
		 	|| ( is_404() && !empty( $aioseop_options['aiosp_404_noindex'] ) )
			|| ( is_tax() && in_array( get_query_var( 'taxonomy' ), $tax_noindex ) ) ) {
				$noindex = 'noindex';
		} elseif ( ( is_single() || is_page() || $this->is_static_posts_page() || is_attachment() || is_category() || is_tag() || is_tax() || ( $page > 1 ) ) ) {
			$post_type = get_post_type();
			if ( !empty( $opts ) ) {
			    $aiosp_noindex = htmlspecialchars( stripslashes( $opts['aiosp_noindex'] ) );
			    $aiosp_nofollow = htmlspecialchars( stripslashes( $opts['aiosp_nofollow'] ) );
			    $aiosp_noodp = htmlspecialchars( stripslashes( $opts['aiosp_noodp'] ) );
			    $aiosp_noydir = htmlspecialchars( stripslashes( $opts['aiosp_noydir'] ) );
			}
			if ( $aiosp_noindex || $aiosp_nofollow || $aiosp_noodp || $aiosp_noydir || !empty( $aioseop_options['aiosp_cpostnoindex'] )
				|| !empty( $aioseop_options['aiosp_cpostnofollow'] ) || !empty( $aioseop_options['aiosp_cpostnoodp'] ) || !empty( $aioseop_options['aiosp_cpostnoydir'] )
				|| !empty( $aioseop_options['aiosp_paginated_noindex'] ) || !empty( $aioseop_options['aiosp_paginated_nofollow'] ) ) {
				if ( ( $aiosp_noindex == 'on' ) || ( ( !empty( $aioseop_options['aiosp_paginated_noindex'] ) ) && ( ( $page > 1 ) ) ) ||
					 ( ( $aiosp_noindex == '' ) && ( !empty( $aioseop_options['aiosp_cpostnoindex'] ) ) && ( in_array( $post_type, $aioseop_options['aiosp_cpostnoindex'] ) ) ) )
					$noindex = "noindex";
				if ( ( $aiosp_nofollow == 'on' ) || ( ( !empty( $aioseop_options['aiosp_paginated_nofollow'] ) ) && ( ( $page > 1 ) ) ) ||
					 ( ( $aiosp_nofollow == '' ) && ( !empty( $aioseop_options['aiosp_cpostnofollow'] ) ) && ( in_array( $post_type, $aioseop_options['aiosp_cpostnofollow'] ) ) ) )
					$nofollow = "nofollow";
				if ( ( $aiosp_noodp == 'on' ) || ( empty( $aiosp_noodp ) && ( !empty( $aioseop_options['aiosp_cpostnoodp'] ) && ( in_array( $post_type, $aioseop_options['aiosp_cpostnoodp'] ) ) ) ) )
					$aiosp_noodp = true;
				else
					$aiosp_noodp = false;
				if ( ( $aiosp_noydir == 'on' ) || ( empty( $aiosp_noydir ) && ( !empty( $aioseop_options['aiosp_cpostnoydir'] ) && ( in_array( $post_type, $aioseop_options['aiosp_cpostnoydir'] ) ) ) ) )
					$aiosp_noydir = true;
				else
				    $aiosp_noydir = false;
			}
		}
		if ( !empty( $aioseop_options['aiosp_noodp'] ) && $aioseop_options['aiosp_noodp'] )   $aiosp_noodp = true;
		if ( !empty( $aioseop_options['aiosp_noydir'] ) && $aioseop_options['aiosp_noydir'] ) $aiosp_noydir = true;
		if ( $aiosp_noodp ) $nofollow .= ',noodp';
		if ( $aiosp_noydir ) $nofollow .= ',noydir';
		$robots_meta = $noindex . ',' . $nofollow;
		if ( $robots_meta == 'index,follow' ) $robots_meta = '';
		return $robots_meta;
	}

	function get_main_description( $post = null ) {
		global $aioseop_options;
		$opts = $this->meta_opts;
		$description = '';
		if ( is_author()  && $this->show_page_description() ) {
			$description = $this->internationalize( get_the_author_meta( 'description' ) );
		} else if ( function_exists( 'woocommerce_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = woocommerce_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
			//$description = $this->get_post_description( $post );
			//$description = $this->apply_cf_fields( $description );
			if ( !(woocommerce_get_page_id( 'shop' ) == get_option( 'page_on_front' ) )  ){
		$description = trim( ( $this->internationalize( get_post_meta( $post->ID, "_aioseop_description", true ) ) ) );
			}
			else if ( woocommerce_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && !empty( $aioseop_options['aiosp_use_static_home_info'] ) ){
			//$description = $this->get_aioseop_description( $post );
					$description = trim( ( $this->internationalize( get_post_meta( $post->ID, "_aioseop_description", true ) ) ) );
		}else if ( woocommerce_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && empty( $aioseop_options['aiosp_use_static_home_info'] ) ){
			$description = $this->get_aioseop_description( $post );
		}
		} else if ( is_front_page() ) {
			$description = $this->get_aioseop_description( $post );
		} else if ( is_single() || is_page() || is_attachment() || is_home() || $this->is_static_posts_page() ) {
			$description = $this->get_aioseop_description( $post );
		} else if ( ( is_category() || is_tag() || is_tax() ) && $this->show_page_description() ) {
			if ( !empty( $opts ) && AIOSEOPPRO ) $description = $opts['aiosp_description'];
			if ( empty( $description ) ) $description = term_description();
			$description = $this->internationalize( $description );
		}
		if ( empty( $aioseop_options['aiosp_dont_truncate_descriptions'] ) ) {
			$description = $this->trim_excerpt_without_filters( $description );
		}
		return $description;
	}

	function trim_description( $description ) {
		$description = trim( wp_strip_all_tags( $description ) );
		$description = str_replace( '"', '&quot;', $description );
		$description = str_replace( "\r\n", ' ', $description );
		$description = str_replace( "\n", ' ', $description );
		return $description;
	}

	function apply_description_format( $description, $post = null ) {
		global $aioseop_options;
		$description_format = $aioseop_options['aiosp_description_format'];
		if ( !isset( $description_format ) || empty( $description_format ) ) {
			$description_format = "%description%";
		}
		$description = str_replace( '%description%', apply_filters( 'aioseop_description_override', $description ), $description_format );
		if ( strpos( $description, '%blog_title%'		) !== false ) $description = str_replace( '%blog_title%',		get_bloginfo( 'name' ), $description );
		if ( strpos( $description, '%blog_description%'	) !== false ) $description = str_replace( '%blog_description%',	get_bloginfo( 'description' ), $description );
		if ( strpos( $description, '%wp_title%'			) !== false ) $description = str_replace( '%wp_title%',			$this->get_original_title(), $description );
		if ( strpos( $description, '%post_title%'		) !== false ) $description = str_replace( '%post_title%',		$this->get_aioseop_title( $post ), $description );


		/*this was intended to make attachment descriptions unique if pulling from the parent... let's remove it and see if there are any problems
		*on the roadmap is to have a better hierarchy for attachment description pulling
		* if ($aioseop_options['aiosp_can']) $description = $this->make_unique_att_desc($description);
		*/

		return $description;
	}

	function make_unique_att_desc($description){
		global $wp_query;
		if( is_attachment() ) {
			
			$url = $this->aiosp_mrt_get_url( $wp_query );
			if ( $url ) {
				$matches = Array();
				preg_match_all( '/(\d+)/', $url, $matches );
				if ( is_array( $matches ) ){
					$uniqueDesc = join( '', $matches[0] );
				}
			}
			$description .= ' ' . $uniqueDesc;
					return $description;
		}
	}

	function get_main_keywords() {
		global $aioseop_options;
		global $aioseop_keywords;
		global $post;
		$opts = $this->meta_opts;
		if ( ( ( is_front_page() && $aioseop_options['aiosp_home_keywords'] && !$this->is_static_posts_page() ) || $this->is_static_front_page() ) ) {
			if ( !empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				$keywords = $this->get_all_keywords();
			} else {
				$keywords = trim( $this->internationalize( $aioseop_options['aiosp_home_keywords'] ) );
			}
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && $this->is_static_posts_page() ) {
			$keywords = stripslashes( $this->internationalize( $opts["aiosp_keywords"] ) ); // and if option = use page set keywords instead of keywords from recent posts
		} elseif ( ( $blog_page = aiosp_common::get_blog_page( $post ) )  && empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) ) {
			$keywords = stripslashes( $this->internationalize( get_post_meta( $blog_page->ID, "_aioseop_keywords", true ) ) );
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && ( is_archive() || is_post_type_archive() ) ) {
			$keywords = "";
		} else {
			$keywords = $this->get_all_keywords();
		}
		return $keywords;
	}

	function wp_head() {
			if ( !$this->is_page_included() ) return;
			$opts = $this->meta_opts;
			global $aioseop_update_checker, $wp_query, $aioseop_options, $posts;
			static $aioseop_dup_counter = 0;
			$aioseop_dup_counter++;
			if ( $aioseop_dup_counter > 1 ) {
			    echo "\n<!-- " . sprintf( __( "Debug Warning: All in One SEO Pack meta data was included again from %s filter. Called %s times!", 'all-in-one-seo-pack' ), current_filter(), $aioseop_dup_counter ) . " -->\n";
			    return;
			}
			if ( is_home() && !is_front_page() ) {
				$post = aiosp_common::get_blog_page();
			} else {
				$post = $this->get_queried_object();
			}
			$meta_string = null;
			$description = '';
			// logging - rewrite handler check for output buffering
			$this->check_rewrite_handler();
			if ( AIOSEOPPRO ) {
			echo "\n<!-- All in One SEO Pack Pro $this->version by Michael Torbert of Semper Fi Web Design";
			} else {
				echo "\n<!-- All in One SEO Pack $this->version by Michael Torbert of Semper Fi Web Design";
			}
			if ( $this->ob_start_detected )
				echo "ob_start_detected ";
			echo "[$this->title_start,$this->title_end] ";
			echo "-->\n";
			if ( AIOSEOPPRO )	echo "<!-- " . __( "Debug String", 'all-in-one-seo-pack' ) . ": " . $aioseop_update_checker->get_verification_code() . " -->\n";
			$blog_page = aiosp_common::get_blog_page( $post );
			$save_posts = $posts;
			if ( function_exists( 'woocommerce_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = woocommerce_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
				global $posts;
				$opts = $this->meta_opts = $this->get_current_options( Array(), 'aiosp', null, $post );
				$posts = Array();
				$posts[] = $post;
			}
			$posts = $save_posts;
			$description = apply_filters( 'aioseop_description', $this->get_main_description( $post ) );	// get the description
			// handle the description format
			if ( isset($description) && ( $this->strlen($description) > $this->minimum_description_length ) && !( is_front_page() && is_paged() ) ) {
				$description = $this->trim_description( $description );
				if ( !isset( $meta_string) ) $meta_string = '';
				// description format
				$description = apply_filters( 'aioseop_description_full', $this->apply_description_format( $description, $post ) );
				$desc_attr = '';
				if ( !empty( $aioseop_options['aiosp_schema_markup'] ) )
					$desc_attr = 'itemprop="description"';
				$desc_attr = apply_filters( 'aioseop_description_attributes', $desc_attr );
				$meta_string .= sprintf( "<meta name=\"description\" %s content=\"%s\" />\n", $desc_attr, $description );
			}
			// get the keywords
			$togglekeywords = 0;
			if ( isset( $aioseop_options['aiosp_togglekeywords'] ) )
				$togglekeywords = $aioseop_options['aiosp_togglekeywords'];
			if ( $togglekeywords == 0  && !( is_front_page() && is_paged() ) ) {
				$keywords = $this->get_main_keywords();
				$keywords = $this->apply_cf_fields( $keywords );
				$keywords = apply_filters( 'aioseop_keywords', $keywords );

				if ( isset( $keywords ) && !empty( $keywords ) ) {
					if ( isset( $meta_string ) ) $meta_string .= "\n";
					$keywords = wp_filter_nohtml_kses( str_replace( '"', '', $keywords ) );
					$key_attr = '';
					if ( !empty( $aioseop_options['aiosp_schema_markup'] ) )
						$key_attr = 'itemprop="keywords"';
					$key_attr = apply_filters( 'aioseop_keywords_attributes', $key_attr );
					$meta_string .= sprintf( "<meta name=\"keywords\" %s content=\"%s\" />\n", $key_attr, $keywords );
				}
			}
			// handle noindex, nofollow - robots meta
			$robots_meta = apply_filters( 'aioseop_robots_meta', $this->get_robots_meta() );
			if ( !empty( $robots_meta ) )
				$meta_string .= '<meta name="robots" content="' . esc_attr( $robots_meta ) . '" />' . "\n";
			// handle site verification
			if ( is_front_page() ) {
				foreach( Array( 'google' => 'google-site-verification', 'bing' => 'msvalidate.01', 'pinterest' => 'p:domain_verify' ) as $k => $v )
					if ( !empty( $aioseop_options["aiosp_{$k}_verify"] ) )
						$meta_string .= '<meta name="' . $v . '" content="' . trim( strip_tags( $aioseop_options["aiosp_{$k}_verify"] ) ) . '" />' . "\n";

				// sitelinks search
				if ( !empty( $aioseop_options["aiosp_google_sitelinks_search"] ) || !empty( $aioseop_options["aiosp_google_set_site_name"] ) )
					$meta_string .= $this->sitelinks_search_box() . "\n";
			}
			// handle extra meta fields
			foreach( Array( 'page_meta', 'post_meta', 'home_meta', 'front_meta' ) as $meta ) {
				if ( !empty( $aioseop_options["aiosp_{$meta}_tags" ] ) )
					$$meta = html_entity_decode( stripslashes( $aioseop_options["aiosp_{$meta}_tags" ] ), ENT_QUOTES );
				else
					$$meta = '';
			}
			if ( is_page() && isset( $page_meta ) && !empty( $page_meta ) && ( !is_front_page() || empty( $front_meta ) ) ) {
				if ( isset( $meta_string ) ) $meta_string .= "\n";
				$meta_string .= $page_meta;
			}
			if ( is_single() && isset( $post_meta ) && !empty( $post_meta ) ) {
				if ( isset( $meta_string ) ) $meta_string .= "\n";
				$meta_string .= $post_meta;
			}
			// handle authorship
			$authorship = $this->get_google_authorship( $post );
			$publisher = apply_filters( 'aioseop_google_publisher', $authorship["publisher"] );
			if ( !empty( $publisher ) )
				$meta_string = '<link rel="publisher" href="' . esc_url( $publisher ) . '" />' . "\n" . $meta_string;
			$author = apply_filters( 'aioseop_google_author', $authorship["author"] );
			if ( !empty( $author ) )
				$meta_string = '<link rel="author" href="' . esc_url( $author ) . '" />' . "\n" . $meta_string;

			if ( is_front_page() && !empty( $front_meta ) ) {
				if ( isset( $meta_string ) ) $meta_string .= "\n";
				$meta_string .= $front_meta;
			} else {
				if ( is_home() && !empty( $home_meta ) ) {
					if ( isset( $meta_string ) ) $meta_string .= "\n";
					$meta_string .= $home_meta;
				}
			}
			$prev_next = $this->get_prev_next_links( $post );
			$prev = apply_filters( 'aioseop_prev_link', $prev_next['prev'] );
			$next = apply_filters( 'aioseop_next_link', $prev_next['next'] );
			if ( !empty( $prev ) ) $meta_string .= "<link rel='prev' href='" . esc_url( $prev ) . "' />\n";
			if ( !empty( $next ) ) $meta_string .= "<link rel='next' href='" . esc_url( $next ) . "' />\n";
			if ( $meta_string != null ) echo "$meta_string\n";
			// handle canonical links
			$show_page = true;
			if ( !empty( $aioseop_options["aiosp_no_paged_canonical_links"] ) ) $show_page = false;

			if ( $aioseop_options['aiosp_can'] ) {
				$url = '';
				if ( !empty( $aioseop_options['aiosp_customize_canonical_links'] ) && !empty( $opts['aiosp_custom_link'] ) ) $url = $opts['aiosp_custom_link'];
				if ( empty( $url ) )
					$url = $this->aiosp_mrt_get_url( $wp_query, $show_page );
				$url = apply_filters( 'aioseop_canonical_url', $url );
				if ( !empty( $url ) )
					echo '<link rel="canonical" href="'. esc_url( $url ) . '" />'."\n";
			}
			do_action( 'aioseop_modules_wp_head' );
			if ( AIOSEOPPRO ) {
			echo "<!-- /all in one seo pack pro -->\n";
			} else{
				echo "<!-- /all in one seo pack -->\n";
			}
	}

	function override_options( $options, $location, $settings ) {
		if ( class_exists( 'DOMDocument' ) ) {
			$options['aiosp_google_connect'] = $settings['aiosp_google_connect']['default'];
		}
		return $options;
	}

	function get_analytics_domain() {
		global $aioseop_options;
		if ( !empty( $aioseop_options['aiosp_ga_domain'] ) )
			return $this->sanitize_domain( $aioseop_options['aiosp_ga_domain'] );
		return '';
	}

	function universal_analytics() {
		global $aioseop_options;
		$analytics = '';
		if ( !empty( $aioseop_options['aiosp_ga_use_universal_analytics'] ) ) {
			$allow_linker = $cookie_domain = $domain = $addl_domains = $domain_list = '';
			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) )
				$cookie_domain = $this->get_analytics_domain();
			if ( !empty( $cookie_domain ) ) {
				$cookie_domain = esc_js( $cookie_domain );
				$cookie_domain = "'cookieDomain': '{$cookie_domain}'";
			}
			if ( empty( $cookie_domain ) ) {
				$domain = ", 'auto'";
			}
			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_multi_domain'] ) ) {
				$allow_linker = "'allowLinker': true";
				if ( !empty( $aioseop_options['aiosp_ga_addl_domains'] ) ) {
					$addl_domains = trim( $aioseop_options['aiosp_ga_addl_domains'] );
					$addl_domains = preg_split('/[\s,]+/', $addl_domains);
					if ( !empty( $addl_domains ) ) {
						foreach( $addl_domains as $d ) {
							$d = $this->sanitize_domain( $d );
							if ( !empty( $d ) ) {
								if ( !empty( $domain_list ) )
									$domain_list .= ", ";
								$domain_list .= "'" . $d . "'";
							}
						}
					}
				}
			}
			$extra_options = '';
			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_display_advertising'] ) ) {
				$extra_options .= "ga('require', 'displayfeatures');";
			}
			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_enhanced_ecommerce'] ) ) {
				if ( !empty( $extra_options ) ) $extra_options .= "\n\t\t\t";
				$extra_options .= "ga('require', 'ec');";
			}
			if ( !empty( $domain_list ) ) {
				if ( !empty( $extra_options ) ) $extra_options .= "\n\t\t\t";
				$extra_options .= "ga('require', 'linker');\n\t\t\tga('linker:autoLink', [{$domain_list}] );";
			}
			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_link_attribution'] ) ) {
				if ( !empty( $extra_options ) ) $extra_options .= "\n\t\t\t";
				$extra_options .= "ga('require', 'linkid', 'linkid.js');";
			}

			if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_anonymize_ip'] ) ) {
				if ( !empty( $extra_options ) ) $extra_options .= "\n\t\t\t";
				$extra_options .= "ga('set', 'anonymizeIp', true);";
			}
			$js_options = Array();
			foreach( Array( 'cookie_domain', 'allow_linker' ) as $opts ) {
				if ( !empty( $$opts ) ) $js_options[] = $$opts;
			}
			if ( !empty( $js_options ) ) {
				$js_options = join( ',', $js_options );
				$js_options = ', { ' . $js_options . ' } ';
			} else $js_options = '';
			$analytics_id = esc_js( $aioseop_options["aiosp_google_analytics_id"] );
			$analytics =<<<EOF
			<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '{$analytics_id}'{$domain}{$js_options});
			{$extra_options}
			ga('send', 'pageview');
			</script>

EOF;
		}
		return $analytics;
	}

function aiosp_google_analytics() {
	global $aioseop_options;
	$analytics = '';
	if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_exclude_users'] ) ) {
		if ( is_user_logged_in() ) {
			global $current_user;
			if ( empty( $current_user ) ) wp_get_current_user();
			if ( !empty( $current_user ) ) {
				$intersect = array_intersect( $aioseop_options['aiosp_ga_exclude_users'], $current_user->roles );
				if ( !empty( $intersect ) ) return;
			}
		}
	}
	if ( !empty( $aioseop_options['aiosp_google_analytics_id'] ) ) {
	ob_start();
	$analytics = $this->universal_analytics();
	echo $analytics;
	if ( empty( $analytics ) ) {
?>		<script type="text/javascript">
		  var _gaq = _gaq || [];
<?php if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_link_attribution'] ) ) {
?>		var pluginUrl =
 		'//www.google-analytics.com/plugins/ga/inpage_linkid.js';
		_gaq.push(['_require', 'inpage_linkid', pluginUrl]);
<?php
}
?>		  _gaq.push(['_setAccount', '<?php
			echo $aioseop_options['aiosp_google_analytics_id'];
		  ?>']);
<?php if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_anonymize_ip'] ) ) {
?>		  _gaq.push(['_gat._anonymizeIp']);
<?php
}
?>
<?php if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_multi_domain'] ) ) {
?>		  _gaq.push(['_setAllowLinker', true]);
<?php
}
?>
<?php if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_domain'] ) ) {
		  $domain = $this->get_analytics_domain();
?>		  _gaq.push(['_setDomainName', '<?php echo $domain; ?>']);
<?php
}
?>		  _gaq.push(['_trackPageview']);
		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
<?php
		if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && !empty( $aioseop_options['aiosp_ga_display_advertising'] ) ) {
?>			ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
<?php
		} else {
?>			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
<?php
		}
?>		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
<?php
	}
	if ( !empty( $aioseop_options['aiosp_ga_advanced_options'] ) && $aioseop_options['aiosp_ga_track_outbound_links'] ) { ?>
		<script type="text/javascript">
		function recordOutboundLink(link, category, action) {
		<?php if ( !empty( $aioseop_options['aiosp_ga_use_universal_analytics'] ) ) { ?>
			ga('send', 'event', category, action);
		<?php }
		 	  if ( empty( $aioseop_options['aiosp_ga_use_universal_analytics'] ) ) {	?>
			_gat._getTrackerByName()._trackEvent(category, action);
		<?php } ?>
			if ( link.target == '_blank' ) return true;
			setTimeout('document.location = "' + link.href + '"', 100);
			return false;
		}
			/* use regular Javascript for this */
			function getAttr(ele, attr) {
				var result = (ele.getAttribute && ele.getAttribute(attr)) || null;
				if( !result ) {
					var attrs = ele.attributes;
					var length = attrs.length;
					for(var i = 0; i < length; i++)
					if(attr[i].nodeName === attr) result = attr[i].nodeValue;
				}
				return result;
			}

			function aiosp_addLoadEvent(func) {
			  var oldonload = window.onload;
			  if (typeof window.onload != 'function') {
			    window.onload = func;
			  } else {
			    window.onload = function() {
			      if (oldonload) {
			        oldonload();
			      }
			      func();
			    }
			  }
			}

			function aiosp_addEvent(element, evnt, funct){
			  if (element.attachEvent)
			   return element.attachEvent('on'+evnt, funct);
			  else
			   return element.addEventListener(evnt, funct, false);
			}

			aiosp_addLoadEvent(function () {
				var links = document.getElementsByTagName('a');
				for (var x=0; x < links.length; x++) {
					if (typeof links[x] == 'undefined') continue;
					aiosp_addEvent( links[x], 'onclick', function () {
						var mydomain = new RegExp(document.domain, 'i');
						href = getAttr(this, 'href');
						if (href && href.toLowerCase().indexOf('http') === 0 && !mydomain.test(href)) {
							recordOutboundLink(this, 'Outbound Links', href);
						}
					});
				}
			});
		</script>
<?php
		}
	$analytics = ob_get_clean();
	}
	echo apply_filters( 'aiosp_google_analytics', $analytics );
}

	function sitelinks_search_box() {
		global $aioseop_options;
		$home_url = esc_url( get_home_url() );
		$name_block = $search_block = '';
		if ( !empty( $aioseop_options["aiosp_google_set_site_name"] ) ) {
			if ( !empty( $aioseop_options["aiosp_google_specify_site_name"] ) ) {
				$blog_name = $aioseop_options["aiosp_google_specify_site_name"];
			} else {
				$blog_name = get_bloginfo( 'name' );
			}
			$blog_name = esc_attr( $blog_name );
			$name_block=<<<EOF
		  "name": "{$blog_name}",
EOF;
		}

		if ( !empty( $aioseop_options["aiosp_google_sitelinks_search"] ) ) {
			$search_block=<<<EOF
        "potentialAction": {
          "@type": "SearchAction",
          "target": "{$home_url}/?s={search_term}",
          "query-input": "required name=search_term"
        },
EOF;
		}

		$search_box=<<<EOF
<script type="application/ld+json">
        {
          "@context": "http://schema.org",
          "@type": "WebSite",
EOF;
		if ( !empty( $name_block ) )   $search_box .= $name_block;
		if ( !empty( $search_block ) ) $search_box .= $search_block;
		$search_box.=<<<EOF
		  "url": "{$home_url}/"
        }
</script>
EOF;
		return apply_filters( 'aiosp_sitelinks_search_box', $search_box );
	}

	function aiosp_mrt_get_url( $query, $show_page = true ) {
		if ( $query->is_404 || $query->is_search )
			return false;
		$link = '';
		$haspost = count( $query->posts ) > 0;
		if ( get_query_var( 'm' ) ) {
			$m = preg_replace( '/[^0-9]/', '', get_query_var( 'm' ) );
			switch ( $this->strlen( $m ) ) {
				case 4: $link = get_year_link( $m ); break;
        		case 6: $link = get_month_link( $this->substr( $m, 0, 4), $this->substr($m, 4, 2 ) ); break;
        		case 8: $link = get_day_link( $this->substr( $m, 0, 4 ), $this->substr( $m, 4, 2 ), $this->substr( $m, 6, 2 ) ); break;
       			default:
       			return false;
			}
		} elseif ( ( $query->is_home && (get_option( 'show_on_front' ) == 'page' ) && ( $pageid = get_option( 'page_for_posts' ) ) ) ) {
			$link = get_permalink( $pageid );
		} elseif ( is_front_page() || ( $query->is_home && ( get_option( 'show_on_front' ) != 'page' || !get_option( 'page_for_posts' ) ) ) ) {
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
     		if ($author === false) return false;
			$link = get_author_posts_url( $author->ID, $author->user_nicename );
  		} elseif ( $query->is_category && $haspost ) {
    		$link = get_category_link( get_query_var( 'cat' ) );
		} elseif ( $query->is_tag && $haspost ) {
			$tag = get_term_by( 'slug', get_query_var( 'tag' ), 'post_tag' );
       		if ( !empty( $tag->term_id ) )
				$link = get_tag_link( $tag->term_id );
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
			$term = get_query_var( 'term' );
			if ( !empty( $term ) )
				$link = get_term_link( $term, $taxonomy );
        } elseif ( $query->is_archive && function_exists( 'get_post_type_archive_link' ) && ( $post_type = get_query_var( 'post_type' ) ) ) {
			if ( is_array( $post_type ) )
				$post_type = reset( $post_type );
			$link = get_post_type_archive_link( $post_type );
	    } else {
	        return false;
	    }
		if ( empty( $link ) || !is_string( $link ) ) return false;
		if ( apply_filters( 'aioseop_canonical_url_pagination', $show_page ) )
			$link = $this->get_paged( $link );
		if ( !empty( $link ) ) {
			global $aioseop_options;
			if ( !empty( $aioseop_options['aiosp_can_set_protocol'] ) && ( $aioseop_options['aiosp_can_set_protocol'] != 'auto' ) ) {
				if ( $aioseop_options['aiosp_can_set_protocol'] == 'http' ) {
					$link = preg_replace("/^https:/i", "http:", $link );
				} elseif ( $aioseop_options['aiosp_can_set_protocol'] == 'https' ) {
					$link = preg_replace("/^http:/i", "https:", $link );
				}
			}
		}
		return $link;
	}

	function get_page_number() {
		$page = get_query_var( 'page' );
		if ( empty( $page ) )
			$page = get_query_var( 'paged' );
		return $page;
	}

	function get_paged( $link ) {
		global $wp_rewrite;
		$page = $this->get_page_number();
		$page_name = 'page';
		if ( !empty( $wp_rewrite ) && !empty( $wp_rewrite->pagination_base ) ) $page_name = $wp_rewrite->pagination_base;
        if ( !empty( $page ) && $page > 1 ) {
			if ( get_query_var( 'page' ) == $page )
				$link = trailingslashit( $link ) . "$page";
			else
				$link = trailingslashit( $link ) . trailingslashit( $page_name ) . $page;
			$link = user_trailingslashit( $link, 'paged' );
		}
		return $link;
	}

	function is_singular( $post_types = Array(), $post = null ) {
		if ( !empty( $post_types ) && is_object( $post ) )
			return in_array( $post->post_type, (array)$post_types );
		else
			return is_singular( $post_types );
	}

	function show_page_description() {
		global $aioseop_options;
		if ( !empty( $aioseop_options['aiosp_hide_paginated_descriptions'] ) ) {
			$page = $this->get_page_number();
			if ( !empty( $page ) && ( $page > 1 ) )
				return false;
		}
		return true;
	}

	function get_post_description( $post ) {
		global $aioseop_options;
		$description = '';
		if ( !$this->show_page_description() ) {
			return '';
		}
	    $description = trim( ( $this->internationalize( get_post_meta( $post->ID, "_aioseop_description", true ) ) ) );
		if ( !empty( $post ) && post_password_required( $post ) ) {
			return $description;
		}
		if ( !$description ) {
			if ( empty( $aioseop_options["aiosp_skip_excerpt"] ) )
				$description = $this->trim_excerpt_without_filters_full_length( $this->internationalize( $post->post_excerpt ) );
			if ( !$description && $aioseop_options["aiosp_generate_descriptions"] ) {
				$content = $post->post_content;
				if ( !empty( $aioseop_options["aiosp_run_shortcodes"] ) ) $content = do_shortcode( $content );
				$content = wp_strip_all_tags( $content );
				$description = $this->trim_excerpt_without_filters( $this->internationalize( $content ) );
			}
		}

		// "internal whitespace trim"
		$description = preg_replace( "/\s\s+/u", " ", $description );
		return $description;
	}


	function get_aioseop_description( $post = null ) {
		global $aioseop_options;
		if ( $post === null )
			$post = $GLOBALS["post"];
		$blog_page = aiosp_common::get_blog_page();
		$description = '';
		if ( is_front_page() && empty( $aioseop_options['aiosp_use_static_home_info'] ) )
			$description = trim( ( $this->internationalize( $aioseop_options['aiosp_home_description'] ) ) );
		elseif ( !empty( $blog_page ) )
			$description = $this->get_post_description( $blog_page );
		if ( empty( $description ) && is_object( $post ) && !is_archive() && empty( $blog_page ) )
			$description = $this->get_post_description( $post );
		$description = $this->apply_cf_fields( $description );
		return $description;
	}

	function replace_title( $content, $title ) {
		//We can probably improve this... I'm not sure half of this is even being used.
		$title = trim( strip_tags( $title ) );
		$title_tag_start = "<title";
		$title_tag_end = "</title";
		$start = $this->strpos( $content, $title_tag_start );
		$end = $this->strpos( $content, $title_tag_end );
		$this->title_start = $start;
		$this->title_end = $end;
		$this->orig_title = $title;

		return preg_replace( '/<title([^>]*?)\s*>([^<]*?)<\/title\s*>/is', '<title\\1>' . preg_replace('/(\$|\\\\)(?=\d)/', '\\\\\1', strip_tags( $title ) ) . '</title>', $content, 1 );
	}

	function internationalize( $in ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) )
			$in = langswitch_filter_langs_with_message( $in );

		if ( function_exists( 'polyglot_filter' ) )
			$in = polyglot_filter( $in );

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'localization', $in );
	}

	/** @return The original title as delivered by WP (well, in most cases) */
	function get_original_title( $sep = '|', $echo = false, $seplocation = '' ) {
		global $aioseop_options;
		if ( !empty( $aioseop_options['aiosp_use_original_title'] ) ) {
			$has_filter = has_filter( 'wp_title', Array( $this, 'wp_title' ) );
			if ( $has_filter !== false )
				remove_filter( 'wp_title', Array( $this, 'wp_title' ), $has_filter );
			if ( current_theme_supports( 'title-tag' ) ) {
				$sep = '|';
				$echo = false;
				$seplocation = 'right';
			}
			$title = wp_title( $sep, $echo, $seplocation );
			if ( $has_filter !== false )
				add_filter( 'wp_title', Array( $this, 'wp_title' ), $has_filter );
			if ( $title && ( $title = trim( $title ) ) )
				return trim( $title );
		}

		// the_search_query() is not suitable, it cannot just return
		global $s;

		$title = null;

		if ( is_home() ) {
			$title = get_option( 'blogname' );
		} else if ( is_single() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} else if ( is_search() && isset($s) && !empty($s) ) {
			$search = esc_attr( stripslashes($s) );
			if ( !empty( $aioseop_options['aiosp_cap_titles'] ) )
				$search = $this->capitalize( $search );
			$title = $search;
		} else if ( ( is_tax() || is_category() ) && !is_feed() ) {
			$category_name = $this->ucwords($this->internationalize( single_cat_title( '', false ) ) );
			$title = $category_name;
		} else if ( is_page() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} else if ( is_tag() ) {
			global $utw;
			if ( $utw ) {
				$tags = $utw->GetCurrentTagSet();
				$tag = $tags[0]->tag;
		        $tag = str_replace('-', ' ', $tag);
			} else {
				// wordpress > 2.3
				$tag = $this->internationalize( single_term_title( '', false ) );
			}
			if ( $tag ) $title = $tag;
		} else if ( is_author() ) {
			$author = get_userdata( get_query_var( 'author' ) );
			if ( $author === false ) {
				global $wp_query;
				$author = $wp_query->get_queried_object();
			}
			if ($author !== false)
				$title = $author->display_name;
		} else if ( is_day() ) {
			$title = get_the_date();
		} else if ( is_month() ) {
			$title = get_the_date( 'F, Y' );
		} else if ( is_year() ) {
			$title = get_the_date( 'Y' );
		} else if ( is_archive() ) {
			$title = $this->internationalize( post_type_archive_title( '', false) );
		} else if ( is_404() ) {
		    $title_format = $aioseop_options['aiosp_404_title_format'];
		    $new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
		    if ( strpos( $new_title, '%blog_description%' ) !== false ) $new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
		    if ( strpos( $new_title, '%request_url%'	  ) !== false ) $new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title);
		    if ( strpos( $new_title, '%request_words%'	  ) !== false ) $new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			$title = $new_title;
		}
		return trim( $title );
	}

	function paged_title( $title ) {
		// the page number if paged
		global $paged;
		global $aioseop_options;
		// simple tagging support
		global $STagging;
		$page = get_query_var( 'page' );
		if ( $paged > $page ) $page = $paged;
		if ( is_paged() || ( isset($STagging) && $STagging->is_tag_view() && $paged ) || ( $page > 1 ) ) {
			$part = $this->internationalize( $aioseop_options['aiosp_paged_format'] );
			if ( isset( $part ) || !empty( $part ) ) {
				$part = " " . trim( $part );
				$part = str_replace( '%page%', $page, $part );
				$this->log( "paged_title() [$title] [$part]" );
				$title .= $part;
			}
		}
		return $title;
	}

	function get_tax_title_format( $tax = '' ) {
		global $aioseop_options;
		if ( AIOSEOPPRO ){
		$title_format = '%taxonomy_title% | %blog_title%';
		if ( is_category() ) {
			$title_format = $aioseop_options['aiosp_category_title_format'];
		} else {
			$taxes = $aioseop_options['aiosp_taxactive'];
			if ( empty( $tax ) )
				$tax = get_query_var( 'taxonomy' );
			if ( !empty( $aioseop_options["aiosp_{$tax}_tax_title_format"] ) )
				$title_format = $aioseop_options["aiosp_{$tax}_tax_title_format"];
		}
		if ( empty( $title_format ) )
			$title_format = '%category_title% | %blog_title%';
		} else {
			$title_format = '%category_title% | %blog_title%';
			if ( !empty( $aioseop_options['aiosp_category_title_format'] ) )
				$title_format = $aioseop_options['aiosp_category_title_format'];
			return $title_format;
		}
		return $title_format;
	}

	function apply_tax_title_format( $category_name, $category_description, $tax = '' ) {
		if ( empty( $tax ) ) $tax = get_query_var( 'taxonomy' );
        $title_format = $this->get_tax_title_format( $tax );
        $title = str_replace( '%taxonomy_title%', $category_name, $title_format );
        if ( strpos( $title, '%taxonomy_description%' ) !== false ) $title = str_replace( '%taxonomy_description%', $category_description, $title );
        if ( strpos( $title, '%category_title%'		  ) !== false ) $title = str_replace( '%category_title%', $category_name, $title );
        if ( strpos( $title, '%category_description%' ) !== false ) $title = str_replace( '%category_description%', $category_description, $title );
        if ( strpos( $title, '%blog_title%'			  ) !== false ) $title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
        if ( strpos( $title, '%blog_description%'	  ) !== false ) $title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
		$title = wp_strip_all_tags( $title );
        return $this->paged_title( $title );
	}

	function get_tax_name( $tax ) {
		global $aioseop_options;
		if ( AIOSEOPPRO ){
		$opts = $this->meta_opts;
		if ( !empty( $opts ) )
			$name = $opts['aiosp_title'];
		} else {
			$name = '';
		}
		if ( empty( $name ) ) $name = single_term_title( '', false );
		//apparently we're already ucwordsing this elsewhere, and doing it a second time messes it up... why aren't we just doing this at the end??
		//		if ( ( $tax == 'category' ) && ( !empty( $aioseop_options['aiosp_cap_cats'] ) ) )
		//				$name = $this->ucwords( $name );

		return $this->internationalize( $name );
	}

	function get_tax_desc( $tax ) {
		if ( AIOSEOPPRO ) {
		$opts = $this->meta_opts;
		if ( !empty( $opts ) )
			$desc = $opts['aiosp_description'];
		} else {
			$desc = '';
		}
		if ( empty( $desc ) ) $desc = term_description( '', $tax );
		return $this->internationalize( $desc );
	}

	function get_tax_title( $tax = '' ) {
		if ( AIOSEOPPRO ){
		if ( empty( $this->meta_opts ) )
			$this->meta_opts = $this->get_current_options( Array(), 'aiosp' );
		}
		if ( empty( $tax ) )
			if ( is_category() )
				$tax = 'category';
			else
				$tax = get_query_var( 'taxonomy' );
		$name = $this->get_tax_name( $tax );
		$desc = $this->get_tax_desc( $tax );
		return $this->apply_tax_title_format( $name, $desc, $tax );
	}

	function get_post_title_format( $title_type = 'post', $p = null ) {
		global $aioseop_options;
		if ( ( $title_type != 'post' ) && ( $title_type != 'archive' ) ) return false;
		$title_format = "%{$title_type}_title% | %blog_title%";
		if ( isset( $aioseop_options["aiosp_{$title_type}_title_format"] ) )
			$title_format = $aioseop_options["aiosp_{$title_type}_title_format"];
		if( !empty( $aioseop_options['aiosp_enablecpost'] ) && !empty( $aioseop_options['aiosp_cpostactive'] ) ) {
			$wp_post_types = $aioseop_options['aiosp_cpostactive'];
			if ( !empty( $aioseop_options["aiosp_cposttitles"] ) ) {
				if ( ( ( $title_type == 'archive' ) && is_post_type_archive( $wp_post_types ) && $prefix = "aiosp_{$title_type}_" ) ||
					( ( $title_type == 'post' ) && $this->is_singular( $wp_post_types, $p ) && $prefix = "aiosp_" ) ) {
						$post_type = get_post_type( $p );
						if ( !empty( $aioseop_options["{$prefix}{$post_type}_title_format"] ) ) {
							$title_format = $aioseop_options["{$prefix}{$post_type}_title_format"];
						}
				}
			}
		}
		return $title_format;
	}

	function get_archive_title_format() {
		return $this->get_post_title_format( "archive" );
	}

	function apply_archive_title_format( $title, $category = '' ) {
		$title_format = $this->get_archive_title_format();
		$r_title = array( '%blog_title%', '%blog_description%', '%archive_title%' );
		$d_title = array( $this->internationalize( get_bloginfo('name') ), $this->internationalize( get_bloginfo( 'description' ) ), post_type_archive_title( '', false ) );
		$title = trim( str_replace( $r_title, $d_title, $title_format ) );
		return $title;
	}

	function title_placeholder_helper( $title, $post, $type = 'post', $title_format = '', $category = '' ) {
		if ( !empty( $post ) )
			$authordata = get_userdata( $post->post_author );
		else
			$authordata = new WP_User();
		$new_title = str_replace( "%blog_title%", $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
        if ( strpos( $new_title, "%blog_description%"			) !== false ) $new_title = str_replace( "%blog_description%", $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
        if ( strpos( $new_title, "%{$type}_title%"				) !== false ) $new_title = str_replace( "%{$type}_title%", $title, $new_title );
		if ( $type == 'post' ) {
	        if ( strpos( $new_title, "%category%"				) !== false ) $new_title = str_replace( "%category%", $category, $new_title );
	        if ( strpos( $new_title, "%category_title%"			) !== false ) $new_title = str_replace( "%category_title%", $category, $new_title );
			if ( strpos( $new_title, "%tax_" ) && !empty( $post ) ) {
				$taxes = get_object_taxonomies( $post, 'objects' );
				if ( !empty( $taxes ) )
					foreach( $taxes as $t )
						if ( strpos( $new_title, "%tax_{$t->name}%" ) ) {
							$terms = $this->get_all_terms( $post->ID, $t->name );
							$term = '';
							if ( count( $terms ) > 0 )
								$term = $terms[0];
							$new_title = str_replace( "%tax_{$t->name}%", $term, $new_title );
						}
			}
		}
        if ( strpos( $new_title, "%{$type}_author_login%"		) !== false ) $new_title = str_replace( "%{$type}_author_login%", $authordata->user_login, $new_title );
        if ( strpos( $new_title, "%{$type}_author_nicename%"	) !== false ) $new_title = str_replace( "%{$type}_author_nicename%", $authordata->user_nicename, $new_title );
        if ( strpos( $new_title, "%{$type}_author_firstname%"	) !== false ) $new_title = str_replace( "%{$type}_author_firstname%", $this->ucwords($authordata->first_name ), $new_title );
        if ( strpos( $new_title, "%{$type}_author_lastname%"	) !== false ) $new_title = str_replace( "%{$type}_author_lastname%", $this->ucwords($authordata->last_name ), $new_title );
		$title = trim( $new_title );
		return $title;
	}

	function apply_post_title_format( $title, $category = '', $p = null ) {
		if ( $p === null ) {
			global $post;
		} else {
			$post = $p;
		}
		$title_format = $this->get_post_title_format( 'post', $post );
		return $this->title_placeholder_helper( $title, $post, 'post', $title_format, $category );
	}

	function apply_page_title_format( $title, $p = null, $title_format = '' ) {
		global $aioseop_options;
		if ( $p === null ) {
			global $post;
		} else {
			$post = $p;
		}
		if ( empty( $title_format ) )
			$title_format = $aioseop_options['aiosp_page_title_format'];
		return $this->title_placeholder_helper( $title, $post, 'page', $title_format );
	}

	/*** Gets the title that will be used by AIOSEOP for title rewrites or returns false. ***/
	function get_aioseop_title( $post ) {
		global $aioseop_options;
		// the_search_query() is not suitable, it cannot just return
		global $s, $STagging;
		$opts = $this->meta_opts;
		if ( is_front_page() ) {
			if ( !empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				global $post;
				if ( get_option( 'show_on_front' ) == 'page' && is_page() && $post->ID == get_option( 'page_on_front' ) ) {
					$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
					if ( !$title ) $title = $this->internationalize( $post->post_title );
					if ( !$title ) $title = $this->internationalize( $this->get_original_title( '', false ) );
					if ( !empty( $aioseop_options['aiosp_home_page_title_format'] ) )
						$title = $this->apply_page_title_format( $title, $post, $aioseop_options['aiosp_home_page_title_format'] );
	                $title = $this->paged_title( $title );
					$title = apply_filters( 'aioseop_home_page_title', $title );
				}
			} else {
				$title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
				if ( !empty( $aioseop_options['aiosp_home_page_title_format'] ) )
					$title = $this->apply_page_title_format( $title, null, $aioseop_options['aiosp_home_page_title_format'] );
			}
			if (empty( $title ) )
				$title = $this->internationalize( get_option( 'blogname' ) ) . ' | ' . $this->internationalize( get_bloginfo( 'description' ) );

				global $post;
				$post_id = $post->ID;

			if ( is_post_type_archive() && is_post_type_archive( 'product' ) && $post_id = woocommerce_get_page_id( 'shop' ) &&  $post = get_post( $post_id ) ){
				$frontpage_id = get_option('page_on_front');

				if ( woocommerce_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && !empty( $aioseop_options['aiosp_use_static_home_info'] ) ){
					$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
				}
				//$title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
				if ( !$title ) $title = $this->internationalize( get_post_meta( $frontpage_id, "_aioseop_title", true ) ); //this is/was causing the first product to come through
				if ( !$title ) $title = $this->internationalize( $post->post_title );
				if ( !$title ) $title = $this->internationalize( $this->get_original_title( '', false ) );



				$title = $this->apply_page_title_format( $title, $post );
	            $title = $this->paged_title( $title );
				$title = apply_filters( 'aioseop_title_page', $title );
				return $title;

			}

			return $this->paged_title( $title ); //this is returned for woo
		} else if ( is_attachment() ) {
			if ( $post === null ) return false;
			$title = get_post_meta( $post->ID, "_aioseop_title", true );
			if ( empty( $title ) ) $title = $post->post_title;
			if ( empty( $title ) ) $title = $this->get_original_title( '', false );
			if ( empty( $title ) ) $title = get_the_title( $post->post_parent );
			$title = apply_filters( 'aioseop_attachment_title', $this->internationalize( $this->apply_post_title_format( $title, '', $post ) ) );
			return $title;
		} else if ( is_page() || $this->is_static_posts_page() || ( is_home() && !$this->is_static_posts_page() ) ) {
			if ( $post === null ) return false;
			if ( ( $this->is_static_front_page() ) && ( $home_title = $this->internationalize( $aioseop_options['aiosp_home_title'] ) ) ) {
				if ( !empty( $aioseop_options['aiosp_home_page_title_format'] ) )
					$home_title = $this->apply_page_title_format( $home_title, $post, $aioseop_options['aiosp_home_page_title_format'] );
				//home title filter
				return apply_filters( 'aioseop_home_page_title', $home_title );
			} else {
				$page_for_posts = '';
				if ( is_home() )
					$page_for_posts = get_option( 'page_for_posts' );
				if ( $page_for_posts ) {
					$title = $this->internationalize( get_post_meta( $page_for_posts, "_aioseop_title", true ) );
					if ( !$title ) {
						$post_page = get_post( $page_for_posts );
						$title = $this->internationalize( $post_page->post_title );
					}
				} else {
					$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
					if ( !$title )
						$title = $this->internationalize( $post->post_title );
				}
				if ( !$title )
					$title = $this->internationalize( $this->get_original_title( '', false ) );

				$title = $this->apply_page_title_format( $title, $post );
                $title = $this->paged_title( $title );
				$title = apply_filters( 'aioseop_title_page', $title );
				if ( $this->is_static_posts_page() )
					$title = apply_filters( 'single_post_title', $title );
				return $title;
			}
		} else if ( function_exists( 'woocommerce_get_page_id' ) && is_post_type_archive( 'product' ) && ( $post_id = woocommerce_get_page_id( 'shop' ) ) && ( $post = get_post( $post_id ) ) ) {
			//too far down? -mrt
			$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
			if ( !$title ) $title = $this->internationalize( $post->post_title );
			if ( !$title ) $title = $this->internationalize( $this->get_original_title( '', false ) );
			$title = $this->apply_page_title_format( $title, $post );
            $title = $this->paged_title( $title );
			$title = apply_filters( 'aioseop_title_page', $title );
			return $title;
		} else if ( is_single() ) {
			// we're not in the loop :(
			if ( $post === null ) return false;
			$categories = $this->get_all_categories();
			$category = '';
			if ( count( $categories ) > 0 )
				$category = $categories[0];
			$title = $this->internationalize( get_post_meta( $post->ID, "_aioseop_title", true ) );
			if ( !$title ) {
				$title = $this->internationalize( get_post_meta( $post->ID, "title_tag", true ) );
				if ( !$title ) $title = $this->internationalize($this->get_original_title( '', false ) );
			}
			if ( empty( $title ) ) $title = $post->post_title;
			if ( !empty( $title ) )
				$title = $this->apply_post_title_format( $title, $category, $post );
			$title = $this->paged_title( $title );
			return apply_filters( 'aioseop_title_single', $title );
		} else if ( is_search() && isset( $s ) && !empty( $s ) ) {
			$search = esc_attr( stripslashes( $s ) );
			if ( !empty( $aioseop_options['aiosp_cap_titles'] ) )
				$search = $this->capitalize( $search );
            $title_format = $aioseop_options['aiosp_search_title_format'];
            $title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
            if ( strpos( $title, '%blog_description%' ) !== false ) $title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
            if ( strpos( $title, '%search%'			  ) !== false ) $title = str_replace( '%search%', $search, $title );
			$title = $this->paged_title( $title );
			return $title;
		} else if ( is_tag() ) {
			global $utw;
			$tag = $tag_description = '';
			if ( $utw ) {
				$tags = $utw->GetCurrentTagSet();
				$tag = $tags[0]->tag;
	            $tag = str_replace('-', ' ', $tag);
			} else {
				if ( AIOSEOPPRO ){
				if ( !empty( $opts ) && !empty( $opts['aiosp_title'] ) ) $tag = $opts['aiosp_title'];
				if ( !empty( $opts ) ) {
					if ( !empty( $opts['aiosp_title'] ) ) $tag = $opts['aiosp_title'];
					if ( !empty( $opts['aiosp_description'] ) ) $tag_description = $opts['aiosp_description'];
					}
				}
				if ( empty( $tag ) ) $tag = $this->get_original_title( '', false );
				if ( empty( $tag_description ) ) $tag_description = tag_description();
				$tag = $this->internationalize( $tag );
				$tag_description = $this->internationalize( $tag_description );
			}
			if ( $tag ) {
				if ( !empty( $aioseop_options['aiosp_cap_titles'] ) )
					$tag = $this->capitalize( $tag );
	            $title_format = $aioseop_options['aiosp_tag_title_format'];
	            $title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo('name') ), $title_format );
	            if ( strpos( $title, '%blog_description%' ) !== false ) $title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description') ), $title );
	            if ( strpos( $title, '%tag%'			  ) !== false ) $title = str_replace( '%tag%', $tag, $title );
		        if ( strpos( $title, '%tag_description%' ) !== false ) $title = str_replace( '%tag_description%', $tag_description, $title );
		        if ( strpos( $title, '%taxonomy_description%' ) !== false ) $title = str_replace( '%taxonomy_description%', $tag_description, $title );
				$title = trim( wp_strip_all_tags( $title ) );
				$title = str_replace( Array( '"', "\r\n", "\n" ), Array( '&quot;', ' ', ' ' ), $title );
	            $title = $this->paged_title( $title );
				return $title;
			}
		} else if ( ( is_tax() || is_category() ) && !is_feed() ) {
			return $this->get_tax_title();
		} else if ( isset( $STagging ) && $STagging->is_tag_view() ) { // simple tagging support
			$tag = $STagging->search_tag;
			if ( $tag ) {
				if ( !empty( $aioseop_options['aiosp_cap_titles'] ) )
					$tag = $this->capitalize($tag);
	            $title_format = $aioseop_options['aiosp_tag_title_format'];
	            $title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name') ), $title_format);
	            if ( strpos( $title, '%blog_description%' ) !== false ) $title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description') ), $title);
	            if ( strpos( $title, '%tag%'			  ) !== false ) $title = str_replace( '%tag%', $tag, $title );
	            $title = $this->paged_title( $title );
				return $title;
			}
		} else if ( is_archive() || is_post_type_archive() ) {
			if ( is_author() ) {
				$author = $this->internationalize( $this->get_original_title( '', false ) );
	            $title_format = $aioseop_options['aiosp_author_title_format'];
	            $new_title = str_replace( '%author%', $author, $title_format );
			} else if ( is_date() ) {
				global $wp_query;
				$date = $this->internationalize( $this->get_original_title( '', false ) );
	            $title_format = $aioseop_options['aiosp_date_title_format'];
	            $new_title = str_replace( '%date%', $date, $title_format );
				$day = get_query_var( 'day' );
				if ( empty( $day ) ) $day = '';
				$new_title = str_replace( '%day%', $day, $new_title );
				$monthnum = get_query_var( 'monthnum' );
				$year = get_query_var( 'year' );
				if ( empty( $monthnum ) || is_year() ) {
					$month = '';
					$monthnum = 0;
				}
				$month = date( "F", mktime( 0,0,0,(int)$monthnum,1,(int)$year ) );
				$new_title = str_replace( '%monthnum%', $monthnum, $new_title );
				if ( strpos( $new_title, '%month%' ) !== false ) $new_title = str_replace( '%month%', $month, $new_title );
	            if ( strpos( $new_title, '%year%' ) !== false ) $new_title = str_replace( '%year%', get_query_var( 'year' ), $new_title );
			} else if ( is_post_type_archive() ) {
				if ( empty( $title ) ) $title = $this->get_original_title( '', false );
				$new_title = apply_filters( 'aioseop_archive_title', $this->apply_archive_title_format( $title ) );
			} else return false;
			$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
            if ( strpos( $new_title, '%blog_description%' ) !== false ) $new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			$title = trim( $new_title );
            $title = $this->paged_title( $title );
			return $title;
		} else if ( is_404() ) {
            $title_format = $aioseop_options['aiosp_404_title_format'];
			$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name') ), $title_format );
            if ( strpos( $new_title, '%blog_description%' ) !== false ) $new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
            if ( strpos( $new_title, '%request_url%' ) !== false	  ) $new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title );
            if ( strpos( $new_title, '%request_words%' ) !== false	  ) $new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			if ( strpos( $new_title, '%404_title%' ) !== false		  ) $new_title = str_replace( '%404_title%', $this->internationalize( $this->get_original_title( '', false ) ), $new_title );
			return $new_title;
		}
		return false;
	}

	/*** Used to filter wp_title(), get our title. ***/
	function wp_title() {
		global $aioseop_options;
		$title = false;
		$post = $this->get_queried_object();
		if ( !empty( $aioseop_options['aiosp_rewrite_titles'] ) ) {
			$title = $this->get_aioseop_title( $post );
			$title = $this->apply_cf_fields( $title );
		}

		if ( $title === false )
			$title = $this->get_original_title();

		//if we're going to have this here, which seems logical, we should probably take it out of other places... do all titles pass through here?
		// The following lines have been commented out to fix an error with Capitalize Titles as reported in the WP forums
		// if ( !empty( $aioseop_options['aiosp_cap_titles'] ) )
		//	$title = $this->capitalize( $title );

		return apply_filters( 'aioseop_title', $title );
	}

	/*** Used for forcing title rewrites. ***/
	function rewrite_title($header) {
		global $wp_query;
		if (!$wp_query) {
			$header .= "<!-- no wp_query found! -->\n";
			return $header;
		}
		$title = $this->wp_title();
		if ( !empty( $title ) )
			$header = $this->replace_title( $header, $title );
		return $header;
	}

	/**
	 * @return User-readable nice words for a given request.
	 */
	function request_as_words( $request ) {
		$request = htmlspecialchars( $request );
		$request = str_replace( '.html', ' ', $request );
		$request = str_replace( '.htm', ' ', $request );
		$request = str_replace( '.', ' ', $request );
		$request = str_replace( '/', ' ', $request );
		$request = str_replace( '-', ' ', $request );
		$request_a = explode( ' ', $request );
		$request_new = array();
		foreach ( $request_a as $token ) {
			$request_new[] = $this->ucwords( trim( $token ) );
		}
		$request = implode( ' ', $request_new );
		return $request;
	}

	function capitalize( $s ) {
		$s = trim( $s );
		$tokens = explode( ' ', $s );
		while ( list( $key, $val ) = each( $tokens ) ) {
			$tokens[ $key ] = trim( $tokens[ $key ] );
			$tokens[ $key ] = $this->strtoupper( $this->substr( $tokens[$key], 0, 1 ) ) . $this->substr( $tokens[$key], 1 );
		}
		$s = implode( ' ', $tokens );
		return $s;
	}

	function trim_excerpt_without_filters( $text, $max = 0 ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text );
		$text = wp_strip_all_tags( $text );
		// Treat other common word-break characters like a space
		$text2 = preg_replace( '/[,._\-=+&!\?;:*]/s', ' ', $text );
		if ( !$max ) $max = $this->maximum_description_length;
		$max_orig = $max;
		$len = $this->strlen( $text2 );
		if ( $max < $len ) {
			if ( function_exists( 'mb_strrpos' ) ) {
				$pos = mb_strrpos( $text2, ' ', -($len - $max) );
				if ( $pos === false ) $pos = $max;
				if ( $pos > $this->minimum_description_length ) {
					$max = $pos;
				} else {
					$max = $this->minimum_description_length;
				}
			} else {
				while( $text2[$max] != ' ' && $max > $this->minimum_description_length ) {
					$max--;
				}
			}

			// probably no valid chars to break on?
			if ( $len > $max_orig && $max < intval( $max_orig / 2 ) ) {
				$max = $max_orig;
			}
		}
		$text = $this->substr( $text, 0, $max );
		return trim( $text );
	}

	function trim_excerpt_without_filters_full_length( $text ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
                $text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $text );
		$text = wp_strip_all_tags( $text );
		return trim( ( $text ) );
	}

	function keyword_string_to_list( $keywords ) {
		$traverse = Array();
		$keywords_i = str_replace( '"', '', $keywords );
        if (isset( $keywords_i ) && !empty( $keywords_i ) ) {
        	$traverse = explode( ',', $keywords_i );
        }
		return $traverse;
	}

	function get_all_categories( $id = 0 ) {
		$keywords = Array();
		$categories = get_the_category( $id );
		if ( !empty( $categories ) )
			foreach ( $categories as $category )
				$keywords[] = $this->internationalize( $category->cat_name );
        return $keywords;
	}

	function get_all_tags( $id = 0 ) {
		$keywords = Array();
		$tags = get_the_tags( $id );
        if ( !empty( $tags ) && is_array( $tags) )
            foreach ( $tags as $tag )
            	$keywords[] = $this->internationalize( $tag->name );
		// Ultimate Tag Warrior integration
        global $utw;
        if ( $utw ) {
        	$tags = $utw->GetTagsForPost( $p );
        	if ( is_array( $tags ) )
            	foreach ( $tags as $tag ) {
					$tag = $tag->tag;
					$tag = str_replace( '_', ' ', $tag );
					$tag = str_replace( '-', ' ', $tag );
					$tag = stripslashes( $tag );
            		$keywords[] = $tag;
            	}
        }
		return $keywords;
	}

	function get_all_terms( $id, $taxonomy ) {
		$keywords = Array();
		$terms = get_the_terms( $id, $taxonomy );
		if ( !empty( $terms ) )
			foreach ( $terms as $term )
				$keywords[] = $this->internationalize( $term->name );
        return $keywords;
	}

	/**
	 * @return comma-separated list of unique keywords
	 */
	function get_all_keywords() {
		global $posts;
		global $aioseop_options;
		if ( is_404() ) return null;
		// if we are on synthetic pages
		if ( !is_home() && !is_page() && !is_single() && !$this->is_static_front_page() && !$this->is_static_posts_page() && !is_archive() && !is_post_type_archive() &&!is_category() && !is_tag() && !is_tax() )
			return null;
	    $keywords = array();
		$opts = $this->meta_opts;
		if ( !empty( $opts["aiosp_keywords"] ) ) {
			$traverse = $this->keyword_string_to_list( $this->internationalize( $opts["aiosp_keywords"] ) );
			if ( !empty( $traverse ) )
				foreach ( $traverse as $keyword ) $keywords[] = $keyword;
		}
		if ( empty( $posts ) ) {
			global $post;
			$post_arr = Array( $post );
		} else {
			$post_arr = $posts;
		}
	    if ( is_array( $post_arr ) ) {
			$postcount = count( $post_arr );
	        foreach ( $post_arr as $p ) {
	            if ( $p ) {
					$id = $p->ID;
					if ( $postcount == 1 || !empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) ) {
		                // custom field keywords
		                $keywords_i = null;
		                $keywords_i = stripslashes( $this->internationalize( get_post_meta( $id, "_aioseop_keywords", true ) ) );
						if ( is_attachment() ) {
							$id = $p->post_parent;
							if ( empty( $keywords_i ) )
								$keywords_i = stripslashes( $this->internationalize( get_post_meta( $id, "_aioseop_keywords", true ) ) );
						}
						$traverse = $this->keyword_string_to_list( $keywords_i );
						if ( !empty( $traverse ) )
							foreach ( $traverse as $keyword ) $keywords[] = $keyword;
					}

					if ( !empty( $aioseop_options['aiosp_use_tags_as_keywords'] ) ) {
						$keywords = array_merge( $keywords, $this->get_all_tags( $id ) );
					}
	                // autometa
					$autometa = stripslashes( get_post_meta( $id, 'autometa', true ) );
	                if ( isset( $autometa ) && !empty( $autometa ) ) {
	                	$autometa_array = explode( ' ', $autometa );
	                	foreach ( $autometa_array as $e )
	                		$keywords[] = $e;
	                }

	            	if ( $aioseop_options['aiosp_use_categories'] && !is_page() ) {
						$keywords = array_merge( $keywords, $this->get_all_categories( $id ) );
	            	}
	            }
	        }
	    }
	    return $this->get_unique_keywords( $keywords );
	}

	function clean_keyword_list( $keywords ) {
		$small_keywords = array();
		if ( !is_array( $keywords ) ) $keywords = $this->keyword_string_to_list( $keywords );
		if ( !empty( $keywords ) )
			foreach ( $keywords as $word ) {
				$small_keywords[] = trim( $this->strtolower( $word ) );
			}
		return array_unique( $small_keywords );
	}

	function get_unique_keywords($keywords) {
		return implode( ',', $this->clean_keyword_list( $keywords ) );
	}

	function log( $message ) {
		if ( $this->do_log ) {
			@error_log( date( 'Y-m-d H:i:s' ) . " " . $message . "\n", 3, $this->log_file );
		}
	}

	function save_post_data( $id ) {
		$awmp_edit = $nonce = null;
		if ( empty( $_POST ) ) return false;
		if ( isset( $_POST[ 'aiosp_edit' ] ) )				$awmp_edit = $_POST['aiosp_edit'];
		if ( isset( $_POST[ 'nonce-aioseop-edit' ] ) )		$nonce     = $_POST['nonce-aioseop-edit'];

	    if ( isset($awmp_edit) && !empty($awmp_edit) && wp_verify_nonce($nonce, 'edit-aioseop-nonce') ) {

			$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'sitemap_exclude', 'disable', 'disable_analytics', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
			if ( !( !empty( $this->options['aiosp_can'] ) ) && ( !empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist["custom_link"] );
			}
		    foreach ( $optlist as $f ) {
				$field = "aiosp_$f";
				if ( isset( $_POST[$field] ) ) $$field = $_POST[$field];
		    }

			$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
			if ( !( !empty( $this->options['aiosp_can'] ) ) && ( !empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist["custom_link"] );
			}
			foreach ( $optlist as $f )
				delete_post_meta( $id, "_aioseop_{$f}" );

		    if ( $this->is_admin() ) {
		    	delete_post_meta($id, '_aioseop_sitemap_exclude' );
		    	delete_post_meta($id, '_aioseop_disable' );
		    	delete_post_meta($id, '_aioseop_disable_analytics' );
			}

			foreach ( $optlist as $f ) {
				$var = "aiosp_$f";
				$field = "_aioseop_$f";
				if ( isset( $$var ) && !empty( $$var ) )
				    add_post_meta( $id, $field, $$var );
		    }
		    if (isset( $aiosp_sitemap_exclude ) && !empty( $aiosp_sitemap_exclude ) && $this->is_admin() )
			    add_post_meta( $id, '_aioseop_sitemap_exclude', $aiosp_sitemap_exclude );
		    if (isset( $aiosp_disable ) && !empty( $aiosp_disable ) && $this->is_admin() ) {
			    add_post_meta( $id, '_aioseop_disable', $aiosp_disable );
			    if (isset( $aiosp_disable_analytics ) && !empty( $aiosp_disable_analytics ) )
				    add_post_meta( $id, '_aioseop_disable_analytics', $aiosp_disable_analytics );
			}
	    }
	}

	function display_tabbed_metabox( $post, $metabox ) {
		$tabs = $metabox['args'];
		echo '<div class="aioseop_tabs">';
		$header = $this->get_metabox_header( $tabs );
		echo $header;
		$active = "";
		foreach( $tabs as $m ) {
			echo '<div id="'.$m['id'].'" class="aioseop_tab"' . $active . '>';
			if ( !$active ) $active = ' style="display:none;"';
			$m['args'] = $m['callback_args'];
			$m['callback'][0]->{$m['callback'][1]}( $post, $m );
			echo '</div>';
		}
		echo '</div>';
	}

	function admin_bar_menu() {
		global $wp_admin_bar, $aioseop_admin_menu, $aioseop_options, $post;

		$toggle = '';
		if ( isset($_POST['aiosp_use_original_title']) && isset($_POST['aiosp_admin_bar']) ) $toggle = 'on';
		if ( isset($_POST['aiosp_use_original_title']) && !isset($_POST['aiosp_admin_bar']) ) $toggle = 'off';

		if ( !empty( $aioseop_options['aiosp_admin_bar'] ) && $toggle != 'off' || isset($_POST['aiosp_admin_bar']) ) {
			$menu_slug = plugin_basename( __FILE__ );

			$url = '';
            if ( function_exists( 'menu_page_url' ) )
                    $url = menu_page_url( $menu_slug, 0 );
            if ( empty( $url ) )
                    $url = esc_url( admin_url( 'admin.php?page=' . $menu_slug ) );

			$wp_admin_bar->add_menu( array( 'id' => AIOSEOP_PLUGIN_DIRNAME, 'title' => __( 'SEO', 'all-in-one-seo-pack' ), 'href' => $url ) );

			if ( current_user_can( 'update_plugins' ) && !AIOSEOPPRO ){
				$wp_admin_bar->add_menu( array( 'parent' => AIOSEOP_PLUGIN_DIRNAME, 'title' => __( 'Upgrade To Pro', 'all-in-one-seo-pack' ), 'id' => 'aioseop-pro-upgrade', 'href' => 'http://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=menu', 'meta' => Array( 'target' => '_blank' ) ) );
				//	add_action( 'admin_bar_menu', array( $this, 'admin_bar_upgrade_menu' ), 1101 );
			}

			$aioseop_admin_menu = 1;
			if ( !is_admin() && !empty( $post ) ) {
				$blog_page = aiosp_common::get_blog_page( $post );
				if ( !empty( $blog_page ) ) $post = $blog_page;
				$wp_admin_bar->add_menu( array( 'id' => 'aiosp_edit_' . $post->ID, 'parent' => AIOSEOP_PLUGIN_DIRNAME, 'title' => __( 'Edit SEO', 'all-in-one-seo-pack' ), 'href' => get_edit_post_link( $post->ID ) . '#aiosp' ) );
			}
		}
	}



	function menu_order() {
		return 5;
	}

	function display_category_metaboxes( $tax ) {
		$screen = 'edit-' . $tax->taxonomy;
	?><div id="poststuff">
	<?php do_meta_boxes( '', 'advanced', $tax ); ?>
	</div>
	<?php
	}

	function save_category_metaboxes( $id ) {
		$awmp_edit = $nonce = null;
		if ( isset( $_POST[ 'aiosp_edit' ] ) )				$awmp_edit = $_POST['aiosp_edit'];
		if ( isset( $_POST[ 'nonce-aioseop-edit' ] ) )		$nonce     = $_POST['nonce-aioseop-edit'];

	    if ( isset($awmp_edit) && !empty($awmp_edit) && wp_verify_nonce($nonce, 'edit-aioseop-nonce') ) {
			$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'sitemap_exclude', 'disable', 'disable_analytics', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
		    foreach ( $optlist as $f ) {
				$field = "aiosp_$f";
				if ( isset( $_POST[$field] ) ) $$field = $_POST[$field];
		    }

			$optlist = Array( 'keywords', 'description', 'title', 'custom_link', 'noindex', 'nofollow', 'noodp', 'noydir', 'titleatr', 'menulabel' );
			if ( !( !empty( $this->options['aiosp_can'] ) ) && ( !empty( $this->options['aiosp_customize_canonical_links'] ) ) ) {
				unset( $optlist["custom_link"] );
			}
			foreach ( $optlist as $f )
				delete_term_meta( $id, "_aioseop_{$f}" );

		    if ( $this->is_admin() ) {
		    	delete_term_meta($id, '_aioseop_sitemap_exclude' );
		    	delete_term_meta($id, '_aioseop_disable' );
		    	delete_term_meta($id, '_aioseop_disable_analytics' );
			}

			foreach ( $optlist as $f ) {
				$var = "aiosp_$f";
				$field = "_aioseop_$f";
				if ( isset( $$var ) && !empty( $$var ) )
				    add_term_meta( $id, $field, $$var );
		    }
		    if (isset( $aiosp_sitemap_exclude ) && !empty( $aiosp_sitemap_exclude ) && $this->is_admin() )
			    add_term_meta( $id, '_aioseop_sitemap_exclude', $aiosp_sitemap_exclude );
		    if (isset( $aiosp_disable ) && !empty( $aiosp_disable ) && $this->is_admin() ) {
			    add_term_meta( $id, '_aioseop_disable', $aiosp_disable );
			    if (isset( $aiosp_disable_analytics ) && !empty( $aiosp_disable_analytics ) )
				    add_term_meta( $id, '_aioseop_disable_analytics', $aiosp_disable_analytics );
			}
	    }
	}

	function admin_menu() {
		$file = plugin_basename( __FILE__ );
		$menu_name = __( 'All in One SEO', 'all-in-one-seo-pack' );

		$this->locations['aiosp']['default_options']['nonce-aioseop-edit']['default'] = wp_create_nonce('edit-aioseop-nonce');

		$custom_menu_order = false;
		global $aioseop_options;
		if ( !isset( $aioseop_options['custom_menu_order'] ) )
			$custom_menu_order = true;

		$this->update_options( );

		$this->add_admin_pointers();
		if ( !empty( $this->pointers ) )
			foreach( $this->pointers as $k => $p )
				if ( !empty( $p["pointer_scope"] ) && ( $p["pointer_scope"] == 'global' ) )
					unset( $this->pointers[$k] );

		$donated = false;
		if ( ( isset( $_POST ) ) && ( isset( $_POST['module'] ) ) && ( isset( $_POST['nonce-aioseop'] ) ) && ( $_POST['module'] == 'All_in_One_SEO_Pack' ) && ( wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) ) ) {
			if ( isset( $_POST["aiosp_donate"] ) )
				$donated = $_POST["aiosp_donate"];
			if ( isset($_POST["Submit"] ) ) {
				if ( isset( $_POST["aiosp_custom_menu_order"] ) )
					$custom_menu_order = $_POST["aiosp_custom_menu_order"];
				else
					$custom_menu_order = false;
			} else if ( ( isset($_POST["Submit_Default"] ) ) || ( ( isset($_POST["Submit_All_Default"] ) ) ) ) {
				$custom_menu_order = true;
			}
		} else {
			if ( isset( $this->options["aiosp_donate"] ) )
				$donated = $this->options["aiosp_donate"];
			if ( isset( $this->options["aiosp_custom_menu_order"] ) )
				$custom_menu_order = $this->options["aiosp_custom_menu_order"];
		}

		if ( $custom_menu_order ) {
			add_filter( 'custom_menu_order', '__return_true' );
			add_filter( 'menu_order', array( $this, 'set_menu_order' ), 11 );
		}

		if ( $donated ) {
			// Thank you for your donation
			$this->pointers['aioseop_donate'] = Array( 'pointer_target' => '#aiosp_donate_wrapper',
														'pointer_text' => '<h3>' . __( 'Thank you!', 'all-in-one-seo-pack' )
														. '</h3><p>' . __( 'Thank you for your donation, it helps keep this plugin free and actively developed!', 'all-in-one-seo-pack' ) . '</p>'
												 );
		}

		if ( !AIOSEOPPRO ){
		if ( !empty( $this->pointers ) )
			foreach( $this->pointers as $k => $p )
				if ( !empty( $p["pointer_scope"] ) && ( $p["pointer_scope"] == 'global' ) )
					unset( $this->pointers[$k] );

		$this->filter_pointers();
		}

		if ( !empty( $this->options['aiosp_enablecpost'] ) && $this->options['aiosp_enablecpost'] ) {
			if ( AIOSEOPPRO ) {
			$this->locations['aiosp']['display'] = $this->options['aiosp_cpostactive'];
			if ( !empty( $this->options['aiosp_taxactive'] ) ) {
				foreach( $this->options['aiosp_taxactive'] as $tax ) {
					$this->locations['aiosp']['display'][] = 'edit-' . $tax;
					add_action( "{$tax}_edit_form", Array( $this, 'display_category_metaboxes' ) );
					add_action( "edited_{$tax}", Array( $this, 'save_category_metaboxes' ) );
				}
			}
			} else {
				if ( !empty( $this->options['aiosp_cpostactive'] ) ) {
					$this->locations['aiosp']['display'] = $this->options['aiosp_cpostactive'];
				} else {
					$this->locations['aiosp']['display'] = Array();
				}
			}
		} else {
			$this->locations['aiosp']['display'] = Array( 'post', 'page' );
		}


			add_menu_page( $menu_name, $menu_name, apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ) , $file, Array( $this, 'display_settings_page' ) );

		add_meta_box('aioseop-list', __( "Join Our Mailing List", 'all-in-one-seo-pack' ), array( 'aiosp_metaboxes', 'display_extra_metaboxes'), 'aioseop_metaboxes', 'normal', 'core');
		if ( AIOSEOPPRO ){
		add_meta_box('aioseop-about', __( "About", 'all-in-one-seo-pack' ), array( 'aiosp_metaboxes', 'display_extra_metaboxes'), 'aioseop_metaboxes', 'side', 'core');
		} else {
			add_meta_box('aioseop-about', "About <span class='Taha' style='float:right;'>Version <b>" . AIOSEOP_VERSION . "</b></span>", array( 'aiosp_metaboxes', 'display_extra_metaboxes'), 'aioseop_metaboxes', 'side', 'core');
		}
		add_meta_box('aioseop-support', __( "Support", 'all-in-one-seo-pack' ) . " <span  class='Taha' style='float:right;'>" . __( "Version", 'all-in-one-seo-pack' ) . " <b>" . AIOSEOP_VERSION . "</b></span>", array( 'aiosp_metaboxes', 'display_extra_metaboxes'), 'aioseop_metaboxes', 'side', 'core');

		add_action( 'aioseop_modules_add_menus', Array( $this, 'add_menu' ), 5 );
		do_action( 'aioseop_modules_add_menus', $file );

		$metaboxes = apply_filters( 'aioseop_add_post_metabox', Array() );

		if ( !empty( $metaboxes ) ) {
			if ( $this->tabbed_metaboxes ) {
				$tabs = Array();
				$tab_num = 0;
				foreach ( $metaboxes as $m ) {
					if ( !isset( $tabs[ $m['post_type'] ] ) ) $tabs[ $m['post_type'] ] = Array();
					$tabs[ $m['post_type'] ][] = $m;
				}

				if ( !empty( $tabs ) ) {
					foreach( $tabs as $p => $m ) {
						$tab_num = count( $m );
						$title = $m[0]['title'];
						if ( $title != $this->plugin_name ) $title = $this->plugin_name . ' - ' . $title;
						if ( $tab_num <= 1 ) {
							if ( !empty( $m[0]['callback_args']['help_link'] ) )
								$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $m[0]['callback_args']['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . "</span></a>";
							add_meta_box( $m[0]['id'], $title, $m[0]['callback'], $m[0]['post_type'], $m[0]['context'], $m[0]['priority'], $m[0]['callback_args'] );
						} elseif ( $tab_num > 1 ) {
							add_meta_box( $m[0]['id'] . '_tabbed', $title, Array( $this, 'display_tabbed_metabox' ), $m[0]['post_type'], $m[0]['context'], $m[0]['priority'], $m );
						}
					}
				}
			} else {
				foreach ( $metaboxes as $m ) {
					$title = $m['title'];
					if ( $title != $this->plugin_name ) $title = $this->plugin_name . ' - ' . $title;
					if ( !empty( $m['help_link'] ) )
						$title .= "<a class='aioseop_help_text_link aioseop_meta_box_help' target='_blank' href='" . $m['help_link'] . "'><span>" . __( 'Help', 'all-in-one-seo-pack' ) . "</span></a>";
					add_meta_box( $m['id'], $title, $m['callback'], $m['post_type'], $m['context'], $m['priority'], $m['callback_args'] );
				}
			}
		}
	}

	function get_metabox_header( $tabs ) {
		$header = '<ul class="aioseop_header_tabs hide">';
		$active = ' active';
		foreach( $tabs as $t ) {
			if ( $active )
				$title = __( 'Main Settings', 'all-in-one-seo-pack' );
			else
				$title = $t['title'];
			$header .= '<li><label class="aioseop_header_nav"><a class="aioseop_header_tab' . $active . '" href="#'. $t['id'] .'">'.$title.'</a></label></li>';
			$active = '';
		}
		$header .= '</ul>';
		return $header;
	}

	function set_menu_order( $menu_order ) {
		$order = array();
		$file = plugin_basename( __FILE__ );
		foreach ( $menu_order as $index => $item ) {
			if ( $item != $file ) $order[] = $item;
			if ( $index == 0 )    $order[] = $file;
		}
		return $order;
	}

	function display_settings_header() {
	}
	function display_settings_footer( ) {
	}

	function display_right_sidebar( ) {
		global $wpdb;

		if( !get_option( 'aioseop_options' ) ) {
			$msg = "<div style='text-align:center;'><p><strong>Your database options need to be updated.</strong><em>(Back up your database before updating.)</em>
				<FORM action='' method='post' name='aioseop-migrate-options'>
					<input type='hidden' name='nonce-aioseop-migrate-options' value='" . wp_create_nonce( 'aioseop-migrate-nonce-options' ) . "' />
					<input type='submit' name='aioseop_migrate_options' class='button-primary' value='Update Database Options'>
		 		</FORM>
			</p></div>";
			aioseop_output_dismissable_notice( $msg, "", "error" );
		}
?>
		<div class="aioseop_top">
			<div class="aioseop_top_sidebar aioseop_options_wrapper">
				<?php do_meta_boxes( 'aioseop_metaboxes', 'normal', Array( 'test' ) ); ?>
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
			do_meta_boxes( 'aioseop_metaboxes', 'side', Array( 'test' ) );
			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					if ( typeof postboxes !== 'undefined' )
						postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
				});
				//]]>
			</script>
		<?php if ( !AIOSEOPPRO ) { ?>
			<div class="aioseop_advert aioseop_nopad_all">
					<?php $adid = mt_rand( 21, 23 );

					if($adid == 23){ ?><div style="height: 220px; background-image: url('https://www.wincher.com/Content/Images/plugin/wp/banner30.jpg')">
						<form  style="position: relative; top: 170px; left: 40px;" action="https://www.wincher.com/FastReg" method="post" target="_blank">
							<input type="hidden" name="adreferer" value="banner<?php echo $adid; ?>"/>
							<input type="hidden" name="referer" value="all-in-one-seo-pack"/>
							<input type="text" name="email" placeholder="Email" style="padding-left: 7px; height: 30px; width: 290px; border: solid 1px #DDD;"/>
							<input type="submit" name="sub"  value="Sign up!" style="height: 30px; width: 90px; background-color: #42DA76; color: #FFF; font-weight: bold; border:none; margin-left:5px;"/>
							</form></div>
							<?
						}else{
							?>
							<a href="https://www.wincher.com/?referer=all-in-one-seo-pack&adreferer=banner<?php echo $adid; ?>" target="_blank"><div class=wincherad id=wincher<?php echo $adid; ?>>
							</div></a>
							<?php } ?>
							</div>
							<!-- Headway Themes-->
								<div class="aioseop_advert headwaythemes">
								<div>
								<h3>Drag and Drop WordPress Design</h3>
								<p><a href="http://semperfiwebdesign.com/headwayaio/" target="_blank">Headway Themes</a> allows you to easily create your own stunning website designs! Stop using premade themes start making your own design with Headway's easy to use Drag and Drop interface. All in One SEO Pack users have an exclusive discount by using coupon code <strong>SEMPERFI30</strong> at checkout.</p>
								</div>
								<a href="http://semperfiwebdesign.com/headwayaio/" target="_blank"><img src="<?php echo AIOSEOP_PLUGIN_IMAGES_URL; ?>headwaybanner.png"></a>
								</div>
								<?php } ?>
								</div>
								</div>
								<?php
							}

}
