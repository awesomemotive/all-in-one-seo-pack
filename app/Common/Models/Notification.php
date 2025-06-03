<?php
namespace AIOSEO\Plugin\Common\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Notification DB Model.
 *
 * @since 4.0.0
 */
class Notification extends Model {
	/**
	 * The name of the table in the database, without the prefix.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	protected $table = 'aioseo_notifications';

	/**
	 * An array of fields to set to null if already empty when saving to the database.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $nullFields = [
		'start',
		'end',
		'notification_id',
		'notification_name',
		'button1_label',
		'button1_action',
		'button2_label',
		'button2_action'
	];

	/**
	 * Fields that should be json encoded on save and decoded on get.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $jsonFields = [ 'level' ];

	/**
	 * Fields that should be boolean values.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $booleanFields = [ 'dismissed' ];

	/**
	 * Fields that should be hidden when serialized.
	 *
	 * @var array
	 */
	protected $hidden = [ 'id' ];

	/**
	 * An array of fields attached to this resource.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected $columns = [
		'id',
		'slug',
		'addon',
		'title',
		'content',
		'type',
		'level',
		'notification_id',
		'notification_name',
		'start',
		'end',
		'button1_label',
		'button1_action',
		'button2_label',
		'button2_action',
		'dismissed',
		'new',
		'created',
		'updated'
	];

	/**
	 * Get the list of notifications.
	 *
	 * @since 4.1.3
	 *
	 * @param  bool  $reset Whether or not to reset the notifications.
	 * @return array        An array of notifications.
	 */
	public static function getNotifications( $reset = true ) {
		static $notifications = null;
		if ( null !== $notifications ) {
			return $notifications;
		}

		$notifications = [
			'active'    => self::getAllActiveNotifications(),
			'new'       => self::getNewNotifications( $reset ),
			'dismissed' => self::getAllDismissedNotifications()
		];

		return $notifications;
	}

	/**
	 * Get an array of active notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of active notifications.
	 */
	public static function getAllActiveNotifications() {
		static $activeNotifications = null;
		if ( null !== $activeNotifications ) {
			return $activeNotifications;
		}

		$staticNotifications = self::getStaticNotifications();
		$notifications       = array_values( json_decode( wp_json_encode( self::getActiveNotifications() ), true ) );

		$activeNotifications = ! empty( $staticNotifications )
			? array_merge( $staticNotifications, $notifications )
			: $notifications;

		return $activeNotifications;
	}

	/**
	 * Get all new notifications. After retrieving them, this will reset them.
	 * This means that calling this method twice will result in no results
	 * the second time. The only exception is to pass false as a reset variable to prevent it.
	 *
	 * @since 4.1.3
	 *
	 * @param  bool  $reset Whether or not to reset the new notifications.
	 * @return array        An array of new notifications if any exist.
	 */
	public static function getNewNotifications( $reset = true ) {
		static $newNotifications = null;
		if ( null !== $newNotifications ) {
			return $newNotifications;
		}

		$newNotifications = self::filterNotifications(
			aioseo()->core->db
				->start( 'aioseo_notifications' )
				->where( 'dismissed', 0 )
				->where( 'new', 1 )
				->whereRaw( "(start <= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR start IS NULL)" )
				->whereRaw( "(end >= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR end IS NULL)" )
				->orderBy( 'start DESC' )
				->orderBy( 'created DESC' )
				->run()
				->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' )
		);

		if ( $reset ) {
			self::resetNewNotifications();
		}

		return $newNotifications;
	}

	/**
	 * Resets all new notifications.
	 *
	 * @since 4.1.3
	 *
	 * @return void
	 */
	public static function resetNewNotifications() {
		aioseo()->core->db
			->update( 'aioseo_notifications' )
			->where( 'new', 1 )
			->set( 'new', 0 )
			->run();
	}

	/**
	 * Returns all static notifications.
	 *
	 * @since 4.1.2
	 *
	 * @return array An array of static notifications.
	 */
	public static function getStaticNotifications() {
		$staticNotifications = [];
		$notifications       = [
			'unlicensed-addons',
			'review'
		];

		foreach ( $notifications as $notification ) {
			switch ( $notification ) {
				case 'review':
					// If they intentionally dismissed the main notification, we don't show the repeat one.
					$originalDismissed = get_user_meta( get_current_user_id(), '_aioseo_plugin_review_dismissed', true );
					if ( '4' !== $originalDismissed ) {
						break;
					}

					$dismissed = get_user_meta( get_current_user_id(), '_aioseo_notification_plugin_review_dismissed', true );
					if ( '3' === $dismissed ) {
						break;
					}

					if ( ! empty( $dismissed ) && $dismissed > time() ) {
						break;
					}

					$activated = aioseo()->internalOptions->internal->firstActivated( time() );
					if ( $activated > strtotime( '-20 days' ) ) {
						break;
					}

					$isV3                  = get_option( 'aioseop_options' ) || get_option( 'aioseo_options_v3' );
					$staticNotifications[] = [
						'slug'      => 'notification-' . $notification,
						'component' => 'notifications-' . $notification . ( $isV3 ? '' : '2' )
					];
					break;
				case 'unlicensed-addons':
					$unlicensedAddons = aioseo()->addons->unlicensedAddons();
					if ( empty( $unlicensedAddons['addons'] ) ) {
						break;
					}

					$staticNotifications[] = [
						'slug'      => 'notification-' . $notification,
						'component' => 'notifications-' . $notification,
						'addons'    => $unlicensedAddons['addons'],
						'message'   => $unlicensedAddons['message']
					];
					break;
			}
		}

		return $staticNotifications;
	}

	/**
	 * Retrieve active notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of active notifications or empty.
	 */
	protected static function getActiveNotifications() {
		return self::filterNotifications(
			aioseo()->core->db
				->start( 'aioseo_notifications' )
				->where( 'dismissed', 0 )
				->whereRaw( "(start <= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR start IS NULL)" )
				->whereRaw( "(end >= '" . gmdate( 'Y-m-d H:i:s' ) . "' OR end IS NULL)" )
				->orderBy( 'start DESC' )
				->orderBy( 'created DESC' )
				->run()
				->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' )
		);
	}

	/**
	 * Get an array of dismissed notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of dismissed notifications.
	 */
	protected static function getAllDismissedNotifications() {
		return array_values( json_decode( wp_json_encode( self::getDismissedNotifications() ), true ) );
	}

	/**
	 * Retrieve dismissed notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of dismissed notifications or empty.
	 */
	protected static function getDismissedNotifications() {
		static $dismissedNotifications = null;
		if ( null !== $dismissedNotifications ) {
			return $dismissedNotifications;
		}

		$dismissedNotifications = self::filterNotifications(
			aioseo()->core->db
				->start( 'aioseo_notifications' )
				->where( 'dismissed', 1 )
				->orderBy( 'updated DESC' )
				->run()
				->models( 'AIOSEO\\Plugin\\Common\\Models\\Notification' )
		);

		return $dismissedNotifications;
	}

	/**
	 * Returns a notification by its name.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $name The notification name.
	 * @return Notification       The notification.
	 */
	public static function getNotificationByName( $name ) {
		return aioseo()->core->db
			->start( 'aioseo_notifications' )
			->where( 'notification_name', $name )
			->run()
			->model( 'AIOSEO\\Plugin\\Common\\Models\\Notification' );
	}

	/**
	 * Stores a new notification in the DB.
	 *
	 * @since 4.0.0
	 *
	 * @param  array        $fields       The fields.
	 * @return Notification $notification The notification.
	 */
	public static function addNotification( $fields ) {
		// Set the dismissed status to false.
		$fields['dismissed'] = 0;

		$notification = new self();
		$notification->set( $fields );
		$notification->save();

		return $notification;
	}

	/**
	 * Deletes a notification by its name.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $name The notification name.
	 * @return void
	 */
	public static function deleteNotificationByName( $name ) {
		aioseo()->core->db
			->delete( 'aioseo_notifications' )
			->where( 'notification_name', $name )
			->run();
	}

	/**
	 * Filters the notifications based on the targeted plan levels.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $notifications          The notifications
	 * @return array $remainingNotifications The remaining notifications.
	 */
	protected static function filterNotifications( $notifications ) {
		$remainingNotifications = [];
		foreach ( $notifications as $notification ) {
			// If announcements are disabled and this is an announcement, skip adding it and move on.
			if (
				! aioseo()->options->advanced->announcements &&
				'success' === $notification->type
			) {
				continue;
			}

			// If this is an addon notification and the addon is disabled, skip adding it and move on.
			if ( ! empty( $notification->addon ) && ! aioseo()->addons->getLoadedAddon( $notification->addon ) ) {
				continue;
			}

			$levels = $notification->level;
			if ( ! is_array( $levels ) ) {
				$levels = empty( $notification->level ) ? [ 'all' ] : [ $notification->level ];
			}

			foreach ( $levels as $level ) {
				if ( ! aioseo()->notices->validateType( $level ) ) {
					continue 2;
				}
			}

			$remainingNotifications[] = $notification;
		}

		return $remainingNotifications;
	}
}