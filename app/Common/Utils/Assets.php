<?php
namespace AIOSEO\Plugin\Common\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Traits;

/**
 * Load file assets.
 *
 * @since 4.1.9
 */
class Assets {
	use Traits\Assets;

	/**
	 * Get the script handle to use for asset enqueuing.
	 *
	 * @since 4.1.9
	 *
	 * @var string
	 */
	private $scriptHandle = 'aioseo';

	/**
	 * Class constructor.
	 *
	 * @since 4.1.9
	 *
	 * @param \AIOSEO\Plugin\Common\Core\Core $core The AIOSEO Core class.
	 */
	public function __construct( $core ) {
		$this->core         = $core;
		$this->version      = aioseo()->version;
		$this->manifestFile = AIOSEO_DIR . '/dist/' . aioseo()->versionPath . '/manifest.php';
		$this->isDev        = aioseo()->isDev;

		if ( $this->isDev ) {
			$this->domain = getenv( 'VITE_AIOSEO_DOMAIN' );
			$this->port   = getenv( 'VITE_AIOSEO_DEV_PORT' );
		}

		add_filter( 'script_loader_tag', [ $this, 'scriptLoaderTag' ], 10, 3 );
		add_action( 'admin_head', [ $this, 'devRefreshRuntime' ] );
		add_action( 'wp_head', [ $this, 'devRefreshRuntime' ] );
	}

	/**
	 * Get the public URL base.
	 *
	 * @since 4.1.9
	 *
	 * @return string The URL base.
	 */
	private function getPublicUrlBase() {
		return $this->shouldLoadDev() ? $this->getDevUrl() . 'dist/' . aioseo()->versionPath . '/assets/' : $this->basePath();
	}

	/**
	 * Get the base path URL.
	 *
	 * @since 4.1.9
	 *
	 * @return string The base path URL.
	 */
	private function basePath() {
		return $this->normalizeAssetsHost( plugins_url( 'dist/' . aioseo()->versionPath . '/assets/', AIOSEO_FILE ) );
	}

	/**
	 * Adds the RefreshRuntime.
	 *
	 * @since 4.1.9
	 *
	 * @return void
	 */
	public function devRefreshRuntime() {
		if ( $this->shouldLoadDev() ) {
			echo sprintf( '<script type="module">
			import RefreshRuntime from "%1$s@react-refresh"
			RefreshRuntime.injectIntoGlobalHook(window)
			window.$RefreshReg$ = () => {}
			window.$RefreshSig$ = () => (type) => type
			window.__vite_plugin_react_preamble_installed__ = true
			</script>', $this->getDevUrl() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}