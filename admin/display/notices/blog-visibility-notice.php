<?php
/**
 * Blog Visibility Notice
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

/**
 * Notice - Blog Visibility
 *
 * Displays when blog disables search engines from indexing.
 *
 * @since 3.0
 *
 * @return array Notice configuration.
 */
function aioseop_notice_blog_visibility() {
	$text_link = '<a href="' . admin_url( 'options-reading.php' ) . '">' . __( 'Reading Settings', 'all-in-one-seo-pack' ) . '</a>';

	return array(
		'slug'           => 'blog_public_disabled',
		'delay_time'     => 0,
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, All in One SEO Pack. "Settings > Reading" refers to the "Reading" submenu in WordPress Core. */
		'message'        => sprintf( __( 'Warning: %s has detected that you are blocking access to search engines. You can change this in Settings > Reading if this was unintended.', 'all-in-one-seo-pack' ), AIOSEOP_PLUGIN_NAME ),
		'class'          => 'notice-error',
		'target'         => 'site',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Update Reading Settings', 'all-in-one-seo-pack' ),
				'link'    => admin_url( 'options-reading.php' ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 604800,
				'text'    => __( 'Remind me later', 'all-in-one-seo-pack' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		),
	);
}
add_filter( 'aioseop_admin_notice-blog_public_disabled', 'aioseop_notice_blog_visibility' );
