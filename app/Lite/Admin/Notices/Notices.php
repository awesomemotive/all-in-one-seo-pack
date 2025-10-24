<?php
namespace AIOSEO\Plugin\Lite\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Admin\Notices as CommonNotices;
use AIOSEO\Plugin\Common\Models;

/**
 * Lite version of the notices class.
 *
 * @since 4.0.0
 */
class Notices extends CommonNotices\Notices {
	/**
	 * Initialize the internal notices.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function initInternalNotices() {
		parent::initInternalNotices();

		$this->wooUpsellNotice();
	}

	/**
	 * Validates the notification type.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $type The notification type we are targeting.
	 * @return boolean       True if yes, false if no.
	 */
	public function validateType( $type ) {
		$validated = parent::validateType( $type );

		// Any lite notification should pass here.
		if ( 'lite' === $type ) {
			$validated = true;
		}

		return $validated;
	}

	/**
	 * Add a notice if WooCommerce is detected and not licensed or running Lite.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function wooUpsellNotice() {
		$notification = Models\Notification::getNotificationByName( 'woo-upsell' );

		if (
			! class_exists( 'WooCommerce' )
		) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'woo-upsell' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'woo-upsell',
			// Translators: 1 - "WooCommerce".
			'title'             => sprintf( __( 'Advanced %1$s Support', 'all-in-one-seo-pack' ), 'WooCommerce' ),
			// Translators: 1 - "WooCommerce", 2 - The plugin short name ("AIOSEO").
			'content'           => sprintf( __( 'We have detected you are running %1$s. Upgrade to %2$s to unlock our advanced eCommerce SEO features, including SEO for Product Categories and more.', 'all-in-one-seo-pack' ), 'WooCommerce', AIOSEO_PLUGIN_SHORT_NAME . ' Pro' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'type'              => 'info',
			'level'             => [ 'all' ],
			// Translators: 1 - "Pro".
			'button1_label'     => sprintf( __( 'Upgrade to %1$s', 'all-in-one-seo-pack' ), 'Pro' ),
			'button1_action'    => aioseo()->helpers->decodeHtmlEntities(
				apply_filters(
					'aioseo_upgrade_link',
					aioseo()->helpers->utmUrl( AIOSEO_MARKETING_URL . 'lite-upgrade/', 'woo-notification-upsell', false )
				)
			),
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}
}