<?php
namespace AIOSEO\Plugin\Common\Meta;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Instantiates the meta links "next" and "prev".
 *
 * @since 4.0.0
 */
class Links {
	/**
	 * Get the prev/next links for the current page.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of link data.
	 */
	public function getLinks() {
		$links = [
			'prev' => '',
			'next' => '',
		];

		if ( is_home() || is_archive() || is_paged() ) {
			$links = $this->getHomeLinks();
		}

		if ( is_page() || is_single() ) {
			global $post;
			$links = $this->getPostLinks( $post );
		}

		$links['prev'] = apply_filters( 'aioseo_prev_link', $links['prev'] );
		$links['next'] = apply_filters( 'aioseo_next_link', $links['next'] );

		return $links;
	}

	/**
	 * Get the prev/next links for the current page (home/archive, etc.).
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of link data.
	 */
	private function getHomeLinks() {
		$prev = '';
		$next = '';
		$page = aioseo()->helpers->getPageNumber();

		global $wp_query; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$maxPage = $wp_query->max_num_pages; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		if ( $page > 1 ) {
			$prev = get_previous_posts_page_link();
		}
		if ( $page < $maxPage ) {
			$next  = get_next_posts_page_link();
			$paged = is_paged();
			if ( ! is_single() ) {
				if ( ! $paged ) {
					$page = 1;
				}
				$nextpage = intval( $page ) + 1;
				if ( ! $maxPage || $maxPage >= $nextpage ) {
					$next = get_pagenum_link( $nextpage );
				}
			}
		}

		// Remove trailing slashes if not set in the permalink structure.
		$prev = aioseo()->helpers->maybeRemoveTrailingSlash( $prev );
		$next = aioseo()->helpers->maybeRemoveTrailingSlash( $next );

		// Remove any query args that may be set on the URL, except if the site is using plain permalinks.
		$permalinkStructure = get_option( 'permalink_structure' );
		if ( ! empty( $permalinkStructure ) ) {
			$prev = explode( '?', $prev )[0];
			$next = explode( '?', $next )[0];
		}

		return [
			'prev' => $prev,
			'next' => $next,
		];
	}

	/**
	 * Get the prev/next links for the current post.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_Post $post The post.
	 * @return array          An array of link data.
	 */
	private function getPostLinks( $post ) {
		$prev     = '';
		$next     = '';
		$numpages = 1;
		$page     = aioseo()->helpers->getPageNumber();
		$content  = is_a( $post, 'WP_Post' ) ? $post->post_content : '';
		if ( false !== strpos( $content, '<!--nextpage-->', 0 ) ) {
			$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
			$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
			$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
			// Ignore nextpage at the beginning of the content.
			if ( 0 === strpos( $content, '<!--nextpage-->', 0 ) ) {
				$content = substr( $content, 15 );
			}
			$pages    = explode( '<!--nextpage-->', $content );
			$numpages = count( $pages );
		} else {
			$page = null;
		}
		if ( ! empty( $page ) ) {
			if ( $page > 1 ) {
				$prev = $this->getLinkPage( $page - 1 );
			}
			if ( $page + 1 <= $numpages ) {
				$next = $this->getLinkPage( $page + 1 );
			}
		}

		return [
			'prev' => $prev,
			'next' => $next,
		];
	}

	/**
	 * This is a clone of _wp_link_page, except that we don't output HTML.
	 *
	 * @since 4.0.0
	 *
	 * @param  integer $number The page number.
	 * @return string          The URL.
	 */
	private function getLinkPage( $number ) {
		global $wp_rewrite; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$post      = get_post();
		$queryArgs = [];

		if ( 1 === (int) $number ) {
			$url = get_permalink();
		} else {
			if ( ! get_option( 'permalink_structure' ) || in_array( $post->post_status, [ 'draft', 'pending' ], true ) ) {
				$url = add_query_arg( 'page', $number, get_permalink() );
			} elseif ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) === $post->ID ) {
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $number, 'single_paged' ); // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			} else {
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( $number, 'single_paged' );
			}
		}

		if ( is_preview() ) {
			// phpcs:disable HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended	
			if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
				$queryArgs['preview_id']    = sanitize_text_field( wp_unslash( $_GET['preview_id'] ) );
				$queryArgs['preview_nonce'] = sanitize_text_field( wp_unslash( $_GET['preview_nonce'] ) );
			}
			// phpcs:enable

			$url = get_preview_post_link( $post, $queryArgs, $url );
		}

		return esc_url( $url );
	}
}