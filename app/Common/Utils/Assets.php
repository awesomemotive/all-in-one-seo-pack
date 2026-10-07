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
	 * @since   4.1.9
	 * @version 5.0.3 The manifest path is built by {@see distPath()}.
	 *
	 * @param \AIOSEO\Plugin\Common\Core\Core $core The AIOSEO Core class.
	 */
	public function __construct( $core ) {
		$this->core         = $core;
		$this->version      = aioseo()->version;
		$this->manifestFile = $this->distPath( 'manifest.php' );
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
	 * Get the filesystem path of a file inside the built dist directory.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $path The path inside the dist directory.
	 * @return string       The absolute filesystem path.
	 */
	private function distPath( $path = '' ) {
		return AIOSEO_DIR . '/dist/' . aioseo()->versionPath . '/' . ltrim( $path, '/' );
	}

	/**
	 * Get the script dependencies the build recorded for an asset.
	 *
	 * NOTE: The manifest lists what webpack externalized, so a package a bundle reads off a global
	 * instead of importing is absent from it and has to be declared by the caller.
	 *
	 * @since 5.0.3
	 *
	 * @param  string $asset    The asset path inside the assets directory, without an extension.
	 * @param  array  $fallback The dependencies to use when no manifest is readable.
	 * @return array            The script dependencies.
	 */
	public function getAssetDependencies( $asset, $fallback = [] ) {
		// Keep the lookup inside the dist directory, since the path reaches the filesystem.
		if ( false !== strpos( $asset, '..' ) ) {
			return $fallback;
		}

		$manifest = $this->distPath( 'assets/' . ltrim( $asset, '/' ) . '.asset.json' );
		$data     = json_decode( (string) aioseo()->core->fs->getContents( $manifest ), true );

		return empty( $data['dependencies'] ) || ! is_array( $data['dependencies'] )
			? $fallback
			: $data['dependencies'];
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