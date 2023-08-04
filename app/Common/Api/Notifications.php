<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Notifications {
	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function blogVisibilityReminder() {
		return self::reminder( 'blog-visibility' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.5
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function descriptionFormatReminder() {
		return self::reminder( 'description-format' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installMiReminder() {
		return self::reminder( 'install-mi' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.2.1
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installOmReminder() {
		return self::reminder( 'install-om' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installAddonsReminder() {
		return self::reminder( 'install-addons' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installImageSeoReminder() {
		return self::reminder( 'install-aioseo-image-seo' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installLocalBusinessReminder() {
		return self::reminder( 'install-aioseo-local-business' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installNewsSitemapReminder() {
		return self::reminder( 'install-aioseo-news-sitemap' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function installVideoSitemapReminder() {
		return self::reminder( 'install-aioseo-video-sitemap' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function conflictingPluginsReminder() {
		return self::reminder( 'conflicting-plugins' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function migrationCustomFieldReminder() {
		return self::reminder( 'v3-migration-custom-field' );
	}

	/**
	 * Extend the start date of a notice.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function migrationSchemaNumberReminder() {
		return self::reminder( 'v3-migration-schema-number' );
	}

	/**
	 * This allows us to not repeat code over and over.
	 *
	 * @since 4.0.0
	 *
	 * @param  string            $slug The slug of the reminder.
	 * @return \WP_REST_Response       The response.
	 */
	protected static function reminder( $slug ) {
		aioseo()->notices->remindMeLater( $slug );

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}

	/**
	 * Dismiss notifications.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function dismissNotifications( $request ) {
		$slugs = $request->get_json_params();

		$notifications = aioseo()->core->db
			->start( 'aioseo_notifications' )
			->whereIn( 'slug', $slugs )
			->run()
			->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' );

		foreach ( $notifications as $notification ) {
			$notification->dismissed = 1;
			$notification->save();
		}

		// Dismiss static notifications.
		if ( in_array( 'notification-review', $slugs, true ) ) {
			update_user_meta( get_current_user_id(), '_aioseo_notification_plugin_review_dismissed', '3' );
		}

		if ( in_array( 'notification-review-delay', $slugs, true ) ) {
			update_user_meta( get_current_user_id(), '_aioseo_notification_plugin_review_dismissed', strtotime( '+1 week' ) );
		}

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}
}