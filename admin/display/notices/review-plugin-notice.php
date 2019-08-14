<?php
/**
 * Review Plugin Notice
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

/**
 * Notice - Review Plugin
 *
 * @since 3.0
 *
 * @return array Notice configuration.
 */
function aioseop_notice_review_plugin() {
	return array(
		'slug'           => 'review_plugin',
		'delay_time'     => 1036800,
		'target'         => 'user',
		'screens'        => array( 'aioseop' ),
		'class'          => 'notice-info',
		/* translators: %1$s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. */
		'message'        => sprintf( __( 'You have been using %1$s for a while now. That is awesome! If you like %2$s, then please leave us a 5-star rating. Huge thanks in advance!', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME, AIOSEOP_PLUGIN_NAME ),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Add a review', 'all-in-one-seo-pack' ),
				'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
				'dismiss' => false,
				'class'   => 'button-primary button-orange',
			),
			array(
				'text'    => __( 'Remind me later', 'all-in-one-seo-pack' ),
				'time'    => 432000,
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
			array(
				'time'    => 0,
				'text'    => __( 'No, thanks', 'all-in-one-seo-pack' ),
				'dismiss' => true,
				'class'   => 'button-secondary',
			),
		),
	);
}
// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
// add_filter( 'aioseop_admin_notice-review_plugin', 'aioseop_notice_review_plugin' );
