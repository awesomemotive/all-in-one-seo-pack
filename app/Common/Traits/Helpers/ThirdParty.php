<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains all third-party related helper methods.
 *
 * @since 4.1.4
 */
trait ThirdParty {
	/**
	 * Checks whether WooCommerce is active.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean Whether WooCommerce is active.
	 */
	public function isWooCommerceActive() {
		return class_exists( 'woocommerce' );
	}

	/**
	 * Checks if the current page is a special WooCommerce page (Cart, Checkout, ...).
	 *
	 * @since 4.0.0
	 *
	 * @param  int         $postId The post ID.
	 * @return string|bool         The type of page or false if it isn't a WooCommerce page.
	 */
	public function isWooCommercePage( $postId = 0 ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		$postId = $postId ? $postId : get_the_ID();

		static $cartPageId;
		if ( ! $cartPageId ) {
			$cartPageId = (int) get_option( 'woocommerce_cart_page_id' );
		}

		static $checkoutPageId;
		if ( ! $checkoutPageId ) {
			$checkoutPageId = (int) get_option( 'woocommerce_checkout_page_id' );
		}

		static $myAccountPageId;
		if ( ! $myAccountPageId ) {
			$myAccountPageId = (int) get_option( 'woocommerce_myaccount_page_id' );
		}

		static $termsPageId;
		if ( ! $termsPageId ) {
			$termsPageId = (int) get_option( 'woocommerce_terms_page_id' );
		}

		switch ( $postId ) {
			case $cartPageId:
				return 'cart';
			case $checkoutPageId:
				return 'checkout';
			case $myAccountPageId:
				return 'myAccount';
			case $termsPageId:
				return 'terms';
			default:
				return false;
		}
	}

	/**
	 * Checks whether the current page is a special WooCommerce page we shouldn't show our schema settings for.
	 *
	 * @since 4.1.6
	 *
	 * @param  int  $postId The post ID.
	 * @return bool         Whether the current page is a disallowed WooCommerce page.
	 */
	public function isWooCommercePageWithoutSchema( $postId = 0 ) {
		$page = $this->isWooCommercePage( $postId );
		if ( ! $page ) {
			return false;
		}

		$disallowedPages = [ 'cart', 'checkout', 'myAccount' ];

		return in_array( $page, $disallowedPages, true );
	}

	/**
	 * Checks whether the queried object is the WooCommerce shop page.
	 *
	 * @since 4.0.0
	 *
	 * @param  int  $id The post ID to check against (optional).
	 * @return bool     Whether the current page is the WooCommerce shop page.
	 */
	public function isWooCommerceShopPage( $id = 0 ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && function_exists( 'is_shop' ) ) {
			return is_shop();
		}

		$id = ! $id && ! empty( $_GET['post'] ) ? (int) wp_unslash( $_GET['post'] ) : (int) $id; // phpcs:ignore HM.Security.ValidatedSanitizedInput, HM.Security.NonceVerification.Recommended

		return $id && wc_get_page_id( 'shop' ) === $id;
	}

	/**
	 * Checks whether the queried object is the WooCommerce cart page.
	 *
	 * @since 4.1.3
	 *
	 * @param  int  $id The post ID to check against (optional).
	 * @return bool     Whether the current page is the WooCommerce cart page.
	 */
	public function isWooCommerceCartPage( $id = 0 ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && function_exists( 'is_cart' ) ) {
			return is_cart();
		}

		$id = ! $id && ! empty( $_GET['post'] ) ? (int) wp_unslash( $_GET['post'] ) : (int) $id; // phpcs:ignore HM.Security.ValidatedSanitizedInput, HM.Security.NonceVerification.Recommended

		return $id && wc_get_page_id( 'cart' ) === $id;
	}

	/**
	 * Checks whether the queried object is the WooCommerce checkout page.
	 *
	 * @since 4.1.3
	 *
	 * @param  int  $id The post ID to check against (optional).
	 * @return bool     Whether the current page is the WooCommerce checkout page.
	 */
	public function isWooCommerceCheckoutPage( $id = 0 ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && function_exists( 'is_checkout' ) ) {
			return is_checkout();
		}

		$id = ! $id && ! empty( $_GET['post'] ) ? (int) wp_unslash( $_GET['post'] ) : (int) $id; // phpcs:ignore HM.Security.ValidatedSanitizedInput, HM.Security.NonceVerification.Recommended

		return $id && wc_get_page_id( 'checkout' ) === $id;
	}

	/**
	 * Checks whether the queried object is the WooCommerce account page.
	 *
	 * @since 4.1.3
	 *
	 * @param  int  $id The post ID to check against (optional).
	 * @return bool     Whether the current page is the WooCommerce account page.
	 */
	public function isWooCommerceAccountPage( $id = 0 ) {
		if ( ! $this->isWooCommerceActive() ) {
			return false;
		}

		if ( ! is_admin() && ! aioseo()->helpers->isAjaxCronRestRequest() && function_exists( 'is_account_page' ) ) {
			return is_account_page();
		}

		$id = ! $id && ! empty( $_GET['post'] ) ? (int) wp_unslash( $_GET['post'] ) : (int) $id; // phpcs:ignore HM.Security.ValidatedSanitizedInput, HM.Security.NonceVerification.Recommended

		return $id && wc_get_page_id( 'myaccount' ) === $id;
	}

	/**
	 * Checks whether the queried object is a WooCommerce product page.
	 *
	 * @since 4.5.5
	 *
	 * @return bool Whether the current page is a WooCommerce product page.
	 */
	public function isWooCommerceProductPage() {
		if (
			! $this->isWooCommerceActive() ||
			! function_exists( 'is_product' )
		) {
			return false;
		}

		return is_product();
	}

	/**
	 * Checks whether the queried object is a WooCommerce taxonomy page.
	 *
	 * @since 4.5.5
	 *
	 * @return bool Whether the current page is a WooCommerce taxonomy page.
	 */
	public function isWooCommerceTaxonomyPage() {
		if (
			! $this->isWooCommerceActive() ||
			! function_exists( 'is_product_taxonomy' )
		) {
			return false;
		}

		return is_product_taxonomy();
	}

	/**
	 * Internationalize.
	 *
	 * @since 4.0.0
	 *
	 * @param $in
	 * @return mixed|void
	 */
	public function internationalize( $in ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'localization', $in );
	}

	/**
	 * Checks if WPML is active.
	 *
	 * @since 4.0.0
	 *
	 * @return bool True if it is, false if not.
	 */
	public function isWpmlActive() {
		return class_exists( 'SitePress' );
	}

	/**
	 * Localizes a given URL.
	 *
	 * This is required for compatibility with WPML.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $path The relative path of the URL.
	 * @return string $url  The filtered URL.
	 */
	public function localizedUrl( $path ) {
		$url = apply_filters( 'wpml_home_url', home_url( '/' ) );

		// Remove URL parameters.
		preg_match_all( '/\?[\s\S]+/', $url, $matches );

		// Get the base URL.
		$url  = preg_replace( '/\?[\s\S]+/', '', $url );
		$url  = trailingslashit( $url );
		$url .= preg_replace( '/\//', '', $path, 1 );

		// Readd URL parameters.
		if ( $matches && $matches[0] ) {
			$url .= $matches[0][0];
		}

		return $url;
	}

	/**
	 * Checks whether BuddyPress is active.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function isBuddyPressActive() {
		return class_exists( 'BuddyPress' );
	}

	/**
	 * Checks whether the queried object is a buddy press user page.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function isBuddyPressUser() {
		return $this->isBuddyPressActive() && function_exists( 'bp_is_user' ) && bp_is_user();
	}

	/**
	 * Returns if the page is a BuddyPress page (Activity, Members, Groups).
	 *
	 * @since 4.0.0
	 *
	 * @param  int  $postId The post ID.
	 * @return bool         If the page is a BuddyPress page or not.
	 */
	public function isBuddyPressPage( $postId = 0 ) {
		$bpPages = get_option( 'bp-pages' );

		if ( empty( $bpPages ) ) {
			return false;
		}

		foreach ( $bpPages as $page ) {
			if ( (int) $page === (int) $postId ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if is a BBpress post type.
	 *
	 * @since 4.2.8
	 *
	 * @param  string $postType The post type to check.
	 * @return bool             Whether this is a bbPress post type.
	 */
	public function isBBPressPostType( $postType ) {
		if ( ! class_exists( 'bbPress' ) ) {
			return false;
		}

		$bbPressPostTypes = [ 'forum', 'topic', 'reply' ];

		return in_array( $postType, $bbPressPostTypes, true );
	}

	/**
	 * Returns ACF fields as an array of meta keys and values.
	 *
	 * @since 4.0.6
	 *
	 * @param  \WP_Post|int $post  The post.
	 * @param  array        $types A whitelist of ACF field types.
	 * @return array               An array of meta keys and values.
	 */
	public function getAcfContent( $post = null, $types = [] ) {
		$post = ( $post && is_object( $post ) ) ? $post : $this->getPost( $post );

		if ( ! class_exists( 'ACF' ) || ! function_exists( 'get_field_objects' ) ) {
			return [];
		}

		if ( defined( 'ACF_VERSION' ) && version_compare( ACF_VERSION, '5.7.0', '<' ) ) {
			return [];
		}

		// Set defaults.
		$allowedTypes = [
			'text',
			'textarea',
			'email',
			'url',
			'wysiwyg',
			'image',
			'gallery',
			'link',
			// 'taxonomy',
		];

		$types        = wp_parse_args( $types, $allowedTypes );
		$fieldObjects = get_field_objects( $post->ID );

		if ( empty( $fieldObjects ) ) {
			return [];
		}

		// Filter out any fields that are not in our allowed types.
		$fields = array_filter( $fieldObjects, function( $object ) use ( $types ) {
			return ! empty( $object['value'] ) && in_array( $object['type'], $types, true );
		});

		// Create an array with the field names and values with added HTML markup.
		$acfFields = [];
		foreach ( $fields as $field ) {
			switch ( $field['type'] ) {
				case 'url':
					$value = make_clickable( $field['value'] ?? '' );
					break;
				case 'image':
					// Image format options are array, URL (string), id (int).
					$imageUrl = is_array( $field['value'] ) ? $field['value']['url'] : $field['value'];
					$imageUrl = is_numeric( $imageUrl ) ? wp_get_attachment_image_url( $imageUrl ) : $imageUrl;

					$value = "<img src='$imageUrl' />";
					break;
				case 'gallery':
					$imageUrl = $field['value'];
					// The value of a gallery field should always be an array.
					if ( is_array( $imageUrl ) ) {
						$imageUrl = current( $imageUrl );
					}

					// Image array format.
					if ( is_array( $imageUrl ) && ! empty( $imageUrl['url'] ) ) {
						$imageUrl = $imageUrl['url'];
					}

					// Image ID format.
					$imageUrl = is_numeric( $imageUrl ) ? wp_get_attachment_image_url( $imageUrl ) : $imageUrl;

					$value = ! empty( $imageUrl ) ? "<img src='{$imageUrl}' />" : '';
					break;
				case 'link':
					$value = make_clickable( $field['value']['url'] ?? $field['value'] ?? '' );
					break;
				default:
					$value = $field['value'];
					break;
			}

			if ( $value ) {
				$acfFields[ $field['name'] ] = $value;
			}
		}

		return $acfFields;
	}

	/**
	 * Checks whether the Smash Balloon Custom Facebook Feed plugin is active.
	 *
	 * @since 4.2.0
	 *
	 * @return bool Whether the SB CFF plugin is active.
	 */
	public function isSbCustomFacebookFeedActive() {
		static $isActive = null;
		if ( null !== $isActive ) {
			return $isActive;
		}

		$isActive = defined( 'CFFVER' ) || is_plugin_active( 'custom-facebook-feed/custom-facebook-feed.php' );

		return $isActive;
	}

	/**
	 * Returns the access token for Facebook from Smash Balloon if there is one.
	 *
	 * @since 4.2.0
	 *
	 * @return string|false The access token or false if there is none.
	 */
	public function getSbAccessToken() {
		static $accessToken = null;
		if ( null !== $accessToken ) {
			return $accessToken;
		}

		if ( ! $this->isSbCustomFacebookFeedActive() ) {
			$accessToken = false;

			return $accessToken;
		}

		$oembedTokenData = get_option( 'cff_oembed_token', [] );
		if ( ! $oembedTokenData || empty( $oembedTokenData['access_token'] ) ) {
			$accessToken = false;

			return $accessToken;
		}

		$sbFacebookDataEncryptionInstance = new \CustomFacebookFeed\SB_Facebook_Data_Encryption();
		$accessToken                      = $sbFacebookDataEncryptionInstance->maybe_decrypt( $oembedTokenData['access_token'] );

		return $accessToken;
	}

	/**
	* Returns the homepage URL for a language code.
	*
	* @since 4.2.1
	*
	* @param  string|int $identifier The language code or the post id to return the url.
	* @return string                 The home URL.
	*/
	public function wpmlHomeUrl( $identifier ) {
		foreach ( $this->wpmlHomePages() as $langCode => $wpmlHomePage ) {
			if (
				( is_string( $identifier ) && $langCode === $identifier ) ||
				( is_numeric( $identifier ) && $wpmlHomePage['id'] === $identifier )
			) {
				return $wpmlHomePage['url'];
			}
		}

		return '';
	}

	/**
	 * Returns the homepage IDs.
	 *
	 * @since 4.2.1
	 *
	 * @return array An array of home page ids.
	 */
	public function wpmlHomePages() {
		global $sitepress;
		static $homePages = [];

		if ( ! $this->isWpmlActive() || empty( $sitepress ) || ! method_exists( $sitepress, 'language_url' ) ) {
			return $homePages;
		}

		if ( empty( $homePages ) ) {
			$languages  = apply_filters( 'wpml_active_languages', [] );
			$homePageId = (int) get_option( 'page_on_front' );
			foreach ( $languages as $language ) {
				$homePages[ $language['code'] ] = [
					'id'  => apply_filters( 'wpml_object_id', $homePageId, 'page', false, $language['code'] ),
					'url' => $sitepress->language_url( $language['code'] )
				];
			}
		}

		return $homePages;
	}

	/**
	 * Returns if the post id os a WPML home page.
	 *
	 * @since 4.2.1
	 *
	 * @param  int  $postId The post ID.
	 * @return bool         Is the post id a home page.
	 */
	public function wpmlIsHomePage( $postId ) {
		foreach ( $this->wpmlHomePages() as $wpmlHomePage ) {
			if ( $wpmlHomePage['id'] === $postId ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the WPML url format.
	 *
	 * @since 4.2.8
	 *
	 * @return string The format.
	 */
	public function getWpmlUrlFormat() {
		global $sitepress;

		if (
			! $this->isWpmlActive() ||
			empty( $sitepress ) ||
			! method_exists( $sitepress, 'get_setting' )
		) {
			return '';
		}

		switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
			case WPML_LANGUAGE_NEGOTIATION_TYPE_DIRECTORY:
			case 1:
				return 'directory';
			case WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN:
			case 2:
				return 'domain';
			case WPML_LANGUAGE_NEGOTIATION_TYPE_PARAMETER:
			case 3:
				return 'parameter';
			default:
				return '';
		}
	}

	/**
	 * Checks whether the WooCommerce Follow Up Emails plugin is active.
	 *
	 * @since 4.2.2
	 *
	 * @return bool Whether the plugin is active.
	 */
	public function isWooCommerceFollowupEmailsActive() {
		$isActive = defined( 'FUE_VERSION' ) || is_plugin_active( 'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' );

		return $isActive;
	}

	/**
	 * Checks if the current page is an AMP page.
	 *
	 * @since 4.2.3
	 *
	 * @param  string $pluginName The name of the AMP plugin to check for (optional).
	 * @return bool               Whether the current page is an AMP page.
	 */
	public function isAmpPage( $pluginName = '' ) {
		// Official AMP plugin.
		if ( 'amp' === $pluginName ) {
			// If we're checking for the AMP page plugin specifically, return early if it's not active.
			// Otherwise, we'll return true if AMP for WP is enabled because the helper method doesn't distinguish between the two.
			if ( ! defined( 'AMP__VERSION' ) ) {
				return false;
			}

			$options = get_option( 'amp-options' );
			if ( ! empty( $options['theme_support'] ) && 'standard' === strtolower( $options['theme_support'] ) ) {
				return true;
			}
		}

		return $this->isAmpPageHelper();
	}

	/**
	 * Helper function for {@see isAmpPage()}.
	 * Checks if the current page is an AMP page.
	 *
	 * @since 4.2.4
	 *
	 * @return bool Whether the current page is an AMP page.
	 */
	private function isAmpPageHelper() {
		// First check for the existence of any AMP plugin functions. Bail early if none are found, and prevent false positives.
		if (
			! function_exists( 'amp_is_request' ) &&
			! function_exists( 'is_amp_endpoint' ) &&
			! function_exists( 'ampforwp_is_amp_endpoint' ) &&
			! function_exists( 'is_amp_wp' )
		) {
			// If none of the AMP plugin functions are found, return false and allow compatibility with custom implementations.
			return apply_filters( 'aioseo_is_amp_page', false );
		}

		if ( did_action( 'parse_query' ) ) {
			// Check for the "AMP" plugin.
			if ( function_exists( 'amp_is_request' ) ) {
				return (bool) amp_is_request();
			}

			// Check for the "AMP" plugin (`is_amp_endpoint()` is deprecated).
			if ( function_exists( 'is_amp_endpoint' ) ) {
				return (bool) is_amp_endpoint();
			}

			// Check for the "AMP for WP – Accelerated Mobile Pages" plugin.
			if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
				return (bool) ampforwp_is_amp_endpoint();
			}

			// Check for the "AMP WP" plugin.
			if ( function_exists( 'is_amp_wp' ) ) {
				return (bool) is_amp_wp();
			}
		}

		return false;
	}

	/**
	 * If we're in a LearnPress lesson page, return the lesson ID.
	 *
	 * @since 4.3.1
	 *
	 * @return int|false
	 */
	public function getLearnPressLesson() {
		global $lp_course_item;
		if ( $lp_course_item && method_exists( $lp_course_item, 'get_id' ) ) {
			return $lp_course_item->get_id();
		}

		return false;
	}

	/**
	 * Set a flag to indicate Divi whether it is processing internal content or not.
	 *
	 * @since 4.4.3
	 *
	 * @param  null|bool $flag The flag value.
	 * @return null|bool       The previous flag value to reset it later.
	 */
	public function setDiviInternalRendering( $flag ) {
		if ( ! defined( 'ET_BUILDER_VERSION' ) ) {
			return null;
		}

		global $et_pb_rendering_column_content;

		$originalValue                  = $et_pb_rendering_column_content;
		$et_pb_rendering_column_content = $flag;

		return $originalValue;
	}

	/**
	 * Checks whether the current request is being done by a crawler from Yandex.
	 *
	 * @since 4.4.0
	 *
	 * @return bool Whether the current request is being done by a crawler from Yandex.
	 */
	public function isYandexUserAgent() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		return preg_match( '#.*Yandex.*#', $_SERVER['HTTP_USER_AGENT'] );
	}
}