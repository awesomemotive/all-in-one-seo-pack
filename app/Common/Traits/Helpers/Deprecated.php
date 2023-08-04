<?php
namespace AIOSEO\Plugin\Common\Traits\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contains deprecated methods to be removed at a later date..
 *
 * @since 4.1.9
 */
trait Deprecated {
	/**
	 * Helper method to enqueue scripts.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $script The script to enqueue.
	 * @param  string $url    The URL of the script.
	 * @param  bool   $vue    Whether or not this is a vue script.
	 * @return void
	 */
	public function enqueueScript( $script, $url, $vue = true ) {
		if ( ! wp_script_is( $script, 'enqueued' ) ) {
			wp_enqueue_script(
				$script,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version,
				true
			);
		}
	}

	/**
	 * Helper method to enqueue stylesheets.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $style The stylesheet to enqueue.
	 * @param  string $url   The URL of the stylesheet.
	 * @param  bool   $vue    Whether or not this is a vue stylesheet.
	 * @return void
	 */
	public function enqueueStyle( $style, $url, $vue = true ) {
		if ( ! wp_style_is( $style, 'enqueued' ) && $this->shouldEnqueue( $url ) ) {
			wp_enqueue_style(
				$style,
				$this->getScriptUrl( $url, $vue ),
				[],
				aioseo()->version
			);
		}
	}

	/**
	 * Whether or not we should enqueue a file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url to check against.
	 * @return bool        Whether or not we should enqueue.
	 */
	private function shouldEnqueue( $url ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;

		if ( ! $host ) {
			return true;
		}

		if ( false !== strpos( $url, 'chunk-common.css' ) ) {
			// return false;
		}

		return true;
	}

	/**
	 * Retrieve the proper URL for this script or style.
	 *
	 * @since 4.0.0
	 *
	 * @param  string $url The url.
	 * @param  bool   $vue Whether or not this is a vue script.
	 * @return string      The modified url.
	 */
	public function getScriptUrl( $url, $vue = true ) {
		$version  = strtoupper( aioseo()->versionPath );
		$host     = $vue && defined( 'AIOSEO_DEV_' . $version ) ? constant( 'AIOSEO_DEV_' . $version ) : false;
		$localUrl = $url;
		$url      = plugins_url( 'dist/' . aioseo()->versionPath . '/assets/' . $url, AIOSEO_FILE );

		if ( ! $host ) {
			return $url;
		}

		if ( $host && ! self::$connection ) {
			$splitHost        = explode( ':', str_replace( '/', '', str_replace( 'http://', '', str_replace( 'https://', '', $host ) ) ) );
			self::$connection = @fsockopen( $splitHost[0], $splitHost[1] ); // phpcs:ignore WordPress
		}

		if ( ! self::$connection ) {
			return $url;
		}

		return $host . $localUrl;
	}

	/**
	 * Returns the filesystem object if we have access to it.
	 *
	 * @since 4.0.0
	 *
	 * @param  array                    $args The connection args.
	 * @return \WP_Filesystem_Base|bool       The filesystem object.
	 */
	public function wpfs( $args = [] ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem( $args );

		global $wp_filesystem;
		if ( is_object( $wp_filesystem ) ) {
			return $wp_filesystem;
		}

		return false;
	}

	/**
	 * Checks whether the current request is an AJAX, CRON or REST request.
	 *
	 * @since 4.1.9.1
	 *
	 * @return bool Whether the current request is an AJAX, CRON or REST request.
	 */
	public function isAjaxCronRest() {
		return $this->isAjaxCronRestRequest();
	}
}