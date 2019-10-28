<?php
/**
 * Schema Builder Class
 *
 * Creates the schema to be displayed on frontend.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * AIOSEOP Schema Builder
 *
 * @since 3.2
 */
class AIOSEOP_Schema_Builder {

	/**
	 * Graph Classes.
	 *
	 * @since 3.2
	 *
	 * @var array $graphs
	 */
	public $graphs = array();

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 */
	public function __construct() {
		$this->graphs = $this->get_graphs();
	}

	/**
	 * Register Graphs
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	public function get_graphs() {
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-organization.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-person.php';

		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-creativework.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-article.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-website.php';

		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-webpage.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-collectionpage.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-profilepage.php';
		require_once AIOSEOP_PLUGIN_DIR . 'inc/schema/graphs/graph-searchresultspage.php';

		$graphs = array(
			// Keys/Slugs follow Schema's @type format.
			'Article'           => new AIOSEOP_Graph_Article(),
			'CollectionPage'    => new AIOSEOP_Graph_CollectionPage(),
			'Organization'      => new AIOSEOP_Graph_Organization(),
			'Person'            => new AIOSEOP_Graph_Person(),
			'ProfilePage'       => new AIOSEOP_Graph_ProfilePage(),
			'SearchResultsPage' => new AIOSEOP_Graph_SearchResultsPage(),
			'Website'           => new AIOSEOP_Graph_WebSite(),
			'Webpage'           => new AIOSEOP_Graph_Webpage(),
		);

		/**
		 * Register Schema Objects
		 *
		 * @since 3.2
		 *
		 * @param $graphs Array containing schema objects that are currently active.
		 */
		$graphs = apply_filters( 'aioseop_register_schema_objects', $graphs );

		// TODO Could add operation here to loop through objects to *::add_hooks(). Rather than schema __constructor executing add_hooks().
		// That would allow some schema objects to be completely replaced without interfering.

		return $graphs;
	}

	/**
	 * Get Layout
	 *
	 * Presets the schema layout to be generated.
	 *
	 * This concept is intended to allow...
	 *
	 * * Better dynamics with configurable layout settings.
	 * * Unnecessarily generating data where some instances remove it.
	 *
	 * @since 3.2
	 *
	 * @uses WP's Template Hierarchy
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
	 */
	public function get_layout() {
		global $aioseop_options;

		$layout = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				'[aioseop_schema_Organization]',
				'[aioseop_schema_WebSite]',
			),
		);

		// TODO Add layout customizations to settings.
		if ( is_front_page() || is_home() ) {
			array_push( $layout['@graph'], '[aioseop_schema_WebPage]' );
		} elseif ( is_archive() ) {
			if ( is_author() ) {
				array_push( $layout['@graph'], '[aioseop_schema_ProfilePage]' );
				array_push( $layout['@graph'], '[aioseop_schema_Person]' );
			} elseif ( is_post_type_archive() ) {
				array_push( $layout['@graph'], '[aioseop_schema_CollectionPage]' );
			} elseif ( is_tax() || is_category() || is_tag() ) {
				array_push( $layout['@graph'], '[aioseop_schema_CollectionPage]' );
				// Remove when Custom Taxonomies is supported.
				if ( is_tax() ) {
					$layout = array();
				}
			} elseif ( is_date() ) {
				array_push( $layout['@graph'], '[aioseop_schema_CollectionPage]' );
			}
		} elseif ( is_singular() || is_single() ) {
			global $post;

			array_push( $layout['@graph'], '[aioseop_schema_WebPage]' );
			if ( ! is_post_type_hierarchical( $post->post_type ) ) {
				// TODO Add custom setting for individual posts.

				array_push( $layout['@graph'], '[aioseop_schema_Article]' );
				array_push( $layout['@graph'], '[aioseop_schema_Person]' );
			}

			// Remove when CPT is supported.
			if ( ! in_array( get_post_type( $post ), array( 'post', 'page' ) ) ) {
				$layout = array();
			}
		} elseif ( is_search() ) {
			array_push( $layout['@graph'], '[aioseop_schema_SearchResultsPage]' );
		} elseif ( is_404() ) {
			// Do 404 page.
		}

		/**
		 * Schema Layout
		 *
		 * Pre-formats the schema array shortcode layout.
		 *
		 * @since 3.2
		 *
		 * @param array $layout Schema array/object containing shortcodes.
		 */
		$layout = apply_filters( 'aioseop_schema_layout', $layout );

		// Encode to json string, and remove string type around shortcodes.
		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
			$layout = wp_json_encode( (object) $layout, JSON_UNESCAPED_SLASHES ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.json_unescaped_slashesFound
		} else {
			// PHP <= 5.3 compatibility.
			$layout = wp_json_encode( (object) $layout );
			$layout = str_replace( '\/', '/', $layout );
		}

		$layout = str_replace( '"[', '[', $layout );
		$layout = str_replace( ']"', ']', $layout );

		return $layout;
	}

	/**
	 * Display JSON LD Script
	 *
	 * @since 3.2
	 */
	public function display_json_ld_head_script() {
		// do stuff.

		$layout = $this->get_layout();

		do_action( 'aioseop_schema_internal_shortcodes_on' );
		$schema_content = do_shortcode( $layout );
		do_action( 'aioseop_schema_internal_shortcodes_off' );

		echo '<script type="application/ld+json" class="aioseop-schema">' . $schema_content . '</script>';
		echo "\n";
	}

	/**
	 * Display JSON LD Script
	 *
	 * Intended for data that isn't readily available during `wp_head`.
	 *
	 * This should be avoided if possible. If an instance requires data to be loaded later,
	 * then use transient data to load in next instance within `wp_head`.
	 *
	 * @since 3.2
	 */
	public function display_json_ld_body_script() {
		// do stuff.
	}

}
