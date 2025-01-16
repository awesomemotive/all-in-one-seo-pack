<?php
namespace AIOSEO\Plugin\Lite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin as CommonAdmin;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class PostSettings extends CommonAdmin\PostSettings {
	/**
	 * Holds a list of page builder integration class instances.
	 * This prop exists for backwards compatibility with pre-4.2.0 versions (see backwardsCompatibilityLoad() in AIOSEO.php).
	 *
	 * @since 4.4.2
	 *
	 * @var object[]
	 */
	public $integrations = null;

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add upsell to terms.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			// We don't call getPublicTaxonomies() here because we want to show the CTA for Product Attributes as well.
			$taxonomies = get_taxonomies( [], 'objects' );
			foreach ( $taxonomies as $taxObject ) {
				if (
					empty( $taxObject->label ) ||
					! is_taxonomy_viewable( $taxObject )
				) {
					unset( $taxonomies[ $taxObject->name ] );
				}
			}

			foreach ( $taxonomies as $taxonomy ) {
				add_action( $taxonomy->name . '_edit_form', [ $this, 'addTaxonomyUpsell' ] );
				add_action( 'after-' . $taxonomy->name . '-table', [ $this, 'addTaxonomyUpsell' ] );
			}
		}
	}

	/**
	 * Add Taxonomy Upsell
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addTaxonomyUpsell() {
		$screen = aioseo()->helpers->getCurrentScreen();
		if (
			! isset( $screen->parent_base ) ||
			'edit' !== $screen->parent_base ||
			empty( $screen->taxonomy )
		) {
			return;
		}

		include_once AIOSEO_DIR . '/app/Lite/Views/taxonomy-upsell.php';
	}
}