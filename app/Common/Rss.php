<?php
namespace AIOSEO\Plugin\Common;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds content before or after posts in the RSS feed.
 *
 * @since 4.0.0
 */
class Rss {
	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			return;
		}

		add_filter( 'the_content_feed', [ $this, 'addRssContent' ] );
		add_filter( 'the_excerpt_rss', [ $this, 'addRssContentExcerpt' ] );

		// If advanced RSS settings are not enabled, return early.
		if ( ! aioseo()->options->searchAppearance->advanced->crawlCleanup->enable ) {
			return;
		}

		// Control which feed links are visible.
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		add_action( 'wp_head', [ $this, 'rssFeedLinks' ], 3 );

		if ( ! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->global ) {
			add_filter( 'feed_links_show_posts_feed', '__return_false' );
		}

		if ( ! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->globalComments ) {
			add_filter( 'feed_links_show_comments_feed', '__return_false' );
		}

		// Disable feeds that we no longer want on this site.
		add_action( 'wp', [ $this, 'disableFeeds' ], -1000 );
	}

	/**
	 * Adds content before or after the RSS excerpt.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The post excerpt.
	 * @return string          The post excerpt with prepended/appended content.
	 */
	public function addRssContentExcerpt( $content ) {
		return $this->addRssContent( $content, 'excerpt' );
	}

	/**
	 * Adds content before or after the RSS post.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $content The post content.
	 * @param  string $type    Type of feed.
	 * @return string          The post content with prepended/appended content.
	 */
	public function addRssContent( $content, $type = 'complete' ) {
		$content = trim( $content );
		if ( empty( $content ) ) {
			return '';
		}

		if ( is_feed() ) {
			global $wp_query;
			$isHome = is_home();
			if ( $isHome ) {
				// If this feed is for the static blog page, we must temporarily set "is_home" to false.
				// Otherwise any getPost() calls will return the blog page object for every post in the feed.
				$wp_query->is_home = false;
			}

			$before = aioseo()->tags->replaceTags( aioseo()->options->rssContent->before, get_the_ID() );
			$after  = aioseo()->tags->replaceTags( aioseo()->options->rssContent->after, get_the_ID() );

			if ( $before || $after ) {
				if ( 'excerpt' === $type ) {
					$content = wpautop( $content );
				}
				$content = aioseo()->helpers->decodeHtmlEntities( $before ) . $content . aioseo()->helpers->decodeHtmlEntities( $after );
			}

			// Set back to the original value.
			$wp_query->is_home = $isHome;
		}

		return $content;
	}

	/**
	 * Disable feeds we don't want to have on this site.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function disableFeeds() {
		// This should only run if we are trying to parse a feed.
		if ( ! is_feed() ) {
			return;
		}

		$rssFeed = get_query_var( 'feed' );
		$homeUrl = get_home_url();

		// Atom feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->atom &&
			'atom' === $rssFeed
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// RDF/RSS 1.0 feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->rdf &&
			'rdf' === $rssFeed
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Global feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->global &&
			[ 'feed' => 'feed' ] === $GLOBALS['wp_query']->query
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Global comments feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->globalComments &&
			is_comment_feed() &&
			! ( is_singular() || is_attachment() )
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Static blog page feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->staticBlogPage &&
			aioseo()->helpers->getBlogPageId() === get_queried_object_id()
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Post comment feeds.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->postComments &&
			is_comment_feed() &&
			is_singular()
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Attachment feeds.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->attachments &&
			'feed' === $rssFeed &&
			get_query_var( 'attachment', false )
		) {
			$this->redirectRssFeed( $homeUrl );
		}

		// Author feeds.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->authors &&
			is_author()
		) {
			$this->redirectRssFeed( get_author_posts_url( (int) get_query_var( 'author' ) ) );
		}

		// Search results feed.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->search &&
			is_search()
		) {
			$this->redirectRssFeed( esc_url( trailingslashit( $homeUrl ) . '?s=' . get_search_query() ) );
		}

		// All post types.
		$archives = aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->archives->included;
		$postType = $this->getTheQueriedPostType();
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->archives->all &&
			! in_array( $postType, $archives, true ) &&
			is_post_type_archive()
		) {
			$this->redirectRssFeed( get_post_type_archive_link( $postType ) );
		}

		// All taxonomies.
		$taxonomies = aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->included;
		$term       = get_queried_object();
		if (
			$term &&
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->all &&
			! in_array( $term->taxonomy, $taxonomies, true ) &&
			(
				is_category() ||
				is_tag() ||
				is_tax()
			)
		) {
			$termUrl = get_term_link( $term, $term->taxonomy );
			if ( is_wp_error( $termUrl ) ) {
				$termUrl = $homeUrl;
			}

			$this->redirectRssFeed( $termUrl );
		}

		// Paginated feed pages. This one is last since we are using a regular expression to validate.
		if (
			! aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->paginated &&
			preg_match( '/(\d+\/|(?<=\/)page\/\d+\/)$/', $_SERVER['REQUEST_URI'] )
		) {
			$this->redirectRssFeed( $homeUrl );
		}
	}

	/**
	 * Get the currently queried post type.
	 *
	 * @since 4.2.1
	 *
	 * @return string The queried post type.
	 */
	private function getTheQueriedPostType() {
		$postType = get_query_var( 'post_type' );
		if ( is_array( $postType ) ) {
			$postType = reset( $postType );
		}

		return $postType;
	}

	/**
	 * Redirect the feed to the appropriate URL.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	private function redirectRssFeed( $url ) {
		if ( empty( $url ) ) {
			return;
		}

		// Set or remove headers.
		header_remove( 'Content-Type' );
		header_remove( 'Last-Modified' );
		header_remove( 'Expires' );

		$cache = 'public, max-age=604800, s-maxage=604800, stale-while-revalidate=120, stale-if-error=14400';
		if ( is_user_logged_in() ) {
			$cache = 'private, max-age=0';
		}

		header( 'Cache-Control: ' . $cache, true );

		wp_safe_redirect( $url, 301, AIOSEO_PLUGIN_SHORT_NAME );
	}

	/**
	 * Rewrite the RSS feed links.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args The arguments to filter.
	 * @return void
	 */
	public function rssFeedLinks( $args ) {
		$defaults = [
			// Translators: Separator between blog name and feed type in feed links.
			'separator'     => _x( '-', 'feed link', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Post title.
			'singletitle'   => __( '%1$s %2$s %3$s Comments Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Category name.
			'cattitle'      => __( '%1$s %2$s %3$s Category Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Tag name.
			'tagtitle'      => __( '%1$s %2$s %3$s Tag Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Term name, 4: Taxonomy singular name.
			'taxtitle'      => __( '%1$s %2$s %3$s %4$s Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Author name.
			'authortitle'   => __( '%1$s %2$s Posts by %3$s Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Search query.
			'searchtitle'   => __( '%1$s %2$s Search Results for &#8220;%3$s&#8221; Feed', 'all-in-one-seo-pack' ),
			// Translators: 1 - Blog name, 2 - Separator (raquo), 3 - Post type name.
			'posttypetitle' => __( '%1$s %2$s %3$s Feed', 'all-in-one-seo-pack' ),
		];

		$args       = wp_parse_args( $args, $defaults );
		$attributes = [
			'title' => null,
			'href'  => null
		];

		if (
			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->postComments &&
			is_singular()
		) {
			$attributes = $this->getPostCommentsAttributes( $args );
		}

		$archives = aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->archives->included;
		$postType = $this->getTheQueriedPostType();
		if (
			(
				aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->archives->all ||
				in_array( $postType, $archives, true )
			) &&
			is_post_type_archive()
		) {
			$attributes = $this->getPostTypeArchivesAttributes( $args );
		}

		// All taxonomies.
		$taxonomies = aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->included;
		$term       = get_queried_object();
		if (
			$term &&
			isset( $term->taxonomy ) &&
			(
				aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->taxonomies->all ||
				in_array( $term->taxonomy, $taxonomies, true )
			) &&
			(
				is_category() ||
				is_tag() ||
				is_tax()
			)
		) {
			$attributes = $this->getTaxonomiesAttributes( $args, $term );
		}

		if (
			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->authors &&
			is_author()
		) {
			$attributes = $this->getAuthorAttributes( $args );
		}

		if (
			aioseo()->options->searchAppearance->advanced->crawlCleanup->feeds->search &&
			is_search()
		) {
			$attributes = $this->getSearchAttributes( $args );
		}

		if ( ! empty( $attributes['title'] ) && ! empty( $attributes['href'] ) ) {
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr( $attributes['title'] ) . '" href="' . esc_url( $attributes['href'] ) . '" />' . "\n";
		}
	}

	/**
	 * Retrieve the attributes for post comments feed.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args An array of arguments.
	 * @return array       An array of attributes.
	 */
	private function getPostCommentsAttributes( $args ) {
		$id    = 0;
		$post  = get_post( $id );
		$title = null;
		$href  = null;

		if (
			comments_open() ||
			pings_open() ||
			0 < $post->comment_count
		) {
			$title = sprintf(
				$args['singletitle'],
				get_bloginfo( 'name' ),
				$args['separator'],
				the_title_attribute( [ 'echo' => false ] )
			);

			$href = get_post_comments_feed_link( $post->ID );
		}

		return [
			'title' => $title,
			'href'  => $href
		];
	}

	/**
	 * Retrieve the attributes for post type archives feed.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args An array of arguments.
	 * @return array       An array of attributes.
	 */
	private function getPostTypeArchivesAttributes( $args ) {
		$postTypeObject = get_post_type_object( $this->getQueriedPostType() );
		$title          = sprintf( $args['posttypetitle'], get_bloginfo( 'name' ), $args['separator'], $postTypeObject->labels->name );
		$href           = get_post_type_archive_feed_link( $postTypeObject->name );

		return [
			'title' => $title,
			'href'  => $href
		];
	}

	/**
	 * Retrieve the attributes for taxonomies feed.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args    An array of arguments.
	 * @param  \WP_Term $term The term.
	 * @return array          An array of attributes.
	 */
	private function getTaxonomiesAttributes( $args, $term ) {
		$title = null;
		$href  = null;

		if ( is_category() ) {
			$title = sprintf( $args['cattitle'], get_bloginfo( 'name' ), $args['separator'], $term->name );
			$href  = get_category_feed_link( $term->term_id );
		}

		if ( is_tag() ) {
			$title = sprintf( $args['tagtitle'], get_bloginfo( 'name' ), $args['separator'], $term->name );
			$href  = get_tag_feed_link( $term->term_id );
		}

		if ( is_tax() ) {
			$tax   = get_taxonomy( $term->taxonomy );
			$title = sprintf( $args['taxtitle'], get_bloginfo( 'name' ), $args['separator'], $term->name, $tax->labels->singular_name );
			$href  = get_term_feed_link( $term->term_id, $term->taxonomy );
		}

		return [
			'title' => $title,
			'href'  => $href
		];
	}

	/**
	 * Retrieve the attributes for the author feed.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args An array of arguments.
	 * @return array       An array of attributes.
	 */
	private function getAuthorAttributes( $args ) {
		$authorId = (int) get_query_var( 'author' );
		$title    = sprintf( $args['authortitle'], get_bloginfo( 'name' ), $args['separator'], get_the_author_meta( 'display_name', $authorId ) );
		$href     = get_author_feed_link( $authorId );

		return [
			'title' => $title,
			'href'  => $href
		];
	}

	/**
	 * Retrieve the attributes for the search feed.
	 *
	 * @since 4.2.1
	 *
	 * @param  array $args An array of arguments.
	 * @return array       An array of attributes.
	 */
	private function getSearchAttributes( $args ) {
		$title = sprintf( $args['searchtitle'], get_bloginfo( 'name' ), $args['separator'], get_search_query( false ) );
		$href  = get_search_feed_link();

		return [
			'title' => $title,
			'href'  => $href
		];
	}

	/**
	 * Get the currently queried post type.
	 *
	 * @since 4.2.1
	 *
	 * @return string The currently queried post type.
	 */
	private function getQueriedPostType() {
		$postType = get_query_var( 'post_type' );
		if ( is_array( $postType ) ) {
			$postType = reset( $postType );
		}

		return $postType;
	}
}