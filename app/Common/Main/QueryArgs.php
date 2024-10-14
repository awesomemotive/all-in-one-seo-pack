<?php
namespace AIOSEO\Plugin\Common\Main;

use AIOSEO\Plugin\Common\Models\CrawlCleanupLog;
use AIOSEO\Plugin\Common\Models\CrawlCleanupBlockedArg;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query arguments class.
 *
 * @since   4.2.1
 * @version 4.5.8
 */
class QueryArgs {
	/**
	 * Construct method.
	 *
	 * @since 4.2.1
	 */
	public function __construct() {
		if (
			is_admin() ||
			aioseo()->helpers->isWpLoginPage() ||
			aioseo()->helpers->isAjaxCronRestRequest() ||
			aioseo()->helpers->isDoingWpCli()
		) {
			return;
		}

		add_action( 'template_redirect', [ $this, 'maybeRemoveQueryArgs' ], 1 );

		$this->removeReplyToCom();
	}

	/**
	 * Check if we can remove query args.
	 *
	 * @since 4.5.8
	 *
	 * @return boolean True if the query args can be removed.
	 */
	private function canRemoveQueryArgs() {
		if (
			! aioseo()->options->searchAppearance->advanced->blockArgs->enable ||
			is_user_logged_in() ||
			is_admin() ||
			is_robots() ||
			get_query_var( 'aiosp_sitemap_path' ) ||
			empty( $_GET ) // phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
		) {
			return false;
		}

		if ( is_singular() ) {
			global $post;
			$thePost = aioseo()->helpers->getPost( $post->ID );

			// Leave the preview query arguments intact.
			if (
				// phpcs:disable phpcs:ignore HM.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Recommended
				isset( $_GET['preview'] ) &&
				isset( $_GET['preview_nonce'] ) &&
				// phpcs:enable
				wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['preview_nonce'] ) ), 'post_preview_' . $thePost->ID ) &&
				current_user_can( 'edit_post', $thePost->ID )
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Maybe remove query args.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	public function maybeRemoveQueryArgs() {
		if ( ! $this->canRemoveQueryArgs() ) {
			return;
		}

		$currentRequest = aioseo()->helpers->getRequestUrl();

		// Remove the home path from the url for subfolder installs.
		$currentRequest       = aioseo()->helpers->excludeHomePath( $currentRequest );
		$currentRequestParsed = wp_parse_url( $currentRequest );

		// No query args? Never mind!
		if ( empty( $currentRequestParsed['query'] ) ) {
			return;
		}

		parse_str( $currentRequestParsed['query'], $currentRequestQueryArgs );
		$notAllowed          = [];
		$recognizedQueryLogs = [];

		foreach ( $currentRequestQueryArgs as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}
			$this->addQueryLog( $currentRequestParsed['path'], $key, $value );

			$blocked = CrawlCleanupBlockedArg::getByKeyValue( $key, null );
			if ( ! $blocked->exists() ) {
				$blocked = CrawlCleanupBlockedArg::getByKeyValue( $key, $value );
			}

			if ( ! $blocked->exists() ) {
				$blocked = CrawlCleanupBlockedArg::matchRegex( $key, $value );
			}

			if ( $blocked->exists() ) {
				$queryArg = $key . ( $value ? '=' . $value : null );
				$notAllowed[] = $queryArg;
				$blocked->addHit();
				continue;
			}

			$recognizedQueryLogs[ $key ] = empty( $value ) ? true : $value;
		}

		if ( ! empty( $notAllowed ) ) {
			$newUrl = home_url( $currentRequestParsed['path'] );

			header( 'Content-Type: redirect', true );
			header_remove( 'Content-Type' );
			header_remove( 'Last-Modified' );
			header_remove( 'X-Pingback' );

			wp_safe_redirect( add_query_arg( $recognizedQueryLogs, $newUrl ), 301, AIOSEO_PLUGIN_SHORT_NAME . ' Crawl Cleanup' );
			exit;
		}
	}

	/**
	 * Remove ?replytocom.
	 *
	 * @since 4.5.8
	 *
	 * @return void
	 */
	private function removeReplyToCom() {
		if ( ! apply_filters( 'aioseo_remove_reply_to_com', true ) ) {
			return;
		}

		add_filter( 'comment_reply_link', [ $this, 'removeReplyToComLink' ] );
		add_action( 'template_redirect', [ $this, 'replyToComRedirect' ], 1 );
	}

	/**
	 * Remove ?replytocom.
	 *
	 * @since 4.7.3
	 *
	 * @param  string $link The comment link as a string.
	 * @return string       The modified link.
	 */
	public function removeReplyToComLink( $link ) {
		return preg_replace( '`href=(["\'])(?:.*(?:\?|&|&#038;)replytocom=(\d+)#respond)`', 'href=$1#comment-$2', $link );
	}

	/**
	 * Redirects out the ?replytocom variables.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function replyToComRedirect() {
		$replyToCom = absint( wp_unslash( $_GET['replytocom'] ?? null ) ); // phpcs:ignore HM.Security.NonceVerification.Recommended
		if ( ! empty( $replyToCom ) && is_singular() ) {
			$url = get_permalink( $GLOBALS['post']->ID );
			if ( isset( $_SERVER['QUERY_STRING'] ) ) {
				$queryString = remove_query_arg( 'replytocom', sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) );
				if ( ! empty( $queryString ) ) {
					$url = add_query_arg( [], $url ) . '?' . $queryString;
				}
			}
			$url = add_query_arg( [], $url ) . '#comment-' . $replyToCom;

			wp_safe_redirect( $url, 301, AIOSEO_PLUGIN_SHORT_NAME );
			exit;
		}
	}

	/**
	 * Add query args log.
	 *
	 * @since 4.5.8
	 *
	 * @param string $path  A String of the path to create a slug.
	 * @param string $key   A String of key from query arg.
	 * @param string $value A String of value from query arg.
	 * @return void
	 */
	private function addQueryLog( $path, $key, $value = null ) {
		$slug = $path . '?' . $key . ( 0 < strlen( $value ) ? '=' . $value : '' );
		$log  = CrawlCleanupLog::getBySlug( $slug );

		$data = [
			'slug'  => $slug,
			'key'   => $key,
			'value' => $value
		];

		$log->set( $data );
		$log->create();
	}
}