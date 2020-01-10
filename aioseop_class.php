<?php
/**
 * All in One SEO Pack Main Class file
 *
 * Main class file, to be broken up later.
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

/**
 * Module Base Class
 */
require_once( AIOSEOP_PLUGIN_DIR . 'admin/aioseop_module_class.php' ); // Include the module base class.
require_once( AIOSEOP_PLUGIN_DIR . 'inc/general/aioseop-robots-meta.php' ); // Include the module base class.

/**
 * Class All_in_One_SEO_Pack
 *
 * The main class.
 */
class All_in_One_SEO_Pack extends All_in_One_SEO_Pack_Module {

	/**
	 * Plugin Version
	 *
	 * Current version of the plugin.
	 *
	 * @since ?
	 *
	 * @var string $version
	 */
	var $version = AIOSEOP_VERSION;

	/**
	 * Max Description Length
	 *
	 * Max numbers of chars in auto-generated description.
	 *
	 * @since ?
	 *
	 * @var int $maximum_description_length
	 */
	var $maximum_description_length = 160;

	/**
	 * Min Description Length
	 *
	 * Minimum number of chars an excerpt should be so that it can be used as description.
	 *
	 * @since ?
	 *
	 * @var int $minimum_description_length
	 */
	var $minimum_description_length = 1;

	/**
	 * OB Start Detected
	 *
	 * Whether output buffering is already being used during forced title rewrites.
	 *
	 * @since ?
	 *
	 * @var bool $ob_start_detected
	 */
	var $ob_start_detected = false;

	/**
	 * Title Start
	 *
	 * The start of the title text in the head section for forced title rewrites.
	 *
	 * @since ?
	 *
	 * @var int $title_start
	 */
	var $title_start = - 1;

	/**
	 * Title End
	 *
	 * The end of the title text in the head section for forced title rewrites.
	 *
	 * @since ?
	 *
	 * @var int $title_end
	 */
	var $title_end = - 1;

	/**
	 * Original Title
	 *
	 * The title before rewriting.
	 *
	 * @since ?
	 *
	 * @var string $orig_title
	 */
	var $orig_title = '';

	/**
	 * Log File
	 *
	 * Filename of log file.
	 *
	 * @since ?
	 *
	 * @var string $log_file
	 */
	var $log_file;

	/**
	 * Do Log
	 *
	 * Flag whether there should be logging.
	 *
	 * @since ?
	 *
	 * @var bool $do_log
	 */
	var $do_log;

	/**
	 * Token
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $token
	 */
	var $token;

	/**
	 * Secret
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $secret
	 */
	var $secret;

	/**
	 * Access Token
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $access_token
	 */
	var $access_token;

	/**
	 * GA Token
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $ga_token
	 */
	var $ga_token;

	/**
	 * Account Cache
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $account_cache
	 */
	var $account_cache;

	/**
	 * Profile ID
	 *
	 * @since ?
	 * @deprecated
	 *
	 * @var null $profile_id
	 */
	var $profile_id;

	/**
	 * Meta Opts
	 *
	 * @since ?
	 *
	 * @var bool $meta_opts
	 */
	var $meta_opts = false;

	/**
	 * Is Front Page
	 *
	 * @since ?
	 *
	 * @var bool|null $is_front_page
	 */
	var $is_front_page = null;

	/**
	 * Constructor
	 *
	 * All_in_One_SEO_Pack constructor.
	 *
	 * @since ?
	 * @since 2.3.14 #921 More google analytics options added.
	 * @since 2.4.0 #1395 Longer Meta Descriptions.
	 * @since 2.6.1 #1694 Back to shorter meta descriptions.
	 */
	function __construct() {
		global $aioseop_options;
		$this->log_file = WP_CONTENT_DIR . '/all-in-one-seo-pack.log'; // PHP <5.3 compatibility, once we drop support we can use __DIR___.

		if ( ! empty( $aioseop_options ) && isset( $aioseop_options['aiosp_do_log'] ) && $aioseop_options['aiosp_do_log'] ) {
			$this->do_log = true;
		} else {
			$this->do_log = false;
		}
		/* translators: This is a header for the General Settings menu. %s is a placeholder and is replaced with the name of the plugin. */
		$this->name = sprintf( __( '%s Plugin Options', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME );
		/* translators: This is the main menu of the plugin. */
		$this->menu_name = __( 'General Settings', 'all-in-one-seo-pack' );

		$this->prefix       = 'aiosp_';                        // Option prefix.
		$this->option_name  = 'aioseop_options';
		$this->store_option = true;
		$this->file         = __FILE__;                                // The current file.
		$blog_name          = esc_attr( get_bloginfo( 'name' ) );
		parent::__construct();

		$this->default_options = array(
			'license_key'                 => array(
				/* translators: This is a setting where users can enter their license code for All in One SEO Pack Pro. */
				'name' => __( 'License Key', 'all-in-one-seo-pack' ),
				'type' => 'text',
			),
			'home_title'                  => array(
				/* translators: This is a setting where users can enter the title for their homepage. */
				'name'     => __( 'Home Title', 'all-in-one-seo-pack' ),
				'default'  => null,
				'type'     => 'text',
				'sanitize' => 'text',
				'count'    => true,
				'rows'     => 1,
				'cols'     => 60,
				'condshow' => array( 'aiosp_use_static_home_info' => 0 ),
			),
			'home_description'            => array(
				/* translators: This is a setting where users can enter the description for their homepage. */
				'name'     => __( 'Home Description', 'all-in-one-seo-pack' ),
				'default'  => '',
				'type'     => 'textarea',
				'sanitize' => 'text',
				'count'    => true,
				'cols'     => 80,
				'rows'     => 2,
				'condshow' => array( 'aiosp_use_static_home_info' => 0 ),
			),
			'togglekeywords'              => array(
				/* translators: This is a setting where users can enable the use of meta keywords for their website. */
				'name'            => __( 'Use Keywords', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'radio',
				'initial_options' => array(
					/* translators: Some settings are either 'Enabled' or 'Disabled'. 'Activated' and 'Deactivated' mean the same. */
					0 => __( 'Enabled', 'all-in-one-seo-pack' ),
					/* translators: Some settings are either 'Enabled' or 'Disabled'. 'Activated' and 'Deactivated' mean the same. */
					1 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'home_keywords'               => array(
				/* translators: This is a setting where users can enter meta keywords for their homepage. */
				'name'     => __( 'Home Keywords (comma separated)', 'all-in-one-seo-pack' ),
				'default'  => null,
				'type'     => 'textarea',
				'sanitize' => 'text',
				'condshow' => array(
					'aiosp_togglekeywords'       => 0,
					'aiosp_use_static_home_info' => 0,
				),
			),
			'use_static_home_info'        => array(
				/* translators: This is a setting where users can indicate that they are using a static page for their homepage. */
				'name'            => __( 'Use Static Front Page Instead', 'all-in-one-seo-pack' ),
				'default'         => 0,
				'type'            => 'radio',
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'can'                         => array(
				/* translators: This is the name of a setting. Canonical URLs help users prevent duplicate content issues - https://en.wikipedia.org/wiki/Canonical_link_element. Leave "Canonical" in English if there is no such term in your language. */
				'name'    => __( 'Canonical URLs', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'no_paged_canonical_links'    => array(
				/* translators: This is the name of a setting. Canonical URLs help users prevent duplicate content issues - https://en.wikipedia.org/wiki/Canonical_link_element. Leave "Canonical" in English if there is no such term in your language. Enabling this setting means the plugin will use the URL of the first page as the canonical URL for all subsequent paginated pages. */
				'name'     => __( 'No Pagination for Canonical URLs', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_can' => 'on' ),
			),
			'force_rewrites'              => array(
				/* translators: This is the name of a setting. Enabling this option forces the plugin to use output buffering to ensure that the title tag will be rewritten. */
				'name'            => __( 'Force Rewrites', 'all-in-one-seo-pack' ),
				'default'         => 1,
				'type'            => 'hidden',
				'prefix'          => $this->prefix,
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'use_original_title'          => array(
				/* translators: This is the name of a setting. Enabling this option forces the plugin to use the wp_title() function to fetch the title tag. */
				'name'            => __( 'Use Original Title', 'all-in-one-seo-pack' ),
				'type'            => 'radio',
				'default'         => 0,
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			'home_page_title_format'      => array(
				/* translators: This is a setting where users can enter the title format for the homepage. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Home Page Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%page_title%',
			),
			'page_title_format'           => array(

				/* translators: This is a setting where users can enter the title format for Pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Page Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%page_title% | %site_title%',
			),
			'post_title_format'           => array(
				/* translators: This is a setting where users can enter the title format for Posts. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Post Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%post_title% | %site_title%',
			),
			'category_title_format'       => array(
				/* translators: This is a setting where users can enter the title format for categories. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Category Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%category_title% | %site_title%',
			),
			'archive_title_format'        => array(
				/*  translators: This is a setting where users can enter the title format for archive pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Archive Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%archive_title% | %site_title%',
			),
			'date_title_format'           => array(
				/*  translators: This is a setting where users can enter the title format for date archive pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Date Archive Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%date% | %site_title%',
			),
			'author_title_format'         => array(
				/* translators: This is a setting where users can enter the title format for author archive pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Author Archive Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%author% | %site_title%',
			),
			'tag_title_format'            => array(
				/* translators: This is a setting where users can enter the title format for tag archive pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Tag Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%tag% | %site_title%',
			),
			'search_title_format'         => array(
				/* translators: This is a setting where users can enter the title format for the search page. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Search Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%search% | %site_title%',
			),
			'description_format'          => array(
				/* translators: This is a setting where users can enter the description format. The description format is the format All in One SEO Pack uses to rewrite the meta description tag. */
				'name'    => __( 'Description Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => '%description%',
			),
			'404_title_format'            => array(
				/* translators: This is a setting where users can enter the title format for the 404 page. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( '404 Title Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => __( 'Nothing found for %request_words%', 'all-in-one-seo-pack' ),
			),
			'paged_format'                => array(
				/* translators: This is a setting where users can enter the title format for paginated pages. The title format is the format All in One SEO Pack uses to rewrite the title tag. */
				'name'    => __( 'Paged Format', 'all-in-one-seo-pack' ),
				'type'    => 'text',
				'default' => sprintf( ' - %s %%page%%', __( 'Part', 'all-in-one-seo-pack' ) ),
			),
			'cpostactive'                 => array(
				/* translators: This is a setting where users can indicate which post types they want to use All in One SEO Pack with. */
				'name'    => __( 'SEO on only these Content Types', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array( 'post', 'page' ),
			),
			'taxactive'                   => array(
				/* translators: This is a setting where users can indicate which taxonomies they want to use All in One SEO Pack with. */
				'name'    => __( 'SEO on only these taxonomies', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array( 'category', 'post_tag' ),
			),
			'cpostnoindex'                => array(
				/* translators: This is a setting where users can indicate which post types they want to NOINDEX by default. NOINDEX is a value of the HTML robots meta tag that asks search engines not to index the page. */
				'name'    => __( 'Default to NOINDEX', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array(),
			),
			'cpostnofollow'               => array(
				/* translators: This is a setting where users can indicate which post types they want to NOFOLLOW by default. NOFOLLOW is a value of the HTML robots meta tag that asks search engines not to follow any links on the page. */
				'name'    => __( 'Default to NOFOLLOW', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array(),
			),
			'posttypecolumns'             => array(
				/* translators: This is a setting where users can indicate for which post types they want to enable columns. Columns are added to the All Posts, All Pages, etc. list pages and allow users to quick-edit their title and description - https://semperplugins.com/documentation/display-settings/#show-column-labels-for-custom-post-types. */
				'name'    => __( 'Show Column Labels for Custom Post Types', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array( 'post', 'page' ),
			),
			'google_verify'               => array(
				'name'    => 'Google Search Console',
				'default' => '',
				'type'    => 'text',
			),
			'bing_verify'                 => array(
				'name'    => 'Bing Webmaster Tools',
				'default' => '',
				'type'    => 'text',
			),
			'pinterest_verify'            => array(
				/* translators: This is a setting where users can add their Pinterest website verification code. */
				'name'    => __( 'Pinterest Site Verification', 'all-in-one-seo-pack' ),
				'default' => '',
				'type'    => 'text',
			),
			'yandex_verify'               => array(
				'name'    => 'Yandex Webmaster Tools',
				'default' => '',
				'type'    => 'text',
			),
			'baidu_verify'                => array(
				'name'    => 'Baidu Webmaster Tools',
				'default' => '',
				'type'    => 'text',
			),
			'google_analytics_id'         => array(
				/* translators: This is a setting where users can add their Google Analytics verification code. Leave this in English if there is no translation for "Google Analytics". */
				'name'        => __( 'Google Analytics ID', 'all-in-one-seo-pack' ),
				'default'     => null,
				'type'        => 'text',
				'placeholder' => 'UA-########-#',
			),
			'ga_advanced_options'         => array(
				/* translators: This is a setting users can enable to display more advanced options for Google Analytics. */
				'name'            => __( 'Advanced Analytics Options', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting which allows users to set the cookie domain for their Google Analytics tracking code. */
				'name'     => __( 'Tracking Domain', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting which allows users to enable Google Analytics tracking for multiple domain names. */
				'name'     => __( 'Track Multiple Domains', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting which allows users to enter additional domain names used for Google Analytics cross-domain tracking - https://support.google.com/analytics/answer/1034342?hl=en.*/
				'name'     => __( 'Additional Domains', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting which tells Google Analytics not to track and store the IP addresses of website visitors.	This is required to be compliant with the GDPR for example. */
				'name'     => __( 'Anonymize IP Addresses', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting that enables a collection of Google Analytics features so you can, for example, create segments based on demographic and interest data. */
				'name'     => __( 'Display Advertiser Tracking', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting that allows you to exclude certain WordPress user roles, e.g. Administrators, from Google Analytics tracking. */
				'name'     => __( 'Exclude Users From Tracking', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting that enables tracking of outbound/external links by Google Analytics. */
				'name'     => __( 'Track Outbound Links', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting for Google Analytics that allows you to tag your pages to implement enhanced link-tracking. */
				'name'     => __( 'Enhanced Link Attribution', 'all-in-one-seo-pack' ),
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
				/* translators: This is a setting which tells Google Analytics to track your customers' path to purchase on your e-commerce website. */
				'name'     => __( 'Enhanced Ecommerce', 'all-in-one-seo-pack' ),
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
			'schema_markup'               => array(
				/* translators: This is a setting that outputs basic Schema.org markup, also known as structured data, into the source code of each page. */
				'name'            => __( 'Use Schema.org Markup', 'all-in-one-seo-pack' ),
				'type'            => 'radio',
				'default'         => 1,
				'initial_options' => array(
					1 => __( 'Enabled', 'all-in-one-seo-pack' ),
					0 => __( 'Disabled', 'all-in-one-seo-pack' ),
				),
			),
			// TODO Change `schema_search_results_page` to `schema_add_search_results_page`. Requires modifying double arrow alignment.
			'schema_search_results_page'  => array(
				/*  translators: This is a setting users can enable to add the basic markup code to their source code that is needed for Google to generate a Sitelinks Search Box - https://developers.google.com/search/docs/data-types/sitelinks-searchbox.*/
				'name'     => __( 'Display Sitelinks Search Box', 'all-in-one-seo-pack' ),
				'condshow' => array(
					'aiosp_schema_markup' => 1,
				),
			),
			'schema_social_profile_links' => array(
				/* translators: This is a setting where users can add links to their social media profiles. These are then output as schema.org markup. */
				'name'     => __( 'Social Profile Links', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'cols'     => 60,
				'rows'     => 5,
				'condshow' => array(
					'aiosp_schema_markup' => 1,
				),
			),
			'schema_site_represents'      => array(
				/* translators: This is a setting where users can indicate whether their website represents a person or organization. This is used for our schema.org markup. */
				'name'            => __( 'Person or Organization', 'all-in-one-seo-pack' ),
				'type'            => 'radio',
				'default'         => 'organization',
				'initial_options' => array(
					'organization' => __( 'Organization', 'all-in-one-seo-pack' ),
					'person'       => __( 'Person', 'all-in-one-seo-pack' ),
				),
				'condshow'        => array(
					'aiosp_schema_markup' => 1,
				),
			),
			'schema_organization_name'    => array(
				/* translators: This is a setting where users can enter the name of their organization. This is used for our schema.org markup. */
				'name'     => __( 'Organization Name', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'default'  => '',
				'condshow' => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'organization',
				),
			),
			'schema_organization_logo'    => array(
				/* translators: This is a setting where users can upload and select a logo for their organization. This is used for our schema.org markup. */
				'name'     => __( 'Organization Logo', 'all-in-one-seo-pack' ),
				'type'     => 'image',
				'condshow' => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'organization',
				),
			),

			'schema_person_user'          => array(
				/* translators: This is a dropdown setting where users can select the username of the person that the website is for. The profile from that user is then used for our schema.org markup.*/
				'name'     => __( 'Person\'s Username', 'all-in-one-seo-pack' ),
				'type'     => 'select',
				'default'  => 1,
				'condshow' => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'person',
				),
				// Add initial options below.
			),
			'schema_person_manual_name'   => array(
				/* translators: Option shown when 'Manually Enter' is selected in Person's Username. Users use this to enter the Person's name for schema Person. */
				'name'     => __( 'Person\'s Name', 'all-in-one-seo-pack' ),
				'type'     => 'text',
				'condshow' => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'person',
					'aiosp_schema_person_user'     => '-1',
				),
			),
			'schema_person_manual_image'  => array(
				/* translators: Option shown when 'Manually Enter' is selected in Person's Username. Users use this to enter the Person's image for schema Person. */
				'name'     => __( 'Person\'s Image', 'all-in-one-seo-pack' ),
				'type'     => 'image',
				'condshow' => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'person',
					'aiosp_schema_person_user'     => '-1',
				),
			),
			'schema_phone_number'         => array(
				/* translators: This is a setting where users can enter a phone number for their organization. This is used for our schema.org markup. */
				'name'         => __( 'Phone Number', 'all-in-one-seo-pack' ),
				'type'         => 'tel',
				'autocomplete' => 'off',
				'condshow'     => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'organization',
				),
			),
			'schema_contact_type'         => array(
				/* translators: This is a setting where users have to indicate what contact/department their phone number connects to (e.g. "Sales" or "Customer Support"). This is used for our schema.org markup. */
				'name'            => __( 'Type of Contact', 'all-in-one-seo-pack' ),
				'type'            => 'select',
				'condshow'        => array(
					'aiosp_schema_markup'          => 1,
					'aiosp_schema_site_represents' => 'organization',
				),
				'initial_options' => array(
					/* translators: This is the placeholder we use in one of our dropdowns when no value has been selected yet. */
					'none'                => __( '-- Select --', 'all-in-one-seo-pack' ),
					'customer support'    => __( 'Customer Support', 'all-in-one-seo-pack' ),
					'tech support'        => __( 'Technical Support', 'all-in-one-seo-pack' ),
					/* translators: This is the support department of a business that handles all billing related enquiries. */
					'billing support'     => __( 'Billing Support', 'all-in-one-seo-pack' ),
					/* translators: This is the department of a business that handles payments of bills. */
					'bill payment'        => __( 'Bill Payment', 'all-in-one-seo-pack' ),
					'sales'               => __( 'Sales', 'all-in-one-seo-pack' ),
					'reservations'        => __( 'Reservations', 'all-in-one-seo-pack' ),
					'credit card support' => __( 'Credit Card Support', 'all-in-one-seo-pack' ),
					'emergency'           => __( 'Emergency', 'all-in-one-seo-pack' ),
					/* translators: This is the department that handles baggage enquiries when e.g. baggage is lost or missing. */
					'baggage tracking'    => __( 'Baggage Tracking', 'all-in-one-seo-pack' ),
					'roadside assistance' => __( 'Roadside Assistance', 'all-in-one-seo-pack' ),
					/* translators: This refers to the department of a package courier that handles enquiries when e.g. a package has not been delivered or is missing.  */
					'package tracking'    => __( 'Package Tracking', 'all-in-one-seo-pack' ),
				),
			),
			'use_categories'              => array(
				/* translators: This is the name of a setting. By enabling it, the plugin will use the categories of the relevant post as meta keywords in addition to any user-specified keywords. */
				'name'     => __( 'Use Categories for META keywords', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'use_tags_as_keywords'        => array(
				/* translators: This is the name of a setting. By enabling it, the plugin will use the tags of the relevant post as meta keywords in addition to any user-specified keywords. */
				'name'     => __( 'Use Tags for META keywords', 'all-in-one-seo-pack' ),
				'default'  => 1,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'dynamic_postspage_keywords'  => array(
				/* translators: This a setting that allows you to dynamically output meta keywords on archive pages based on the keywords from the posts that are displayed by the archive page. */
				'name'     => __( 'Dynamically Generate Keywords for Posts Page/Archives', 'all-in-one-seo-pack' ),
				'default'  => 1,
				'condshow' => array( 'aiosp_togglekeywords' => 0 ),
			),
			'category_noindex'            => array(
				/* translators: This is a global setting that allows you to NOINDEX all your categories. */
				'name'    => __( 'Use noindex for Categories', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'archive_date_noindex'        => array(
				/* translators: This is a global setting that allows you to NOINDEX all your date archive pages. */
				'name'    => __( 'Use noindex for Date Archives', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'archive_author_noindex'      => array(
				/* translators: This is a global setting that allows you to NOINDEX all your author archive pages. */
				'name'    => __( 'Use noindex for Author Archives', 'all-in-one-seo-pack' ),
				'default' => 1,
			),
			'tags_noindex'                => array(
				/* translators: This is a global setting that allows you to NOINDEX all your tag archive pages. */
				'name'    => __( 'Use noindex for Tag Archives', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'search_noindex'              => array(
				/* translators: This is a setting that allows you to NOINDEX your search results page. */
				'name'    => __( 'Use noindex for the Search page', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'404_noindex'                 => array(
				/* translators: This is a setting that allows you to NOINDEX your 404 Not Found page. */
				'name'    => __( 'Use noindex for the 404 page', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'tax_noindex'                 => array(
				/* translators: This is a global setting that allows you to NOINDEX specific taxonomies. */
				'name'    => __( 'Use noindex for Taxonomy Archives', 'all-in-one-seo-pack' ),
				'type'    => 'multicheckbox',
				'default' => array(),
			),
			'paginated_noindex'           => array(
				/* translators: This is a global setting that allows you to NOINDEX all your paginated content (page 2 and higher). */
				'name'    => __( 'Use noindex for paginated pages/posts', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'paginated_nofollow'          => array(
				/* translators: This is a global setting that allows you to NOFOLLOW all your paginated content. */
				'name'    => __( 'Use nofollow for paginated pages/posts', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'generate_descriptions'       => array(
				/* translators: This is a setting that allows the plugin to automatically populate the meta description tag based on the excerpt or content of the post/page.*/
				'name'    => __( 'Autogenerate Descriptions', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'skip_excerpt'                => array(
				/* translators: This is the name of a setting. By enabling it, the plugin will use the content of the post/page to automatically populate the meta description tag, instead of the excerpt. */
				'name'     => __( 'Use Content For Autogenerated Descriptions', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_generate_descriptions' => 'on' ),
			),
			'run_shortcodes'              => array(
				/* translators: This is a setting that enables the plugin to execute shortcodes in the autogenerated descriptions. Shortcodes allow people to execute code inside WordPress posts, pages, and widgets without writing any code directly. */
				'name'     => __( 'Run Shortcodes In Autogenerated Descriptions', 'all-in-one-seo-pack' ),
				'default'  => 0,
				'condshow' => array( 'aiosp_generate_descriptions' => 'on' ),
			),
			'hide_paginated_descriptions' => array(
				/* translators: This is a setting that, if enabled, removes the meta description for paginated content (page 2 and higher). */
				'name'    => __( 'Remove Descriptions For Paginated Pages', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'dont_truncate_descriptions'  => array(
				/* translators: This is a setting that makes sure the plugin does not truncate the meta description tag if it is longer than what All in One SEO Pack recommends. */
				'name'    => __( 'Never Shorten Long Descriptions', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'unprotect_meta'              => array(
				/* translators: This is a setting that allows users to unprotect internal postmeta fields for use with XML-RPC. */
				'name'    => __( 'Unprotect Post Meta Fields', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'redirect_attachement_parent' => array(
				/* translators: This is the name of a setting. By enabling it, the plugin will redirect attachment page requests to the post parent, or in other words, the post/page where the media is embedded. */
				'name'    => __( 'Redirect Attachments to Post Parent', 'all-in-one-seo-pack' ),
				'default' => 0,
			),
			'ex_pages'                    => array(
				/* translators: This is a textarea setting where users can enter a list of pages that All in One SEO Pack should not affect. */
				'name'    => __( 'Exclude Pages', 'all-in-one-seo-pack' ),
				'type'    => 'textarea',
				'default' => '',
			),
			'post_meta_tags'              => array(
				/* translators: This is a setting that allows users to ouput additional code, such as references to stylesheets or JavaScript libraries, into the HEAD section of each post. */
				'name'     => __( 'Additional Post Headers', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'page_meta_tags'              => array(
				/* translators: This is a setting that allows users to ouput additional HTML tags, such as references to stylesheets or JavaScript libraries, into the HEAD section of each page. */
				'name'     => __( 'Additional Page Headers', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'front_meta_tags'             => array(
				/* translators: This is a setting that allows users to ouput additional HTML tags, such as references to stylesheets or JavaScript libraries, into the HEAD section of the frontpage/homepage. */
				'name'     => __( 'Additional Front Page Headers', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'home_meta_tags'              => array(
				/* translators: This is a setting that allows users to ouput additional HTML tags, such as references to stylesheets or JavaScript libraries, into the HEAD section of the static Posts page (see Settings > Reading). */
				'name'     => __( 'Additional Posts Page Headers', 'all-in-one-seo-pack' ),
				'type'     => 'textarea',
				'default'  => '',
				'sanitize' => 'default',
			),
			'do_log'                      => array(
				/* translators: This is a setting that enables All in One SEO Pack to log important events to help with debugging. */
				'name'    => __( 'Log important events', 'all-in-one-seo-pack' ),
				'default' => null,
			),

		);

		if ( ! AIOSEOPPRO ) {
			unset( $this->default_options['license_key'] );
			unset( $this->default_options['taxactive'] );
		}

		$this->locations = array(
			'default' => array(
				'name'    => $this->name,
				'prefix'  => 'aiosp_',
				'type'    => 'settings',
				'options' => null,
			),
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
						'default' => aiosp_common::get_upgrade_hyperlink(
							'meta',
							sprintf(
								'%1$s %2$s Pro',
								/* translators: The complete string is as follows: "Upgrade to All in One SEO Pack Pro". */
								__( 'Upgrade to', 'all-in-one-seo-pack' ),
								AIOSEOP_PLUGIN_NAME
							),
							__( 'UPGRADE TO PRO VERSION', 'all-in-one-seo-pack' ),
							'_blank'
						),
					),
					'support'            => array(
						'type'    => 'html',
						'label'   => 'none',
						'default' => '<a target="_blank" href="https://semperplugins.com/support/">' . __( 'Support Forum', 'all-in-one-seo-pack' ) . '</a>',
					),
					'snippet'            => array(
						/* translators: The preview snippet shows how the page will look like in the search results (title, meta description and permalink). */
						'name'    => __( 'Preview Snippet', 'all-in-one-seo-pack' ),
						'type'    => 'custom',
						'label'   => 'top',
						'default' => '<div class="preview_snippet"><div id="aioseop_snippet"><h3><a>%s</a></h3><div><div><cite id="aioseop_snippet_link">%s</cite></div><span id="aioseop_snippet_description">%s</span></div></div></div>',
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

					'keywords'           => array(
						'name' => __( 'Keywords (comma separated)', 'all-in-one-seo-pack' ),
						'type' => 'text',
					),
					'custom_link'        => array(
						/* translators: This is a setting that users can enable to enter a custom canonical URL. */
						'name' => __( 'Custom Canonical URL', 'all-in-one-seo-pack' ),
						'type' => 'text',
						'size' => 60,
					),
					'noindex'            => array(
						/* translators: This is a setting that allows users to add the NOINDEX robots meta tag value to the current post/page. */
						'name'    => __( 'NOINDEX this page/post', 'all-in-one-seo-pack' ),
						'default' => '',
					),
					'nofollow'           => array(
						/* translators: This is a setting that allows users to add the NOFOLLOW robots meta tag value to the current post/page. */
						'name'    => __( 'NOFOLLOW this page/post', 'all-in-one-seo-pack' ),
						'default' => '',
					),
					/* translators: This is a setting that allows users to exclude the current post/page from the sitemap. */
					'sitemap_exclude'    => array( 'name' => __( 'Exclude From Sitemap', 'all-in-one-seo-pack' ) ),
					/* translators: This is a setting that allows users to disable All in One SEO Pack for the current post/page. */
					'disable'            => array( 'name' => __( 'Disable on this page/post', 'all-in-one-seo-pack' ) ),
					'disable_analytics'  => array(
						/* translators: This is a setting that allows users to disable Google Analytics tracking for the current post/page. */
							'name' => __( 'Disable Google Analytics', 'all-in-one-seo-pack' ),
						'condshow' => array( 'aiosp_disable' => 'on' ),
					),
				),
				// #1067: if SEO is disabled and an empty array is passed below, it will be overridden. So let's pass a post type that cannot possibly exist.
				'display'         => ! empty( $aioseop_options['aiosp_cpostactive'] ) ? array( $aioseop_options['aiosp_cpostactive'] ) : array( '___null___' ),
			),
		);

		$this->layout = array(
			'default'   => array(
				/* translators: This is the name of the main menu. */
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
				/* translators: This is the name of a settings section where users can indicate which post types and taxonomies they want to use All in One SEO Pack with. */
				'name'      => __( 'Content Type Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/custom-post-type-settings/',
				'options'   => array( 'taxactive', 'cpostactive' ),
			),
			'display'   => array(
				/* translators: This is the name of a settings section where users can control how All in One SEO Pack appears in the WordPress Administrator Panel. */
				'name'      => __( 'Display Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/display-settings/',
				'options'   => array( 'posttypecolumns' ),
			),
			'webmaster' => array(
				/* translators: This is the name of a settings section where users can add verification codes of webmaster platforms such as Google Search Console, Bing Webmaster Tools, etc. */
				'name'      => __( 'Webmaster Verification', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/sections/webmaster-verification/',
				'options'   => array( 'google_verify', 'bing_verify', 'pinterest_verify', 'yandex_verify', 'baidu_verify' ),
			),
			'google'    => array(
				'name'      => __( 'Google Analytics', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/advanced-google-analytics-settings/',
				'options'   => array(
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
			'schema'    => array(
				'name'      => __( 'Schema Settings', 'all-in-one-seo-pack' ),
				'help_link' => 'https://semperplugins.com/documentation/schema-settings/',
				'options'   => array(
					'schema_markup',
					'schema_search_results_page',
					'schema_social_profile_links',
					'schema_site_represents',
					'schema_organization_name',
					'schema_organization_logo',
					'schema_person_user',
					'schema_person_manual_name',
					'schema_person_manual_image',
					'schema_phone_number',
					'schema_contact_type',
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

		global $pagenow;
		if ( 'admin.php' === $pagenow ) {
			// Person's Username setting.
			$this->default_options['schema_person_user']['initial_options'] = array(
				0  => __( '- Select -', 'all-in-one-seo-pack' ),
				-1 => __( 'Manually Enter', 'all-in-one-seo-pack' ),
			);

			global $wpdb;
			$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->users" );
			if ( 50 < $user_count ) {
				$this->default_options['schema_person_user']['initial_options'] = array(
					-1 => __( 'Manually Enter', 'all-in-one-seo-pack' ),
				);
			} else {
				$user_args = array(
					'role__in' => array(
						'administrator',
						'editor',
						'author',
					),
					'orderby'  => 'nicename',
				);
				$users     = get_users( $user_args );

				foreach ( $users as $user ) {
					$this->default_options['schema_person_user']['initial_options'][ $user->ID ] = $user->data->user_nicename . ' (' . $user->data->display_name . ')';
				}
			}
		}

		if ( AIOSEOPPRO ) {
			// Add Pro options.
			$this->default_options = aioseop_add_pro_opt( $this->default_options );
			$this->layout          = aioseop_add_pro_layout( $this->layout );
		}

		if ( ! AIOSEOPPRO ) {
			unset( $this->layout['cpt']['options']['0'] );
		}

		$other_options = array();
		foreach ( $this->layout as $k => $v ) {
			$other_options = array_merge( $other_options, $v['options'] );
		}

		$this->layout['default']['options'] = array_diff( array_keys( $this->default_options ), $other_options );

		if ( is_admin() ) {
			add_action( 'aioseop_global_settings_header', array( $this, 'display_right_sidebar' ) );
			add_action( 'aioseop_global_settings_footer', array( $this, 'display_settings_footer' ) );
			add_action( 'output_option', array( $this, 'custom_output_option' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'visibility_warning' ) );
			add_action( 'admin_init', array( $this, 'woo_upgrade_notice' ) );

		}
		if ( AIOSEOPPRO ) {
			add_action( 'split_shared_term', array( $this, 'split_shared_term' ), 10, 4 );
		}
	}

	// good candidate for pro dir.
	/**
	 * Custom Output Option
	 *
	 * Use custom callback for outputting snippet
	 *
	 * @since ?
	 * @since 2.3.16 Decodes HTML entities on title, description and title length count.
	 *
	 * @param $buf
	 * @param $args
	 * @return string
	 */
	function custom_output_option( $buf, $args ) {
		if ( 'aiosp_snippet' === $args['name'] ) {
			$args['options']['type']   = 'html';
			$args['options']['nowrap'] = false;
			$args['options']['save']   = false;
			$info                      = $this->get_page_snippet_info();
		} else {
			return '';
		}

		$args['options']['type']   = 'html';
		$args['options']['nowrap'] = false;
		$args['options']['save']   = false;
		$info                      = $this->get_page_snippet_info();
		$title                     = $info['title'];
		$description               = $info['description'];
		$keywords                  = $info['keywords'];
		$url                       = $info['url'];
		$title_format              = $info['title_format'];
		$category                  = $info['category'];
		$w                         = $info['w'];
		$p                         = $info['p'];

		if ( $this->strlen( $title ) > 70 ) {
			$title = $this->trim_excerpt_without_filters(
				$this->html_entity_decode( $title ),
				70
			) . '...';
		}
		if ( $this->strlen( $description ) > 156 ) {
			$description = $this->trim_excerpt_without_filters(
				$this->html_entity_decode( $description ),
				156
			) . '...';
		}
		if ( empty( $title_format ) ) {
			$title = '<span id="' . $args['name'] . '_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) ) ) . '</span>';
		} else {
			$title_format = $this->get_preview_snippet_title();
			$title        = $title_format;
		}

		$args['value'] = sprintf( $args['value'], $title, esc_url( $url ), esc_attr( $description ) );
		$buf           = $this->get_option_row( $args['name'], $args['options'], $args );

		return $buf;
	}

	/**
	 * The get_preview_snippet_title() function.
	 *
	 * Processes the title format for the snippet preview on the Edit screen.
	 *
	 * @since 2.4.9
	 * @since 3.2.0 Fix #1408 & #2526.
	 *
	 * @return mixed
	 */
	public function get_preview_snippet_title() {
		$info         = $this->get_page_snippet_info();
		$title        = $info['title'];
		$description  = $info['description'];
		$keywords     = $info['keywords'];
		$url          = $info['url'];
		$title_format = $info['title_format'];
		$category     = $info['category'];
		$wp_query     = $info['w'];
		$post         = $info['p'];

		// Posts page title doesn't need to be processed because get_aioseop_title() does this.
		if ( is_home() ) {
			return $this->get_preview_snippet_title_helper( $title );
		}

		/**
		 * The aioseop_before_get_title_format action hook.
		 *
		 * Runs before we process the title format for the snippet preview is.
		 *
		 * @since 3.0.0
		 */
		do_action( 'aioseop_before_get_title_format' );

		if ( false !== strpos( $title_format, '%site_title%', 0 ) ) {
			$title_format = str_replace( '%site_title%', get_bloginfo( 'name' ), $title_format );
		}
		// %blog_title% macro is deprecated.
		if ( false !== strpos( $title_format, '%blog_title%', 0 ) ) {
			$title_format = str_replace( '%blog_title%', get_bloginfo( 'name' ), $title_format );
		}
		$title_format = $this->apply_cf_fields( $title_format );
		if ( false !== strpos( $title_format, '%post_title%', 0 ) ) {
			$title_format = str_replace( '%post_title%', $this->get_preview_snippet_title_helper( $title ), $title_format );
		}
		if ( false !== strpos( $title_format, '%page_title%', 0 ) ) {
			$title_format = str_replace( '%page_title%', $this->get_preview_snippet_title_helper( $title ), $title_format );
		}
		if ( false !== strpos( $title_format, '%current_date%', 0 ) ) {
			$title_format = str_replace( '%current_date%', aioseop_formatted_date(), $title_format );
		}
		if ( false !== strpos( $title_format, '%current_year%', 0 ) ) {
			$title_format = str_replace( '%current_year%', date( 'Y' ), $title_format );
		}
		if ( false !== strpos( $title_format, '%current_month%', 0 ) ) {
			$title_format = str_replace( '%current_month%', date( 'M' ), $title_format );
		}
		if ( false !== strpos( $title_format, '%current_month_i18n%', 0 ) ) {
			$title_format = str_replace( '%current_month_i18n%', date_i18n( 'M' ), $title_format );
		}
		if ( false !== strpos( $title_format, '%post_date%', 0 ) ) {
			$title_format = str_replace( '%post_date%', aioseop_formatted_date( get_the_time( 'U' ) ), $title_format );
		}
		if ( false !== strpos( $title_format, '%post_year%', 0 ) ) {
			$title_format = str_replace( '%post_year%', get_the_date( 'Y' ), $title_format );
		}
		if ( false !== strpos( $title_format, '%post_month%', 0 ) ) {
			$title_format = str_replace( '%post_month%', get_the_date( 'F' ), $title_format );
		}
		if ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ) {
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
			if ( false !== strpos( $title_format, '%category_title%', 0 ) ) {
				$title_format = str_replace( '%category_title%', $title, $title_format );
			}
			if ( false !== strpos( $title_format, '%taxonomy_title%', 0 ) ) {
				$title_format = str_replace( '%taxonomy_title%', $title, $title_format );
			}
		} else {
			if ( false !== strpos( $title_format, '%category%', 0 ) ) {
				$title_format = str_replace( '%category%', $category, $title_format );
			}
			if ( false !== strpos( $title_format, '%category_title%', 0 ) ) {
				$title_format = str_replace( '%category_title%', $category, $title_format );
			}
			if ( false !== strpos( $title_format, '%taxonomy_title%', 0 ) ) {
				$title_format = str_replace( '%taxonomy_title%', $category, $title_format );
			}
			if ( AIOSEOPPRO ) {
				if ( strpos( $title_format, '%tax_', 0 ) && ! empty( $post ) ) {
					$taxes = get_object_taxonomies( $post, 'objects' );
					if ( ! empty( $taxes ) ) {
						foreach ( $taxes as $t ) {
							if ( strpos( $title_format, "%tax_{$t->name}%", 0 ) ) {
								$terms = $this->get_all_terms( $post->ID, $t->name );
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
		if ( false !== strpos( $title_format, '%taxonomy_description%', 0 ) ) {
			$title_format = str_replace( '%taxonomy_description%', $description, $title_format );
		}

		/**
		 * The aioseop_title_format filter hook.
		 *
		 * Filter the title for the preview snippet after replacing all macros.
		 *
		 * @since 3.0.0
		 *
		 * @param string $title_format Title format to be filtered.
		 */
		$title_format = apply_filters( 'aioseop_title_format', $title_format );

		/**
		 * The aioseop_after_format_title action hook.
		 *
		 * Runs after we have processed the title format for the snippet preview is.
		 *
		 * @since 3.0.0
		 */
		do_action( 'aioseop_after_format_title' );

		return $title_format;
	}

	/**
	 * The get_preview_snippet_title_helper() function.
	 *
	 * Wraps the page or post title for the preview snippet title in its HTML span element.
	 * Helper function for the get_preview_snippet_title() function.
	 *
	 * @since 3.2.0
	 *
	 * @param string $title_format
	 * @return string
	 */
	private function get_preview_snippet_title_helper( $title_format ) {
		return '<span id="aiosp_snippet_title">' . esc_attr( wp_strip_all_tags( html_entity_decode( $title_format, ENT_COMPAT, 'UTF-8' ) ) ) . '</span>';
	}

	/**
	 * The get_page_snippet_info() function.
	 *
	 * Gets data that is needed to determine the preview snippet.
	 *
	 * @since ?
	 *
	 * @return array
	 */
	function get_page_snippet_info() {
		static $info = array();
		if ( ! empty( $info ) ) {
			return $info;
		}
		global $post, $aioseop_options, $wp_query;
		$title       = '';
		$url         = '';
		$description = '';
		$term        = '';
		$category    = '';
		$p           = $post;
		$w           = $wp_query;
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
				$wp_query = new WP_Query(
					array(
						'p'         => $post_id,
						'post_type' => $post->post_type,
					)
				);
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
				if ( is_page() && get_option( 'page_on_front' ) == $post->ID ) {
					$this->is_front_page = true;
				} elseif ( get_option( 'page_for_posts' ) == $post->ID ) {
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

			// All this needs to be in it's own function (class really).
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
		} elseif ( is_object( $term ) ) {
			if ( 'category' === $_GET['taxonomy'] ) {
				query_posts( array( 'cat' => $_GET['tag_ID'] ) );
			} elseif ( 'post_tag' === $_GET['taxonomy'] ) {
				query_posts( array( 'tag' => $term->slug ) );
			} else {
				query_posts(
					array(
						'page'            => '',
						$_GET['taxonomy'] => $term->slug,
						'post_type'       => $_GET['post_type'],
					)
				);
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
		if ( true == $this->is_front_page ) {
			// $title_format = $aioseop_options['aiosp_home_page_title_format'];
			$title_format = ''; // Not sure why this needs to be this way, but we should extract all this out to figure out what's going on.
		}
		$show_page = true;
		if ( ! empty( $aioseop_options['aiosp_no_paged_canonical_links'] ) ) {
			$show_page = false;
		}
		if ( $aioseop_options['aiosp_can'] ) {
			if ( ! empty( $opts['aiosp_custom_link'] ) ) {
				$url = $opts['aiosp_custom_link'];
			}
			if ( empty( $url ) ) {
				$url = $this->aiosp_mrt_get_url( $wp_query, $show_page );
			}
			$url = apply_filters( 'aioseop_canonical_url', $url );
		}
		if ( ! $url ) {
			$url = aioseop_get_permalink();
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
	 * Get Queried Object
	 *
	 * @since ?
	 *
	 * @return null|object|WP_Post
	 */
	function get_queried_object() {
		static $p = null;
		global $wp_query, $post;
		if ( null !== $p && ! defined( 'AIOSEOP_UNIT_TESTING' ) ) {
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
	 * Get Current Option
	 *
	 * @since ?
	 *
	 * @param array $opts
	 * @param null $location
	 * @param null $defaults
	 * @param null $post
	 * @return array
	 */
	function get_current_options( $opts = array(), $location = null, $defaults = null, $post = null ) {
		if ( ( 'aiosp' === $location ) && ( 'metabox' == $this->locations[ $location ]['type'] ) ) {
			if ( null === $post ) {
				global $post;
			}

			// TODO Fetch correct ID for static posts page/Woocommerce shop page - #2729.
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
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) ) {
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
						$get_opts[ $field ] = htmlspecialchars( $meta, ENT_COMPAT, 'UTF-8' );
					} else {
						$get_opts[ $field ] = htmlspecialchars( stripslashes( $meta ), ENT_COMPAT, 'UTF-8' );
					}
				} else {
					if ( ! is_category() && ! is_tag() && ! is_tax() ) {
						$field = "aiosp_$f";
						$meta  = get_post_meta( $post_id, '_aioseop_' . $f, true );
						if ( 'title' === $f || 'description' === $f ) {
							$get_opts[ $field ] = htmlspecialchars( $meta, ENT_COMPAT, 'UTF-8' );
						} else {
							$get_opts[ $field ] = htmlspecialchars( stripslashes( $meta ), ENT_COMPAT, 'UTF-8' );
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
	 * Internationalize
	 *
	 * @since ?
	 *
	 * @param $in
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

	/**
	 * WP Title
	 *
	 * Used to filter wp_title(), get our title.
	 *
	 * @since ?
	 *
	 * @return mixed|void
	 */
	function wp_title() {
		if ( ! $this->is_seo_enabled_for_cpt() ) {
			return;
		}

		global $aioseop_options;
		$title = false;
		$post  = $this->get_queried_object();
		$title = $this->get_aioseop_title( $post );
		$title = $this->apply_cf_fields( $title );

		if ( false === $title ) {
			$title = $this->get_original_title();
		}

		return apply_filters( 'aioseop_title', $title );
	}

	/**
	 * Get AIOSEOP Title
	 *
	 * Gets the title that will be used by AIOSEOP for title rewrites or returns false.
	 *
	 * @param WP_Post $post the post object
	 * @param bool $use_original_title_format should the original title format be used viz. post_title | blog_title. This parameter was introduced
	 * to resolve issue#986
	 * @return bool|string
	 */
	function get_aioseop_title( $post, $use_original_title_format = true ) {
		global $aioseop_options;
		// the_search_query() is not suitable, it cannot just return.
		global $s, $STagging; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$opts = $this->meta_opts;
		if ( is_front_page() ) {
			if ( ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				global $post;
				if ( 'page' == get_option( 'show_on_front' ) && is_page() && get_option( 'page_on_front' ) == $post->ID ) {
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

			// #1616 - Avoid trying to get property of non-object when no posts are present on the homepage.
			global $post;

			if ( null === $post ) {
				$post_id = get_option( 'page_on_front' );
			} else {
				$post_id = $post->ID;
			}

			if ( is_post_type_archive() && is_post_type_archive( 'product' ) && function_exists( 'wc_get_page_id' ) ) {
				$post_id = wc_get_page_id( 'shop' );
				if ( $post_id ) {
					$post = get_post( $post_id );
					if ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
						$title = $this->internationalize( get_post_meta( $post->ID, '_aioseop_title', true ) );
					}
					// $title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
					if ( ! $title ) {
						$title = $this->internationalize( get_post_meta( $post_id, '_aioseop_title', true ) );
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
			}

			// this is returned for woo.
			return $this->paged_title( $title );
		} elseif ( is_attachment() ) {
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
		} elseif ( is_page() || $this->is_static_posts_page() || ( is_home() && ! $this->is_static_posts_page() ) ) {
			if ( null === $post ) {
				return false;
			}

			$home_title = $this->internationalize( $aioseop_options['aiosp_home_title'] );
			if ( $this->is_static_front_page() && ( $home_title ) ) {
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
		} elseif ( is_post_type_archive( 'product' ) && function_exists( 'wc_get_page_id' ) ) {
			$post_id = wc_get_page_id( 'shop' );
			if ( $post_id ) {
				$post = get_post( $post_id );
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
			}
		} elseif ( is_single() || $this->check_singular() ) {
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
				if ( ! $title && $use_original_title_format ) {
					$title = $this->internationalize( $this->get_original_title( '', false ) );
				}
			}
			if ( empty( $title ) ) {
				$title = $post->post_title;
			}
			if ( ! empty( $title ) && $use_original_title_format ) {
				$title = $this->apply_post_title_format( $title, $category, $post );
			}
			$title = $this->paged_title( $title );

			return apply_filters( 'aioseop_title_single', $title );
		} elseif ( is_search() && isset( $s ) && ! empty( $s ) ) {
			$search       = esc_attr( stripslashes( $s ) );
			$title_format = $aioseop_options['aiosp_search_title_format'];
			$title        = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( false !== strpos( $title, '%blog_title%', 0 ) ) {
				$title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
			}
			if ( false !== strpos( $title, '%site_description%', 0 ) ) {
				$title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
			}
			if ( false !== strpos( $title, '%blog_description%', 0 ) ) {
				$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
			}
			if ( false !== strpos( $title, '%search%', 0 ) ) {
				$title = str_replace( '%search%', $search, $title );
			}
			$title = $this->paged_title( $title );

			return $title;
		} elseif ( is_tag() ) {
			global $utw;
			$tag             = '';
			$tag_description = '';
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
				$title        = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
				if ( false !== strpos( $title, '%blog_title%', 0 ) ) {
					$title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
				}
				if ( false !== strpos( $title, '%site_description%', 0 ) ) {
					$title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( false !== strpos( $title, '%blog_description%', 0 ) ) {
					$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( false !== strpos( $title, '%tag%', 0 ) ) {
					$title = str_replace( '%tag%', $tag, $title );
				}
				if ( false !== strpos( $title, '%tag_description%', 0 ) ) {
					$title = str_replace( '%tag_description%', $tag_description, $title );
				}
				if ( false !== strpos( $title, '%taxonomy_description%', 0 ) ) {
					$title = str_replace( '%taxonomy_description%', $tag_description, $title );
				}
				$title = trim( wp_strip_all_tags( $title ) );
				$title = str_replace( array( '"', "\r\n", "\n" ), array( '&quot;', ' ', ' ' ), $title );
				$title = $this->paged_title( $title );

				return $title;
			}
		} elseif ( ( is_tax() || is_category() ) && ! is_feed() ) {
			return $this->get_tax_title();

		} elseif ( isset( $STagging ) && $STagging->is_tag_view() ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			// Simple tagging support.
			$tag = $STagging->search_tag; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			if ( $tag ) {
				$title_format = $aioseop_options['aiosp_tag_title_format'];
				$title        = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
				if ( false !== strpos( $title, '%blog_title%', 0 ) ) {
					$title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
				}
				if ( false !== strpos( $title, '%site_description%', 0 ) ) {
					$title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( false !== strpos( $title, '%blog_description%', 0 ) ) {
					$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
				}
				if ( false !== strpos( $title, '%tag%', 0 ) ) {
					$title = str_replace( '%tag%', $tag, $title );
				}
				$title = $this->paged_title( $title );

				return $title;
			}
		} elseif ( is_archive() || is_post_type_archive() ) {
			if ( is_author() ) {
				$author       = $this->internationalize( $this->get_original_title( '', false ) );
				$title_format = $aioseop_options['aiosp_author_title_format'];
				$new_title    = str_replace( '%author%', $author, $title_format );
			} elseif ( is_date() ) {
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
				if ( false !== strpos( $new_title, '%month%', 0 ) ) {
					$new_title = str_replace( '%month%', $month, $new_title );
				}
				if ( false !== strpos( $new_title, '%year%', 0 ) ) {
					$new_title = str_replace( '%year%', get_query_var( 'year' ), $new_title );
				}
			} elseif ( is_post_type_archive() ) {
				if ( empty( $title ) ) {
					$title = $this->get_original_title( '', false );
				}
				$new_title = apply_filters( 'aioseop_archive_title', $this->apply_archive_title_format( $title ) );
			} else {
				return false;
			}
			$new_title = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
			if ( false !== strpos( $new_title, '%blog_title%', 0 ) ) {
				$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%site_description%', 0 ) ) {
				$new_title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%blog_description%', 0 ) ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			$title = trim( $new_title );
			$title = $this->paged_title( $title );

			return $title;
		} elseif ( is_404() ) {
			$title_format = $aioseop_options['aiosp_404_title_format'];
			$new_title    = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( false !== strpos( $new_title, '%blog_title%', 0 ) ) {
				$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%site_description%', 0 ) ) {
				$new_title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%blog_description%', 0 ) ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%request_url%', 0 ) ) {
				$new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title );
			}
			if ( false !== strpos( $new_title, '%request_words%', 0 ) ) {
				$new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			}
			if ( false !== strpos( $new_title, '%404_title%', 0 ) ) {
				$new_title = str_replace( '%404_title%', $this->internationalize( $this->get_original_title( '', false ) ), $new_title );
			}

			return $new_title;
		}

		return false;
	}

	/**
	 * Get Original Title
	 *
	 * @since ?
	 *
	 * @param string $sep
	 * @param bool $echo
	 * @param string $seplocation
	 * @return string The original title as delivered by WP (well, in most cases).
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
			if ( false !== $has_filter ) {
				add_filter( 'wp_title', array( $this, 'wp_title' ), $has_filter );
			}
			$title = trim( $title );
			if ( $title ) {
				return trim( $title );
			}
		}

		// the_search_query() is not suitable, it cannot just return.
		global $s;

		$title = null;

		if ( is_home() ) {
			$title = get_option( 'blogname' );
		} elseif ( is_single() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} elseif ( is_search() && isset( $s ) && ! empty( $s ) ) {
			$search = esc_attr( stripslashes( $s ) );
			$title  = $search;
		} elseif ( ( is_tax() || is_category() ) && ! is_feed() ) {
			$category_name = $this->ucwords( $this->internationalize( single_cat_title( '', false ) ) );
			$title         = $category_name;
		} elseif ( is_page() ) {
			$title = $this->internationalize( single_post_title( '', false ) );
		} elseif ( is_tag() ) {
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
		} elseif ( is_author() ) {
			$author = get_userdata( get_query_var( 'author' ) );
			if ( false === $author ) {
				global $wp_query;
				$author = $wp_query->get_queried_object();
			}
			if ( false !== $author ) {
				$title = $author->display_name;
			}
		} elseif ( is_day() ) {
			$title = get_the_date();
		} elseif ( is_month() ) {
			$title = get_the_date( 'F, Y' );
		} elseif ( is_year() ) {
			$title = get_the_date( 'Y' );
		} elseif ( is_archive() ) {
			$title = $this->internationalize( post_type_archive_title( '', false ) );
		} elseif ( is_404() ) {
			$title_format = $aioseop_options['aiosp_404_title_format'];
			$new_title    = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
			if ( false !== strpos( $new_title, '%blog_title%', 0 ) ) {
				$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%site_description%', 0 ) ) {
				$new_title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%blog_description%', 0 ) ) {
				$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
			}
			if ( false !== strpos( $new_title, '%request_url%', 0 ) ) {
				$new_title = str_replace( '%request_url%', $_SERVER['REQUEST_URI'], $new_title );
			}
			if ( false !== strpos( $new_title, '%request_words%', 0 ) ) {
				$new_title = str_replace( '%request_words%', $this->request_as_words( $_SERVER['REQUEST_URI'] ), $new_title );
			}
			$title = $new_title;
		}

		return trim( $title );
	}

	/**
	 * Request as Words
	 *
	 * @since ?
	 *
	 * @param $request
	 * @return string User -readable nice words for a given request.
	 */
	function request_as_words( $request ) {
		$request     = htmlspecialchars( $request, ENT_COMPAT, 'UTF-8' );
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
	 * Apply Page Title Format
	 *
	 * @since ?
	 *
	 * @param $title
	 * @param null $p
	 * @param string $title_format
	 * @return string
	 */
	function apply_page_title_format( $title, $p = null, $title_format = '' ) {
		global $aioseop_options;
		if ( null === $p ) {
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
	 * Title Placeholder Helper
	 *
	 * Replace doc title templates inside % symbol on the frontend.
	 *
	 * @since ?
	 *
	 * @param $title
	 * @param $post
	 * @param string $type
	 * @param string $title_format
	 * @param string $category
	 * @return string
	 */
	function title_placeholder_helper( $title, $post, $type = 'post', $title_format = '', $category = '' ) {

		/**
		 * Runs before applying the formatting for the doc title on the frontend.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_before_title_placeholder_helper' );

		if ( ! empty( $post ) ) {
			$authordata = get_userdata( $post->post_author );
		} else {
			$authordata = new WP_User();
		}
		$new_title = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title_format );
		if ( false !== strpos( $new_title, '%blog_title%', 0 ) ) {
			$new_title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $new_title );
		}
		if ( false !== strpos( $new_title, '%site_description%', 0 ) ) {
			$new_title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
		}
		if ( false !== strpos( $new_title, '%blog_description%', 0 ) ) {
			$new_title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $new_title );
		}
		if ( false !== strpos( $new_title, "%{$type}_title%", 0 ) ) {
			$new_title = str_replace( "%{$type}_title%", $title, $new_title );
		}
		if ( 'post' == $type ) {
			if ( false !== strpos( $new_title, '%category%', 0 ) ) {
				$new_title = str_replace( '%category%', $category, $new_title );
			}
			if ( false !== strpos( $new_title, '%category_title%', 0 ) ) {
				$new_title = str_replace( '%category_title%', $category, $new_title );
			}
			if ( false !== strpos( $new_title, '%tax_', 0 ) && ! empty( $post ) ) {
				$taxes = get_object_taxonomies( $post, 'objects' );
				if ( ! empty( $taxes ) ) {
					foreach ( $taxes as $t ) {
						if ( false !== strpos( $new_title, "%tax_{$t->name}%", 0 ) ) {
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
		if ( false !== strpos( $new_title, "%{$type}_author_login%", 0 ) ) {
			$new_title = str_replace( "%{$type}_author_login%", $authordata->user_login, $new_title );
		}
		if ( false !== strpos( $new_title, "%{$type}_author_nicename%", 0 ) ) {
			$new_title = str_replace( "%{$type}_author_nicename%", $authordata->user_nicename, $new_title );
		}
		if ( false !== strpos( $new_title, "%{$type}_author_firstname%", 0 ) ) {
			$new_title = str_replace( "%{$type}_author_firstname%", $this->ucwords( $authordata->first_name ), $new_title );
		}
		if ( false !== strpos( $new_title, "%{$type}_author_lastname%", 0 ) ) {
			$new_title = str_replace( "%{$type}_author_lastname%", $this->ucwords( $authordata->last_name ), $new_title );
		}
		if ( false !== strpos( $new_title, '%current_date%', 0 ) ) {
			$new_title = str_replace( '%current_date%', aioseop_formatted_date(), $new_title );
		}
		if ( false !== strpos( $new_title, '%current_year%', 0 ) ) {
			$new_title = str_replace( '%current_year%', date( 'Y' ), $new_title );
		}
		if ( false !== strpos( $new_title, '%current_month%', 0 ) ) {
			$new_title = str_replace( '%current_month%', date( 'M' ), $new_title );
		}
		if ( false !== strpos( $new_title, '%current_month_i18n%', 0 ) ) {
			$new_title = str_replace( '%current_month_i18n%', date_i18n( 'M' ), $new_title );
		}
		if ( false !== strpos( $new_title, '%post_date%', 0 ) ) {
			$new_title = str_replace( '%post_date%', aioseop_formatted_date( get_the_date( 'U' ) ), $new_title );
		}
		if ( false !== strpos( $new_title, '%post_year%', 0 ) ) {
			$new_title = str_replace( '%post_year%', get_the_date( 'Y' ), $new_title );
		}
		if ( false !== strpos( $new_title, '%post_month%', 0 ) ) {
			$new_title = str_replace( '%post_month%', get_the_date( 'F' ), $new_title );
		}

		/**
		 * Filters document title after applying the formatting.
		 *
		 * @since 3.0
		 *
		 * @param string $new_title Document title to be filtered.
		 */
		$new_title = apply_filters( 'aioseop_title_format', $new_title );

		/**
		 * Runs after applying the formatting for the doc title on the frontend.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_after_title_placeholder_helper' );

		$title = trim( $new_title );

		return $title;
	}

	/**
	 * Get All Terms
	 *
	 * @since ?
	 *
	 * @param $id
	 * @param $taxonomy
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
	 * Paged Title
	 *
	 * @since ?
	 *
	 * @param $title
	 * @return string
	 */
	function paged_title( $title ) {
		// The page number if paged.
		global $paged;
		global $aioseop_options;
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
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
		// phpcs:enable

		return $title;
	}

	/**
	 * Log
	 *
	 * @since ?
	 *
	 * @param $message
	 */
	function log( $message ) {
		if ( $this->do_log ) {
			// @codingStandardsIgnoreStart
			@error_log( date( 'Y-m-d H:i:s' ) . ' ' . $message . "\n", 3, $this->log_file );
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Apply Post Title Format
	 *
	 * @since ?
	 *
	 * @param $title
	 * @param string $category
	 * @param null $p
	 * @return string
	 */
	function apply_post_title_format( $title, $category = '', $p = null ) {
		if ( null === $p ) {
			global $post;
		} else {
			$post = $p;
		}
		$title_format = $this->get_post_title_format( 'post', $post );

		return $this->title_placeholder_helper( $title, $post, 'post', $title_format, $category );
	}

	/**
	 * Get Post Title Format
	 *
	 * @since ?
	 *
	 * @param string $title_type
	 * @param null $p
	 * @return bool|string
	 */
	function get_post_title_format( $title_type = 'post', $p = null ) {
		global $aioseop_options;
		if ( ( 'post' != $title_type ) && ( 'archive' != $title_type ) ) {
			return false;
		}
		$title_format = "%{$title_type}_title% | %site_title%";
		if ( isset( $aioseop_options[ "aiosp_{$title_type}_title_format" ] ) ) {
			$title_format = $aioseop_options[ "aiosp_{$title_type}_title_format" ];
		}

		if ( ! empty( $aioseop_options['aiosp_cpostactive'] ) ) {
			$wp_post_types = $aioseop_options['aiosp_cpostactive'];

			$is_post_type_archive = ( 'archive' == $title_type ) && is_post_type_archive( $wp_post_types );
			$is_singular_post     = ( 'post' == $title_type ) && $this->is_singular( $wp_post_types, $p );

			if ( $is_post_type_archive || $is_singular_post ) {
				if ( $is_post_type_archive ) {
					$prefix = "aiosp_{$title_type}_";
				} else {
					$prefix = 'aiosp_';
				}

				$post_type = get_post_type( $p );

				if ( ! empty( $aioseop_options[ "{$prefix}{$post_type}_title_format" ] ) ) {
					$title_format = $aioseop_options[ "{$prefix}{$post_type}_title_format" ];
				}
			}
		}

		return $title_format;
	}

	/**
	 * Is Singular
	 *
	 * @since ?
	 *
	 * @param array $post_types
	 * @param null $post
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
	 * Is Static Posts Page
	 *
	 * @since ?
	 *
	 * @return bool|null
	 */
	function is_static_posts_page() {
		static $is_posts_page = null;
		if ( null !== $is_posts_page ) {
			return $is_posts_page;
		}
		$post          = $this->get_queried_object();
		$is_posts_page = ( 'page' == get_option( 'show_on_front' ) && is_home() && ! empty( $post ) && get_option( 'page_for_posts' ) == $post->ID );

		return $is_posts_page;
	}

	/**
	 * Is Static Front Page
	 *
	 * @since ?
	 *
	 * @return bool|null
	 */
	function is_static_front_page() {
		if ( isset( $this->is_front_page ) && null !== $this->is_front_page ) {
			return $this->is_front_page;
		}
		$post                = $this->get_queried_object();
		$this->is_front_page = ( 'page' == get_option( 'show_on_front' ) && is_page() && ! empty( $post ) && get_option( 'page_on_front' ) == $post->ID );

		return $this->is_front_page;
	}

	/**
	 * Get All Categories
	 *
	 * @since ?
	 *
	 * @param int $id
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
	 * Get Taxonomy Title
	 *
	 * @since ?
	 *
	 * @param string $tax
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

	/**
	 * Gets Taxonomy Name
	 *
	 * @param $tax
	 *
	 * @since ?
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
	 * Get Taxonomy Description
	 *
	 * @since ?
	 *
	 * @param $tax
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
	 * Apply Taxonomy Title Format
	 *
	 * @since ?
	 *
	 * @param $category_name
	 * @param $category_description
	 * @param string $tax
	 * @return string
	 */
	function apply_tax_title_format( $category_name, $category_description, $tax = '' ) {

		/**
		 * Runs before applying the formatting for the taxonomy title.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_before_tax_title_format' );

		if ( empty( $tax ) ) {
			$tax = get_query_var( 'taxonomy' );
		}
		$title_format = $this->get_tax_title_format( $tax );
		$title        = str_replace( '%taxonomy_title%', $category_name, $title_format );
		if ( false !== strpos( $title, '%taxonomy_description%', 0 ) ) {
			$title = str_replace( '%taxonomy_description%', $category_description, $title );
		}
		if ( false !== strpos( $title, '%category_title%', 0 ) ) {
			$title = str_replace( '%category_title%', $category_name, $title );
		}
		if ( false !== strpos( $title, '%category_description%', 0 ) ) {
			$title = str_replace( '%category_description%', $category_description, $title );
		}
		if ( false !== strpos( $title, '%site_title%', 0 ) ) {
			$title = str_replace( '%site_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
		}
		if ( false !== strpos( $title, '%blog_title%', 0 ) ) {
			$title = str_replace( '%blog_title%', $this->internationalize( get_bloginfo( 'name' ) ), $title );
		}
		if ( false !== strpos( $title, '%site_description%', 0 ) ) {
			$title = str_replace( '%site_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
		}
		if ( false !== strpos( $title, '%blog_description%', 0 ) ) {
			$title = str_replace( '%blog_description%', $this->internationalize( get_bloginfo( 'description' ) ), $title );
		}
		if ( false !== strpos( $title, '%current_year%', 0 ) ) {
			$title = str_replace( '%current_year%', date( 'Y' ), $title );
		}
		if ( false !== strpos( $title, '%current_month%', 0 ) ) {
			$title = str_replace( '%current_month%', date( 'M' ), $title );
		}
		if ( false !== strpos( $title, '%current_month_i18n%', 0 ) ) {
			$title = str_replace( '%current_month_i18n%', date_i18n( 'M' ), $title );
		}

		/**
		 * Filters document title after applying the formatting.
		 *
		 * @since 3.0
		 *
		 * @param string $title Document title to be filtered.
		 */
		$title = apply_filters( 'aioseop_title_format', $title );

		$title = wp_strip_all_tags( $title );

		/**
		 * Runs after applying the formatting for the taxonomy title.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_after_tax_title_format' );

		return $this->paged_title( $title );
	}

	/**
	 * Get Taxonomy Title Format
	 *
	 * @since ?
	 *
	 * @param string $tax
	 * @return string
	 */
	function get_tax_title_format( $tax = '' ) {
		global $aioseop_options;
		if ( AIOSEOPPRO ) {
			$title_format = '%taxonomy_title% | %site_title%';
			if ( is_category() ) {
				$title_format = $aioseop_options['aiosp_category_title_format'];
			} else {
				$taxes = $aioseop_options['aiosp_taxactive'];
				if ( empty( $tax ) ) {
					$tax = get_query_var( 'taxonomy' );
				}
				if ( ! empty( $aioseop_options[ "aiosp_{$tax}_tax_title_format" ] ) ) {
					$title_format = $aioseop_options[ "aiosp_{$tax}_tax_title_format" ];
				}
			}
			if ( empty( $title_format ) ) {
				$title_format = '%category_title% | %site_title%';
			}
		} else {
			$title_format = '%category_title% | %site_title%';
			if ( ! empty( $aioseop_options['aiosp_category_title_format'] ) ) {
				$title_format = $aioseop_options['aiosp_category_title_format'];
			}

			return $title_format;
		}

		return $title_format;
	}

	/**
	 * Apply Archive Title Format
	 *
	 * @since ?
	 *
	 * @param $title
	 * @param string $category
	 * @return string
	 */
	function apply_archive_title_format( $title, $category = '' ) {
		$title_format = $this->get_archive_title_format();
		$r_title      = array( '%site_title%', '%site_description%', '%archive_title%' );
		$d_title      = array(
			$this->internationalize( get_bloginfo( 'name' ) ),
			$this->internationalize( get_bloginfo( 'description' ) ),
			post_type_archive_title( '', false ),
		);
		$title        = trim( str_replace( $r_title, $d_title, $title_format ) );

		return $title;
	}

	/**
	 * Get Archive Title Format
	 *
	 * @since ?
	 *
	 * @return bool|string
	 */
	function get_archive_title_format() {
		return $this->get_post_title_format( 'archive' );
	}

	/**
	 * Get Main Description
	 *
	 * @since ?
	 * @since 2.3.14 #932 Adds filter "aioseop_description", removes extra filtering.
	 * @since 2.4 #951 Trim/truncates occurs inside filter "aioseop_description".
	 * @since 2.4.4.1 #1395 Longer Meta Descriptions & don't trim manual Descriptions.
	 *
	 * @param null $post
	 * @return mixed|string|void
	 */
	function get_main_description( $post = null ) {
		global $aioseop_options;
		$opts        = $this->meta_opts;
		$description = '';
		if ( is_author() && $this->show_page_description() ) {
			$description = $this->internationalize( get_the_author_meta( 'description' ) );
		} elseif ( function_exists( 'wc_get_page_id' ) && is_post_type_archive( 'product' ) ) {
			$post_id = wc_get_page_id( 'shop' );
			if ( $post_id ) {
				$post = get_post( $post_id );
				// $description = $this->get_post_description( $post );
				// $description = $this->apply_cf_fields( $description );
				if ( ! ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) ) ) {
					$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
				} elseif ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
					// $description = $this->get_aioseop_description( $post );
					$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
				} elseif ( wc_get_page_id( 'shop' ) == get_option( 'page_on_front' ) && empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
					$description = $this->get_aioseop_description( $post );
				}
			}
		} elseif ( is_front_page() ) {
			$description = $this->get_aioseop_description( $post );
		} elseif ( is_single() || is_page() || is_attachment() || is_home() || $this->is_static_posts_page() || $this->check_singular() ) {
			$description = $this->get_aioseop_description( $post );
		} elseif ( ( is_category() || is_tag() || is_tax() ) && $this->show_page_description() ) {
			if ( ! empty( $opts ) && AIOSEOPPRO ) {
				$description = $opts['aiosp_description'];
			}
			if ( empty( $description ) ) {
				$description = term_description();
			}
			$description = $this->internationalize( $description );
		}

		// #1308 - we want to make sure we are ignoring php version only in the admin area while editing the post, so that it does not impact #932.
		$screen             = is_admin() ? get_current_screen() : null;
		$ignore_php_version = $screen && isset( $screen->id ) && 'post' === $screen->id;

		$truncate     = false;
		$aioseop_desc = '';
		if ( ! empty( $post->ID ) ) {
			$aioseop_desc = get_post_meta( $post->ID, '_aioseop_description', true );
		}

		if ( empty( $aioseop_desc ) && isset( $aioseop_options['aiosp_generate_descriptions'] ) && 'on' === $aioseop_options['aiosp_generate_descriptions'] && empty( $aioseop_options['aiosp_dont_truncate_descriptions'] ) ) {
			$truncate = true;
		}

		$description = apply_filters(
			'aioseop_description',
			$description,
			$truncate,
			$ignore_php_version
		);

		return $description;
	}

	/**
	 * Show Page Description
	 *
	 * @since ?
	 *
	 * @return bool
	 */
	function show_page_description() {
		global $aioseop_options;
		if ( ! empty( $aioseop_options['aiosp_hide_paginated_descriptions'] ) ) {
			$page = aioseop_get_page_number();
			if ( ! empty( $page ) && ( $page > 1 ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get AIOSEOP Description
	 *
	 * @since ?
	 * @since 2.4 #1395 Longer Meta Descriptions & don't trim manual Descriptions.
	 *
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
	 * Gets Post Description
	 *
	 * Auto-generates description if settings are ON.
	 *
	 * @since 2.3.13 #899 Fixes non breacking space, applies filter "aioseop_description".
	 * @since 2.3.14 #932 Removes filter "aioseop_description".
	 * @since 2.4 #951 Removes "wp_strip_all_tags" and "trim_excerpt_without_filters", they are done later in filter.
	 * @since 2.4 #1395 Longer Meta Descriptions & don't trim manual Descriptions.
	 *
	 * @param object $post Post object.
	 * @return mixed|string
	 */
	function get_post_description( $post ) {
		global $aioseop_options;
		if ( ! $this->show_page_description() ) {
			return '';
		}
		$description = trim( $this->internationalize( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
		if ( ! empty( $post ) && post_password_required( $post ) ) {
			return $description;
		}
		if ( ! $description && ! empty( $aioseop_options['aiosp_generate_descriptions'] ) ) {
			if ( empty( $aioseop_options['aiosp_skip_excerpt'] ) ) {
				$description = $post->post_excerpt;
			}
			if ( ! $description ) {
				if ( ! AIOSEOPPRO || ( AIOSEOPPRO && apply_filters( 'aiosp_generate_descriptions_from_content', true, $post ) ) ) {
					$content = $post->post_content;
					if ( ! empty( $aioseop_options['aiosp_run_shortcodes'] ) ) {
						$content = aioseop_do_shortcodes( $content );
					}
					$description = $content;
				} else {
					$description = $post->post_excerpt;
				}
			}

			$description = $this->trim_text_without_filters_full_length( $this->internationalize( $description ) );
		}

		return $description;
	}

	/**
	 * Trim Text without Filter Full Length
	 *
	 * @since ?
	 * @since 2.3.15 Brackets not longer replaced from filters.
	 *
	 * @param $text
	 * @return string
	 */
	function trim_text_without_filters_full_length( $text ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = strip_shortcodes( $text );
		$text = wp_strip_all_tags( $text );

		return trim( $text );
	}

	/**
	 * Trim Excerpt without Filters
	 *
	 * @since ?
	 * @since 2.3.15 Brackets not longer replaced from filters.
	 *
	 * @param $text
	 * @param int $max
	 * @return string
	 */
	function trim_excerpt_without_filters( $text, $max = 0 ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = strip_shortcodes( $text );
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
				$pos = mb_strrpos( $text2, ' ', - ( $len - $max ), 'UTF-8' );
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
	 * AIOSEOP Get URL
	 *
	 * @since ?
	 *
	 * @todo Change name to `*_get_url`.
	 *
	 * @param $query
	 * @param bool $show_page
	 * @return bool|false|string
	 */
	function aiosp_mrt_get_url( $query, $show_page = true ) {
		if ( $query->is_404 || $query->is_search ) {
			return false;
		}

		// this boolean will determine if any additional parameters will be added to the final link or not.
		// this is especially useful in issues such as #491.
		$add_query_params = false;
		$link             = '';
		$haspost          = false;
		if ( ! empty( $query->posts ) ) {
			$haspost = count( $query->posts ) > 0;
		}

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
			$add_query_params = true;
		} elseif ( $query->is_home && ( get_option( 'show_on_front' ) == 'page' ) ) {
			$pageid = get_option( 'page_for_posts' );
			if ( $pageid ) {
				$link = aioseop_get_permalink( $pageid );
			}
		} elseif ( is_front_page() || ( $query->is_home && ( get_option( 'show_on_front' ) != 'page' || ! get_option( 'page_for_posts' ) ) ) ) {
			if ( function_exists( 'icl_get_home_url' ) ) {
				$link = icl_get_home_url();
			} else {
				$link = trailingslashit( home_url() );
			}
		} elseif ( ( $query->is_single || $query->is_page ) && $haspost ) {
			$post = $query->posts[0];
			$link = aioseop_get_permalink( $post->ID );
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
			$link             = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
			$add_query_params = true;
		} elseif ( $query->is_month && $haspost ) {
			$link             = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
			$add_query_params = true;
		} elseif ( $query->is_year && $haspost ) {
			$link             = get_year_link( get_query_var( 'year' ) );
			$add_query_params = true;
		} elseif ( $query->is_tax && $haspost ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$term     = get_query_var( 'term' );
			if ( ! empty( $term ) ) {
				$link = get_term_link( $term, $taxonomy );
			}
		} elseif ( $query->is_archive && function_exists( 'get_post_type_archive_link' ) ) {
			$post_type = get_query_var( 'post_type' );
			if ( $post_type && is_array( $post_type ) ) {
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

		if ( $add_query_params ) {
			$post_type = get_query_var( 'post_type' );
			if ( ! empty( $post_type ) ) {
				$link = add_query_arg( 'post_type', $post_type, $link );
			}
		}

		return $link;
	}

	/**
	 * Get Paged
	 *
	 * @since ?
	 *
	 * @param $link
	 * @return string
	 */
	function get_paged( $link ) {
		global $wp_rewrite;
		$page      = aioseop_get_page_number();
		$page_name = 'page';
		if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
			$page_name = $wp_rewrite->pagination_base;
		}
		if ( ! empty( $page ) && $page > 1 ) {
			if ( get_query_var( 'page' ) == $page ) {
				if ( get_query_var( 'p' ) ) {
					// non-pretty urls.
					$link = add_query_arg( 'page', $page, $link );
				} else {
					$link = trailingslashit( $link ) . "$page";
				}
			} else {
				if ( get_query_var( 'p' ) ) {
					// non-pretty urls.
					$link = add_query_arg( 'page', $page, trailingslashit( $link ) . $page_name );
				} else {
					$link = trailingslashit( $link ) . trailingslashit( $page_name ) . $page;
				}
			}
			$link = user_trailingslashit( $link, 'paged' );
		}

		return $link;
	}

	/**
	 * Get Main Keywords
	 *
	 * @since ?
	 *
	 * @return comma|string
	 */
	function get_main_keywords() {
		global $aioseop_options;
		global $aioseop_keywords;
		global $post;

		$opts      = $this->meta_opts;
		$blog_page = aiosp_common::get_blog_page( $post );
		if ( ( is_front_page() && $aioseop_options['aiosp_home_keywords'] && ! $this->is_static_posts_page() ) || $this->is_static_front_page() ) {
			if ( ! empty( $aioseop_options['aiosp_use_static_home_info'] ) ) {
				$keywords = $this->get_all_keywords();
			} else {
				$keywords = trim( $this->internationalize( $aioseop_options['aiosp_home_keywords'] ) );
			}
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && $this->is_static_posts_page() ) {
			$keywords = stripslashes( $this->internationalize( $opts['aiosp_keywords'] ) ); // And if option = use page set keywords instead of keywords from recent posts.
		} elseif ( $blog_page && empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) ) {
			$keywords = stripslashes( $this->internationalize( get_post_meta( $blog_page->ID, '_aioseop_keywords', true ) ) );
		} elseif ( empty( $aioseop_options['aiosp_dynamic_postspage_keywords'] ) && ( is_archive() || is_post_type_archive() ) ) {
			$keywords = '';
		} else {
			$keywords = $this->get_all_keywords();
		}

		return $keywords;
	}

	/**
	 * Get All Keywords
	 *
	 * @since ?
	 *
	 * @return string|null comma-separated list of unique keywords
	 */
	function get_all_keywords() {
		global $posts;
		global $aioseop_options;
		if ( is_404() ) {
			return null;
		}
		// If we are on synthetic pages.
		if ( ! is_home() && ! is_page() && ! is_single() && ! $this->is_static_front_page() && ! $this->is_static_posts_page() && ! is_archive() && ! is_post_type_archive() && ! is_category() && ! is_tag() && ! is_tax() && ! $this->check_singular() ) {
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
	 * Keyword String to List
	 *
	 * @since ?
	 *
	 * @param $keywords
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
	 * Get All Tags
	 *
	 * @since ?
	 *
	 * @param int $id
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
	 * Get Unique Keywords
	 *
	 * @since ?
	 *
	 * @param $keywords
	 * @return string
	 */
	function get_unique_keywords( $keywords ) {
		return implode( ',', $this->clean_keyword_list( $keywords ) );
	}

	/**
	 * Clean Keyword List
	 *
	 * @since ?
	 *
	 * @param $keywords
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
	 * Split Share Term
	 *
	 * @since ?
	 *
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
	 * Get All Term Data
	 *
	 * @since ?
	 *
	 * @param $term_id
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
		// $this->add_admin_pointers();
		// TODO Enqueue script as a JS file.
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
					foreach ( $this->pointers as $k => $p ) {
						if ( ! empty( $p['pointer_scope'] ) && ( 'global' === $p['pointer_scope'] ) ) {
							?>
												admin_index = "<?php echo esc_attr( $k ); ?>";
											admin_pointer = <?php echo json_encode( $p ); ?>;
											aioseop_show_pointer(admin_index, admin_pointer);
											<?php
						}
					}
					?>
				});
			}
			<?php	} ?>
		</script>
		<?php
	}

	/*
	 * Admin Pointer function.
	 * Not in use at the moment. Below is an example of we can implement them.
	 *
	function add_admin_pointers() {

		$pro = '';
		if ( AIOSEOPPRO ) {
			$pro = '-pro';
		}

		$this->pointers['aioseop_menu_2640'] = array(
			'pointer_target' => "#toplevel_page_all-in-one-seo-pack$pro-aioseop_class",
			'pointer_text'   => '<h3>' . __( 'Review Your Settings', 'all-in-one-seo-pack' )
								. '</h3><p>' . sprintf( __( 'Welcome to version %1$s. Thank you for running the latest and greatest %2$s ever! Please review your settings, as we\'re always adding new features for you!', 'all-in-one-seo-pack' ), AIOSEOP_VERSION, AIOSEOP_PLUGIN_NAME ) . '</p>',
			'pointer_edge'   => 'top',
			'pointer_align'  => 'left',
			'pointer_scope'  => 'global',
		);
		$this->filter_pointers();
	}
	*/

	/**
	 * Add Page Hooks
	 *
	 * @since ?
	 */
	function add_page_hooks() {

		global $aioseop_options;

		$post_objs  = get_post_types( '', 'objects' );
		$pt         = array_keys( $post_objs );
		$rempost    = array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ); // Don't show these built-in types as options for CPT SEO.
		$pt         = array_diff( $pt, $rempost );
		$post_types = array();

		foreach ( $pt as $p ) {
			if ( ! empty( $post_objs[ $p ]->label ) ) {
				$post_types[ $p ] = $post_objs[ $p ]->label;
			} else {
				$post_types[ $p ] = $p;
			}
		}

		foreach ( $pt as $p ) {
			if ( ! empty( $post_objs[ $p ]->label ) ) {
				$all_post_types[ $p ] = $post_objs[ $p ]->label;
			}
		}

		if ( isset( $post_types['attachment'] ) ) {
			/* translators: This refers to entries in the Media Library (images, videos, recordings and other files) and their attachment pages. */
			$post_types['attachment'] = __( 'Media / Attachments', 'all-in-one-seo-pack' );
		}
		if ( isset( $all_post_types['attachment'] ) ) {
			$all_post_types['attachment'] = __( 'Media / Attachments', 'all-in-one-seo-pack' );
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

		/**
		 * Allows users to filter the taxonomies that are shown in the General Settings menu.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tax_types All registered taxonomies.
		 */
		$tax_types = apply_filters( 'aioseop_pre_tax_types_setting', $tax_types );

		$this->default_options['posttypecolumns']['initial_options'] = $post_types;
		$this->default_options['cpostactive']['initial_options']     = $all_post_types;
		$this->default_options['cpostnoindex']['initial_options']    = $post_types;
		$this->default_options['cpostnofollow']['initial_options']   = $post_types;
		if ( AIOSEOPPRO ) {
			$this->default_options['taxactive']['initial_options'] = $tax_types;
		}

		foreach ( $all_post_types as $p => $pt ) {
			$field = $p . '_title_format';
			$name  = $post_objs[ $p ]->labels->singular_name;
			if ( ! isset( $this->default_options[ $field ] ) ) {
				$this->default_options[ $field ]  = array(
					/* translators: The title format is the template that is used to format the title for each post of a certain post type (Posts, Pages, etc.). */
					'name'     => "$name " . __( 'Title Format', 'all-in-one-seo-pack' ) . "<br />($p)",
					'type'     => 'text',
					'default'  => '%post_title% | %site_title%',
					'condshow' => array(
						'aiosp_cpostactive\[\]' => $p,
					),
				);
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
						/* translators: The taxonomy title format is the template that is used to format the title for each taxonomy term of a certain taxonomy (Categories, Tags, etc.). */
						'name'     => "$name " . __( 'Taxonomy Title Format:', 'all-in-one-seo-pack' ),
						'type'     => 'text',
						'default'  => '%taxonomy_title% | %site_title%',
						'condshow' => array(
							'aiosp_taxactive\[\]' => $p,
						),
					);
					$this->layout['cpt']['options'][] = $field;
				}
			}
		}
		$this->setting_options();

		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;
			add_action( "{$this->prefix}update_options", array( $aioseop_update_checker, 'license_change_check' ), 10, 2 );
			add_action( "{$this->prefix}settings_update", array( $aioseop_update_checker, 'update_check' ), 10, 2 );
		}

		add_filter( "{$this->prefix}display_options", array( $this, 'filter_options' ), 10, 2 );
		parent::add_page_hooks();
	}

	function settings_page_init() {
		add_filter( "{$this->prefix}submit_options", array( $this, 'filter_submit' ) );
	}

	/**
	 * Admin Enqueue Styles All (Screens)
	 *
	 * Enqueue style on all admin screens.
	 *
	 * @since 2.9
	 *
	 * @param $hook_suffix
	 */
	public function admin_enqueue_styles_all( $hook_suffix ) {
		wp_enqueue_style(
			'aiosp_admin_style',
			AIOSEOP_PLUGIN_URL . 'css/aiosp_admin.css',
			array(),
			AIOSEOP_VERSION
		);
	}

	/**
	 * Admin Enqueue Scripts
	 *
	 * @since 2.5.0
	 * @since 2.9 Refactor code to `admin_enqueue_scripts` hook, and move enqueue stylesheet to \All_in_One_SEO_Pack::admin_enqueue_styles_all().
	 *
	 * @uses All_in_One_SEO_Pack_Module::admin_enqueue_scripts();
	 *
	 * @param string $hook_suffix
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		global $current_screen;
		global $aioseop_options;

		add_filter( "{$this->prefix}display_settings", array( $this, 'filter_settings' ), 10, 3 );
		add_filter( "{$this->prefix}display_options", array( $this, 'filter_options' ), 10, 2 );

		$count_chars_data = array();
		switch ( $hook_suffix ) {
			case 'term.php':
				// Legacy code for taxonomy terms until we refactor all title format related code.
				$count_chars_data['aiosp_title_extra'] = 0;
				wp_enqueue_script(
					'aioseop-count-chars-old',
					AIOSEOP_PLUGIN_URL . 'js/admin/aioseop-count-chars-old.js',
					array(),
					AIOSEOP_VERSION,
					true
				);
				wp_localize_script( 'aioseop-count-chars-old', 'aioseop_count_chars', $count_chars_data );
				break;
			case 'post.php':
			case 'post-new.php':
				$title_format       = $this->get_preview_snippet_title();
				$extra_title_length = strlen( preg_replace( '/<span.*\/span>/', '', $title_format ) );

				$snippet_preview_data = array(
					'autogenerateDescriptions' => $aioseop_options['aiosp_generate_descriptions'],
					'skipExcerpt'              => $aioseop_options['aiosp_skip_excerpt'],
				);

				$count_chars_data['extraTitleLength']         = $extra_title_length;
				$count_chars_data['autogenerateDescriptions'] = $aioseop_options['aiosp_generate_descriptions'];

				wp_enqueue_script(
					'aioseop-preview-snippet',
					AIOSEOP_PLUGIN_URL . 'js/admin/aioseop-preview-snippet.js',
					array(),
					AIOSEOP_VERSION
				);
				wp_localize_script( 'aioseop-preview-snippet', 'aioseop_preview_snippet', $snippet_preview_data );

				wp_enqueue_script(
					'xregexp',
					'https://cdn.jsdelivr.net/npm/xregexp@3.2.0',
					array(),
					'3.2.0'
				);
				// No break required.
			case 'toplevel_page_' . AIOSEOP_PLUGIN_DIRNAME . '/aioseop_class':
				$count_chars_data['pluginDirName'] = AIOSEOP_PLUGIN_DIRNAME;
				$count_chars_data['currentPage']   = $hook_suffix;

				wp_enqueue_script(
					'aioseop-admin-functions',
					AIOSEOP_PLUGIN_URL . 'js/admin/aioseop-admin-functions.js',
					array(),
					AIOSEOP_VERSION
				);

				wp_enqueue_script(
					'aioseop-count-chars',
					AIOSEOP_PLUGIN_URL . 'js/admin/aioseop-count-chars.js',
					array(),
					AIOSEOP_VERSION,
					true
				);
				wp_localize_script( 'aioseop-count-chars', 'aioseopCharacterCounter', $count_chars_data );
				break;
		}
		parent::admin_enqueue_scripts( $hook_suffix );
	}

	/**
	 * Filter Submit
	 *
	 * @since ?
	 *
	 * @param $submit
	 * @return mixed
	 */
	function filter_submit( $submit ) {
		$submit['Submit_Default']     = array(
			'type'  => 'submit',
			'class' => 'aioseop_reset_settings_button button-secondary',
			/* translators: This is the text of a button that allows users to reset the General Settings to their default values. */
			'value' => __( 'Reset General Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;',
		);
		$submit['Submit_All_Default'] = array(
			'type'  => 'submit',
			'class' => 'aioseop_reset_settings_button button-secondary',
			/* translators: This is the text of a button that allows users to reset all settings across the entire plugin to their default values. */
			'value' => __( 'Reset ALL Settings to Defaults', 'all-in-one-seo-pack' ) . ' &raquo;',
		);

		return $submit;
	}

	/**
	 * Reset Options
	 *
	 * Handle resetting options to defaults, but preserve the license key if pro.
	 *
	 * @since ?
	 *
	 * @param null $location
	 * @param bool $delete
	 */
	function reset_options( $location = null, $delete = false ) {
		if ( AIOSEOPPRO ) {
			global $aioseop_update_checker;
		}
		if ( true === $delete ) {

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
				if ( 'aiosp_license_key' != $k ) {
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
	 * Filter Settings
	 *
	 * @since ?
	 * @since 2.3.16 Forces HTML entity decode on placeholder values.
	 *
	 * @param $settings
	 * @param $location
	 * @param $current
	 * @return mixed
	 */
	function filter_settings( $settings, $location, $current ) {
		if ( null == $location ) {
			$prefix = $this->prefix;

			foreach ( array( 'seopostcol', 'seocustptcol', 'debug_info', 'max_words_excerpt' ) as $opt ) {
				unset( $settings[ "{$prefix}$opt" ] );
			}

			if ( AIOSEOPPRO ) {
				if ( ! empty( $this->options['aiosp_license_key'] ) ) {
					$settings['aiosp_license_key']['type'] = 'password';
					$settings['aiosp_license_key']['size'] = 38;
				}
			}
		} elseif ( 'aiosp' == $location ) {
			global $post, $aioseop_sitemap;
			$prefix = $this->get_prefix( $location ) . $location . '_';
			if ( ! empty( $post ) ) {
				$post_type = get_post_type( $post );
				if ( ! empty( $this->options['aiosp_cpostnoindex'] ) && in_array( $post_type, $this->options['aiosp_cpostnoindex'] ) ) {
					$settings[ "{$prefix}noindex" ]['type']            = 'select';
					$settings[ "{$prefix}noindex" ]['initial_options'] = array(
						/* translators: This indicates that the current post/page is using the default value for its post type, which is NOINDEX. */
						''    => __( 'Default - noindex', 'all-in-one-seo-pack' ),
						'off' => __( 'index', 'all-in-one-seo-pack' ),
						'on'  => __( 'noindex', 'all-in-one-seo-pack' ),
					);
				}
				if ( ! empty( $this->options['aiosp_cpostnofollow'] ) && in_array( $post_type, $this->options['aiosp_cpostnofollow'] ) ) {
					$settings[ "{$prefix}nofollow" ]['type']            = 'select';
					$settings[ "{$prefix}nofollow" ]['initial_options'] = array(
						/* translators: This indicates that the current post/page is using the default value for its post type, which is NOFOLLOW. */
						''    => __( 'Default - nofollow', 'all-in-one-seo-pack' ),
						'off' => __( 'follow', 'all-in-one-seo-pack' ),
						'on'  => __( 'nofollow', 'all-in-one-seo-pack' ),
					);
				}

				global $post;
				$info = $this->get_page_snippet_info();

				$title       = $info['title'];
				$description = $info['description'];
				$keywords    = $info['keywords'];

				$settings[ "{$prefix}title" ]['placeholder']       = $this->html_entity_decode( $title );
				$settings[ "{$prefix}description" ]['placeholder'] = $this->html_entity_decode( $description );
				$settings[ "{$prefix}keywords" ]['placeholder']    = $keywords;
			}

			if ( ! AIOSEOPPRO ) {
				if ( ! current_user_can( 'update_plugins' ) ) {
					unset( $settings[ "{$prefix}upgrade" ] );
				}
			}

			if ( ! is_object( $aioseop_sitemap ) ) {
				unset( $settings['aiosp_sitemap_exclude'] );
			}

			if ( ! empty( $this->options[ $this->prefix . 'togglekeywords' ] ) ) {
				unset( $settings[ "{$prefix}keywords" ] );
				unset( $settings[ "{$prefix}togglekeywords" ] );
			} elseif ( ! empty( $current[ "{$prefix}togglekeywords" ] ) ) {
				unset( $settings[ "{$prefix}keywords" ] );
			}
			if ( empty( $this->options['aiosp_can'] ) ) {
				unset( $settings[ "{$prefix}custom_link" ] );
			}
		}

		return $settings;
	}

	/**
	 * Filter Options
	 *
	 * @since ?
	 *
	 * @param $options
	 * @param $location
	 * @return mixed
	 */
	function filter_options( $options, $location ) {
		if ( 'aiosp' == $location ) {
			global $post;
			if ( ! empty( $post ) ) {
				$prefix    = $this->prefix;
				$post_type = get_post_type( $post );
				foreach ( array( 'noindex', 'nofollow' ) as $no ) {
					if ( empty( $this->options[ 'aiosp_cpost' . $no ] ) || ( ! in_array( $post_type, $this->options[ 'aiosp_cpost' . $no ] ) ) ) {
						if ( isset( $options[ "{$prefix}{$no}" ] ) && ( 'on' != $options[ "{$prefix}{$no}" ] ) ) {
							unset( $options[ "{$prefix}{$no}" ] );
						}
					}
				}
			}
		}
		if ( null == $location ) {
			$prefix = $this->prefix;
			if ( isset( $options[ "{$prefix}use_original_title" ] ) && ( '' === $options[ "{$prefix}use_original_title" ] ) ) {
				$options[ "{$prefix}use_original_title" ] = 0;
			}
		}

		return $options;
	}

	/**
	 * Template Redirect
	 *
	 * @since ?
	 */
	function template_redirect() {
		global $aioseop_options;

		$post = $this->get_queried_object();

		if ( ! $this->is_page_included() ) {
				return;
		}

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

	/**
	 * The is_page_included() function.
	 *
	 * Checks whether All in One SEO Pack is enabled for this page.
	 *
	 * @since ?
	 * @since 3.3 Show Google Analytics if post type isn't checked in options.
	 *
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
			} elseif ( ! in_array( $post_type, $wp_post_types ) && ! is_front_page() && ! is_post_type_archive( $wp_post_types ) && ! is_404() && ! is_search() ) {
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

		$this->meta_opts = $this->get_current_options( array(), 'aiosp' );

		$aiosp_disable = false;

		if ( ! empty( $this->meta_opts ) ) {
			if ( isset( $this->meta_opts['aiosp_disable'] ) ) {
				$aiosp_disable = $this->meta_opts['aiosp_disable'];
			}
		}

		$aiosp_disable = apply_filters( 'aiosp_disable', $aiosp_disable ); // API filter to disable AIOSEOP.

		if ( $aiosp_disable ) {
			return false;
		}

		if ( ! empty( $this->meta_opts ) && true == $this->meta_opts['aiosp_disable'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Output Callback for Title
	 *
	 * @since ?
	 *
	 * @param $content
	 * @return mixed|string
	 */
	function output_callback_for_title( $content ) {
		return $this->rewrite_title( $content );
	}

	/**
	 * Rewrite Title
	 *
	 * Used for forcing title rewrites.
	 *
	 * @since ?
	 *
	 * @param $header
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
	 * Replace Title
	 *
	 * @since ?
	 *
	 * @param $content
	 * @param $title
	 * @return mixed
	 */
	function replace_title( $content, $title ) {
		// We can probably improve this... I'm not sure half of this is even being used.
		$title             = trim( strip_tags( $title ) );
		$title_tag_start   = '<title';
		$title_tag_end     = '</title';
		$start             = $this->strpos( $content, $title_tag_start, 0 );
		$end               = $this->strpos( $content, $title_tag_end, 0 );
		$this->title_start = $start;
		$this->title_end   = $end;
		$this->orig_title  = $title;

		return preg_replace( '/<title([^>]*?)\s*>([^<]*?)<\/title\s*>/is', '<title\\1>' . preg_replace( '/(\$|\\\\)(?=\d)/', '\\\\\1', strip_tags( $title ) ) . '</title>', $content, 1 );
	}

	/**
	 * Add Hooks
	 *
	 * Adds WordPress hooks.
	 *
	 * @since ?
	 * @since 2.3.13 #899 Adds filter:aioseop_description.
	 * @since 2.3.14 #593 Adds filter:aioseop_title.
	 * @since 2.4 #951 Increases filter:aioseop_description arguments number.
	 */
	function add_hooks() {
		global $aioseop_options, $aioseop_update_checker;

		if ( is_admin() ) {
			// this checks if the settiongs options exist and if they dont, it sets the defaults.
			// let's do this only in backend.
			aioseop_update_settings_check();
		}
		add_filter( 'user_contactmethods', 'aioseop_add_contactmethods' );
		if ( is_user_logged_in() && is_admin_bar_showing() && current_user_can( 'aiosp_manage_seo' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );
		}

		if ( is_admin() ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			}
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			add_action( 'admin_head', array( $this, 'add_page_icon' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles_all' ) );
			add_action( 'admin_init', 'aioseop_addmycolumns', 1 );
			add_action( 'admin_init', 'aioseop_handle_ignore_notice' );
			add_action( 'shutdown', array( $this, 'check_recently_activated_modules' ), 99 );
			if ( AIOSEOPPRO ) {
				if ( current_user_can( 'update_plugins' ) ) {
					add_action( 'admin_notices', array( $aioseop_update_checker, 'key_warning' ) );
				}
				add_action( 'after_plugin_row_' . AIOSEOP_PLUGIN_BASENAME, array( $aioseop_update_checker, 'add_plugin_row' ) );
			}
		} else {
			if ( '1' == $aioseop_options['aiosp_can'] || 'on' == $aioseop_options['aiosp_can'] ) {
				remove_action( 'wp_head', 'rel_canonical' );
			}
			add_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ), apply_filters( 'aioseop_wp_head_priority', 1 ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );
		}
		add_filter( 'aioseop_description', array( &$this, 'filter_description' ), 10, 3 );
		add_filter( 'aioseop_title', array( &$this, 'filter_title' ) );

		// Plugin compatibility hooks.
		// AMP.
		$this->add_hooks_amp();

		// TODO Move WooCommerce hooks here from __construct().
	}

	/**
	 * Add Hooks for AMP.
	 *
	 * @since 3.3.0
	 */
	protected function add_hooks_amp() {
		if ( is_admin() ) {
			return;
		}
		global $aioseop_options;

		// Add AIOSEOP's output to AMP.
		add_action( 'amp_post_template_head', array( $this, 'amp_head' ), 11 );

		/**
		 * AIOSEOP AMP Schema Enable/Disable
		 *
		 * Allows or prevents the use of schema on AMP generated posts/pages.
		 *
		 * @since 3.3.0
		 *
		 * @param bool $var True to enable, and false to disable.
		 */
		$use_schema = apply_filters( 'aioseop_amp_schema', true );
		if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) && (bool) $aioseop_options['aiosp_schema_markup'] && $use_schema ) {
			// Removes AMP's Schema data to prevent any conflicts/duplications with AIOSEOP's.
			add_action( 'amp_post_template_head', array( $this, 'remove_hooks_amp_schema' ), 9 );
		}
	}

	/**
	 * Remove Hooks with AMP's Schema.
	 *
	 * @since 3.3.0
	 */
	public function remove_hooks_amp_schema() {
		// Remove AMP Schema hook used for outputting data.
		remove_action( 'amp_post_template_head', 'amp_print_schemaorg_metadata' );
	}

	/**
	 * Visibility Warning
	 *
	 * Checks if 'Search Engine Visibility' is enabled in Settings > Reading.
	 *
	 * @todo Change to earlier hook. Before `admin_enqueue` if possible.
	 *
	 * @since ?
	 * @since 3.0 Changed to AIOSEOP_Notices class.
	 *
	 * @see `self::constructor()` with 'all_admin_notices' Filter Hook
	 */
	function visibility_warning() {
		global $aioseop_notices;
		if ( '0' === get_option( 'blog_public' ) ) {
			$aioseop_notices->activate_notice( 'blog_public_disabled' );
		} elseif ( '1' === get_option( 'blog_public' ) ) {
			$aioseop_notices->deactivate_notice( 'blog_public_disabled' );
		}
	}

	/**
	 * WooCommerce Upgrade Notice
	 *
	 * @since ?
	 * @since 3.0 Changed to AIOSEOP Notices.
	 */
	public function woo_upgrade_notice() {
		global $aioseop_notices;
		if ( class_exists( 'WooCommerce' ) && ! AIOSEOPPRO ) {
			$aioseop_notices->activate_notice( 'woocommerce_detected' );
		} else {
			global $aioseop_notices;
			$aioseop_notices->deactivate_notice( 'woocommerce_detected' );
		}
	}

	/**
	 * Make Unique Attachment Description
	 *
	 * @since ?
	 *
	 * @param $description
	 * @return string
	 */
	function make_unique_att_desc( $description ) {
		global $wp_query;
		if ( is_attachment() ) {

			$url         = $this->aiosp_mrt_get_url( $wp_query );
			$unique_desc = '';
			if ( $url ) {
				$matches = array();
				preg_match_all( '/(\d+)/', $url, $matches );
				if ( is_array( $matches ) ) {
					$unique_desc = join( '', $matches[0] );
				}
			}
			$description .= ' ' . $unique_desc;
		}

		return $description;
	}

	/**
	 * AMP Head
	 *
	 * Adds meta description to AMP pages.
	 *
	 * @todo Change void returns to empty string returns.
	 *
	 * @since 2.3.11.5
	 * @since 3.3.0 Fix loose comparator reading empty string in $description as false and returning. #2875
	 * @since 3.3.0 Add Schema to AMP. #506
	 *
	 * @return void
	 */
	function amp_head() {
		if ( ! $this->is_seo_enabled_for_cpt() ) {
			return;
		}

		$post = $this->get_queried_object();
		/**
		 * AIOSEOP AMP Description.
		 *
		 * To disable AMP meta description just __return_false on the aioseop_amp_description filter.
		 *
		 * @since ?
		 *
		 * @param string $post_description
		 */
		$description = apply_filters( 'aioseop_amp_description', $this->get_main_description( $post ) );    // Get the description.

		global $aioseop_options;

		// Handle the description format.
		if ( isset( $description ) && false !== $description && ( $this->strlen( $description ) > $this->minimum_description_length ) && ! ( is_front_page() && is_paged() ) ) {
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
			$desc_attr    = apply_filters( 'aioseop_amp_description_attributes', $desc_attr );
			$meta_string .= sprintf( "<meta name=\"description\" %s content=\"%s\" />\n", $desc_attr, $description );
		}
		if ( ! empty( $meta_string ) ) {
			echo $meta_string;
		}

		// Handle Schema.
		/**
		 * AIOSEOP AMP Schema Enable/Disable
		 *
		 * Allows or prevents the use of schema on AMP generated posts/pages. Use __return_false to disable.
		 *
		 * @since 3.3.0
		 *
		 * @param bool $var True to enable, and false to disable.
		 */
		$use_schema = apply_filters( 'aioseop_amp_schema', true );
		if ( $use_schema && ! empty( $aioseop_options['aiosp_schema_markup'] ) && (bool) $aioseop_options['aiosp_schema_markup'] ) {
			$aioseop_schema = new AIOSEOP_Schema_Builder();
			$aioseop_schema->display_json_ld_head_script();
		}
	}

	/**
	 * Is SEO Enabled for CPT
	 *
	 * Checks whether the current CPT should show the SEO tags.
	 *
	 * @since 2.9.0
	 *
	 * @todo Remove this as it is only a simple boolean check.
	 *
	 * @return bool
	 */
	private function is_seo_enabled_for_cpt() {
		global $aioseop_options;
		return empty( $post_type ) || in_array( get_post_type(), $aioseop_options['aiosp_cpostactive'], true );
	}

	/**
	 * Checks to see if Google Analytics should be excluded from the current page.
	 *
	 * Looks at both the individual post settings and the General Settings.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	function analytics_excluded() {

		$this->meta_opts = $this->get_current_options( array(), 'aiosp' ); // Get page-specific options.

		$aiosp_disable_analytics = false;

		if ( isset( $this->meta_opts['aiosp_disable_analytics'] ) ) {
			$aiosp_disable_analytics = $this->meta_opts['aiosp_disable_analytics'];
		}

		if ( $aiosp_disable_analytics || ! aioseop_option_isset( 'aiosp_google_analytics_id' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * WP Head
	 *
	 * @since ?
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

			$aioseop_robots_meta = new AIOSEOP_Robots_Meta();
			$robots_meta         = $aioseop_robots_meta->get_robots_meta_tag();

			if ( ! empty( $robots_meta ) ) {
				echo $robots_meta;
			}

			if ( ! empty( $old_wp_query ) ) {
				// Change the query back after we've finished.
				$GLOBALS['wp_query'] = $old_wp_query;
				unset( $old_wp_query );
			}

			if ( ! $this->analytics_excluded() ) {
				remove_action( 'aioseop_modules_wp_head', array( $this, 'aiosp_google_analytics' ) );
				add_action( 'wp_head', array( $this, 'aiosp_google_analytics' ) );
			}

			return;
		}

		if ( ! $this->is_seo_enabled_for_cpt() ) {
			return;
		}

		$opts = $this->meta_opts;
		global $aioseop_update_checker, $wp_query, $aioseop_options, $posts;
		static $aioseop_dup_counter = 0;
		$aioseop_dup_counter ++;

		if ( ! defined( 'AIOSEOP_UNIT_TESTING' ) && $aioseop_dup_counter > 1 ) {

			/* translators: %1$s, %2$s and %3$s are placeholders and should not be translated. %1$s expands to the name of the plugin, All in One SEO Pack, %2$s to the name of a filter function and %3$s is replaced with a number. */
			echo "\n<!-- " . sprintf( __( 'Debug Warning: %1$s meta data was included again from %2$s filter. Called %3$s times!', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME, current_filter(), $aioseop_dup_counter ) . " -->\n";
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

		/* translators: The complete string is: "All in One SEO Pack by Michael Torbert of Semper Fi Web Design". The placeholders shouldn't be altered; only the words "by" and "of" should be translated. */
		printf( "\n<!-- " . __( '%1$s by %2$s of %3$s', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME . ' ' . $this->version, 'Michael Torbert', 'Semper Fi Web Design' );

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
		if ( function_exists( 'wc_get_page_id' ) && is_post_type_archive( 'product' ) ) {
			$post_id = wc_get_page_id( 'shop' );
			if ( $post_id ) {
				$post = get_post( $post_id );

				global $posts;
				$opts            = $this->get_current_options( array(), 'aiosp', null, $post );
				$this->meta_opts = $this->get_current_options( array(), 'aiosp', null, $post );
				$posts           = array();
				$posts[]         = $post;
			}
		}

		$posts = $save_posts;
		// Handle the description format.
		// We are not going to mandate that post description needs to be present because the content could be derived from a custom field too.
		if ( ! ( is_front_page() && is_paged() ) ) {
			$description = $this->get_main_description( $post );    // Get the description.
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
			if ( ! empty( $description ) ) {
				$meta_string .= sprintf( "<meta name=\"description\" %s content=\"%s\" />\n", $desc_attr, $description );
			}
		}
		// Get the keywords.
		$togglekeywords = 0;
		if ( isset( $aioseop_options['aiosp_togglekeywords'] ) ) {
			$togglekeywords = $aioseop_options['aiosp_togglekeywords'];
		}
		if ( 0 == $togglekeywords && ! ( is_front_page() && is_paged() ) ) {
			$keywords = $this->get_main_keywords();
			$keywords = $this->apply_cf_fields( $keywords );
			$keywords = apply_filters( 'aioseop_keywords', $keywords );

			if ( isset( $keywords ) && ! empty( $keywords ) ) {
				if ( isset( $meta_string ) ) {
					$meta_string .= "\n";
				}
				$keywords     = wp_filter_nohtml_kses( str_replace( '"', '', $keywords ) );
				$key_attr     = apply_filters( 'aioseop_keywords_attributes', '' );
				$meta_string .= sprintf( "<meta name=\"keywords\" %s content=\"%s\" />\n", $key_attr, $keywords );
			}
		}

		$aioseop_robots_meta = new AIOSEOP_Robots_Meta();
		$robots_meta         = $aioseop_robots_meta->get_robots_meta_tag();

		if ( ! empty( $robots_meta ) ) {
			$meta_string .= $robots_meta;
		}

		// Handle site verification.
		if ( is_front_page() ) {
			foreach (
				array(
					'google'    => 'google-site-verification',
					'bing'      => 'msvalidate.01',
					'pinterest' => 'p:domain_verify',
					'yandex'    => 'yandex-verification',
					'baidu'     => 'baidu-site-verification',
				) as $k => $v
			) {
				if ( ! empty( $aioseop_options[ "aiosp_{$k}_verify" ] ) ) {
					$meta_string .= '<meta name="' . $v . '" content="' . trim( strip_tags( $aioseop_options[ "aiosp_{$k}_verify" ] ) ) . '" />' . "\n";
				}
			}
		}
		// Handle extra meta fields.
		foreach ( array( 'page_meta', 'post_meta', 'home_meta', 'front_meta' ) as $meta ) {
			if ( ! empty( $aioseop_options[ "aiosp_{$meta}_tags" ] ) ) {
				$$meta = html_entity_decode( stripslashes( $aioseop_options[ "aiosp_{$meta}_tags" ] ), ENT_QUOTES, 'UTF-8' );
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
			$meta_string .= '<link rel="prev" href="' . esc_url( $prev ) . "\" />\n";
		}
		if ( ! empty( $next ) ) {
			$meta_string .= '<link rel="next" href="' . esc_url( $next ) . "\" />\n";
		}
		if ( null != $meta_string ) {
			echo "$meta_string\n";
		}

		/**
		 * The aioseop_disable_schema filter hook.
		 *
		 * Used to disable schema.org output programatically.
		 *
		 * @since 3.2.8
		 *
		 * @return boolean
		 */
		if ( ! apply_filters( 'aioseop_disable_schema', false ) ) {
			// Handle Schema.
			if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
				if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) && boolval( $aioseop_options['aiosp_schema_markup'] ) ) { // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.boolvalFound
					$aioseop_schema = new AIOSEOP_Schema_Builder();
					$aioseop_schema->display_json_ld_head_script();
				}
			} else {
				if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) && (bool) $aioseop_options['aiosp_schema_markup'] ) {
					$aioseop_schema = new AIOSEOP_Schema_Builder();
					$aioseop_schema->display_json_ld_head_script();
				}
			}
		}

		// Handle canonical links.
		$show_page = true;
		if ( ! empty( $aioseop_options['aiosp_no_paged_canonical_links'] ) ) {
			$show_page = false;
		}

		if ( isset( $aioseop_options['aiosp_can'] ) && $aioseop_options['aiosp_can'] ) {
			$url = '';
			if ( ! empty( $opts['aiosp_custom_link'] ) && ! is_home() ) {
				$url = $opts['aiosp_custom_link'];
				if ( apply_filters( 'aioseop_canonical_url_pagination', $show_page ) ) {
					$url = $this->get_paged( $url );
				}
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
		echo sprintf( "<!-- %s -->\n", AIOSEOP_PLUGIN_NAME );

		if ( ! empty( $old_wp_query ) ) {
			// Change the query back after we've finished.
			$GLOBALS['wp_query'] = $old_wp_query;
			unset( $old_wp_query );
		}

	}
	/**
	 * Check Rewrite Handler
	 *
	 * @since ?
	 */
	function check_rewrite_handler() {
		global $aioseop_options;

		$force_rewrites = 1;
		if ( isset( $aioseop_options['aiosp_force_rewrites'] ) ) {
			$force_rewrites = $aioseop_options['aiosp_force_rewrites'];
		}

		if ( $force_rewrites ) {
			// Make the title rewrite as short as possible.
			if ( function_exists( 'ob_list_handlers' ) ) {
				$active_handlers = ob_list_handlers();
			} else {
				$active_handlers = array();
			}
			if (
					sizeof( $active_handlers ) > 0 &&
					$this->strtolower( $active_handlers[ sizeof( $active_handlers ) - 1 ] ) == $this->strtolower( 'All_in_One_SEO_Pack::output_callback_for_title' )
			) {
				ob_end_flush();
			} else {
				$this->log( 'another plugin interfering?' );
				// If we get here there *could* be trouble with another plugin :(.
				$this->ob_start_detected = true;

				// Try alternate method -- pdb.
				add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );

				if ( function_exists( 'ob_list_handlers' ) ) {
					foreach ( ob_list_handlers() as $handler ) {
						$this->log( "detected output handler $handler" );
					}
				}
			}
		}
	}

	/**
	 * Trim Description
	 *
	 * @since ?
	 *
	 * @param $description
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
	 * Apply Description Format
	 *
	 * @since ?
	 *
	 * @param $description
	 * @param null $post
	 * @return mixed
	 */
	function apply_description_format( $description, $post = null ) {

		/**
		 * Runs before applying the formatting for the meta description.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_before_apply_description_format' );

		global $aioseop_options;
		$description_format = $aioseop_options['aiosp_description_format'];
		if ( ! isset( $description_format ) || empty( $description_format ) ) {
			$description_format = '%description%';
		}
		$description = str_replace( '%description%', apply_filters( 'aioseop_description_override', $description ), $description_format );
		if ( false !== strpos( $description, '%site_title%', 0 ) ) {
			$description = str_replace( '%site_title%', get_bloginfo( 'name' ), $description );
		}
		if ( false !== strpos( $description, '%blog_title%', 0 ) ) {
			$description = str_replace( '%blog_title%', get_bloginfo( 'name' ), $description );
		}
		if ( false !== strpos( $description, '%site_description%', 0 ) ) {
			$description = str_replace( '%site_description%', get_bloginfo( 'description' ), $description );
		}
		if ( false !== strpos( $description, '%blog_description%', 0 ) ) {
			$description = str_replace( '%blog_description%', get_bloginfo( 'description' ), $description );
		}
		if ( false !== strpos( $description, '%wp_title%', 0 ) ) {
			$description = str_replace( '%wp_title%', $this->get_original_title(), $description );
		}
		if ( false !== strpos( $description, '%post_title%', 0 ) ) {
			$description = str_replace( '%post_title%', $this->get_aioseop_title( $post, false ), $description );
		}
		if ( false !== strpos( $description, '%current_date%', 0 ) ) {
			$description = str_replace( '%current_date%', date_i18n( get_option( 'date_format' ) ), $description );
		}
		if ( false !== strpos( $description, '%current_year%', 0 ) ) {
			$description = str_replace( '%current_year%', date( 'Y' ), $description );
		}
		if ( false !== strpos( $description, '%current_month%', 0 ) ) {
			$description = str_replace( '%current_month%', date( 'M' ), $description );
		}
		if ( false !== strpos( $description, '%current_month_i18n%', 0 ) ) {
			$description = str_replace( '%current_month_i18n%', date_i18n( 'M' ), $description );
		}
		if ( false !== strpos( $description, '%post_date%', 0 ) ) {
			$description = str_replace( '%post_date%', get_the_date(), $description );
		}
		if ( false !== strpos( $description, '%post_year%', 0 ) ) {
			$description = str_replace( '%post_year%', get_the_date( 'Y' ), $description );
		}
		if ( false !== strpos( $description, '%post_month%', 0 ) ) {
			$description = str_replace( '%post_month%', get_the_date( 'F' ), $description );
		}

		/*
		 * This was intended to make attachment descriptions unique if pulling from the parent... let's remove it and see if there are any problems
		 * on the roadmap is to have a better hierarchy for attachment description pulling
		 * if ($aioseop_options['aiosp_can']) $description = $this->make_unique_att_desc($description);
		 */
		$description = $this->apply_cf_fields( $description );

		/**
		 * Runs after applying the formatting for the meta description.
		 *
		 * @since 3.0
		 */
		do_action( 'aioseop_after_apply_description_format' );

		return esc_html( $description );
	}

	/**
	 * Check Singular
	 *
	 * Determine if the post is 'like' singular. In some specific instances, such as when the Reply post type of bbpress is loaded in its own page,
	 * it reflects as singular intead of single
	 *
	 * @since 2.4.2
	 *
	 * @return bool
	 */
	private function check_singular() {
		global $wp_query, $post;
		$is_singular = false;
		if ( is_singular() ) {
			// #1297 - support for bbpress 'reply' post type.
			if ( $post && 'reply' === $post->post_type ) {
				$is_singular = true;
			}
		}
		return $is_singular;
	}

	/**
	 * Get Previous/Next Links
	 *
	 * @since ?
	 *
	 * @param null $post
	 * @return array
	 */
	function get_prev_next_links( $post = null ) {
		$prev = '';
		$next = '';
		$page = aioseop_get_page_number();
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
		} elseif ( is_page() || is_single() ) {
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
			if ( false !== strpos( $content, '<!--nextpage-->', 0 ) ) {
				if ( $page > 1 ) {
					$more = 1;
				}
				$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
				$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
				// Ignore nextpage at the beginning of the content.
				if ( 0 === strpos( $content, '<!--nextpage-->', 0 ) ) {
					$content = substr( $content, 15 );
				}
				$pages    = explode( '<!--nextpage-->', $content );
				$numpages = count( $pages );
				if ( $numpages > 1 ) {
					$multipage = 1;
				}
			} else {
				$page = null;
			}
			if ( ! empty( $page ) ) {
				if ( $page > 1 ) {
					// Cannot use `wp_link_page()` since it is for rendering purposes and has no control over the page number.
					// TODO Investigate alternate wp concept. If none is found, keep private function in case of any future WP changes.
					$prev = _wp_link_page( $page - 1 );
				}
				if ( $page + 1 <= $numpages ) {
					// Cannot use `wp_link_page()` since it is for rendering purposes and has no control over the page number.
					// TODO Investigate alternate wp concept. If none is found, keep private function in case of any future WP changes.
					$next = _wp_link_page( $page + 1 );
				}
			}

			if ( ! empty( $prev ) ) {
				$dom = new DOMDocument();
				$dom->loadHTML( $prev );
				$prev = $dom->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'href' );
			}
			if ( ! empty( $next ) ) {
				$dom = new DOMDocument();
				$dom->loadHTML( $next );
				$next = $dom->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'href' );
			}
		}

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

	/**
	 * Validate URL Scheme
	 *
	 * Validates whether the url should be https or http.
	 *
	 * Mainly we're just using this for canonical URLS, but eventually it may be useful for other things
	 *
	 * @since 2.3.5
	 * @since 2.3.11 Removed check for legacy protocol setting. Added filter.
	 *
	 * @param $url
	 * @return string $url
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
	 * Google Analytics
	 *
	 * @since ?
	 * @since 3.3.0 Added support for Google Analytics.
	 *
	 * @param $options
	 * @param $location
	 * @param $settings
	 * @return mixed
	 */
	function aiosp_google_analytics() {
		if ( AIOSEOPPRO ) {
			new AIOSEOP_Pro_Google_Tag_Manager;
		}
		new aioseop_google_analytics;
	}

	/**
	 * Save Post Data
	 *
	 * @since ?
	 *
	 * @param $id
	 * @return bool
	 */
	function save_post_data( $id ) {
		$awmp_edit = null;
		$nonce     = null;
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
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) ) {
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
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) ) {
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
	 * Display Tabbed Metabox
	 *
	 * @since ?
	 *
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
	 * Get Metabox Header
	 *
	 * @since ?
	 *
	 * @param $tabs
	 *
	 * @return string
	 */
	function get_metabox_header( $tabs ) {
		$header = '<ul class="aioseop_header_tabs hide">';
		$active = ' active';
		foreach ( $tabs as $t ) {
			if ( $active ) {
				/* translators: This is the name of the main tab of the All in One SEO Pack meta box that appears on the Edit screen. */
				$title = __( 'Main Settings', 'all-in-one-seo-pack' );
			} else {
				$title = $t['title'];
			}
			$header .= '<li><label class="aioseop_header_nav"><a class="aioseop_header_tab' . $active . '" href="#' . $t['id'] . '">' . $title . '</a></label></li>';
			$active  = '';
		}
		$header .= '</ul>';

		return $header;
	}

	/**
	 * Admin Bar Menu
	 *
	 * @since ?
	 */
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

		if ( ( ! isset( $aioseop_options['aiosp_admin_bar'] ) && 'off' !== $toggle ) || ( ! empty( $aioseop_options['aiosp_admin_bar'] ) && 'off' !== $toggle ) || isset( $_POST['aiosp_admin_bar'] ) || true == apply_filters( 'aioseo_show_in_admin_bar', false ) ) {

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

			$wp_admin_bar->add_menu(
				array(
					'id'    => AIOSEOP_PLUGIN_DIRNAME,
					'title' => __( 'SEO', 'all-in-one-seo-pack' ),
					'href'  => $url,
				)
			);

			if ( current_user_can( 'update_plugins' ) && ! AIOSEOPPRO ) {
				$wp_admin_bar->add_menu(
					array(
						'parent' => AIOSEOP_PLUGIN_DIRNAME,
						/* translators: This is a CTA action link to upgrade to the premium version of the plugin. */
						'title'  => __( 'Upgrade To Pro', 'all-in-one-seo-pack' ),
						'id'     => 'aioseop-pro-upgrade',
						'href'   => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=menu',
						'meta'   => array( 'target' => '_blank' ),
					)
				);
				// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
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
							$wp_admin_bar->add_menu(
								array(
									'id'     => 'aiosp_edit_' . $current_object->ID,
									'parent' => AIOSEOP_PLUGIN_DIRNAME,
									'title'  => 'Edit SEO',
									'href'   => $edit_post_link . '#aiosp',
								)
							);
						} else {
							// Try the post object.
							$wp_admin_bar->add_menu(
								array(
									'id'     => 'aiosp_edit_' . $post->ID,
									'parent' => AIOSEOP_PLUGIN_DIRNAME,
									'title'  => __( 'Edit SEO', 'all-in-one-seo-pack' ),
									'href'   => get_edit_post_link( $post->ID ) . '#aiosp',
								)
							);
						}
					}

					if ( AIOSEOPPRO && ( is_category() || is_tax() || is_tag() ) ) {
						// SEO for taxonomies are only available in Pro version.
						$edit_term_link = get_edit_term_link( $current_object->term_id, $current_object->taxonomy );
						$wp_admin_bar->add_menu(
							array(
								'id'     => 'aiosp_edit_' . $post->ID,
								'parent' => AIOSEOP_PLUGIN_DIRNAME,
								'title'  => __( 'Edit SEO', 'all-in-one-seo-pack' ),
								'href'   => $edit_term_link . '#aiosp',
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Menu Order
	 *
	 * @since ?
	 *
	 * Order for adding the menus for the aioseop_modules_add_menus hook.
	 */
	function menu_order() {
		return 5;
	}

	/**
	 * Display Category Metaboxes
	 *
	 * @since ?
	 *
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
	 * Save Category Metaboxes
	 *
	 * @since ?
	 *
	 * @param $id
	 */
	function save_category_metaboxes( $id ) {
		$awmp_edit = null;
		$nonce     = null;
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
			if ( ! ( ! empty( $this->options['aiosp_can'] ) ) ) {
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

	/**
	 * Admin Menu
	 *
	 * @since ?
	 */
	function admin_menu() {
		$file      = plugin_basename( __FILE__ );
		$menu_name = 'All in One SEO';

		$this->locations['aiosp']['default_options']['nonce-aioseop-edit']['default'] = wp_create_nonce( 'edit-aioseop-nonce' );

		$custom_menu_order = false;
		global $aioseop_options;
		if ( ! isset( $aioseop_options['custom_menu_order'] ) ) {
			$custom_menu_order = true;
		}

		$this->update_options();

		/*
		For now we're removing admin pointers.
		$this->add_admin_pointers();
		if ( ! empty( $this->pointers ) ) {
			foreach ( $this->pointers as $k => $p ) {
				if ( ! empty( $p['pointer_scope'] ) && ( $p['pointer_scope'] == 'global' ) ) {
					unset( $this->pointers[ $k ] );
				}
			}
		}
		*/

		if ( isset( $_POST ) && isset( $_POST['module'] ) && isset( $_POST['nonce-aioseop'] ) && ( 'All_in_One_SEO_Pack' == $_POST['module'] ) && wp_verify_nonce( $_POST['nonce-aioseop'], 'aioseop-nonce' ) ) {
			if ( isset( $_POST['Submit'] ) && AIOSEOPPRO ) {
				if ( isset( $_POST['aiosp_custom_menu_order'] ) ) {
					$custom_menu_order = $_POST['aiosp_custom_menu_order'];
				} else {
					$custom_menu_order = false;
				}
			} elseif ( isset( $_POST['Submit_Default'] ) || isset( $_POST['Submit_All_Default'] ) ) {
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
					if ( ! empty( $p['pointer_scope'] ) && ( 'global' == $p['pointer_scope'] ) ) {
						unset( $this->pointers[ $k ] );
					}
				}
			}

			$this->filter_pointers();
		}

		if ( AIOSEOPPRO ) {
			if ( is_array( $this->options['aiosp_cpostactive'] ) ) {
				$this->locations['aiosp']['display'] = $this->options['aiosp_cpostactive'];
			} else {
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

		add_menu_page(
			$menu_name,
			$menu_name,
			apply_filters( 'manage_aiosp', 'aiosp_manage_seo' ),
			$file,
			array( $this, 'display_settings_page' ),
			aioseop_get_menu_icon()
		);

		add_meta_box(
			'aioseop-list',
			__( 'Join Our Mailing List', 'all-in-one-seo-pack' ),
			array( 'aiosp_metaboxes', 'display_extra_metaboxes' ),
			'aioseop_metaboxes',
			'normal',
			'core'
		);
		if ( AIOSEOPPRO ) {
			add_meta_box(
				'aioseop-about',
				__( 'About', 'all-in-one-seo-pack' ),
				array( 'aiosp_metaboxes', 'display_extra_metaboxes' ),
				'aioseop_metaboxes',
				'side',
				'core'
			);
		} else {
			add_meta_box(
				'aioseop-about',
				__( 'About', 'all-in-one-seo-pack' ) . "<span class='Taha' style='float:right;'>" . __( 'Version', 'all-in-one-seo-pack' ) . ' <b>' . AIOSEOP_VERSION . '</b></span>',
				array( 'aiosp_metaboxes', 'display_extra_metaboxes' ),
				'aioseop_metaboxes',
				'side',
				'core'
			);
		}
		add_meta_box(
			'aioseop-support',
			__( 'Support', 'all-in-one-seo-pack' ) . " <span  class='Taha' style='float:right;'>" . __( 'Version', 'all-in-one-seo-pack' ) . ' <b>' . AIOSEOP_VERSION . '</b></span>',
			array( 'aiosp_metaboxes', 'display_extra_metaboxes' ),
			'aioseop_metaboxes',
			'side',
			'core'
		);

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
							add_meta_box(
								$m[0]['id'] . '_tabbed',
								$title,
								array( $this, 'display_tabbed_metabox' ),
								$m[0]['post_type'],
								$m[0]['context'],
								$m[0]['priority'],
								$m
							);
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
	 * Set Menu Order
	 *
	 * @since ?
	 *
	 * @param $menu_order
	 * @return array
	 */
	function set_menu_order( $menu_order ) {
		$order = array();
		$file  = plugin_basename( __FILE__ );
		foreach ( $menu_order as $index => $item ) {
			if ( $item != $file ) {
				$order[] = $item;
			}
			if ( 0 == $index ) {
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
		// Decode entities.
		$value = $this->html_entity_decode( $value );
		// Encode to valid SEO html entities.
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
	 * @since 2.4 #951 Reorders filters/encodings/decondings applied and adds additional param.
	 *
	 * @param string $value    Value to filter.
	 * @param bool   $truncate Flag that indicates if value should be truncated/cropped.
	 * @param bool   $ignore_php_version Flag that indicates if the php version check should be ignored.
	 *
	 * @return string
	 */
	public function filter_description( $value, $truncate = false, $ignore_php_version = false ) {
		// TODO: change preg_match to version_compare someday when the reason for this condition is understood better.
		if ( $ignore_php_version || preg_match( '/5.2[\s\S]+/', PHP_VERSION ) ) {
			$value = htmlspecialchars( wp_strip_all_tags( htmlspecialchars_decode( $value ) ), ENT_COMPAT, 'UTF-8' );
		}
		// Decode entities.
		$value = $this->html_entity_decode( $value );
		$value = preg_replace(
			array(
				'#<a.*?>([^>]*)</a>#i', // Remove link but keep anchor text.
				'@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', // Remove URLs.
			),
			array(
				'$1', // Replacement link's anchor text.
				'', // Replacement URLs.
			),
			$value
		);
		// Strip html.
		$value = wp_strip_all_tags( $value );
		// External trim.
		$value = trim( $value );
		// Internal whitespace trim.
		$value = preg_replace( '/\s\s+/u', ' ', $value );
		// Truncate / crop.
		if ( ! empty( $truncate ) && $truncate ) {
			$value = $this->trim_excerpt_without_filters( $value );
		}
		// Encode to valid SEO html entities.
		return $this->seo_entity_encode( $value );
	}

	/**
	 * Returns string with decoded html entities.
	 * - Custom html_entity_decode supported on PHP 5.2
	 *
	 * @since 2.3.14
	 * @since 2.3.14.2 Hot fix on apostrophes.
	 * @since 2.3.16   &#039; Added to the list of apostrophes.
	 *
	 * @param string $value Value to decode.
	 *
	 * @return string
	 */
	private function html_entity_decode( $value ) {
		// Special conversions.
		$value = preg_replace(
			array(
				// Double quotes.
				'/\“|\”|&#[xX]00022;|&#34;|&[lLrRbB](dquo|DQUO)(?:[rR])?;|&#[xX]0201[dDeE];'
					. '|&[OoCc](pen|lose)[Cc]urly[Dd]ouble[Qq]uote;|&#822[012];|&#[xX]27;/',
				// Apostrophes.
				'/&#039;|&#8217;|&apos;/',
			),
			array(
				// Double quotes.
				'"',
				// Apostrophes.
				'\'',
			),
			$value
		);
		return html_entity_decode( $value, ENT_COMPAT, 'UTF-8' );
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
				'/\"|\“|\”|\„/', // Double quotes.
				'/\'|\’|\‘/',   // Apostrophes.
			),
			array(
				'&quot;', // Double quotes.
				'&#039;', // Apostrophes.
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

		<div class="aioseop_right_sidebar aioseop_options_wrapper">

			<div class="aioseop_sidebar">
				<?php
				do_meta_boxes( 'aioseop_metaboxes', 'side', array( 'test' ) );
				?>
				<script>
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
							<a
								href="https://www.wincher.com/?referer=all-in-one-seo-pack&adreferer=banner<?php echo $adid; ?>"
								target="_blank" aria-label="<?php _e( 'Banner advert for free Wincher rank checker plugin', 'all-in-one-seo-pack' ); ?>">
								<div class=wincherad id=wincher<?php echo $adid; ?>>
								</div>
							</a>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Checks which module(s) have been (de)activated just now and fires a corresponding action.
	 */
	function check_recently_activated_modules() {
		global $aioseop_options;
		$options        = get_option( 'aioseop_options', array() );
		$modules_before = array();
		$modules_now    = array();
		if ( array_key_exists( 'modules', $aioseop_options ) && array_key_exists( 'aiosp_feature_manager_options', $aioseop_options['modules'] ) ) {
			foreach ( $aioseop_options['modules']['aiosp_feature_manager_options'] as $module => $state ) {
				if ( ! empty( $state ) ) {
					$modules_before[] = $module;
				}
			}
		}
		if ( array_key_exists( 'modules', $options ) && array_key_exists( 'aiosp_feature_manager_options', $options['modules'] ) ) {
			foreach ( $options['modules']['aiosp_feature_manager_options'] as $module => $state ) {
				if ( ! empty( $state ) ) {
					$modules_now[] = $module;
				}
			}
		}

		$action = 'deactivate';
		$diff   = array_diff( $modules_before, $modules_now );
		if ( count( $modules_now ) > count( $modules_before ) ) {
			$action = 'activate';
			$diff   = array_diff( $modules_now, $modules_before );
		}

		if ( $diff ) {
			foreach ( $diff as $module ) {
				$name = str_replace( 'aiosp_feature_manager_enable_', '', $module );
				do_action( $this->prefix . $action . '_' . $name );
			}
		}
	}
}
