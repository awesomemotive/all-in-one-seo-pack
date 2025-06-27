<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Plugins {
	/**
	 * Installs plugins from vue.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function installPlugins( $request ) {
		$error   = esc_html__( 'Installation failed. Please check permissions and try again.', 'all-in-one-seo-pack' );
		$body    = $request->get_json_params();
		$plugins = ! empty( $body['plugins'] ) ? $body['plugins'] : [];
		$network = ! empty( $body['network'] ) ? $body['network'] : false;

		if ( ! is_array( $plugins ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		if ( ! aioseo()->addons->canInstall() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		$failed    = [];
		$completed = [];
		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin['plugin'] ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $error
				], 400 );
			}

			$result = aioseo()->addons->installAddon( $plugin['plugin'], $network );
			if ( ! $result ) {
				$failed[] = $plugin['plugin'];
			} else {
				$completed[ $plugin['plugin'] ] = $result;
			}
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'completed' => $completed,
			'failed'    => $failed
		], 200 );
	}

	/**
	 * Upgrade plugins from vue.
	 *
	 * @since 4.1.6
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function upgradePlugins( $request ) {
		$error   = esc_html__( 'Plugin update failed. Please check permissions and try again.', 'all-in-one-seo-pack' );
		$body    = $request->get_json_params();
		$plugins = ! empty( $body['plugins'] ) ? $body['plugins'] : [];
		$network = ! empty( $body['network'] ) ? $body['network'] : false;

		if ( ! is_array( $plugins ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		if ( ! aioseo()->addons->canUpdate() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		$failed    = [];
		$completed = [];
		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin['plugin'] ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $error
				], 400 );
			}

			$result = aioseo()->addons->upgradeAddon( $plugin['plugin'], $network );
			if ( ! $result ) {
				$failed[] = $plugin['plugin'];
			} else {
				$completed[ $plugin['plugin'] ] = aioseo()->addons->getAddon( $plugin['plugin'], true );
			}
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'completed' => $completed,
			'failed'    => $failed
		], 200 );
	}

	/**
	 * Deactivates plugins from vue.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deactivatePlugins( $request ) {
		$error   = esc_html__( 'Deactivation failed. Please check permissions and try again.', 'all-in-one-seo-pack' );
		$body    = $request->get_json_params();
		$plugins = ! empty( $body['plugins'] ) ? $body['plugins'] : [];
		$network = ! empty( $body['network'] ) ? $body['network'] : false;

		if ( ! is_array( $plugins ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$failed    = [];
		$completed = [];
		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin['plugin'] ) ) {
				return new \WP_REST_Response( [
					'success' => false,
					'message' => $error
				], 400 );
			}

			deactivate_plugins( $plugin['plugin'], false, $network );

			$stillActive = $network ? is_plugin_active_for_network( $plugin['plugin'] ) : is_plugin_active( $plugin['plugin'] );
			if ( $stillActive ) {
				$failed[] = $plugin['plugin'];
			}

			$completed[] = $plugin['plugin'];
		}

		return new \WP_REST_Response( [
			'success'   => true,
			'completed' => $completed,
			'failed'    => $failed
		], 200 );
	}
}