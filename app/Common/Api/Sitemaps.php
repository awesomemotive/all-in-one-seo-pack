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
class Sitemaps {
	/**
	 * Delete all static sitemap files.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function deleteStaticFiles() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$files = list_files( get_home_path(), 1 );
		if ( ! count( $files ) ) {
			return;
		}

		$isGeneralSitemapStatic = aioseo()->options->sitemap->general->advancedSettings->enable &&
			in_array( 'staticSitemap', aioseo()->internalOptions->internal->deprecatedOptions, true ) &&
			! aioseo()->options->deprecated->sitemap->general->advancedSettings->dynamic;

		$detectedFiles = [];
		if ( ! $isGeneralSitemapStatic ) {
			foreach ( $files as $filename ) {
				if ( preg_match( '#.*sitemap.*#', (string) $filename ) ) {
					// We don't want to delete the video sitemap here at all.
					$isVideoSitemap = preg_match( '#.*video.*#', (string) $filename ) ? true : false;
					if ( ! $isVideoSitemap ) {
						$detectedFiles[] = $filename;
					}
				}
			}
		}

		if ( ! count( $detectedFiles ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No sitemap files found.'
			], 400 );
		}

		$fs = aioseo()->core->fs;
		if ( ! $fs->isWpfsValid() ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No access to filesystem.'
			], 400 );
		}

		foreach ( $detectedFiles as $file ) {
			$fs->fs->delete( $file, false, 'f' );
		}

		Models\Notification::deleteNotificationByName( 'sitemap-static-files' );

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}

	/**
	 * Deactivates conflicting plugins.
	 *
	 * @since 4.0.0
	 *
	 * @return \WP_REST_Response The response.
	 */
	public static function deactivateConflictingPlugins() {
		$error = esc_html__( 'Deactivation failed. Please check permissions and try again.', 'all-in-one-seo-pack' );
		if ( ! current_user_can( 'install_plugins' ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => $error
			], 400 );
		}

		aioseo()->conflictingPlugins->deactivateConflictingPlugins( [ 'seo', 'sitemap' ] );

		Models\Notification::deleteNotificationByName( 'conflicting-plugins' );

		return new \WP_REST_Response( [
			'success'       => true,
			'notifications' => Models\Notification::getNotifications()
		], 200 );
	}

	/**
	* Check whether the slug for the HTML sitemap is not in use.
	*
	* @since 4.1.3
	*
	* @param  \WP_REST_Request   $request The REST Request
	* @return \WP_REST_Response           The response.
	*/
	public static function validateHtmlSitemapSlug( $request ) {
		$body = $request->get_json_params();

		$pageUrl = ! empty( $body['pageUrl'] ) ? sanitize_text_field( $body['pageUrl'] ) : '';
		if ( empty( $pageUrl ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'No path was provided.'
			], 400 );
		}

		$parsedPageUrl = wp_parse_url( $pageUrl );
		if ( empty( $parsedPageUrl['path'] ) ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'The given path is invalid.'
			], 400 );
		}

		$isUrl         = aioseo()->helpers->isUrl( $pageUrl );
		$isInternalUrl = aioseo()->helpers->isInternalUrl( $pageUrl );
		if ( $isUrl && ! $isInternalUrl ) {
			return new \WP_REST_Response( [
				'success' => false,
				'message' => 'The given URL is not a valid internal URL.'
			], 400 );
		}

		$pathExists = self::pathExists( $parsedPageUrl['path'], false );

		return new \WP_REST_Response( [
			'exists' => $pathExists
		], 200 );
	}

	/**
	 * Checks whether the given path is unique or not.
	 *
	 * @since   4.1.4
	 * @version 4.2.6
	 *
	 * @param  string  $path The path.
	 * @param  bool    $path Whether the given path is a URL.
	 * @return boolean       Whether the path exists.
	 */
	private static function pathExists( $path, $isUrl ) {
		$path = trim( aioseo()->helpers->excludeHomePath( $path ), '/' );
		$url  = $isUrl ? $path : trailingslashit( home_url() ) . $path;
		$url  = user_trailingslashit( $url );

		// Let's do another check here, just to be sure that the domain matches.
		if ( ! aioseo()->helpers->isInternalUrl( $url ) ) {
			return false;
		}

		$response = wp_safe_remote_head( $url );
		$status   = wp_remote_retrieve_response_code( $response );

		if ( ! $status ) {
			// If there is no status code, we might be in a local environment with CURL misconfigured.
			// In that case we can still check if a post exists for the path by quering the DB.
			$post = aioseo()->helpers->getPostbyPath(
				$path,
				OBJECT,
				aioseo()->helpers->getPublicPostTypes( true )
			);

			return is_object( $post );
		}

		return 200 === $status;
	}
}