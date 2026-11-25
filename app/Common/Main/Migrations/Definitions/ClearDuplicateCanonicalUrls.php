<?php
namespace AIOSEO\Plugin\Common\Main\Migrations\Definitions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main\Migrations\Migration;
use AIOSEO\Plugin\Common\Models;

/**
 * Clears the canonical URL that 5.0.2 and 5.0.3 pointed at the original on Yoast Duplicate Post copies.
 *
 * NOTE: Only clears a canonical that is a URL the original (or a post it was copied from) had, on a copy
 * created since 5.0.2 whose original has no canonical of its own; any other value was set by hand.
 *
 * @since 5.0.6
 */
class ClearDuplicateCanonicalUrls implements Migration {
	/**
	 * Copies created before this (GMT) predate the auto-canonical, so their canonical was set by hand.
	 *
	 * @since 5.0.6
	 *
	 * @var string
	 */
	private $since = '2026-09-23 00:00:00';

	/**
	 * Marks the cleanup as done, since verify() runs before permalinks can be resolved.
	 *
	 * @since 5.0.6
	 *
	 * @var string
	 */
	private $doneOptionName = 'aioseo_cleared_duplicate_canonical_urls';

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function name() {
		return 'clear_duplicate_canonical_urls';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function version() {
		return '5.0.6';
	}

	/**
	 * {@inheritdoc}
	 *
	 * NOTE: get_permalink() needs the rewrite rules and post types, which aren't there during
	 * preLoad(), so the work waits for `wp_loaded`. When AIOSEO loads after that (on activation),
	 * it waits for `shutdown` instead, since the models need the services load() sets up.
	 *
	 * @since 5.0.6
	 */
	public function up() {
		if ( did_action( 'wp_loaded' ) ) {
			add_action( 'shutdown', [ $this, 'clear' ] );

			return;
		}

		add_action( 'wp_loaded', [ $this, 'clear' ] );
	}

	/**
	 * Clears the matching canonical URLs. Public because it doubles as the deferred callback.
	 *
	 * @since 5.0.6
	 *
	 * @return void
	 */
	public function clear() {
		foreach ( $this->getCandidates() as $candidate ) {
			if ( ! $this->isAutoCanonical( (int) $candidate->post_id, (int) $candidate->original_id, $candidate->canonical_url ) ) {
				continue;
			}

			$aioseoPost                = Models\Post::getPost( (int) $candidate->post_id );
			$aioseoPost->canonical_url = null;
			$aioseoPost->save();
		}

		update_option( $this->doneOptionName, time(), false );
	}

	/**
	 * Checks whether a copy's canonical URL is the permalink 5.0.2 gave it from its original.
	 *
	 * NOTE: A copy of a copy inherited the first original's URL, so every post up the `_dp_original`
	 * chain counts as the original.
	 *
	 * @since 5.0.6
	 *
	 * @param  int    $postId       The copy's ID.
	 * @param  int    $originalId   The ID of the post it was copied from.
	 * @param  string $canonicalUrl The copy's canonical URL.
	 * @return bool                 Whether it's the auto-canonical.
	 */
	private function isAutoCanonical( $postId, $originalId, $canonicalUrl ) {
		if ( ! $this->isInternalUrl( $canonicalUrl ) ) {
			return false;
		}

		$originalPost = get_post( $originalId );

		// A deleted original leaves no URL to compare, but a canonical that reaches no post points at a 404.
		// A site that opted back in may have wanted it, and its callbacks can't be given a post to decide.
		if ( ! is_object( $originalPost ) ) {
			return ! has_filter( 'aioseo_set_duplicate_canonical_url' ) &&
				$this->isPostShapedUrl( $canonicalUrl ) &&
				! url_to_postid( $canonicalUrl ) &&
				! $this->slugExists( $canonicalUrl );
		}

		// A site that opted back into the auto-canonical wants this one.
		if ( apply_filters( 'aioseo_set_duplicate_canonical_url', false, $postId, $originalPost ) ) {
			return false;
		}

		$ancestors = $this->getAncestors( $originalPost );

		// A canonical that resolves to some other post was pointed there by hand.
		$resolvedId = url_to_postid( $canonicalUrl );
		if ( $resolvedId && ! isset( $ancestors[ $resolvedId ] ) ) {
			return false;
		}

		$matched = $resolvedId ? $ancestors[ $resolvedId ] : null;
		foreach ( $ancestors as $ancestor ) {
			if ( $matched ) {
				break;
			}

			$matched = $this->matchesPost( $canonicalUrl, $ancestor ) ? $ancestor : null;
		}

		if ( ! $matched ) {
			return false;
		}

		// 5.0.2 only pointed a copy at an original without a canonical of its own.
		$aioseoAncestor = Models\Post::getPost( $matched->ID );

		return empty( $aioseoAncestor->canonical_url );
	}

	/**
	 * Returns the original and the posts it was copied from in turn, keyed by ID.
	 *
	 * @since 5.0.6
	 *
	 * @param  \WP_Post $originalPost The original post.
	 * @return array                  The posts.
	 */
	private function getAncestors( $originalPost ) {
		$ancestors = [];
		$post      = $originalPost;
		while ( is_object( $post ) && ! isset( $ancestors[ $post->ID ] ) && 10 > count( $ancestors ) ) {
			$ancestors[ $post->ID ] = $post;

			$parentId = (int) get_post_meta( $post->ID, '_dp_original', true );
			$post     = $parentId ? get_post( $parentId ) : null;
		}

		return $ancestors;
	}

	/**
	 * Checks whether a URL is one the post had: its permalink now or under an old slug, a post-shaped URL
	 * that ends in its slug or ID (still holds after a permalink structure change or once it's a draft or
	 * trashed), or one an AIOSEO redirect sends to the post.
	 *
	 * NOTE: WordPress doesn't record old slugs for hierarchical post types; the redirect covers those on Pro.
	 *
	 * @since 5.0.6
	 *
	 * @param  string   $url  The URL.
	 * @param  \WP_Post $post The post.
	 * @return bool           Whether it's the post's URL.
	 */
	private function matchesPost( $url, $post ) {
		if ( $this->isPermalink( $url, $post ) ) {
			return true;
		}

		$lastSegment = $this->getLastPathSegment( $url );
		if (
			'' !== $lastSegment &&
			(
				in_array( $lastSegment, array_map( 'rawurldecode', array_filter( $this->getSlugs( $post ) ) ), true ) ||
				(string) $post->ID === $lastSegment
			) &&
			$this->isPostShapedUrl( $url )
		) {
			return true;
		}

		return $this->redirectsToPost( $url, $post );
	}

	/**
	 * Returns the post's current slug and its old ones.
	 *
	 * @since 5.0.6
	 *
	 * @param  \WP_Post $post The post.
	 * @return array          The slugs.
	 */
	private function getSlugs( $post ) {
		return array_merge(
			[ preg_replace( '/__trashed$/', '', (string) $post->post_name ) ],
			(array) get_post_meta( $post->ID, '_wp_old_slug' )
		);
	}

	/**
	 * Checks whether a URL is the post's permalink, now or under an old slug, or its `?p=`/`?page_id=` URL.
	 *
	 * @since 5.0.6
	 *
	 * @param  string   $url  The URL.
	 * @param  \WP_Post $post The post.
	 * @return bool           Whether it's the post's permalink.
	 */
	private function isPermalink( $url, $post ) {
		$permalinks = [ get_permalink( $post ) ];
		foreach ( $this->getSlugs( $post ) as $slug ) {
			$oldPost            = clone $post;
			$oldPost->post_name = $slug;
			$permalinks[]       = get_permalink( $oldPost );
		}

		if ( in_array( untrailingslashit( $url ), array_map( 'untrailingslashit', array_filter( $permalinks ) ), true ) ) {
			return true;
		}

		$query = [];
		wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		foreach ( [ 'p', 'page_id' ] as $queryVar ) {
			if ( ! empty( $query[ $queryVar ] ) && (int) $query[ $queryVar ] === $post->ID ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether the site's rewrite rules read a URL as a single post, not a term, author, date or
	 * post type archive.
	 *
	 * NOTE: Mirrors the rule matching in url_to_postid(), which only returns IDs. A URL no rule matches,
	 * or a site on plain permalinks, can't be told apart, so it counts as post-shaped.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return bool        Whether it's post-shaped.
	 */
	private function isPostShapedUrl( $url ) {
		$wpRewrite = isset( $GLOBALS['wp_rewrite'] ) ? $GLOBALS['wp_rewrite'] : null;
		$rules     = $wpRewrite instanceof \WP_Rewrite ? $wpRewrite->wp_rewrite_rules() : [];
		if ( empty( $rules ) ) {
			return true;
		}

		$path     = (string) wp_parse_url( $url, PHP_URL_PATH );
		$homePath = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		$path     = preg_replace( '#^' . preg_quote( untrailingslashit( $homePath ), '#' ) . '#', '', $path );
		if ( ! $wpRewrite->using_index_permalinks() ) {
			$path = str_replace( $wpRewrite->index . '/', '', (string) $path );
		}

		$path = trim( (string) $path, '/' );

		$postQueryVars = [ 'p', 'page_id', 'name', 'pagename', 'attachment', 'attachment_id' ];
		foreach ( get_post_types( [], 'objects' ) as $postType ) {
			if ( ! empty( $postType->query_var ) && is_string( $postType->query_var ) ) {
				$postQueryVars[] = $postType->query_var;
			}
		}

		foreach ( $rules as $match => $query ) {
			if ( ! preg_match( "#^$match#", $path, $matches ) ) {
				continue;
			}

			$queryVars = [];
			parse_str( \WP_MatchesMapRegex::apply( preg_replace( '!^.+\?!', '', (string) $query ), $matches ), $queryVars );

			return ! empty( array_filter( array_intersect_key( $queryVars, array_flip( $postQueryVars ) ) ) );
		}

		return true;
	}

	/**
	 * Checks whether an enabled AIOSEO redirect sends a URL to the post, directly or through a few hops.
	 *
	 * NOTE: Redirects are Pro-only, so this is always false on Lite.
	 *
	 * @since 5.0.6
	 *
	 * @param  string   $url  The URL.
	 * @param  \WP_Post $post The post.
	 * @param  int      $hops The redirects followed so far.
	 * @return bool           Whether a redirect sends the URL to the post.
	 */
	private function redirectsToPost( $url, $post, $hops = 0 ) {
		if (
			5 <= $hops ||
			empty( aioseo()->redirects->redirect ) ||
			! $this->isInternalUrl( $url ) ||
			! aioseo()->core->db->tableExists( 'aioseo_redirects' )
		) {
			return false;
		}

		$path = aioseo()->helpers->excludeHomePath( rawurldecode( (string) wp_parse_url( $url, PHP_URL_PATH ) ) );
		foreach ( aioseo()->redirects->redirect->getRedirects( $path ) as $redirect ) {
			// A 4xx/5xx redirect sends nowhere, and one without a target falls back to the home page.
			if ( 400 <= (int) $redirect->type || aioseo()->redirects->helpers->isMissingTarget( $redirect ) ) {
				continue;
			}

			$targetUrl = aioseo()->redirects->redirect->buildTargetUrl( $redirect, isset( $redirect->regexMatches ) ? $redirect->regexMatches : [] );
			if (
				url_to_postid( $targetUrl ) === $post->ID ||
				$this->isPermalink( $targetUrl, $post ) ||
				$this->redirectsToPost( $targetUrl, $post, $hops + 1 )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether some existing post or term has the URL's last path segment as its slug.
	 *
	 * NOTE: Catches a URL that url_to_postid() doesn't resolve: an archive, or a post after a permalink
	 * structure change.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return bool        Whether a post or term has that slug.
	 */
	private function slugExists( $url ) {
		$lastSegment = $this->getLastPathSegment( $url );
		if ( '' === $lastSegment ) {
			return true;
		}

		$db   = aioseo()->core->db->db;
		$slug = sanitize_title( $lastSegment );

		return (bool) $db->get_var(
			$db->prepare(
				"SELECT ID
				FROM {$db->posts}
				WHERE post_name = %s
				AND post_status NOT IN ( 'inherit', 'auto-draft' )
				UNION ALL
				SELECT term_id
				FROM {$db->terms}
				WHERE slug = %s
				LIMIT 1",
				$slug,
				$slug
			)
		);
	}

	/**
	 * Returns a URL's last path segment, decoded.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return string      The segment.
	 */
	private function getLastPathSegment( $url ) {
		$segments = explode( '/', trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ) );

		return rawurldecode( (string) end( $segments ) );
	}

	/**
	 * Checks whether a URL is on this site, with or without `www.`, as url_to_postid() reads it.
	 *
	 * @since 5.0.6
	 *
	 * @param  string $url The URL.
	 * @return bool        Whether it's on this site.
	 */
	private function isInternalUrl( $url ) {
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $host ) {
			return false;
		}

		$normalize = function ( $host ) {
			return preg_replace( '/^www\./', '', strtolower( $host ) );
		};

		return $normalize( $host ) === $normalize( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 5.0.6
	 */
	public function verify() {
		if ( get_option( $this->doneOptionName ) ) {
			return true;
		}

		return empty( $this->getCandidates() );
	}

	/**
	 * Returns the copies with a canonical URL that may have come from the original's permalink.
	 *
	 * NOTE: reads INFORMATION_SCHEMA rather than tableExists(), whose cache can be stale this early.
	 *
	 * @since 5.0.6
	 *
	 * @return array
	 */
	private function getCandidates() {
		$db        = aioseo()->core->db->db;
		$tableName = $db->prefix . 'aioseo_posts';

		$tableExists = $db->get_var(
			$db->prepare(
				'SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s',
				$tableName
			)
		);

		if ( empty( $tableExists ) ) {
			return [];
		}

		return $db->get_results(
			$db->prepare(
				"SELECT ap.post_id, ap.canonical_url, pm.meta_value AS original_id
				FROM {$tableName} AS ap
				INNER JOIN {$db->postmeta} AS pm ON pm.post_id = ap.post_id AND pm.meta_key = '_dp_original'
				WHERE ap.canonical_url IS NOT NULL
				AND ap.canonical_url != ''
				AND ap.created >= %s",
				$this->since
			)
		);
	}
}