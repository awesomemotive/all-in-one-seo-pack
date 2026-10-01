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
	 * Returns the content with only the allowed shortcodes replaced.
	 *
	 * @since   4.1.2
	 * @version 4.6.6 Added the $allowedTags parameter.
	 * @version 5.0.2.1 Expand only the allowed tags instead of removing every other one.
	 *
	 * @param  string $content     The content.
	 * @param  int    $postId      The post ID (optional).
	 * @param  array  $allowedTags The shortcode tags to allow (optional).
	 * @return string              The content with shortcodes replaced.
	 */
	public function doAllowedShortcodes( $content, $postId = null, $allowedTags = [] ) {
		$allowedTags = apply_filters( 'aioseo_allowed_shortcode_tags', $allowedTags );
		foreach ( $allowedTags as $index => $allowedTag ) {
			$allowedTags[ $index ] = str_replace( [ '[', ']' ], '', $allowedTag );
		}

		// Nothing is allowed, so there is nothing to expand.
		if ( ! $allowedTags ) {
			return $content;
		}

		global $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		// Narrow the registry to the allowed tags rather than unregistering every other one. Removing
		// the complement only works if we tokenize the content exactly as core does, and we cannot.
		$registered   = $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		$fullRegistry = array_diff_key( $registered, array_flip( $this->getConflictingShortcodeTags() ) );

		$narrowed = [];
		foreach ( array_intersect_key( $registered, array_flip( $allowedTags ) ) as $tag => $callback ) {
			// The narrowing only guards the content we were given. An allowed callback that expands
			// shortcodes of its own (e.g. a SiteOrigin widget) needs the full registry for them.
			$narrowed[ $tag ] = function ( $attributes, $content, $shortcodeTag ) use ( $callback, $fullRegistry ) {
				global $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

				$outer          = $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				$shortcode_tags = $fullRegistry; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

				try {
					return call_user_func( $callback, $attributes, $content, $shortcodeTag );
				} finally {
					$shortcode_tags = $outer; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				}
			};
		}

		$shortcode_tags = $narrowed; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		try {
			return $this->doShortcodesHelper( $content, [], $postId );
		} finally {
			$shortcode_tags = $registered; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
		}
	}

	/**
	 * Replaces the shortcodes in the content, minus the conflicting ones.
	 *
	 * @since   4.1.2
	 * @version 5.0.2.1 Restore the registry, Divi flag and post data even if a callback throws.
	 *
	 * @param  string $content      The content.
	 * @param  array  $tagsToRemove The shortcode tags to remove (optional).
	 * @param  int    $postId       The post ID (optional).
	 * @return string               The content with shortcodes replaced.
	 */
	private function doShortcodesHelper( $content, $tagsToRemove = [], $postId = 0 ) {
		global $shortcode_tags; // phpcs:ignore Squiz.NamingConventions.ValidVariableName

		$conflictingTags = $this->getConflictingShortcodeTags( $tagsToRemove );
		$tagsToRemove    = [];
		foreach ( $conflictingTags as $shortcodeTag ) {
			if ( array_key_exists( $shortcodeTag, $shortcode_tags ) ) { // phpcs:ignore Squiz.NamingConventions.ValidVariableName
				$tagsToRemove[ $shortcodeTag ] = $shortcode_tags[ $shortcodeTag ]; // phpcs:ignore Squiz.NamingConventions.ValidVariableName
			}
		}

		$default     = null;
		$diviFlagSet = false;

		try {
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

			$default     = aioseo()->helpers->setDiviInternalRendering( true );
			$diviFlagSet = true;

			return do_shortcode( $content );
		} finally {
			// Reset the Divi flag to its default value. That value is null on the first call of a
			// request, so it cannot double as the "was it set" check.
			if ( $diviFlagSet ) {
				aioseo()->helpers->setDiviInternalRendering( $default );
			}

			if ( $postId ) {
				wp_reset_postdata();
			}

			// Add back shortcodes as remove_shortcode() disables them site-wide.
			foreach ( $tagsToRemove as $shortcodeTag => $shortcodeCallback ) {
				add_shortcode( $shortcodeTag, $shortcodeCallback );
			}
		}
	}

	/**
	 * Returns the tags of the shortcodes known to conflict with AIOSEO.
	 *
	 * @since 5.0.2.1
	 *
	 * @param  array $tagsToRemove Additional shortcode tags to treat as conflicting (optional).
	 * @return array               The shortcode tags.
	 */
	private function getConflictingShortcodeTags( $tagsToRemove = [] ) {
		$conflictingShortcodes = array_merge( $tagsToRemove, $this->conflictingShortcodes );
		$conflictingShortcodes = apply_filters( 'aioseo_conflicting_shortcodes', $conflictingShortcodes );

		return array_map( function ( $shortcode ) {
			return str_replace( [ '[', ']' ], '', $shortcode );
		}, (array) $conflictingShortcodes );
	}

	/**
	 * Returns visitor input decoded and without square brackets, so it can never form a shortcode.
	 * NOTE: The result is decoded text, so escape it for the context it is output in.
	 *
	 * @since 5.0.2.1
	 *
	 * @param  string $string The visitor input.
	 * @return string         The input without square brackets.
	 */
	public function removeShortcodeBrackets( $string ) {
		$string = (string) $string;

		// Decode until nothing changes. Any entity left over could still decode to a bracket
		// further down the line, so a fixed number of passes is not enough.
		do {
			$previous = $string;
			$string   = $this->decodeHtmlEntities( $string );
		} while ( $previous !== $string );

		return str_replace( [ '[', ']' ], '', $string );
	}
}