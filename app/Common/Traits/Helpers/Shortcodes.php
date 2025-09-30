<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains shortcode specific helper methods.
 *
 * @since 4.1.2
 */
trait Shortcodes {
	/**
	 * Shortcodes known to conflict with AIOSEO.
	 * NOTE: This is deprecated and only there for users who already were using the aioseo_conflicting_shortcodes_hook before 4.2.0.
	 *
	 * @since 4.1.2
	 *
	 * @var array
	 */
	private $conflictingShortcodes = [
		'WooCommerce Login'                => 'woocommerce_my_account',
		'WooCommerce Checkout'             => 'woocommerce_checkout',
		'WooCommerce Order Tracking'       => 'woocommerce_order_tracking',
		'WooCommerce Cart'                 => 'woocommerce_cart',
		'WooCommerce Registration'         => 'wwp_registration_form',
		'WISDM Group Registration'         => 'wdm_group_users',
		'WISDM Quiz Reporting'             => 'wdm_quiz_statistics_details',
		'WISDM Course Review'              => 'rrf_course_review',
		'Simple Membership Login'          => 'swpm_login_form',
		'Simple Membership Mini Login'     => 'swpm_mini_login',
		'Simple Membership Payment Button' => 'swpm_payment_button',
		'Simple Membership Thank You Page' => 'swpm_thank_you_page_registration',
		'Simple Membership Registration'   => 'swpm_registration_form',
		'Simple Membership Profile'        => 'swpm_profile_form',
		'Simple Membership Reset'          => 'swpm_reset_form',
		'Simple Membership Update Level'   => 'swpm_update_level_to',
		'Simple Membership Member Info'    => 'swpm_show_member_info',
		'Revslider'                        => 'rev_slider'
	];

	/**
	 * Returns the content with shortcodes replaced.
	 *
	 * @since 4.0.5
	 *
	 * @param  string $content  The post content.
	 * @param  bool   $override Whether shortcodes should be parsed regardless of the context. Needed for ActionScheduler actions.
	 * @param  int    $postId   The post ID (optional).
	 * @return string $content  The post content with shortcodes replaced.
	 */
	public function doShortcodes( $content, $override = false, $postId = 0 ) {
		// NOTE: This is_admin() check can never be removed because themes like Avada will otherwise load the wrong post.
		if ( ! $override && is_admin() ) {
			return $content;
		}

		if ( ! wp_doing_cron() && ! wp_doing_ajax() ) {
			if ( ! $override && apply_filters( 'aioseo_disable_shortcode_parsing', false ) ) {
				return $content;
			}

			if ( ! $override && ! aioseo()->options->searchAppearance->advanced->runShortcodes ) {
				return $this->doAllowedShortcodes( $content, $postId );
			}
		}

		$content = $this->doShortcodesHelper( $content, [], $postId );

		return $content;
	}

	/**
	 * Returns the content with only the allowed shortcodes and wildcards replaced.
	 *
	 * @since   4.1.2
	 * @version 4.6.6 Added the $allowedTags parameter.
	 *
	 * @param  string $content     The content.
	 * @param  int    $postId      The post ID (optional).
	 * @param  array  $allowedTags The shortcode tags to allow (optional).
	 * @return string              The content with shortcodes replaced.
	 */
	public function doAllowedShortcodes( $content, $postId = null, $allowedTags = [] ) {
		// Extract list of shortcodes from the post content.
		$tags = $this->getShortcodeTags( $content );
		if ( ! count( $tags ) ) {
			return $content;
		}

		$allowedTags  = apply_filters( 'aioseo_allowed_shortcode_tags', $allowedTags );
		$tagsToRemove = array_diff( $tags, $allowedTags );

		$content = $this->doShortcodesHelper( $content, $tagsToRemove, $postId );

		return $content;
	}

	/**
	 * Returns the content with only the allowed shortcodes and wildcards replaced.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $content      The content.
	 * @param  array  $tagsToRemove The shortcode tags to remove (optional).
	 * @param  int    $postId       The post ID (optional).
	 * @return string               The content with shortcodes replaced.
	 */
	private function doShortcodesHelper( $content, $tagsToRemove = [], $postId = 0 ) {
		global $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$conflictingShortcodes = array_merge( $tagsToRemove, $this->conflictingShortcodes );
		$conflictingShortcodes = apply_filters( 'aioseo_conflicting_shortcodes', $conflictingShortcodes );

		$tagsToRemove = [];
		foreach ( $conflictingShortcodes as $shortcode ) {
			$shortcodeTag = str_replace( [ '[', ']' ], '', $shortcode );
			if ( array_key_exists( $shortcodeTag, $shortcode_tags ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				$tagsToRemove[ $shortcodeTag ] = $shortcode_tags[ $shortcodeTag ]; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			}
		}

		// Remove all conflicting shortcodes before parsing the content.
		foreach ( $tagsToRemove as $shortcodeTag => $shortcodeCallback ) {
			remove_shortcode( $shortcodeTag );
		}

		if ( $postId ) {
			global $post;
			$post = get_post( $postId );
			if ( is_a( $post, 'WP_Post' ) ) {
				// Add the current post to the loop so that shortcodes can use it if needed.
				setup_postdata( $post );
			}
		}

		// Set a flag to indicate Divi that it's processing internal content.

		$default = aioseo()->helpers->setDiviInternalRendering( true );

		$content = do_shortcode( $content );

		// Reset the Divi flag to its default value.
		aioseo()->helpers->setDiviInternalRendering( $default );

		if ( $postId ) {
			wp_reset_postdata();
		}

		// Add back shortcodes as remove_shortcode() disables them site-wide.
		foreach ( $tagsToRemove as $shortcodeTag => $shortcodeCallback ) {
			add_shortcode( $shortcodeTag, $shortcodeCallback );
		}

		return $content;
	}

	/**
	 * Extracts the shortcode tags from the content.
	 *
	 * @since 4.1.2
	 *
	 * @param  string $content The content.
	 * @return array  $tags    The shortcode tags.
	 */
	private function getShortcodeTags( $content ) {
		$tags    = [];
		$pattern = '\\[(\\[?)([^\s]*)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
		if ( preg_match_all( "#$pattern#s", (string) $content, $matches ) && array_key_exists( 2, $matches ) ) {
			$tags = array_unique( $matches[2] );
		}

		if ( ! count( $tags ) ) {
			return $tags;
		}

		// Extract nested shortcodes.
		foreach ( $matches[5] as $innerContent ) {
			$tags = array_merge( $tags, $this->getShortcodeTags( $innerContent ) );
		}

		return $tags;
	}
}