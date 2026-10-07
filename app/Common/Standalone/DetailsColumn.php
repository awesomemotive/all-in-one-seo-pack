<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Handles the AIOSEO Details post column.
 *
 * @since 4.2.0
 */
class DetailsColumn {
	/**
	 * The slug for the script.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	protected $scriptSlug = 'src/vue/standalone/posts-table/main.js';

	/**
	 * Class constructor.
	 *
	 * @since 4.2.0
	 */
	public function __construct() {
		// Quick Edit saves over AJAX, where registerColumnHooks() bails because the screen
		// has no post type, so this can't live there. It self-guards on the marker field
		// that only our Quick Edit / Bulk Edit fieldsets submit.
		add_action( 'save_post', [ $this, 'saveRobotsFields' ] );

		if ( wp_doing_ajax() ) {
			add_action( 'init', [ $this, 'addPostColumnsAjax' ], 1 );
		}

		if ( ! is_admin() || wp_doing_cron() ) {
			return;
		}

		add_action( 'current_screen', [ $this, 'registerColumnHooks' ], 1 );
	}

	/**
	 * Adds the columns to the page/post types.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function registerColumnHooks() {
		$screen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $screen->base ) || empty( $screen->post_type ) ) {
			return;
		}

		if ( ! $this->shouldRegisterColumn( $screen->base, $screen->post_type ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );

		if ( 'product' === $screen->post_type ) {
			add_filter( 'manage_edit-product_columns', [ $this, 'addColumn' ] );
			add_action( 'manage_posts_custom_column', [ $this, 'renderColumn' ], 10, 2 );
		} elseif ( 'attachment' === $screen->post_type ) {
			$enabled = apply_filters( 'aioseo_image_seo_media_columns', true );
			if ( ! $enabled ) {
				return;
			}

			add_filter( 'manage_media_columns', [ $this, 'addColumn' ] );
			add_action( 'manage_media_custom_column', [ $this, 'renderColumn' ], 10, 2 );
		} else {
			add_filter( "manage_edit-{$screen->post_type}_columns", [ $this, 'addColumn' ] );
			add_action( "manage_{$screen->post_type}_posts_custom_column", [ $this, 'renderColumn' ], 10, 2 );
		}

		// Both hooks fire once per non-core column, so registering the column above is
		// what makes these available. Attachments have no robots meta of their own.
		if ( 'attachment' !== $screen->post_type ) {
			add_action( 'quick_edit_custom_box', [ $this, 'renderRobotsQuickEditFields' ] );
			add_action( 'bulk_edit_custom_box', [ $this, 'renderRobotsBulkEditFields' ] );
		}

		/**
		 * Fires after the AIOSEO Details column is activated for a post type.
		 * Use this to register features that depend on the column being present (e.g., row actions).
		 *
		 * @since 4.9.6
		 *
		 * @param string $postType The post type the column was activated for.
		 */
		do_action( 'aioseo_details_column_activated', $screen->post_type );
	}

	/**
	 * Registers our post columns after a post has been quick-edited.
	 *
	 * @since 4.2.3
	 *
	 * @return void
	 */
	public function addPostColumnsAjax() {
		if (
			! isset( $_POST['_inline_edit'], $_POST['post_ID'], $_POST['aioseo-has-details-column'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_inline_edit'] ) ), 'inlineeditnonce' )
		) {
			return;
		}

		$postId = (int) $_POST['post_ID'];
		if ( ! $postId ) {
			return;
		}

		$post     = get_post( $postId );
		$postType = $post->post_type;

		add_filter( "manage_edit-{$postType}_columns", [ $this, 'addColumn' ] );
		add_action( "manage_{$postType}_posts_custom_column", [ $this, 'renderColumn' ], 10, 2 );
	}

	/**
	 * Renders the robots meta fields in the Quick Edit form.
	 *
	 * NOTE: Fires once as a hidden template for the whole table, not per row, so the
	 * current values are copied in by JS from each row when the form is opened.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $columnName The column name.
	 * @return void
	 */
	public function renderRobotsQuickEditFields( $columnName ) {
		if ( 'aioseo-details' !== $columnName || ! current_user_can( 'aioseo_page_general_settings' ) ) {
			return;
		}

		$this->renderRobotsFieldset( false );
	}

	/**
	 * Renders the robots meta fields in the Bulk Edit form.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $columnName The column name.
	 * @return void
	 */
	public function renderRobotsBulkEditFields( $columnName ) {
		if ( 'aioseo-details' !== $columnName || ! current_user_can( 'aioseo_page_general_settings' ) ) {
			return;
		}

		$this->renderRobotsFieldset( true );
	}

	/**
	 * Outputs the shared robots fieldset for the Quick Edit and Bulk Edit forms.
	 *
	 * @since 5.0.0
	 *
	 * @param  bool $isBulk Whether this is the Bulk Edit form.
	 * @return void
	 */
	private function renderRobotsFieldset( $isBulk ) {
		// Both are consumed by the required view below.
		$prefix = $isBulk ? 'aioseo-bulk' : 'aioseo-quick'; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		require AIOSEO_DIR . '/app/Common/Views/admin/posts/robots-inline-edit.php';
	}

	/**
	 * Saves the robots meta fields submitted from the Quick Edit or Bulk Edit form.
	 *
	 * @since 5.0.0
	 *
	 * @param  int  $postId The post ID.
	 * @return void
	 */
	public function saveRobotsFields( $postId ) {
		// Saving a post spawns a revision, which fires save_post again with the revision's
		// ID while the marker and nonce are still in the request — without this we'd write
		// an aioseo_posts row for the revision too.
		if ( ! aioseo()->helpers->isValidPost( $postId, [ 'all' ] ) ) {
			return;
		}

		// The marker is only submitted by the two fieldsets we render, which keeps this
		// off autosaves, REST writes, cron and every other save_post caller.
		$isQuickEdit = isset( $_REQUEST['aioseo-quick-robots-submitted'] ); // phpcs:ignore HM.Security.NonceVerification.Recommended
		$isBulkEdit  = isset( $_REQUEST['aioseo-bulk-robots-submitted'] ); // phpcs:ignore HM.Security.NonceVerification.Recommended
		if ( ! $isQuickEdit && ! $isBulkEdit ) {
			return;
		}

		// Core verifies these before save_post runs in the Quick Edit and Bulk Edit flows,
		// but save_post also fires from wp_insert_post() elsewhere, where a stray marker in
		// the request would otherwise be enough to reach the write below.
		$nonce       = $isBulkEdit ? '_wpnonce' : '_inline_edit';
		$nonceAction = $isBulkEdit ? 'bulk-posts' : 'inlineeditnonce';
		if (
			! isset( $_REQUEST[ $nonce ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $nonce ] ) ), $nonceAction )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $postId ) || ! current_user_can( 'aioseo_page_general_settings' ) ) {
			return;
		}

		// Special pages take their robots meta from Search Appearance, so writing a per-post
		// override would be ignored. Bulk Edit can't hide the control per row, so it's
		// enforced here as well as in the Quick Edit form.
		if ( aioseo()->helpers->isSpecialPage( $postId ) ) {
			return;
		}

		$prefix  = $isBulkEdit ? 'aioseo-bulk' : 'aioseo-quick';
		$default = isset( $_REQUEST[ $prefix . '-robots-default' ] )
			? sanitize_text_field( wp_unslash( $_REQUEST[ $prefix . '-robots-default' ] ) )
			: '';

		// Bulk Edit defaults every control to "no change" so it never clobbers posts the
		// user did not intend to touch.
		if ( $isBulkEdit && '' === $default ) {
			return;
		}

		$useDefault = $isBulkEdit ? ( 'default' === $default ) : ! empty( $default );
		$noindex    = ! empty( $_REQUEST[ $prefix . '-robots-noindex' ] );
		$nofollow   = ! empty( $_REQUEST[ $prefix . '-robots-nofollow' ] );

		// Route through savePost() rather than setting fields and calling save() directly:
		// it fires aioseo_insert_post, which is what spawns the SEO revision, and it syncs
		// the traditional post meta the multilingual plugins read. It patches, so sending
		// only the robots fields leaves the title and description untouched. The keys are the
		// API names from getSanitizeFieldMap(), not the column names — a column name isn't in
		// the map, so it would be dropped without an error.
		Models\Post::savePost( $postId, [
			'default'  => $useDefault,
			'noindex'  => $useDefault ? false : $noindex,
			'nofollow' => $useDefault ? false : $nofollow
		] );
	}

	/**
	 * Enqueues the JS/CSS for the page/posts table page.
	 *
	 * @since   4.0.0
	 * @version 4.9.10 Strip site-global configuration for users who cannot manage AIOSEO.
	 * @version 5.0.2 Resolves the batch scan gate through {@see isBatchScanEnabled()}.
	 *
	 * @return void
	 */
	public function enqueueScripts() {
		$screen   = aioseo()->helpers->getCurrentScreen();
		$postType = ! empty( $screen->post_type ) ? $screen->post_type : '';

		$data          = aioseo()->helpers->getVueData();
		$data['posts'] = [];
		$data['terms'] = [];

		// Lets Quick Edit recompute the column's noindex badge after a save without a
		// round trip: a post on defaults resolves the same way for its whole post type.
		$data['postTypeIsNoindexed'] = $postType ? $this->isPostTypeNoindexed( $postType ) : false;

		// Nouns for anything on the list that names what it is working on, e.g. the batch scan's
		// "Successfully analyzed 3 categories." Taken from the registered labels so a term list
		// doesn't say "posts" and a product list doesn't either.
		$data['objectNouns'] = $this->getScreenObjectNouns( $screen, $postType );

		$data['batchScanEnabled']     = $this->isBatchScanEnabled();
		$data['batchScanConcurrency'] = apply_filters( 'aioseo_truseo_batch_scan_concurrency', 3, $postType );
		$data['batchScanStartDelay']  = apply_filters( 'aioseo_truseo_batch_scan_start_delay', 2000, $postType ); // milliseconds

		aioseo()->core->assets->load( $this->scriptSlug, [], aioseo()->helpers->filterPrivilegedVueData( $data ) );
	}

	/**
	 * Whether the list table's batch scan will run on the current screen and put a TruSEO score back.
	 *
	 * NOTE: The single source of truth for that question. Anything destructive that relies on the
	 * scan to recompute what it clears must gate on this, or it strands the score.
	 *
	 * @since 5.0.2
	 *
	 * @return bool Whether the batch scan is enabled on the current screen.
	 */
	public function isBatchScanEnabled() {
		$screen = aioseo()->helpers->getCurrentScreen();

		// Off a list-table screen there is nothing to scan. Bail before the filter rather than
		// handing callbacks a `false` screen and an empty post type, which they have never had to
		// guard against.
		if ( ! is_a( $screen, 'WP_Screen' ) ) {
			return false;
		}

		$postType = ! empty( $screen->post_type ) ? $screen->post_type : '';

		return (bool) apply_filters(
			'aioseo_truseo_batch_scan_enabled',
			$this->isBatchScanSupported( $screen, $postType ),
			$postType,
			$screen
		);
	}

	/**
	 * Whether this screen is one the batch scan supports, before the filter gets a say.
	 *
	 * @since 5.0.2
	 *
	 * @param  \WP_Screen $screen   The current screen.
	 * @param  string     $postType The current post type.
	 * @return bool                 Whether the screen supports the batch scan.
	 */
	protected function isBatchScanSupported( $screen, $postType ) {
		// The scan ships inside the Details column's own bundle, so whatever keeps the column from
		// registering keeps the scan from ever starting — the capability and the include-list both.
		if ( ! $this->isColumnRegistered( $screen ) ) {
			return false;
		}

		// Otherwise the list would run (and persist) analysis for a disabled feature.
		if ( ! aioseo()->options->advanced->truSeo ) {
			return false;
		}

		// Term lists back the same scan, gated on the taxonomy being TruSEO-eligible rather than on
		// the post type, which a term screen doesn't have.
		if ( 'edit-tags' === $screen->base ) {
			return ! empty( $screen->taxonomy ) &&
				in_array( $screen->taxonomy, aioseo()->helpers->getTruSeoEligibleTaxonomies(), true );
		}

		// Only post list pages, not the media library. The Trash view lists only trashed posts,
		// which aren't editable content, so skip it too.
		// Eligibility rather than just the master switch: a post type the user excluded from TruSEO
		// must not be scanned from its list either, or unscored rows get analysed and persisted behind
		// the user's back. It also covers the never-analysable types (attachments, web stories).
		return 'edit' === $screen->base &&
			! aioseo()->helpers->isTrashListView() &&
			in_array( $postType, aioseo()->helpers->getTruSeoEligiblePostTypes(), true );
	}

	/**
	 * Whether the AIOSEO Details column is registered on the given screen.
	 *
	 * NOTE: Resolves the same decision {@see registerColumnHooks()} makes, so callers outside the
	 * column can tell whether its scripts were ever enqueued.
	 *
	 * @since 5.0.2
	 *
	 * @param  \WP_Screen $screen The current screen.
	 * @return bool               Whether the column is registered.
	 */
	protected function isColumnRegistered( $screen ) {
		if ( empty( $screen->base ) || empty( $screen->post_type ) ) {
			return false;
		}

		return $this->shouldRegisterColumn( $screen->base, $screen->post_type );
	}

	/**
	 * Returns the nouns describing what the current list screen holds, e.g. "category"/"categories".
	 *
	 * @since 5.0.1
	 *
	 * @param  \WP_Screen|null $screen   The current screen.
	 * @param  string          $postType The current post type.
	 * @return array                     The singular and plural nouns.
	 */
	private function getScreenObjectNouns( $screen, $postType ) {
		return ! empty( $screen->taxonomy )
			? aioseo()->helpers->getTaxonomyContentNouns( $screen->taxonomy )
			: aioseo()->helpers->getPostTypeContentNouns( $postType );
	}

	/**
	 * Adds the AIOSEO Details column to the page/post tables in the admin.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $columns The columns we are adding ours onto.
	 * @return array          The modified columns.
	 */
	public function addColumn( $columns ) {
		$canManageSeo = apply_filters( 'aioseo_manage_seo', 'aioseo_manage_seo' );
		if (
			! current_user_can( $canManageSeo ) &&
			(
				! current_user_can( 'aioseo_page_general_settings' ) &&
				! current_user_can( 'aioseo_page_analysis' )
			)
		) {
			return $columns;
		}

		// Translators: 1 - The short plugin name ("AIOSEO").
		$columns['aioseo-details'] = sprintf( esc_html__( '%1$s Details', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );

		return $columns;
	}

	/**
	 * Renders the column in the page/post table.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $columnName The column name.
	 * @param  int    $postId     The current rows, post id.
	 * @return void
	 */
	public function renderColumn( $columnName, $postId = 0 ) {
		if ( ! current_user_can( 'edit_post', $postId ) && ! current_user_can( 'aioseo_manage_seo' ) ) {
			return;
		}

		if ( 'aioseo-details' !== $columnName ) {
			return;
		}

		// Add this column/post to the localized array.
		global $wp_scripts; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		if (
			! is_object( $wp_scripts ) || // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			! method_exists( $wp_scripts, 'get_data' ) || // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			! method_exists( $wp_scripts, 'add_data' ) // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		) {
			return;
		}

		$data = null;
		if ( is_object( $wp_scripts ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$data = $wp_scripts->get_data( 'aioseo/js/' . $this->scriptSlug, 'data' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		}

		if ( ! is_array( $data ) ) {
			$data = json_decode( str_replace( 'var aioseo = ', '', substr( $data, 0, -1 ) ), true );
		}

		// We have to temporarily modify the query here since the query incorrectly identifies
		// the current page as a category page when posts are filtered by a specific category.
		// phpcs:disable Squiz.NamingConventions.ValidVariableName
		global $wp_query;
		$originalQuery         = clone $wp_query;
		$wp_query->is_category = false;
		$wp_query->is_tag      = false;
		$wp_query->is_tax      = false;
		// phpcs:enable Squiz.NamingConventions.ValidVariableName

		$posts    = ! empty( $data['posts'] ) ? $data['posts'] : [];
		$postData = $this->getPostData( $postId, $columnName );

		$addonsColumnData = array_filter( aioseo()->addons->doAddonFunction( 'admin', 'renderColumnData', [
			$columnName,
			$postId,
			$postData
		] ) );

		$wp_query = $originalQuery; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		foreach ( $addonsColumnData as $addonColumnData ) {
			$postData = array_merge( $postData, $addonColumnData );
		}

		// Get broken link count if Broken Link Checker is active.
		if (
			function_exists( 'aioseoBrokenLinkChecker' ) &&
			class_exists( '\AIOSEO\BrokenLinkChecker\Models\LinkStatus' ) &&
			method_exists( '\AIOSEO\BrokenLinkChecker\Models\LinkStatus', 'getBrokenCountByPostId' ) &&
			'aioseo-details' === $columnName
		) {
			$brokenCount                 = \AIOSEO\BrokenLinkChecker\Models\LinkStatus::getBrokenCountByPostId( $postId );
			$postData['brokenLinkCount'] = (int) $brokenCount ?? 0;

			// Older versions of the plugin report the count but can't scope their report to a post.
			if ( method_exists( '\AIOSEO\BrokenLinkChecker\Models\LinkStatus', 'getBrokenReportUrl' ) ) {
				$postData['brokenLinkUrl'] = \AIOSEO\BrokenLinkChecker\Models\LinkStatus::getBrokenReportUrl( $postId );
			}
		}

		$posts[]       = $postData;
		$data['posts'] = $posts;

		$wp_scripts->add_data( 'aioseo/js/' . $this->scriptSlug, 'data', '' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		wp_localize_script( 'aioseo/js/' . $this->scriptSlug, 'aioseo', $data );

		require AIOSEO_DIR . '/app/Common/Views/admin/posts/columns.php';
	}

	/**
	 * Gets the post data for the column.
	 *
	 * @since 4.5.0
	 *
	 * @param  int    $postId     The Post ID.
	 * @param  string $columnName The column name.
	 * @return array              The post data.
	 */
	protected function getPostData( $postId, $columnName ) {
		$nonce    = wp_create_nonce( "aioseo_meta_{$columnName}_{$postId}" );
		$thePost  = Models\Post::getPost( $postId );
		$postType = get_post_type( $postId );

		$postData = [
			'id'                 => $postId,
			'columnName'         => $columnName,
			'nonce'              => $nonce,
			'title'              => $thePost->title,
			'defaultTitle'       => aioseo()->meta->title->getPostTypeTitle( $postType ),
			'showTitle'          => apply_filters( 'aioseo_details_column_post_show_title', true, $postId ),
			'description'        => $thePost->description,
			'defaultDescription' => aioseo()->meta->description->getPostTypeDescription( $postType ),
			'showDescription'    => apply_filters( 'aioseo_details_column_post_show_description', true, $postId ),
			'value'              => ! empty( $thePost->seo_score ) ? (int) $thePost->seo_score : 0,
			// Only posts TruSEO actually analyses show a score. Excluding a post type used to hide just
			// the Optimization tab, leaving a stale score sitting in this column with no way to refresh
			// it. Mirrors what the term column does.
			'showScore'          => aioseo()->helpers->isTruSeoEligible( $postId ),
			'showMedia'          => false,
			'isSpecialPage'      => aioseo()->helpers->isSpecialPage( $postId ),
			'postType'           => $postType,
			'isPostVisible'      => aioseo()->helpers->isPostPubliclyViewable( $postId ),
			'isNoindexed'        => $this->isNoindexed( $thePost, $postType ),
			// Effective state above drives the column badge; the raw post-level values
			// below populate the Quick Edit form, which edits the override itself.
			'robots'             => [
				'default'  => (bool) $thePost->robots_default,
				'noindex'  => (bool) $thePost->robots_noindex,
				'nofollow' => (bool) $thePost->robots_nofollow
			]
		];

		return $postData;
	}

	/**
	 * Returns whether the post ends up noindexed, using its own override when it has
	 * one and otherwise deferring to the post type default.
	 *
	 * NOTE: Deliberately does not use {@see \AIOSEO\Plugin\Common\Meta\Robots::post()}.
	 * That accumulates into a shared instance property and mixes in request context
	 * (pagination, front page), both of which break when looping over a list table.
	 *
	 * @since 5.0.0
	 *
	 * @param  Models\Post $thePost  The AIOSEO post object.
	 * @param  string      $postType The post type.
	 * @return bool                  Whether the post is noindexed.
	 */
	private function isNoindexed( $thePost, $postType ) {
		if ( ! $thePost->robots_default ) {
			return (bool) $thePost->robots_noindex;
		}

		return $this->isPostTypeNoindexed( $postType );
	}

	/**
	 * Returns whether a post type noindexes by default, falling back to the global
	 * robots meta when the post type defers to it.
	 *
	 * @since 5.0.0
	 *
	 * @param  string $postType The post type.
	 * @return bool             Whether the post type is noindexed by default.
	 */
	private function isPostTypeNoindexed( $postType ) {
		// Traverse from the same object we probe with has(), mirroring
		// {@see \AIOSEO\Plugin\Common\Meta\Robots::globalValues()} — a post type can be
		// registered without an `advanced` group, so every hop has to be guarded.
		$options = aioseo()->dynamicOptions->noConflict( true )->searchAppearance;
		if ( ! $options->has( 'postTypes', false ) ) {
			return false;
		}

		$options = $options->postTypes;
		if ( ! $options->has( $postType, false ) ) {
			return false;
		}

		$options = $options->$postType;

		// A post type excluded from search results is noindexed regardless of its robots meta.
		// Read `show` off a clone, like globalValues() does: __get() on a leaf calls
		// resetGroups(), which would discard the traversal the lookup below still needs.
		$clonedOptions = clone $options;
		if ( ! $clonedOptions->show ) {
			return true;
		}

		$globalRobotsMeta = aioseo()->options->searchAppearance->advanced->globalRobotsMeta->all();

		// Probe with has() rather than a chained isset(): the latter runs both __isset()
		// and __get(), each pushing the sub-group, so the traversal lands on
		// `advanced.advanced` and the post type's own robots meta is never found.
		$hasRobotsMeta = $options->has( 'advanced', false );
		if ( $hasRobotsMeta ) {
			$options       = $options->advanced;
			$hasRobotsMeta = $options->has( 'robotsMeta', false );
		}

		if ( ! $hasRobotsMeta ) {
			$robotsMeta = $globalRobotsMeta;
		} else {
			$robotsMeta = $options->robotsMeta->all();
			if ( ! empty( $robotsMeta['default'] ) ) {
				$robotsMeta = $globalRobotsMeta;
			}
		}

		if ( ! empty( $robotsMeta['default'] ) ) {
			return false;
		}

		return ! empty( $robotsMeta['noindex'] );
	}

	/**
	 * Checks whether the AIOSEO Details column should be registered.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the column should be registered.
	 */
	public function shouldRegisterColumn( $screen, $postType ) {
		// Only allow users with the correct permissions to see the column.
		if ( ! current_user_can( 'aioseo_page_general_settings' ) ) {
			return false;
		}

		if ( 'type' === $postType ) {
			$postType = '_aioseo_type';
		}

		if ( 'edit' === $screen || 'upload' === $screen ) {
			if (
				aioseo()->options->advanced->postTypes->all &&
				in_array( $postType, aioseo()->helpers->getPublicPostTypes( true ), true )
			) {
				return true;
			}

			$postTypes = aioseo()->options->advanced->postTypes->included;
			if ( in_array( $postType, $postTypes, true ) ) {
				return true;
			}
		}

		return false;
	}
}