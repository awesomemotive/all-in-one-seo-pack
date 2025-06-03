<?php
namespace AIOSEO\Plugin\Common\Standalone;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Integrations\BuddyPress as BuddyPressIntegration;

/**
 * Handles the SEO Preview feature on the front-end.
 *
 * @since 4.2.8
 */
class SeoPreview {
	/**
	 * Whether this feature is allowed on the current page or not.
	 *
	 * @since 4.2.8
	 *
	 * @var bool
	 */
	private $enable = false;

	/**
	 * The relative JS filename for this standalone.
	 *
	 * @since 4.3.1
	 *
	 * @var string
	 */
	private $mainAssetRelativeFilename = 'src/vue/standalone/seo-preview/main.js';

	/**
	 * Class constructor.
	 *
	 * @since 4.2.8
	 */
	public function __construct() {
		// Allow users to disable SEO Preview.
		if ( apply_filters( 'aioseo_seo_preview_disable', false ) ) {
			return;
		}

		// Hook into `wp` in order to have access to the WP queried object.
		add_action( 'wp', [ $this, 'init' ], 20 );
	}

	/**
	 * Initialize the feature.
	 * Hooked into `wp` action hook.
	 *
	 * @since 4.2.8
	 *
	 * @return void
	 */
	public function init() {
		if (
			is_admin() ||
			! aioseo()->helpers->isAdminBarEnabled() ||
			// If we're seeing the Divi theme Visual Builder.
			( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) ||
			aioseo()->helpers->isAmpPage()
		) {
			return;
		}

		$allow = [
			'archive',
			'attachment',
			'author',
			'date',
			'dynamic_home',
			'page',
			'search',
			'single',
			'taxonomy',
		];

		if ( ! in_array( aioseo()->helpers->getTemplateType(), $allow, true ) ) {
			return;
		}

		$this->enable = true;

		// Prevent Autoptimize from optimizing the translations for the SEO Preview. If we don't do this, Autoptimize can break the frontend for certain languages - #5235.
		if ( is_user_logged_in() && 'en_US' !== get_user_locale() ) {
			add_filter( 'autoptimize_filter_noptimize', '__return_true' );
		}

		// As WordPress uses priority 10 to print footer scripts we use 9 to make sure our script still gets output.
		add_action( 'wp_print_footer_scripts', [ $this, 'enqueueScript' ], 9 );
	}

	/**
	 * Hooked into `wp_print_footer_scripts` action hook.
	 * Enqueue the standalone JS the latest possible and prevent 3rd-party performance plugins from merging it.
	 *
	 * @since 4.3.1
	 *
	 * @return void
	 */
	public function enqueueScript() {
		aioseo()->core->assets->load( $this->mainAssetRelativeFilename, [], $this->getVueData(), 'aioseoSeoPreview' );
		aioseo()->main->enqueueTranslations();
	}

	/**
	 * Returns the data for Vue.
	 *
	 * @since 4.2.8
	 *
	 * @return array The data.
	 */
	private function getVueData() {
		$data = [
			'editGoogleSnippetUrl'   => '',
			'editFacebookSnippetUrl' => '',
			'editTwitterSnippetUrl'  => '',
			'editObjectBtnText'      => '',
			'editObjectUrl'          => '',
			'keyphrases'             => '',
			'page_analysis'          => '',
			'urls'                   => [
				'home'        => home_url(),
				'domain'      => aioseo()->helpers->getSiteDomain(),
				'mainSiteUrl' => aioseo()->helpers->getSiteUrl(),
			],
			'mainAssetCssQueue'      => aioseo()->core->assets->getJsAssetCssQueue( $this->mainAssetRelativeFilename ),
			'data'                   => [
				'isDev'           => aioseo()->helpers->isDev(),
				'siteName'        => aioseo()->helpers->getWebsiteName(),
				'usingPermalinks' => aioseo()->helpers->usingPermalinks()
			]
		];

		if ( BuddyPressIntegration::isComponentPage() ) {
			return array_merge( $data, aioseo()->standalone->buddyPress->getVueDataSeoPreview() );
		}

		$queriedObject = get_queried_object(); // Don't use the getTerm helper here.
		$templateType  = aioseo()->helpers->getTemplateType();
		if (
			'taxonomy' === $templateType ||
			'single' === $templateType ||
			'page' === $templateType ||
			'attachment' === $templateType
		) {
			$labels = null;

			if ( is_a( $queriedObject, 'WP_Term' ) ) {
				$wpObject              = $queriedObject;
				$labels                = get_taxonomy_labels( get_taxonomy( $queriedObject->taxonomy ) );
				$data['editObjectUrl'] = get_edit_term_link( $queriedObject, $queriedObject->taxonomy );
			} else {
				$wpObject = aioseo()->helpers->getPost();

				if ( is_a( $wpObject, 'WP_Post' ) ) {
					$labels                = get_post_type_labels( get_post_type_object( $wpObject->post_type ) );
					$data['editObjectUrl'] = get_edit_post_link( $wpObject, 'url' );

					if (
						! aioseo()->helpers->isSpecialPage( $wpObject->ID ) &&
						'attachment' !== $templateType
					) {
						$aioseoPost            = Models\Post::getPost( $wpObject->ID );
						$data['page_analysis'] = Models\Post::getPageAnalysisDefaults( $aioseoPost->page_analysis );
						$data['keyphrases']    = Models\Post::getKeyphrasesDefaults( $aioseoPost->keyphrases );
					}
				}
			}

			// At this point if `$wpObject` is not an instance of WP_Term nor WP_Post, then we can't have the URLs.
			if (
				is_object( $wpObject ) &&
				is_object( $labels )
			) {
				$data['editObjectBtnText'] = sprintf(
					// Translators: 1 - A noun for something that's being edited ("Post", "Page", "Article", "Product", etc.).
					esc_html__( 'Edit %1$s', 'all-in-one-seo-pack' ),
					$labels->singular_name
				);
				$data['editGoogleSnippetUrl']   = $this->getEditSnippetUrl( $templateType, 'google', $wpObject );
				$data['editFacebookSnippetUrl'] = $this->getEditSnippetUrl( $templateType, 'facebook', $wpObject );
				$data['editTwitterSnippetUrl']  = $this->getEditSnippetUrl( $templateType, 'twitter', $wpObject );
			}
		}

		if (
			'archive' === $templateType ||
			'author' === $templateType ||
			'date' === $templateType ||
			'search' === $templateType
		) {
			if ( is_a( $queriedObject, 'WP_User' ) ) {
				$data['editObjectUrl']     = get_edit_user_link( $queriedObject->ID );
				$data['editObjectBtnText'] = esc_html__( 'Edit User', 'all-in-one-seo-pack' );
			}

			$data['editGoogleSnippetUrl'] = $this->getEditSnippetUrl( $templateType, 'google' );
		}

		if ( 'dynamic_home' === $templateType ) {
			$data['editGoogleSnippetUrl']   = $this->getEditSnippetUrl( $templateType, 'google' );
			$data['editFacebookSnippetUrl'] = $this->getEditSnippetUrl( $templateType, 'facebook' );
			$data['editTwitterSnippetUrl']  = $this->getEditSnippetUrl( $templateType, 'twitter' );
		}

		return $data;
	}

	/**
	 * Get the URL to the place where the snippet details can be edited.
	 *
	 * @since 4.2.8
	 *
	 * @param  string                 $templateType The WP template type {@see WpContext::getTemplateType}.
	 * @param  string                 $snippet      'google', 'facebook' or 'twitter'.
	 * @param  \WP_Post|\WP_Term|null $object       Post or term object.
	 * @return string                               The URL. Returns an empty string if nothing matches.
	 */
	private function getEditSnippetUrl( $templateType, $snippet, $object = null ) {
		$url = '';

		// Bail if `$snippet` doesn't fit requirements.
		if ( ! in_array( $snippet, [ 'google', 'facebook', 'twitter' ], true ) ) {
			return $url;
		}

		// If we're in a post/page/term (not an attachment) we'll have a URL directly to the meta box.
		if ( in_array( $templateType, [ 'single', 'page', 'attachment', 'taxonomy' ], true ) ) {
			$url = 'taxonomy' === $templateType
				? get_edit_term_link( $object, $object->taxonomy ) . '#aioseo-term-settings-field'
				: get_edit_post_link( $object, 'url' ) . '#aioseo-settings';

			$queryArgs = [ 'aioseo-tab' => 'general' ];
			if ( in_array( $snippet, [ 'facebook', 'twitter' ], true ) ) {
				$queryArgs = [
					'aioseo-tab' => 'social',
					'social-tab' => $snippet
				];
			}

			return add_query_arg( $queryArgs, $url );
		}

		// If we're in any sort of archive let's point to the global archive editing.
		if ( in_array( $templateType, [ 'archive', 'author', 'date', 'search' ], true ) ) {
			return admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/archives';
		}

		// If homepage is set to show the latest posts let's point to the global home page editing.
		if ( 'dynamic_home' === $templateType ) {
			// Default `$url` for 'google' snippet.
			$url = add_query_arg(
				[ 'aioseo-scroll' => 'home-page-settings' ],
				admin_url( 'admin.php?page=aioseo-search-appearance' ) . '#/global-settings'
			);

			if ( in_array( $snippet, [ 'facebook', 'twitter' ], true ) ) {
				$url = admin_url( 'admin.php?page=aioseo-social-networks' ) . '#/' . $snippet;
			}

			return $url;
		}

		return $url;
	}

	/**
	 * Returns the "SEO Preview" submenu item data ("node" as WP calls it).
	 *
	 * @since 4.2.8
	 *
	 * @return array The admin bar menu item data or an empty array if this feature is disabled.
	 */
	public function getAdminBarMenuItemNode() {
		if ( ! $this->enable ) {
			return [];
		}

		$title = esc_html__( 'SEO Preview', 'all-in-one-seo-pack' );

		// @TODO Remove 'NEW' after a couple months.
		$title .= '<span class="aioseo-menu-new-indicator">';
		$title .= esc_html__( 'NEW', 'all-in-one-seo-pack' ) . '!';
		$title .= '</span>';

		return [
			'id'     => 'aioseo-seo-preview',
			'parent' => 'aioseo-main',
			'title'  => $title,
			'href'   => '#',
		];
	}
}