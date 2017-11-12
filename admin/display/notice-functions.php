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
	 * Notices Full Reset
	 *
	 * @since 2.4.2
	 */
	function aioseop_notices_full_reset() {
		aioseop_notice_set_activation_review_plugin( false, true );
		aioseop_notice_set_blog_public_disabled( false, true );
		aioseop_notice_set_yoast_detected( false, true );
		aioseop_notice_set_woocommerce_detected_on_nonpro( false, true );
	}
	/**
	 * Set Notice on Activation to Review Plugin
	 *
	 * A delayed notice that is set during activation, or initialization (old installs),
	 * to later display a review/rate AIOSEOP plugin. Delay time: 12 days.
	 * Delay "...give me a week." 5 days
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_set_activation_review_plugin( $update = false, $reset = false ) {
		global $aioseop_notices;

		$notice = array(
			'slug'          => 'activation_review_plugin',
			'delay_time'    => 1036800,
			'message'       => __( 'Looks like you\'ve been using All in One SEO Plugin for awhile now, and that\'s awesome! We are an open source community built from other\'s contributions. By helping us with a 5-star review, it also helps us to reach out to more people.', 'all-in-one-seo-pack' ),
			'delay_options' => array(),

			'target'        => 'user',
			'screens'       => array(),
		);

		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => __( 'Yes, absolutely!', 'all-in-one-seo-pack' ),
			'link'    => 'https://wordpress.org/support/plugin/all-in-one-seo-pack/reviews?rate=5#new-post',
			'dismiss' => false,
			'class'   => '',
		);
		$notice['delay_options'][] = array(
			'text'    => 'Maybe, give me a Week.',
			'time'    => 432000,
			'dismiss' => false,
			'class'   => '',
		);
		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => 'Already Rated. Thank You!',
			'dismiss' => true,
			'class'   => '',
		);

		if ( $aioseop_notices->insert_notice( $notice ) ) {
			aioseop_footer_review();
		} elseif ( $update ) {
			$aioseop_notices->update_notice( $notice );

			if ( $reset ) {
				$aioseop_notices->activate_notice( $slug );
				aioseop_footer_remove_review();
				aioseop_footer_set_review();
			}
		}
	}

	/**
	 * Set Notice for Disabled Public Blog
	 *
	 * Admin Notice when "Discourage search engines from indexing this site" is
	 * enabled in Settings > Reading.
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 *
	 * @param boolean $update Updates the notice with new content and configurations.
	 * @param boolean $reset  Notice are re-initiated.
	 */
	function aioseop_notice_set_blog_public_disabled( $update = false, $reset = false ) {
		global $aioseop_notices;

		global $wp_version;
		if ( version_compare( $wp_version, '3.5.0', '>=' ) || function_exists( 'set_url_scheme' ) ) {
			$text = __( 'Reading Settings', 'all-in-one-seo-pack' );
			$link = admin_url( 'options-reading.php' );

		} else {
			$text = __( 'Privacy Settings', 'all-in-one-seo-pack' );
			$link = admin_url( 'options-privacy.php' );
		}

		$notice = array(
			'slug'          => 'blog_public_disabled',
			'delay_time'    => 0,
			'message'       => sprintf( __( 'Warning: You\'re blocking access to search engines. You can change this in %1$s to toggle your blog visibility.', 'all-in-one-seo-pack' ), $text ),
			'delay_options' => array(),
			'class'         => 'notice-error',
			'target'        => 'site',
			'screens'       => array(),
		);

		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => $text,
			'link'    => $link,
			'dismiss' => false,
			'class'   => 'button-secondary',
		);
		$notice['delay_options'][] = array(
			'time'    => 604800,
			'text'    => __( 'Delay notice for a week.', 'all-in-one-seo-pack' ),
			'link'    => '',
			'dismiss' => false,
			'class'   => '',
		);

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
	 * Disable Notice for Disabled Public Blog
	 *
	 * @since 2.4.2
	 *
	 * @global AIOSEOP_Notices $aioseop_notices
	 */
	function aioseop_notice_disable_blog_public_disabled() {
		global $aioseop_notices;
		$aioseop_notices->deactivate_notice( 'blog_public_disabled' );
	}

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

		$yoasturl = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'wpseo-import' ) ), admin_url( 'admin.php?page=wpseo_tools&tool=import-export&import=1&importaioseo=1#top#import-seo' ) );
		$aiourl   = add_query_arg( array( '_wpnonce' => wp_create_nonce( 'aiosp-import' ) ), admin_url( 'tools.php?page=aiosp_import' ) );

		$notice = array(
			'slug'          => 'yoast_detected_1',
			'delay_time'    => 0,
			'message'       => __( 'The plugin Yoast SEO has been detected. Do you want to import its settings into All in One SEO Pack?', 'all-in-one-seo-pack' ),
			'delay_options' => array(),
			'class'         => 'notice-warning',
			'target'        => 'site',
			'screens'       => array(),
		);
		$notice['delay_options'][] = array(
			'time'    => 0,
			'text'    => __( 'Import Settings', 'all-in-one-seo-pack' ),
			'link'    => $aiourl,
			'dismiss' => false,
			'class'   => 'button-primary',
		);

		$notice2 = array(
			'slug'          => 'yoast_detected_2',
			'delay_time'    => 0,
			'message'       => __( 'The plugin All-In-One-SEO has been detected. Do you want to import its settings?', 'all-in-one-seo-pack' ),
			'delay_options' => array(),
			'class'         => 'notice-error',
			'target'        => 'site',
			'screens'       => array(),
		);
		$notice2['delay_options'][] = array(
			'time'    => 0,
			'text'    => __( 'Import Settings', 'all-in-one-seo-pack' ),
			'link'    => $yoasturl,
			'dismiss' => false,
			'class'   => 'button-secondary',
		);

		if ( ! $aioseop_notices->insert_notice( $notice ) ) {
			if ( $update ) {
				$aioseop_notices->update_notice( $notice );
			}
			if ( $reset || ! isset( $aioseop_notices->active_notices[ $notice['slug'] ] ) ) {
				$aioseop_notices->activate_notice( $notice['slug'] );
			}
		}
		if ( ! $aioseop_notices->insert_notice( $notice2 ) ) {
			if ( $update ) {
				$aioseop_notices->update_notice( $notice2 );
			}
			if ( $reset || ! isset( $aioseop_notices->active_notices[ $notice2['slug'] ] ) ) {
				$aioseop_notices->activate_notice( $notice2['slug'] );
			}
		}
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
