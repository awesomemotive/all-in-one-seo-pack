<?php
/**
 * Notice Functions for AIOSEOP_Notices
 *
 * @since 3.0
 * @package All-in-One-SEO-Pack
 * @subpackage AIOSEOP_Notices
 */

if ( class_exists( 'AIOSEOP_Notices' ) ) {

	include_once AIOSEOP_PLUGIN_DIR . 'admin/display/notices/sitemap-indexes.php';

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
			'message'        => __( 'We have detected you are running WooCommerce. Upgrade to All in One SEO Pack Pro to unlock our advanced e-commerce features, including SEO for Product Categories and more.', 'all-in-one-seo-pack' ),

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

	/**
	 * Set Notice on Activation to Review Plugin
	 *
	 * A delayed notice that is set during activation, or initialization (old installs),
	 * to later display a review/rate AIOSEOP plugin. Delay time: 12 days.
	 * Delay "...give me a week." 5 days
	 *
	 * @since 3.0
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_set_activation_review_plugin( $update = false, $reset = false ) {
		global $aioseop_notices;

		// TODO Optimize - Create a callback function/method to store most of the configurations (Avoid Database concept).
		// Dynamic variable could be stored in the database. Config functions could go into a config file/folder.
		$notice = aioseop_notice_review_plugin();

		if ( $aioseop_notices->insert_notice( $notice ) ) {
			// aioseop_footer_set_review();
		} elseif ( $update ) {
			$aioseop_notices->update_notice( $notice );

			if ( $reset ) {
				$aioseop_notices->activate_notice( $notice['slug'] );
				// aioseop_footer_remove_review();
				// aioseop_footer_set_review();
			}
		}
	}

	/**
	 * Notice - Review Plugin
	 *
	 * @since 3.0
	 *
	 * @return array Notice configuration.
	 */
	function aioseop_notice_review_plugin() {
		return array(
			'slug'           => 'activation_review_plugin',
			'delay_time'     => 1036800,
			'target'         => 'user',
			'screens'        => array(),
			'class'          => 'notice-info',
			'message'        => __( 'You have been using All in One SEO Pack for a while now. That is awesome! If you like All in One SEO Pack, then please leave us a 5-star rating. Huge thanks in advance!', 'all-in-one-seo-pack' ),
			'action_options' => array(
				array(
					'time'    => 0,
					'text'    => __( 'Add a review', 'all-in-one-seo-pack' ),
					'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'text'    => __( 'Remind me later', 'all-in-one-seo-pack' ),
					'time'    => 432000,
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'time'    => 0,
					'text'    => __( 'No, thanks', 'all-in-one-seo-pack' ),
					'dismiss' => true,
					'class'   => '',
				),
			),
		);
	}
}
