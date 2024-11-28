<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the HTML sitemap block.
 *
 * @since 4.1.3
 */
class Block {
	/**
	 * Class constructor.
	 *
	 * @since 4.1.1
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Registers the block.
	 *
	 * @since  4.1.3
	 *
	 * @return void
	 */
	public function register() {
		aioseo()->blocks->registerBlock(
			'aioseo/html-sitemap', [
				'attributes'      => [
					'default'          => [
						'type'    => 'boolean',
						'default' => true
					],
					'post_types'       => [
						'type'    => 'string',
						'default' => wp_json_encode( [ 'post', 'page' ] )
					],
					'post_types_all'   => [
						'type'    => 'boolean',
						'default' => true
					],
					'taxonomies'       => [
						'type'    => 'string',
						'default' => wp_json_encode( [ 'category', 'post_tag' ] )
					],
					'taxonomies_all'   => [
						'type'    => 'boolean',
						'default' => true
					],
					'show_label'       => [
						'type'    => 'boolean',
						'default' => true
					],
					'archives'         => [
						'type'    => 'boolean',
						'default' => false
					],
					'publication_date' => [
						'type'    => 'boolean',
						'default' => true
					],
					'nofollow_links'   => [
						'type'    => 'boolean',
						'default' => false
					],
					'order_by'         => [
						'type'    => 'string',
						'default' => 'publish_date'
					],
					'order'            => [
						'type'    => 'string',
						'default' => 'asc'
					],
					'excluded_posts'   => [
						'type'    => 'string',
						'default' => wp_json_encode( [] )
					],
					'excluded_terms'   => [
						'type'    => 'string',
						'default' => wp_json_encode( [] )
					],
					'is_admin'         => [
						'type'    => 'boolean',
						'default' => false
					]
				],
				'render_callback' => [ $this, 'render' ],
				'editor_style'    => 'aioseo-html-sitemap'
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @since 4.1.3
	 *
	 * @param  array  $attributes The attributes.
	 * @return string             The HTML sitemap code.
	 */
	public function render( $attributes ) {
		if ( ! $attributes['default'] ) {
			$jsonFields = [ 'post_types', 'taxonomies', 'excluded_posts', 'excluded_terms' ];
			foreach ( $attributes as $k => $v ) {
				if ( in_array( $k, $jsonFields, true ) ) {
					$attributes[ $k ] = json_decode( $v );
				}
			}

			$attributes['excluded_posts'] = $this->extractIds( $attributes['excluded_posts'] );
			$attributes['excluded_terms'] = $this->extractIds( $attributes['excluded_terms'] );

			if ( ! empty( $attributes['post_types_all'] ) ) {
				$attributes['post_types'] = aioseo()->helpers->getPublicPostTypes( true );
			}
			if ( ! empty( $attributes['taxonomies_all'] ) ) {
				$attributes['taxonomies'] = aioseo()->helpers->getPublicTaxonomies( true );
			}
		} else {
			$attributes = [];
		}

		$attributes = aioseo()->htmlSitemap->frontend->getAttributes( $attributes );

		return aioseo()->htmlSitemap->frontend->output( false, $attributes );
	}

	/**
	 * Extracts the IDs from the excluded objects.
	 *
	 * @since 4.1.3
	 *
	 * @param  array $objects The objects.
	 * @return array          The object IDs.
	 */
	private function extractIds( $objects ) {
		return array_map( function ( $object ) {
			$object = json_decode( $object );

			return (int) $object->value;
		}, $objects );
	}
}