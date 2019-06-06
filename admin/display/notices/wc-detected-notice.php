<?php
/**
 * WooCommerce Detected Notice
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

/**
 * Notice - Pro Promotion for WooCommerce
 *
 * @since 3.0
 *
 * @return array
 */
function aioseop_notice_pro_promo_woocommerce() {
	return array(
		'slug'           => 'woocommerce_detected',
		'delay_time'     => 0,
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the premium version of the plugin, All in One SEO Pack Pro. */
		'message'        => sprintf( __( 'We have detected you are running WooCommerce. Upgrade to %s to unlock our advanced e-commerce features, including SEO for Product Categories and more.', 'all-in-one-seo-pack' ), 'All in One SEO Pack Pro' ),

		'class'          => 'notice-info',
		'target'         => 'site',
		'screens'        => array( 'aioseop' ),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Upgrade', 'all-in-one-seo-pack' ),
				'link'    => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=woo',
				'dismiss' => false,
				'class'   => 'button-primary button-orange',
			),
			array(
				'time'    => 2592000, // 30 days.
				'text'    => __( 'No Thanks', 'all-in-one-seo-pack' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		),
	);
}
add_filter( 'aioseop_admin_notice-woocommerce_detected', 'aioseop_notice_pro_promo_woocommerce' );
