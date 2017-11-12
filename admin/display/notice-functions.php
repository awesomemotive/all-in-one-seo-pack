<?php
/**
 * Notice Functions for AIOSEOP_Notices
 *
 * @since 2.4.2
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

if ( class_exists( 'AIOSEOP_Notices' ) ) {
	/**
	 * Notices Full Reset
	 *
	 * @since 2.4.2
	 */
	function aioseop_notices_full_reset() {
		aioseop_notice_set_activation_review_plugin( false, true );
		aioseop_notice_set_blog_public_disabled( false, true );
		aioseop_notice_set_yoast_detected( false, true );
		aioseop_notice_set_woocommerce_detected_on_nonpro( false, true );
	}
	/**
	 * Set Notice on Activation to Review Plugin
	 *
	 * A delayed notice that is set during activation, or initialization (old installs),
	 * to later display a review/rate AIOSEOP plugin. Delay time: 12 days.
	 * Delay "...give me a week." 5 days
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_set_activation_review_plugin( $update = false, $reset = false ) {
		global $aioseop_notices;

		$notice = array(
			'slug'          => 'activation_review_plugin',
			'delay_time'    => 1036800,
			'message'       => __( 'Looks like you\'ve been using All in One SEO Plugin for awhile now, and that\'s awesome! We are an open source community built from other\'s contributions. By helping us with a 5-star review, it also helps us to reach out to more people.', 'all-in-one-seo-pack' ),
			'delay_options' => array(),

			'target'        => 'user',
			'screens'       => array(),
		);

		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => __( 'Yes, absolutely!', 'all-in-one-seo-pack' ),
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => '',
		);
		$notice['delay_options'][] = array(
			'text'    => 'Maybe, give me a Week.',
			'time'    => 432000,
			'dismiss' => false,
			'class'   => '',
		);
		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => 'Already Rated. Thank You!',
			'dismiss' => true,
			'class'   => '',
		);

		if ( $aioseop_notices->insert_notice( $notice ) ) {
			aioseop_footer_review();
		} elseif ( $update ) {
			$aioseop_notices->update_notice( $notice );

			if ( $reset ) {
				$aioseop_notices->activate_notice( $slug );
				aioseop_footer_remove_review();
				aioseop_footer_set_review();
			}
		}
	}
}
