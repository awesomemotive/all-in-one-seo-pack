<?php
/**
 * Blog Visibility Notice
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */


/**
 * Set Notice for Disabled Public Blog
 *
 * Admin Notice when "Discourage search engines from indexing this site" is
 * enabled in Settings > Reading.
 *
 * @since 3.0
 *
 * @global AIOSEOP_Notices $aioseop_notices
 *
 * @param boolean $update Updates the notice with new content and configurations.
 * @param boolean $reset  Notice are re-initiated.
 */
function aioseop_notice_set_blog_public_disabled( $update = false, $reset = false ) {
	global $aioseop_notices;

	$notice = aioseop_notice_blog_visibility();

	if ( ! $aioseop_notices->insert_notice( $notice ) ) {
		if ( $update ) {
			$aioseop_notices->update_notice( $notice );
		}
		if ( $reset || ! isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) ) {
			$aioseop_notices->activate_notice( $notice['slug'] );
		}
	}
}

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
		/* Translators: A link containing text. Which is handled with $text_link. */
		'message'        => __( 'Warning: All in One SEO Pack has detected that you are blocking access to search engines. You can change this in Settings > Reading if this was unintended.', 'all-in-one-seo-pack' ),
		'delay_options'  => array(),
		'class'          => 'notice-error',
		'target'         => 'site',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Settings > Reading', 'all-in-one-seo-pack' ),
				'link'    => admin_url( 'options-reading.php' ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 604800,
				'text'    => __( 'Remind me later.', 'all-in-one-seo-pack' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		),
	);
}

/**
 * Disable Notice for Disabled Public Blog
 *
 * @since 3.0
 *
 * @global AIOSEOP_Notices $aioseop_notices
 */
function aioseop_notice_disable_blog_public_disabled() {
	global $aioseop_notices;
	$aioseop_notices->deactivate_notice( 'blog_public_disabled' );
}
