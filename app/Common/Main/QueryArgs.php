<?php
namespace AIOSEO\Plugin\Common\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query arguments class.
 *
 * @since 4.2.1
 */
class QueryArgs {
	/**
	 * List of plugins to check against.
	 *
	 * @since 4.2.1
	 *
	 * @var array
	 */
	private $plugins;

	/**
	 * Construct method.
	 *
	 * @since 4.2.1
	 */
	public function __construct() {
		if (
			is_admin() ||
			aioseo()->helpers->isWpLoginPage() ||
			aioseo()->helpers->isAjaxCronRestRequest()
		) {
			return;
		}

		if (
			aioseo()->options->searchAppearance->advanced->crawlCleanup->enable &&
			aioseo()->options->searchAppearance->advanced->crawlCleanup->removeUnrecognizedQueryArgs
		) {
			add_action( 'template_redirect', [ $this, 'removeUnrecognizedQueryArgs' ], 1 );
		}
	}

	/**
	 * Remove any unrecognized query args.
	 *
	 * @since 4.2.1
	 *
	 * @return void
	 */
	public function removeUnrecognizedQueryArgs() {
		$this->plugins = aioseo()->helpers->getPluginData();

		if (
			is_user_logged_in() ||
			is_admin() ||
			is_robots() ||
			get_query_var( 'aiosp_sitemap_path' ) ||
			empty( $_GET ) // phpcs:ignore HM.Security.NonceVerification.Recommended
		) {
			return;
		}

		$currentUrl       = aioseo()->helpers->getCurrentUrl();
		$currentUrlParsed = wp_parse_url( $currentUrl );

		// No query args? Never mind!
		if ( empty( $currentUrlParsed['query'] ) ) {
			return;
		}

		$newUrl = '';
		if ( is_singular() ) {
			global $post;
			$thePost = aioseo()->helpers->getPost( $post->ID );

			// Leave the preview query arguments intact.
			if (
				isset( $_GET['preview'] ) && // phpcs:ignore HM.Security.NonceVerification.Recommended
				isset( $_GET['preview_nonce'] ) && // phpcs:ignore HM.Security.NonceVerification.Recommended
				current_user_can( 'edit_post', $thePost->ID )
			) {
				return;
			}

			$newUrl = $this->getSingularUrl( $thePost );
		}

		if ( is_front_page() ) {
			$newUrl = $this->getFrontPageUrl();
		} elseif ( is_home() ) {
			$newUrl = get_permalink( get_option( 'page_for_posts' ) );
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$newUrl = $this->getTaxonomyUrl();
		}

		if ( is_search() ) {
			$newUrl = $this->getSearchUrl();
		}

		if ( is_404() ) {
			$newUrl = $this->get404Url();
		}

		global $wp_query;
		if (
			! empty( $newUrl ) &&
			0 !== $wp_query->query_vars['paged'] &&
			0 !== $wp_query->post_count
		) {
			if ( is_search() ) {
				$newUrl = get_bloginfo( 'url' ) . '/page/' . $wp_query->query_vars['paged'] . '/?s=' . rawurlencode( get_search_query() );
			} else {
				$newUrl = user_trailingslashit( trailingslashit( $newUrl ) . 'page/' . $wp_query->query_vars['paged'] );
			}
		}

		$allowedQueryArgs = array_merge(
			$this->getDefaultQueryArgs(),
			$this->getWpQueryArgs(),
			$this->getAioseoQueryArgs(),
			$this->getAmpQueryArgs(),
			$this->getWpFormsQueryArgs(),
			$this->getRafflepressQueryArgs(),
			$this->getEddQueryArgs(),
			$this->getSmashBalloonQueryArgs(),
			$this->getSearchWpQueryArgs(),
			$this->getWooCommerceQueryArgs()
		);

		if ( aioseo()->options->searchAppearance->advanced->crawlCleanup->allowedQueryArgs ) {
			$allowedQueryArgs = array_merge(
				$allowedQueryArgs,
				explode( "\n", aioseo()->options->searchAppearance->advanced->crawlCleanup->allowedQueryArgs )
			);
		}

		$allowedQueryArgs      = array_unique( $allowedQueryArgs );
		$allowedQueryArgs      = apply_filters( 'aioseo_unrecognized_allowed_query_args', $allowedQueryArgs );
		$currentUrlQueryArgs   = explode( '&', $currentUrlParsed['query'] );
		$recognizedQueryArgs   = [];
		$unRecognizedQueryArgs = [];
		foreach ( $currentUrlQueryArgs as $queryArg ) {
			$queryArgArray = explode( '=', $queryArg );
			$key           = $queryArgArray[0];

			if ( in_array( $key, $allowedQueryArgs, true ) ) {
				$recognizedQueryArgs[ $queryArgArray[0] ] = empty( $queryArgArray[1] ) ? true : $queryArgArray[1];
				continue;
			}

			// Check if this is a RegEx pattern.
			foreach ( $allowedQueryArgs as $allowedQueryArg ) {
				if ( ! aioseo()->helpers->isValidRegex( $allowedQueryArg ) ) {
					continue;
				}

				if ( preg_match( $allowedQueryArg, $key ) ) {
					$recognizedQueryArgs[ $queryArgArray[0] ] = empty( $queryArgArray[1] ) ? true : $queryArgArray[1];
					continue 2;
				}
			}

			// If it's here we don't recognize it. Let's get rid of it.
			$unRecognizedQueryArgs[] = $queryArg;
		}

		if ( ! empty( $newUrl ) && ! empty( $unRecognizedQueryArgs ) ) {
			header( 'Content-Type: redirect', true );
			header_remove( 'Content-Type' );
			header_remove( 'Last-Modified' );
			header_remove( 'X-Pingback' );

			wp_safe_redirect( add_query_arg( $recognizedQueryArgs, $newUrl ), 301, AIOSEO_PLUGIN_SHORT_NAME . ' Crawl Cleanup' );
			exit;
		}
	}

	/**
	 * Get the URL for the singular post.
	 *
	 * @since 4.2.1
	 *
	 * @param  \WP_Post $thePost The post we are looking at.
	 * @return string            The new URL.
	 */
	private function getSingularUrl( $thePost ) {
		$page   = aioseo()->helpers->getPageNumber();
		$newUrl = get_permalink( $thePost->ID );
		if ( 1 < $page ) {
			$newUrl    = user_trailingslashit( trailingslashit( get_permalink( $thePost->ID ) ) . $page );
			$pageCount = substr_count( $thePost->post_content, '<!--nextpage-->' );
			if ( $page > ( $pageCount + 1 ) ) {
				$newUrl = user_trailingslashit( trailingslashit( get_permalink( $thePost->ID ) ) . ( $pageCount + 1 ) );
			}
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) && preg_match( '/(\?replytocom=[^&]+)/', sanitize_text_field( $_SERVER['REQUEST_URI'] ), $matches ) ) {
			$newUrl .= str_replace( '?replytocom=', '#comment-', $matches[0] );
		}

		return $newUrl;
	}

	/**
	 * Get the URL for the front page.
	 *
	 * @since 4.2.1
	 *
	 * @return string The new URL.
	 */
	private function getFrontPageUrl() {
		if ( ! aioseo()->helpers->isStaticHomePage() ) {
			return get_bloginfo( 'url' ) . '/';
		}

		return get_permalink( $GLOBALS['post']->ID );
	}

	/**
	 * Get the URL for the current taxonomy.
	 *
	 * @since 4.2.1
	 *
	 * @return string The new URL.
	 */
	private function getTaxonomyUrl() {
		global $wp_query;
		$term   = $wp_query->get_queried_object();
		$newUrl = get_term_link( $term, $term->taxonomy );

		if ( is_feed() ) {
			$newUrl = get_term_feed_link( $term->term_id, $term->taxonomy );
		}

		return $newUrl;
	}

	/**
	 * Get the URL for the search page.
	 *
	 * @since 4.2.1
	 *
	 * @return string The new URL.
	 */
	private function getSearchUrl() {
		$s = rawurlencode( preg_replace( '/(%20|\+)/', ' ', get_search_query() ) );

		return get_bloginfo( 'url' ) . '/?s=' . $s;
	}

	/**
	 * Get the URL for the 404 page.
	 *
	 * @since 4.2.1
	 *
	 * @return string The new URL.
	 */
	private function get404Url() {
		$newUrl     = null;
		$currentUrl = aioseo()->helpers->getCurrentUrl();

		if (
			is_multisite() &&
			! is_subdomain_install() &&
			is_main_site()
		) {
			if (
				get_bloginfo( 'url' ) . '/blog/' === $currentUrl ||
				get_bloginfo( 'url' ) . '/blog' === $currentUrl
			) {
				$newUrl = get_bloginfo( 'url' ) . '/';

				if ( aioseo()->helpers->isStaticHomePage() ) {
					$newUrl = get_permalink( get_option( 'page_for_posts' ) );
				}
			}
		}

		return $newUrl;
	}

	/**
	 * Get any default query args.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getDefaultQueryArgs() {
		$defaultArgs = [
			'q',
			's',
			'action'
		];

		if ( ! get_option( 'permalink_structure' ) ) {
			$defaultArgs = array_merge(
				$defaultArgs,
				[
					'p',
					'cat',
					'tag',
					'term'
				]
			);
		}

		return $defaultArgs;
	}

	/**
	 * Get any query args for WP.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getWpQueryArgs() {
		return [
			'_wp-find-template'
		];
	}

	/**
	 * Get any query args for AMP.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getAmpQueryArgs() {
		return [
			'amp'
		];
	}

	/**
	 * Get any query args for AIOSEO.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getAioseoQueryArgs() {
		return [
			'/^aioseo-/'
		];
	}

	/**
	 * Get any query args for WPForms.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getWpFormsQueryArgs() {
		if (
			! $this->plugins['wpForms']['activated'] &&
			! $this->plugins['wpFormsPro']['activated']
		) {
			return [];
		}

		return [
			'/^wpforms/',
			'/^wpf[0-9*]_/',
			'hook_url',
			'/^zap_/',
			'login',
			'key'
		];
	}

	/**
	 * Get any query args for Rafflepress.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getRafflepressQueryArgs() {
		if (
			! $this->plugins['rafflePress']['activated'] &&
			! $this->plugins['rafflePressPro']['activated']
		) {
			return [];
		}

		return [
			'/^rafflepress_/',
			'/^rafflepres_/' // To account for a bug in rafflepress.
		];
	}

	/**
	 * Get any query args for Smash balloon plugins.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getEddQueryArgs() {
		if (
			! $this->plugins['easyDigitalDownloads']['activated']
		) {
			return [];
		}

		return [
			'/^edd_/',
			'/_id$/',
			'license',
			'discount',
		];
	}

	/**
	 * Get any query args for Smash balloon plugins.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getSmashBalloonQueryArgs() {
		if (
			! $this->plugins['instagramFeed']['activated'] &&
			! $this->plugins['instagramFeedPro']['activated'] &&
			! $this->plugins['facebookFeed']['activated'] &&
			! $this->plugins['facebookFeedPro']['activated'] &&
			! $this->plugins['twitterFeed']['activated'] &&
			! $this->plugins['twitterFeedPro']['activated'] &&
			! $this->plugins['youTubeFeed']['activated'] &&
			! $this->plugins['youTubeFeedPro']['activated']
		) {
			return [];
		}

		return [
			'sb_demo'
		];
	}

	/**
	 * Get any query args for the SearchWP plugin.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getSearchWpQueryArgs() {
		if (
			! $this->plugins['searchWp']['activated']
		) {
			return [];
		}

		return [
			'searchwp',
			'swppg'
		];
	}

	/**
	 * Get any query args for WooCommerce.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of query args.
	 */
	private function getWooCommerceQueryArgs() {
		if (
			! aioseo()->helpers->isWooCommerceActive()
		) {
			return [];
		}

		return [
			'/^attribute_/',
			'/_id$/',
			'_wcsnonce',
			'add-to-cart',
			'add_coupon',
			'item',
			'key',
			'orderby',
			'post_type',
			'product',
			'product_cat',
			'reset-link-sent'
		];
	}
}