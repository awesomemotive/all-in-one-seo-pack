<?php
namespace AIOSEO\Plugin\Common\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;

/**
 * Abstract class that Pro and Lite both extend.
 *
 * @since 4.0.0
 */
class Notices {
	/**
	 * Source of notifications content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $url = 'https://plugin-cdn.aioseo.com/wp-content/notifications.json';

	/**
	 * Review class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Review
	 */
	private $review = null;

	/**
	 * Migration class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Migration
	 */
	private $migration = null;

	/**
	 * Import class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var Import
	 */
	private $import = null;

	/**
	 * DeprecatedWordPress class instance.
	 *
	 * @since 4.2.7
	 *
	 * @var DeprecatedWordPress
	 */
	private $deprecatedWordPress = null;

	/**
	 * ConflictingPlugins class instance.
	 *
	 * @since 4.5.1
	 *
	 * @var ConflictingPlugins
	 */
	private $conflictingPlugins = null;

	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'aioseo_admin_notifications_update', [ $this, 'update' ] );

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'updated_option', [ $this, 'maybeResetBlogVisibility' ], 10, 3 );
		add_action( 'init', [ $this, 'init' ], 2 );

		$this->review              = new Review();
		$this->migration           = new Migration();
		$this->import              = new Import();
		$this->deprecatedWordPress = new DeprecatedWordPress();
		$this->conflictingPlugins  = new ConflictingPlugins();

		add_action( 'admin_notices', [ $this, 'notices' ] );
	}

	/**
	 * Initialize notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		// If our tables do not exist, create them now.
		if ( ! aioseo()->core->db->tableExists( 'aioseo_notifications' ) ) {
			aioseo()->updates->addInitialCustomTablesForV4();
		}

		$this->maybeUpdate();
		$this->initInternalNotices();
		$this->deleteInternalNotices();
	}

	/**
	 * Checks if we should update our notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function maybeUpdate() {
		$nextRun = aioseo()->core->networkCache->get( 'admin_notifications_update' );
		if ( null !== $nextRun && time() < $nextRun ) {
			return;
		}

		// Schedule the action.
		aioseo()->actionScheduler->scheduleAsync( 'aioseo_admin_notifications_update' );

		// Update the cache.
		aioseo()->core->networkCache->update( 'admin_notifications_update', time() + DAY_IN_SECONDS );
	}

	/**
	 * Update Notifications from the server.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function update() {
		$notifications = $this->fetch();
		foreach ( $notifications as $notification ) {
			// First, let's check to see if this notification already exists. If so, we want to override it.
			$n = aioseo()->core->db
				->start( 'aioseo_notifications' )
				->where( 'notification_id', $notification->id )
				->run()
				->model( 'AIOSEO\\Plugin\\Common\\Models\\Notification' );

			$buttons = [
				'button1' => [
					'label' => ! empty( $notification->btns->main->text ) ? $notification->btns->main->text : null,
					'url'   => ! empty( $notification->btns->main->url ) ? $notification->btns->main->url : null
				],
				'button2' => [
					'label' => ! empty( $notification->btns->alt->text ) ? $notification->btns->alt->text : null,
					'url'   => ! empty( $notification->btns->alt->url ) ? $notification->btns->alt->url : null
				]
			];

			if ( $n->exists() ) {
				$n->title           = $notification->title;
				$n->content         = $notification->content;
				$n->type            = ! empty( $notification->notification_type ) ? $notification->notification_type : 'info';
				$n->level           = $notification->type;
				$n->notification_id = $notification->id;
				$n->start           = ! empty( $notification->start ) ? $notification->start : null;
				$n->end             = ! empty( $notification->end ) ? $notification->end : null;
				$n->button1_label   = $buttons['button1']['label'];
				$n->button1_action  = $buttons['button1']['url'];
				$n->button2_label   = $buttons['button2']['label'];
				$n->button2_action  = $buttons['button2']['url'];
				$n->save();
				continue;
			}

			$n                  = new Models\Notification();
			$n->slug            = uniqid();
			$n->title           = $notification->title;
			$n->content         = $notification->content;
			$n->type            = ! empty( $notification->notification_type ) ? $notification->notification_type : 'info';
			$n->level           = $notification->type;
			$n->notification_id = $notification->id;
			$n->start           = ! empty( $notification->start ) ? $notification->start : null;
			$n->end             = ! empty( $notification->end ) ? $notification->end : null;
			$n->button1_label   = $buttons['button1']['label'];
			$n->button1_action  = $buttons['button1']['url'];
			$n->button2_label   = $buttons['button2']['label'];
			$n->button2_action  = $buttons['button2']['url'];
			$n->dismissed       = 0;
			$n->save();

			// Since we've added a new remote notification, let's show the notification drawer.
			aioseo()->core->cache->update( 'show_notifications_drawer', true );
		}
	}

	/**
	 * Fetches the feed of notifications.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of notifications.
	 */
	private function fetch() {
		$response = aioseo()->helpers->wpRemoteGet( $this->getUrl() );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return [];
		}

		return $this->verify( json_decode( $body ) );
	}

	/**
	 * Verify notification data before it is saved.
	 *
	 * @since 4.0.0
	 *
	 * @param  array $notifications Array of notifications items to verify.
	 * @return array                An array of verified notifications.
	 */
	private function verify( $notifications ) {
		$data = [];
		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return $data;
		}

		foreach ( $notifications as $notification ) {
			// The message and license should never be empty, if they are, ignore.
			if ( empty( $notification->content ) || empty( $notification->type ) ) {
				continue;
			}

			if ( ! is_array( $notification->type ) ) {
				$notification->type = [ $notification->type ];
			}
			foreach ( $notification->type as $type ) {
				// Ignore if type does not match.
				if ( ! $this->validateType( $type ) ) {
					continue 2;
				}
			}

			// Ignore if expired.
			if ( ! empty( $notification->end ) && time() > strtotime( $notification->end ) ) {
				continue;
			}

			// Ignore if notification existed before installing AIOSEO.
			// Prevents bombarding the user with notifications after activation.
			$activated = aioseo()->internalOptions->internal->firstActivated( time() );
			if (
				! empty( $notification->start ) &&
				$activated > strtotime( $notification->start )
			) {
				continue;
			}

			$data[] = $notification;
		}

		return $data;
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
		$validated = false;

		if ( 'all' === $type ) {
			$validated = true;
		}

		// Store notice if version matches.
		if ( $this->versionMatch( aioseo()->version, $type ) ) {
			$validated = true;
		}

		return $validated;
	}

	/**
	 * Version Compare.
	 *
	 * @since 4.0.0
	 *
	 * @param  string       $currentVersion The current version being used.
	 * @param  string|array $compareVersion The version to compare with.
	 * @return bool                         True if we match, false if not.
	 */
	public function versionMatch( $currentVersion, $compareVersion ) {
		if ( is_array( $compareVersion ) ) {
			foreach ( $compareVersion as $compare_single ) {
				$recursiveResult = $this->versionMatch( $currentVersion, $compare_single );
				if ( $recursiveResult ) {
					return true;
				}
			}

			return false;
		}

		$currentParse = explode( '.', $currentVersion );
		if ( strpos( $compareVersion, '-' ) ) {
			$compareParse = explode( '-', $compareVersion );
		} elseif ( strpos( $compareVersion, '.' ) ) {
			$compareParse = explode( '.', $compareVersion );
		} else {
			return false;
		}

		$currentCount = count( $currentParse );
		$compareCount = count( $compareParse );
		for ( $i = 0; $i < $currentCount || $i < $compareCount; $i++ ) {
			if ( isset( $compareParse[ $i ] ) && 'x' === strtolower( $compareParse[ $i ] ) ) {
				unset( $compareParse[ $i ] );
			}

			if ( ! isset( $currentParse[ $i ] ) ) {
				unset( $compareParse[ $i ] );
			} elseif ( ! isset( $compareParse[ $i ] ) ) {
				unset( $currentParse[ $i ] );
			}
		}

		foreach ( $compareParse as $index => $subNumber ) {
			if ( $currentParse[ $index ] !== $subNumber ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Gets the URL for the notifications api.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to use for the api requests.
	 */
	private function getUrl() {
		if ( defined( 'AIOSEO_NOTIFICATIONS_URL' ) ) {
			return AIOSEO_NOTIFICATIONS_URL;
		}

		return $this->url;
	}

	/**
	 * Add notices.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function notices() {
		// Double check we're actually in the admin before outputting anything.
		if ( ! is_admin() ) {
			return;
		}

		$this->review->maybeShowNotice();
		$this->migration->maybeShowNotice();
		$this->import->maybeShowNotice();
		$this->deprecatedWordPress->maybeShowNotice();
		$this->conflictingPlugins->maybeShowNotice();
	}

	/**
	 * Initialize the internal notices.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function initInternalNotices() {
		$this->blogVisibility();
		$this->descriptionFormat();
	}

	/**
	 * Deletes internal notices we no longer need.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function deleteInternalNotices() {
		$pluginData = aioseo()->helpers->getPluginData();
		if ( $pluginData['miPro']['installed'] || $pluginData['miLite']['installed'] ) {
			$notification = Models\Notification::getNotificationByName( 'install-mi' );
			if ( ! $notification->exists() ) {
				return;
			}

			Models\Notification::deleteNotificationByName( 'install-mi' );
		}

		if ( $pluginData['optinMonster']['installed'] ) {
			$notification = Models\Notification::getNotificationByName( 'install-om' );
			if ( ! $notification->exists() ) {
				return;
			}

			Models\Notification::deleteNotificationByName( 'install-om' );
		}
	}

	/**
	 * Extends a notice by a (default) 1 week start date.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $notice The notice to extend.
	 * @param  string $start  How long to extend.
	 * @return void
	 */
	public function remindMeLater( $notice, $start = '+1 week' ) {
		$notification = Models\Notification::getNotificationByName( $notice );
		if ( ! $notification->exists() ) {
			return;
		}

		$notification->start = gmdate( 'Y-m-d H:i:s', strtotime( $start ) );
		$notification->save();
	}

	/**
	 * Add a notice if the blog is set to hidden.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	private function blogVisibility() {
		$notification = Models\Notification::getNotificationByName( 'blog-visibility' );
		if ( get_option( 'blog_public' ) ) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'blog-visibility' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'blog-visibility',
			'title'             => __( 'Search Engines Blocked', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'Warning: %1$s has detected that you are blocking access to search engines. You can change this in Settings > Reading if this was unintended.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
			'button1_action'    => admin_url( 'options-reading.php' ),
			'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#notification/blog-visibility-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Add a notice if the description format is missing the Description tag.
	 *
	 * @since 4.0.5
	 *
	 * @return void
	 */
	private function descriptionFormat() {
		$notification = Models\Notification::getNotificationByName( 'description-format' );
		if ( ! in_array( 'descriptionFormat', aioseo()->internalOptions->deprecatedOptions, true ) ) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'description-format' );
			}

			return;
		}

		$descriptionFormat = aioseo()->options->deprecated->searchAppearance->global->descriptionFormat;
		if ( false !== strpos( $descriptionFormat, '#description' ) ) {
			if ( $notification->exists() ) {
				Models\Notification::deleteNotificationByName( 'description-format' );
			}

			return;
		}

		if ( $notification->exists() ) {
			return;
		}

		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'description-format',
			'title'             => __( 'Invalid Description Format', 'all-in-one-seo-pack' ),
			'content'           => sprintf(
				// Translators: 1 - The plugin short name ("AIOSEO").
				__( 'Warning: %1$s has detected that you may have an invalid description format. This could lead to descriptions not being properly applied to your content.', 'all-in-one-seo-pack' ),
				AIOSEO_PLUGIN_SHORT_NAME
			) . ' ' . __( 'A Description tag is required in order to properly display your meta descriptions on your site.', 'all-in-one-seo-pack' ),
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://route#aioseo-search-appearance&aioseo-scroll=description-format&aioseo-highlight=description-format:advanced',
			'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#notification/description-format-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}

	/**
	 * Check if blog visibility is changing and add/delete the appropriate notification.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $optionName The name of the option we are checking.
	 * @param  mixed  $oldValue   The old value.
	 * @param  mixed  $newValue   The new value.
	 * @return void
	 */
	public function maybeResetBlogVisibility( $optionName, $oldValue = '', $newValue = '' ) {
		if ( 'blog_public' === $optionName ) {
			if ( 1 === intval( $newValue ) ) {
				$notification = Models\Notification::getNotificationByName( 'blog-visibility' );
				if ( ! $notification->exists() ) {
					return;
				}

				Models\Notification::deleteNotificationByName( 'blog-visibility' );

				return;
			}

			$this->blogVisibility();
		}
	}

	/**
	 * Add a notice if the blog is set to hidden.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function conflictingPlugins( $plugins = [] ) {
		if ( empty( $plugins ) ) {
			return;
		}

		$content = sprintf(
			// Translators: 1 - The plugin short name ("AIOSEO").
			__( 'Warning: %1$s has detected other active SEO or sitemap plugins. We recommend that you deactivate the following plugins to prevent any conflicts:', 'all-in-one-seo-pack' ),
			AIOSEO_PLUGIN_SHORT_NAME
		) . '<ul>';

		foreach ( $plugins as $pluginName => $pluginPath ) {
			$content .= '<li><strong>' . $pluginName . '</strong></li>';
		}

		$content .= '</ul>';

		// Update an existing notice.
		$notification = Models\Notification::getNotificationByName( 'conflicting-plugins' );
		if ( $notification->exists() ) {
			$notification->content = $content;
			$notification->save();

			return;
		}

		// Create a new one if it doesn't exist.
		Models\Notification::addNotification( [
			'slug'              => uniqid(),
			'notification_name' => 'conflicting-plugins',
			'title'             => __( 'Conflicting Plugins Detected', 'all-in-one-seo-pack' ),
			'content'           => $content,
			'type'              => 'error',
			'level'             => [ 'all' ],
			'button1_label'     => __( 'Fix Now', 'all-in-one-seo-pack' ),
			'button1_action'    => 'http://action#sitemap/deactivate-conflicting-plugins?refresh',
			'button2_label'     => __( 'Remind Me Later', 'all-in-one-seo-pack' ),
			'button2_action'    => 'http://action#notification/conflicting-plugins-reminder',
			'start'             => gmdate( 'Y-m-d H:i:s' )
		] );
	}
}