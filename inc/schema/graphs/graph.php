<?php
/**
 * Schema Graph Base Class
 *
 * Acts as the base class for Schema Thing.
 *
 * @package All_in_One_SEO_Pack
 */

/**
 * Class AIOSEOP_Graph
 *
 * @since 3.2
 *
 * @see Schema Thing
 * @link https://schema.org/Thing
 */
abstract class AIOSEOP_Graph {

	/**
	 * Schema Slug.
	 *
	 * @since 3.2
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Schema Name.
	 *
	 * @since 3.2
	 *
	 * @var string
	 */
	public $name;

	// TODO Add Schema content/context object to handled all post/page (post_type, taxonomy, terms, author) data.

	/**
	 * AIOSEOP_Graph Constructor.
	 *
	 * @since 3.2
	 *
	 * @throws LogicException Shows which child class variables are missing or empty.
	 */
	public function __construct() {
		$this->slug = $this->get_slug();
		$this->name = $this->get_name();

		if ( ! isset( $this->slug ) || empty( $this->slug ) ) {
			throw new LogicException( 'Class ' . get_class( $this ) . ' property $slug is missing or empty.' );
		}
		if ( ! isset( $this->name ) || empty( $this->name ) ) {
			throw new LogicException( 'Class ' . get_class( $this ) . ' property $name is missing or empty.' );
		}

		$this->add_hooks();
	}

	/**
	 * Get Graph Slug.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	abstract protected function get_slug();

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	abstract protected function get_name();

	/**
	 * Prepare data.
	 *
	 * @since 3.2
	 *
	 * @return array
	 */
	abstract protected function prepare();

	/**
	 * Add Hooks.
	 *
	 * @since 3.2
	 */
	protected function add_hooks() {
		add_action( 'aioseop_schema_internal_shortcodes_on', array( $this, 'add_shortcode' ) );
		add_action( 'aioseop_schema_internal_shortcodes_off', array( $this, 'remove_shortcode' ) );
	}

	/**
	 * Add Shortcode
	 *
	 * @since 3.2
	 */
	public function add_shortcode() {
		add_shortcode( 'aioseop_schema_' . $this->slug, array( $this, 'display_json_ld' ) );
	}

	/**
	 * Remove Shortcode
	 *
	 * @since 3.2
	 */
	public function remove_shortcode() {
		remove_shortcode( 'aioseop_schema_' . $this->slug );
	}

	/**
	 * Display JSON LD
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function display_json_ld() {
		/**
		 * AIOSEOP Schema Class's Prepared Data
		 *
		 * @since 3.2
		 *
		 * @param array  Dynamically generated data through inherited schema graphs.
		 * @param string Current schema (child) class being used to prepare data.
		 */
		$schema_data = apply_filters( 'aioseop_schema_class_data', $this->prepare(), get_class( $this ) );
		return wp_json_encode( (object) $schema_data, JSON_UNESCAPED_SLASHES ) ?: '';
	}

	/**
	 * Prepare Image Data.
	 *
	 * TODO !?Move to graph.php since it is part of schema 'thing' object?!
	 *
	 * @since 3.2
	 *
	 * @param WP_Post $post See WP_Post for details.
	 * @return array
	 */
	protected function prepare_image( $post ) {
		$rtn_data = array(
			'@type' => 'ImageObject',
			'url'   => '',
		);

		$featured_image_url = $this->get_featured_image_url( $post );

		if ( $featured_image_url ) {
			// TODO Possibly change to call Graph ImageObject class.
			$rtn_data = array(
				'@type' => 'ImageObject',
				'@id'   => wp_get_canonical_url( $post ) . '#primaryimage',
				'url'   => $featured_image_url,

			);
		}

		return $rtn_data;
	}

	/**
	 * Get Featured Image URL.
	 *
	 * @since 3.2
	 *
	 * @param WP_Post $post See WP_Post for details.
	 * @return false|string
	 */
	protected function get_featured_image_url( $post ) {
		$image_url = '';

		if ( has_post_thumbnail( $post ) ) {
			$image_url = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
		} else {
			// Get first image from content.
			if ( ( substr_count( $post->post_content, '<img' ) + substr_count( $post->post_content, '<IMG' ) ) ) {
				if ( class_exists( 'DOMDocument' ) ) {
					$dom = new domDocument();

					// Non-compliant HTML might give errors, so ignore them.
					libxml_use_internal_errors( true );
					$dom->loadHTML( $post->post_content );
					libxml_clear_errors();

					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$dom->preserveWhiteSpace = false;

					$matches = $dom->getElementsByTagName( 'img' );
					foreach ( $matches as $match ) {
						$image_url = $match->getAttribute( 'src' );
					}
				} else {
					preg_match_all( '/<img.*src=([\'"])?(.*?)\\1/', $post->post_content, $matches );
					if ( $matches && isset( $matches[2] ) ) {
						$image_url = $matches[2];
					}
				}
			}
		}

		return $image_url;
	}

}
