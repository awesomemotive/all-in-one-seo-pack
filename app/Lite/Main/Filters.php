<?php
namespace AIOSEO\Plugin\Lite\Main;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Main as CommonMain;

/**
 * Filters class with methods that are called.
 *
 * @since 4.0.0
 */
class Filters extends CommonMain\Filters {
	/**
	 * Registers our row meta for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions    List of existing actions.
	 * @param  string $pluginFile The plugin file.
	 * @return array              List of action links.
	 */
	public function pluginRowMeta( $actions, $pluginFile = '' ) {
		$reviewLabel = str_repeat( '<span class="dashicons dashicons-star-filled" style="font-size: 18px; width:16px; height: 16px; color: #ffb900;"></span>', 5 );

		$actionLinks = [
			'suggest-feature' => [
				// Translators: This is an action link users can click to open a feature request.
				'label' => __( 'Suggest a Feature', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'suggest-a-feature/', 'plugin-row-meta', 'feature' ),
			],
			'review'          => [
				'label' => $reviewLabel,
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'review-aioseo', 'plugin-row-meta', 'review' ),
				'title' => sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO").
					__( 'Rate %1$s', 'all-in-one-seo-pack' ),
					'AIOSEO'
				)
			]
		];

		return $this->parseActionLinks( $actions, $pluginFile, $actionLinks );
	}

	/**
	 * Registers our action links for the plugins page.
	 *
	 * @since 4.0.0
	 *
	 * @param  array  $actions    List of existing actions.
	 * @param  string $pluginFile The plugin file.
	 * @return array              List of action links.
	 */
	public function pluginActionLinks( $actions, $pluginFile = '' ) {
		$actionLinks = [
			'settings'   => [
				'label' => __( 'SEO Settings', 'all-in-one-seo-pack' ),
				'url'   => get_admin_url( null, 'admin.php?page=aioseo-settings' ),
			],
			'support'    => [
				// Translators: This is an action link users can click to open our premium support.
				'label' => __( 'Support', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'contact/', 'plugin-action-links', 'Support' ),
			],
			'docs'       => [
				// Translators: This is an action link users can click to open our general documentation page.
				'label' => __( 'Documentation', 'all-in-one-seo-pack' ),
				'url'   => aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'docs/', 'plugin-action-links', 'Documentation' ),
			],
			'proupgrade' => [
				// Translators: This is an action link users can click to purchase a license for All in One SEO Pro.
				'label' => __( 'Upgrade to Pro', 'all-in-one-seo-pack' ),
				'url'   => apply_filters( 'aioseo_upgrade_link', aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', 'plugin-action-links', 'Upgrade', false ) ),
			]
		];

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $this->parseActionLinks( $actions, $pluginFile, $actionLinks, 'before' );
	}
}