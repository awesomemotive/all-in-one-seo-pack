<?php
/**
 * Sitemap Index Notice
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

/**
 * Notice - Sitemap Indexes
 *
 * @since 3.0
 *
 * @return array
 */
function aioseop_notice_sitemap_indexes() {
	return array(
		'slug'           => 'sitemap_max_warning',
		'delay_time'     => 0,
		'message'        => __( 'Notice: To avoid problems with your XML Sitemap, we strongly recommend you set the Maximum Posts per Sitemap Page to 1,000.', 'all-in-one-seo-pack' ),
		'class'          => 'notice-warning',
		'target'         => 'user',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Update Sitemap Settings', 'all-in-one-seo-pack' ),
				'link'    => esc_url( get_admin_url( null, 'admin.php?page=' . AIOSEOP_PLUGIN_DIRNAME . '/modules/aioseop_sitemap.php' ) ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 86400, // 24 hours.
				'text'    => __( 'Remind me later', 'all-in-one-seo-pack' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),

		),
	);
}
add_filter( 'aioseop_admin_notice-sitemap_max_warning', 'aioseop_notice_sitemap_indexes' );
