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

		// Add metabox to terms on init hook.
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
		if ( ! isset( $_POST['PostSettingsNonce'] ) || ! wp_verify_nonce( $_POST['PostSettingsNonce'], 'aioseoPostSettingsNonce' ) ) {
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

		$currentPost = json_decode( stripslashes( $_POST['aioseo-post-settings'] ), true ); // phpcs:ignore HM.Security.ValidatedSanitizedInput

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
		$postTypes      = [];
		$dynamicOptions = aioseo()->dynamicOptions->noConflict();

		foreach ( aioseo()->helpers->getPublicPostTypes( true ) as $postType ) {
			if (
				! $dynamicOptions->searchAppearance->postTypes->has( $postType ) ||
				! $dynamicOptions->searchAppearance->postTypes->$postType->show ||
				! $dynamicOptions->searchAppearance->postTypes->$postType->advanced->showMetaBox ||
				'attachment' === $postType ||
				aioseo()->helpers->isBBPressPostType( $postType )
			) {
				continue;
			}

			$postTypes[ $postType ] = $this->getPostTypeOverview( $postType );
		}

		return $postTypes;
	}

	/**
	 * Get how many posts are good, okay, needs improvement or are missing the focus keyphrase for the given post type.
	 *
	 * @since 4.2.0
	 *
	 * @param  string $postType The post type name.
	 * @return array            The overview for the given post type.
	 */
	public function getPostTypeOverview( $postType ) {
		$overview = aioseo()->core->cache->get( $postType . '_overview_data' );
		if ( null !== $overview ) {
			return $overview;
		}

		$posts = aioseo()->core->db->start( 'posts as p' )
			->select( 'p.ID, ap.seo_score, ap.keyphrases' )
			->leftJoin( 'aioseo_posts as ap', 'ap.post_id = p.ID' )
			->where( 'p.post_status', 'publish' )
			->where( 'p.post_type', $postType )
			->run()
			->result();

		$overview = [
			'total'                 => 0,
			'needsImprovement'      => 0,
			'okay'                  => 0,
			'good'                  => 0,
			'withoutFocusKeyphrase' => 0,
		];

		foreach ( $posts as $post ) {
			if ( ! aioseo()->helpers->isPageAnalysisEligible( $post->ID ) ) {
				continue;
			}

			$overview['total']++;

			if ( empty( $post->keyphrases ) || strpos( $post->keyphrases, '{"focus":{"keyphrase":""' ) === 0 ) {
				$overview['withoutFocusKeyphrase']++;

				// We skip to the next since we will just consider posts with focus keyphrase in the counts.
				continue;
			}

			if ( 50 > $post->seo_score ) {
				$overview['needsImprovement']++;
				continue;
			}

			if ( 50 <= $post->seo_score && 80 >= $post->seo_score ) {
				$overview['okay']++;
				continue;
			}

			if ( 80 < $post->seo_score ) {
				$overview['good']++;
			}
		}

		aioseo()->core->cache->update( $postType . '_overview_data', $overview, HOUR_IN_SECONDS );

		return $overview;
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
			case 'withoutFocusKeyphrase':
				$whereClause = " AND $noKeyphrasesClause ";
				break;
			case 'needsImprovement':
				$whereClause = " AND aioseo_p.seo_score < 50 AND NOT $noKeyphrasesClause ";
				break;
			case 'okay':
				$whereClause = " AND aioseo_p.seo_score BETWEEN 50 AND 80 AND NOT $noKeyphrasesClause ";
				break;
			case 'good':
				$whereClause = " AND aioseo_p.seo_score > 80 AND NOT $noKeyphrasesClause ";
				break;
		}

		$prefix            = aioseo()->core->db->prefix;
		$postsTable        = aioseo()->core->db->db->posts;
		$clauses['join']  .= " LEFT JOIN {$prefix}aioseo_posts AS aioseo_p ON ({$postsTable}.ID = aioseo_p.post_id) ";
		$clauses['where'] .= $whereClause;

		return $clauses;
	}
}