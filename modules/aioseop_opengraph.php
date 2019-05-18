<?php
/**
 * The Opengraph class.
 *
 * @package All-in-One-SEO-Pack
 * @version 2.3.16
 */
if ( ! class_exists( 'All_in_One_SEO_Pack_Opengraph' ) ) {
	class All_in_One_SEO_Pack_Opengraph extends All_in_One_SEO_Pack_Module {
		var $fb_object_types;
		var $type;

		/**
		 * Module constructor.
		 *
		 * @since 2.3.14 Added display filter.
		 * @since 2.3.16 #1066 Force init on constructor.
		 */
		function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'og_admin_enqueue_scripts' ) );

			$this->name            = __( 'Social Meta', 'all-in-one-seo-pack' );    // Human-readable name of the plugin
			$this->prefix          = 'aiosp_opengraph_';                        // option prefix
			$this->file            = __FILE__;                                    // the current file
			$this->fb_object_types = array(
				'Activities'                 => array(
					'activity' => __( 'Activity', 'all-in-one-seo-pack' ),
					'sport'    => __( 'Sport', 'all-in-one-seo-pack' ),
				),
				'Businesses'                 => array(
					'bar'        => __( 'Bar', 'all-in-one-seo-pack' ),
					'company'    => __( 'Company', 'all-in-one-seo-pack' ),
					'cafe'       => __( 'Cafe', 'all-in-one-seo-pack' ),
					'hotel'      => __( 'Hotel', 'all-in-one-seo-pack' ),
					'restaurant' => __( 'Restaurant', 'all-in-one-seo-pack' ),
				),
				'Groups'                     => array(
					'cause'         => __( 'Cause', 'all-in-one-seo-pack' ),
					'sports_league' => __( 'Sports League', 'all-in-one-seo-pack' ),
					'sports_team'   => __( 'Sports Team', 'all-in-one-seo-pack' ),
				),
				'Organizations'              => array(
					'band'       => __( 'Band', 'all-in-one-seo-pack' ),
					'government' => __( 'Government', 'all-in-one-seo-pack' ),
					'non_profit' => __( 'Non Profit', 'all-in-one-seo-pack' ),
					'school'     => __( 'School', 'all-in-one-seo-pack' ),
					'university' => __( 'University', 'all-in-one-seo-pack' ),
				),
				'People'                     => array(
					'actor'         => __( 'Actor', 'all-in-one-seo-pack' ),
					'athlete'       => __( 'Athlete', 'all-in-one-seo-pack' ),
					'author'        => __( 'Author', 'all-in-one-seo-pack' ),
					'director'      => __( 'Director', 'all-in-one-seo-pack' ),
					'musician'      => __( 'Musician', 'all-in-one-seo-pack' ),
					'politician'    => __( 'Politician', 'all-in-one-seo-pack' ),
					'profile'       => __( 'Profile', 'all-in-one-seo-pack' ),
					'public_figure' => __( 'Public Figure', 'all-in-one-seo-pack' ),
				),
				'Places'                     => array(
					'city'           => __( 'City', 'all-in-one-seo-pack' ),
					'country'        => __( 'Country', 'all-in-one-seo-pack' ),
					'landmark'       => __( 'Landmark', 'all-in-one-seo-pack' ),
					'state_province' => __( 'State Province', 'all-in-one-seo-pack' ),
				),
				'Products and Entertainment' => array(
					'album'   => __( 'Album', 'all-in-one-seo-pack' ),
					'book'    => __( 'Book', 'all-in-one-seo-pack' ),
					'drink'   => __( 'Drink', 'all-in-one-seo-pack' ),
					'food'    => __( 'Food', 'all-in-one-seo-pack' ),
					'game'    => __( 'Game', 'all-in-one-seo-pack' ),
					'movie'   => __( 'Movie', 'all-in-one-seo-pack' ),
					'product' => __( 'Product', 'all-in-one-seo-pack' ),
					'song'    => __( 'Song', 'all-in-one-seo-pack' ),
					'tv_show' => __( 'TV Show', 'all-in-one-seo-pack' ),
					'episode' => __( 'Episode', 'all-in-one-seo-pack' ),
				),
				'Websites'                   => array(
					'article' => __( 'Article', 'all-in-one-seo-pack' ),
					'blog'    => __( 'Blog', 'all-in-one-seo-pack' ),
					'website' => __( 'Website', 'all-in-one-seo-pack' ),
				),
			);
			parent::__construct();

			$this->help_text = array(
				'setmeta'                => __( 'Checking this box will use the Home Title and Home Description set in All in One SEO Pack, General Settings as the Open Graph title and description for your home page.', 'all-in-one-seo-pack' ),
				'key'                    => __( 'Enter your Facebook Admin ID here. You can enter multiple IDs separated by a comma. You can look up your Facebook ID using this tool http://findmyfbid.com/', 'all-in-one-seo-pack' ),
				'appid'                  => __( 'Enter your Facebook App ID here. Information about how to get your Facebook App ID can be found at https://developers.facebook.com/docs/apps/register', 'all-in-one-seo-pack' ),
				'title_shortcodes'       => __( 'Run shortcodes that appear in social title meta tags.', 'all-in-one-seo-pack' ),
				'description_shortcodes' => __( 'Run shortcodes that appear in social description meta tags.', 'all-in-one-seo-pack' ),
				'sitename'               => __( 'The Site Name is the name that is used to identify your website.', 'all-in-one-seo-pack' ),
				'hometitle'              => __( 'The Home Title is the Open Graph title for your home page.', 'all-in-one-seo-pack' ),
				'description'            => __( 'The Home Description is the Open Graph description for your home page.', 'all-in-one-seo-pack' ),
				'homeimage'              => __( 'The Home Image is the Open Graph image for your home page.', 'all-in-one-seo-pack' ),
				'generate_descriptions'  => __( 'This option will auto generate your Open Graph descriptions from your post content instead of your post excerpt. WooCommerce users should read the documentation regarding this setting.', 'all-in-one-seo-pack' ),
				'defimg'                 => __( 'This option lets you choose which image will be displayed by default for the Open Graph image. You may override this on individual posts.', 'all-in-one-seo-pack' ),
				'fallback'               => __( 'This option lets you fall back to the default image if no image could be found above.', 'all-in-one-seo-pack' ),
				'dimg'                   => __( 'This option sets a default image that can be used for the Open Graph image. You can upload an image, select an image from your Media Library or paste the URL of an image here.', 'all-in-one-seo-pack' ),
				'dimgwidth'              => __( 'This option lets you set a default width for your images, where unspecified.', 'all-in-one-seo-pack' ),
				'dimgheight'             => __( 'This option lets you set a default height for your images, where unspecified.', 'all-in-one-seo-pack' ),
				'meta_key'               => __( 'Enter the name of a custom field (or multiple field names separated by commas) to use that field to specify the Open Graph image on Pages or Posts.', 'all-in-one-seo-pack' ),
				'image'                  => __( 'This option lets you select the Open Graph image that will be used for this Page or Post, overriding the default settings.', 'all-in-one-seo-pack' ),
				'customimg'              => __( 'This option lets you upload an image to use as the Open Graph image for this Page or Post.', 'all-in-one-seo-pack' ),
				'imagewidth'             => __( 'Enter the width for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
				'imageheight'            => __( 'Enter the height for your Open Graph image in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
				'video'                  => __( 'This option lets you specify a link to the Open Graph video used on this Page or Post.', 'all-in-one-seo-pack' ),
				'videowidth'             => __( 'Enter the width for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
				'videoheight'            => __( 'Enter the height for your Open Graph video in pixels (i.e. 600).', 'all-in-one-seo-pack' ),
				'defcard'                => __( 'Select the default type of Twitter Card to display.', 'all-in-one-seo-pack' ),
				'setcard'                => __( 'Select the Twitter Card type to use for this Page or Post, overriding the default setting.', 'all-in-one-seo-pack' ),
				'twitter_site'           => __( 'Enter the Twitter username associated with your website here.', 'all-in-one-seo-pack' ),
				'twitter_creator'        => __( 'Allows your authors to be identified by their Twitter usernames as content creators on the Twitter cards for their posts.', 'all-in-one-seo-pack' ),
				'twitter_domain'         => __( 'Enter the name of your website here.', 'all-in-one-seo-pack' ),
				'customimg_twitter'      => __( 'This option lets you upload an image to use as the Twitter image for this Page or Post.', 'all-in-one-seo-pack' ),
				'gen_tags'               => __( 'Automatically generate article tags for Facebook type article when not provided.', 'all-in-one-seo-pack' ),
				'gen_keywords'           => __( 'Use keywords in generated article tags.', 'all-in-one-seo-pack' ),
				'gen_categories'         => __( 'Use categories in generated article tags.', 'all-in-one-seo-pack' ),
				'gen_post_tags'          => __( 'Use post tags in generated article tags.', 'all-in-one-seo-pack' ),
				'types'                  => __( 'Select which Post Types you want to use All in One SEO Pack to set Open Graph meta values for.', 'all-in-one-seo-pack' ),
				'title'                  => __( 'This is the Open Graph title of this Page or Post.', 'all-in-one-seo-pack' ),
				'desc'                   => __( 'This is the Open Graph description of this Page or Post.', 'all-in-one-seo-pack' ),
				'category'               => __( 'Select the Open Graph type that best describes the content of this Page or Post.', 'all-in-one-seo-pack' ),
				'facebook_debug'         => __( 'Press this button to have Facebook re-fetch and debug this page.', 'all-in-one-seo-pack' ),
				'section'                => __( 'This Open Graph meta allows you to add a general section name that best describes this content.', 'all-in-one-seo-pack' ),
				'tag'                    => __( 'This Open Graph meta allows you to add a list of keywords that best describe this content.', 'all-in-one-seo-pack' ),
				'facebook_publisher'     => __( 'Link articles to the Facebook page associated with your website.', 'all-in-one-seo-pack' ),
				'facebook_author'        => __( 'Allows your authors to be identified by their Facebook pages as content authors on the Opengraph meta for their articles.', 'all-in-one-seo-pack' ),
				'person_or_org'          => __( 'Are the social profile links for your website for a person or an organization?', 'all-in-one-seo-pack' ),
				'profile_links'          => __( "Add URLs for your website's social profiles here (Facebook, Twitter, Google+, Instagram, LinkedIn), one per line.", 'all-in-one-seo-pack' ),
				'social_name'            => __( 'Add the name of the person or organization who owns these profiles.', 'all-in-one-seo-pack' ),
			);

			$this->help_anchors = array(
				'title_shortcodes'       => '#run-shortcodes-in-title',
				'description_shortcodes' => '#run-shortcodes-in-description',
				'generate_descriptions'  => '#auto-generate-og-descriptions',
				'setmeta'                => '#use-aioseo-title-and-description',
				'sitename'               => '#site-name',
				'hometitle'              => '#home-title-and-description',
				'description'            => '#home-title-and-description',
				'homeimage'              => '#home-image',
				'defimg'                 => '#select-og-image-source',
				'fallback'               => '#use-default-if-no-image-found',
				'dimg'                   => '#default-og-image',
				'dimgwidth'              => '#default-image-width',
				'dimgheight'             => '#default-image-height',
				'meta_key'               => '#use-custom-field-for-image',
				'profile_links'          => '#social-profile-links',
				'person_or_org'          => '#social-profile-links',
				'social_name'            => '#social-profile-links',
				'key'                    => '#facebook-admin-id',
				'appid'                  => '#facebook-app-id',
				'gen_tags'               => '#automatically-generate-article-tags',
				'gen_keywords'           => '#use-keywords-in-article-tags',
				'gen_categories'         => '#use-categories-in-article-tags',
				'gen_post_tags'          => '#use-post-tags-in-article-tags',
				'facebook_publisher'     => '#show-facebook-publisher-on-articles',
				'facebook_author'        => '#show-facebook-author-on-articles',
				'types'                  => '#enable-facebook-meta-for',
				'defcard'                => '#default-twitter-card',
				'twitter_site'           => '#twitter-site',
				'twitter_creator'        => '#show-twitter-author',
				'twitter_domain'         => '#twitter-domain',
				'scan_header'            => '#scan-social-meta',
				'title'                  => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#title',
				'desc'                   => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#description',
				'image'                  => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#image',
				'customimg'              => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-image',
				'imagewidth'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
				'imageheight'            => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-image-width-height',
				'video'                  => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-video',
				'videowidth'             => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
				'videoheight'            => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#specify-video-width-height',
				'category'               => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-object-type',
				'facebook_debug'         => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#facebook-debug',
				'section'                => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-section',
				'tag'                    => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#article-tags',
				'setcard'                => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#twitter-card-type',
				'customimg_twitter'      => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/#custom-twitter-image',
			);

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ), 5 );
			} else {
				add_action( 'wp', array( $this, 'type_setup' ) );
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->do_opengraph();
			}
			// Set variables after WordPress load.
			add_action( 'init', array( &$this, 'init' ), 999999 );
			add_filter( 'jetpack_enable_open_graph', '__return_false' ); // Avoid having duplicate meta tags
			// Force refresh of Facebook cache.
			add_action( 'post_updated', array( &$this, 'force_fb_refresh_update' ), 10, 3 );
			add_action( 'transition_post_status', array( &$this, 'force_fb_refresh_transition' ), 10, 3 );
			add_action( 'edited_term', array( &$this, 'save_tax_data' ), 10, 3 );
			// Adds special filters
			add_filter( 'aioseop_opengraph_placeholder', array( &$this, 'filter_placeholder' ) );
			// Call to init to generate menus
			$this->init();
		}

		/**
		 * Hook called after WordPress has been loaded.
		 *
		 * @since 2.4.14
		 */
		public function init() {
			$count_desc = __( ' characters. Open Graph allows up to a maximum of %1$s chars for the %2$s.', 'all-in-one-seo-pack' );
			// Create default options
			$this->default_options = array(
				'scan_header'            => array(
					'name' => __( 'Scan Header', 'all-in-one-seo-pack' ),
					'type' => 'custom',
					'save' => true,
				),
				'setmeta'                => array(
					'name' => __( 'Use AIOSEO Title and Description', 'all-in-one-seo-pack' ),
					'type' => 'checkbox',
				),
				'key'                    => array(
					'name'    => __( 'Facebook Admin ID', 'all-in-one-seo-pack' ),
					'default' => '',
					'type'    => 'text',
				),
				'appid'                  => array(
					'name'    => __( 'Facebook App ID', 'all-in-one-seo-pack' ),
					'default' => '',
					'type'    => 'text',
				),
				'title_shortcodes'       => array(
					'name' => __( 'Run Shortcodes In Title', 'all-in-one-seo-pack' ),
				),
				'description_shortcodes' => array(
					'name' => __( 'Run Shortcodes In Description', 'all-in-one-seo-pack' ),
				),
				'sitename'               => array(
					'name'    => __( 'Site Name', 'all-in-one-seo-pack' ),
					'default' => get_bloginfo( 'name' ),
					'type'    => 'text',
				),
				'hometitle'              => array(
					'name'     => __( 'Home Title', 'all-in-one-seo-pack' ),
					'default'  => '',
					'type'     => 'textarea',
					'condshow' => array(
						'aiosp_opengraph_setmeta' => array(
							'lhs' => 'aiosp_opengraph_setmeta',
							'op'  => '!=',
							'rhs' => 'on',
						),
					),
				),
				'description'            => array(
					'name'     => __( 'Home Description', 'all-in-one-seo-pack' ),
					'default'  => '',
					'type'     => 'textarea',
					'condshow' => array(
						'aiosp_opengraph_setmeta' => array(
							'lhs' => 'aiosp_opengraph_setmeta',
							'op'  => '!=',
							'rhs' => 'on',
						),
					),
				),
				'homeimage'              => array(
					'name' => __( 'Home Image', 'all-in-one-seo-pack' ),
					'type' => 'image',
				),
				'generate_descriptions'  => array(
					'name'    => __( 'Use Content For Autogenerated OG Descriptions', 'all-in-one-seo-pack' ),
					'default' => 0,
				),
				'defimg'                 => array(
					'name'            => __( 'Select OG:Image Source', 'all-in-one-seo-pack' ),
					'type'            => 'select',
					'initial_options' => array(
						''         => __( 'Default Image' ),
						'featured' => __( 'Featured Image' ),
						'attach'   => __( 'First Attached Image' ),
						'content'  => __( 'First Image In Content' ),
						'custom'   => __( 'Image From Custom Field' ),
						'author'   => __( 'Post Author Image' ),
						'auto'     => __( 'First Available Image' ),
					),
				),
				'fallback'               => array(
					'name' => __( 'Use Default If No Image Found', 'all-in-one-seo-pack' ),
					'type' => 'checkbox',
				),
				'dimg'                   => array(
					'name'    => __( 'Default OG:Image', 'all-in-one-seo-pack' ),
					'default' => AIOSEOP_PLUGIN_IMAGES_URL . 'default-user-image.png',
					'type'    => 'image',
				),
				'dimgwidth'              => array(
					'name'    => __( 'Default Image Width', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'dimgheight'             => array(
					'name'    => __( 'Default Image Height', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'meta_key'               => array(
					'name'    => __( 'Use Custom Field For Image', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'image'                  => array(
					'name'            => __( 'Image', 'all-in-one-seo-pack' ),
					'type'            => 'radio',
					'initial_options' => array(
						0 => '<img style="width:50px;height:auto;display:inline-block;vertical-align:bottom;" src="' . AIOSEOP_PLUGIN_IMAGES_URL . 'default-user-image.png' . '">',
					),
				),
				'customimg'              => array(
					'name' => __( 'Custom Image', 'all-in-one-seo-pack' ),
					'type' => 'image',
				),
				'imagewidth'             => array(
					'name'    => __( 'Specify Image Width', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'imageheight'            => array(
					'name'    => __( 'Specify Image Height', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'video'                  => array(
					'name' => __( 'Custom Video', 'all-in-one-seo-pack' ),
					'type' => 'text',
				),
				'videowidth'             => array(
					'name'     => __( 'Specify Video Width', 'all-in-one-seo-pack' ),
					'type'     => 'text',
					'default'  => '',
					'condshow' => array(
						'aioseop_opengraph_settings_video' => array(
							'lhs' => 'aioseop_opengraph_settings_video',
							'op'  => '!=',
							'rhs' => '',
						),
					),
				),
				'videoheight'            => array(
					'name'     => __( 'Specify Video Height', 'all-in-one-seo-pack' ),
					'type'     => 'text',
					'default'  => '',
					'condshow' => array(
						'aioseop_opengraph_settings_video' => array(
							'lhs' => 'aioseop_opengraph_settings_video',
							'op'  => '!=',
							'rhs' => '',
						),
					),
				),
				'defcard'                => array(
					'name'            => __( 'Default Twitter Card', 'all-in-one-seo-pack' ),
					'type'            => 'select',
					'default'         => 'summary',
					'initial_options' => array(
						'summary'             => __( 'Summary', 'all-in-one-seo-pack' ),
						'summary_large_image' => __( 'Summary Large Image', 'all-in-one-seo-pack' ),

						/*
						 REMOVING THIS TWITTER CARD TYPE FROM SOCIAL META MODULE
						'photo' => __( 'Photo', 'all-in-one-seo-pack' )
						*/
					),
				),
				'setcard'                => array(
					'name'            => __( 'Twitter Card Type', 'all-in-one-seo-pack' ),
					'type'            => 'select',
					'initial_options' => array(
						'summary_large_image' => __( 'Summary Large Image', 'all-in-one-seo-pack' ),
						'summary'             => __( 'Summary', 'all-in-one-seo-pack' ),

						/*
						 REMOVING THIS TWITTER CARD TYPE FROM SOCIAL META MODULE
						'photo' => __( 'Photo', 'all-in-one-seo-pack' )
						*/
					),
				),
				'twitter_site'           => array(
					'name'    => __( 'Twitter Site', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'twitter_creator'        => array(
					'name' => __( 'Show Twitter Author', 'all-in-one-seo-pack' ),
				),
				'twitter_domain'         => array(
					'name'    => __( 'Twitter Domain', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'customimg_twitter'      => array(
					'name' => __( 'Custom Twitter Image', 'all-in-one-seo-pack' ),
					'type' => 'image',
				),
				'gen_tags'               => array(
					'name' => __( 'Automatically Generate Article Tags', 'all-in-one-seo-pack' ),
				),
				'gen_keywords'           => array(
					'name'     => __( 'Use Keywords In Article Tags', 'all-in-one-seo-pack' ),
					'default'  => 'on',
					'condshow' => array( 'aiosp_opengraph_gen_tags' => 'on' ),
				),
				'gen_categories'         => array(
					'name'     => __( 'Use Categories In Article Tags', 'all-in-one-seo-pack' ),
					'default'  => 'on',
					'condshow' => array( 'aiosp_opengraph_gen_tags' => 'on' ),
				),
				'gen_post_tags'          => array(
					'name'     => __( 'Use Post Tags In Article Tags', 'all-in-one-seo-pack' ),
					'default'  => 'on',
					'condshow' => array( 'aiosp_opengraph_gen_tags' => 'on' ),
				),
				'types'                  => array(
					'name'            => __( 'Enable Facebook Meta for Post Types', 'all-in-one-seo-pack' ),
					'type'            => 'multicheckbox',
					'default'         => array( 'post' => 'post', 'page' => 'page' ),
					'initial_options' => $this->get_post_type_titles( array( '_builtin' => false ) ),
				),
				'title'                  => array(
					'name'       => __( 'Title', 'all-in-one-seo-pack' ),
					'default'    => '',
					'type'       => 'text',
					'size'       => 95,
					'count'      => 1,
					'count_desc' => $count_desc,
				),
				'desc'                   => array(
					'name'       => __( 'Description', 'all-in-one-seo-pack' ),
					'default'    => '',
					'type'       => 'textarea',
					'cols'       => 250,
					'rows'       => 4,
					'count'      => 1,
					'count_desc' => $count_desc,
				),
				'category'               => array(
					'name'            => __( 'Facebook Object Type', 'all-in-one-seo-pack' ),
					'type'            => 'select',
					'style'           => '',
					'default'         => '',
					'initial_options' => $this->fb_object_types,
				),
				'facebook_debug'         => array(
					'name'    => __( 'Facebook Debug', 'all-in-one-seo-pack' ),
					'type'    => 'html',
					'save'    => false,
					'default' => $this->get_facebook_debug(),
				),
				'section'                => array(
					'name'     => __( 'Article Section', 'all-in-one-seo-pack' ),
					'type'     => 'text',
					'default'  => '',
					'condshow' => array( 'aioseop_opengraph_settings_category' => 'article' ),
				),
				'tag'                    => array(
					'name'     => __( 'Article Tags', 'all-in-one-seo-pack' ),
					'type'     => 'text',
					'default'  => '',
					'condshow' => array( 'aioseop_opengraph_settings_category' => 'article' ),
				),
				'facebook_publisher'     => array(
					'name'    => __( 'Show Facebook Publisher on Articles', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
				'facebook_author'        => array(
					'name' => __( 'Show Facebook Author on Articles', 'all-in-one-seo-pack' ),
				),
				'profile_links'          => array(
					'name' => __( 'Social Profile Links', 'all-in-one-seo-pack' ),
					'type' => 'textarea',
					'cols' => 60,
					'rows' => 5,
				),
				'person_or_org'          => array(
					'name'            => __( 'Person or Organization?', 'all-in-one-seo-pack' ),
					'type'            => 'radio',
					'initial_options' => array(
						'person' => __( 'Person', 'all-in-one-seo-pack' ),
						'org'    => __( 'Organization', 'all-in-one-seo-pack' ),
					),
				),
				'social_name'            => array(
					'name'    => __( 'Associated Name', 'all-in-one-seo-pack' ),
					'type'    => 'text',
					'default' => '',
				),
			);
			// load initial options / set defaults
			$this->update_options();
			$display = array();
			if ( isset( $this->options['aiosp_opengraph_types'] ) && ! empty( $this->options['aiosp_opengraph_types'] ) ) {
				$display = $this->options['aiosp_opengraph_types'];
			}
			$this->locations = array(
				'opengraph' => array(
					'name'    => $this->name,
					'prefix'  => 'aiosp_',
					'type'    => 'settings',
					'options' => array(
						'scan_header',
						'setmeta',
						'key',
						'appid',
						'sitename',
						'title_shortcodes',
						'description_shortcodes',
						'hometitle',
						'description',
						'homeimage',
						'generate_descriptions',
						'defimg',
						'fallback',
						'dimg',
						'dimgwidth',
						'dimgheight',
						'meta_key',
						'defcard',
						'profile_links',
						'person_or_org',
						'social_name',
						'twitter_site',
						'twitter_creator',
						'twitter_domain',
						'gen_tags',
						'gen_keywords',
						'gen_categories',
						'gen_post_tags',
						'types',
						'facebook_publisher',
						'facebook_author',
					),
				),
				'settings'  => array(
					'name'      => __( 'Social Settings', 'all-in-one-seo-pack' ),
					'type'      => 'metabox',
					'help_link' => 'https://semperplugins.com/documentation/social-meta-settings-individual-pagepost-settings/',
					'options'   => array(
						'title',
						'desc',
						'image',
						'customimg',
						'imagewidth',
						'imageheight',
						'video',
						'videowidth',
						'videoheight',
						'category',
						'facebook_debug',
						'section',
						'tag',
						'setcard',
						'customimg_twitter',
					),
					'display'   => apply_filters( 'aioseop_opengraph_display', $display ),
					'prefix'    => 'aioseop_opengraph_',
				),
			);
			$this->layout    = array(
				'home'      => array(
					'name'      => __( 'Home Page Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#use-aioseo-title-and-description',
					'options'   => array( 'setmeta', 'sitename', 'hometitle', 'description', 'homeimage' ),
				),
				'image'     => array(
					'name'      => __( 'Image Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#select-og-image-source',
					'options'   => array( 'defimg', 'fallback', 'dimg', 'dimgwidth', 'dimgheight', 'meta_key' ),
				),
				'links'     => array(
					'name'      => __( 'Social Profile Links', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#social-profile-links',
					'options'   => array( 'profile_links', 'person_or_org', 'social_name' ),
				),
				'facebook'  => array(
					'name'      => __( 'Facebook Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#facebook-settings',
					'options'   => array(
						'key',
						'appid',
						'types',
						'gen_tags',
						'gen_keywords',
						'gen_categories',
						'gen_post_tags',
						'facebook_publisher',
						'facebook_author',
					),
				),
				'twitter'   => array(
					'name'      => __( 'Twitter Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#default-twitter-card',
					'options'   => array( 'defcard', 'setcard', 'twitter_site', 'twitter_creator', 'twitter_domain' ),
				),
				'default'   => array(
					'name'      => __( 'Advanced Settings', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/',
					'options'   => array(), // this is set below, to the remaining options -- pdb
				),
				'scan_meta' => array(
					'name'      => __( 'Scan Social Meta', 'all-in-one-seo-pack' ),
					'help_link' => 'https://semperplugins.com/documentation/social-meta-module/#scan_meta',
					'options'   => array( 'scan_header' ),
				),
			);
			$other_options   = array();
			foreach ( $this->layout as $k => $v ) {
				$other_options = array_merge( $other_options, $v['options'] );
			}

			$this->layout['default']['options'] = array_diff( array_keys( $this->default_options ), $other_options );
		}

		/**
		 * Forces FaceBook OpenGraph to refresh its cache when a post is changed to
		 *
		 * @param $new_status
		 * @param $old_status
		 * @param $post
		 *
		 * @todo  this and force_fb_refresh_update can probably have the remote POST extracted out.
		 *
		 * @see   https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
		 * @since 2.3.11
		 */
		function force_fb_refresh_transition( $new_status, $old_status, $post ) {
			if ( 'publish' !== $new_status ) {
				return;
			}
			if ( 'future' !== $old_status ) {
				return;
			}

			$current_post_type = get_post_type();

			// Only ping Facebook if Social SEO is enabled on this post type.
			if ( $this->option_isset( 'types' ) && is_array( $this->options['aiosp_opengraph_types'] ) && in_array( $current_post_type, $this->options['aiosp_opengraph_types'] ) ) {
				$post_url = aioseop_get_permalink( $post->ID );
				$endpoint = sprintf(
					'https://graph.facebook.com/?%s', http_build_query(
						array(
							'id'     => $post_url,
							'scrape' => true,
						)
					)
				);
				wp_remote_post( $endpoint, array( 'blocking' => false ) );
			}
		}

		/**
		 * Forces FaceBook OpenGraph refresh on update.
		 *
		 * @param $post_id
		 * @param $post_after
		 *
		 * @see   https://developers.facebook.com/docs/sharing/opengraph/using-objects#update
		 * @since 2.3.11
		 */
		function force_fb_refresh_update( $post_id, $post_after ) {

			$current_post_type = get_post_type();

			// Only ping Facebook if Social SEO is enabled on this post type.
			if ( 'publish' === $post_after->post_status && $this->option_isset( 'types' ) && is_array( $this->options['aiosp_opengraph_types'] ) && in_array( $current_post_type, $this->options['aiosp_opengraph_types'] ) ) {
				$post_url = aioseop_get_permalink( $post_id );
				$endpoint = sprintf(
					'https://graph.facebook.com/?%s', http_build_query(
						array(
							'id'     => $post_url,
							'scrape' => true,
						)
					)
				);
				wp_remote_post( $endpoint, array( 'blocking' => false ) );
			}
		}

		function settings_page_init() {
			add_filter( 'aiosp_output_option', array( $this, 'display_custom_options' ), 10, 2 );
		}

		function filter_options( $options, $location ) {
			if ( $location == 'settings' ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
				list( $legacy, $images ) = $this->get_all_images( $options );
				if ( isset( $options ) && isset( $options["{$prefix}image"] ) ) {
					$thumbnail = $options["{$prefix}image"];
					if ( ctype_digit( (string) $thumbnail ) || ( $thumbnail == 'post' ) ) {
						if ( $thumbnail == 'post' ) {
							$thumbnail = $images['post1'];
						} elseif ( ! empty( $legacy[ $thumbnail ] ) ) {
							$thumbnail = $legacy[ $thumbnail ];
						}
					}
					$options["{$prefix}image"] = $thumbnail;
				}
				if ( empty( $options[ $prefix . 'image' ] ) ) {
					$img = array_keys( $images );
					if ( ! empty( $img ) && ! empty( $img[1] ) ) {
						$options[ $prefix . 'image' ] = $img[1];
					}
				}
			}

			return $options;
		}

		/**
		 * Applies filter to module settings.
		 *
		 * @since 2.3.11
		 * @since 2.4.14 Added filter for description and title placeholders.
		 * @since 2.3.15 do_shortcode on description.
		 *
		 * @see   [plugin]\admin\aioseop_module_class.php > display_options()
		 */
		function filter_settings( $settings, $location, $current ) {
			global $aiosp, $post;
			if ( $location == 'opengraph' || $location == 'settings' ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
				if ( $location == 'opengraph' ) {
					return $settings;
				}
				if ( $location == 'settings' ) {
					list( $legacy, $settings[ $prefix . 'image' ]['initial_options'] ) = $this->get_all_images( $current );
					$opts              = array( 'title', 'desc' );
					$current_post_type = get_post_type();
					if ( isset( $this->options[ "aiosp_opengraph_{$current_post_type}_fb_object_type" ] ) ) {
						$flat_type_list = array();
						foreach ( $this->fb_object_types as $k => $v ) {
							if ( is_array( $v ) ) {
								$flat_type_list = array_merge( $flat_type_list, $v );
							} else {
								$flat_type_list[ $k ] = $v;
							}
						}
						$settings[ $prefix . 'category' ]['initial_options'] = array_merge(
							array(
								$this->options[ "aiosp_opengraph_{$current_post_type}_fb_object_type" ] => __( 'Default ', 'all-in-one-seo-pack' ) . ' - '
																										 . $flat_type_list[ $this->options[ "aiosp_opengraph_{$current_post_type}_fb_object_type" ] ],
							),
							$settings[ $prefix . 'category' ]['initial_options']
						);
					}
					if ( isset( $this->options['aiosp_opengraph_defcard'] ) ) {
						$settings[ $prefix . 'setcard' ]['default'] = $this->options['aiosp_opengraph_defcard'];
					}
					$info = $aiosp->get_page_snippet_info();
					$title = $info['title'];
					$description = $info['description'];

					// Description options
					if ( is_object( $post ) ) {
						// Always show excerpt
						$description = empty( $this->options['aiosp_opengraph_generate_descriptions'] )
							? $aiosp->trim_excerpt_without_filters(
								$aiosp->internationalize( preg_replace( '/\s+/', ' ', $post->post_excerpt ) ),
								1000
							)
							: $aiosp->trim_excerpt_without_filters(
								$aiosp->internationalize( preg_replace( '/\s+/', ' ', $post->post_content ) ),
								1000
							);
					}

					// #1308 - we want to make sure we are ignoring php version only in the admin area while editing the post, so that it does not impact #932.
					$screen = get_current_screen();
					$ignore_php_version = is_admin() && isset( $screen->id ) && 'post' == $screen->id;

					// Add filters
					$description = apply_filters( 'aioseop_description', $description, false, $ignore_php_version );
					// Add placholders
					$settings[ "{$prefix}title" ]['placeholder'] = apply_filters( 'aioseop_opengraph_placeholder', $title );
					$settings[ "{$prefix}desc" ]['placeholder']  = apply_filters( 'aioseop_opengraph_placeholder', $description );
				}
				if ( isset( $current[ $prefix . 'setmeta' ] ) && $current[ $prefix . 'setmeta' ] ) {
					foreach ( $opts as $opt ) {
						if ( isset( $settings[ $prefix . $opt ] ) ) {
							$settings[ $prefix . $opt ]['type']      = 'hidden';
							$settings[ $prefix . $opt ]['label']     = 'none';
							$settings[ $prefix . $opt ]['help_text'] = '';
							unset( $settings[ $prefix . $opt ]['count'] );
						}
					}
				}
			}

			return $settings;
		}

		/**
		 * Applies filter to module options.
		 * These will display in the "Social Settings" object tab.
		 * filter:{prefix}override_options
		 *
		 * @since 2.3.11
		 * @since 2.4.14 Overrides empty og:type values.
		 *
		 * @see [plugin]\admin\aioseop_module_class.php > display_options()
		 *
		 * @global array $aioseop_options Plugin options.
		 *
		 * @param array  $options  Current options.
		 * @param string $location Location where filter is called.
		 * @param array  $settings Settings.
		 *
		 * @return array
		 */
		function override_options( $options, $location, $settings ) {
			global $aioseop_options;
			// Prepare default and prefix
			$prefix = $this->get_prefix( $location ) . $location . '_';
			$opts = array();

			foreach ( $settings as $k => $v ) {
				if ( $v['save'] ) {
					$opts[ $k ] = $v['default'];
				}
			}
			foreach ( $options as $k => $v ) {
				switch ( $k ) {
					case $prefix . 'category':
						if ( empty( $v ) ) {
							// Get post type
							$type = isset( get_current_screen()->post_type )
								? get_current_screen()->post_type
								: null;
							// Assign default from plugin options
							if ( ! empty( $type )
								&& isset( $aioseop_options['modules'] )
								&& isset( $aioseop_options['modules']['aiosp_opengraph_options'] )
								&& isset( $aioseop_options['modules']['aiosp_opengraph_options'][ 'aiosp_opengraph_' . $type . '_fb_object_type' ] )
							) {
								$options[ $prefix . 'category' ] =
									$aioseop_options['modules']['aiosp_opengraph_options'][ 'aiosp_opengraph_' . $type . '_fb_object_type' ];
							}
							continue;
						}
						break;
				}
				if ( $v === null ) {
					unset( $options[ $k ] );
				}
			}
			$options = wp_parse_args( $options, $opts );

			return $options;
		}

		/**
		 * Applies filter to metabox settings before they are saved.
		 * Sets custom as default if a custom image is uploaded.
		 * filter:{prefix}filter_metabox_options
		 * filter:{prefix}filter_term_metabox_options
		 *
		 * @since 2.3.11
		 * @since 2.4.14 Fixes for aioseop-pro #67 and other bugs found.
		 *
		 * @see [plugin]\admin\aioseop_module_class.php > save_post_data()
		 * @see [this file] > save_tax_data()
		 *
		 * @param array  $options  List of current options.
		 * @param string $location Location where filter is called.
		 * @param int    $id       Either post_id or term_id.
		 *
		 * @return array
		 */
		function filter_metabox_options( $options, $location, $post_id ) {
			if ( $location == 'settings' ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
				if ( isset( $options[ $prefix . 'customimg_checker' ] )
					&& $options[ $prefix . 'customimg_checker' ]
				) {
					$options[ $prefix . 'image' ] = $options[ $prefix . 'customimg' ];
				}
			}
			return $options;
		}

		/** Custom settings **/
		function display_custom_options( $buf, $args ) {
			if ( $args['name'] == 'aiosp_opengraph_scan_header' ) {
				$buf .= '<div class="aioseop aioseop_options aiosp_opengraph_settings"><div class="aioseop_wrapper aioseop_custom_type" id="aiosp_opengraph_scan_header_wrapper"><div class="aioseop_input" id="aiosp_opengraph_scan_header" style="padding-left:20px;">';
				$args['options']['type'] = 'submit';
				$args['attr']            = " class='button-primary' ";
				$args['value']           = $args['options']['default'] = __( 'Scan Now', 'all-in-one-seo-pack' );
				$buf .= __( 'Scan your site for duplicate social meta tags.', 'all-in-one-seo-pack' );
				$buf .= '<br /><br />' . $this->get_option_html( $args );
				$buf .= '</div></div></div>';
			}

			return $buf;
		}

		function add_attributes( $output ) {
			// avoid having duplicate meta tags
			$type = $this->type;
			if ( empty( $type ) ) {
				$type = 'website';
			}

			$schema_types = array(
				'album'      => 'MusicAlbum',
				'article'    => 'Article',
				'bar'        => 'BarOrPub',
				'blog'       => 'Blog',
				'book'       => 'Book',
				'cafe'       => 'CafeOrCoffeeShop',
				'city'       => 'City',
				'country'    => 'Country',
				'episode'    => 'Episode',
				'food'       => 'FoodEvent',
				'game'       => 'Game',
				'hotel'      => 'Hotel',
				'landmark'   => 'LandmarksOrHistoricalBuildings',
				'movie'      => 'Movie',
				'product'    => 'Product',
				'profile'    => 'ProfilePage',
				'restaurant' => 'Restaurant',
				'school'     => 'School',
				'sport'      => 'SportsEvent',
				'website'    => 'WebSite',
			);

			if ( ! empty( $schema_types[ $type ] ) ) {
				$type = $schema_types[ $type ];
			} else {
				$type = 'WebSite';
			}

			$attributes = apply_filters(
				$this->prefix . 'attributes', array(
					'prefix="og: http://ogp.me/ns#"',
				)
			);

			foreach ( $attributes as $attr ) {
				if ( strpos( $output, $attr ) === false ) {
					$output .= "\n\t$attr ";
				}
			}

			return $output;
		}

		/**
		 * Add our social meta.
		 *
		 * @since 1.0.0
		 * @since 2.3.11.5 Support for multiple fb_admins.
		 * @since 2.3.13   Adds filter:aioseop_description on description.
		 * @since 2.4.14   Fixes for aioseop-pro #67.
		 * @since 2.3.15   Always do_shortcode on descriptions, removed for titles.
		 *
		 * @global object $post            Current WP_Post object.
		 * @global object $aiosp           All in one seo plugin object.
		 * @global array  $aioseop_options All in one seo plugin options.
		 * @global object $wp_query        WP_Query global instance.
		 */
		function add_meta() {
			global $post, $aiosp, $aioseop_options, $wp_query;
			$metabox           = $this->get_current_options( array(), 'settings' );
			$key               = $this->options['aiosp_opengraph_key'];
			$key               = $this->options['aiosp_opengraph_key'];
			$dimg              = $this->options['aiosp_opengraph_dimg'];
			$current_post_type = get_post_type();
			$title             = $description = $image = $video = '';
			$type              = $this->type;
			$sitename          = $this->options['aiosp_opengraph_sitename'];

			$appid = isset( $this->options['aiosp_opengraph_appid'] ) ? $this->options['aiosp_opengraph_appid'] : '';

			if ( ! empty( $aioseop_options['aiosp_hide_paginated_descriptions'] ) ) {
				$first_page = false;
				if ( $aiosp->get_page_number() < 2 ) {
					$first_page = true;
				}
			} else {
				$first_page = true;
			}
			$url = $aiosp->aiosp_mrt_get_url( $wp_query );
			$url = apply_filters( 'aioseop_canonical_url', $url );

			$setmeta      = $this->options['aiosp_opengraph_setmeta'];
			$social_links = '';
			if ( is_front_page() ) {
				$title = $this->options['aiosp_opengraph_hometitle'];
				if ( $first_page ) {
					$description = $this->options['aiosp_opengraph_description'];
					if ( empty( $description ) ) {
						$description = get_bloginfo( 'description' );
					}
				}
				if ( ! empty( $this->options['aiosp_opengraph_homeimage'] ) ) {
					$thumbnail = $this->options['aiosp_opengraph_homeimage'];
				} else {
					$thumbnail = $this->options['aiosp_opengraph_dimg'];
				}

				/* If Use AIOSEO Title and Desc Selected */
				if ( $setmeta ) {
					$title = $aiosp->wp_title();
					if ( $first_page ) {
						$description = $aiosp->get_aioseop_description( $post );
					}
				}

				/* Add some defaults */
				if ( empty( $title ) ) {
					$title = get_bloginfo( 'name' );
				}
				if ( empty( $sitename ) ) {
					$sitename = get_bloginfo( 'name' );
				}

				if ( empty( $description ) && $first_page && ! empty( $post ) && ! post_password_required( $post ) ) {

					if ( ! empty( $post->post_content ) || ! empty( $post->post_excerpt ) ) {
						$description = $aiosp->trim_excerpt_without_filters( $aiosp->internationalize( preg_replace( '/\s+/', ' ', $post->post_excerpt ) ), 1000 );

						if ( ! empty( $this->options['aiosp_opengraph_generate_descriptions'] ) ) {
							$description = $aiosp->trim_excerpt_without_filters( $aiosp->internationalize( preg_replace( '/\s+/', ' ', $post->post_content ) ), 1000 );
						}
					}
				}

				if ( empty( $description ) && $first_page ) {
					$description = get_bloginfo( 'description' );
				}
				if ( ! empty( $this->options['aiosp_opengraph_profile_links'] ) ) {
					$social_links = $this->options['aiosp_opengraph_profile_links'];
					if ( ! empty( $this->options['aiosp_opengraph_social_name'] ) ) {
						$social_name = $this->options['aiosp_opengraph_social_name'];
					} else {
						$social_name = '';
					}
					if ( $this->options['aiosp_opengraph_person_or_org'] == 'person' ) {
						$social_type = 'Person';
					} else {
						$social_type = 'Organization';
					}
				}
			} elseif ( is_singular() && $this->option_isset( 'types' )
					   && is_array( $this->options['aiosp_opengraph_types'] )
					   && in_array( $current_post_type, $this->options['aiosp_opengraph_types'] )
			) {

				if ( $type == 'article' ) {
					if ( ! empty( $metabox['aioseop_opengraph_settings_section'] ) ) {
						$section = $metabox['aioseop_opengraph_settings_section'];
					}
					if ( ! empty( $metabox['aioseop_opengraph_settings_tag'] ) ) {
						$tag = $metabox['aioseop_opengraph_settings_tag'];
					}
					if ( ! empty( $this->options['aiosp_opengraph_facebook_publisher'] ) ) {
						$publisher = $this->options['aiosp_opengraph_facebook_publisher'];
					}
				}

				if ( ! empty( $this->options['aiosp_opengraph_twitter_domain'] ) ) {
					$domain = $this->options['aiosp_opengraph_twitter_domain'];
				}

				if ( $type == 'article' && ! empty( $post ) ) {
					if ( isset( $post->post_author ) && ! empty( $this->options['aiosp_opengraph_facebook_author'] ) ) {
						$author = get_the_author_meta( 'facebook', $post->post_author );
					}

					if ( isset( $post->post_date_gmt ) ) {
						$published_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_date_gmt ) );
					}

					if ( isset( $post->post_modified_gmt ) ) {
						$modified_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_modified_gmt ) );
					}
				}

				$image       = $metabox['aioseop_opengraph_settings_image'];
				$video       = $metabox['aioseop_opengraph_settings_video'];
				$title       = $metabox['aioseop_opengraph_settings_title'];
				$description = $metabox['aioseop_opengraph_settings_desc'];

				/* Add AIOSEO variables if Site Title and Desc from AIOSEOP not selected */
				global $aiosp;
				if ( empty( $title ) ) {
					$title = $aiosp->wp_title();
				}
				if ( empty( $description ) ) {
					$description = trim( strip_tags( get_post_meta( $post->ID, '_aioseop_description', true ) ) );
				}

				/* Add default title */
				if ( empty( $title ) ) {
					$title = get_the_title();
				}

				// Add default description.
				if ( empty( $description ) && ! post_password_required( $post ) ) {

					$description = $post->post_excerpt;

					if ( $this->options['aiosp_opengraph_generate_descriptions'] || empty( $description ) ) {
						$description = $post->post_content;
					}
				}
				if ( empty( $type ) ) {
					$type = 'article';
				}
			} elseif ( AIOSEOPPRO && ( is_category() || is_tag() || is_tax() ) ) {
				if ( isset( $this->options['aioseop_opengraph_settings_category'] ) ) {
					$type = $this->options['aioseop_opengraph_settings_category'];
				}
				if ( isset( $metabox['aioseop_opengraph_settings_category'] ) ) {
					$type = $metabox['aioseop_opengraph_settings_category'];
				}
				if ( $type == 'article' ) {
					if ( ! empty( $metabox['aioseop_opengraph_settings_section'] ) ) {
						$section = $metabox['aioseop_opengraph_settings_section'];
					}
					if ( ! empty( $metabox['aioseop_opengraph_settings_tag'] ) ) {
						$tag = $metabox['aioseop_opengraph_settings_tag'];
					}
					if ( ! empty( $this->options['aiosp_opengraph_facebook_publisher'] ) ) {
						$publisher = $this->options['aiosp_opengraph_facebook_publisher'];
					}
				}
				if ( ! empty( $this->options['aiosp_opengraph_twitter_domain'] ) ) {
					$domain = $this->options['aiosp_opengraph_twitter_domain'];
				}
				if ( $type == 'article' && ! empty( $post ) ) {
					if ( isset( $post->post_author ) && ! empty( $this->options['aiosp_opengraph_facebook_author'] ) ) {
						$author = get_the_author_meta( 'facebook', $post->post_author );
					}

					if ( isset( $post->post_date_gmt ) ) {
						$published_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_date_gmt ) );
					}
					if ( isset( $post->post_modified_gmt ) ) {
						$modified_time = date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $post->post_modified_gmt ) );
					}
				}
				$image       = $metabox['aioseop_opengraph_settings_image'];
				$video       = $metabox['aioseop_opengraph_settings_video'];
				$title       = $metabox['aioseop_opengraph_settings_title'];
				$description = $metabox['aioseop_opengraph_settings_desc'];
				/* Add AIOSEO variables if Site Title and Desc from AIOSEOP not selected */
				global $aiosp;
				if ( empty( $title ) ) {
					$title = $aiosp->wp_title();
				}
				if ( empty( $description ) ) {
					$term_id = isset( $_GET['tag_ID'] ) ? (int) $_GET['tag_ID'] : 0;
					$term_id = $term_id ? $term_id : get_queried_object()->term_id;
					$description = trim( strip_tags( get_term_meta( $term_id, '_aioseop_description', true ) ) );
				}
				// Add default title
				if ( empty( $title ) ) {
					$title = get_the_title();
				}
				// Add default description.
				if ( empty( $description ) && ! post_password_required( $post ) ) {
					$description = get_queried_object()->description;
				}
				if ( empty( $type ) ) {
					$type = 'website';
				}
			} elseif ( is_home() && ! is_front_page() ) {
				// This is the blog page but not the homepage.
				global $aiosp;
				$image       = $metabox['aioseop_opengraph_settings_image'];
				$video       = $metabox['aioseop_opengraph_settings_video'];
				$title       = $metabox['aioseop_opengraph_settings_title'];
				$description = $metabox['aioseop_opengraph_settings_desc'];

				if ( empty( $description ) ) {
					// If there's not social description, fall back to the SEO description.
					$description = trim( strip_tags( get_post_meta( get_option( 'page_for_posts' ), '_aioseop_description', true ) ) );
				}
				if ( empty( $title ) ) {
					$title = $aiosp->wp_title();
				}
			} else {
				return;
			}

			if ( $type === 'article' && ! empty( $post ) && is_singular() ) {
				if ( ! empty( $this->options['aiosp_opengraph_gen_tags'] ) ) {
					if ( ! empty( $this->options['aiosp_opengraph_gen_keywords'] ) ) {
						$keywords = $aiosp->get_main_keywords();
						$keywords = $this->apply_cf_fields( $keywords );
						$keywords = apply_filters( 'aioseop_keywords', $keywords );
						if ( ! empty( $keywords ) && ! empty( $tag ) ) {
							$tag .= ',' . $keywords;
						} elseif ( empty( $tag ) ) {
							$tag = $keywords;
						}
					}
					$tag = $aiosp->keyword_string_to_list( $tag );
					if ( ! empty( $this->options['aiosp_opengraph_gen_categories'] ) ) {
						$tag = array_merge( $tag, $aiosp->get_all_categories( $post->ID ) );
					}
					if ( ! empty( $this->options['aiosp_opengraph_gen_post_tags'] ) ) {
						$tag = array_merge( $tag, $aiosp->get_all_tags( $post->ID ) );
					}
				}
				if ( ! empty( $tag ) ) {
					$tag = $aiosp->clean_keyword_list( $tag );
				}
			}

			if ( ! empty( $this->options['aiosp_opengraph_title_shortcodes'] ) ) {
				$title = do_shortcode( $title );
			}
			if ( ! empty( $description ) ) {
				$description = $aiosp->internationalize( preg_replace( '/\s+/', ' ', $description ) );
				if ( ! empty( $this->options['aiosp_opengraph_description_shortcodes'] ) ) {
					$description = do_shortcode( $description );
				}
				$description = $aiosp->trim_excerpt_without_filters( $description, 1000 );
			}

			$title       = $this->apply_cf_fields( $title );
			$description = $this->apply_cf_fields( $description );

			/* Data Validation */
			$title       = strip_tags( esc_attr( $title ) );
			$sitename    = strip_tags( esc_attr( $sitename ) );
			$description = strip_tags( esc_attr( $description ) );

			if ( empty( $thumbnail ) && ! empty( $image ) ) {
				$thumbnail = $image;
			}

			// Add user supplied default image.
			if ( empty( $thumbnail ) ) {
				if ( empty( $this->options['aiosp_opengraph_defimg'] ) ) {
					$thumbnail = $this->options['aiosp_opengraph_dimg'];
				} else {
					$img_type = $this->options['aiosp_opengraph_defimg'];
					if ( ! empty( $post ) ) {
						// Customize the type of image per post/post_type.
						$img_type = apply_filters( $this->prefix . 'default_image_type', $img_type, $post, $type );
					}
					switch ( $img_type ) {
						case 'featured':
							$thumbnail = $this->get_the_image_by_post_thumbnail();
							break;
						case 'attach':
							$thumbnail = $this->get_the_image_by_attachment();
							break;
						case 'content':
							$thumbnail = $this->get_the_image_by_scan();
							break;
						case 'custom':
							$meta_key = $this->options['aiosp_opengraph_meta_key'];
							if ( ! empty( $meta_key ) && ! empty( $post ) ) {
								$meta_key  = explode( ',', $meta_key );
								$thumbnail = $this->get_the_image_by_meta_key(
									array(
										'post_id'  => $post->ID,
										'meta_key' => $meta_key,
									)
								);
							}
							break;
						case 'auto':
							$thumbnail = $this->get_the_image();
							break;
						case 'author':
							$thumbnail = $this->get_the_image_by_author();
							break;
						default:
							$thumbnail = $this->options['aiosp_opengraph_dimg'];
					}
				}
			}

			if ( empty( $thumbnail ) && ! empty( $this->options['aiosp_opengraph_fallback'] ) ) {
				$thumbnail = $this->options['aiosp_opengraph_dimg'];
				if ( ! empty( $post ) ) {
					// Customize the default image per post/post_type.
					$thumbnail = apply_filters( $this->prefix . 'default_image', $thumbnail, $post, $type );
				}
			}

			if ( ! empty( $thumbnail ) ) {
				$thumbnail = esc_url( $thumbnail );
				$thumbnail = set_url_scheme( $thumbnail );
			}

			$width = $height = '';
			if ( ! empty( $thumbnail ) ) {
				if ( ! empty( $metabox['aioseop_opengraph_settings_imagewidth'] ) ) {
					$width = $metabox['aioseop_opengraph_settings_imagewidth'];
				}
				if ( ! empty( $metabox['aioseop_opengraph_settings_imageheight'] ) ) {
					$height = $metabox['aioseop_opengraph_settings_imageheight'];
				}
				if ( empty( $width ) && ! empty( $this->options['aiosp_opengraph_dimgwidth'] ) ) {
					$width = $this->options['aiosp_opengraph_dimgwidth'];
				}
				if ( empty( $height ) && ! empty( $this->options['aiosp_opengraph_dimgheight'] ) ) {
					$height = $this->options['aiosp_opengraph_dimgheight'];
				}
			}

			if ( ! empty( $video ) ) {
				if ( ! empty( $metabox['aioseop_opengraph_settings_videowidth'] ) ) {
					$videowidth = $metabox['aioseop_opengraph_settings_videowidth'];
				}
				if ( ! empty( $metabox['aioseop_opengraph_settings_videoheight'] ) ) {
					$videoheight = $metabox['aioseop_opengraph_settings_videoheight'];
				}
			}

			$card = 'summary';
			if ( ! empty( $this->options['aiosp_opengraph_defcard'] ) ) {
				$card = $this->options['aiosp_opengraph_defcard'];
			}

			if ( ! empty( $metabox['aioseop_opengraph_settings_setcard'] ) ) {
				$card = $metabox['aioseop_opengraph_settings_setcard'];
			}

			// support for changing legacy twitter cardtype-photo to summary large image
			if ( $card == 'photo' ) {
				$card = 'summary_large_image';
			}

			$site = $domain = $creator = '';

			if ( ! empty( $this->options['aiosp_opengraph_twitter_site'] ) ) {
				$site = $this->options['aiosp_opengraph_twitter_site'];
				$site = AIOSEOP_Opengraph_Public::prepare_twitter_username( $site );
			}

			if ( ! empty( $this->options['aiosp_opengraph_twitter_domain'] ) ) {
				$domain = $this->options['aiosp_opengraph_twitter_domain'];
			}

			if ( ! empty( $post ) && isset( $post->post_author ) && ! empty( $this->options['aiosp_opengraph_twitter_creator'] ) ) {
				$creator = get_the_author_meta( 'twitter', $post->post_author );
				$creator = AIOSEOP_Opengraph_Public::prepare_twitter_username( $creator );
			}

			if ( ! empty( $thumbnail ) ) {
				$twitter_thumbnail = $thumbnail; // Default Twitter image if custom isn't set.
			}

			if ( isset( $metabox['aioseop_opengraph_settings_customimg_twitter'] ) && ! empty( $metabox['aioseop_opengraph_settings_customimg_twitter'] ) ) {
				// Set Twitter image from custom.
				$twitter_thumbnail = set_url_scheme( $metabox['aioseop_opengraph_settings_customimg_twitter'] );
			}

			// Apply last filters.
			$description = apply_filters( 'aioseop_description', $description );

			$meta = array(
				'facebook' => array(
					'title'          => 'og:title',
					'type'           => 'og:type',
					'url'            => 'og:url',
					'thumbnail'      => 'og:image',
					'width'          => 'og:image:width',
					'height'         => 'og:image:height',
					'video'          => 'og:video',
					'videowidth'     => 'og:video:width',
					'videoheight'    => 'og:video:height',
					'sitename'       => 'og:site_name',
					'key'            => 'fb:admins',
					'appid'          => 'fb:app_id',
					'description'    => 'og:description',
					'section'        => 'article:section',
					'tag'            => 'article:tag',
					'publisher'      => 'article:publisher',
					'author'         => 'article:author',
					'published_time' => 'article:published_time',
					'modified_time'  => 'article:modified_time',
				),
				'twitter'  => array(
					'card'              => 'twitter:card',
					'site'              => 'twitter:site',
					'creator'           => 'twitter:creator',
					'domain'            => 'twitter:domain',
					'title'             => 'twitter:title',
					'description'       => 'twitter:description',
					'twitter_thumbnail' => 'twitter:image',
				),
			);

			// Only show if "use schema.org markup is checked".
			if ( ! empty( $aioseop_options['aiosp_schema_markup'] ) ) {
				$meta['google+'] = array( 'thumbnail' => 'image' );
			}

			$tags = array(
				'facebook' => array( 'name' => 'property', 'value' => 'content' ),
				'twitter'  => array( 'name' => 'name', 'value' => 'content' ),
				'google+'  => array( 'name' => 'itemprop', 'value' => 'content' ),
			);

			foreach ( $meta as $t => $data ) {
				foreach ( $data as $k => $v ) {
					if ( empty( $$k ) ) {
						$$k = '';
					}
					$filtered_value = $$k;
					$filtered_value = apply_filters( $this->prefix . 'meta', $filtered_value, $t, $k );
					if ( ! empty( $filtered_value ) ) {
						if ( ! is_array( $filtered_value ) ) {
							$filtered_value = array( $filtered_value );
						}

						/**
						 * This is to accomodate multiple fb:admins on separate lines.
						 * @TODO Eventually we'll want to put this in its own function so things like images work too.
						 */
						if ( 'key' === $k ) {
							$fbadmins = explode( ',', str_replace( ' ', '', $filtered_value[0] ) ); // Trim spaces then turn comma-separated values into an array.
							foreach ( $fbadmins as $fbadmin ) {
								echo '<meta ' . $tags[ $t ]['name'] . '="' . $v . '" ' . $tags[ $t ]['value'] . '="' . $fbadmin . '" />' . "\n";
							}
						} else {
							// For everything else.
							foreach ( $filtered_value as $f ) {
								// #1363: use esc_attr( $f ) instead of htmlspecialchars_decode( $f, ENT_QUOTES )
								echo '<meta ' . $tags[ $t ]['name'] . '="' . $v . '" ' . $tags[ $t ]['value'] . '="' . esc_attr( $f ) . '" />' . "\n";
							}
						}
					}
				}
			}
			$social_link_schema = '';
			if ( ! empty( $social_links ) ) {
				$home_url     = esc_url( get_home_url() );
				$social_links = explode( "\n", $social_links );
				foreach ( $social_links as $k => $v ) {
					$v = trim( $v );
					if ( empty( $v ) ) {
						unset( $social_links[ $k ] );
					} else {
						$v                  = esc_url( $v );
						$social_links[ $k ] = $v;
					}
				}
				$social_links       = join( '","', $social_links );
				$social_link_schema = <<<END
<script type="application/ld+json">
{ "@context" : "http://schema.org",
  "@type" : "{$social_type}",
  "name" : "{$social_name}",
  "url" : "{$home_url}",
  "sameAs" : ["{$social_links}"]
}
</script>

END;
			}
			echo apply_filters( 'aiosp_opengraph_social_link_schema', $social_link_schema );
		}

		/**
		 * Do / adds opengraph properties to meta.
		 * @since 2.3.11
		 *
		 * @global array $aioseop_options AIOSEOP plugin options.
		 */
		public function do_opengraph() {
			global $aioseop_options;
			if ( ! empty( $aioseop_options )
				&& ! empty( $aioseop_options['aiosp_schema_markup'] )
			) {
				add_filter( 'language_attributes', array( &$this, 'add_attributes' ) );
			}
			if ( ! defined( 'DOING_AJAX' ) ) {
				add_action( 'aioseop_modules_wp_head', array( &$this, 'add_meta' ), 5 );
				// Add social meta to AMP plugin.
				if ( apply_filters( 'aioseop_enable_amp_social_meta', true ) === true ) {
					add_action( 'amp_post_template_head', array( &$this, 'add_meta' ), 12 );
				}
			}
		}

		/**
		 * Set up types.
		 *
		 * @since ?
		 * @since 2.3.15 Change to website for homepage and blog post index page, default to object.
		 */
		function type_setup() {
			$this->type = 'object'; // Default to type object if we don't have some other rule.

			if ( is_home() || is_front_page() ) {
				$this->type = 'website'; // Home page and blog page should be website.
			} elseif ( is_singular() && $this->option_isset( 'types' ) ) {
				$metabox           = $this->get_current_options( array(), 'settings' );
				$current_post_type = get_post_type();
				if ( ! empty( $metabox['aioseop_opengraph_settings_category'] ) ) {
					$this->type = $metabox['aioseop_opengraph_settings_category'];
				} elseif ( isset( $this->options[ "aiosp_opengraph_{$current_post_type}_fb_object_type" ] ) ) {
					$this->type = $this->options[ "aiosp_opengraph_{$current_post_type}_fb_object_type" ];
				}
			}
		}

		/**
		 * Inits hooks and others for admin init.
		 * action:admin_init.
		 *
		 * @since 2.3.11
		 * @since 2.4.14 Refactored function name, and new filter added for defaults and missing term metabox.
		 */
		function admin_init() {
			add_filter( $this->prefix . 'display_settings', array( &$this, 'filter_settings' ), 10, 3 );
			add_filter( $this->prefix . 'override_options', array( &$this, 'override_options' ), 10, 3 );
			add_filter( $this->get_prefix( 'settings' ) . 'default_options', array(
				&$this,
				'filter_default_options',
			), 10, 2 );
			add_filter(
				$this->get_prefix( 'settings' ) . 'filter_metabox_options', array(
				&$this,
				'filter_metabox_options',
			), 10, 3
			);
			add_filter(
				$this->get_prefix( 'settings' ) . 'filter_term_metabox_options', array(
				&$this,
				'filter_metabox_options',
			), 10, 3
			);
			$post_types                                        = $this->get_post_type_titles();
			$rempost                                           = array(
				'revision'            => 1,
				'nav_menu_item'       => 1,
				'custom_css'          => 1,
				'customize_changeset' => 1,
			);
			$post_types                                        = array_diff_key( $post_types, $rempost );
			$this->default_options['types']['initial_options'] = $post_types;
			foreach ( $post_types as $slug => $name ) {
				$field                                     = $slug . '_fb_object_type';
				$this->default_options[ $field ]           = array(
					'name'            => "$name " . __( 'Object Type', 'all-in-one-seo-pack' ) . "<br />($slug)",
					'type'            => 'select',
					'style'           => '',
					'initial_options' => $this->fb_object_types,
					'default'         => 'article',
					'condshow'        => array( 'aiosp_opengraph_types\[\]' => $slug ),
				);
				$this->help_text[ $field ]                 = __( 'Choose a default value that best describes the content of your post type.', 'all-in-one-seo-pack' );
				$this->help_anchors[ $field ]              = '#content-object-types';
				$this->locations['opengraph']['options'][] = $field;
				$this->layout['facebook']['options'][]     = $field;
			}
			$this->setting_options();
			$this->add_help_text_links();

		}

		function get_all_images( $options = null, $p = null ) {
			static $img = array();
			if ( ! is_array( $options ) ) {
				$options = array();
			}
			if ( ! empty( $this->options['aiosp_opengraph_meta_key'] ) ) {
				$options['meta_key'] = $this->options['aiosp_opengraph_meta_key'];
			}
			if ( empty( $img ) ) {
				$size    = apply_filters( 'post_thumbnail_size', 'large' );
				$default = $this->get_the_image_by_default();
				if ( ! empty( $default ) ) {
					$default = set_url_scheme( $default );
					$img[ $default ] = 0;
				}
				$img = array_merge( $img, parent::get_all_images( $options, null ) );
			}

			if ( ! empty( $options ) && ! empty( $options['aioseop_opengraph_settings_customimg'] ) ) {
				$img[ $options['aioseop_opengraph_settings_customimg'] ] = 'customimg';
			}

			if ( ! empty( $options ) && ! empty( $options['aioseop_opengraph_settings_customimg'] ) ) {
				$img[ $options['aioseop_opengraph_settings_customimg'] ]         = 'customimg';
				$img[ $options['aioseop_opengraph_settings_customimg_twitter'] ] = 'customimg_twitter';
			}

			if ( $author_img = $this->get_the_image_by_author( $p ) ) {
				$image['author'] = $author_img;
			}
			$image  = array_flip( $img );
			$images = array();
			if ( ! empty( $image ) ) {
				foreach ( $image as $k => $v ) {
					$images[ $v ] = '<img height=150 src="' . $v . '">';
				}
			}

			return array( $image, $images );
		}

		function get_the_image_by_author( $options = null, $p = null ) {
			if ( $p === null ) {
				global $post;
			} else {
				$post = $p;
			}
			if ( ! empty( $post ) && ! empty( $post->post_author ) ) {
				$matches    = array();
				$get_avatar = get_avatar( $post->post_author, 300 );
				if ( preg_match( "/src='(.*?)'/i", $get_avatar, $matches ) ) {
					return $matches[1];
				}
			}

			return false;
		}

		function get_the_image( $options = null, $p = null ) {
			$meta_key = $this->options['aiosp_opengraph_meta_key'];

			return parent::get_the_image( array( 'meta_key' => $meta_key ), $p );
		}

		function get_the_image_by_default( $args = array() ) {
			return $this->options['aiosp_opengraph_dimg'];
		}

		function settings_update() {

		}

		/**
		 * Enqueue our file upload scripts and styles.
		 * @param $hook
		 */
		function og_admin_enqueue_scripts( $hook ) {

			if ( 'all-in-one-seo_page_aiosp_opengraph' != $hook && 'term.php' != $hook ) {
				// Only enqueue if we're on the social module settings page.
				return;
			}

			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_media();
		}

		function save_tax_data( $term_id, $tt_id, $taxonomy ) {
			static $update = false;
			if ( $update ) {
				return;
			}
			if ( $this->locations !== null ) {
				foreach ( $this->locations as $k => $v ) {
					if ( isset( $v['type'] ) && ( $v['type'] === 'metabox' ) ) {
						$opts    = $this->default_options( $k );
						$options = array();
						$update  = false;
						foreach ( $opts as $l => $o ) {
							if ( isset( $_POST[ $l ] ) ) {
								$options[ $l ] = stripslashes_deep( $_POST[ $l ] );
								$options[ $l ] = esc_attr( $options[ $l ] );
								$update        = true;
							}
						}
						if ( $update ) {
							$prefix  = $this->get_prefix( $k );
							$options = apply_filters( $prefix . 'filter_term_metabox_options', $options, $k, $term_id );
							update_term_meta( $term_id, '_' . $prefix . $k, $options );
						}
					}
				}
			}
		}

		/**
		 * Returns the placeholder filtered and ready for DOM display.
		 * filter:aioseop_opengraph_placeholder
		 * @since 2.4.14
		 *
		 * @param mixed  $placeholder Placeholder to be filtered.
		 * @param string $type        Type of the value to be filtered.
		 *
		 * @return string
		 */
		public function filter_placeholder( $placeholder, $type = 'text' ) {
			return strip_tags( trim( $placeholder ) );
		}

		/**
		 * Returns filtered default options.
		 * filter:{prefix}default_options
		 * @since 2.4.13
		 *
		 * @param array  $options  Default options.
		 * @param string $location Location.
		 *
		 * @return array
		 */
		public function filter_default_options( $options, $location ) {
			if ( $location === 'settings' ) {
				$prefix = $this->get_prefix( $location ) . $location . '_';
				// Add image checker as default
				$options[ $prefix . 'customimg_checker' ] = 0;
			}
			return $options;
		}

		/**
		 * Returns facebook debug script and link.
		 * @since 2.4.14
		 *
		 * @return string
		 */
		private function get_facebook_debug() {
			ob_start();
			?>
            <script>
                jQuery(document).ready(function () {
                    var snippet = jQuery("#aioseop_snippet_link");
                    if (snippet.length === 0) {
                        jQuery("#aioseop_opengraph_settings_facebook_debug_wrapper").hide();
                    } else {
                        snippet = snippet.html();
                        jQuery("#aioseop_opengraph_settings_facebook_debug")
                            .attr("href", "https://developers.facebook.com/tools/debug/sharing/?q=" + snippet);
                    }
                });
            </script>
            <a name="aioseop_opengraph_settings_facebook_debug"
               id="aioseop_opengraph_settings_facebook_debug"
               class="button-primary"
               href=""
               target="_blank"
            ><?php echo __( 'Debug This Post', 'all-in-one-seo-pack' ); ?></a>
			<?php
			return ob_get_clean();
		}
	}
}