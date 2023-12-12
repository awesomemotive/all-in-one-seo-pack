<?php
namespace AIOSEO\Plugin\Common\Standalone\PageBuilders;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager as ControlsManager;
use Elementor\Core\DocumentTypes\PageBase;

/**
 * Integrate our SEO Panel with Elementor Page Builder.
 *
 * @since 4.1.7
 */
class Elementor extends Base {
	/**
	 * The plugin files.
	 *
	 * @since 4.1.7
	 *
	 * @var array
	 */
	public $plugins = [
		'elementor/elementor.php',
		'elementor-pro/elementor-pro.php',
	];

	/**
	 * The integration slug.
	 *
	 * @since 4.1.7
	 *
	 * @var string
	 */
	public $integrationSlug = 'elementor';

	/**
	 * Init the integration.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function init() {
		if ( ! aioseo()->postSettings->canAddPostSettingsMetabox( get_post_type( $this->getPostId() ) ) ) {
			return;
		}

		if ( ! did_action( 'elementor/init' ) ) {
			add_action( 'elementor/init', [ $this, 'addPanelTab' ] );
		} else {
			$this->addPanelTab();
		}

		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'elementor/documents/register_controls', [ $this, 'registerDocumentControls' ] );
		add_action( 'elementor/editor/footer', [ $this, 'addContainers' ] );

		// Add the SEO tab to the main Elementor panel.
		add_action( 'elementor/editor/footer', [ $this, 'startCapturing' ], 0 );
		add_action( 'elementor/editor/footer', [ $this, 'endCapturing' ], 999 );
	}

	/**
	 * Start capturing buffer.
	 *
	 * @since 4.3.5
	 *
	 * @return void
	 */
	public function startCapturing() {
		ob_start();
	}

	/**
	 * End capturing buffer and add button.
	 * This is a hack to add the SEO tab to the main Elementor panel.
	 * We need to do this because Elementor doesn't provide a filter to add tabs to the main panel.
	 *
	 * @since 4.3.5
	 *
	 * @return void
	 */
	public function endCapturing() {
		$output  = ob_get_clean();
		$search  = '/(<div class="elementor-component-tab elementor-panel-navigation-tab" data-tab="global">.*<\/div>)/m';
		$replace = '${1}<div class="elementor-component-tab elementor-panel-navigation-tab" data-tab="aioseo">SEO</div>';
		echo preg_replace( $search, $replace, $output ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add the AIOSEO Panel Tab on Elementor.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function addPanelTab() {
		ControlsManager::add_tab( 'aioseo', AIOSEO_PLUGIN_SHORT_NAME );
	}

	/**
	 * Register the Elementor Document Controls.
	 *
	 * @since 4.1.7
	 *
	 * @return void
	 */
	public function registerDocumentControls( $document ) {
		// PageBase is the base class for documents like `post` `page` and etc.
		if ( ! $document instanceof PageBase || ! $document::get_property( 'has_elements' ) ) {
			return;
		}

		// This is needed to get the tab to appear, but will be overwritten in the JavaScript.
		$document->start_controls_section(
			'aioseo_section',
			[
				'label' => AIOSEO_PLUGIN_SHORT_NAME,
				'tab'   => 'aioseo',
			]
		);

		$document->end_controls_section();
	}

	/**
	 * Returns whether or not the given Post ID was built with Elementor.
	 *
	 * @since 4.1.7
	 *
	 * @param  int     $postId The Post ID.
	 * @return boolean         Whether or not the Post was built with Elementor.
	 */
	public function isBuiltWith( $postId ) {
		$document = $this->getElementorDocument( $postId );
		if ( ! $document ) {
			return false;
		}

		return $document->is_built_with_elementor();
	}

	/**
	 * Returns the Elementor edit url for the given Post ID.
	 *
	 * @since 4.3.1
	 *
	 * @param  int    $postId The Post ID.
	 * @return string         The Edit URL.
	 */
	public function getEditUrl( $postId ) {
		$document = $this->getElementorDocument( $postId );
		if ( ! $document || ! $document->is_editable_by_current_user() ) {
			return '';
		}

		return esc_url( $document->get_edit_url() );
	}

	/**
	 * Add the containers to mount our panel.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	public function addContainers() {
		echo '<div id="aioseo-admin"></div>';
	}

	/**
	 * Returns the Elementor Document instance for the given Post ID.
	 *
	 * @since 4.3.5
	 *
	 * @param  int    $postId The Post ID.
	 * @return object         The Elementor Document instance.
	 */
	private function getElementorDocument( $postId ) {
		if (
			! class_exists( '\Elementor\Plugin' ) ||
			! is_object( \Elementor\Plugin::instance()->documents ) ||
			! method_exists( \Elementor\Plugin::instance()->documents, 'get' )
		) {
			return false;
		}

		$elementorDocument = \Elementor\Plugin::instance()->documents->get( $postId );
		if ( empty( $elementorDocument ) ) {
			return false;
		}

		return $elementorDocument;
	}

	/**
	 * Checks whether or not we should prevent the date from being modified.
	 * This method is supposed to be used in the `wp_ajax_seedprod_pro_save_lpage` action.
	 *
	 * @since 4.5.2
	 *
	 * @param  int  $postId The Post ID.
	 * @return bool         Whether or not we should prevent the date from being modified.
	 */
	public function limitModifiedDate( $postId ) {
		// This method is supposed to be used in the `wp_ajax_elementor_ajax` action.
		if ( empty( $_REQUEST['_nonce'] ) || ! wp_verify_nonce( $_REQUEST['_nonce'], 'elementor_ajax' ) ) {
			return false;
		}

		$editorPostId = ! empty( $_REQUEST['editor_post_id'] ) ? (int) $_REQUEST['editor_post_id'] : false;
		if ( $editorPostId !== $postId ) {
			return false;
		}

		return ! empty( $_REQUEST['aioseo_limit_modified_date'] );
	}
}