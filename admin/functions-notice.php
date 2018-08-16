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
	 * Set Notice for Yoast Detected
	 *
	 * Adds 2 Notices. 1) For importing Yoast into AIOSEOP, and 2) for importing
	 * into AIOSEOP.
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_set_yoast_detected( $update = false, $reset = false ) {
		global $aioseop_notices;

		$notice_1 = aioseop_notice_yoast_detected_1();
		$notice_2 = aioseop_notice_yoast_detected_2();
		if ( ! $aioseop_notices->insert_notice( $notice_1 ) ) {
			if ( $update ) {
				$aioseop_notices->update_notice( $notice_1 );
			}
			if ( $reset || ! isset( $aioseop_notices->active_notices[ $notice_1['slug'] ] ) ) {
				$aioseop_notices->activate_notice( $notice_1['slug'] );
			}
		}
		if ( ! $aioseop_notices->insert_notice( $notice_2 ) ) {
			if ( $update ) {
				$aioseop_notices->update_notice( $notice_2 );
			}
			if ( $reset || ! isset( $aioseop_notices->active_notices[ $notice_2['slug'] ] ) ) {
				$aioseop_notices->activate_notice( $notice_2['slug'] );
			}
		}
	}

	/**
	 * Notice - Yoast detected, import  Yoast to AIOSEOP
	 *
	 * @since 2.4.2
	 *
	 * @return array
	 */
	function aioseop_notice_yoast_detected_1() {
		return array(
			'slug'           => 'yoast_detected_1',
			'delay_time'     => 0,
			'message'        => __( 'The plugin Yoast SEO has been detected. Do you want to import its settings into All in One SEO Pack?', 'all-in-one-seo-pack' ),
			'class'          => 'notice-warning',
			'target'         => 'site',
			'screens'        => array(),
			'action_options' => array(
				'time'    => 0,
				'text'    => __( 'Import Settings', 'all-in-one-seo-pack' ),
				'link'    => add_query_arg( array( '_wpnonce' => wp_create_nonce( 'aiosp-import' ) ), admin_url( 'tools.php?page=aiosp_import' ) ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
		);
	}

	/**
	 * Notice - Yoast detected, import AIOSEOP to Yoast
	 *
	 * @since 2.4.2
	 *
	 * @return array
	 */
	function aioseop_notice_yoast_detected_2() {
		return array(
			'slug'           => 'yoast_detected_2',
			'delay_time'     => 0,
			'message'        => __( 'The plugin All-In-One-SEO has been detected. Do you want to import its settings?', 'all-in-one-seo-pack' ),
			'class'          => 'notice-error',
			'target'         => 'site',
			'screens'        => array(),
			'action_options' => array(
				'time'    => 0,
				'text'    => __( 'Import Settings', 'all-in-one-seo-pack' ),
				'link'    => add_query_arg( array( '_wpnonce' => wp_create_nonce( 'wpseo-import' ) ), admin_url( 'admin.php?page=wpseo_tools&tool=import-export&import=1&importaioseo=1#top#import-seo' ) ),
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		);
	}

	/**
	 * Disable Notice for Yoast Detected
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_notice_disable_yoast_detected() {
		global $aioseop_notices;
		$aioseop_notices->deactivate_notice( 'yoast_detected_1' );
		$aioseop_notices->deactivate_notice( 'yoast_detected_2' );
	}
}
