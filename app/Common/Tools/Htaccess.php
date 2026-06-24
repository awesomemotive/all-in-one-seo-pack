<?php
namespace AIOSEO\Plugin\Common\Tools;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Htaccess {
	/**
	 * The path to the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Class constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->path = ABSPATH . '.htaccess';
	}

	/**
	 * Get the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @return string The contents of the file.
	 */
	public function getContents() {
		$fs = aioseo()->core->fs;
		if ( ! $fs->exists( $this->path ) ) {
			return false;
		}

		$contents = $fs->getContents( $this->path );

		return aioseo()->helpers->encodeOutputHtml( $contents );
	}

	/**
	 * Saves the contents of the .htaccess file.
	 *
	 * @since 4.0.0
	 *
	 * @param  string  $contents The contents to write.
	 * @return boolean           True if the file was updated.
	 */
	public function saveContents( $contents ) {
		$fs = aioseo()->core->fs;
		if ( ! $fs->isWritable( $this->path ) ) {
			return [
				'success' => false,
				'reason'  => 'file-not-writable',
				'message' => sprintf(
					// Translators: 1 - The full path to the .htaccess file, 2 - File permission value (e.g. "644").
					__( 'We couldn\'t save the .htaccess file at %1$s because it isn\'t writable by WordPress. Try changing the file permissions to %2$s (or contact your host), then reload this page and save again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$this->path,
					'644'
				)
			];
		}

		$fileExists       = $fs->exists( $this->path );
		$originalContents = $fileExists ? $fs->getContents( $this->path ) : null;
		$fileSaved        = $fs->putContents( $this->path, $contents );
		if ( false === $fileSaved ) {
			return [
				'success' => false,
				'reason'  => 'file-not-saved',
				'message' => sprintf(
					// Translators: 1 - The full path to the .htaccess file.
					__( 'We couldn\'t write to the .htaccess file at %1$s. The file appears writable, but the save operation failed — usually a server-side restriction (disk quota, SELinux, or a security plugin). Check your server logs or contact your host, then try again.', 'all-in-one-seo-pack' ), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
					$this->path
				)
			];
		}

		$response       = wp_safe_remote_get( home_url( '?' . time() ) );
		$isValidRequest = wp_remote_retrieve_response_code( $response );

		if (
			// Add an exception for Windows devs since the request fails in Local.
			! defined( 'AIOSEO_DEV_WINDOWS' ) &&
			( is_wp_error( $response ) || 200 !== $isValidRequest )
		) {
			$fs->putContents( $this->path, $originalContents );

			return [
				'success' => false,
				'reason'  => 'syntax-errors',
				'message' => __( 'Your changes were reverted because the new .htaccess content caused the site to stop responding. This usually means an invalid directive, a redirect loop (e.g. a "force HTTPS" rule when HTTPS is already enforced), or conflicting rewrite rules. Review the code below, remove or comment out recent additions, and try again.', 'all-in-one-seo-pack' ) // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			];
		}

		return [
			'success' => true
		];
	}
}