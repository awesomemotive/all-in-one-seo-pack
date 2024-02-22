<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all context related helper methods.
 * This includes methods to check the context of the current request, but also get WP objects.
 *
 * @since 4.1.4
 */
trait WpContext {
	/**
	 * The original main query.
	 *
	 * @since 4.3.0
	 *
	 * @var \WP_Query
	 */
	public $originalQuery;

	/**
	 * The original main post variable.
	 *
	 * @since 4.3.0
	 *
	 * @var \WP_Post
	 */
	public $originalPost;

	/**
	 * Get the home page object.
	 *
	 * @since 4.1.1
	 *
	 * @return \WP_Post|null The home page.
	 */
	public function getHomePage() {
		$homePageId = $this->getHomePageId();

		return $homePageId ? get_post( $homePageId ) : null;
	}

	/**
	 * Get the ID of the home page.
	 *
	 * @since 4.0.0
	 *
	 * @return int|null The home page ID.
	 */
	public function getHomePageId() {
		$pageShowOnFront = ( 'page' === get_option( 'show_on_front' ) );
		$pageOnFrontId   = get_option( 'page_on_front' );

		return $pageShowOnFront && $pageOnFrontId ? (int) $pageOnFrontId : null;
	}

	/**
	 * Returns the blog page.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_Post|null The blog page.
	 */
	public function getBlogPage() {
		$blogPageId = $this->getBlogPageId();

		return $blogPageId ? get_post( $blogPageId ) : null;
	}

	/**
	 * Gets the current blog page id if it's configured.
	 *
	 * @since 4.1.1
	 *
	 * @return int|null
	 */
	public function getBlogPageId() {
		$pageShowOnFront = ( 'page' === get_option( 'show_on_front' ) );
		$blogPageId      = (int) get_option( 'page_for_posts' );

		return $pageShowOnFront && $blogPageId ? $blogPageId : null;
	}

	/**
	 * Checks whether the current page is a taxonomy term archive.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the current page is a taxonomy term archive.
	 */
	public function isTaxTerm() {
		$object = get_queried_object();

		return $object instanceof \WP_Term;
	}

	/**
	 * Checks whether the current page is a static one.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the current page is a static one.
	 */
	public function isStaticPage() {
		return $this->isStaticHomePage() || $this->isStaticPostsPage() || $this->isWooCommerceShopPage();
	}

	/**
	 * Checks whether the current page is the static homepage.
	 *
	 * @since 4.0.0
	 *
	 * @param  mixed $post Pass in an optional post to check if its the static home page.
	 * @return bool        Whether the current page is the static homepage.
	 */
	public function isStaticHomePage( $post = null ) {
		static $isHomePage = null;
		if ( null !== $isHomePage ) {
			return $isHomePage;
		}

		$post = aioseo()->helpers->getPost( $post );

		$isHomePage = ( 'page' === get_option( 'show_on_front' ) && ! empty( $post->ID ) && (int) get_option( 'page_on_front' ) === $post->ID );

		return $isHomePage;
	}

	/**
	 * Checks whether the current page is the dynamic homepage.
	 *
	 * @since 4.2.3
	 *
	 * @return bool Whether the current page is the dynamic homepage.
	 */
	public function isDynamicHomePage() {
		return is_front_page() && is_home();
	}

	/**
	 * Checks whether the current page is the static posts page.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the current page is the static posts page.
	 */
	public function isStaticPostsPage( $post = null ) {
		static $isStaticPostsPage = null;
		if ( null !== $isStaticPostsPage ) {
			return $isStaticPostsPage;
		}

		$post = aioseo()->helpers->getPost( $post );

		$isStaticPostsPage = (
			( is_home() && ( 0 !== (int) get_option( 'page_for_posts' ) ) ) ||
			( ! empty( $post->ID ) && (int) get_option( 'page_for_posts' ) === $post->ID )
		);

		return $isStaticPostsPage;
	}

	/**
	 * Checks whether current page supports meta.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the current page supports meta.
	 */
	public function supportsMeta() {
		return ! is_date() && ! is_author() && ! is_search() && ! is_404();
	}

	/**
	 * Returns the current post object.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|int|bool $postId The post ID.
	 * @return \WP_Post|null             The post object.
	 */
	public function getPost( $postId = false ) {
		$postId = is_a( $postId, 'WP_Post' ) ? $postId->ID : $postId;

		if ( aioseo()->helpers->isWooCommerceShopPage( $postId ) ) {
			return get_post( wc_get_page_id( 'shop' ) );
		}

		if ( is_front_page() || is_home() ) {
			$showOnFront = 'page' === get_option( 'show_on_front' );
			if ( $showOnFront ) {
				if ( is_front_page() ) {
					$pageOnFront = (int) get_option( 'page_on_front' );

					return get_post( $pageOnFront );
				} elseif ( is_home() ) {
					$pageForPosts = (int) get_option( 'page_for_posts' );

					return get_post( $pageForPosts );
				}
			}
		}

		// Learnpress lessons load the course. So here we need to switch to the lesson.
		$learnPressLesson = aioseo()->helpers->getLearnPressLesson();
		if ( ! $postId && $learnPressLesson ) {
			$postId = $learnPressLesson;
		}

		// Allow other plugins to filter the post ID e.g. for a special archive page.
		$postId = apply_filters( 'aioseo_get_post_id', $postId );

		// We need to check these conditions and cannot always return get_post() because we'll return the first post on archive pages (dynamic homepage, term pages, etc.).
		if (
			$this->isScreenBase( 'post' ) ||
			$postId ||
			is_singular()
		) {
			return get_post( $postId );
		}

		return null;
	}

	/**
	 * Returns the current post ID.
	 *
	 * @since 4.3.1
	 *
	 * @return int|null The post ID.
	 */
	public function getPostId() {
		$post = $this->getPost();

		return is_object( $post ) && property_exists( $post, 'ID' ) ? $post->ID : null;
	}

	/**
	 * Returns the post content after parsing it.
	 *
	 * @since 4.1.5
	 *
	 * @param  \WP_Post|int $post The post (optional).
	 * @return string             The post content.
	 */
	public function getPostContent( $post = null ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : $this->getPost( $post );

		static $content = [];
		if ( isset( $content[ $post->ID ] ) ) {
			return $content[ $post->ID ];
		}

		// We need to process the content for page builders.
		$postContent = $post->post_content;
		$pageBuilder = aioseo()->helpers->getPostPageBuilderName( $post->ID );
		if ( ! empty( $pageBuilder ) ) {
			$postContent = aioseo()->standalone->pageBuilderIntegrations[ $pageBuilder ]->processContent( $post->ID, $postContent );
		}

		$content[ $post->ID ] = $this->theContent( $postContent );

		if ( apply_filters( 'aioseo_description_include_custom_fields', true, $post ) ) {
			$content[ $post->ID ] .= $this->theContent( $this->getPostCustomFieldsContent( $post ) );
		}

		return $content[ $post->ID ];
	}

	/**
	 * Gets the content from configured custom fields.
	 *
	 * @since 4.2.7
	 *
	 * @param  \WP_Post|int $post A post object or ID.
	 * @return string             The content.
	 */
	public function getPostCustomFieldsContent( $post = null ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : $this->getPost( $post );

		if ( ! aioseo()->dynamicOptions->searchAppearance->postTypes->has( $post->post_type ) ) {
			return '';
		}

		$customFieldKeys = aioseo()->dynamicOptions->searchAppearance->postTypes->{$post->post_type}->customFields;
		if ( empty( $customFieldKeys ) ) {
			return '';
		}

		$customFieldKeys = explode( ' ', sanitize_text_field( $customFieldKeys ) );

		return aioseo()->helpers->getCustomFieldsContent( $post, $customFieldKeys );
	}

	/**
	 * Returns the post content after parsing shortcodes and blocks.
	 * We avoid using the "the_content" hook because it breaks stuff if we call it outside the loop or main query.
	 * See https://developer.wordpress.org/reference/hooks/the_content/
	 *
	 * @since 4.1.5.2
	 *
	 * @param  string $postContent The post content.
	 * @return string              The parsed post content.
	 */
	public function theContent( $postContent ) {
		if ( ! aioseo()->options->searchAppearance->advanced->runShortcodes ) {
			return $postContent;
		}

		// Because do_blocks() and do_shortcodes() can trigger conflicts, we need to clone these objects and restore them afterwards.
		// We need to clone deep to sever pointers/references because these have nested object properties.
		global $wp_query, $post;
		$this->originalQuery = $this->deepClone( $wp_query );
		$this->originalPost  = is_a( $post, 'WP_Post' ) ? $this->deepClone( $post ) : null;

		// The order of the function calls below is intentional and should NOT change.
		$postContent = do_blocks( $postContent );
		$postContent = wpautop( $postContent );
		$postContent = $this->doShortcodes( $postContent );

		$this->restoreWpQuery();

		return $postContent;
	}

	/**
	 * Returns the description based on the post content.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post|int $post The post (optional).
	 * @return string             The description.
	 */
	public function getDescriptionFromContent( $post = null ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : $this->getPost( $post );

		static $content = [];
		if ( isset( $content[ $post->ID ] ) ) {
			return $content[ $post->ID ];
		}

		$content[ $post->ID ] = '';
		if ( ! empty( $post->post_password ) ) {
			return $content[ $post->ID ];
		}

		$postContent = $this->getPostContent( $post );
		// Strip images, captions and WP oembed wrappers (e.g. YouTube URLs) from the post content.
		$postContent          = preg_replace( '/(<figure.*?\/figure>|<img.*?\/>|<div.*?class="wp-block-embed__wrapper".*?>.*?<\/div>)/s', '', $postContent );
		$postContent          = str_replace( ']]>', ']]&gt;', $postContent );
		$postContent          = trim( wp_strip_all_tags( strip_shortcodes( $postContent ) ) );
		$content[ $post->ID ] = wp_trim_words( $postContent, 55, '' );

		return $content[ $post->ID ];
	}

	/**
	 * Returns custom fields as a string.
	 *
	 * @since 4.0.6
	 *
	 * @param  \WP_Post|int $post The post.
	 * @param  array        $keys The post meta_keys to check for values.
	 * @return string             The custom field content.
	 */
	public function getCustomFieldsContent( $post = null, $keys = [] ) {
		$post = is_a( $post, 'WP_Post' ) ? $post : $this->getPost( $post );

		$customFieldContent = '';

		$acfFields = $this->getAcfContent( $post );
		foreach ( $keys as $key ) {
			// Try ACF.
			if ( isset( $acfFields[ $key ] ) ) {
				$customFieldContent .= "$acfFields[$key] ";
				continue;
			}

			// Fallback to post meta.
			$value = get_post_meta( $post->ID, $key, true );
			if ( $value && is_scalar( $value ) ) {
				$customFieldContent .= $value . ' ';
			}
		}

		return $customFieldContent;
	}

	/**
	 * Returns if the page is a special type (WooCommerce pages, Privacy page).
	 *
	 * @since 4.0.0
	 *
	 * @param  int  $postId The post ID.
	 * @return bool         If the page is special or not.
	 */
	public function isSpecialPage( $postId = 0 ) {
		if (
			(int) get_option( 'page_for_posts' ) === (int) $postId ||
			(int) get_option( 'wp_page_for_privacy_policy' ) === (int) $postId ||
			$this->isBuddyPressPage( $postId ) ||
			$this->isWooCommercePage( $postId )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the page number of the current page.
	 *
	 * @since 4.0.0
	 *
	 * @return int The page number.
	 */
	public function getPageNumber() {
		$page = get_query_var( 'page' );
		if ( ! empty( $page ) ) {
			return (int) $page;
		}

		$paged = get_query_var( 'paged' );
		if ( ! empty( $paged ) ) {
			return (int) $paged;
		}

		return 1;
	}


	/**
	 * Returns the page number for the comment page.
	 *
	 * @since 4.2.1
	 *
	 * @return int|false The page number or false if we're not on a comment page.
	 */
	public function getCommentPageNumber() {
		$cpage = get_query_var( 'cpage', null );
		if ( $this->isBlockTheme() ) {
			global $wp_query;

			// For block themes we can't rely on `get_query_var()` because of {@see build_comment_query_vars_from_block()},
			// so we need to check the query directly.
			$cpage = $wp_query->query['cpage'] ?? null;
		}

		return isset( $cpage ) ? (int) $cpage : false;
	}

	/**
	 * Check if the post passed in is a valid post, not a revision or autosave.
	 *
	 * @since 4.0.5
	 *
	 * @param  \WP_Post $post                The Post object to check.
	 * @param  array    $allowedPostStatuses Allowed post statuses.
	 * @return bool                          True if valid, false if not.
	 */
	public function isValidPost( $post, $allowedPostStatuses = [ 'publish' ] ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		if ( ! is_object( $post ) ) {
			$post = get_post( $post );
		}

		// No post, no go.
		if ( empty( $post ) ) {
			return false;
		}

		// In order to prevent recursion, we are skipping scheduled-action posts and revisions.
		if (
			'scheduled-action' === $post->post_type ||
			'revision' === $post->post_type
		) {
			return false;
		}

		// Ensure this post has the proper post status.
		if (
			! in_array( $post->post_status, $allowedPostStatuses, true ) &&
			! in_array( 'all', $allowedPostStatuses, true )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks whether the given URL is a valid attachment.
	 *
	 * @since 4.0.13
	 *
	 * @param  string $url The URL.
	 * @return bool        Whether the URL is a valid attachment.
	 */
	public function isValidAttachment( $url ) {
		$uploadDirUrl = aioseo()->helpers->escapeRegex( $this->getWpContentUrl() );

		return preg_match( "/$uploadDirUrl.*/", $url );
	}

	/**
	 * Tries to convert an attachment URL into a post ID.
	 *
	 * This our own optimized version of attachment_url_to_postid().
	 *
	 * @since 4.0.13
	 *
	 * @param  string   $url The attachment URL.
	 * @return int|bool      The attachment ID or false if no attachment could be found.
	 */
	public function attachmentUrlToPostId( $url ) {
		$cacheName = 'attachment_url_to_post_id_' . sha1( "aioseo_attachment_url_to_post_id_$url" );

		$cachedId = aioseo()->core->cache->get( $cacheName );
		if ( $cachedId ) {
			return 'none' !== $cachedId && is_numeric( $cachedId ) ? (int) $cachedId : false;
		}

		$path          = $url;
		$uploadDirInfo = wp_get_upload_dir();

		$siteUrl   = wp_parse_url( $uploadDirInfo['url'] );
		$imagePath = wp_parse_url( $path );

		// Force the protocols to match if needed.
		if ( isset( $imagePath['scheme'] ) && ( $imagePath['scheme'] !== $siteUrl['scheme'] ) ) {
			$path = str_replace( $imagePath['scheme'], $siteUrl['scheme'], $path );
		}

		if ( ! $this->isValidAttachment( $path ) ) {
			aioseo()->core->cache->update( $cacheName, 'none' );

			return false;
		}

		if ( 0 === strpos( $path, $uploadDirInfo['baseurl'] . '/' ) ) {
			$path = substr( $path, strlen( $uploadDirInfo['baseurl'] . '/' ) );
		}

		$results = aioseo()->core->db->start( 'postmeta' )
			->select( 'post_id' )
			->where( 'meta_key', '_wp_attached_file' )
			->where( 'meta_value', $path )
			->limit( 1 )
			->run()
			->result();

		if ( empty( $results[0]->post_id ) ) {
			aioseo()->core->cache->update( $cacheName, 'none' );

			return false;
		}

		aioseo()->core->cache->update( $cacheName, $results[0]->post_id );

		return $results[0]->post_id;
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 * This function was copied from WooCommerce and improved.
	 *
	 * @since 4.1.2
	 *
	 * @return bool True if this is a REST API request.
	 */
	public function isRestApiRequest() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		global $wp_rewrite;

		if ( empty( $wp_rewrite ) ) {
			return false;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$restUrl = wp_parse_url( get_rest_url() );
		$restUrl = $restUrl['path'] . ( ! empty( $restUrl['query'] ) ? '?' . $restUrl['query'] : '' );

		$isRestApiRequest = ( 0 === strpos( $_SERVER['REQUEST_URI'], $restUrl ) );

		return apply_filters( 'aioseo_is_rest_api_request', $isRestApiRequest );
	}

	/**
	 * Checks whether the current request is an AJAX, CRON or REST request.
	 *
	 * @since 4.1.3
	 *
	 * @return bool Wether the request is an AJAX, CRON or REST request.
	 */
	public function isAjaxCronRestRequest() {
		return wp_doing_ajax() || wp_doing_cron() || $this->isRestApiRequest();
	}

	/**
	 * Check if we are in the middle of a WP-CLI call.
	 *
	 * @since 4.2.8
	 *
	 * @return bool True if we are in the WP_CLI context.
	 */
	public function isDoingWpCli() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Checks whether we're on the given screen.
	 *
	 * @since   4.0.7
	 * @version 4.3.1
	 *
	 * @param  string $screenName The screen name.
	 * @param  string $comparison Check as a prefix.
	 * @return bool               Whether we're on the given screen.
	 */
	public function isScreenBase( $screenName, $comparison = '' ) {
		$screen = $this->getCurrentScreen();
		if ( ! $screen || ! isset( $screen->base ) ) {
			return false;
		}

		if ( 'prefix' === $comparison ) {
			return 0 === stripos( $screen->base, $screenName );
		}

		return $screen->base === $screenName;
	}

	/**
	 * Returns if current screen is of a post type
	 *
	 * @since 4.0.17
	 *
	 * @param  string $postType Post type slug
	 * @return bool             True if the current screen is a post type screen.
	 */
	public function isScreenPostType( $postType ) {
		$screen = $this->getCurrentScreen();
		if ( ! $screen || ! isset( $screen->post_type ) ) {
			return false;
		}

		return $screen->post_type === $postType;
	}

	/**
	 * Returns if current screen is a post list, optionaly of a post type.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $postType Post type slug.
	 * @return bool             Is a post list.
	 */
	public function isScreenPostList( $postType = '' ) {
		$screen = $this->getCurrentScreen();
		if (
			! $this->isScreenBase( 'edit' ) ||
			empty( $screen->post_type )
		) {
			return false;
		}

		if ( ! empty( $postType ) && $screen->post_type !== $postType ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns if current screen is a post edit screen, optionaly of a post type.
	 *
	 * @since 4.2.4
	 *
	 * @param  string $postType Post type slug.
	 * @return bool             Is a post editing screen.
	 */
	public function isScreenPostEdit( $postType = '' ) {
		$screen = $this->getCurrentScreen();
		if (
			! $this->isScreenBase( 'post' ) ||
			empty( $screen->post_type )
		) {
			return false;
		}

		if ( ! empty( $postType ) && $screen->post_type !== $postType ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets current admin screen.
	 *
	 * @since 4.0.17
	 *
	 * @return false|\WP_Screen|null
	 */
	public function getCurrentScreen() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		return get_current_screen();
	}

	/**
	 * Checks whether the current site is a multisite subdomain.
	 *
	 * @since 4.1.9
	 *
	 * @return bool Whether the current site is a subdomain.
	 */
	public function isSubdomain() {
		if ( ! is_multisite() ) {
			return false;
		}

		return apply_filters( 'aioseo_multisite_subdomain', is_subdomain_install() );
	}

	/**
	 * Returns if the current page is the login or register page.
	 *
	 * @since 4.2.1
	 *
	 * @return bool Login or register page.
	 */
	public function isWpLoginPage() {
		// We can't sanitize the filename using sanitize_file_name() here because it will cause issues with custom login pages and certain plugins/themes where this function is not defined.
		$self = ! empty( $_SERVER['PHP_SELF'] ) ? wp_unslash( $_SERVER['PHP_SELF'] ) : ''; // phpcs:ignore HM.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( preg_match( '/wp-login\.php$|wp-register\.php$/', $self ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns which type of WordPress page we're seeing.
	 * It will only work if {@see \WP_Query::$queried_object} has been set.
	 *
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#filter-hierarchy
	 *
	 * @since 4.2.8
	 *
	 * @return string|null The template type or `null` if no match.
	 */
	public function getTemplateType() {
		static $type = null;

		if ( ! empty( $type ) ) {
			return $type;
		}

		if ( is_attachment() ) {
			$type = 'attachment';
		} elseif ( is_single() ) {
			$type = 'single';
		} elseif (
			is_page() ||
			$this->isStaticPostsPage() ||
			$this->isWooCommerceShopPage()
		) {
			$type = 'page';
		} elseif ( is_author() ) { // An author page is an archive page, so it needs to be checked before `is_archive()`.
			$type = 'author';
		} elseif (
			is_tax() ||
			is_category() ||
			is_tag()
		) { // A taxonomy term page is an archive page, so it needs to be checked before `is_archive()`.
			$type = 'taxonomy';
		} elseif ( is_date() ) { // A date page is an archive page, so it needs to be checked before `is_archive()`.
			$type = 'date';
		} elseif ( is_archive() ) {
			$type = 'archive';
		} elseif ( is_home() && is_front_page() ) {
			$type = 'dynamic_home';
		} elseif ( is_search() ) {
			$type = 'search';
		}

		return $type;
	}

	/**
	 * Sets the given post as the queried object of the main query.
	 *
	 * @since 4.3.0
	 *
	 * @param  \WP_Post|int $wpPost The post object or ID.
	 * @return void
	 */
	public function setWpQueryPost( $wpPost ) {
		$wpPost = is_a( $wpPost, 'WP_Post' ) ? $wpPost : get_post( $wpPost );

		global $wp_query, $post;
		$this->originalQuery = $this->deepClone( $wp_query );
		$this->originalPost  = is_a( $post, 'WP_Post' ) ? $this->deepClone( $post ) : null;

		$wp_query->posts                 = [ $wpPost ];
		$wp_query->post                  = $wpPost;
		$wp_query->post_count            = 1;
		$wp_query->get_queried_object_id = (int) $wpPost->ID;
		$wp_query->queried_object        = $wpPost;
		$wp_query->is_single             = true;
		$wp_query->is_singular           = true;

		if ( 'page' === $wpPost->post_type ) {
			$wp_query->is_page = true;
		}

		$post = $wpPost;
	}

	/**
	 * Restores the main query back to the original query.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	public function restoreWpQuery() {
		global $wp_query, $post;
		if ( is_a( $this->originalQuery, 'WP_Query' ) ) {
			// Loop over all properties and replace the ones that have changed.
			// We want to avoid replacing the entire object because it can cause issues with other plugins.
			foreach ( $this->originalQuery as $key => $value ) {
				if ( $value !== $wp_query->{$key} ) {
					$wp_query->{$key} = $value;
				}
			}
		}

		if ( is_a( $this->originalPost, 'WP_Post' ) ) {
			foreach ( $this->originalPost as $key => $value ) {
				if ( $value !== $post->{$key} ) {
					$post->{$key} = $value;
				}
			}
		}

		$this->originalQuery = null;
		$this->originalPost  = null;
	}

	/**
	 * Gets the list of theme features.
	 *
	 * @since 4.4.9
	 *
	 * @return array List of theme features.
	 */
	public function getThemeFeatures() {
		global $_wp_theme_features;

		return isset( $_wp_theme_features ) && is_array( $_wp_theme_features ) ? $_wp_theme_features : [];
	}

	/**
	 * Returns whether the active theme is a block-based theme or not.
	 *
	 * @since 4.5.3
	 *
	 * @return bool Whether the active theme is a block-based theme or not.
	 */
	public function isBlockTheme() {
		if ( function_exists( 'wp_is_block_theme' ) ) {
			return wp_is_block_theme(); // phpcs:ignore AIOSEO.WpFunctionUse.NewFunctions.wp_is_block_themeFound
		}

		return false;
	}
}