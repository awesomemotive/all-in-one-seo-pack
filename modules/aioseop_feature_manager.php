<?php
/**
 * The Feature Manager class.
 *
 * @package All_in_One_SEO_Pack
 * @since ?
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Feature_Manager' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Feature_Manager
	 */
	class All_in_One_SEO_Pack_Feature_Manager extends All_in_One_SEO_Pack_Module {

		/**
		 * Module Info
		 *
		 * @since ?
		 *
		 * @var array|mixed|void $module_info
		 */
		protected $module_info = array();

		/**
		 * All_in_One_SEO_Pack_Feature_Manager constructor.
		 *
		 * @since ?
		 *
		 * @param $mod Module.
		 */
		function __construct( $mod ) {
			/* translators: the Feature Manager allows users to (de)activate other modules of the plugin. */
			$this->name   = __( 'Feature Manager', 'all-in-one-seo-pack' );        // Human-readable name of the plugin.
			$this->prefix = 'aiosp_feature_manager_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			parent::__construct();
			$this->module_info = array(
				'sitemap'           => array(
					/* translators: the XML Sitemaps module allows users to generate a sitemap in .xml format for their website and submit it to search engines such as Google, Bing and Yahoo. */
					'name'        => __( 'XML Sitemaps', 'all-in-one-seo-pack' ),
					'description' => __( 'Create and manage your XML Sitemaps using this feature and submit your XML Sitemap to Google, Bing/Yahoo and Ask.com.', 'all-in-one-seo-pack' ),
				),
				'opengraph'         => array(
					/* translators: the Social Meta module allows users to add Open Graph (OG:) meta tags to their site's post/pages to control the appearance of them when shared on social media networks like Facebook and Twitter. */
					'name'        => __( 'Social Meta', 'all-in-one-seo-pack' ),
					/* translators: Social Meta refers to Open Graph (OG:) meta tags, which can be used to control the appearance of a site's posts/pages when shared on social media networks like Facebook and Twitter. */
					'description' => __( 'Add Social Meta data to your site to deliver closer integration between your website and social media.', 'all-in-one-seo-pack' ),
				),
				'robots'            => array(
					/* translators: the Robots.txt module allows users to provide instructions to web robots, e.g. search engine crawlers. */
					'name'        => __( 'Robots.txt', 'all-in-one-seo-pack' ),
					'description' => __( 'Generate and validate your robots.txt file to guide search engines through your site.', 'all-in-one-seo-pack' ),
				),
				'file_editor'       => array(
					/* translators: the File Editor module allows users to edit the robots.txt file or .htaccess file on their site. */
					'name'        => __( 'File Editor', 'all-in-one-seo-pack' ),
					'description' => __( 'Edit your .htaccess file to fine-tune your site.', 'all-in-one-seo-pack' ),
				),
				'importer_exporter' => array(
					/* translators: the Importer & Exporter module allows users to import/export their All in One SEO Pack settings for backup purposes or when migrating their site. */
					'name'        => __( 'Importer & Exporter', 'all-in-one-seo-pack' ),
					/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
					'description' => sprintf( __( 'Exports and imports your %s plugin settings.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
				),
				'bad_robots'        => array(
					/* translators: the Bad Bot Blocker module allows users to block requests from user agents that are known to misbehave. */
					'name'        => __( 'Bad Bot Blocker', 'all-in-one-seo-pack' ),
					/* translators: 'bots' refers to user agents/web robots that misbehave. */
					'description' => __( 'Stop badly behaving bots from slowing down your website.', 'all-in-one-seo-pack' ),
				),
				'performance'       => array(
					/* translators: the Performance module allows users to set certain performance related settings and check the status of their WordPress installation. */
					'name'        => __( 'Performance', 'all-in-one-seo-pack' ),
					'description' => __( 'Optimize performance related to SEO and check your system status.', 'all-in-one-seo-pack' ),
					'default'     => 'on',
				),
			);

			if ( AIOSEOPPRO ) {

				$this->module_info['coming_soon']   = array(
					/* translators: this refers to a feature that will be launched in the near future. */
					'name'        => __( 'Coming Soon...', 'all-in-one-seo-pack' ),
					/* translators: the Image SEO module allows users to optimize their images for search engines. */
					'description' => __( 'Image SEO', 'all-in-one-seo-pack' ),
					'save'        => false,
				);
				$this->module_info['video_sitemap'] = array(
					/* translators: the Video Sitemap module allows users to generate a sitemap with video content in .xml format for their website and submit it to search engines such as Google, Bing and Yahoo. */
					'name'        => __( 'Video Sitemap', 'all-in-one-seo-pack' ),
					'description' => __( 'Create and manage your Video Sitemap using this feature and submit your Video Sitemap to Google, Bing/Yahoo and Ask.com.', 'all-in-one-seo-pack' ),
				);

			} else {

				$this->module_info['coming_soon'] = array(
					'name'        => __( 'Video Sitemap', 'all-in-one-seo-pack' ),
					/* translators: this refers to a module that is exclusively available in All in One SEO Pack Pro. */
					'description' => __( 'Pro Version Only', 'all-in-one-seo-pack' ),
					'save'        => false,
				);

			}

			// Set up default settings fields.
			// Name			- Human-readable name of the setting.
			// Help_text	- Inline documentation for the setting.
			// Type			- Type of field; this defaults to checkbox; currently supported types are checkbox, text, select, multiselect.
			// Default		- Default value of the field.
			// Initial_options - Initial option list used for selects and multiselects.
			// Other supported options: class, id, style -- allows you to set these HTML attributes on the field.
			$this->default_options = array();
			$this->module_info     = apply_filters( 'aioseop_module_info', $this->module_info );
			$mod[]                 = 'coming_soon';

			foreach ( $mod as $m ) {
				if ( 'performance' === $m && ! is_super_admin() ) {
					continue;
				}
				$this->default_options[ "enable_$m" ] = array(
					'name'      => $this->module_info[ $m ]['name'],
					'help_text' => $this->module_info[ $m ]['description'],
					'type'      => 'custom',
					'class'     => 'aioseop_feature',
					'id'        => "aioseop_$m",
					'save'      => true,
				);

				if ( ! empty( $this->module_info[ $m ]['image'] ) ) {
					$this->default_options[ "enable_$m" ]['image'] = $this->module_info[ $m ]['image'];
				}
				if ( ! empty( $this->module_info[ $m ] ) ) {
					foreach ( array( 'save', 'default' ) as $option ) {
						if ( isset( $this->module_info[ $m ][ $option ] ) ) {
							$this->default_options[ "enable_$m" ][ $option ] = $this->module_info[ $m ][ $option ];
						}
					}
				}
			}
			$this->layout = array(
				'default' => array(
					'name'      => $this->name,
					'help_link' => 'https://semperplugins.com/documentation/feature-manager/',
					'options'   => array_keys( $this->default_options ),
				),
			);
			// Load initial options / set defaults.
			$this->update_options();
			if ( is_admin() ) {
				add_filter( $this->prefix . 'output_option', array( $this, 'display_option_div' ), 10, 2 );
				add_filter( $this->prefix . 'submit_options', array( $this, 'filter_submit' ) );
			}
		}

		/**
		 * Menu Order
		 *
		 * Determines the menu order.
		 *
		 * @since ?
		 *
		 * @return int
		 */
		function menu_order() {
			return 20;
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
			$submit['Submit']['value']  = __( 'Update Features', 'all-in-one-seo-pack' ) . ' &raquo;';
			$submit['Submit']['class'] .= ' hidden';
			/* translators: this button deactivates all active modules of the plugin. */
			$submit['Submit_Default']['value'] = __( 'Reset Features', 'all-in-one-seo-pack' ) . ' &raquo;';

			return $submit;
		}

		/**
		 * Display Option Div
		 *
		 * @since ?
		 *
		 * @param $buf
		 * @param $args
		 * @return string
		 */
		function display_option_div( $buf, $args ) {
			$name     = '';
			$img      = '';
			$desc     = '';
			$checkbox = '';
			$class    = '';
			if ( isset( $args['options']['help_text'] ) && ! empty( $args['options']['help_text'] ) ) {
				$desc .= '<p class="aioseop_desc">' . $args['options']['help_text'] . '</p>';
			}
			if ( $args['value'] ) {
				$class = ' active';
			}
			if ( isset( $args['options']['image'] ) && ! empty( $args['options']['image'] ) ) {
				$img .= '<p><img src="' . AIOSEOP_PLUGIN_IMAGES_URL . $args['options']['image'] . '"></p>';
			} else {
				$img .= '<p><span class="aioseop_featured_image' . $class . '"></span></p>';
			}

			if ( $args['options']['save'] ) {
				$name      = "<h3>{$args['options']['name']}</h3>";
				$checkbox .= '<input type="checkbox" onchange="jQuery(\'#' . $args['options']['id'] . ' .aioseop_featured_image, #' . $args['options']['id'] . ' .feature_button\').toggleClass(\'active\', this.checked);jQuery(\'input[name=Submit]\').trigger(\'click\');" style="display:none;" id="' . $args['name'] . '" name="' . $args['name'] . '"';
				if ( $args['value'] ) {
					$checkbox .= ' CHECKED';
				}
				$checkbox .= '><span class="button-primary feature_button' . $class . '"></span>';
			} else {
				$name = "<b>{$args['options']['name']}</b>";
			}
			if ( ! empty( $args['options']['id'] ) ) {
				$args['attr'] .= " id='{$args['options']['id']}'";
			}

			return $buf . "<div {$args['attr']}><label for='{$args['name']}'><div class='free flag'>FREE</div><div class='pro flag'>PRO</div>{$name}{$img}{$desc}{$checkbox}</label></div>";
		}
	}
}
