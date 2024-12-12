<?php
namespace AIOSEO\Plugin\Common\Sitemap\Html {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	* Main class for the HTML sitemap.
	*
	* @since 4.1.3
	*/
	class Sitemap {
		/** Instance of the frontend class.
		 *
		 * @since 4.1.3
		 *
		 * @var Frontend
		 */
		public $frontend;

		/**
		 * Instance of the shortcode class.
		 *
		 * @since 4.1.3
		 *
		 * @var Shortcode
		 */
		public $shortcode;

		/**
		 * Instance of the block class.
		 *
		 * @since 4.1.3
		 *
		 * @var Block
		 */
		public $block;

		/**
		 * Whether the current queried page is the dedicated sitemap page.
		 *
		 * @since 4.1.3
		 *
		 * @var bool
		 */
		public $isDedicatedPage = false;

		/**
		 * Class constructor.
		 *
		 * @since 4.1.3
		 */
		public function __construct() {
			$this->frontend  = new Frontend();
			$this->shortcode = new Shortcode();
			$this->block     = new Block();

			add_action( 'widgets_init', [ $this, 'registerWidget' ] );
			add_filter( 'aioseo_canonical_url', [ $this, 'getCanonicalUrl' ] );

			if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
				add_action( 'template_redirect', [ $this, 'checkForDedicatedPage' ] );
			}
		}

		/**
		 * Register our HTML sitemap widget.
		 *
		 * @since 4.1.3
		 *
		 * @return void
		 */
		public function registerWidget() {
			if ( aioseo()->helpers->canRegisterLegacyWidget( 'aioseo-html-sitemap-widget' ) ) {
				register_widget( 'AIOSEO\Plugin\Common\Sitemap\Html\Widget' );
			}
		}

		/**
		 * Checks whether the current request is for our dedicated HTML sitemap page.
		 *
		 * @since 4.1.3
		 *
		 * @return void
		 */
		public function checkForDedicatedPage() {
			if ( ! aioseo()->options->sitemap->html->enable ) {
				return;
			}

			global $wp;
			$sitemapUrl = aioseo()->options->sitemap->html->pageUrl;
			if ( ! $sitemapUrl || empty( $wp->request ) ) {
				return;
			}

			$sitemapUrl = wp_parse_url( $sitemapUrl );
			if ( empty( $sitemapUrl['path'] ) ) {
				return;
			}

			$sitemapUrl = trim( $sitemapUrl['path'], '/' );
			if ( trim( $wp->request, '/' ) === $sitemapUrl ) {
				$this->isDedicatedPage = true;
				$this->generatePage();
			}
		}

		/**
		 * Checks whether the current request is for our dedicated HTML sitemap page.
		 *
		 * @since 4.1.3
		 *
		 * @return void
		 */
		private function generatePage() {
			global $wp_query, $wp, $post; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

			$postId     = -1337; // Set a negative ID to prevent conflicts with existing posts.
			$sitemapUrl = aioseo()->options->sitemap->html->pageUrl;
			$path       = trim( wp_parse_url( $sitemapUrl )['path'], '/' );

			$fakePost                 = new \stdClass();
			$fakePost->ID             = $postId;
			$fakePost->post_author    = 1;
			$fakePost->post_date      = current_time( 'mysql' );
			$fakePost->post_date_gmt  = current_time( 'mysql', 1 );
			$fakePost->post_title     = apply_filters( 'aioseo_html_sitemap_page_title', __( 'Sitemap', 'all-in-one-seo-pack' ) );
			$fakePost->post_content   = '[aioseo_html_sitemap archives=false]';
			// We're using post instead of page to prevent calls to get_ancestors(), which will trigger errors.
			// To loead the page template, we set is_page to true on the WP_Query object.
			$fakePost->post_type      = 'post';
			$fakePost->post_status    = 'publish';
			$fakePost->comment_status = 'closed';
			$fakePost->ping_status    = 'closed';
			$fakePost->post_name      = $path;
			$fakePost->filter         = 'raw'; // Needed to prevent calls to the database when creating the WP_Post object.
			$postObject               = new \WP_Post( $fakePost );

			$post = $postObject;

			// We'll set as much properties on the WP_Query object as we can to prevent conflicts with other plugins/themes.
			// phpcs:disable Squiz.NamingConventions.ValidVariableName
			$wp_query->is_404            = false;
			$wp_query->is_page           = true;
			$wp_query->is_singular       = true;
			$wp_query->post              = $postObject;
			$wp_query->posts             = [ $postObject ];
			$wp_query->queried_object    = $postObject;
			$wp_query->queried_object_id = $postId;
			$wp_query->found_posts       = 1;
			$wp_query->post_count        = 1;
			$wp_query->max_num_pages     = 1;

			unset( $wp_query->query['error'] );
			$wp_query->query_vars['error'] = '';
			// phpcs:enable Squiz.NamingConventions.ValidVariableName

			// We need to add the post object to the cache so that get_post() calls don't trigger database calls.
			wp_cache_add( $postId, $postObject, 'posts' );

			$GLOBALS['wp_query'] = $wp_query; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			$wp->register_globals();

			// Setting is_404 is not sufficient, so we still need to change the status code.
			status_header( 200 );
		}

		/**
		 * Get the canonical URL for the dedicated HTML sitemap page.
		 *
		 * @since 4.5.7
		 *
		 * @param  string $originalUrl The canonical URL.
		 * @return string              The canonical URL.
		 */
		public function getCanonicalUrl( $originalUrl ) {
			$sitemapOptions = aioseo()->options->sitemap->html;

			if ( ! $sitemapOptions->enable || ! $this->isDedicatedPage ) {
				return $originalUrl;
			}

			// If the user has set a custom URL for the sitemap page, use that.
			if ( $sitemapOptions->pageUrl ) {
				return $sitemapOptions->pageUrl;
			}

			// Return the current URL of WP.
			global $wp;

			return home_url( $wp->request );
		}
	}
}

namespace {
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! function_exists( 'aioseo_html_sitemap' ) ) {
		/**
		 * Global function that can be used to print the HTML sitemap.
		 *
		 * @since 4.1.3
		 *
		 * @param  array   $attributes User-defined attributes that override the default settings.
		 * @param  boolean $echo       Whether to echo the output or return it.
		 * @return string              The HTML sitemap code.
		 */
		function aioseo_html_sitemap( $attributes = [], $echo = true ) {
			$attributes = aioseo()->htmlSitemap->frontend->getAttributes( $attributes );

			return aioseo()->htmlSitemap->frontend->output( $echo, $attributes );
		}
	}
}