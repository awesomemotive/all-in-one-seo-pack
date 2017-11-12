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
	 * Footer Review Notices
	 *
	 * @see aioseop_notice_set_activation_review_plugin() in `all-in-one-seo-pack\admin\display\notice-functions.php`.
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_footer_set_review() {
		global $aioseop_notices;

		$footer = array(
			'slug'           => 'activation_review',
			'delay_time'     => 0,
			'html'           => __( 'Thank you for using All in One SEO Plugin for WordPress. If you enjoy your experience, feel free to give us a %1$s rating. %2$s / %3$s', 'all-in-one-seo-pack' ),
			'action_options' => array(),
			'layer_level'    => 10,
			'screens'        => array( 'aioseop' ),
		);

		$footer['action_options'][] = array(
			'time'    => 0,
			'text'    => '&#9733;&#9733;&#9733;&#9733;&#9733;',
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => 'aioseop-rating-link',
		);
		$footer['action_options'][] = array(
			'time'    => 0,
			'text'    => 'Review AIOSEOP',
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => '',
		);
		$footer['action_options'][] = array(
			'time'    => 0,
			'text'    => 'Dismiss',
			'dismiss' => true,
			'class'   => '',
		);

		$aioseop_notices->insert_footer( $footer );
	}

	/**
	 * Remove Footer Review Notice
	 *
	 * Concept for removing review footer.
	 *
	 * @see aioseop_notice_set_activation_review_plugin() in `all-in-one-seo-pack\admin\display\notice-functions.php`.
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_footer_remove_review() {
		global $aioseop_notices;
		$slug = 'activation_review';
		$aioseop_notices->remove_footer( $slug );
	}
}
