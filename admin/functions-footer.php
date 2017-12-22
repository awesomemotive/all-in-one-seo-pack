<?php
/**
 * Footer Functions for AIOSEOP_Footers
 *
 * @since 2.4.2
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Footers
 */

if ( class_exists( 'AIOSEOP_Footers' ) ) {
	/**
	 * Footer Review Footers
	 *
	 * @see aioseop_footer_set_activation_review_plugin() in `all-in-one-seo-pack\admin\display\functions-footer.php`.
	 *
	 * @global AIOSEOP_Footers $aioseop_footers
	 */
	function aioseop_footer_set_activation_review_plugin() {
		global $aioseop_footers;

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

		$aioseop_footers->insert_footer( $footer );
	}

	/**
	 * Remove Footer Review Footer
	 *
	 * Concept for removing review footer.
	 *
	 * @see aioseop_footer_set_activation_review_plugin() in `all-in-one-seo-pack\admin\display\functions-footer.php`.
	 *
	 * @global AIOSEOP_Footers $aioseop_footers
	 */
	function aioseop_footer_remove_activation_review_plugin() {
		global $aioseop_footers;
		$slug = 'activation_review';
		$aioseop_footers->remove_footer( $slug );
	}
}
