<?php
/**
 * Notice Functions for AIOSEOP_Notices
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

if ( class_exists( 'AIOSEOP_Notices' ) ) {

	/**
	 * Set Notice with WooCommerce Detected on Non-Pro AIOSEOP
	 *
	 * When WC is detected on Non-Pros, and message is displayed to upgrade to
	 * AIOSEOP Pro. "No Thanks" delays for 30 days.
	 *
	 * @since 3.0
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_activate_pro_promo_woocommerce( $update = false, $reset = false ) {
		global $aioseop_notices;

		$notice = aioseop_notice_pro_promo_woocommerce();

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
			'message'        => __( 'We\'ve detected you\'re running WooCommerce. Upgrade to All in One SEO Pack Pro for increased SEO compatibility for your products.', 'all-in-one-seo-pack' ),

			'class'          => 'notice-info',
			'target'         => 'user',
			'screens'        => array(),
			'action_options' => array(
				array(
					'time'    => 0,
					'text'    => __( 'Upgrade', 'all-in-one-seo-pack' ),
					'link'    => 'https://semperplugins.com/plugins/all-in-one-seo-pack-pro-version/?loc=woo',
					'dismiss' => false,
					'class'   => 'button-primary',
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

	/**
	 * Disable Notice for WooCommerce/Upgrade-to-Pro
	 *
	 * @todo Add to Pro version to disable message set by Non-Pro.
	 *
	 * @since 3.0
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_notice_disable_woocommerce_detected_on_nonpro() {
		global $aioseop_notices;
		$aioseop_notices->deactivate_notice( 'woocommerce_detected' );
	}
}
