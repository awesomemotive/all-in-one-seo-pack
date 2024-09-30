<?php
namespace AIOSEO\Plugin\Common\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage tracking class.
 *
 * @since 4.0.0
 */
abstract class Usage {
	/**
	 * Returns the current plugin version type ("lite" or "pro").
	 *
	 * @since 4.1.3
	 *
	 * @return string The version type.
	 */
	abstract public function getType();

	/**
	 * Source of notifications content.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $url = 'https://aiousage.com/v1/track';

	/**
	 * Whether or not usage tracking is enabled.
	 *
	 * @since 4.0.0
	 *
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * Class Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 2 );
	}

	/**
	 * Runs on the init action.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function init() {
		try {
			$action = 'aioseo_send_usage_data';
			if ( ! $this->enabled ) {
				aioseo()->actionScheduler->unschedule( $action );

				return;
			}

			// Register the action handler.
			add_action( $action, [ $this, 'process' ] );

			if ( ! as_next_scheduled_action( $action ) ) {
				as_schedule_recurring_action( $this->generateStartDate(), WEEK_IN_SECONDS, $action, [], 'aioseo' );

				// Run the task immediately using an async action.
				as_enqueue_async_action( $action, [], 'aioseo' );
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}
	}

	/**
	 * Processes the usage tracking.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function process() {
		if ( ! $this->enabled ) {
			return;
		}

		wp_remote_post(
			$this->getUrl(),
			[
				'timeout'    => 10,
				'headers'    => array_merge( [
					'Content-Type' => 'application/json; charset=utf-8'
				], aioseo()->helpers->getApiHeaders() ),
				'user-agent' => aioseo()->helpers->getApiUserAgent(),
				'body'       => wp_json_encode( $this->getData() )
			]
		);
	}

	/**
	 * Gets the URL for the notifications api.
	 *
	 * @since 4.0.0
	 *
	 * @return string The URL to use for the api requests.
	 */
	private function getUrl() {
		if ( defined( 'AIOSEO_USAGE_TRACKING_URL' ) ) {
			return AIOSEO_USAGE_TRACKING_URL;
		}

		return $this->url;
	}

	/**
	 * Retrieves the data to send in the usage tracking.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of data to send.
	 */
	protected function getData() {
		$themeData = wp_get_theme();
		$type      = $this->getType();

		return [
			// Generic data (environment).
			'url'                           => home_url(),
			'php_version'                   => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'wp_version'                    => get_bloginfo( 'version' ),
			'mysql_version'                 => aioseo()->core->db->db->db_version(),
			'server_version'                => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'is_ssl'                        => is_ssl(),
			'is_multisite'                  => is_multisite(),
			'sites_count'                   => function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1,
			'active_plugins'                => $this->getActivePlugins(),
			'theme_name'                    => $themeData->name,
			'theme_version'                 => $themeData->version,
			'user_count'                    => function_exists( 'get_user_count' ) ? get_user_count() : null,
			'locale'                        => get_locale(),
			'timezone_offset'               => wp_timezone_string(),
			'email'                         => get_bloginfo( 'admin_email' ),
			// AIOSEO specific data.
			'aioseo_version'                => AIOSEO_VERSION,
			'aioseo_license_key'            => null,
			'aioseo_license_type'           => null,
			'aioseo_is_pro'                 => false,
			"aioseo_{$type}_installed_date" => aioseo()->internalOptions->internal->installed,
			'aioseo_settings'               => $this->getSettings()
		];
	}

	/**
	 * Get the settings and escape the quotes so it can be JSON encoded.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of settings data.
	 */
	private function getSettings() {
		$settings = aioseo()->options->all();
		array_walk_recursive( $settings, function( &$v ) {
			if ( is_string( $v ) && strpos( $v, '&quot' ) !== false ) {
				$v = str_replace( '&quot', '&#x5c;&quot', $v );
			}
		});

		$settings = $this->filterPrivateSettings( $settings );

		$internal = aioseo()->internalOptions->all();
		array_walk_recursive( $internal, function( &$v ) {
			if ( is_string( $v ) && strpos( $v, '&quot' ) !== false ) {
				$v = str_replace( '&quot', '&#x5c;&quot', $v );
			}
		});

		return [
			'options'  => $settings,
			'internal' => $internal
		];
	}

	/**
	 * Return a list of active plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return array An array of active plugin data.
	 */
	private function getActivePlugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$active  = get_option( 'active_plugins', [] );
		$plugins = array_intersect_key( get_plugins(), array_flip( $active ) );

		return array_map(
			static function ( $plugin ) {
				if ( isset( $plugin['Version'] ) ) {
					return $plugin['Version'];
				}

				return 'Not Set';
			},
			$plugins
		);
	}

	/**
	 * Generate a random start date for usage tracking.
	 *
	 * @since 4.0.0
	 *
	 * @return integer The randomized start date.
	 */
	private function generateStartDate() {
		$tracking = [
			'days'    => wp_rand( 0, 6 ) * DAY_IN_SECONDS,
			'hours'   => wp_rand( 0, 23 ) * HOUR_IN_SECONDS,
			'minutes' => wp_rand( 0, 23 ) * HOUR_IN_SECONDS,
			'seconds' => wp_rand( 0, 59 )
		];

		return strtotime( 'next sunday' ) + array_sum( $tracking );
	}

	/**
	 * Anonimizes or obfuscates the value of certain settings.
	 *
	 * @since 4.3.2
	 *
	 * @param  array $settings The settings.
	 * @return array           The altered settings.
	 */
	private function filterPrivateSettings( $settings ) {
		if ( ! empty( $settings['advanced']['openAiKey'] ) ) {
			$settings['advanced']['openAiKey'] = true;
		}

		if ( ! empty( $settings['localBusiness']['maps']['apiKey'] ) ) {
			$settings['localBusiness']['maps']['apiKey'] = true;
		}

		return $settings;
	}
}