<?php
namespace AIOSEO\Plugin\Common\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Models;
use AIOSEO\Plugin\Common\Tools as CommonTools;

/**
 * Route class for the API.
 *
 * @since 4.0.0
 */
class Tools {
	/**
	 * Import contents from a robots.txt url, static file or pasted text.
	 *
	 * @since   4.0.0
	 * @version 4.4.2
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function importRobotsTxt( $request ) {
		$body   = $request->get_json_params();
		$blogId = ! empty( $body['blogId'] ) ? $body['blogId'] : 0;
		$source = ! empty( $body['source'] ) ? $body['source'] : '';
		$text   = ! empty( $body['text'] ) ? sanitize_textarea_field( $body['text'] ) : '';
		$url    = ! empty( $body['url'] ) ? sanitize_url( $body['url'], [ 'http', 'https' ] ) : '';

		try {
			if ( is_multisite() && 'network' !== $blogId ) {
				aioseo()->helpers->switchToBlog( $blogId );
			}

			switch ( $source ) {
				case 'url':
					aioseo()->robotsTxt->importRobotsTxtFromUrl( $url, $blogId );

					break;
				case 'text':
					aioseo()->robotsTxt->importRobotsTxtFromText( $text, $blogId );

					break;
				case 'static':
				default:
					aioseo()->robotsTxt->importPhysicalRobotsTxt( $blogId );
					aioseo()->robotsTxt->deletePhysicalRobotsTxt();

					$options = aioseo()->options;
					if ( 'network' === $blogId ) {
						$options = aioseo()->networkOptions;
					}

					$options->tools->robots->enable = true;

					break;
			}

			return new \WP_REST_Response( [
				'success'       => true,
				'notifications' => Models\Notification::getNotifications()
			], 200 );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage()
			], 400 );
		}
	}

	/**
	 * Delete the static robots.txt file.
	 *
	 * @since   4.0.0
	 * @version 4.4.5
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function deleteRobotsTxt() {
		try {
			aioseo()->robotsTxt->deletePhysicalRobotsTxt();

			return new \WP_REST_Response( [
				'success'       => true,
				'notifications' => Models\Notification::getNotifications()
			], 200 );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $e->getMessage()
			], 400 );
		}
	}

	/**
	 * Email debug info.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response The response.
	 */
	public static function emailDebugInfo( $request ) {
		$body  = $request->get_json_params();
		$email = ! empty( $body['email'] ) ? $body['email'] : null;

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'invalid-email-address'
			], 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/update.php';

		// Translators: 1 - The plugin name ("All in One SEO"), 2 - The Site URL.
		$html = sprintf( __( '%1$s Debug Info from %2$s', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME, aioseo()->helpers->getSiteDomain() ) . "\r\n------------------\r\n\r\n";
		$info = CommonTools\SystemStatus::getSystemStatusInfo();
		foreach ( $info as $group ) {
			if ( empty( $group['results'] ) ) {
				continue;
			}

			$html .= "\r\n\r\n{$group['label']}\r\n";
			foreach ( $group['results'] as $data ) {
				$html .= "{$data['header']}: {$data['value']}\r\n";
			}
		}

		if ( ! wp_mail(
			$email,
			// Translators: 1 - The plugin name ("All in One SEO).
			sprintf( __( '%1$s Debug Info', 'all-in-one-seo-pack' ), AIOSEO_PLUGIN_NAME ),
			$html
		) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'Unable to send debug email, please check your email send settings and try again.'
			], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}

	/**
	 * Create a settings backup.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function createBackup( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		aioseo()->backup->create();

		return new \WP_REST_Response( [
			'success' => true,
			'backups' => array_reverse( aioseo()->backup->all() )
		], 200 );
	}

	/**
	 * Restore a settings backup.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function restoreBackup( $request ) {
		$body   = $request->get_json_params();
		$backup = ! empty( $body['backup'] ) ? (int) $body['backup'] : null;
		if ( empty( $backup ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'backups' => array_reverse( aioseo()->backup->all() )
			], 400 );
		}

		aioseo()->backup->restore( $backup );

		return new \WP_REST_Response( [
			'success'         => true,
			'backups'         => array_reverse( aioseo()->backup->all() ),
			'options'         => aioseo()->options->all(),
			'internalOptions' => aioseo()->internalOptions->all()
		], 200 );
	}

	/**
	 * Delete a settings backup.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function deleteBackup( $request ) {
		$body   = $request->get_json_params();
		$backup = ! empty( $body['backup'] ) ? (int) $body['backup'] : null;
		if ( empty( $backup ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'backups' => array_reverse( aioseo()->backup->all() )
			], 400 );
		}

		aioseo()->backup->delete( $backup );

		return new \WP_REST_Response( [
			'success' => true,
			'backups' => array_reverse( aioseo()->backup->all() )
		], 200 );
	}

	/**
	 * Save the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @param  \WP_REST_Request  $request The REST Request
	 * @return \WP_REST_Response          The response.
	 */
	public static function saveHtaccess( $request ) {
		$body     = $request->get_json_params();
		$htaccess = ! empty( $body['htaccess'] ) ? sanitize_textarea_field( $body['htaccess'] ) : '';

		if ( empty( $htaccess ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => __( '.htaccess file is empty.', 'all-in-one-seo-pack' )
			], 400 );
		}

		$htaccess     = aioseo()->helpers->decodeHtmlEntities( $htaccess );
		$saveHtaccess = (object) aioseo()->htaccess->saveContents( $htaccess );
		if ( ! $saveHtaccess->success ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $saveHtaccess->message ? $saveHtaccess->message : __( 'An error occurred while trying to write to the .htaccess file. Please try again later.', 'all-in-one-seo-pack' ),
				'reason'  => $saveHtaccess->reason
			], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true
		], 200 );
	}
}