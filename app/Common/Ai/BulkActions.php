<?php
namespace AIOSEO\Plugin\Common\Ai;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AI bulk actions for post types and the Media Library.
 *
 * @since 4.9.6
 */
class BulkActions {
	/**
	 * The required capability to use bulk generation.
	 *
	 * @since 4.9.6
	 *
	 * @var string
	 */
	const REQUIRED_CAPABILITY = 'aioseo_page_ai_content_settings';

	/**
	 * The additional capability required to save bulk generated content.
	 *
	 * @since 4.9.9
	 *
	 * @var string
	 */
	const REQUIRED_SAVE_CAPABILITY = 'aioseo_page_general_settings';

	/**
	 * Class constructor.
	 *
	 * @since 4.9.6
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'registerBulkActions' ] );
	}

	/**
	 * Register bulk actions for all public post types.
	 *
	 * @since 4.9.6
	 *
	 * @return void
	 */
	public function registerBulkActions() {
		$postTypes = aioseo()->helpers->getPublicPostTypes( false, false, true );

		foreach ( $postTypes as $postType ) {
			if ( 'attachment' === $postType['name'] ) {
				continue;
			}

			add_filter( 'bulk_actions-edit-' . $postType['name'], [ $this, 'addBulkAction' ] );
			add_filter( 'handle_bulk_actions-edit-' . $postType['name'], [ $this, 'handleBulkAction' ], 10, 3 );
		}

		// Media Library bulk actions.
		add_filter( 'bulk_actions-upload', [ $this, 'addMediaBulkAction' ] );
		add_filter( 'handle_bulk_actions-upload', [ $this, 'handleMediaBulkAction' ], 10, 3 );

		// Row action is registered only when the AIOSEO Details column is active,
		// since it emits an event that the column's Vue component handles.
		add_action( 'aioseo_details_column_activated', [ $this, 'registerMediaRowAction' ] );
	}

	/**
	 * Registers the media row action when the AIOSEO Details column is activated for attachments.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $postType The post type the column was activated for.
	 * @return void
	 */
	public function registerMediaRowAction( $postType ) {
		if ( 'attachment' === $postType ) {
			add_filter( 'media_row_actions', [ $this, 'addMediaRowAction' ], 10, 2 );
		}
	}

	/**
	 * Add the AI bulk actions to a post type list.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $bulkActions The existing bulk actions.
	 * @return array              The modified bulk actions.
	 */
	public function addBulkAction( $bulkActions ) {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) || ! aioseo()->access->hasCapability( self::REQUIRED_SAVE_CAPABILITY ) || $this->isTrashView() ) {
			return $bulkActions;
		}

		$bulkActions[ AIOSEO_PLUGIN_SHORT_NAME ] = array_map( function ( $config ) {
			return $config['label'];
		}, $this->getPostListActions() );

		return $bulkActions;
	}

	/**
	 * Add the AI bulk actions to the Media Library.
	 *
	 * @since 4.9.6
	 *
	 * @param  array $bulkActions The existing bulk actions.
	 * @return array              The modified bulk actions.
	 */
	public function addMediaBulkAction( $bulkActions ) {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) || ! aioseo()->access->hasCapability( self::REQUIRED_SAVE_CAPABILITY ) || $this->isTrashView() ) {
			return $bulkActions;
		}

		$label   = AIOSEO_PLUGIN_SHORT_NAME;
		$actions = array_map( function ( $config ) {
			return $config['label'];
		}, $this->getMediaActions() );

		$bulkActions[ $label ] = isset( $bulkActions[ $label ] )
			? array_merge( $bulkActions[ $label ], $actions )
			: $actions;

		return $bulkActions;
	}

	/**
	 * Handle a post list bulk action when triggered.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $redirectTo The redirect URL.
	 * @param  string $doAction   The action being taken.
	 * @param  array  $postIds    The array of post IDs.
	 * @return string             The redirect URL.
	 */
	public function handleBulkAction( $redirectTo, $doAction, $postIds ) {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) || ! aioseo()->access->hasCapability( self::REQUIRED_SAVE_CAPABILITY ) ) {
			return $redirectTo;
		}

		$actions = $this->getPostListActions();
		if ( ! isset( $actions[ $doAction ] ) || empty( $postIds ) ) {
			return $redirectTo;
		}

		return $this->buildRedirectUrl( $postIds, $actions[ $doAction ]['type'] );
	}

	/**
	 * Handle the Media Library bulk action when triggered.
	 *
	 * @since 4.9.6
	 *
	 * @param  string $redirectTo The redirect URL.
	 * @param  string $doAction   The action being taken.
	 * @param  array  $postIds    The array of post IDs.
	 * @return string             The redirect URL.
	 */
	public function handleMediaBulkAction( $redirectTo, $doAction, $postIds ) {
		if ( ! current_user_can( self::REQUIRED_CAPABILITY ) || ! aioseo()->access->hasCapability( self::REQUIRED_SAVE_CAPABILITY ) ) {
			return $redirectTo;
		}

		$actions = $this->getMediaActions();
		if ( ! isset( $actions[ $doAction ] ) || empty( $postIds ) ) {
			return $redirectTo;
		}

		return $this->buildRedirectUrl( $postIds, $actions[ $doAction ]['type'] );
	}

	/**
	 * Add a row action to generate alt text with AI for individual images in the Media Library.
	 *
	 * @since 4.9.6
	 *
	 * @param  array    $actions The existing row actions.
	 * @param  \WP_Post $post    The current attachment post object.
	 * @return array             The modified row actions.
	 */
	public function addMediaRowAction( $actions, $post ) {
		if (
			! current_user_can( self::REQUIRED_CAPABILITY ) ||
			! aioseo()->access->hasCapability( self::REQUIRED_SAVE_CAPABILITY ) ||
			! current_user_can( 'edit_post', $post->ID ) ||
			! aioseo()->helpers->attachmentIs( 'image', $post->ID ) ||
			! aioseo()->addons->getLoadedAddon( 'imageSeo' )
		) {
			return $actions;
		}

		$onclick = sprintf(
			'event.preventDefault(); window.aioseoBus && window.aioseoBus.$emit(\'generateAltInline%d\')',
			$post->ID
		);

		$actions['aioseo_generate_alt'] = sprintf(
			'<a href="#" onclick="%1$s">%2$s</a>',
			esc_attr( $onclick ),
			esc_html__( 'Generate Alt Text with AI', 'all-in-one-seo-pack' )
		);

		return $actions;
	}

	/**
	 * Whether the current list view is filtered to trashed posts.
	 *
	 * @since 4.9.6
	 *
	 * @return bool
	 */
	private function isTrashView() {
		// Mirrors how WordPress core determines the trash view in WP_Posts_List_Table and WP_Media_List_Table.
		// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		$postStatus       = sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ?? '' ) );
		$attachmentFilter = sanitize_text_field( wp_unslash( $_REQUEST['attachment-filter'] ?? '' ) );
		// phpcs:enable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended

		return 'trash' === $postStatus || 'trash' === $attachmentFilter;
	}

	/**
	 * Returns the post list bulk action definitions.
	 * Single source of truth for post list action keys, labels, and types.
	 *
	 * @since 4.9.6
	 *
	 * @return array[] Keyed by action slug, each with 'label' and 'type'.
	 */
	private function getPostListActions() {
		return [
			'aioseo_generate_ai_titles'       => [
				'label' => __( 'Generate SEO Titles with AI', 'all-in-one-seo-pack' ),
				'type'  => 'title'
			],
			'aioseo_generate_ai_descriptions' => [
				'label' => __( 'Generate Meta Descriptions with AI', 'all-in-one-seo-pack' ),
				'type'  => 'description'
			],
		];
	}

	/**
	 * Returns the Media Library bulk action definitions.
	 * Single source of truth for media action keys, labels, and types.
	 *
	 * @since 4.9.6
	 *
	 * @return array[] Keyed by action slug, each with 'label' and 'type'.
	 */
	private function getMediaActions() {
		return [
			'aioseo_generate_ai_alt_text' => [
				'label' => __( 'Generate Alt Text for Images with AI', 'all-in-one-seo-pack' ),
				'type'  => 'alt'
			],
		];
	}

	/**
	 * Build the redirect URL for the AI bulk generate page.
	 *
	 * @since 4.9.6
	 *
	 * @param  array  $postIds The array of post IDs.
	 * @param  string $type    The generation type (title, description, alt).
	 * @return string          The redirect URL.
	 */
	private function buildRedirectUrl( $postIds, $type ) {
		return add_query_arg(
			[
				'ids'  => implode( ',', array_map( 'intval', $postIds ) ),
				'type' => $type
			],
			admin_url( 'admin.php?page=aioseo-ai-bulk-generate' )
		);
	}
}