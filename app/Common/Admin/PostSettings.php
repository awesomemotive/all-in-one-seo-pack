<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class PostSettings {
	/**
	 * The integrations instance.
	 *
	 * @since 4.4.3
	 *
	 * @var array[object]
	 */
	public $integrations;

	/**
	 * Initialize the admin.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Clear the Post Type Overview cache.
		add_action( 'save_post', [ $this, 'clearPostTypeOverviewCache' ], 100 );
		add_action( 'delete_post', [ $this, 'clearPostTypeOverviewCache' ], 100 );
		add_action( 'wp_trash_post', [ $this, 'clearPostTypeOverviewCache' ], 100 );

		if ( wp_doing_ajax() || wp_doing_cron() || ! is_admin() ) {
			return;
		}

		// Load Vue APP.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueuePostSettingsAssets' ] );

		// Add metabox.
		add_action( 'add_meta_boxes', [ $this, 'addPostSettingsMetabox' ] );

		// Add metabox (upsell) to terms on init hook.
		add_action( 'init', [ $this, 'init' ], 1000 );

		// Save metabox.
		add_action( 'save_post', [ $this, 'saveSettingsMetabox' ] );
		add_action( 'edit_attachment', [ $this, 'saveSettingsMetabox' ] );
		add_action( 'add_attachment', [ $this, 'saveSettingsMetabox' ] );

		// Filter the sql clauses to show posts filtered by our params.
		add_filter( 'posts_clauses', [ $this, 'changeClausesToFilterPosts' ], 10, 2 );
	}

	/**
	 * Enqueues the JS/CSS for the on page/posts settings.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function enqueuePostSettingsAssets() {
		if (
			aioseo()->helpers->isScreenBase( 'event-espresso' ) ||
			aioseo()->helpers->isScreenBase( 'post' ) ||
			aioseo()->helpers->isScreenBase( 'term' ) ||
			aioseo()->helpers->isScreenBase( 'edit-tags' ) ||
			aioseo()->helpers->isScreenBase( 'site-editor' )
		) {
			$page = null;
			if (
				aioseo()->helpers->isScreenBase( 'event-espresso' ) ||
				aioseo()->helpers->isScreenBase( 'post' )
			) {
				$page = 'post';
			}

			aioseo()->core->assets->load( 'src/vue/standalone/post-settings/main.js', [], aioseo()->helpers->getVueData( $page ) );
			aioseo()->core->assets->load( 'src/vue/standalone/link-format/main.js', [], aioseo()->helpers->getVueData( $page ) );
		}

		$screen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $screen->id ) ) {
			return;
		}

		if ( 'attachment' === $screen->id ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Check whether or not we can add the metabox.
	 *
	 * @since 4.1.7
	 *
	 * @param  string  $postType The post type to check.
	 * @return boolean           Whether or not can add the Metabox.
	 */
	public function canAddPostSettingsMetabox( $postType ) {
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		$pageAnalysisSettingsCapability = aioseo()->access->hasCapability( 'aioseo_page_analysis' );
		$generalSettingsCapability      = aioseo()->access->hasCapability( 'aioseo_page_general_settings' );
		$socialSettingsCapability       = aioseo()->access->hasCapability( 'aioseo_page_social_settings' );
		$schemaSettingsCapability       = aioseo()->access->hasCapability( 'aioseo_page_schema_settings' );
		$linkAssistantCapability        = aioseo()->access->hasCapability( 'aioseo_page_link_assistant_settings' );
		$redirectsCapability            = aioseo()->access->hasCapability( 'aioseo_page_redirects_manage' );
		$advancedSettingsCapability     = aioseo()->access->hasCapability( 'aioseo_page_advanced_settings' );
		$seoRevisionsSettingsCapability = aioseo()->access->hasCapability( 'aioseo_page_seo_revisions_settings' );

		if (
			$dynamicOptions->searchAppearance->postTypes->has( $postType ) &&
			$dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox &&
			! (
				empty( $pageAnalysisSettingsCapability ) &&
				empty( $generalSettingsCapability ) &&
				empty( $socialSettingsCapability ) &&
				empty( $schemaSettingsCapability ) &&
				empty( $linkAssistantCapability ) &&
				empty( $redirectsCapability ) &&
				empty( $advancedSettingsCapability ) &&
				empty( $seoRevisionsSettingsCapability )
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Adds a meta box to page/posts screens.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function addPostSettingsMetabox() {
		$screen = aioseo()->helpers->getCurrentScreen();
		if ( empty( $screen->post_type ) ) {
			return;
		}

		$postType = $screen->post_type;
		if ( $this->canAddPostSettingsMetabox( $postType ) ) {
			// Translators: 1 - The plugin short name ("AIOSEO").
			$aioseoMetaboxTitle = sprintf( esc_html__( '%1$s Settings', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_SHORT_NAME );

			add_meta_box(
				'aioseo-settings',
				$aioseoMetaboxTitle,
				[ $this, 'postSettingsMetabox' ],
				[ $postType ],
				'normal',
				apply_filters( 'aioseo_post_metabox_priority', 'high' )
			);
		}
	}

	/**
	 * Render the on page/posts settings metabox with Vue App wrapper.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function postSettingsMetabox() {
		$this->postSettingsHiddenField();
		?>
		<div id="aioseo-post-settings-metabox">
			<?php aioseo()->templates->getTemplate( 'parts/loader.php' ); ?>
		</div>
		<?php
	}

	/**
	 * Adds the hidden field where all the metabox data goes.
	 *
	 * @since 4.0.17
	 *
	 * @return void
	 */
	public function postSettingsHiddenField() {
		static $fieldExists = false;
		if ( $fieldExists ) {
			return;
		}

		$fieldExists = true;

		?>
		<div id="aioseo-post-settings-field">
			<input type="hidden" name="aioseo-post-settings" id="aioseo-post-settings" value=""/>
			<?php wp_nonce_field( 'aioseoPostSettingsNonce', 'PostSettingsNonce' ); ?>
		</div>
		<?php
	}

	/**
	 * Handles metabox saving.
	 *
	 * @since 4.0.3
	 *
	 * @param  int  $postId Post ID.
	 * @return void
	 */
	public function saveSettingsMetabox( $postId ) {
		if ( ! aioseo()->helpers->isValidPost( $postId, [ 'all' ] ) ) {
			return;
		}

		// Security check.
		if ( ! isset( $_POST['PostSettingsNonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['PostSettingsNonce'] ) ), 'aioseoPostSettingsNonce' ) ) {
			return;
		}

		// If we don't have our post settings input, we can safely skip.
		if ( ! isset( $_POST['aioseo-post-settings'] ) ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $postId ) ) {
			return;
		}

		$currentPost = json_decode( wp_unslash( ( $_POST['aioseo-post-settings'] ) ), true );
		$currentPost = aioseo()->helpers->sanitize( $currentPost );

		// If there is no data, there likely was an error, e.g. if the hidden field wasn't populated on load and the user saved the post without making changes in the metabox.
		// In that case we should return to prevent a complete reset of the data.

		if ( empty( $currentPost ) ) {
			return;
		}

		Models\Post::savePost( $postId, $currentPost );
	}

	/**
	 * Clear the Post Type Overview cache from our cache table.
	 *
	 * @since 4.2.0
	 *
	 * @param  int  $postId The Post ID being updated/deleted.
	 * @return void
	 */
	public function clearPostTypeOverviewCache( $postId ) {
		$postType = get_post_type( $postId );
		if ( empty( $postType ) ) {
			return;
		}

		aioseo()->core->cache->delete( $postType . '_overview_data' );
	}

	/**
	 * Get a list of post types with an overview showing how many posts are good, okay and so on.
	 *
	 * @since 4.2.0
	 *
	 * @return array The list of post types with the overview.
	 */
	public function getPostTypesOverview() {
		$overviewData      = [];
		$eligiblePostTypes = aioseo()->helpers->getTruSeoEligiblePostTypes();
		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			if ( ! in_array( $postType, $eligiblePostTypes, true ) ) {
				continue;
			}

			$overviewData[ $postType ] = $this->getPostTypeOverview( $postType );
		}

		return $overviewData;
	}

	/**
	 * Get how many posts are good, okay, needs improvement or are missing the focus keyphrase for the given post type.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $postType The post type name.
	 * @return array            The overview data for the given post type.
	 */
	public function getPostTypeOverview( $postType ) {
		$overviewData = aioseo()->core->cache->get( $postType . '_overview_data' );
		if ( null !== $overviewData ) {
			return $overviewData;
		}

		$eligiblePostTypes = aioseo()->helpers->getTruSeoEligiblePostTypes();
		if ( ! in_array( $postType, $eligiblePostTypes, true ) ) {
			return [
				'total'               => 0,
				'withoutFocusKeyword' => 0,
				'needsImprovement'    => 0,
				'okay'                => 0,
				'good'                => 0
			];
		}

		$specialPageIds             = aioseo()->helpers->getSpecialPageIds();
		$implodedPageIdPlaceholders = array_fill( 0, count( $specialPageIds ), '%d' );
		$implodedPageIdPlaceholders = implode( ', ', $implodedPageIdPlaceholders );

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$overviewData = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) as total,
					COALESCE( SUM(CASE WHEN ap.keyphrases = '' OR ap.keyphrases IS NULL OR ap.keyphrases LIKE %s THEN 1 ELSE 0 END), 0) as withoutFocusKeyword,
					COALESCE( SUM(CASE WHEN ap.seo_score IS NULL OR ap.seo_score = 0 THEN 1 ELSE 0 END), 0) as withoutTruSeoScore,
					COALESCE( SUM(CASE WHEN ap.seo_score > 0 AND ap.seo_score < 50 THEN 1 ELSE 0 END), 0) as needsImprovement,
					COALESCE( SUM(CASE WHEN ap.seo_score BETWEEN 50 AND 79 THEN 1 ELSE 0 END), 0) as okay,
					COALESCE( SUM(CASE WHEN ap.seo_score >= 80 THEN 1 ELSE 0 END), 0) as good
				FROM {$wpdb->posts} as p
				LEFT JOIN {$wpdb->prefix}aioseo_posts as ap ON ap.post_id = p.ID
				WHERE p.post_status = 'publish'
				AND p.post_type = %s
				AND p.ID NOT IN ( $implodedPageIdPlaceholders )",
				'{"focus":{"keyphrase":""%',
				$postType,
				...array_values( $specialPageIds )
			),
			ARRAY_A
		);

		// Ensure sure all the values are integers.
		foreach ( $overviewData as $key => $value ) {
			$overviewData[ $key ] = (int) $value;
		}

		// Give me the raw SQL of the query.
		aioseo()->core->cache->update( $postType . '_overview_data', $overviewData, HOUR_IN_SECONDS );

		return $overviewData;
	}

	/**
	 * Change the JOIN and WHERE clause to filter just the posts we need to show depending on the query string.
	 *
	 * @since 4.2.0
	 *
	 * @param  array     $clauses Associative array of the clauses for the query.
	 * @param  \WP_Query $query   The WP_Query instance (passed by reference).
	 * @return array              The clauses array updated.
	 */
	public function changeClausesToFilterPosts( $clauses, $query = null ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $clauses;
		}

		$filter = filter_input( INPUT_GET, 'aioseo-filter' );
		if ( empty( $filter ) ) {
			return $clauses;
		}

		$whereClause        = '';
		$noKeyphrasesClause = "(aioseo_p.keyphrases = '' OR aioseo_p.keyphrases IS NULL OR aioseo_p.keyphrases LIKE '{\"focus\":{\"keyphrase\":\"\"%')";
		switch ( $filter ) {
			case 'withoutFocusKeyword':
				$whereClause = " AND $noKeyphrasesClause ";
				break;
			case 'withoutTruSeoScore':
				$whereClause = ' AND ( aioseo_p.seo_score IS NULL OR aioseo_p.seo_score = 0 ) ';
				break;
			case 'needsImprovement':
				$whereClause = ' AND ( aioseo_p.seo_score > 0 AND aioseo_p.seo_score < 50 ) ';
				break;
			case 'okay':
				$whereClause = ' AND aioseo_p.seo_score BETWEEN 50 AND 80 ';
				break;
			case 'good':
				$whereClause = ' AND aioseo_p.seo_score > 80 ';
				break;
		}

		$prefix            = aioseo()->core->db->prefix;
		$postsTable        = aioseo()->core->db->db->posts;
		$clauses['join']  .= " LEFT JOIN {$prefix}aioseo_posts AS aioseo_p ON ({$postsTable}.ID = aioseo_p.post_id) ";
		$clauses['where'] .= $whereClause;

		add_action( 'wp', [ $this, 'filterPostsAfterChangingClauses' ] );

		return $clauses;
	}

	/**
	 * Filter the posts array to remove the ones that are not eligible for page analysis.
	 * Hooked into `wp` action hook.
	 *
	 * @since 4.7.1
	 *
	 * @return void
	 */
	public function filterPostsAfterChangingClauses() {
		remove_action( 'wp', [ $this, 'filterPostsAfterChangingClauses' ] );
		// phpcs:disable Squiz.NamingConventions.ValidVariableName
		global $wp_query;
		if ( ! empty( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
			$wp_query->posts = array_filter( $wp_query->posts, function ( $post ) {
				return aioseo()->helpers->isTruSeoEligible( $post->ID );
			} );

			// Update `post_count` for pagination.
			if ( isset( $wp_query->post_count ) ) {
				$wp_query->post_count = count( $wp_query->posts );
			}
		}
		// phpcs:enable Squiz.NamingConventions.ValidVariableName
	}
}